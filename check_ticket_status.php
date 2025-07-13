<?php
session_start();
require_once 'db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "<h2>🔒 Admin Access Required</h2>";
    echo "<p>Please log in as admin to run this test.</p>";
    echo "<p><a href='login.php'>Login</a></p>";
    exit;
}

echo "<h2>🔍 Checking Ticket Statuses</h2>";

try {
    // Get all tickets with their status
    $stmt = $pdo->prepare("SELECT id, ticket_number, subject, status, assigned_technician_id, assigned_technician_name FROM tickets ORDER BY created_at DESC LIMIT 20");
    $stmt->execute();
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($tickets)) {
        echo "<p style='color: orange;'>⚠️ No tickets found in the system.</p>";
        echo "<p><a href='submit_ticket.php'>Create a test ticket</a></p>";
        exit;
    }
    
    echo "<h3>Current Tickets:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f3f4f6;'>";
    echo "<th style='padding: 8px;'>Ticket #</th>";
    echo "<th style='padding: 8px;'>Subject</th>";
    echo "<th style='padding: 8px;'>Status</th>";
    echo "<th style='padding: 8px;'>Assigned To</th>";
    echo "<th style='padding: 8px;'>Should Show Assign Button?</th>";
    echo "</tr>";
    
    foreach ($tickets as $ticket) {
        $shouldShowAssign = ($ticket['status'] === 'pending' && !$ticket['assigned_technician_id']) ? 'YES' : 'NO';
        $statusColor = $ticket['status'] === 'pending' ? 'green' : 'orange';
        
        echo "<tr>";
        echo "<td style='padding: 8px;'>{$ticket['ticket_number']}</td>";
        echo "<td style='padding: 8px;'>{$ticket['subject']}</td>";
        echo "<td style='padding: 8px; color: $statusColor;'><strong>{$ticket['status']}</strong></td>";
        echo "<td style='padding: 8px;'>{$ticket['assigned_technician_name'] ?: 'None'}</td>";
        echo "<td style='padding: 8px; color: " . ($shouldShowAssign === 'YES' ? 'green' : 'red') . ";'><strong>$shouldShowAssign</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Count by status
    $stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM tickets GROUP BY status");
    $stmt->execute();
    $statusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Status Summary:</h3>";
    foreach ($statusCounts as $status) {
        echo "<p>- <strong>{$status['status']}:</strong> {$status['count']} tickets</p>";
    }
    
    // Check for pending unassigned tickets
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tickets WHERE status = 'pending' AND assigned_technician_id IS NULL");
    $stmt->execute();
    $pendingUnassigned = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "<h3>🎯 Assignment Status:</h3>";
    if ($pendingUnassigned > 0) {
        echo "<p style='color: green;'>✅ Found $pendingUnassigned pending unassigned tickets - Assignment buttons SHOULD be visible</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ No pending unassigned tickets found - Assignment buttons will NOT be visible</p>";
        echo "<p>To see assignment buttons, you need tickets with status = 'pending' and no assigned technician.</p>";
    }
    
    echo "<h3>🔧 Quick Fix Options:</h3>";
    echo "<p><a href='create_test_tickets.php' style='background: #2563eb; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>Create Test Tickets</a></p>";
    echo "<p><a href='reset_ticket_status.php' style='background: #dc2626; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>Reset All Tickets to Pending</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 