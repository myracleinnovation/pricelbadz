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
    $rider_id = isset($_POST['rider_id']) ? $_POST['rider_id'] : '';
    $new_status = isset($_POST['new_status']) ? $_POST['new_status'] : '';
    
    // Validate data
    if (empty($rider_id) || empty($new_status)) {
        $_SESSION['error_message'] = "Rider ID and new status are required.";
        header("Location: delivery_rider.php");
        exit();
    }
    
    // Update the rider status in the database
    $update_query = "UPDATE triders SET rider_status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $new_status, $rider_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Rider status updated successfully.";
    } else {
        $_SESSION['error_message'] = "Error updating rider status: " . $conn->error;
    }
    
    $stmt->close();
} else {
    $_SESSION['error_message'] = "Invalid request method.";
}

// Redirect back to delivery rider page
header("Location: delivery_rider.php");
exit();
?>