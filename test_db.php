<?php
require 'db.php';

try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll();
    echo "Database connection successful!<br>";
    echo "Tables in database:<br>";
    foreach ($tables as $table) {
        echo $table[0] . "<br>";
    }
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
}
?> 