<?php
session_start();
require_once 'db.php';

// Only allow admin to run this script
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die('Access denied. Admin only.');
}

echo "<h1>🔔 Adding Sample Notifications</h1>";

try {
    // Sample notifications data
    $sampleNotifications = [
        [
            'message' => 'System maintenance scheduled for tonight at 10 PM. Brief service interruption expected.',
            'target_role' => 'all',
            'type' => 'maintenance',
            'priority' => 'high'
        ],
        [
            'message' => 'New outage reporting feature is now available. Users can now submit tickets directly.',
            'target_role' => 'all',
            'type' => 'update',
            'priority' => 'normal'
        ],
        [
            'message' => 'Emergency: Power outage reported in downtown area. Technicians dispatched.',
            'target_role' => 'user',
            'type' => 'error',
            'priority' => 'urgent'
        ],
        [
            'message' => 'Weekly system backup completed successfully. All data is secure.',
            'target_role' => 'admin',
            'type' => 'success',
            'priority' => 'low'
        ],
        [
            'message' => 'New technician training session scheduled for next week. Check your email for details.',
            'target_role' => 'technician',
            'type' => 'info',
            'priority' => 'normal'
        ]
    ];
    
    $stmt = $pdo->prepare('INSERT INTO notifications (message, target_role, type, priority) VALUES (?, ?, ?, ?)');
    $insertedCount = 0;
    
    foreach ($sampleNotifications as $notification) {
        try {
            $stmt->execute([
                $notification['message'],
                $notification['target_role'],
                $notification['type'],
                $notification['priority']
            ]);
            $insertedCount++;
            echo "✅ Added notification: " . substr($notification['message'], 0, 50) . "...<br>";
        } catch (Exception $e) {
            echo "❌ Error adding notification: " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<br><strong>Summary:</strong><br>";
    echo "Successfully added {$insertedCount} sample notifications<br>";
    
    // Show current notification count
    $totalNotifications = $pdo->query('SELECT COUNT(*) FROM notifications')->fetchColumn();
    echo "Total notifications in database: {$totalNotifications}<br>";
    
    echo "<br><h3>🚀 Test Links:</h3>";
    echo "<ul>";
    echo "<li><a href='user_dashboard.php' target='_blank'>User Dashboard</a> - View notifications as user</li>";
    echo "<li><a href='admin_dashboard.php' target='_blank'>Admin Dashboard</a> - View as admin</li>";
    echo "<li><a href='communication_center.php' target='_blank'>Communication Center</a> - Manage notifications</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?> 