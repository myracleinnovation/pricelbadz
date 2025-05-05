<?php
function createRider($conn, $first_name, $middle_name, $last_name, $contact_number, $license_number, $vehicle_type, $vehicle_cor, $vehicle_plate_number, $topup_balance = 0.0, $rider_status = 'Active')
{
    try {
        $sql = "INSERT INTO triders (
            first_name, middle_name, last_name, contact_number,
            license_number, vehicle_type, vehicle_cor,
            vehicle_plate_number, topup_balance, rider_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql) ?? throw new Exception('Prepare failed: ' . $conn->error);
        $stmt->bind_param('ssssssssds', $first_name, $middle_name, $last_name, $contact_number, $license_number, $vehicle_type, $vehicle_cor, $vehicle_plate_number, $topup_balance, $rider_status);

        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }

        $stmt->close();
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Rider registered successfully.',
                confirmButtonColor: '#F26522'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'home.php';
                }
            });
        </script>";
        return true;
    } catch (Exception $e) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to register rider: " .
            $e->getMessage() .
            "',
                confirmButtonColor: '#F26522'
            });
        </script>";
        return false;
    }
}
?>
