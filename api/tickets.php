<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query('SELECT uo.*, u.username, u.phone as user_phone, t.username as technician, t.phone as technician_phone FROM user_outages uo LEFT JOIN users u ON uo.user_id=u.id LEFT JOIN users t ON uo.technician_id=t.id WHERE uo.status IN ("Submitted","Assigned") ORDER BY uo.created_at DESC');
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? 0);
    $status = $data['status'] ?? '';
    $technician_id = isset($data['technician_id']) ? intval($data['technician_id']) : null;
    if (!$id || !in_array($status, ['Assigned','Rejected'])) {
        echo json_encode(['error'=>'Invalid input']);
        exit;
    }
    if ($technician_id) {
        $stmt = $pdo->prepare('UPDATE user_outages SET status=?, technician_id=? WHERE id=?');
        $stmt->execute([$status, $technician_id, $id]);
    } else {
        $stmt = $pdo->prepare('UPDATE user_outages SET status=? WHERE id=?');
        $stmt->execute([$status, $id]);
    }
    echo json_encode(['success'=>true]);
    exit;
}
echo json_encode(['error'=>'Invalid request']); 