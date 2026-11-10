-- Development database schema reconstructed from the supplied PHP source.
-- This is NOT an official government database schema or a supplied original dump.
-- Test locally before relying on this for demonstrations.
CREATE DATABASE IF NOT EXISTS leyte_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE leyte_portal;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(200) NOT NULL,
    role ENUM('admin', 'volunteer') NOT NULL DEFAULT 'volunteer',
    availability ENUM('Available', 'Pre-occupied', 'Busy') NOT NULL DEFAULT 'Available'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS alerts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    message TEXT NOT NULL,
    alert_level ENUM('Info', 'Warning', 'Critical') NOT NULL DEFAULT 'Info',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS evacuation_centers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    barangay VARCHAR(200) NOT NULL,
    capacity INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('Available', 'Full', 'Closed') NOT NULL DEFAULT 'Available'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tasks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    status ENUM('Pending', 'In Progress', 'Completed') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS center_assignments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    center_id INT UNSIGNED NOT NULL,
    volunteer_id INT UNSIGNED NOT NULL,
    status ENUM('Pending', 'Approved') NOT NULL DEFAULT 'Pending',
    UNIQUE KEY unique_center_volunteer (center_id, volunteer_id),
    FOREIGN KEY (center_id) REFERENCES evacuation_centers(id) ON DELETE CASCADE,
    FOREIGN KEY (volunteer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS task_assignments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id INT UNSIGNED NOT NULL UNIQUE,
    volunteer_id INT UNSIGNED NOT NULL,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (volunteer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
