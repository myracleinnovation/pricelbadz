<?php
session_start();
include '../includes/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rider_id = $_POST['rider_id'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $license_number = $_POST['license_number'];
    $vehicle_type = $_POST['vehicle_type'];
    $vehicle_cor = $_POST['vehicle_cor'];
    $vehicle_plate_number = $_POST['vehicle_plate_number'];

    // Update rider details in the database
    $sql = "UPDATE delivery_riders SET 
            first_name = ?, 
            middle_name = ?, 
            last_name = ?, 
            license_number = ?, 
            vehicle_type = ?, 
            vehicle_cor = ?, 
            vehicle_plate_number = ? 
            WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", 
        $first_name, 
        $middle_name, 
        $last_name, 
        $license_number, 
        $vehicle_type, 
        $vehicle_cor, 
        $vehicle_plate_number, 
        $rider_id
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Rider details updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating rider details: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

    // Redirect back to the delivery riders page
    header("Location: delivery_rider.php");
    exit();
}
?> 