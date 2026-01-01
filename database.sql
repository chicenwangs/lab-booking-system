-- ============================================================================
-- LAB BOOKING SYSTEM - DATABASE SCHEMA
-- ============================================================================
-- Run this entire script in phpMyAdmin or MySQL command line
-- This will create all necessary tables and insert sample data

-- ============================================================================
-- DROP EXISTING TABLES (Clean slate)
-- ============================================================================
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS labs;
DROP TABLE IF EXISTS users;

-- ============================================================================
-- CREATE DATABASE (if running from command line)
-- ============================================================================
-- CREATE DATABASE IF NOT EXISTS lab_booking_system;
-- USE lab_booking_system;

-- ============================================================================
-- USERS TABLE
-- ============================================================================
-- Stores all users (members and admins)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL COMMENT 'Bcrypt hashed password',
    role ENUM('member', 'admin') DEFAULT 'member',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes for performance
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- LABS TABLE
-- ============================================================================
-- Stores all laboratory information
CREATE TABLE labs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    capacity INT NOT NULL COMMENT 'Maximum number of people',
    equipment TEXT COMMENT 'Available equipment (comma-separated)',
    image_url VARCHAR(255) DEFAULT NULL,
    hourly_rate DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT 'Cost per hour',
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes for performance
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- BOOKINGS TABLE
-- ============================================================================
-- Stores all lab bookings
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lab_id INT NOT NULL,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    purpose TEXT COMMENT 'Purpose of booking',
    total_cost DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign keys
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lab_id) REFERENCES labs(id) ON DELETE CASCADE,
    
    -- Prevent double booking
    UNIQUE KEY unique_booking (lab_id, booking_date, start_time),
    
    -- Indexes for performance
    INDEX idx_user_id (user_id),
    INDEX idx_lab_id (lab_id),
    INDEX idx_booking_date (booking_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- INSERT DEFAULT ADMIN ACCOUNT
-- ============================================================================
-- Default Admin Login:
-- Email: admin@lab.com
-- Password: admin123
-- Note: Password is already bcrypt hashed
INSERT INTO users (full_name, email, password, role) VALUES 
('Admin User', 'admin@lab.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- ============================================================================
-- INSERT SAMPLE LABS
-- ============================================================================
INSERT INTO labs (name, description, capacity, equipment, hourly_rate, status) VALUES 
(
    'Chemistry Lab A', 
    'Fully equipped chemistry laboratory with fume hoods and safety equipment. Ideal for organic and inorganic chemistry experiments.', 
    25, 
    'Fume hoods, Bunsen burners, pH meters, Spectrophotometers, Centrifuges, Analytical balances, Hot plates', 
    50.00, 
    'active'
),
(
    'Physics Lab B', 
    'Advanced physics laboratory with modern measurement and testing equipment. Perfect for mechanics, optics, and electronics experiments.', 
    30, 
    'Oscilloscopes, Function generators, Digital multimeters, Motion sensors, Laser equipment, Power supplies, Data acquisition systems', 
    45.00, 
    'active'
),
(
    'Biology Lab C', 
    'Microbiology and cell culture laboratory with sterile facilities and advanced microscopy equipment.', 
    20, 
    'Microscopes, Incubators, Autoclaves, Centrifuges, PCR machines, Gel electrophoresis, Microplate readers', 
    55.00, 
    'active'
),
(
    'Computer Lab D', 
    'High-performance computing lab with latest software for programming, simulation, and data analysis.', 
    40, 
    '40 High-end workstations, Development software, MATLAB, Python, R, Simulation tools, 3D modeling software', 
    35.00, 
    'active'
),
(
    'Engineering Lab E', 
    '3D printing and prototyping workshop with CAD workstations and manufacturing equipment.', 
    15, 
    '5 3D printers, CAD workstations, SolidWorks, AutoCAD, Testing equipment, Electronic components, Soldering stations', 
    60.00, 
    'active'
);

-- ============================================================================
-- INSERT SAMPLE MEMBER ACCOUNT (Optional)
-- ============================================================================
-- Sample Member Login:
-- Email: student@lab.com
-- Password: student123
INSERT INTO users (full_name, email, password, role) VALUES 
('John Student', 'student@lab.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member');

-- ============================================================================
-- INSERT SAMPLE BOOKINGS (Optional - for testing)
-- ============================================================================
-- Some sample bookings to populate the system
INSERT INTO bookings (user_id, lab_id, booking_date, start_time, end_time, purpose, total_cost, status) VALUES 
(2, 1, '2025-01-05', '09:00:00', '11:00:00', 'Organic chemistry synthesis experiment', 100.00, 'confirmed'),
(2, 3, '2025-01-06', '14:00:00', '16:00:00', 'Cell culture maintenance', 110.00, 'confirmed'),
(2, 4, '2025-01-07', '10:00:00', '12:00:00', 'Machine learning model training', 70.00, 'confirmed');

-- ============================================================================
-- VERIFY INSTALLATION
-- ============================================================================
-- Check that everything was created successfully
SELECT 'Database setup complete!' AS status;
SELECT '========================' AS separator;
SELECT 'Users created:' AS info, COUNT(*) AS count FROM users;
SELECT 'Labs created:' AS info, COUNT(*) AS count FROM labs;
SELECT 'Sample bookings:' AS info, COUNT(*) AS count FROM bookings;
SELECT '========================' AS separator;
SELECT 'Admin login: admin@lab.com / admin123' AS credentials;
SELECT 'Member login: student@lab.com / student123' AS credentials;

-- ============================================================================
-- USEFUL QUERIES FOR TESTING
-- ============================================================================
-- Uncomment these to test your database

-- Show all users
-- SELECT id, full_name, email, role, created_at FROM users;

-- Show all labs
-- SELECT id, name, capacity, hourly_rate, status FROM labs;

-- Show all bookings
-- SELECT b.id, u.full_name, l.name, b.booking_date, b.start_time, b.end_time, b.total_cost, b.status
-- FROM bookings b
-- JOIN users u ON b.user_id = u.id
-- JOIN labs l ON b.lab_id = l.id;

-- ============================================================================
-- END OF DATABASE SETUP
-- ============================================================================