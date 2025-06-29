<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
// Create uploads table if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    status VARCHAR(50) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
// GET: List all uploads
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query('SELECT * FROM uploads ORDER BY uploaded_at DESC');
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
// POST: Add new upload record
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['filename']) || empty($data['status'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing fields']);
        exit;
    }
    $stmt = $pdo->prepare('INSERT INTO uploads (filename, status) VALUES (?, ?)');
    $stmt->execute([$data['filename'], $data['status']]);
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    exit;
}
http_response_code(405);
echo json_encode(['error' => 'Method Not Allowed']); 