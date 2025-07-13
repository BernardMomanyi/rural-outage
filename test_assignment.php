<?php
session_start();
require_once 'db.php';

echo "<h2>🔧 Test Assignment Functionality</h2>";

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "<p style='color: red;'>❌ Only admins can test assignments</p>";
    exit;
}

// Test database structure
echo "<h3>1. Database Structure Check</h3>";
try {
    $stmt = $pdo->prepare("DESCRIBE tickets");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $required_columns = ['assigned_technician_id', 'assigned_technician_name', 'assigned_technician_phone', 'assigned_technician_email'];
    $missing_columns = [];
    
    foreach ($required_columns as $col) {
        $found = false;
        foreach ($columns as $column) {
            if ($column['Field'] === $col) {
                $found = true;
                echo "<p style='color: green;'>✅ Column '$col' exists (Type: {$column['Type']})</p>";
                break;
            }
        }
        if (!$found) {
            $missing_columns[] = $col;
            echo "<p style='color: red;'>❌ Column '$col' is missing!</p>";
        }
    }
    
    if (!empty($missing_columns)) {
        echo "<p style='color: red;'>❌ Missing columns: " . implode(', ', $missing_columns) . "</p>";
        echo "<p>You need to add these columns to the tickets table.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking table structure: " . $e->getMessage() . "</p>";
}

// Check for test data
echo "<h3>2. Test Data Check</h3>";
try {
    // Check tickets
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tickets");
    $stmt->execute();
    $ticket_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p>📋 Total tickets: $ticket_count</p>";
    
    // Check technicians
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'technician'");
    $stmt->execute();
    $tech_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p>👤 Total technicians: $tech_count</p>";
    
    // Check unassigned tickets
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tickets WHERE assigned_technician_id IS NULL");
    $stmt->execute();
    $unassigned_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p>⏳ Unassigned tickets: $unassigned_count</p>";
    
    // Check assigned tickets
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tickets WHERE assigned_technician_id IS NOT NULL");
    $stmt->execute();
    $assigned_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p>✅ Assigned tickets: $assigned_count</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking data: " . $e->getMessage() . "</p>";
}

// Test assignment manually
echo "<h3>3. Manual Assignment Test</h3>";
try {
    // Get first unassigned ticket
    $stmt = $pdo->prepare("SELECT id, ticket_number, subject FROM tickets WHERE assigned_technician_id IS NULL LIMIT 1");
    $stmt->execute();
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get first technician
    $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE role = 'technician' LIMIT 1");
    $stmt->execute();
    $technician = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($ticket && $technician) {
        echo "<p>🎯 Testing assignment of ticket '{$ticket['ticket_number']}' to technician '{$technician['username']}'</p>";
        
        // Perform assignment
        $stmt = $pdo->prepare("UPDATE tickets SET assigned_technician_id = ?, assigned_technician_name = ?, assigned_technician_email = ?, status = 'assigned' WHERE id = ?");
        $result = $stmt->execute([
            $technician['id'],
            $technician['username'],
            $technician['email'],
            $ticket['id']
        ]);
        
        if ($result) {
            echo "<p style='color: green;'>✅ Assignment successful! Rows affected: " . $stmt->rowCount() . "</p>";
            
            // Verify assignment
            $stmt = $pdo->prepare("SELECT id, ticket_number, assigned_technician_id, assigned_technician_name, status FROM tickets WHERE id = ?");
            $stmt->execute([$ticket['id']]);
            $updated_ticket = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<p>📋 Updated ticket data:</p>";
            echo "<pre>" . json_encode($updated_ticket, JSON_PRETTY_PRINT) . "</pre>";
            
        } else {
            echo "<p style='color: red;'>❌ Assignment failed!</p>";
        }
        
    } else {
        if (!$ticket) {
            echo "<p style='color: orange;'>⚠️ No unassigned tickets found</p>";
        }
        if (!$technician) {
            echo "<p style='color: orange;'>⚠️ No technicians found</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error testing assignment: " . $e->getMessage() . "</p>";
}

// Show current assignments
echo "<h3>4. Current Assignments</h3>";
try {
    $stmt = $pdo->prepare("
        SELECT t.id, t.ticket_number, t.subject, t.status, t.assigned_technician_id, t.assigned_technician_name
        FROM tickets t 
        WHERE t.assigned_technician_id IS NOT NULL 
        ORDER BY t.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($assignments)) {
        echo "<p style='color: orange;'>⚠️ No assigned tickets found</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Ticket #</th><th>Subject</th><th>Status</th><th>Tech ID</th><th>Tech Name</th></tr>";
        foreach ($assignments as $assignment) {
            echo "<tr>";
            echo "<td>{$assignment['ticket_number']}</td>";
            echo "<td>{$assignment['subject']}</td>";
            echo "<td>{$assignment['status']}</td>";
            echo "<td>{$assignment['assigned_technician_id']}</td>";
            echo "<td>{$assignment['assigned_technician_name']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error showing assignments: " . $e->getMessage() . "</p>";
}

// Test API endpoint
echo "<h3>5. API Endpoint Test</h3>";
echo "<p>Testing the assignment API endpoint...</p>";

// Simulate API call
$test_data = [
    'action' => 'assign',
    'ticket_id' => 1,
    'technician_id' => 1,
    'technician_name' => 'Test Technician',
    'technician_email' => 'test@example.com'
];

echo "<p>Test data: " . json_encode($test_data) . "</p>";

// Check if there are any tickets and technicians
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tickets");
    $stmt->execute();
    $tickets_exist = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'technician'");
    $stmt->execute();
    $techs_exist = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    
    if ($tickets_exist && $techs_exist) {
        echo "<p style='color: green;'>✅ Ready to test API assignment</p>";
        echo "<p>Try assigning a ticket through the admin interface and check the server logs for debugging info.</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Need tickets and technicians to test API</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking test readiness: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🔧 Next Steps:</h3>";
echo "<ol>";
echo "<li>Check the server error logs for assignment debugging info</li>";
echo "<li>Try assigning a ticket through the admin interface</li>";
echo "<li>Check if the assignment appears in the database</li>";
echo "<li>Login as technician to see if tickets appear</li>";
echo "</ol>";

echo "<p><a href='admin_tickets.php'>📋 Go to Admin Tickets</a></p>";
echo "<p><a href='technician_tickets.php'>🔧 Go to Technician Tickets</a></p>";
?> 