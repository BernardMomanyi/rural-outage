<?php
session_start();
require_once '../db.php';

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Ensure tickets table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS tickets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ticket_number VARCHAR(20) UNIQUE NOT NULL,
        user_id INT NOT NULL,
        user_name VARCHAR(100) NOT NULL,
        user_email VARCHAR(100) NOT NULL,
        user_phone VARCHAR(20),
        subject VARCHAR(200) NOT NULL,
        description TEXT NOT NULL,
        priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
        status ENUM('pending', 'assigned', 'in_progress', 'resolved', 'closed') DEFAULT 'pending',
        category ENUM('technical', 'billing', 'service', 'general', 'outage') DEFAULT 'general',
        assigned_technician_id INT NULL,
        assigned_technician_name VARCHAR(100) NULL,
        assigned_technician_phone VARCHAR(20) NULL,
        assigned_technician_email VARCHAR(100) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        resolved_at TIMESTAMP NULL,
        INDEX idx_status (status),
        INDEX idx_priority (priority),
        INDEX idx_user_id (user_id),
        INDEX idx_assigned_technician_id (assigned_technician_id),
        INDEX idx_created_at (created_at)
    )");
} catch (Exception $e) {
    // Table might already exist, continue
}

// Generate unique ticket number
function generateTicketNumber() {
    $prefix = 'TKT';
    $year = date('Y');
    $month = date('m');
    $random = strtoupper(substr(md5(uniqid()), 0, 6));
    return $prefix . $year . $month . $random;
}

