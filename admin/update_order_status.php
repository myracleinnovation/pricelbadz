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
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $order_number = isset($_POST['order_number']) ? $_POST['order_number'] : '';
    $new_status = isset($_POST['new_status']) ? $_POST['new_status'] : '';
    
    // Validate data
    if (empty($order_number) || empty($new_status)) {
        $_SESSION['error_message'] = "Order number and new status are required.";
        header("Location: customer_orders.php");
        exit();
    }
    
    // Update the order status in the database
    $update_query = "UPDATE tcustomer_order SET order_status = ? WHERE order_number = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ss", $new_status, $order_number);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Order status updated successfully.";
    } else {
        $_SESSION['error_message'] = "Error updating order status: " . $conn->error;
    }
    
    $stmt->close();
} else {
    $_SESSION['error_message'] = "Invalid request method.";
}

// Redirect back to customer orders page
header("Location: customer_orders.php");
exit();
?> 