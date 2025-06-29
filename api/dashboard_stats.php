<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
// Total substations
$total = $pdo->query('SELECT COUNT(*) FROM substations')->fetchColumn();
// Outage predictions: count of High risk
$predictions = $pdo->query("SELECT COUNT(*) FROM substations WHERE risk='High'")->fetchColumn();
// Critical alerts: count of Offline & High risk
$critical = $pdo->query("SELECT COUNT(*) FROM substations WHERE status='Offline' AND risk='High'")->fetchColumn();
echo json_encode([
    'totalSubstations' => (int)$total,
    'predictionsCount' => (int)$predictions,
    'criticalAlerts' => (int)$critical
]); 