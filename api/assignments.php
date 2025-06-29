<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// GET: List assignments
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($role === 'admin') {
        // Admin: list all assignments with technician and substation info
        $stmt = $pdo->query('SELECT ts.id, ts.technician_id, ts.substation_id, u.username as technician, s.name as substation
            FROM technician_substations ts
            JOIN users u ON ts.technician_id = u.id
            JOIN substations s ON ts.substation_id = s.id');
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    } elseif ($role === 'technician') {
        // Technician: list only their assignments
        $stmt = $pdo->prepare('SELECT ts.id, ts.substation_id, s.name as substation
            FROM technician_substations ts
            JOIN substations s ON ts.substation_id = s.id
            WHERE ts.technician_id = ?');
        $stmt->execute([$user_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
}

// Only admin can assign
if ($role !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// POST: Assign substation to technician
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['technician_id']) || empty($data['substation_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing fields']);
        exit;
    }
    // Prevent duplicate assignment
    $stmt = $pdo->prepare('SELECT id FROM technician_substations WHERE technician_id=? AND substation_id=?');
    $stmt->execute([$data['technician_id'], $data['substation_id']]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Already assigned']);
        exit;
    }
    $stmt = $pdo->prepare('INSERT INTO technician_substations (technician_id, substation_id) VALUES (?, ?)');
    $stmt->execute([$data['technician_id'], $data['substation_id']]);
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    exit;
}

// DELETE: Remove assignment
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing id']);
        exit;
    }
    $stmt = $pdo->prepare('DELETE FROM technician_substations WHERE id=?');
    $stmt->execute([$data['id']]);
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method Not Allowed']); 