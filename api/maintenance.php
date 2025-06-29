<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    $substation = trim($data['substation'] ?? '');
    $task = trim($data['task'] ?? '');
    $date = trim($data['date'] ?? '');
    $technician = $data['technician'] ?? null;
    if (!$substation || !$task || !$date) {
        echo json_encode(['error'=>'All fields required']);
        exit;
    }
    $stmt = $pdo->prepare('INSERT INTO maintenance (substation, task, date, technician_id) VALUES (?, ?, ?, ?)');
    $stmt->execute([$substation, $task, $date, $technician]);
    echo json_encode(['success'=>true]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([]);
        exit;
    }
    $stmt = $pdo->query('SELECT m.*, t.username as technician FROM maintenance m LEFT JOIN users t ON m.technician_id = t.id ORDER BY m.date ASC');
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
echo json_encode(['error'=>'Invalid request']); 