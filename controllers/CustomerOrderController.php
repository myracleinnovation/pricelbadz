<?php
function createCustomerOrder($conn, $customer_name, $contact_number, $merchant_name, $pickup_address, $pickup_note, $order_description, $quantity, $estimated_price, $dropoff_address, $dropoff_note, $assigned_rider = null, $order_status = 'Pending')
{
    try {
        $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        
        $sql = "INSERT INTO tcustomer_order (
            order_number, customer_name, contact_number, merchant_name,
            pickup_address, pickup_note, order_description, quantity,
            estimated_price, dropoff_address, dropoff_note,
            assigned_rider, order_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql) ?? throw new Exception('Prepare failed: ' . $conn->error);
        $stmt->bind_param('sssssssiddsss', 
            $order_number, $customer_name, $contact_number, $merchant_name,
            $pickup_address, $pickup_note, $order_description, $quantity,
            $estimated_price, $dropoff_address, $dropoff_note, $assigned_rider,
            $order_status
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }

        $stmt->close();
        $_SESSION['swal'] = ['icon' => 'success', 'title' => 'Success!', 'text' => 'Order placed successfully.'];
        return $order_number;
    } catch (Exception $e) {
        $_SESSION['swal'] = ['icon' => 'error', 'title' => 'Error!', 'text' => $e->getMessage()];
        return false;
    }
}
?>