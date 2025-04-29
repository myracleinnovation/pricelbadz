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
    // Get the order details
    $order_number = $_POST['order_number'];
    $order_type = $_POST['order_type'];
    $service_fee = (float)$_POST['service_fee'];
    $commission = (float)$_POST['commission'];

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

    // Update the service fee and commission
    $update_query = "UPDATE $table SET service_fee = ?, commission = ? WHERE order_number = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('dds', $service_fee, $commission, $order_number);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Service fee and commission updated successfully.";
    } else {
        $_SESSION['error_message'] = "Error updating service fee and commission: " . $conn->error;
    }

    $stmt->close();
} else {
    $_SESSION['error_message'] = "Invalid request method.";
}

// Redirect back to customer orders page
header("Location: customer_orders.php");
exit();
?> 