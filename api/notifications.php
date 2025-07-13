<?php
session_start();
require_once '../db.php';

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Ensure notifications table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        message TEXT NOT NULL,
        target_role ENUM('all', 'admin', 'technician', 'user') DEFAULT 'all',
        type ENUM('info', 'warning', 'success', 'error', 'maintenance', 'update') DEFAULT 'info',
        priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_target_role (target_role),
        INDEX idx_type (type),
        INDEX idx_created_at (created_at)
    )");
} catch (Exception $e) {
    // Table might already exist, continue
}

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// GET: Fetch notifications
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
        $stmt = $pdo->prepare('SELECT * FROM notifications ORDER BY created_at DESC LIMIT ' . $limit);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// POST: Create new notification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON input');
        }
        
        $message = trim($data['message'] ?? '');
        $target_role = $data['target_role'] ?? 'all';
        $type = $data['type'] ?? 'info';
        $priority = $data['priority'] ?? 'normal';
        
        if (empty($message)) {
            echo json_encode(['error' => 'Message is required']);
            exit;
        }
        
        // Validate enum values
        $valid_roles = ['all', 'admin', 'technician', 'user'];
        $valid_types = ['info', 'warning', 'success', 'error', 'maintenance', 'update'];
        $valid_priorities = ['low', 'normal', 'high', 'urgent'];
        
        if (!in_array($target_role, $valid_roles)) {
            $target_role = 'all';
        }
        if (!in_array($type, $valid_types)) {
            $type = 'info';
        }
        if (!in_array($priority, $valid_priorities)) {
            $priority = 'normal';
        }
        
        // Insert notification
        $stmt = $pdo->prepare('INSERT INTO notifications (message, target_role, type, priority) VALUES (?, ?, ?, ?)');
        $stmt->execute([$message, $target_role, $type, $priority]);
        
        // Count recipients (simplified - in real app you'd count actual users)
        $recipient_count = 0;
        if ($target_role === 'all') {
            $stmt = $pdo->query('SELECT COUNT(*) FROM users');
            $recipient_count = $stmt->fetchColumn();
        } else {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE role = ?');
            $stmt->execute([$target_role]);
            $recipient_count = $stmt->fetchColumn();
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Notification sent successfully',
            'recipients' => $recipient_count,
            'notification_id' => $pdo->lastInsertId()
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['error' => 'Invalid request method']);
?> 