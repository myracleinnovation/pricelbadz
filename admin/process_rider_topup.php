<?php
session_start();
require_once '../config/connect.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    $_SESSION['error_message'] = 'Unauthorized access!';
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = 'Invalid request method!';
    header('Location: delivery_rider.php');
    exit();
}

// Validate and sanitize inputs
$rider_id = filter_input(INPUT_POST, 'rider_id', FILTER_SANITIZE_NUMBER_INT);
$topup_amount = filter_input(INPUT_POST, 'topup_amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$transaction_type = filter_input(INPUT_POST, 'transaction_type', FILTER_SANITIZE_STRING);
$notes = filter_input(INPUT_POST, 'topup_notes', FILTER_SANITIZE_STRING);

// Debug values
error_log("Processing top-up: rider_id=$rider_id, amount=$topup_amount, type=$transaction_type, notes=$notes");

// Additional validation
if (!$rider_id || !$topup_amount || !$transaction_type) {
    $_SESSION['error_message'] = 'Missing required fields!';
    header('Location: delivery_rider.php');
    exit();
}

if ($topup_amount < 0) {
    $_SESSION['error_message'] = 'Amount cannot be negative!';
    header('Location: delivery_rider.php');
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Get current balance
    $stmt = $conn->prepare('SELECT topup_balance FROM triders WHERE id = ?');
    $stmt->bind_param('i', $rider_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Rider not found!');
    }

    $current_balance = $result->fetch_assoc()['topup_balance'];
    error_log("Current balance for rider $rider_id: $current_balance");
    
    // Calculate new balance based on transaction type
    if ($transaction_type === 'TOP_UP') {
        $new_balance = $current_balance + $topup_amount;
        $ledger_type = 'Add Top-up';
    } else if ($transaction_type === 'WITHDRAWAL') {
        // Check if rider has enough balance for withdrawal
        if ($current_balance < $topup_amount) {
            throw new Exception('Insufficient balance for withdrawal!');
        }
        $new_balance = $current_balance - $topup_amount;
        $ledger_type = 'Withdraw Top-up';
    } else {
        throw new Exception('Invalid transaction type!');
    }

    error_log("New balance will be: $new_balance");

    // Update rider's balance
    $update_stmt = $conn->prepare('UPDATE triders SET topup_balance = ? WHERE id = ?');
    if (!$update_stmt) {
        throw new Exception('Failed to prepare update statement: ' . $conn->error);
    }
    
    $update_stmt->bind_param('di', $new_balance, $rider_id);
    if (!$update_stmt->execute()) {
        throw new Exception('Failed to update rider balance! Error: ' . $update_stmt->error);
    }

    if ($update_stmt->affected_rows === 0) {
        throw new Exception('No changes were made to the rider\'s balance. Please check if the rider exists.');
    }

    error_log("Successfully updated rider balance. Affected rows: " . $update_stmt->affected_rows);

    // Record the transaction
    $transaction_stmt = $conn->prepare("
        INSERT INTO trider_topup_ledger
        (rider_id, transaction_type, previous_balance, amount, current_balance, processed_by, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$transaction_stmt) {
        throw new Exception('Failed to prepare transaction statement: ' . $conn->error);
    }

    $transaction_stmt->bind_param('isddss', 
        $rider_id, 
        $ledger_type, 
        $current_balance, 
        $topup_amount, 
        $new_balance, 
        $_SESSION['username'], 
        $notes
    );

    if (!$transaction_stmt->execute()) {
        throw new Exception('Failed to record transaction! Error: ' . $transaction_stmt->error);
    }

    if ($transaction_stmt->affected_rows === 0) {
        throw new Exception('Failed to record transaction in ledger. No rows were inserted.');
    }

    error_log("Successfully recorded transaction in ledger. Transaction ID: " . $transaction_stmt->insert_id);

    // Commit transaction
    $conn->commit();

    $action = $transaction_type === 'TOP_UP' ? 'topped up' : 'withdrawn';
    $_SESSION['success_message'] = 'Successfully ' . $action . ' ₱' . number_format($topup_amount, 2) . " from rider's balance!";
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    error_log("Error in top-up process: " . $e->getMessage());
    $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
} finally {
    // Close all statements
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($update_stmt)) {
        $update_stmt->close();
    }
    if (isset($transaction_stmt)) {
        $transaction_stmt->close();
    }

    header('Location: delivery_rider.php');
    exit();
}
