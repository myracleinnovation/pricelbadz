<?php
session_start();
include '../config/connect.php';

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the order number and type
    $order_number = $_POST['order_number'] ?? '';
    $order_type = $_POST['order_type'] ?? '';
    $assigned_rider = $_POST['assigned_rider'] ?? '';
    
    // Validate inputs
    if (empty($order_number) || empty($order_type)) {
        $_SESSION['error_message'] = "Invalid order information.";
        header("Location: customer_orders.php");
        exit;
    }
    
    // Determine which table to update based on order type
    $table_name = '';
    switch ($order_type) {
        case 'PABILI/PASUYO':
            $table_name = 'tpabili_orders';
            break;
        case 'PAHATID/PASUNDO':
            $table_name = 'tpaangkas_orders';
            break;
        case 'PADALA':
            $table_name = 'tpadala_orders';
            break;
        default:
            $_SESSION['error_message'] = "Invalid order type.";
            header("Location: customer_orders.php");
            exit;
    }
    
    // If a rider is assigned, get their vehicle type
    $vehicle_type = null;
    if (!empty($assigned_rider)) {
        $rider_query = "SELECT vehicle_type FROM triders WHERE id = ?";
        $stmt = $conn->prepare($rider_query);
        $stmt->bind_param('i', $assigned_rider);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $rider_data = $result->fetch_assoc();
            $vehicle_type = $rider_data['vehicle_type'];
        }
    }
    
    // Update the order with the assigned rider and vehicle type
    if (!empty($assigned_rider)) {
        // For all order types, update both assigned_rider and vehicle_type
        $update_query = "UPDATE $table_name SET assigned_rider = ?, vehicle_type = ?, order_status = 'On-Going' WHERE order_number = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('iss', $assigned_rider, $vehicle_type, $order_number);
    } else {
        // If no rider is assigned, set assigned_rider to NULL and vehicle_type to NULL
        $update_query = "UPDATE $table_name SET assigned_rider = NULL, vehicle_type = NULL WHERE order_number = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('s', $order_number);
    }
    
    // Execute the update
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Assigned rider updated successfully.";
    } else {
        $_SESSION['error_message'] = "Error updating assigned rider: " . $conn->error;
    }
    
    // Redirect back to the orders page
    header("Location: customer_orders.php");
    exit;
} else {
    // If not a POST request, redirect to the orders page
    header("Location: customer_orders.php");
    exit;
}
?> 