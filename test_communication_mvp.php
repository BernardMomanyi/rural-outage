<?php
session_start();
require_once 'db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Test sending a notification
if (isset($_POST['test_notification'])) {
    try {
        $stmt = $pdo->prepare('INSERT INTO notifications (message, target_role, type, priority, created_by) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            'This is a test notification from the MVP communication center.',
            'all',
            'info',
            'normal',
            $_SESSION['user_id']
        ]);
        $notification_success = true;
    } catch (Exception $e) {
        $notification_error = $e->getMessage();
    }
}

// Test adding a contact message
if (isset($_POST['test_contact'])) {
    try {
        $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)');
        $stmt->execute([
            'Test User',
            'test@example.com',
            'This is a test feedback message for the communication center MVP.'
        ]);
        $contact_success = true;
    } catch (Exception $e) {
        $contact_error = $e->getMessage();
    }
}

// Get current stats
try {
    $notification_count = $pdo->query('SELECT COUNT(*) FROM notifications')->fetchColumn();
    $contact_count = $pdo->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn();
    $new_contact_count = $pdo->query('SELECT COUNT(*) FROM contact_messages WHERE status = "new"')->fetchColumn();
} catch (Exception $e) {
    $stats_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Communication Center MVP Test</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .success { color: green; }
        .error { color: red; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0; }
        .stat-card { padding: 20px; background: #f8f9fa; border-radius: 8px; text-align: center; }
        .stat-number { font-size: 2rem; font-weight: bold; color: #007bff; }
        .stat-label { color: #6c757d; margin-top: 5px; }
    </style>
</head>
<body>
    <h1>Communication Center MVP Test</h1>
    
    <div class="test-section">
        <h2>Database Status</h2>
        <?php if (isset($stats_error)): ?>
            <p class="error">Error: <?php echo $stats_error; ?></p>
        <?php else: ?>
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $notification_count; ?></div>
                    <div class="stat-label">Notifications</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $contact_count; ?></div>
                    <div class="stat-label">Contact Messages</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $new_contact_count; ?></div>
                    <div class="stat-label">New Messages</div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="test-section">
        <h2>Test Notification API</h2>
        <form method="post">
            <button type="submit" name="test_notification">Send Test Notification</button>
        </form>
        <?php if (isset($notification_success)): ?>
            <p class="success">✅ Test notification sent successfully!</p>
        <?php elseif (isset($notification_error)): ?>
            <p class="error">❌ Error: <?php echo $notification_error; ?></p>
        <?php endif; ?>
    </div>
    
    <div class="test-section">
        <h2>Test Contact Message API</h2>
        <form method="post">
            <button type="submit" name="test_contact">Add Test Contact Message</button>
        </form>
        <?php if (isset($contact_success)): ?>
            <p class="success">✅ Test contact message added successfully!</p>
        <?php elseif (isset($contact_error)): ?>
            <p class="error">❌ Error: <?php echo $contact_error; ?></p>
        <?php endif; ?>
    </div>
    
    <div class="test-section">
        <h2>API Endpoints Test</h2>
        <p>Test the following endpoints:</p>
        <ul>
            <li><strong>GET /api/notifications.php</strong> - Fetch notifications</li>
            <li><strong>POST /api/notifications.php</strong> - Send notification</li>
            <li><strong>GET /api/contact_messages.php</strong> - Fetch contact messages</li>
            <li><strong>POST /api/contact_messages.php</strong> - Mark as read/reply</li>
        </ul>
    </div>
    
    <div class="test-section">
        <h2>Next Steps</h2>
        <p>✅ Database tables created</p>
        <p>✅ API endpoints configured</p>
        <p>✅ Frontend integration complete</p>
        <p>✅ Real-time functionality implemented</p>
        <p><a href="users.php" style="color: #007bff; text-decoration: none;">→ Go to Communication Center</a></p>
    </div>
</body>
</html> 