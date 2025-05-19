<?php
session_start();
include '../config/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rider_id = $_POST['rider_id'];

    // Check if rider has any ongoing orders
    $check_query = "SELECT COUNT(*) as count FROM (
        SELECT assigned_rider FROM tpabili_orders WHERE order_status = 'On-Going' AND assigned_rider = ?
        UNION ALL
        SELECT assigned_rider FROM tpaangkas_orders WHERE order_status = 'On-Going' AND assigned_rider = ?
        UNION ALL
        SELECT assigned_rider FROM tpadala_orders WHERE order_status = 'On-Going' AND assigned_rider = ?
    ) AS ongoing_orders";

    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("iii", $rider_id, $rider_id, $rider_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_row = $check_result->fetch_assoc();

    if ($check_row['count'] > 0) {
        $_SESSION['error_message'] = "Cannot delete rider with ongoing orders.";
        header("Location: delivery_rider.php");
        exit();
    }

    // Delete the rider
    $query = "DELETE FROM triders WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $rider_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Rider successfully deleted.";
    } else {
        $_SESSION['error_message'] = "Error deleting rider: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: delivery_rider.php");
    exit();
} else {
    header("Location: delivery_rider.php");
    exit();
}
?>
