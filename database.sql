CREATE DATABASE IF NOT EXISTS pricelbadz;
USE pricelbadz;

-- Table for riders (moved to the top since it's referenced by other tables)
CREATE TABLE triders (
id INT AUTO_INCREMENT PRIMARY KEY,
first_name VARCHAR(50) NOT NULL,
middle_name VARCHAR(50),
last_name VARCHAR(50) NOT NULL,
contact_number VARCHAR(20) NOT NULL,
license_number VARCHAR(20) NOT NULL,
vehicle_type ENUM('Motorcycle (1 seat)', 'Tricycle (2-4 seats)', 'Car (3-4 seats)', 'Car (5-7 seats)', 'Van (10-14
seats)') NOT NULL,
vehicle_cor VARCHAR(50) NOT NULL,
vehicle_plate_number VARCHAR(20) NOT NULL,
topup_balance DECIMAL(10, 2) DEFAULT 0.00,
rider_status ENUM('Active', 'Inactive') DEFAULT 'Active',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for users (admin/staff)
CREATE TABLE tusers (
id INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(50) NOT NULL UNIQUE,
password VARCHAR(255) NOT NULL,
first_name VARCHAR(50) NOT NULL,
last_name VARCHAR(50) NOT NULL,
email VARCHAR(100) NOT NULL UNIQUE,
role ENUM('Admin', 'Staff') DEFAULT 'Staff',
user_status ENUM('Active', 'Inactive') DEFAULT 'Active',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for Pabili/Pasuyo orders
CREATE TABLE tpabili_orders (
id INT AUTO_INCREMENT PRIMARY KEY,
order_number VARCHAR(20) NOT NULL,
customer_name VARCHAR(100) NOT NULL,
contact_number VARCHAR(20) NOT NULL,
merchant_store_name VARCHAR(100) NOT NULL,
order_description TEXT NOT NULL,
store_address TEXT NOT NULL,
pickup_note TEXT,
delivery_address TEXT NOT NULL,
delivery_note TEXT,
assigned_rider INT,
vehicle_type ENUM('Motorcycle (1 seat)', 'Tricycle (2-4 seats)', 'Car (3-4 seats)', 'Car (5-7 seats)', 'Van (10-14
seats)') NULL,
order_status ENUM('Pending', 'On-Going', 'Completed', 'Cancelled') DEFAULT 'Pending',
service_fee DECIMAL(10, 2) DEFAULT 0.00,
commission DECIMAL(10, 2) DEFAULT 0.00,
status_changed_at TIMESTAMP NULL,
status_changed_by VARCHAR(100) NULL,
rider_assigned_by VARCHAR(100) NULL,
rider_assigned_at TIMESTAMP NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (assigned_rider) REFERENCES triders(id) ON DELETE SET NULL
);

-- Table for Pahatid/Pasundo orders
CREATE TABLE tpaangkas_orders (
id INT AUTO_INCREMENT PRIMARY KEY,
order_number VARCHAR(20) NOT NULL,
customer_name VARCHAR(100) NOT NULL,
contact_number VARCHAR(20) NOT NULL,
pickup_address TEXT NOT NULL,
vehicle_type ENUM('Motorcycle (1 seat)', 'Tricycle (2-4 seats)', 'Car (3-4 seats)', 'Car (5-7 seats)', 'Van (10-14
seats)') NOT NULL,
pickup_note TEXT,
dropoff_address TEXT NOT NULL,
dropoff_note TEXT,
assigned_rider INT,
order_status ENUM('Pending', 'On-Going', 'Completed', 'Cancelled') DEFAULT 'Pending',
service_fee DECIMAL(10, 2) DEFAULT 0.00,
commission DECIMAL(10, 2) DEFAULT 0.00,
status_changed_at TIMESTAMP NULL,
status_changed_by VARCHAR(100) NULL,
rider_assigned_by VARCHAR(100) NULL,
rider_assigned_at TIMESTAMP NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (assigned_rider) REFERENCES triders(id) ON DELETE SET NULL
);

-- Table for Padala orders
CREATE TABLE tpadala_orders (
id INT AUTO_INCREMENT PRIMARY KEY,
order_number VARCHAR(20) NOT NULL,
customer_name VARCHAR(100) NOT NULL,
contact_number VARCHAR(20) NOT NULL,
pickup_address TEXT NOT NULL,
order_description TEXT NOT NULL,
pickup_note TEXT,
dropoff_address TEXT NOT NULL,
dropoff_note TEXT,
assigned_rider INT,
vehicle_type ENUM('Motorcycle (1 seat)', 'Tricycle (2-4 seats)', 'Car (3-4 seats)', 'Car (5-7 seats)', 'Van (10-14
seats)') NULL,
order_status ENUM('Pending', 'On-Going', 'Completed', 'Cancelled') DEFAULT 'Pending',
service_fee DECIMAL(10, 2) DEFAULT 0.00,
commission DECIMAL(10, 2) DEFAULT 0.00,
status_changed_at TIMESTAMP NULL,
status_changed_by VARCHAR(100) NULL,
rider_assigned_by VARCHAR(100) NULL,
rider_assigned_at TIMESTAMP NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (assigned_rider) REFERENCES triders(id) ON DELETE SET NULL
);

-- Drop existing table if it exists
DROP TABLE IF EXISTS `trider_topup_ledger`;

-- Table for rider top-up transaction history
CREATE TABLE IF NOT EXISTS `trider_topup_ledger` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`rider_id` int(11) NOT NULL,
`transaction_type` enum('Add Top-up', 'Withdraw Top-up', 'Commission Deduction', 'Refund') NOT NULL,
`previous_balance` decimal(10, 2) NOT NULL,
`amount` decimal(10, 2) NOT NULL,
`current_balance` decimal(10, 2) NOT NULL,
`order_number` varchar(50) DEFAULT NULL,
`processed_by` varchar(100) NOT NULL,
`notes` text DEFAULT NULL,
`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
PRIMARY KEY (`id`),
KEY `rider_id` (`rider_id`),
CONSTRAINT `trider_topup_ledger_ibfk_1` FOREIGN KEY (`rider_id`) REFERENCES `triders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample data for users
INSERT INTO tusers (username, password, first_name, last_name, email, role, user_status) VALUES
('admin', MD5('password'), 'Admin', 'User', 'admin@example.com', 'Admin', 'Active');

-- tmerchants table
CREATE TABLE tmerchants (
id INT AUTO_INCREMENT PRIMARY KEY,
merchant_name VARCHAR(255) NOT NULL,
merchant_description TEXT,
merchant_logo VARCHAR(255) NOT NULL
);

-- tmerchant_images table for additional merchant images
CREATE TABLE tmerchant_images (
id INT AUTO_INCREMENT PRIMARY KEY,
merchant_id INT NOT NULL,
image_path VARCHAR(255) NOT NULL,
image_description TEXT,
display_order INT DEFAULT 0,
date_added DATETIME DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (merchant_id) REFERENCES tmerchants(id) ON DELETE CASCADE
);

-- Table for merchant products/services
CREATE TABLE tmerchant_products (
id INT AUTO_INCREMENT PRIMARY KEY,
merchant_id INT NOT NULL,
name VARCHAR(255) NOT NULL,
description TEXT,
price DECIMAL(10, 2),
image_path VARCHAR(255) NOT NULL,
is_active BOOLEAN DEFAULT TRUE,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (merchant_id) REFERENCES tmerchants(id) ON DELETE CASCADE
);