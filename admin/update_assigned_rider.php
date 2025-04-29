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
    $order_number = $_POST['order_number'];
    $order_type = $_POST['order_type'];
    $assigned_rider = $_POST['assigned_rider'];
    
    // Get the current admin username from session
    $assigned_by = isset($_SESSION['username']) ? $_SESSION['username'] : 'System';

    // Determine which table to update based on order type
    $table = '';
    switch ($order_type) {
        case 'PABILI/PASUYO':
            $table = 'tpabili_orders';
            break;
        case 'PAHATID/PASUNDO':
            $table = 'tpaangkas_orders';
            break;
        case 'PADALA':
            $table = 'tpadala_orders';
            break;
        default:
            $_SESSION['error_message'] = 'Invalid order type';
            header('Location: customer_orders.php');
            exit;
    }

    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get the current order details including commission
        $order_query = "SELECT commission FROM $table WHERE order_number = ?";
        $order_stmt = $conn->prepare($order_query);
        $order_stmt->bind_param("s", $order_number);
        $order_stmt->execute();
        $order_result = $order_stmt->get_result();
        $order_data = $order_result->fetch_assoc();
        $order_stmt->close();
        
        $commission = $order_data['commission'];

        // Update the assigned rider with timestamp and author, and set status to On-Going
        $update_query = "UPDATE $table 
                        SET assigned_rider = ?, 
                            rider_assigned_by = ?, 
                            rider_assigned_at = CURRENT_TIMESTAMP,
                            order_status = 'On-Going',
                            status_changed_at = CURRENT_TIMESTAMP,
                            status_changed_by = ?
                        WHERE order_number = ?";
        $update_stmt = $conn->prepare($update_query);
        
        if (empty($assigned_rider)) {
            // If no rider is selected, set to NULL
            $update_stmt->bind_param('ssss', $assigned_rider, $assigned_by, $assigned_by, $order_number);
        } else {
            $update_stmt->bind_param('ssss', $assigned_rider, $assigned_by, $assigned_by, $order_number);
            
            // Get rider's current balance
            $rider_query = "SELECT id, topup_balance FROM triders WHERE id = ?";
            $rider_stmt = $conn->prepare($rider_query);
            $rider_stmt->bind_param("i", $assigned_rider);
            $rider_stmt->execute();
            $rider_result = $rider_stmt->get_result();
            $rider_data = $rider_result->fetch_assoc();
            $rider_stmt->close();
            
            if ($rider_data) {
                $rider_id = $rider_data['id'];
                $current_balance = $rider_data['topup_balance'];
                $new_balance = $current_balance - $commission; // Deduct the commission
                
                // Update rider's top-up balance
                $balance_query = "UPDATE triders SET topup_balance = ? WHERE id = ?";
                $balance_stmt = $conn->prepare($balance_query);
                $balance_stmt->bind_param("di", $new_balance, $rider_id);
                $balance_stmt->execute();
                $balance_stmt->close();
                
                // Add entry to top-up ledger for commission deduction
                $ledger_query = "INSERT INTO trider_topup_ledger (rider_id, transaction_type, previous_balance, amount, current_balance, order_number, processed_by, notes) 
                                VALUES (?, 'Commission Deduction', ?, ?, ?, ?, ?, ?)";
                $ledger_stmt = $conn->prepare($ledger_query);
                $transaction_note = "Commission deducted for order #$order_number (Rider assigned and status changed to On-Going)";
                $ledger_stmt->bind_param("idddsss", $rider_id, $current_balance, $commission, $new_balance, $order_number, $assigned_by, $transaction_note);
                $ledger_stmt->execute();
                $ledger_stmt->close();
                
                $_SESSION['success_message'] = "Rider assigned successfully. Order status updated to On-Going. Commission of ₱" . number_format($commission, 2) . " deducted from rider's balance.";
            }
        }
        
        $update_stmt->execute();
        $update_stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        if (empty($assigned_rider)) {
            $_SESSION['success_message'] = "Rider assignment removed successfully.";
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "Invalid request method.";
}

// Redirect back to customer orders page
header("Location: customer_orders.php");
exit();
?> 