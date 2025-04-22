CREATE DATABASE IF NOT EXISTS pricelbadz;
USE pricelbadz;

CREATE TABLE tcustomer_order (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    contact_number VARCHAR(255) NOT NULL,
    pickup_address VARCHAR(255) NOT NULL,
    dropoff_address VARCHAR(255) NOT NULL,
    dropoff_contact_person VARCHAR(255) NOT NULL,
    dropoff_contact_number VARCHAR(255) NOT NULL,
    remarks VARCHAR(255) NOT NULL
);

CREATE TABLE triders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    middle_name VARCHAR(255) NOT NULL,
    surname VARCHAR(255) NOT NULL,
    license_number VARCHAR(255) NOT NULL,
    vehicle_plate_number VARCHAR(255) NOT NULL
);