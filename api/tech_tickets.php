<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
$tech_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare('SELECT uo.*, u.username as user, u.phone as user_phone FROM user_outages uo LEFT JOIN users u ON uo.user_id = u.id WHERE uo.technician_id=? AND uo.status != "Resolved" ORDER BY uo.created_at DESC');
    $stmt->execute([$tech_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? 0);
    $status = $data['status'] ?? '';
    $notes = $data['notes'] ?? '';
    if (!$id || !in_array($status, ['InProgress','Resolved','NeedsAction'])) {
        echo json_encode(['error'=>'Invalid input']);
        exit;
    }
    $stmt = $pdo->prepare('UPDATE user_outages SET status=?, feedback=? WHERE id=? AND technician_id=?');
    $stmt->execute([$status, $notes, $id, $tech_id]);
    echo json_encode(['success'=>true]);
    exit;
}
echo json_encode(['error'=>'Invalid request']); 