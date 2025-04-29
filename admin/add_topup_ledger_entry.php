<?php
// Start output buffering to prevent "headers already sent" error
ob_start();

// Include database connection
include '../config/connect.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rider_id = $_POST['rider_id'];
    $transaction_type = $_POST['transaction_type'];
    $amount = $_POST['amount'];
    $order_number = $_POST['order_number'] ?? null;
    $notes = $_POST['notes'] ?? null;
    
    // Get the current admin username from session
    $author = isset($_SESSION['username']) ? $_SESSION['username'] : 'System';
    
    // Insert the ledger entry
    $query = "INSERT INTO trider_topup_ledger (rider_id, transaction_type, amount, order_number, author, notes) 
              VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isdsss", $rider_id, $transaction_type, $amount, $order_number, $author, $notes);
    
    if ($stmt->execute()) {
        // Update the rider's top-up balance based on transaction type
        $balance_query = "SELECT topup_balance FROM triders WHERE id = ?";
        $balance_stmt = $conn->prepare($balance_query);
        $balance_stmt->bind_param("i", $rider_id);
        $balance_stmt->execute();
        $balance_result = $balance_stmt->get_result();
        $rider_data = $balance_result->fetch_assoc();
        $balance_stmt->close();
        
        $current_balance = $rider_data['topup_balance'];
        
        // Adjust balance based on transaction type
        switch ($transaction_type) {
            case 'Additional Top-up':
            case 'Refund Top-up':
                $new_balance = $current_balance + $amount;
                break;
            case 'Commission Deduction':
            case 'Top-up Withdrawal':
                $new_balance = $current_balance - $amount;
                break;
            default:
                $new_balance = $current_balance;
        }
        
        // Update rider's balance
        $update_query = "UPDATE triders SET topup_balance = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("di", $new_balance, $rider_id);
        $update_stmt->execute();
        $update_stmt->close();
        
        $_SESSION['success_message'] = "Top-up ledger entry added successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to add top-up ledger entry: " . $conn->error;
    }
    
    $stmt->close();
    
    // Redirect back to the rider profile page
    header("Location: delivery_rider.php");
    exit();
} else {
    $_SESSION['error_message'] = "Invalid request method.";
    header("Location: delivery_rider.php");
    exit();
}
?> 