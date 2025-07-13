-- Outage Management System Database Schema

-- Users table with enhanced profile fields
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  phone VARCHAR(20),
  role ENUM('admin', 'technician', 'user') DEFAULT 'user',
  status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
  first_name VARCHAR(50),
  last_name VARCHAR(50),
  department VARCHAR(100),
  position VARCHAR(100),
  bio TEXT,
  avatar VARCHAR(255),
  two_factor ENUM('disabled', 'enabled', 'required') DEFAULT 'disabled',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  last_login TIMESTAMP NULL,
  INDEX idx_username (username),
  INDEX idx_email (email),
  INDEX idx_role (role),
  INDEX idx_status (status)
);

CREATE TABLE substations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(255) NOT NULL
);

CREATE TABLE predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    substation_id INT NOT NULL,
    risk_level VARCHAR(50) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (substation_id) REFERENCES substations(id) ON DELETE CASCADE
);

CREATE TABLE alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    substation_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    severity ENUM('info','warning','critical') NOT NULL,
    resolved TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (substation_id) REFERENCES substations(id) ON DELETE CASCADE
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE settings (
    `key` VARCHAR(50) PRIMARY KEY,
    `value` VARCHAR(255) NOT NULL
);

CREATE TABLE outages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    substation_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME DEFAULT NULL,
    description VARCHAR(255),
    FOREIGN KEY (substation_id) REFERENCES substations(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user_outages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  location VARCHAR(255) NOT NULL,
  time_started DATETIME NOT NULL,
  description TEXT,
  status ENUM('Submitted','Assigned','Resolved') DEFAULT 'Submitted',
  technician_id INT DEFAULT NULL,
  feedback TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (technician_id) REFERENCES users(id)
); 