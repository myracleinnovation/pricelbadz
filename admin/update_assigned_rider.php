<?php
session_start();
include '../config/connect.php';

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the order details
    $order_number = $_POST['order_number'];
    $order_type = $_POST['order_type'];
    $assigned_rider = $_POST['assigned_rider'];

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
            $_SESSION['error_message'] = "Invalid order type.";
            header("Location: customer_orders.php");
            exit();
    }

    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get the order details including commission
        $order_query = "SELECT commission FROM $table WHERE order_number = ?";
        $order_stmt = $conn->prepare($order_query);
        $order_stmt->bind_param("s", $order_number);
        $order_stmt->execute();
        $order_result = $order_stmt->get_result();
        $order_data = $order_result->fetch_assoc();
        $commission = $order_data['commission'];
        $order_stmt->close();
        
        // Update the assigned rider and set order status to On-Going
        $update_query = "UPDATE $table SET assigned_rider = ?, order_status = 'On-Going' WHERE order_number = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ss", $assigned_rider, $order_number);
        $update_stmt->execute();
        $update_stmt->close();
        
        // If a rider is assigned, deduct the commission from their top-up balance
        if (!empty($assigned_rider)) {
            // Get rider's current balance
            $rider_query = "SELECT id, topup_balance FROM triders WHERE CONCAT(first_name, ' ', last_name) = ?";
            $rider_stmt = $conn->prepare($rider_query);
            $rider_stmt->bind_param("s", $assigned_rider);
            $rider_stmt->execute();
            $rider_result = $rider_stmt->get_result();
            $rider_data = $rider_result->fetch_assoc();
            $rider_stmt->close();
            
            if ($rider_data) {
                $rider_id = $rider_data['id'];
                $current_balance = $rider_data['topup_balance'];
                $new_balance = $current_balance - $commission;
                
                // Update rider's top-up balance
                $balance_query = "UPDATE triders SET topup_balance = ? WHERE id = ?";
                $balance_stmt = $conn->prepare($balance_query);
                $balance_stmt->bind_param("di", $new_balance, $rider_id);
                $balance_stmt->execute();
                $balance_stmt->close();
                
                $_SESSION['success_message'] = "Rider assigned successfully. Order status set to On-Going. Commission of ₱" . number_format($commission, 2) . " deducted from rider's balance.";
            } else {
                throw new Exception("Rider not found in the database.");
            }
        } else {
            $_SESSION['success_message'] = "Rider assignment removed. Order status remains unchanged.";
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

// Redirect back to the customer orders page
header("Location: customer_orders.php");
exit();
?> 