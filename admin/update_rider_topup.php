<?php
include './header.php';
include '../config/connect.php';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get rider details from form
    $rider_id = $_POST['rider_id'];
    $new_balance = $_POST['new_balance'];
    
    // Validate input
    if (!is_numeric($new_balance) || $new_balance < 0) {
        $_SESSION['error_message'] = "Invalid balance amount. Please enter a valid number.";
        header("Location: delivery_rider.php");
        exit();
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get current balance
        $current_balance_query = "SELECT topup_balance FROM triders WHERE id = ?";
        $current_balance_stmt = $conn->prepare($current_balance_query);
        $current_balance_stmt->bind_param("i", $rider_id);
        $current_balance_stmt->execute();
        $current_balance_result = $current_balance_stmt->get_result();
        $current_balance_data = $current_balance_result->fetch_assoc();
        $current_balance_stmt->close();
        
        $current_balance = $current_balance_data['topup_balance'];
        $difference = $new_balance - $current_balance;
        
        // Update rider's top-up balance in database
        $update_stmt = $conn->prepare("UPDATE triders SET topup_balance = ? WHERE id = ?");
        $update_stmt->bind_param("di", $new_balance, $rider_id);
        $update_stmt->execute();
        $update_stmt->close();
        
        // Add entry to top-up ledger
        if ($difference != 0) {
            $transaction_type = $difference > 0 ? 'Additional Top-up' : 'Top-up Withdrawal';
            $amount = abs($difference);
            
            // Get the current admin username from session
            $author = isset($_SESSION['username']) ? $_SESSION['username'] : 'System';
            
            $ledger_query = "INSERT INTO trider_topup_ledger (rider_id, transaction_type, amount, author, notes) 
                            VALUES (?, ?, ?, ?, ?)";
            $ledger_stmt = $conn->prepare($ledger_query);
            $notes = "Manual balance adjustment from ₱" . number_format($current_balance, 2) . " to ₱" . number_format($new_balance, 2);
            $ledger_stmt->bind_param("isdss", $rider_id, $transaction_type, $amount, $author, $notes);
            $ledger_stmt->execute();
            $ledger_stmt->close();
        }
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['success_message'] = "Rider's top-up balance updated successfully.";
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = "Error updating rider's top-up balance: " . $e->getMessage();
    }
    
    header("Location: delivery_rider.php");
    exit();
} else {
    // If not POST request, redirect to delivery rider page
    header("Location: delivery_rider.php");
    exit();
}
?> 