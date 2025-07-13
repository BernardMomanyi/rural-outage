<?php
require_once 'db.php';

echo "<h2>Profile Columns Migration</h2>";
echo "<p>Adding missing profile columns to users table...</p>";

try {
    // Get current table structure
    $stmt = $pdo->query("DESCRIBE users");
    $existing_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Current columns in users table:</h3>";
    echo "<ul>";
    foreach ($existing_columns as $column) {
        echo "<li>$column</li>";
    }
    echo "</ul>";
    
    // Define the profile columns we need to add
    $profile_columns = [
        'first_name' => 'VARCHAR(50) NULL',
        'last_name' => 'VARCHAR(50) NULL',
        'department' => 'VARCHAR(100) NULL',
        'position' => 'VARCHAR(100) NULL',
        'bio' => 'TEXT NULL',
        'avatar' => 'VARCHAR(255) NULL',
        'two_factor' => "ENUM('disabled', 'enabled', 'required') DEFAULT 'disabled'",
        'last_login' => 'TIMESTAMP NULL'
    ];
    
    $added_columns = [];
    $skipped_columns = [];
    
    foreach ($profile_columns as $column_name => $column_definition) {
        if (!in_array($column_name, $existing_columns)) {
            try {
                $sql = "ALTER TABLE users ADD COLUMN $column_name $column_definition";
                $pdo->exec($sql);
                $added_columns[] = $column_name;
                echo "<p style='color: green;'>✅ Added column: $column_name</p>";
            } catch (PDOException $e) {
                echo "<p style='color: red;'>❌ Error adding column $column_name: " . $e->getMessage() . "</p>";
            }
        } else {
            $skipped_columns[] = $column_name;
            echo "<p style='color: orange;'>⚠️ Column already exists: $column_name</p>";
        }
    }
    
    // Check if status column exists (it's used in the API)
    if (!in_array('status', $existing_columns)) {
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'");
            $added_columns[] = 'status';
            echo "<p style='color: green;'>✅ Added column: status</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Error adding status column: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Status column already exists</p>";
    }
    
    // Update existing users to have default values
    if (!empty($added_columns)) {
        echo "<h3>Updating existing users with default values...</h3>";
        
        // Set default values for new columns
        $update_sql = "UPDATE users SET ";
        $updates = [];
        
        if (in_array('first_name', $added_columns)) {
            $updates[] = "first_name = username";
        }
        if (in_array('last_name', $added_columns)) {
            $updates[] = "last_name = ''";
        }
        if (in_array('department', $added_columns)) {
            $updates[] = "department = 'General'";
        }
        if (in_array('position', $added_columns)) {
            $updates[] = "position = 'User'";
        }
        if (in_array('bio', $added_columns)) {
            $updates[] = "bio = 'No bio available'";
        }
        if (in_array('avatar', $added_columns)) {
            $updates[] = "avatar = 'default-avatar.png'";
        }
        if (in_array('status', $added_columns)) {
            $updates[] = "status = 'active'";
        }
        
        if (!empty($updates)) {
            $update_sql .= implode(', ', $updates);
            $pdo->exec($update_sql);
            echo "<p style='color: green;'>✅ Updated existing users with default values</p>";
        }
    }
    
    // Show final table structure
    echo "<h3>Final table structure:</h3>";
    $stmt = $pdo->query("DESCRIBE users");
    $final_columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($final_columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "<td>{$column['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show sample user data
    echo "<h3>Sample user data:</h3>";
    $stmt = $pdo->query("SELECT id, username, email, role, status, first_name, last_name, department, position FROM users LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($users)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>First Name</th><th>Last Name</th><th>Department</th><th>Position</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['role']}</td>";
            echo "<td>{$user['status']}</td>";
            echo "<td>{$user['first_name']}</td>";
            echo "<td>{$user['last_name']}</td>";
            echo "<td>{$user['department']}</td>";
            echo "<td>{$user['position']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>No users found in database</p>";
    }
    
    echo "<h3>Migration Summary:</h3>";
    echo "<ul>";
    echo "<li>Added columns: " . implode(', ', $added_columns) . "</li>";
    echo "<li>Skipped columns: " . implode(', ', $skipped_columns) . "</li>";
    echo "</ul>";
    
    echo "<p style='color: green; font-weight: bold;'>✅ Migration completed successfully!</p>";
    echo "<p><a href='users.php'>Go to Users Page</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}
?> 