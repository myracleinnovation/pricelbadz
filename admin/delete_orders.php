<?php
session_start();
include '../config/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_number = $_POST['order_number'];
    $order_type = $_POST['order_type'];

    // Determine which table to delete from based on order type
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

    // Delete the order
    $query = "DELETE FROM $table WHERE order_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $order_number);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Order successfully deleted.";
    } else {
        $_SESSION['error_message'] = "Error deleting order: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: customer_orders.php");
    exit();
} else {
    header("Location: customer_orders.php");
    exit();
}
?>
