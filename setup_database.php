<?php
require_once 'db.php';

echo "Setting up database...\n";

// Create users table with new profile fields
$sql = "CREATE TABLE IF NOT EXISTS users (
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
)";

try {
  $pdo->exec($sql);
  echo "Users table created/updated successfully\n";
} catch (PDOException $e) {
  echo "Error creating users table: " . $e->getMessage() . "\n";
}

// Add new columns to existing users table if they don't exist
$columns = [
  'status' => "ALTER TABLE users ADD COLUMN status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'",
  'first_name' => "ALTER TABLE users ADD COLUMN first_name VARCHAR(50)",
  'last_name' => "ALTER TABLE users ADD COLUMN last_name VARCHAR(50)",
  'department' => "ALTER TABLE users ADD COLUMN department VARCHAR(100)",
  'position' => "ALTER TABLE users ADD COLUMN position VARCHAR(100)",
  'bio' => "ALTER TABLE users ADD COLUMN bio TEXT",
  'avatar' => "ALTER TABLE users ADD COLUMN avatar VARCHAR(255)",
  'two_factor' => "ALTER TABLE users ADD COLUMN two_factor ENUM('disabled', 'enabled', 'required') DEFAULT 'disabled'",
  'created_at' => "ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
  'updated_at' => "ALTER TABLE users ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
  'last_login' => "ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL"
];

foreach ($columns as $column => $sql) {
  try {
    // Check if column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM users LIKE ?");
    $stmt->execute([$column]);
    if ($stmt->rowCount() == 0) {
      $pdo->exec($sql);
      echo "Added column: $column\n";
    } else {
      echo "Column $column already exists\n";
    }
  } catch (PDOException $e) {
    echo "Error adding column $column: " . $e->getMessage() . "\n";
  }
}

// Create uploads directory for avatars
$uploadsDir = 'uploads/avatars';
if (!is_dir($uploadsDir)) {
  mkdir($uploadsDir, 0755, true);
  echo "Created uploads directory: $uploadsDir\n";
} else {
  echo "Uploads directory already exists\n";
}

echo "Database setup complete!\n";
?> 