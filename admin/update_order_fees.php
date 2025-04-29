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
    $service_fee = (float) $_POST['service_fee'];
    $commission = (float) $_POST['commission'];

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

    // Update the order fees
    $sql = "UPDATE $table SET service_fee = ?, commission = ? WHERE order_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('dds', $service_fee, $commission, $order_number);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Order fees updated successfully';
    } else {
        $_SESSION['error_message'] = 'Failed to update order fees';
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