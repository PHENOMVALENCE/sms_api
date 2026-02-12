-- Student Management System Database Schema (Revised)
-- This script is safe to run on a fresh database. For existing
-- installations, see the migration notes in README.md.

-- Create database (adjust name if needed)
CREATE DATABASE IF NOT EXISTS sms_api
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE sms_api;

-- Create students table
CREATE TABLE IF NOT EXISTS students (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    phone VARCHAR(20) DEFAULT NULL,
    date_of_birth DATE DEFAULT NULL,
    gender ENUM('Male', 'Female', 'Other') DEFAULT NULL,
    address TEXT DEFAULT NULL,
    enrollment_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_name (first_name, last_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data
INSERT INTO students (first_name, last_name, email, phone, date_of_birth, gender, address, enrollment_date) VALUES
('John', 'Doe', 'john.doe@example.com', '555-0101', '2000-05-15', 'Male', '123 Main St, Anytown, USA', '2024-01-15'),
('Jane', 'Smith', 'jane.smith@example.com', '555-0102', '1999-08-22', 'Female', '456 Oak Ave, Somewhere, USA', '2024-01-16'),
('Michael', 'Johnson', 'michael.j@example.com', '555-0103', '2001-03-10', 'Male', '789 Pine Rd, Elsewhere, USA', '2024-01-17'),
('Emily', 'Brown', 'emily.brown@example.com', '555-0104', '2000-11-30', 'Female', '321 Elm St, Nowhere, USA', '2024-01-18'),
('David', 'Wilson', 'david.wilson@example.com', '555-0105', '1998-07-14', 'Male', '654 Maple Dr, Anywhere, USA', '2024-01-19');
