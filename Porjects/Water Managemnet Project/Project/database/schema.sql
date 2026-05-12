-- WataReport Database Schema

CREATE DATABASE IF NOT EXISTS watareport_db;
USE watareport_db;

-- Table: users
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('water_company', 'admin') NOT NULL,
    company_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: reports
CREATE TABLE IF NOT EXISTS reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tracking_id VARCHAR(20) UNIQUE NOT NULL,
    issue_type ENUM('burst_pipe', 'no_water_unexplained', 'water_suspension_bill', 'other') NOT NULL,
    description TEXT,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    address VARCHAR(255),
    reporter_name VARCHAR(100) NULL,
    upvote_count INT DEFAULT 0,
    status ENUM('pending', 'acknowledged', 'resolved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    acknowledged_at TIMESTAMP NULL,
    resolved_at TIMESTAMP NULL,
    resolution_time_hours DECIMAL(10,2) NULL,
    assigned_team VARCHAR(100) NULL,
    internal_notes TEXT NULL,
    created_by INT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Table: photos
CREATE TABLE IF NOT EXISTS photos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE
);

-- Table: upvotes
CREATE TABLE IF NOT EXISTS upvotes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_id INT NOT NULL,
    session_id VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    UNIQUE KEY unique_upvote (report_id, session_id)
);

-- Table: status_history
CREATE TABLE IF NOT EXISTS status_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_id INT NOT NULL,
    user_id INT NULL,
    old_status VARCHAR(20) NOT NULL,
    new_status VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Table: bill_inquiries
CREATE TABLE IF NOT EXISTS bill_inquiries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_id INT NOT NULL,
    staff_user_id INT NOT NULL,
    bill_status ENUM('paid_pending_reconnect', 'outstanding_balance', 'unknown_referred') NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_user_id) REFERENCES users(id)
);

-- Insert Default Admin User (Password: admin123 -> $2y$10$YourHashHere)
-- Note: Replace the hash with a real bcrypt hash of 'admin123'
INSERT INTO users (username, password_hash, role, company_name) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Admin');
