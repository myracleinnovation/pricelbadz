<?php
// Start session and include files at the very top
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include '../config/connect.php';
    
    // Get product details from form
    $merchant_id = $_POST['merchant_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    // Handle product image upload
    $product_image = '';
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $image_file = $_FILES['product_image'];
        $image_name = time() . '_' . basename($image_file['name']);
        $image_target = '../public/img/' . $image_name;

        // Check if file is an actual image
        $check = getimagesize($image_file['tmp_name']);
        if ($check !== false) {
            // Upload the file
            if (move_uploaded_file($image_file['tmp_name'], $image_target)) {
                $product_image = $image_name;
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
        $_SESSION['error'] = 'Please select a product image.';
        header('Location: merchant.php');
        exit();
    }

    // Insert product into database
    $stmt = $conn->prepare('INSERT INTO tmerchant_products (merchant_id, name, description, price, image_path, is_active) VALUES (?, ?, ?, ?, ?, 1)');
    $stmt->bind_param('issds', $merchant_id, $name, $description, $price, $product_image);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Product added successfully.';
    } else {
        $_SESSION['error'] = 'Error adding product: ' . $conn->error;
    }

    $stmt->close();
    header('Location: merchant.php');
    exit();
}

// If accessed directly without POST data, redirect to merchant page
header('Location: merchant.php');
exit();
?>