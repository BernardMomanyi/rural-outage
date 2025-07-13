<?php
session_start();
require_once 'db.php';

// Test technician tickets functionality
echo "<h2>Technician Tickets Debug</h2>";

// Check if user is logged in as technician
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
    echo "<p style='color: red;'>❌ Not logged in as technician</p>";
    echo "<p>Current session: " . json_encode($_SESSION) . "</p>";
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

echo "<p>✅ Logged in as technician</p>";
echo "<p>User ID: $user_id</p>";
echo "<p>Role: $role</p>";

// Check tickets table structure
echo "<h3>Database Structure</h3>";
try {
    $stmt = $pdo->prepare("DESCRIBE tickets");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking table structure: " . $e->getMessage() . "</p>";
}

// Check all tickets
echo "<h3>All Tickets</h3>";
try {
    $stmt = $pdo->prepare("SELECT id, ticket_number, subject, status, assigned_technician_id, assigned_technician_name FROM tickets ORDER BY created_at DESC LIMIT 10");
    $stmt->execute();
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Ticket #</th><th>Subject</th><th>Status</th><th>Assigned Tech ID</th><th>Assigned Tech Name</th></tr>";
    foreach ($tickets as $ticket) {
        $highlight = ($ticket['assigned_technician_id'] == $user_id) ? 'background-color: yellow;' : '';
        echo "<tr style='$highlight'>";
        echo "<td>{$ticket['id']}</td>";
        echo "<td>{$ticket['ticket_number']}</td>";
        echo "<td>{$ticket['subject']}</td>";
        echo "<td>{$ticket['status']}</td>";
        echo "<td>{$ticket['assigned_technician_id']}</td>";
        echo "<td>{$ticket['assigned_technician_name']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking tickets: " . $e->getMessage() . "</p>";
}

// Check tickets assigned to this technician
echo "<h3>Tickets Assigned to This Technician</h3>";
try {
    $stmt = $pdo->prepare("SELECT id, ticket_number, subject, status, priority, created_at FROM tickets WHERE assigned_technician_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $assigned_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($assigned_tickets)) {
        echo "<p style='color: orange;'>⚠️ No tickets assigned to technician ID: $user_id</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Ticket #</th><th>Subject</th><th>Status</th><th>Priority</th><th>Created</th></tr>";
        foreach ($assigned_tickets as $ticket) {
            echo "<tr>";
            echo "<td>{$ticket['id']}</td>";
            echo "<td>{$ticket['ticket_number']}</td>";
            echo "<td>{$ticket['subject']}</td>";
            echo "<td>{$ticket['status']}</td>";
            echo "<td>{$ticket['priority']}</td>";
            echo "<td>{$ticket['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking assigned tickets: " . $e->getMessage() . "</p>";
}

// Test the API endpoint
echo "<h3>API Test</h3>";
echo "<p>Testing API endpoint: api/tickets.php</p>";

// Simulate the API call
$_GET['action'] = 'get_users';
$role = 'technician';
$user_id = $_SESSION['user_id'];

$where_conditions = [];
$params = [];

// Filter by role (technician)
$where_conditions[] = 'assigned_technician_id = ?';
$params[] = $user_id;

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
$sql = "SELECT * FROM tickets $where_clause ORDER BY created_at DESC";

echo "<p><strong>SQL Query:</strong> $sql</p>";
echo "<p><strong>Parameters:</strong> " . json_encode($params) . "</p>";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Results:</strong> " . count($results) . " tickets found</p>";
    if (!empty($results)) {
        echo "<pre>" . json_encode($results, JSON_PRETTY_PRINT) . "</pre>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ API test error: " . $e->getMessage() . "</p>";
}

echo "<h3>Session Info</h3>";
echo "<pre>" . json_encode($_SESSION, JSON_PRETTY_PRINT) . "</pre>";
?> 