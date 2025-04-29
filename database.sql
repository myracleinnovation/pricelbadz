CREATE DATABASE IF NOT EXISTS pricelbadz;
USE pricelbadz;

-- Table for riders (moved to the top since it's referenced by other tables)
CREATE TABLE triders (
id INT AUTO_INCREMENT PRIMARY KEY,
first_name VARCHAR(50) NOT NULL,
middle_name VARCHAR(50),
last_name VARCHAR(50) NOT NULL,
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

-- Insert sample data for riders
INSERT INTO triders (first_name, middle_name, last_name, license_number, vehicle_type, vehicle_cor,
vehicle_plate_number, topup_balance, rider_status) VALUES
('John', 'Doe', 'Smith', 'LIC123456', 'Motorcycle (1 seat)', 'COR123456', 'ABC1234', 1000.00, 'Active'),
('Jane', 'Marie', 'Johnson', 'LIC789012', 'Tricycle (2-4 seats)', 'COR789012', 'XYZ5678', 1500.00, 'Active'),
('Michael', 'James', 'Brown', 'LIC345678', 'Car (3-4 seats)', 'COR345678', 'DEF9012', 2000.00, 'Active'),
('Mark', 'Lopez', 'Reyes', 'D1234567', 'Motorcycle (1 seat)', 'COR123456', 'ABC-1234', 150.00, 'Active'),
('Miguel', 'Santos', 'Cruz', 'E7654321', 'Motorcycle (1 seat)', 'COR654321', 'XYZ-5678', 200.00, 'Active');

-- Insert sample data for users
INSERT INTO tusers (username, password, first_name, last_name, email, role, user_status) VALUES
('admin', MD5('password'), 'Admin', 'User', 'admin@example.com', 'Admin', 'Active');

-- Insert sample data for top-up transactions
INSERT INTO trider_topup_ledger (rider_id, transaction_type, previous_balance, amount, current_balance, processed_by,
notes) VALUES
(1, 'Add Top-up', 0.00, 1000.00, 1000.00, 'admin', 'Initial top-up'),
(2, 'Add Top-up', 0.00, 1500.00, 1500.00, 'admin', 'Initial top-up'),
(3, 'Add Top-up', 0.00, 2000.00, 2000.00, 'admin', 'Initial top-up'),
(4, 'Add Top-up', 0.00, 150.00, 150.00, 'admin', 'Initial top-up'),
(5, 'Add Top-up', 0.00, 200.00, 200.00, 'admin', 'Initial top-up');

-- tmerchants table
CREATE TABLE tmerchants (
id INT AUTO_INCREMENT PRIMARY KEY,
merchant_name VARCHAR(255) NOT NULL,
merchant_description TEXT,
merchant_logo VARCHAR(255) NOT NULL
);

-- Sample data for tmerchants
INSERT INTO tmerchants (merchant_name, merchant_description, merchant_logo)
VALUES
('Balinsayaw Seaside', 'Fresh seafood restaurant with a beautiful seaside view', 'balinsayaw_seaside.jpg'),
('Banh Pho', 'Authentic Vietnamese cuisine', 'banhpho.jpg'),
('Black Scoop', 'Premium coffee and desserts', 'black_scoop.jpg'),
('Bona Chaolong', 'Specializing in Vietnamese noodle soup', 'bona_chaolong.jpg'),
('Bonchon', 'Korean-style fried chicken', 'bonchon.jpg'),
('Buko Rocks', 'Fresh coconut-based drinks and snacks', 'buko_rocks.jpg'),
('Chowking', 'Chinese fast food restaurant', 'chowking.jpg'),
('Crazy Krunch', 'Crispy fried chicken and sides', 'crazy_krunch.jpg'),
('Crispy King', 'Specializing in crispy fried chicken', 'crispy_king.jpg'),
('Dunkin', 'Donuts, coffee, and baked goods', 'dunkin.jpg'),
('Elmers', 'Family restaurant with Filipino dishes', 'elmers.jpg'),
('Greenwich', 'Pizza and pasta fast food', 'greenwich.jpg'),
('Haim Chicken', 'Specializing in various chicken dishes', 'haim_chicken.jpg'),
('Inasal', 'Filipino grilled chicken restaurant', 'inasal.webp'),
('Island Sizzle', 'Sizzling dishes with island flavors', 'island_sizzle.jpg'),
('Jollibee', 'Filipino fast food restaurant', 'jollibee.jpg'),
('Kainato', 'Authentic Filipino cuisine', 'kainato.jpg'),
('Las Fresas', 'Fresh and healthy dining options', 'las_fresas.jpg'),
('Levs Pizza', 'Artisanal pizza restaurant', 'levs_pizza.jpg'),
('Max Bunny', 'Cafe with bunny theme', 'max_bunny.jpg'),
('McDonalds', 'International fast food chain', 'mcdonalds.jpg'),
('Mister Donut', 'Specializing in donuts and pastries', 'mister_donut.jpg'),
('Mrs Tea', 'Milkshakes and desserts', 'mrs_tea.jpg'),
('Ms TealiciousPH', 'Specialty tea cafe', 'ms_tealiciousph.jpg'),
('Polo Vings', 'Sports-themed restaurant', 'polo_vings.jpg'),
('Potato Corner', 'Specializing in flavored fries', 'potato_corner.jpg'),
('Potdog', 'Hotdog and fast food items', 'potdog.jpg'),
('Shakeys', 'Pizza and pasta restaurant', 'shakeys.png'),
('Shawarma', 'Middle Eastern fast food', 'shawarma.jpg'),
('Thalias Chaolong', 'Vietnamese noodle soup restaurant', 'thalias_chaolong.webp');

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

-- Sample data for tmerchant_images
INSERT INTO tmerchant_images (merchant_id, image_path, image_description, display_order)
VALUES
(23, 'mrs_tea1.jpg', 'Mrs Tea store front', 1),
(23, 'mrs_tea2.jpg', 'Mrs Tea store front', 2),
(23, 'mrs_tea3.jpg', 'Mrs Tea store front', 3),
(23, 'mrs_tea4.jpg', 'Mrs Tea store front', 4),
(23, 'mrs_tea5.jpg', 'Mrs Tea store front', 5),
(23, 'mrs_tea6.jpg', 'Mrs Tea store front', 6),
(23, 'mrs_tea7.jpg', 'Mrs Tea store front', 7),
(23, 'mrs_tea8.jpg', 'Mrs Tea store front', 8),
(23, 'mrs_tea9.jpg', 'Mrs Tea store front', 9),
(23, 'mrs_tea10.jpg', 'Mrs Tea store front', 10),
(23, 'mrs_tea11.jpg', 'Mrs Tea store front', 11),
(23, 'mrs_tea12.jpg', 'Mrs Tea store front', 12),
(23, 'mrs_tea13.jpg', 'Mrs Tea store front', 13),

-- Balinsayaw Seaside images
(1, 'balinsayaw_seaside_interior.jpg', 'Interior view of the restaurant', 1),
(1, 'balinsayaw_seaside_dishes.jpg', 'Popular seafood dishes', 2),
(1, 'balinsayaw_seaside_view.jpg', 'Seaside view from the restaurant', 3),

-- Jollibee images
(16, 'jollibee_store.jpg', 'Jollibee store front', 1),
(16, 'jollibee_chicken.jpg', 'Jollibee chicken joy', 2),
(16, 'jollibee_burger.jpg', 'Jollibee burger', 3),

-- McDonalds images
(21, 'mcdonalds_store.jpg', 'McDonalds store front', 1),
(21, 'mcdonalds_big_mac.jpg', 'Big Mac burger', 2),
(21, 'mcdonalds_fries.jpg', 'McDonalds fries', 3),

-- Shakeys images
(27, 'shakeys_store.jpg', 'Shakeys store front', 1),
(27, 'shakeys_pizza.jpg', 'Shakeys pizza', 2),
(27, 'shakeys_pasta.jpg', 'Shakeys pasta', 3);

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

-- Sample data for merchant products
INSERT INTO tmerchant_products (merchant_id, name, description, price, image_path) VALUES
(16, 'Chicken Joy', 'Crispy juicy fried chicken', 99.00, 'chickenjoy.jpg'),
(16, 'Jolly Spaghetti', 'Sweet style spaghetti', 50.00, 'jollyspaghetti.jpg'),
(21, 'Big Mac', 'Signature burger with special sauce', 169.00, 'bigmac.jpg'),
(21, 'French Fries', 'World famous fries', 49.00, 'mcfries.jpg');