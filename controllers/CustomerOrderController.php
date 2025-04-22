<?php
    function createCustomerOrder($conn, $name, $contact_number, $pickup_address, $dropoff_address, $dropoff_contact_person, $dropoff_contact_number, $remarks) {
        $stmt = $conn->prepare("INSERT INTO tcustomer_order (name, contact_number, pickup_address, dropoff_address, dropoff_contact_person, dropoff_contact_number, remarks) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $contact_number, $pickup_address, $dropoff_address, $dropoff_contact_person, $dropoff_contact_number, $remarks);
        $stmt->execute();
        $stmt->close();
    }
?>