<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include '../config/connect.php';
    
    // Get product details from form
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    
    // Handle product image upload if a new image is provided
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $image_file = $_FILES['product_image'];
        $image_name = time() . '_' . basename($image_file['name']);
        $image_target = '../public/img/' . $image_name;
        
        // Check if file is an actual image
        $check = getimagesize($image_file['tmp_name']);
        if ($check !== false) {
            // Upload the file
            if (move_uploaded_file($image_file['tmp_name'], $image_target)) {
                // Update product with new image
                $stmt = $conn->prepare('UPDATE tmerchant_products SET name = ?, description = ?, price = ?, image_path = ? WHERE id = ?');
                $stmt->bind_param('ssdsi', $name, $description, $price, $image_name, $product_id);
            } else {
                $_SESSION['error'] = 'Sorry, there was an error uploading the product image.';
                header('Location: merchant.php');
                exit();
            }
        } else {
            $_SESSION['error'] = 'File is not an image.';
            header('Location: merchant.php');
            exit();
        }
    } else {
        // Update product without changing the image
        $stmt = $conn->prepare('UPDATE tmerchant_products SET name = ?, description = ?, price = ? WHERE id = ?');
        $stmt->bind_param('ssdi', $name, $description, $price, $product_id);
    }
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Product updated successfully.';
    } else {
        $_SESSION['error'] = 'Error updating product: ' . $conn->error;
    }
    
    $stmt->close();
    header('Location: merchant.php');
    exit();
}

// If accessed directly without POST data, redirect to merchant page
header('Location: merchant.php');
exit(); 