// GET: Fetch tickets based on role and filters
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $role = $_SESSION['role'];
        $user_id = $_SESSION['user_id'];
        $status_filter = $_GET['status'] ?? '';
        $priority_filter = $_GET['priority'] ?? '';
        $category_filter = $_GET['category'] ?? '';
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;

        $where_conditions = [];
        $params = [];

        // Filter by role
        if ($role === 'admin') {
            // Admin can see all tickets
        } elseif ($role === 'technician') {
            // Technicians see only assigned tickets
            $where_conditions[] = 'assigned_technician_id = ?';
            $params[] = $user_id;
        } else {
            // Users see only their own tickets
            $where_conditions[] = 'user_id = ?';
            $params[] = $user_id;
        }

        // Add filters
        if ($status_filter) {
            $where_conditions[] = 'status = ?';
            $params[] = $status_filter;
        }
        if ($priority_filter) {
            $where_conditions[] = 'priority = ?';
            $params[] = $priority_filter;
        }
        if ($category_filter) {
            $where_conditions[] = 'category = ?';
            $params[] = $category_filter;
        }

        $where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        $sql = "SELECT * FROM tickets $where_clause ORDER BY 
                CASE 
                    WHEN priority = 'urgent' THEN 1
                    WHEN priority = 'high' THEN 2
                    WHEN priority = 'medium' THEN 3
                    WHEN priority = 'low' THEN 4
                END,
                created_at DESC LIMIT " . $limit;

        // Debug logging for technicians
        if ($role === 'technician') {
            error_log("Technician tickets query - User ID: $user_id, SQL: $sql, Params: " . json_encode($params));
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug logging for technicians
        if ($role === 'technician') {
            error_log("Technician tickets results - Count: " . count($results));
        }
        
        echo json_encode($results);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// POST: Create new ticket or update existing ticket
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON input');
        }

        $action = $data['action'] ?? 'create';

        if ($action === 'create') {
            // Create new ticket
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }

            $ticket_number = generateTicketNumber();
            $user_id = $_SESSION['user_id'];
            $user_name = $data['user_name'] ?? $_SESSION['username'];
            $user_email = $data['user_email'] ?? '';
            $user_phone = $data['user_phone'] ?? '';
            $subject = trim($data['subject'] ?? '');
            $description = trim($data['description'] ?? '');
            $priority = $data['priority'] ?? 'medium';
            $category = $data['category'] ?? 'general';

            if (empty($subject) || empty($description)) {
                echo json_encode(['error' => 'Subject and description are required']);
                exit;
            }

            $stmt = $pdo->prepare('INSERT INTO tickets (ticket_number, user_id, user_name, user_email, user_phone, subject, description, priority, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$ticket_number, $user_id, $user_name, $user_email, $user_phone, $subject, $description, $priority, $category]);

            echo json_encode([
                'success' => true,
                'message' => 'Ticket created successfully',
                'ticket_number' => $ticket_number,
                'ticket_id' => $pdo->lastInsertId()
            ]);

        } elseif ($action === 'assign' || $action === 'reassign') {
            // Assign or reassign ticket to technician (admin only)
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }

            $ticket_id = intval($data['ticket_id'] ?? 0);
            $technician_id = intval($data['technician_id'] ?? 0);
            $technician_name = $data['technician_name'] ?? '';
            $technician_phone = $data['technician_phone'] ?? '';
            $technician_email = $data['technician_email'] ?? '';

            // Debug logging
            error_log("Assignment request - Ticket ID: $ticket_id, Technician ID: $technician_id, Name: $technician_name");

            if (!$ticket_id || !$technician_id) {
                echo json_encode(['error' => 'Ticket ID and technician ID are required']);
                exit;
            }

            // Check if ticket exists
            $stmt = $pdo->prepare('SELECT id, assigned_technician_id, assigned_technician_name FROM tickets WHERE id = ?');
            $stmt->execute([$ticket_id]);
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$ticket) {
                echo json_encode(['error' => 'Ticket not found']);
                exit;
            }

            error_log("Before assignment - Ticket: " . json_encode($ticket));

            // Update the ticket assignment
            $update_sql = 'UPDATE tickets SET assigned_technician_id = ?, assigned_technician_name = ?, assigned_technician_phone = ?, assigned_technician_email = ?, status = "assigned" WHERE id = ?';
            $update_params = [$technician_id, $technician_name, $technician_phone, $technician_email, $ticket_id];
            
            error_log("Update SQL: $update_sql");
            error_log("Update params: " . json_encode($update_params));
            
            $stmt = $pdo->prepare($update_sql);
            $result = $stmt->execute($update_params);
            
            error_log("Update result: " . ($result ? 'success' : 'failed'));
            error_log("Rows affected: " . $stmt->rowCount());

            // Verify the update
            $stmt = $pdo->prepare('SELECT id, assigned_technician_id, assigned_technician_name, status FROM tickets WHERE id = ?');
            $stmt->execute([$ticket_id]);
            $updated_ticket = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("After assignment - Ticket: " . json_encode($updated_ticket));

            $message = $action === 'reassign' ? 'Ticket reassigned successfully' : 'Ticket assigned successfully';
            echo json_encode(['success' => true, 'message' => $message]);

        } elseif ($action === 'update_status') {
            // Update ticket status
            $ticket_id = intval($data['ticket_id'] ?? 0);
            $status = $data['status'] ?? '';
            $user_id = $_SESSION['user_id'];
            $role = $_SESSION['role'];

            if (!$ticket_id || !$status) {
                echo json_encode(['error' => 'Ticket ID and status are required']);
                exit;
            }

            // Check permissions
            $stmt = $pdo->prepare('SELECT user_id, assigned_technician_id FROM tickets WHERE id = ?');
            $stmt->execute([$ticket_id]);
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$ticket) {
                echo json_encode(['error' => 'Ticket not found']);
                exit;
            }

            $can_update = false;
            if ($role === 'admin') {
                $can_update = true;
            } elseif ($role === 'technician' && $ticket['assigned_technician_id'] == $user_id) {
                $can_update = true;
            } elseif ($role === 'user' && $ticket['user_id'] == $user_id) {
                $can_update = true;
            }

            if (!$can_update) {
                http_response_code(403);
                echo json_encode(['error' => 'Permission denied']);
                exit;
            }

            $resolved_at = ($status === 'resolved') ? date('Y-m-d H:i:s') : null;
            $stmt = $pdo->prepare('UPDATE tickets SET status = ?, resolved_at = ? WHERE id = ?');
            $stmt->execute([$status, $resolved_at, $ticket_id]);

            echo json_encode(['success' => true, 'message' => 'Ticket status updated successfully']);

        } else {
            echo json_encode(['error' => 'Invalid action']);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    }
    exit;
}

// DELETE: Delete ticket (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON input');
        }

        $ticket_id = intval($data['ticket_id'] ?? 0);
        
        if (!$ticket_id) {
            echo json_encode(['error' => 'Ticket ID is required']);
            exit;
        }

        $stmt = $pdo->prepare('DELETE FROM tickets WHERE id = ?');
        $stmt->execute([$ticket_id]);
        echo json_encode(['success' => true, 'message' => 'Ticket deleted successfully']);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['error' => 'Invalid request method']);
?> 