<?php
function createPabiliOrder($conn, $customer_name, $contact_number, $merchant_store_name, $order_description, $store_address, $pickup_note, $delivery_address, $delivery_note, $assigned_rider = null, $order_status = 'Pending', $service_fee = 0.00, $commission = 0.00)
{
    try {
        $order_number = 'PAB-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

        $sql = "INSERT INTO tpabili_orders (
            order_number, customer_name, contact_number, merchant_store_name,
            order_description, store_address, pickup_note, delivery_address, 
            delivery_note, assigned_rider, vehicle_type, order_status, service_fee, commission
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, ?)";

        $stmt = $conn->prepare($sql) ?? throw new Exception('Prepare failed: ' . $conn->error);
        $stmt->bind_param('sssssssssssdd', $order_number, $customer_name, $contact_number, $merchant_store_name, $order_description, $store_address, $pickup_note, $delivery_address, $delivery_note, $assigned_rider, $order_status, $service_fee, $commission);

        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }

        $stmt->close();
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Pabili/Pasuyo order placed successfully. Order Number: " .
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

function createPaangkasOrder($conn, $customer_name, $contact_number, $pickup_address, $vehicle_type, $pickup_note, $dropoff_address, $dropoff_note, $assigned_rider = null, $order_status = 'Pending', $service_fee = 0.00, $commission = 0.00)
{
    try {
        $order_number = 'PAA-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

        $sql = "INSERT INTO tpaangkas_orders (
            order_number, customer_name, contact_number, pickup_address,
            vehicle_type, pickup_note, dropoff_address, dropoff_note,
            assigned_rider, order_status, service_fee, commission
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql) ?? throw new Exception('Prepare failed: ' . $conn->error);
        $stmt->bind_param('ssssssssssdd', $order_number, $customer_name, $contact_number, $pickup_address, $vehicle_type, $pickup_note, $dropoff_address, $dropoff_note, $assigned_rider, $order_status, $service_fee, $commission);

        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }

        $stmt->close();
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Pahatid/Pasundo order placed successfully. Order Number: " .
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

function createPadalaOrder($conn, $customer_name, $contact_number, $pickup_address, $order_description, $pickup_note, $dropoff_address, $dropoff_note, $assigned_rider = null, $order_status = 'Pending', $service_fee = 0.00, $commission = 0.00)
{
    try {
        $order_number = 'PAD-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

        $sql = "INSERT INTO tpadala_orders (
            order_number, customer_name, contact_number, pickup_address,
            order_description, pickup_note, dropoff_address, dropoff_note,
            assigned_rider, vehicle_type, order_status, service_fee, commission
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, ?)";

        $stmt = $conn->prepare($sql) ?? throw new Exception('Prepare failed: ' . $conn->error);
        $stmt->bind_param('ssssssssssdd', $order_number, $customer_name, $contact_number, $pickup_address, $order_description, $pickup_note, $dropoff_address, $dropoff_note, $assigned_rider, $order_status, $service_fee, $commission);

        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }

        $stmt->close();
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Padala order placed successfully. Order Number: " .
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
