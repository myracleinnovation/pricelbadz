<?php
include './header.php';
include '../config/connect.php';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get product details from form
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    
    // Start building the update query
    $update_fields = array();
    $param_types = "";
    $params = array();
    
    // Always update name, description, and price
    $update_fields[] = "name = ?";
    $update_fields[] = "description = ?";
    $update_fields[] = "price = ?";
    $param_types .= "ssd";
    $params[] = $name;
    $params[] = $description;
    $params[] = $price;
    
    // Handle product image upload if a new image was provided
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $image_file = $_FILES['product_image'];
        $image_name = time() . '_' . basename($image_file['name']);
        $image_target = "../public/img/" . $image_name;
        
        // Check if file is an actual image
        $check = getimagesize($image_file["tmp_name"]);
        if ($check !== false) {
            // Upload the file
            if (move_uploaded_file($image_file["tmp_name"], $image_target)) {
                // Get the old image path to delete it
                $old_image_query = "SELECT image_path FROM tmerchant_products WHERE id = ?";
                $old_image_stmt = $conn->prepare($old_image_query);
                $old_image_stmt->bind_param("i", $product_id);
                $old_image_stmt->execute();
                $old_image_result = $old_image_stmt->get_result();
                if ($old_image_row = $old_image_result->fetch_assoc()) {
                    $old_image_path = "../public/img/" . $old_image_row['image_path'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
                
                // Add image path to update query
                $update_fields[] = "image_path = ?";
                $param_types .= "s";
                $params[] = $image_name;
            } else {
                $_SESSION['error'] = "Sorry, there was an error uploading the product image.";
                header("Location: merchant.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "File is not an image.";
            header("Location: merchant.php");
            exit();
        }
    }
    
    // Add product_id to params array
    $param_types .= "i";
    $params[] = $product_id;
    
    // Build and execute the update query
    $update_query = "UPDATE tmerchant_products SET " . implode(", ", $update_fields) . " WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    
    // Create array with references for bind_param
    $refs = array();
    $refs[] = &$param_types;
    foreach($params as $key => $value) {
        $refs[] = &$params[$key];
    }
    call_user_func_array(array($stmt, 'bind_param'), $refs);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Product updated successfully.";
    } else {
        $_SESSION['error'] = "Error updating product: " . $conn->error;
    }
    
    $stmt->close();
    header("Location: merchant.php");
    exit();
}

// If accessed directly without POST data, redirect to merchant page
header("Location: merchant.php");
exit(); 