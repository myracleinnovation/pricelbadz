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

    // Update the assigned rider
    $query = "UPDATE $table SET assigned_rider = ? WHERE order_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $assigned_rider, $order_number);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Assigned rider updated successfully.";
    } else {
        $_SESSION['error_message'] = "Error updating assigned rider: " . $conn->error;
    }
    
    $stmt->close();
} else {
    $_SESSION['error_message'] = "Invalid request method.";
}

// Redirect back to the customer orders page
header("Location: customer_orders.php");
exit();
?> 