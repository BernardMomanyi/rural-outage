<?php
require_once 'db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS technician_substations (
      id INT AUTO_INCREMENT PRIMARY KEY,
      technician_id INT NOT NULL,
      substation_id INT NOT NULL,
      assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (technician_id) REFERENCES users(id) ON DELETE CASCADE,
      FOREIGN KEY (substation_id) REFERENCES substations(id) ON DELETE CASCADE,
      UNIQUE KEY unique_assignment (technician_id, substation_id)
    )";
    $pdo->exec($sql);
    echo "<h2 style='color:green'>✅ technician_substations table created or already exists.</h2>";
} catch (Exception $e) {
    echo "<h2 style='color:red'>❌ Migration failed: " . $e->getMessage() . "</h2>";
}
?> 