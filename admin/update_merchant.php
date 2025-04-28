<?php
session_start();
include '../includes/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $merchant_id = $_POST['merchant_id'];
    $merchant_name = $_POST['merchant_name'];
    $merchant_description = $_POST['merchant_description'];
    
    // Handle logo update if a new logo is uploaded
    $logo_update = "";
    if (isset($_FILES['merchant_logo']) && $_FILES['merchant_logo']['size'] > 0) {
        $logo = $_FILES['merchant_logo'];
        $logo_name = time() . '_' . $logo['name'];
        $logo_tmp = $logo['tmp_name'];
        $logo_destination = '../public/img/' . $logo_name;
        
        // Move uploaded logo
        if (move_uploaded_file($logo_tmp, $logo_destination)) {
            // Get old logo to delete it
            $old_logo_query = "SELECT merchant_logo FROM tmerchants WHERE id = ?";
            $old_logo_stmt = $conn->prepare($old_logo_query);
            $old_logo_stmt->bind_param("i", $merchant_id);
            $old_logo_stmt->execute();
            $old_logo_result = $old_logo_stmt->get_result();
            $old_logo_row = $old_logo_result->fetch_assoc();
            
            // Delete old logo if it exists
            if ($old_logo_row && file_exists('../public/img/' . $old_logo_row['merchant_logo'])) {
                unlink('../public/img/' . $old_logo_row['merchant_logo']);
            }
            
            $logo_update = ", merchant_logo = ?";
        }
    }
    
    // Update merchant details
    $update_query = "UPDATE tmerchants SET merchant_name = ?, merchant_description = ?" . $logo_update . " WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    
    if ($logo_update) {
        $update_stmt->bind_param("sssi", $merchant_name, $merchant_description, $logo_name, $merchant_id);
    } else {
        $update_stmt->bind_param("ssi", $merchant_name, $merchant_description, $merchant_id);
    }
    
    $update_stmt->execute();
    
    // Handle additional images deletion
    if (isset($_POST['delete_images']) && is_array($_POST['delete_images'])) {
        foreach ($_POST['delete_images'] as $image_id) {
            // Get image path before deleting
            $image_query = "SELECT image_path FROM tmerchant_images WHERE id = ? AND merchant_id = ?";
            $image_stmt = $conn->prepare($image_query);
            $image_stmt->bind_param("ii", $image_id, $merchant_id);
            $image_stmt->execute();
            $image_result = $image_stmt->get_result();
            
            if ($image_row = $image_result->fetch_assoc()) {
                // Delete the image file
                $image_path = '../public/img/' . $image_row['image_path'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
                
                // Delete from database
                $delete_query = "DELETE FROM tmerchant_images WHERE id = ? AND merchant_id = ?";
                $delete_stmt = $conn->prepare($delete_query);
                $delete_stmt->bind_param("ii", $image_id, $merchant_id);
                $delete_stmt->execute();
            }
        }
    }
    
    // Handle new additional images upload
    if (isset($_FILES['additional_images']) && !empty($_FILES['additional_images']['name'][0])) {
        $files = $_FILES['additional_images'];
        $file_count = count($files['name']);
        
        for ($i = 0; $i < $file_count; $i++) {
            if ($files['size'][$i] > 0) {
                $file_name = time() . '_' . $i . '_' . $files['name'][$i];
                $file_tmp = $files['tmp_name'][$i];
                $file_destination = '../public/img/' . $file_name;
                
                if (move_uploaded_file($file_tmp, $file_destination)) {
                    // Insert into database
                    $insert_query = "INSERT INTO tmerchant_images (merchant_id, image_path, image_description, display_order) VALUES (?, ?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_query);
                    $description = "Additional image for " . $merchant_name;
                    $display_order = $i + 1; // Simple ordering
                    $insert_stmt->bind_param("issi", $merchant_id, $file_name, $description, $display_order);
                    $insert_stmt->execute();
                }
            }
        }
    }
    
    $_SESSION['success'] = "Merchant updated successfully!";
    header("Location: merchant.php");
    exit();
}
?> 