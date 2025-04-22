<?php
    function createRider($conn, $first_name, $middle_name, $surname, $license_number, $vehicle_plate_number) {
        $stmt = $conn->prepare("INSERT INTO triders (first_name, middle_name, surname, license_number, vehicle_plate_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $first_name, $middle_name, $surname, $license_number, $vehicle_plate_number);
        $stmt->execute();
        $stmt->close();
    }
?>