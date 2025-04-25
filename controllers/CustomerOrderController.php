<?php
function createCustomerOrder($conn, $customer_name, $contact_number, $merchant_name, $pickup_address, $pickup_note, $order_description, $quantity, $estimated_price, $dropoff_address, $dropoff_note, $assigned_rider = null, $order_status = 'Pending')
{
    try {
        $order_number = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 16));

        $sql = "INSERT INTO tcustomer_order (
            order_number, customer_name, contact_number, merchant_name,
            pickup_address, pickup_note, order_description, quantity,
            estimated_price, dropoff_address, dropoff_note,
            assigned_rider, order_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql) ?? throw new Exception('Prepare failed: ' . $conn->error);
        $stmt->bind_param('sssssssiddsss', $order_number, $customer_name, $contact_number, $merchant_name, $pickup_address, $pickup_note, $order_description, $quantity, $estimated_price, $dropoff_address, $dropoff_note, $assigned_rider, $order_status);

        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }

        $stmt->close();
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Order placed successfully. Order Number: " .
            $order_number .
            "',
                confirmButtonColor: '#0E76BC'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'home.php';
                }
            });
        </script>";
        return $order_number;
    } catch (Exception $e) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to place order: " .
            $e->getMessage() .
            "',
                confirmButtonColor: '#0E76BC'
            });
        </script>";
        return false;
    }
}
?>
