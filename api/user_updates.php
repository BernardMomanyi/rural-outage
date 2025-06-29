<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = intval($data['user_id'] ?? 0);
    $type = $data['type'] ?? '';
    if (!$user_id || !$type) {
        echo json_encode(['error'=>'Invalid input']);
        exit;
    }
    $stmt = $pdo->prepare('INSERT INTO user_updates (user_id, type) VALUES (?, ?)');
    $stmt->execute([$user_id, $type]);
    echo json_encode(['success'=>true]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    $stmt = $pdo->query('SELECT uu.*, u.username FROM user_updates uu LEFT JOIN users u ON uu.user_id = u.id ORDER BY uu.created_at DESC');
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
echo json_encode(['error'=>'Invalid request']); 