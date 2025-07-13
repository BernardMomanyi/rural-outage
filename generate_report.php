<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'technician'])) {
  header('Location: login.php');
  exit;
}
require_once 'db.php';

// Fetch all outages (no status column)
$stmt = $pdo->query('SELECT o.id, s.name AS substation, o.start_time, o.end_time, o.description FROM outages o LEFT JOIN substations s ON o.substation_id = s.id ORDER BY o.start_time DESC');
$outages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare CSV
$csvDir = __DIR__ . '/uploads/reports/';
if (!is_dir($csvDir)) mkdir($csvDir, 0777, true);
$filename = 'outage_report_' . date('Ymd_His') . '.csv';
$filepath = $csvDir . $filename;
$fp = fopen($filepath, 'w');
fputcsv($fp, ['Outage ID', 'Substation', 'Start Time', 'End Time', 'Description']);
foreach ($outages as $row) {
  fputcsv($fp, [$row['id'], $row['substation'], $row['start_time'], $row['end_time'], $row['description']]);
}
fclose($fp);

// Store in reports table
$publicPath = 'uploads/reports/' . $filename;
$stmt = $pdo->prepare('INSERT INTO reports (name, file_path) VALUES (?, ?)');
$stmt->execute(['Outage Report ' . date('Y-m-d H:i'), $publicPath]);

header('Location: reports.php?msg=report_generated');
exit; 