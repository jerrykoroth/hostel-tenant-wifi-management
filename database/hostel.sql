
-- --------------------------------------------------------
-- Hostel Management System SQL Dump
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS hostel;
USE hostel;

-- Admin table
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') DEFAULT 'admin'
);

-- Tenants table
CREATE TABLE IF NOT EXISTS tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    address TEXT,
    room_id INT,
    status ENUM('Checked-In', 'Checked-Out') DEFAULT 'Checked-In',
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Rooms table
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(20) NOT NULL UNIQUE,
    capacity INT NOT NULL,
    occupied INT DEFAULT 0
);

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT,
    amount DECIMAL(10,2),
    payment_date DATE,
    remarks TEXT,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- Check-in/Check-out table
CREATE TABLE IF NOT EXISTS checkin_checkout (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT,
    checkin_date DATE,
    checkout_date DATE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- User log (access log)
CREATE TABLE IF NOT EXISTS userlog (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user VARCHAR(50),
    role VARCHAR(20),
    action VARCHAR(255),
    log_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin user (username: admin, password: admin123 hashed)
INSERT INTO admin (username, password, role) VALUES
('admin', '$2y$10$wHfXfE3EB4h8V6IbRHfWJOsUZmkHiVim2k8Sx06aTCWhWmHAB7Uwa', 'admin');
