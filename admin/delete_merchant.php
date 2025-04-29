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
    header("Location: merchant.php");
    exit();
}

// Get merchant ID
$merchant_id = filter_input(INPUT_POST, 'merchant_id', FILTER_SANITIZE_NUMBER_INT);

if (!$merchant_id) {
    $_SESSION['error_message'] = "Invalid merchant ID!";
    header("Location: merchant.php");
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Get merchant details for file deletion
    $merchant_query = "SELECT merchant_logo FROM tmerchants WHERE id = ?";
    $merchant_stmt = $conn->prepare($merchant_query);
    $merchant_stmt->bind_param("i", $merchant_id);
    $merchant_stmt->execute();
    $merchant_result = $merchant_stmt->get_result();
    
    if ($merchant_result->num_rows === 0) {
        throw new Exception("Merchant not found!");
    }
    
    $merchant_data = $merchant_result->fetch_assoc();
    $logo_path = $merchant_data['merchant_logo'];
    
    // Get additional images
    $images_query = "SELECT image_path FROM tmerchant_images WHERE merchant_id = ?";
    $images_stmt = $conn->prepare($images_query);
    $images_stmt->bind_param("i", $merchant_id);
    $images_stmt->execute();
    $images_result = $images_stmt->get_result();
    
    $image_paths = [];
    while ($image = $images_result->fetch_assoc()) {
        $image_paths[] = $image['image_path'];
    }
    
    // Get product images
    $products_query = "SELECT image_path FROM tmerchant_products WHERE merchant_id = ?";
    $products_stmt = $conn->prepare($products_query);
    $products_stmt->bind_param("i", $merchant_id);
    $products_stmt->execute();
    $products_result = $products_stmt->get_result();
    
    while ($product = $products_result->fetch_assoc()) {
        $image_paths[] = $product['image_path'];
    }
    
    // Delete merchant images from database
    $delete_images_query = "DELETE FROM tmerchant_images WHERE merchant_id = ?";
    $delete_images_stmt = $conn->prepare($delete_images_query);
    $delete_images_stmt->bind_param("i", $merchant_id);
    $delete_images_stmt->execute();
    
    // Delete merchant products from database
    $delete_products_query = "DELETE FROM tmerchant_products WHERE merchant_id = ?";
    $delete_products_stmt = $conn->prepare($delete_products_query);
    $delete_products_stmt->bind_param("i", $merchant_id);
    $delete_products_stmt->execute();
    
    // Delete merchant from database
    $delete_merchant_query = "DELETE FROM tmerchants WHERE id = ?";
    $delete_merchant_stmt = $conn->prepare($delete_merchant_query);
    $delete_merchant_stmt->bind_param("i", $merchant_id);
    $delete_merchant_stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Delete files from server
    $base_path = "../public/img/";
    
    // Delete logo
    if (!empty($logo_path) && file_exists($base_path . $logo_path)) {
        unlink($base_path . $logo_path);
    }
    
    // Delete additional images
    foreach ($image_paths as $path) {
        if (!empty($path) && file_exists($base_path . $path)) {
            unlink($base_path . $path);
        }
    }
    
    $_SESSION['success_message'] = "Merchant and all associated data deleted successfully!";
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
} finally {
    // Close all statements
    if (isset($merchant_stmt)) $merchant_stmt->close();
    if (isset($images_stmt)) $images_stmt->close();
    if (isset($products_stmt)) $products_stmt->close();
    if (isset($delete_images_stmt)) $delete_images_stmt->close();
    if (isset($delete_products_stmt)) $delete_products_stmt->close();
    if (isset($delete_merchant_stmt)) $delete_merchant_stmt->close();
    
    header("Location: merchant.php");
    exit();
}
?> 