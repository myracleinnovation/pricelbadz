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
$first_name = trim(htmlspecialchars($_POST['first_name'] ?? '', ENT_QUOTES, 'UTF-8'));
$middle_name = trim(htmlspecialchars($_POST['middle_name'] ?? '', ENT_QUOTES, 'UTF-8'));
$last_name = trim(htmlspecialchars($_POST['last_name'] ?? '', ENT_QUOTES, 'UTF-8'));
$license_number = trim(htmlspecialchars($_POST['license_number'] ?? '', ENT_QUOTES, 'UTF-8'));
$vehicle_type = trim(htmlspecialchars($_POST['vehicle_type'] ?? '', ENT_QUOTES, 'UTF-8'));
$vehicle_plate_number = trim(htmlspecialchars($_POST['vehicle_plate_number'] ?? '', ENT_QUOTES, 'UTF-8'));
$vehicle_cor = trim(htmlspecialchars($_POST['vehicle_cor'] ?? '', ENT_QUOTES, 'UTF-8'));
$rider_status = trim(htmlspecialchars($_POST['rider_status'] ?? '', ENT_QUOTES, 'UTF-8'));

// Additional validation
if (!$rider_id || empty($first_name) || empty($last_name) || empty($license_number) || 
    empty($vehicle_type) || empty($vehicle_plate_number) || empty($vehicle_cor) || empty($rider_status)) {
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