<?php
require_once 'db.php';

echo "<h2>Adding Default Departments</h2>";

try {
    // Check if departments table exists, if not create it
    $stmt = $pdo->query("SHOW TABLES LIKE 'departments'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("CREATE TABLE departments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        echo "<p style='color: green;'>✅ Created departments table</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Departments table already exists</p>";
    }
    
    // Add default departments
    $default_departments = [
        ['name' => 'IT & Technology', 'description' => 'Information Technology and Systems'],
        ['name' => 'Operations', 'description' => 'Field Operations and Maintenance'],
        ['name' => 'Engineering', 'description' => 'Electrical and Civil Engineering'],
        ['name' => 'Customer Service', 'description' => 'Customer Support and Relations'],
        ['name' => 'Management', 'description' => 'Administrative and Management'],
        ['name' => 'Safety & Compliance', 'description' => 'Safety and Regulatory Compliance'],
        ['name' => 'Finance', 'description' => 'Financial and Accounting'],
        ['name' => 'Human Resources', 'description' => 'HR and Personnel Management'],
        ['name' => 'General', 'description' => 'General Department']
    ];
    
    $added = 0;
    $skipped = 0;
    
    foreach ($default_departments as $dept) {
        try {
            $stmt = $pdo->prepare("INSERT INTO departments (name, description) VALUES (?, ?)");
            $stmt->execute([$dept['name'], $dept['description']]);
            $added++;
            echo "<p style='color: green;'>✅ Added department: {$dept['name']}</p>";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $skipped++;
                echo "<p style='color: orange;'>⚠️ Department already exists: {$dept['name']}</p>";
            } else {
                echo "<p style='color: red;'>❌ Error adding {$dept['name']}: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    // Show all departments
    echo "<h3>All Departments:</h3>";
    $stmt = $pdo->query("SELECT * FROM departments ORDER BY name");
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($departments)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Description</th></tr>";
        foreach ($departments as $dept) {
            echo "<tr>";
            echo "<td>{$dept['id']}</td>";
            echo "<td>{$dept['name']}</td>";
            echo "<td>{$dept['description']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>Summary:</h3>";
    echo "<ul>";
    echo "<li>Added: $added departments</li>";
    echo "<li>Skipped: $skipped departments (already existed)</li>";
    echo "</ul>";
    
    echo "<p style='color: green; font-weight: bold;'>✅ Default departments setup completed!</p>";
    echo "<p><a href='users.php'>Go to Users Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 