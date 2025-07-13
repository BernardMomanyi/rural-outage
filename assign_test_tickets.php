<?php
session_start();
require_once 'db.php';

// Only allow admin to run this
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "<h2>❌ Access Denied</h2>";
    echo "<p>Only admins can assign test tickets.</p>";
    exit;
}

echo "<h2>🎯 Assign Test Tickets to Technicians</h2>";

// Get all technicians
try {
    $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE role = 'technician'");
    $stmt->execute();
    $technicians = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($technicians)) {
        echo "<p style='color: red;'>❌ No technicians found in the system!</p>";
        echo "<p>Please create some technician accounts first.</p>";
        exit;
    }
    
    echo "<h3>Available Technicians:</h3>";
    echo "<ul>";
    foreach ($technicians as $tech) {
        echo "<li>ID: {$tech['id']} - {$tech['username']} ({$tech['email']})</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error loading technicians: " . $e->getMessage() . "</p>";
    exit;
}

// Get unassigned tickets
try {
    $stmt = $pdo->prepare("SELECT id, ticket_number, subject, status FROM tickets WHERE assigned_technician_id IS NULL AND status = 'pending' ORDER BY created_at DESC LIMIT 10");
    $stmt->execute();
    $unassigned_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($unassigned_tickets)) {
        echo "<p style='color: orange;'>⚠️ No unassigned pending tickets found!</p>";
        echo "<p>Creating some test tickets first...</p>";
        
        // Create some test tickets
        $test_tickets = [
            ['subject' => 'Power outage in downtown area', 'description' => 'Complete power loss affecting 500 customers', 'priority' => 'urgent', 'category' => 'outage'],
            ['subject' => 'Billing inquiry from customer', 'description' => 'Customer questioning recent bill charges', 'priority' => 'medium', 'category' => 'billing'],
            ['subject' => 'Service upgrade request', 'description' => 'Customer wants to upgrade to premium plan', 'priority' => 'low', 'category' => 'service'],
            ['subject' => 'Technical support needed', 'description' => 'Internet connection issues at customer location', 'priority' => 'high', 'category' => 'technical'],
            ['subject' => 'General inquiry', 'description' => 'Customer has questions about service coverage', 'priority' => 'low', 'category' => 'general']
        ];
        
        foreach ($test_tickets as $ticket) {
            $ticket_number = 'TKT' . date('Ym') . strtoupper(substr(md5(uniqid()), 0, 6));
            $stmt = $pdo->prepare("INSERT INTO tickets (ticket_number, user_id, user_name, user_email, subject, description, priority, category, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $ticket_number,
                1, // Default user ID
                'Test User',
                'test@example.com',
                $ticket['subject'],
                $ticket['description'],
                $ticket['priority'],
                $ticket['category'],
                'pending'
            ]);
        }
        
        echo "<p style='color: green;'>✅ Created 5 test tickets!</p>";
        
        // Reload unassigned tickets
        $stmt = $pdo->prepare("SELECT id, ticket_number, subject, status FROM tickets WHERE assigned_technician_id IS NULL AND status = 'pending' ORDER BY created_at DESC LIMIT 10");
        $stmt->execute();
        $unassigned_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo "<h3>Unassigned Tickets:</h3>";
    echo "<ul>";
    foreach ($unassigned_tickets as $ticket) {
        echo "<li>ID: {$ticket['id']} - {$ticket['ticket_number']} - {$ticket['subject']} ({$ticket['status']})</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error loading tickets: " . $e->getMessage() . "</p>";
    exit;
}

// Assign tickets to technicians
if (!empty($unassigned_tickets) && !empty($technicians)) {
    echo "<h3>🎯 Assigning Tickets...</h3>";
    
    $assigned_count = 0;
    $tech_index = 0;
    
    foreach ($unassigned_tickets as $ticket) {
        $technician = $technicians[$tech_index % count($technicians)];
        
        try {
            $stmt = $pdo->prepare("UPDATE tickets SET assigned_technician_id = ?, assigned_technician_name = ?, assigned_technician_email = ?, status = 'assigned' WHERE id = ?");
            $stmt->execute([
                $technician['id'],
                $technician['username'],
                $technician['email'],
                $ticket['id']
            ]);
            
            echo "<p style='color: green;'>✅ Assigned ticket {$ticket['ticket_number']} to {$technician['username']}</p>";
            $assigned_count++;
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error assigning ticket {$ticket['ticket_number']}: " . $e->getMessage() . "</p>";
        }
        
        $tech_index++;
    }
    
    echo "<h3>🎉 Assignment Complete!</h3>";
    echo "<p>Successfully assigned $assigned_count tickets to technicians.</p>";
    
    // Show current assignments
    echo "<h3>Current Assignments:</h3>";
    try {
        $stmt = $pdo->prepare("
            SELECT t.id, t.ticket_number, t.subject, t.status, t.assigned_technician_name, u.username 
            FROM tickets t 
            LEFT JOIN users u ON t.assigned_technician_id = u.id 
            WHERE t.assigned_technician_id IS NOT NULL 
            ORDER BY t.created_at DESC
        ");
        $stmt->execute();
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; margin-top: 1rem;'>";
        echo "<tr><th>Ticket #</th><th>Subject</th><th>Status</th><th>Assigned To</th></tr>";
        foreach ($assignments as $assignment) {
            echo "<tr>";
            echo "<td>{$assignment['ticket_number']}</td>";
            echo "<td>{$assignment['subject']}</td>";
            echo "<td>{$assignment['status']}</td>";
            echo "<td>{$assignment['assigned_technician_name']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error showing assignments: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "<p style='color: orange;'>⚠️ No tickets or technicians available for assignment.</p>";
}

echo "<hr>";
echo "<h3>🔧 Next Steps:</h3>";
echo "<ol>";
echo "<li>Login as a technician</li>";
echo "<li>Go to 'My Tickets' page</li>";
echo "<li>You should now see assigned tickets</li>";
echo "</ol>";

echo "<p><a href='admin_tickets.php' style='background: #2563eb; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px;'>📋 Go to Admin Tickets</a></p>";
echo "<p><a href='technician_tickets.php' style='background: #22c55e; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px;'>🔧 Go to Technician Tickets</a></p>";
?> 