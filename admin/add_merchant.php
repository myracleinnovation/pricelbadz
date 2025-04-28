<?php
include './header.php';
include '../config/connect.php';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get merchant details from form
    $merchant_name = $_POST['merchant_name'];
    $merchant_description = $_POST['merchant_description'];
    
    // Handle logo upload
    $merchant_logo = '';
    if (isset($_FILES['merchant_logo']) && $_FILES['merchant_logo']['error'] == 0) {
        $logo_file = $_FILES['merchant_logo'];
        $logo_name = time() . '_' . basename($logo_file['name']);
        $logo_target = "../public/img/" . $logo_name;
        
        // Check if file is an actual image
        $check = getimagesize($logo_file["tmp_name"]);
        if ($check !== false) {
            // Upload the file
            if (move_uploaded_file($logo_file["tmp_name"], $logo_target)) {
                $merchant_logo = $logo_name;
            } else {
                $_SESSION['error'] = "Sorry, there was an error uploading the logo.";
                header("Location: merchant.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "File is not an image.";
            header("Location: merchant.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Logo is required.";
        header("Location: merchant.php");
        exit();
    }
    
    // Insert merchant into database
    $stmt = $conn->prepare("INSERT INTO tmerchants (merchant_name, merchant_description, merchant_logo) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $merchant_name, $merchant_description, $merchant_logo);
    
    if ($stmt->execute()) {
        $merchant_id = $conn->insert_id;
        
        // Handle additional images if any
        if (isset($_FILES['additional_images']) && !empty($_FILES['additional_images']['name'][0])) {
            $additional_images = $_FILES['additional_images'];
            $total_images = count($additional_images['name']);
            
            for ($i = 0; $i < $total_images; $i++) {
                if ($additional_images['error'][$i] == 0) {
                    $image_name = time() . '_' . $i . '_' . basename($additional_images['name'][$i]);
                    $image_target = "../public/img/" . $image_name;
                    
                    // Check if file is an actual image
                    $check = getimagesize($additional_images["tmp_name"][$i]);
                    if ($check !== false) {
                        // Upload the file
                        if (move_uploaded_file($additional_images["tmp_name"][$i], $image_target)) {
                            // Insert image record into database
                            $image_description = "Additional image for " . $merchant_name;
                            $display_order = $i + 1;
                            
                            $img_stmt = $conn->prepare("INSERT INTO tmerchant_images (merchant_id, image_path, image_description, display_order) VALUES (?, ?, ?, ?)");
                            $img_stmt->bind_param("issi", $merchant_id, $image_name, $image_description, $display_order);
                            $img_stmt->execute();
                        }
                    }
                }
            }
        }
        
        $_SESSION['success'] = "Merchant added successfully.";
    } else {
        $_SESSION['error'] = "Error adding merchant: " . $conn->error;
    }
    
    $stmt->close();
    header("Location: merchant.php");
    exit();
} else {
    // If not POST request, redirect to merchant page
    header("Location: merchant.php");
    exit();
}
?> 