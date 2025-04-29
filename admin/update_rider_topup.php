<?php
// Start output buffering to prevent "headers already sent" error
ob_start();

// Include database connection
include '../config/connect.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the rider details
    $rider_id = $_POST['rider_id'];
    $transaction_type = $_POST['transaction_type'];
    $amount = (float)$_POST['amount'];
    $withdraw_amount = isset($_POST['withdraw_amount']) ? (float)$_POST['withdraw_amount'] : 0;
    $notes = $_POST['notes'] ?? '';

    // Validate inputs
    if (empty($rider_id) || empty($transaction_type) || ($amount <= 0 && $withdraw_amount <= 0)) {
        $_SESSION['error_message'] = "Invalid input data.";
        header("Location: delivery_rider.php");
        exit();
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Get current rider balance
        $rider_query = "SELECT topup_balance FROM triders WHERE id = ?";
        $stmt = $conn->prepare($rider_query);
        $stmt->bind_param('i', $rider_id);
        $stmt->execute();
        $rider_result = $stmt->get_result();
        $rider = $rider_result->fetch_assoc();

        if (!$rider) {
            throw new Exception("Rider not found.");
        }

        $previous_balance = $rider['topup_balance'];
        
        // Handle withdrawal
        if ($withdraw_amount > 0) {
            $amount = $withdraw_amount;
            $transaction_type = 'withdraw';
        }
        
        $new_balance = $transaction_type === 'add' ? $previous_balance + $amount : $previous_balance - $amount;

        // Check if withdrawal is possible
        if ($transaction_type === 'withdraw' && $new_balance < 0) {
            throw new Exception("Insufficient balance for withdrawal.");
        }

        // Update rider balance
        $update_query = "UPDATE triders SET topup_balance = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('di', $new_balance, $rider_id);
        $stmt->execute();

        // Get the current admin username from session
        $processed_by = isset($_SESSION['username']) ? $_SESSION['username'] : 'System';
        
        // Map transaction type to ledger transaction type
        $ledger_transaction_type = $transaction_type === 'add' ? 'Add Top-up' : 'Withdraw Top-up';
        
        // Record transaction in the ledger
        $transaction_query = "INSERT INTO trider_topup_ledger (
            rider_id, 
            transaction_type, 
            previous_balance,
            amount,
            current_balance,
            processed_by, 
            notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($transaction_query);
        $stmt->bind_param('isddiss', 
            $rider_id, 
            $ledger_transaction_type, 
            $previous_balance,
            $amount,
            $new_balance,
            $processed_by, 
            $notes
        );
        $stmt->execute();

        // Commit transaction
        $conn->commit();
        $_SESSION['success_message'] = "Top-up transaction processed successfully.";
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = "Error processing transaction: " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "Invalid request method.";
}

// Redirect back to delivery rider page
header("Location: delivery_rider.php");
exit();
?> 