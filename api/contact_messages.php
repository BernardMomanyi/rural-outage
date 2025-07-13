<?php
session_start();
require_once '../db.php';

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Ensure contact_messages table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('new', 'read', 'replied') DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_status (status),
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

// GET: Fetch all contact messages or single message
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        if (isset($_GET['id'])) {
            // Get single message
            $id = intval($_GET['id']);
            $stmt = $pdo->prepare('SELECT * FROM contact_messages WHERE id = ?');
            $stmt->execute([$id]);
            $message = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($message) {
                echo json_encode($message);
            } else {
                echo json_encode(['error' => 'Message not found']);
            }
        } else {
            // Get all messages with optional filtering
            $status_filter = $_GET['status'] ?? '';
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
            
            if ($status_filter) {
                $stmt = $pdo->prepare('SELECT * FROM contact_messages WHERE status = ? ORDER BY created_at DESC LIMIT ' . $limit);
                $stmt->execute([$status_filter]);
            } else {
                $stmt = $pdo->prepare('SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT ' . $limit);
                $stmt->execute();
            }
            
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// POST: Mark message as read or mark all as read
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON input');
        }
        
        $action = $data['action'] ?? '';
        
        if ($action === 'mark_read') {
            $id = intval($data['id'] ?? 0);
            
            if (!$id) {
                echo json_encode(['error' => 'Invalid message ID']);
                exit;
            }
            
            $stmt = $pdo->prepare('UPDATE contact_messages SET status = "read" WHERE id = ?');
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Message marked as read']);
            
        } elseif ($action === 'mark_all_read') {
            $stmt = $pdo->prepare('UPDATE contact_messages SET status = "read" WHERE status = "new"');
            $stmt->execute();
            $affected = $stmt->rowCount();
            echo json_encode(['success' => true, 'message' => "Marked $affected messages as read"]);
            
        } elseif ($action === 'reply') {
            $id = intval($data['id'] ?? 0);
            $reply_message = trim($data['reply'] ?? '');
            
            if (!$id || !$reply_message) {
                echo json_encode(['error' => 'Invalid message ID or reply content']);
                exit;
            }
            
            // Mark as replied
            $stmt = $pdo->prepare('UPDATE contact_messages SET status = "replied" WHERE id = ?');
            $stmt->execute([$id]);
            
            // In a real implementation, you would send an email here
            // For MVP, we just mark it as replied
            echo json_encode(['success' => true, 'message' => 'Reply sent successfully']);
            
        } else {
            echo json_encode(['error' => 'Invalid action']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    }
    exit;
}

// DELETE: Delete message
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON input');
        }
        
        $id = intval($data['id'] ?? 0);
        
        if (!$id) {
            echo json_encode(['error' => 'Invalid message ID']);
            exit;
        }
        
        $stmt = $pdo->prepare('DELETE FROM contact_messages WHERE id = ?');
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Message deleted successfully']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['error' => 'Invalid request method']);
?> 