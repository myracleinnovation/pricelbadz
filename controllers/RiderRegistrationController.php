<?php
function createRider($conn, $first_name, $middle_name, $last_name, $license_number, $vehicle_type, $vehicle_cor, $vehicle_plate_number, $topup_balance = 0.0, $rider_status = 'Active')
{
    try {
        $sql = "INSERT INTO triders (
            first_name, middle_name, last_name,
            license_number, vehicle_type, vehicle_cor,
            vehicle_plate_number, topup_balance, rider_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql) ?? throw new Exception('Prepare failed: ' . $conn->error);
        $stmt->bind_param('sssssssss', $first_name, $middle_name, $last_name, $license_number, $vehicle_type, $vehicle_cor, $vehicle_plate_number, $topup_balance, $rider_status);

        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }

        $stmt->close();
        $_SESSION['swal'] = ['icon' => 'success', 'title' => 'Success!', 'text' => 'Rider registered successfully.'];
        return true;
    } catch (Exception $e) {
        $_SESSION['swal'] = ['icon' => 'error', 'title' => 'Error!', 'text' => $e->getMessage()];
        return false;
    }
}
?>