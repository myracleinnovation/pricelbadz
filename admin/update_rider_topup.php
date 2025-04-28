<?php
include './header.php';
include '../config/connect.php';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get rider details from form
    $rider_id = $_POST['rider_id'];
    $new_balance = $_POST['new_balance'];
    
    // Validate input
    if (!is_numeric($new_balance) || $new_balance < 0) {
        $_SESSION['error_message'] = "Invalid balance amount. Please enter a valid number.";
        header("Location: delivery_rider.php");
        exit();
    }
    
    // Update rider's top-up balance in database
    $stmt = $conn->prepare("UPDATE triders SET topup_balance = ? WHERE id = ?");
    $stmt->bind_param("di", $new_balance, $rider_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Rider's top-up balance updated successfully.";
    } else {
        $_SESSION['error_message'] = "Error updating rider's top-up balance: " . $conn->error;
    }
    
    $stmt->close();
    header("Location: delivery_rider.php");
    exit();
} else {
    // If not POST request, redirect to delivery rider page
    header("Location: delivery_rider.php");
    exit();
}
?> 