<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    if ($action === 'all_substations') {
        // Get all substations
        $stmt = $pdo->prepare('SELECT id, name, location FROM substations ORDER BY name');
        $stmt->execute();
        $subs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'substations' => $subs]);
        exit;
    } elseif ($action === 'technician_substations' && isset($_GET['technician_id'])) {
        $tech_id = intval($_GET['technician_id']);
        $stmt = $pdo->prepare('SELECT substation_id FROM technician_substations WHERE technician_id = ?');
        $stmt->execute([$tech_id]);
        $assigned = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode(['success' => true, 'assigned' => $assigned]);
        exit;
    }
    echo json_encode(['error' => 'Invalid action']);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $technician_id = intval($input['technician_id'] ?? 0);
    $substation_ids = $input['substation_ids'] ?? [];
    if (!$technician_id || !is_array($substation_ids)) {
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }
    // Remove all current assignments
    $stmt = $pdo->prepare('DELETE FROM technician_substations WHERE technician_id = ?');
    $stmt->execute([$technician_id]);
    // Insert new assignments
    $inserted = 0;
    foreach ($substation_ids as $sid) {
        $stmt = $pdo->prepare('INSERT IGNORE INTO technician_substations (technician_id, substation_id) VALUES (?, ?)');
        if ($stmt->execute([$technician_id, $sid])) {
            $inserted++;
        }
    }
    echo json_encode(['success' => true, 'assigned_count' => $inserted]);
    exit;
}

echo json_encode(['error' => 'Invalid request']); 