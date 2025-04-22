CREATE DATABASE IF NOT EXISTS pricelbadz;
USE pricelbadz;

-- Updated tcustomer_order table
CREATE TABLE tcustomer_order (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(255) NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    merchant_name VARCHAR(255) NOT NULL,
    pickup_address VARCHAR(255) NOT NULL,
    pickup_note TEXT,
    order_description TEXT,
    quantity INT DEFAULT 1,
    estimated_price DECIMAL(10, 2) DEFAULT 0.00,
    dropoff_address VARCHAR(255) NOT NULL,
    dropoff_note TEXT,
    assigned_rider VARCHAR(255) DEFAULT NULL,
    order_status VARCHAR(100) NOT NULL DEFAULT 'Pending',
    date_ordered DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Sample data for tcustomer_order
INSERT INTO tcustomer_order (
    order_number, customer_name, contact_number, merchant_name, pickup_address,
    pickup_note, order_description, quantity, estimated_price, dropoff_address,
    dropoff_note, assigned_rider, order_status
) VALUES 
('ORD001', 'Juan Dela Cruz', '09171234567', 'Mang Juan Eatery', '123 Rizal St., QC', 
 'Please handle with care', '2x Chicken Inasal', 2, 200.00, '456 Mabini St., Manila', 
 'Leave at the gate', 'Mark Reyes', 'Pending'),
('ORD002', 'Ana Santos', '09179876543', 'Sarap Corner', '789 Luna St., Makati', 
 'Call upon arrival', '1x Pancit, 1x Lumpia', 2, 150.00, '321 Bonifacio St., Taguig', 
 'Drop at the front desk', NULL, 'Pending');

-- Updated triders table with more registration fields
CREATE TABLE triders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    middle_name VARCHAR(255),
    last_name VARCHAR(255) NOT NULL,
    license_number VARCHAR(100) NOT NULL,
    vehicle_type VARCHAR(100) NOT NULL,
    vehicle_cor VARCHAR(255) NOT NULL,
    vehicle_plate_number VARCHAR(100) NOT NULL,
    topup_balance DECIMAL(10, 2) DEFAULT 0.00,
    rider_status VARCHAR(100) NOT NULL DEFAULT 'Active',
    date_registered DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Sample data for triders
INSERT INTO triders (
    first_name, middle_name, last_name, license_number, vehicle_type,
    vehicle_cor, vehicle_plate_number, topup_balance, rider_status
) VALUES 
('Mark', 'Lopez', 'Reyes', 'D1234567', 'Motorcycle', 'COR123456', 'ABC-1234', 150.00, 'Active'),
('Miguel', 'Santos', 'Cruz', 'E7654321', 'Motorcycle', 'COR654321', 'XYZ-5678', 200.00, 'Active'),
('Joey', 'T.', 'Ramirez', 'F2345678', 'Bicycle', 'COR789012', 'LMN-9101', 0.00, 'Inactive');

-- tusers table
CREATE TABLE tusers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    access_type ENUM('Admin', 'Customer', 'Rider') NOT NULL DEFAULT 'Customer',
    user_status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    date_registered DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Sample data for tusers
INSERT INTO tusers (first_name, last_name, username, password, access_type, user_status)
VALUES 
('Juan', 'Dela Cruz', 'customer', MD5('password'), 'Customer', 'Active'),
('Maria', 'Lopez', 'admin', MD5('password'), 'Admin', 'Active'),
('Mark', 'Reyes', 'rider', MD5('password'), 'Rider', 'Active');