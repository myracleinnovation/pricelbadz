<?php
session_start();
include '../config/connect.php';

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the order details
    $order_number = $_POST['order_number'];
    $order_type = $_POST['order_type'];
    $assigned_rider_id = $_POST['assigned_rider'];
    
    // Initialize rider_id as NULL
    $rider_id = null;
    
    // If a rider ID is provided, use it directly
    if (!empty($assigned_rider_id)) {
        $rider_id = $assigned_rider_id;
        
        // Verify that the rider exists
        $rider_query = "SELECT id FROM triders WHERE id = ?";
        $rider_stmt = $conn->prepare($rider_query);
        $rider_stmt->bind_param('i', $rider_id);
        $rider_stmt->execute();
        $rider_result = $rider_stmt->get_result();
        
        if ($rider_result->num_rows === 0) {
            $_SESSION['error_message'] = "Rider not found in the database.";
            header("Location: customer_orders.php");
            exit();
        }
        
        $rider_stmt->close();
    }

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

    // Update the assigned rider
    $update_query = "UPDATE $table SET assigned_rider = ? WHERE order_number = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('is', $rider_id, $order_number);
    
    if ($stmt->execute()) {
        // If a rider is assigned, automatically update status to On-Going
        if (!empty($rider_id)) {
            $status_query = "UPDATE $table SET order_status = 'On-Going' WHERE order_number = ?";
            $status_stmt = $conn->prepare($status_query);
            $status_stmt->bind_param('s', $order_number);
            $status_stmt->execute();
            
            $_SESSION['success_message'] = "Rider assigned and order status updated to On-Going.";
        } else {
            $_SESSION['success_message'] = "Rider assignment updated.";
        }
    } else {
        $_SESSION['error_message'] = "Error updating rider assignment: " . $conn->error;
    }
    
    $stmt->close();
    if (isset($status_stmt)) {
        $status_stmt->close();
    }
} else {
    $_SESSION['error_message'] = "Invalid request method.";
}

// Redirect back to the customer orders page
header("Location: customer_orders.php");
exit();
?> 