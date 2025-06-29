<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');
// Handle suggestions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['suggestion'])) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    $suggestion = trim($_POST['suggestion']);
    if (!$suggestion) {
        echo json_encode(['error'=>'Suggestion required']);
        exit;
    }
    $stmt = $pdo->prepare('INSERT INTO suggestions (technician_id, suggestion) VALUES (?, ?)');
    $stmt->execute([$_SESSION['user_id'], $suggestion]);
    echo json_encode(['success'=>true]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? 0);
    if (!$id) {
        echo json_encode(['error'=>'Invalid id']);
        exit;
    }
    $stmt = $pdo->prepare('DELETE FROM suggestions WHERE id=?');
    $stmt->execute([$id]);
    echo json_encode(['success'=>true]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['suggestions'])) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    $stmt = $pdo->query('SELECT s.*, t.username as technician FROM suggestions s LEFT JOIN users t ON s.technician_id = t.id ORDER BY s.created_at DESC');
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    $msg = trim($data['message'] ?? '');
    if (!$msg) {
        echo json_encode(['error'=>'Message required']);
        exit;
    }
    $stmt = $pdo->prepare('INSERT INTO notifications (message) VALUES (?)');
    $stmt->execute([$msg]);
    echo json_encode(['success'=>true]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([]);
        exit;
    }
    $stmt = $pdo->query('SELECT * FROM notifications ORDER BY created_at DESC LIMIT 10');
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
echo json_encode(['error'=>'Invalid request']); 