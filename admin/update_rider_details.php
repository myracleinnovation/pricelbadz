<?php
session_start();
require_once '../config/connect.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    $_SESSION['error_message'] = "Unauthorized access!";
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Invalid request method!";
    header("Location: delivery_rider.php");
    exit();
}

// Validate and sanitize inputs
$rider_id = filter_input(INPUT_POST, 'rider_id', FILTER_SANITIZE_NUMBER_INT);
$first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
$middle_name = filter_input(INPUT_POST, 'middle_name', FILTER_SANITIZE_STRING);
$last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
$license_number = filter_input(INPUT_POST, 'license_number', FILTER_SANITIZE_STRING);
$vehicle_type = filter_input(INPUT_POST, 'vehicle_type', FILTER_SANITIZE_STRING);
$vehicle_plate_number = filter_input(INPUT_POST, 'vehicle_plate_number', FILTER_SANITIZE_STRING);
$vehicle_cor = filter_input(INPUT_POST, 'vehicle_cor', FILTER_SANITIZE_STRING);
$rider_status = filter_input(INPUT_POST, 'rider_status', FILTER_SANITIZE_STRING);

// Additional validation
if (!$rider_id || !$first_name || !$last_name || !$license_number || !$vehicle_type || !$vehicle_plate_number || !$vehicle_cor || !$rider_status) {
    $_SESSION['error_message'] = "Missing required fields!";
    header("Location: delivery_rider.php");
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Check if rider exists
    $check_stmt = $conn->prepare("SELECT id FROM triders WHERE id = ?");
    $check_stmt->bind_param("i", $rider_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Rider not found!");
    }

    // Update rider details
    $update_stmt = $conn->prepare("
        UPDATE triders 
        SET first_name = ?, 
            middle_name = ?, 
            last_name = ?, 
            license_number = ?, 
            vehicle_type = ?, 
            vehicle_plate_number = ?, 
            vehicle_cor = ?,
            rider_status = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    
    $update_stmt->bind_param(
        "ssssssssi",
        $first_name,
        $middle_name,
        $last_name,
        $license_number,
        $vehicle_type,
        $vehicle_plate_number,
        $vehicle_cor,
        $rider_status,
        $rider_id
    );

    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update rider details!");
    }

    // Commit transaction
    $conn->commit();
    
    $_SESSION['success_message'] = "Successfully updated rider details!";
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
} finally {
    // Close all statements
    if (isset($check_stmt)) $check_stmt->close();
    if (isset($update_stmt)) $update_stmt->close();
    
    header("Location: delivery_rider.php");
    exit();
}
?> 