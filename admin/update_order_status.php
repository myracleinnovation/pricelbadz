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
    $new_status = $_POST['order_status'];

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
        // Get the current order details including assigned rider and commission
        $order_query = "SELECT assigned_rider, commission, order_status FROM $table WHERE order_number = ?";
        $order_stmt = $conn->prepare($order_query);
        $order_stmt->bind_param("s", $order_number);
        $order_stmt->execute();
        $order_result = $order_stmt->get_result();
        $order_data = $order_result->fetch_assoc();
        $order_stmt->close();
        
        $assigned_rider = $order_data['assigned_rider'];
        $commission = $order_data['commission'];
        $current_status = $order_data['order_status'];
        
        // Get the current admin username from session
        $status_changed_by = isset($_SESSION['username']) ? $_SESSION['username'] : 'System';
        
        // Update the order status with timestamp and author
        $update_query = "UPDATE $table SET order_status = ?, status_changed_at = CURRENT_TIMESTAMP, status_changed_by = ? WHERE order_number = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param('sss', $new_status, $status_changed_by, $order_number);
        $update_stmt->execute();
        $update_stmt->close();
        
        // If changing from "On-Going" to "Pending" or "Cancelled" and there's an assigned rider
        if (($new_status === 'Pending' || $new_status === 'Cancelled') && 
            $current_status === 'On-Going' && 
            !empty($assigned_rider)) {
            
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
                $new_balance = $current_balance + $commission; // Return the commission
                
                // Update rider's top-up balance
                $balance_query = "UPDATE triders SET topup_balance = ? WHERE id = ?";
                $balance_stmt = $conn->prepare($balance_query);
                $balance_stmt->bind_param("di", $new_balance, $rider_id);
                $balance_stmt->execute();
                $balance_stmt->close();
                
                // Add entry to top-up ledger for refund
                $ledger_query = "INSERT INTO trider_topup_ledger (rider_id, transaction_type, amount, order_number, author, notes) 
                                VALUES (?, 'Refund Top-up', ?, ?, 'System', ?)";
                $ledger_stmt = $conn->prepare($ledger_query);
                $transaction_note = "Commission refunded for order #$order_number (Status changed from On-Going to $new_status)";
                $ledger_stmt->bind_param("idss", $rider_id, $commission, $order_number, $transaction_note);
                $ledger_stmt->execute();
                $ledger_stmt->close();
                
                $_SESSION['success_message'] = "Order status updated to $new_status. Commission of ₱" . number_format($commission, 2) . " returned to rider's balance.";
            } else {
                throw new Exception("Rider not found in the database.");
            }
        } 
        // If changing to "On-Going" and there's an assigned rider
        else if ($new_status === 'On-Going' && !empty($assigned_rider)) {
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
                
                // Only deduct commission if the order was not already in "On-Going" status
                if ($current_status !== 'On-Going') {
                    $new_balance = $current_balance - $commission; // Deduct the commission
                    
                    // Update rider's top-up balance
                    $balance_query = "UPDATE triders SET topup_balance = ? WHERE id = ?";
                    $balance_stmt = $conn->prepare($balance_query);
                    $balance_stmt->bind_param("di", $new_balance, $rider_id);
                    $balance_stmt->execute();
                    $balance_stmt->close();
                    
                    // Add entry to top-up ledger for commission deduction
                    $ledger_query = "INSERT INTO trider_topup_ledger (rider_id, transaction_type, amount, order_number, author, notes) 
                                    VALUES (?, 'Commission Deduction', ?, ?, 'System', ?)";
                    $ledger_stmt = $conn->prepare($ledger_query);
                    $transaction_note = "Commission deducted for order #$order_number (Status changed to On-Going)";
                    $ledger_stmt->bind_param("idss", $rider_id, $commission, $order_number, $transaction_note);
                    $ledger_stmt->execute();
                    $ledger_stmt->close();
                    
                    $_SESSION['success_message'] = "Order status updated to $new_status. Commission of ₱" . number_format($commission, 2) . " deducted from rider's balance.";
                } else {
                    $_SESSION['success_message'] = "Order status updated to $new_status.";
                }
            } else {
                throw new Exception("Rider not found in the database.");
            }
        } else {
            $_SESSION['success_message'] = "Order status updated to $new_status.";
        }
        
        // Commit transaction
        $conn->commit();
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