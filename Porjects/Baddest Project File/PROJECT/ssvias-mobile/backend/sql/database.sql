-- backend/sql/database.sql
CREATE DATABASE IF NOT EXISTS ssvias_db;
USE ssvias_db;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('citizen', 'police', 'admin', 'government') DEFAULT 'citizen',
    region VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Vehicles table
CREATE TABLE vehicles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    plate_number VARCHAR(20) UNIQUE NOT NULL,
    vin VARCHAR(50) NOT NULL,
    make VARCHAR(50),
    model VARCHAR(50),
    year INT,
    color VARCHAR(30),
    owner_id INT,
    status ENUM('safe', 'stolen', 'recovered') DEFAULT 'safe',
    reported_date DATE,
    last_location VARCHAR(255),
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id)
);

-- Theft reports table
CREATE TABLE theft_reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_id INT,
    reported_by INT,
    last_known_location VARCHAR(255),
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    description TEXT,
    status ENUM('pending', 'verified', 'resolved') DEFAULT 'pending',
    report_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    FOREIGN KEY (reported_by) REFERENCES users(id)
);

-- Sightings table
CREATE TABLE sightings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehicle_id INT,
    reported_by INT,
    location VARCHAR(255),
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    description TEXT,
    image_url VARCHAR(255),
    sighting_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    FOREIGN KEY (reported_by) REFERENCES users(id)
);

-- Notifications table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    title VARCHAR(255),
    message TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert sample data
INSERT INTO users (name, email, phone, password, role) VALUES
('Admin User', 'admin@ssvias.cm', '677000001', MD5('admin123'), 'admin'),
('Police Bamenda', 'police@ssvias.cm', '677000002', MD5('police123'), 'police'),
('John Citizen', 'john@email.com', '677000003', MD5('user123'), 'citizen');

INSERT INTO vehicles (plate_number, vin, make, model, year, color, owner_id, status, reported_date) VALUES
('AB123CD', 'VIN123456789', 'Toyota', 'Corolla', 2020, 'Silver', 3, 'stolen', '2024-01-15'),
('XY789ZZ', 'VIN987654321', 'Honda', 'Civic', 2021, 'Black', 3, 'safe', NULL),
('NW456GH', 'VIN456789123', 'Ford', 'Ranger', 2019, 'White', 3, 'stolen', '2024-01-20');