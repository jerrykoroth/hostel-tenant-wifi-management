-- --------------------------------------------------------
-- Tenant Management Mobile Application SQL Schema
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS tenant_management;
USE tenant_management;

-- Users table (for authentication and role management)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff', 'owner') DEFAULT 'staff',
    hostel_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Hostels table (organizations/hostels)
CREATE TABLE IF NOT EXISTS hostels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Rooms table
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hostel_id INT NOT NULL,
    room_number VARCHAR(20) NOT NULL,
    capacity INT NOT NULL DEFAULT 1,
    monthly_rent DECIMAL(10,2) DEFAULT 0.00,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE CASCADE,
    UNIQUE KEY unique_room_per_hostel (hostel_id, room_number)
);

-- Beds table
CREATE TABLE IF NOT EXISTS beds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    bed_number VARCHAR(20) NOT NULL,
    status ENUM('available', 'occupied', 'maintenance') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    UNIQUE KEY unique_bed_per_room (room_id, bed_number)
);

-- Tenants table
CREATE TABLE IF NOT EXISTS tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    id_proof_type ENUM('aadhar', 'pan', 'passport', 'driving_license') DEFAULT 'aadhar',
    id_proof_number VARCHAR(50),
    emergency_contact VARCHAR(20),
    emergency_contact_name VARCHAR(100),
    bed_id INT,
    checkin_date DATE,
    checkout_date DATE,
    monthly_rent DECIMAL(10,2) DEFAULT 0.00,
    security_deposit DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('active', 'inactive', 'checked_out') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (bed_id) REFERENCES beds(id) ON DELETE SET NULL
);

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    date DATE NOT NULL,
    method ENUM('cash', 'card', 'upi', 'bank_transfer', 'cheque') DEFAULT 'cash',
    notes TEXT,
    payment_type ENUM('rent', 'security_deposit', 'maintenance', 'penalty', 'other') DEFAULT 'rent',
    receipt_number VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- Check-in/Check-out history table
CREATE TABLE IF NOT EXISTS checkin_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    bed_id INT NOT NULL,
    checkin_date DATE NOT NULL,
    checkout_date DATE,
    rent_amount DECIMAL(10,2),
    security_deposit DECIMAL(10,2),
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (bed_id) REFERENCES beds(id) ON DELETE CASCADE
);

-- Activity log table for audit trail
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Add foreign key constraint for hostel_id in users table
ALTER TABLE users ADD FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE SET NULL;

-- Insert default admin user and sample data
INSERT INTO hostels (name, address, phone, email, description) VALUES
('Green Valley Hostel', '123 Main Street, City Center', '+1-555-0123', 'info@greenvalley.com', 'Premium hostel with modern amenities'),
('Sunrise Hostel', '456 Oak Avenue, Downtown', '+1-555-0456', 'contact@sunrisehostel.com', 'Budget-friendly accommodation for students');

-- Insert default admin user (password: admin123)
INSERT INTO users (name, email, password_hash, role, hostel_id) VALUES
('Admin User', 'admin@tenantmanagement.com', '$2y$10$wHfXfE3EB4h8V6IbRHfWJOsUZmkHiVim2k8Sx06aTCWhWmHAB7Uwa', 'admin', 1),
('Staff User', 'staff@greenvalley.com', '$2y$10$wHfXfE3EB4h8V6IbRHfWJOsUZmkHiVim2k8Sx06aTCWhWmHAB7Uwa', 'staff', 1);

-- Insert sample rooms
INSERT INTO rooms (hostel_id, room_number, capacity, monthly_rent, description) VALUES
(1, '101', 2, 5000.00, 'Double sharing room with attached bathroom'),
(1, '102', 1, 7000.00, 'Single room with AC'),
(1, '103', 3, 4000.00, 'Triple sharing room'),
(2, '201', 2, 4500.00, 'Double sharing room'),
(2, '202', 1, 6000.00, 'Single room with balcony');

-- Insert sample beds
INSERT INTO beds (room_id, bed_number, status) VALUES
(1, 'A', 'available'),
(1, 'B', 'available'),
(2, 'A', 'available'),
(3, 'A', 'available'),
(3, 'B', 'available'),
(3, 'C', 'available'),
(4, 'A', 'available'),
(4, 'B', 'available'),
(5, 'A', 'available');

-- Create indexes for better performance
CREATE INDEX idx_tenants_status ON tenants(status);
CREATE INDEX idx_tenants_checkin ON tenants(checkin_date);
CREATE INDEX idx_payments_date ON payments(date);
CREATE INDEX idx_payments_tenant ON payments(tenant_id);
CREATE INDEX idx_beds_status ON beds(status);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_activity_log_user ON activity_log(user_id);
CREATE INDEX idx_activity_log_date ON activity_log(created_at);