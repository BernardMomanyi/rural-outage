<?php
session_start();
require_once 'db.php';

// Ensure we're admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "Please login as admin first";
    exit;
}

try {
    // Add sample contact messages
    $sample_messages = [
        ['John Doe', 'john@example.com', 'The system is working great! Thanks for the improvements.', 'new'],
        ['Jane Smith', 'jane@example.com', 'I noticed a small issue with the outage reporting. Can you look into it?', 'new'],
        ['Mike Johnson', 'mike@example.com', 'The new notification feature is very helpful.', 'read'],
        ['Sarah Wilson', 'sarah@example.com', 'Could you add more detailed outage information?', 'replied']
    ];
    
    $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, message, status) VALUES (?, ?, ?, ?)');
    
    foreach ($sample_messages as $msg) {
        $stmt->execute($msg);
    }
    
    // Add sample notifications
    $sample_notifications = [
        ['System maintenance scheduled for tonight at 10 PM', 'all', 'maintenance', 'high'],
        ['New outage reporting feature is now available', 'all', 'update', 'normal'],
        ['Emergency: Power outage in downtown area', 'admin', 'error', 'urgent'],
        ['Weekly system backup completed successfully', 'admin', 'success', 'low']
    ];
    
    $stmt = $pdo->prepare('INSERT INTO notifications (message, target_role, type, priority) VALUES (?, ?, ?, ?)');
    
    foreach ($sample_notifications as $notif) {
        $stmt->execute($notif);
    }
    
    echo "Sample data added successfully!<br>";
    echo "Added " . count($sample_messages) . " contact messages<br>";
    echo "Added " . count($sample_notifications) . " notifications<br>";
    echo "<a href='communication_center.php'>Go to Communication Center</a>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 