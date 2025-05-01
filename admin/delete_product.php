<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include '../config/connect.php';
    
    $product_id = $_POST['product_id'];
    
    // Get the product image path before deleting
    $stmt = $conn->prepare('SELECT image_path FROM tmerchant_products WHERE id = ?');
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    // Delete the product
    $stmt = $conn->prepare('DELETE FROM tmerchant_products WHERE id = ?');
    $stmt->bind_param('i', $product_id);
    
    if ($stmt->execute()) {
        // Delete the product image file
        if ($product && $product['image_path']) {
            $image_path = '../public/img/' . $product['image_path'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        $_SESSION['success'] = 'Product deleted successfully.';
    } else {
        $_SESSION['error'] = 'Error deleting product: ' . $conn->error;
    }
    
    $stmt->close();
    header('Location: merchant.php');
    exit();
}

// If accessed directly without POST data, redirect to merchant page
header('Location: merchant.php');
exit(); 