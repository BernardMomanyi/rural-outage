<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
// Simulate reports: last 10 substations with High or Medium risk
$stmt = $pdo->prepare("SELECT id, name, county, risk, status, latitude, longitude FROM substations WHERE risk IN ('High','Medium') ORDER BY id DESC LIMIT 10");
$stmt->execute();
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)); 