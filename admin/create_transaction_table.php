<?php
    include '../config/connect.php';

// Create rider_transactions table
$sql = "CREATE TABLE IF NOT EXISTS rider_transactions (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    rider_id INT(11) NOT NULL,
    transaction_type ENUM('TOP_UP', 'WITHDRAWAL') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    previous_balance DECIMAL(10,2) NOT NULL,
    new_balance DECIMAL(10,2) NOT NULL,
    notes TEXT,
    created_by INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rider_id) REFERENCES triders(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "Table rider_transactions created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?> 