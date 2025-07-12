SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS activity_log;
DROP TABLE IF EXISTS checkin_history;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS tenants;
DROP TABLE IF EXISTS beds;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS hostels;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE hostels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff', 'owner') DEFAULT 'staff',
    hostel_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE SET NULL
);

CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hostel_id INT NOT NULL,
    room_number VARCHAR(20) NOT NULL,
    capacity INT NOT NULL DEFAULT 1,
    monthly_rent DECIMAL(10,2) DEFAULT 0.00,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE CASCADE
);

CREATE TABLE beds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    bed_number VARCHAR(20) NOT NULL,
    status ENUM('available', 'occupied', 'maintenance') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

CREATE TABLE tenants (
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

CREATE TABLE payments (
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

CREATE TABLE checkin_history (
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

CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO hostels (name, address, phone, email, description) VALUES ('Green Valley Hostel', '123 Main Street, City Center', '+1-555-0123', 'info@greenvalley.com', 'Premium hostel with modern amenities');

INSERT INTO hostels (name, address, phone, email, description) VALUES ('Sunrise Hostel', '456 Oak Avenue, Downtown', '+1-555-0456', 'contact@sunrisehostel.com', 'Budget-friendly accommodation for students');

INSERT INTO users (name, email, password_hash, role, hostel_id) VALUES ('Admin User', 'admin@tenantmanagement.com', '$2y$10$wHfXfE3EB4h8V6IbRHfWJOsUZmkHiVim2k8Sx06aTCWhWmHAB7Uwa', 'admin', 1);

INSERT INTO users (name, email, password_hash, role, hostel_id) VALUES ('Staff User', 'staff@greenvalley.com', '$2y$10$wHfXfE3EB4h8V6IbRHfWJOsUZmkHiVim2k8Sx06aTCWhWmHAB7Uwa', 'staff', 1);

INSERT INTO rooms (hostel_id, room_number, capacity, monthly_rent, description) VALUES (1, '101', 2, 5000.00, 'Double sharing room with attached bathroom');

INSERT INTO rooms (hostel_id, room_number, capacity, monthly_rent, description) VALUES (1, '102', 1, 7000.00, 'Single room with AC');

INSERT INTO rooms (hostel_id, room_number, capacity, monthly_rent, description) VALUES (1, '103', 3, 4000.00, 'Triple sharing room');

INSERT INTO rooms (hostel_id, room_number, capacity, monthly_rent, description) VALUES (2, '201', 2, 4500.00, 'Double sharing room');

INSERT INTO rooms (hostel_id, room_number, capacity, monthly_rent, description) VALUES (2, '202', 1, 6000.00, 'Single room with balcony');

INSERT INTO beds (room_id, bed_number, status) VALUES (1, 'A', 'available');

INSERT INTO beds (room_id, bed_number, status) VALUES (1, 'B', 'available');

INSERT INTO beds (room_id, bed_number, status) VALUES (2, 'A', 'available');

INSERT INTO beds (room_id, bed_number, status) VALUES (3, 'A', 'available');

INSERT INTO beds (room_id, bed_number, status) VALUES (3, 'B', 'available');

INSERT INTO beds (room_id, bed_number, status) VALUES (3, 'C', 'available');

INSERT INTO beds (room_id, bed_number, status) VALUES (4, 'A', 'available');

INSERT INTO beds (room_id, bed_number, status) VALUES (4, 'B', 'available');

INSERT INTO beds (room_id, bed_number, status) VALUES (5, 'A', 'available');