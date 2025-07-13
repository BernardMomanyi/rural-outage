<?php
require_once 'db.php';

echo "<h2>Database Connection Test</h2>";

try {
    // Test database connection
    echo "<p>✅ Database connection successful</p>";
    
    // Check if contact_messages table exists
    $tableExists = $pdo->query("SHOW TABLES LIKE 'contact_messages'")->rowCount() > 0;
    
    if ($tableExists) {
        echo "<p>✅ contact_messages table exists</p>";
        
        // Test inserting a message
        $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, message, created_at) VALUES (?, ?, ?, NOW())');
        $stmt->execute(['Test User', 'test@example.com', 'This is a test message']);
        echo "<p>✅ Test message inserted successfully</p>";
        
        // Show all messages
        $messages = $pdo->query('SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>Recent Messages:</h3>";
        foreach ($messages as $msg) {
            echo "<p><strong>{$msg['name']}</strong> ({$msg['email']}) - {$msg['created_at']}<br>";
            echo "Message: {$msg['message']}</p>";
        }
        
    } else {
        echo "<p>❌ contact_messages table does not exist</p>";
        echo "<p>Please run this SQL in phpMyAdmin:</p>";
        echo "<pre>";
        echo "USE rural_outage;\n\n";
        echo "CREATE TABLE IF NOT EXISTS contact_messages (\n";
        echo "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
        echo "    name VARCHAR(100) NOT NULL,\n";
        echo "    email VARCHAR(100) NOT NULL,\n";
        echo "    message TEXT NOT NULL,\n";
        echo "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
        echo "    status ENUM('new', 'read', 'replied') DEFAULT 'new'\n";
        echo ");";
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?> 