<?php
// Run this script once to migrate old outage tickets to the outages table
require_once 'db.php';

// Find all tickets with category = 'outage' that are not already in outages
$stmt = $pdo->query("SELECT t.id, t.location, t.created_at, t.description FROM tickets t WHERE t.category = 'outage'");
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$migrated = 0;
$skipped = [];
foreach ($tickets as $ticket) {
    // Try to find substation by name
    if (!$ticket['location']) {
        $skipped[] = '(empty location)';
        continue;
    }
    $subStmt = $pdo->prepare('SELECT id FROM substations WHERE name = ? LIMIT 1');
    $subStmt->execute([$ticket['location']]);
    $substation = $subStmt->fetch(PDO::FETCH_ASSOC);
    if ($substation) {
        // Check if already exists in outages
        $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM outages WHERE substation_id = ? AND start_time = ?');
        $checkStmt->execute([$substation['id'], $ticket['created_at']]);
        if ($checkStmt->fetchColumn() == 0) {
            // Insert into outages
            $insStmt = $pdo->prepare('INSERT INTO outages (substation_id, start_time, description) VALUES (?, ?, ?)');
            $insStmt->execute([$substation['id'], $ticket['created_at'], $ticket['description']]);
            $migrated++;
        }
    } else {
        $skipped[] = $ticket['location'];
    }
}

// Output summary
header('Content-Type: text/plain');
echo "Migration complete.\n";
echo "Migrated: $migrated outage tickets to outages table.\n";
if (!empty($skipped)) {
    echo "Skipped (no matching substation):\n";
    foreach (array_unique($skipped) as $loc) {
        echo " - $loc\n";
    }
} 