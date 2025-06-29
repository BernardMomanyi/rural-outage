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

// GET: List all substations
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query('SELECT * FROM substations');
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// Only admin/technician can modify
if (!in_array($role, ['admin', 'technician'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// POST: Add new substation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare('INSERT INTO substations (name, county, status, risk, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $data['name'], $data['county'], $data['status'], $data['risk'], $data['latitude'], $data['longitude']
    ]);
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    exit;
}

// PUT: Update substation
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare('UPDATE substations SET name=?, county=?, status=?, risk=?, latitude=?, longitude=? WHERE id=?');
    $stmt->execute([
        $data['name'], $data['county'], $data['status'], $data['risk'], $data['latitude'], $data['longitude'], $data['id']
    ]);
    echo json_encode(['success' => true]);
    exit;
}

// DELETE: Delete substation
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare('DELETE FROM substations WHERE id=?');
    $stmt->execute([$data['id']]);
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method Not Allowed']); 