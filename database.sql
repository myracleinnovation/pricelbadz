CREATE DATABASE IF NOT EXISTS pricelbadz;
USE pricelbadz;

-- Table for Pabili/Pasuyo orders
CREATE TABLE tpabili_orders (
id INT AUTO_INCREMENT PRIMARY KEY,
order_number VARCHAR(20) NOT NULL,
customer_name VARCHAR(100) NOT NULL,
contact_number VARCHAR(20) NOT NULL,
store_name VARCHAR(100) NOT NULL,
order_description TEXT NOT NULL,
quantity INT NOT NULL,
estimated_price DECIMAL(10, 2) NOT NULL,
store_address TEXT NOT NULL,
pickup_note TEXT,
delivery_address TEXT NOT NULL,
delivery_note TEXT,
assigned_rider VARCHAR(100),
order_status ENUM('Pending', 'Accepted', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Pending',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for Paangkas (Pahatid/Pasundo) orders
CREATE TABLE tpaangkas_orders (
id INT AUTO_INCREMENT PRIMARY KEY,
order_number VARCHAR(20) NOT NULL,
customer_name VARCHAR(100) NOT NULL,
contact_number VARCHAR(20) NOT NULL,
pickup_address TEXT NOT NULL,
vehicle_type ENUM('Motorcycle', 'Tricycle', 'Car') NOT NULL,
pickup_note TEXT,
dropoff_address TEXT NOT NULL,
dropoff_note TEXT,
assigned_rider VARCHAR(100),
order_status ENUM('Pending', 'Accepted', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Pending',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for Padala orders
CREATE TABLE tpadala_orders (
id INT AUTO_INCREMENT PRIMARY KEY,
order_number VARCHAR(20) NOT NULL,
customer_name VARCHAR(100) NOT NULL,
contact_number VARCHAR(20) NOT NULL,
pickup_location TEXT NOT NULL,
order_description TEXT NOT NULL,
pickup_note TEXT,
dropoff_address TEXT NOT NULL,
dropoff_note TEXT,
assigned_rider VARCHAR(100),
order_status ENUM('Pending', 'Accepted', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Pending',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for riders
CREATE TABLE triders (
id INT AUTO_INCREMENT PRIMARY KEY,
first_name VARCHAR(50) NOT NULL,
middle_name VARCHAR(50),
last_name VARCHAR(50) NOT NULL,
license_number VARCHAR(20) NOT NULL,
vehicle_type ENUM('Motorcycle', 'Tricycle', 'Car') NOT NULL,
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

-- Insert sample data for riders
INSERT INTO triders (first_name, middle_name, last_name, license_number, vehicle_type, vehicle_cor,
vehicle_plate_number, topup_balance, rider_status) VALUES
('John', 'Doe', 'Smith', 'LIC123456', 'Motorcycle', 'COR123456', 'ABC1234', 1000.00, 'Active'),
('Jane', 'Marie', 'Johnson', 'LIC789012', 'Tricycle', 'COR789012', 'XYZ5678', 1500.00, 'Active'),
('Michael', 'James', 'Brown', 'LIC345678', 'Car', 'COR345678', 'DEF9012', 2000.00, 'Active'),
('Mark', 'Lopez', 'Reyes', 'D1234567', 'Motorcycle', 'COR123456', 'ABC-1234', 150.00, 'Active'),
('Miguel', 'Santos', 'Cruz', 'E7654321', 'Motorcycle', 'COR654321', 'XYZ-5678', 200.00, 'Active');

-- Insert sample data for users
INSERT INTO tusers (username, password, first_name, last_name, email, role, user_status) VALUES
('admin', 'md5(password)', 'Admin', 'User', 'admin@example.com', 'Admin',
'Active'),
('staff1', 'md5(password)', 'Staff', 'One', 'staff1@example.com',
'Staff', 'Active');

-- Insert sample data for Pabili orders
INSERT INTO tpabili_orders (order_number, customer_name, contact_number, store_name, order_description, quantity,
estimated_price, store_address, pickup_note, delivery_address, delivery_note, assigned_rider, order_status) VALUES
('PAB-2023-001', 'Juan Dela Cruz', '09123456789', 'Jollibee', '2 Chicken Joy, 1 Spaghetti', 3, 250.00, '123 Main St,
Manila', 'Please handle with care', '456 Home St, Quezon City', 'Leave at the gate', 'John Smith', 'Pending'),
('PAB-2023-002', 'Maria Santos', '09234567890', 'McDonald\'s', '2 Big Mac, 1 Large Fries', 3, 300.00, '789 Food St,
Makati', 'Call upon arrival', '321 House St, Pasig', 'Drop at the front desk', 'Jane Johnson', 'Accepted');

-- Insert sample data for Paangkas orders
INSERT INTO tpaangkas_orders (order_number, customer_name, contact_number, pickup_address, vehicle_type, pickup_note,
dropoff_address, dropoff_note, assigned_rider, order_status) VALUES
('PAA-2023-001', 'Pedro Reyes', '09345678901', '123 Pickup St, Manila', 'Motorcycle', 'Call when arriving', '456 Dropoff
St, Quezon City', 'Leave at the gate', 'John Smith', 'Pending'),
('PAA-2023-002', 'Ana Garcia', '09456789012', '789 Pickup St, Makati', 'Car', 'Wait at the lobby', '321 Dropoff St,
Pasig', 'Drop at the front desk', 'Michael Brown', 'Accepted');

-- Insert sample data for Padala orders
INSERT INTO tpadala_orders (order_number, customer_name, contact_number, pickup_location, order_description,
pickup_note, dropoff_address, dropoff_note, assigned_rider, order_status) VALUES
('PAD-2023-001', 'Jose Santos', '09567890123', '123 Pickup St, Manila', 'Package containing documents', 'Handle with
care', '456 Dropoff St, Quezon City', 'Leave at the gate', 'John Smith', 'Pending'),
('PAD-2023-002', 'Sofia Lopez', '09678901234', '789 Pickup St, Makati', 'Small box containing electronics', 'Fragile
items', '321 Dropoff St, Pasig', 'Drop at the front desk', 'Jane Johnson', 'Accepted');

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