-- Manual Database Setup for Tenant Management System
-- Run this in phpMyAdmin SQL tab

-- Create database (if not already created)
CREATE DATABASE IF NOT EXISTS tenant_management;
USE tenant_management;

-- Create tables
CREATE TABLE IF NOT EXISTS hostels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'admin',
    hostel_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hostel_id INT DEFAULT 1,
    room_number VARCHAR(20) NOT NULL,
    capacity INT DEFAULT 1,
    rent_amount DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    room_id INT,
    rent_amount DECIMAL(10,2) DEFAULT 0.00,
    security_deposit DECIMAL(10,2) DEFAULT 0.00,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method VARCHAR(20) DEFAULT 'cash',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- This is the missing table that dashboard.php needs
CREATE TABLE IF NOT EXISTS checkin_checkout (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    room_id INT NOT NULL,
    checkin_date DATE NOT NULL,
    checkout_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT IGNORE INTO hostels (id, name, address, phone, email) VALUES 
(1, 'Demo Hostel', '123 Main Street', '555-0123', 'info@demo.com');

INSERT IGNORE INTO users (name, email, password, role, hostel_id) VALUES 
('Admin User', 'admin@demo.com', 'admin123', 'admin', 1);

INSERT IGNORE INTO rooms (hostel_id, room_number, capacity, rent_amount) VALUES 
(1, '101', 2, 5000.00),
(1, '102', 1, 7000.00),
(1, '103', 3, 4000.00);

-- Display success message
SELECT 'Database setup completed successfully!' as Status;