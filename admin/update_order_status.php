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
        case 'PABILI':
            $table = 'tpabili_orders';
            break;
        case 'PAANGKAS':
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

    // Update the order status
    $sql = "UPDATE $table SET order_status = ? WHERE order_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $new_status, $order_number);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Order status updated successfully';
    } else {
        $_SESSION['error_message'] = 'Failed to update order status';
    }

    $stmt->close();
    mysqli_close($conn);
} else {
    $_SESSION['error_message'] = "Invalid request method.";
}

// Redirect back to customer orders page
header("Location: customer_orders.php");
exit();
?> 