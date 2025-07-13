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

echo "<h2>🔧 Quick Ticket Assignment Test</h2>";

try {
    // Step 1: Check if technicians exist
    echo "<h3>Step 1: Checking Technicians</h3>";
    $stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE role = 'technician'");
    $stmt->execute();
    $technicians = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($technicians)) {
        echo "<p style='color: red;'>❌ No technicians found! Create some technicians first.</p>";
        echo "<p><a href='users.php'>Go to User Management</a></p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Found " . count($technicians) . " technicians:</p>";
    foreach ($technicians as $tech) {
        echo "<p>- {$tech['username']} ({$tech['email']})</p>";
    }
    
    // Step 2: Check if unassigned tickets exist
    echo "<h3>Step 2: Checking Unassigned Tickets</h3>";
    $stmt = $pdo->prepare("SELECT id, ticket_number, subject, status FROM tickets WHERE assigned_technician_id IS NULL AND status = 'pending' LIMIT 5");
    $stmt->execute();
    $unassigned = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($unassigned)) {
        echo "<p style='color: orange;'>⚠️ No unassigned pending tickets found.</p>";
        echo "<p><a href='submit_ticket.php'>Create a test ticket</a></p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Found " . count($unassigned) . " unassigned tickets:</p>";
    foreach ($unassigned as $ticket) {
        echo "<p>- {$ticket['ticket_number']}: {$ticket['subject']}</p>";
    }
    
    // Step 3: Test assignment
    echo "<h3>Step 3: Testing Assignment</h3>";
    $ticket = $unassigned[0];
    $technician = $technicians[0];
    
    echo "<p>🎯 Testing assignment of ticket '{$ticket['ticket_number']}' to technician '{$technician['username']}'</p>";
    
    // Update the ticket
    $stmt = $pdo->prepare("UPDATE tickets SET assigned_technician_id = ?, assigned_technician_name = ?, assigned_technician_email = ?, status = 'assigned' WHERE id = ?");
    $result = $stmt->execute([$technician['id'], $technician['username'], $technician['email'], $ticket['id']]);
    
    if ($result) {
        echo "<p style='color: green;'>✅ Assignment successful!</p>";
        
        // Verify the assignment
        $stmt = $pdo->prepare("SELECT id, ticket_number, assigned_technician_id, assigned_technician_name, status FROM tickets WHERE id = ?");
        $stmt->execute([$ticket['id']]);
        $updated = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p><strong>Verification:</strong></p>";
        echo "<p>- Ticket: {$updated['ticket_number']}</p>";
        echo "<p>- Assigned to ID: {$updated['assigned_technician_id']}</p>";
        echo "<p>- Assigned to Name: {$updated['assigned_technician_name']}</p>";
        echo "<p>- Status: {$updated['status']}</p>";
        
        // Test technician view
        echo "<h3>Step 4: Testing Technician View</h3>";
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tickets WHERE assigned_technician_id = ?");
        $stmt->execute([$technician['id']]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        echo "<p style='color: green;'>✅ Technician {$technician['username']} now has $count assigned tickets</p>";
        
        echo "<h3>🎉 Test Complete!</h3>";
        echo "<p>If you see the green checkmarks above, the assignment system is working correctly.</p>";
        echo "<p><a href='admin_tickets.php'>Go to Admin Tickets</a> | <a href='technician_tickets.php'>Go to Technician Tickets</a></p>";
        
    } else {
        echo "<p style='color: red;'>❌ Assignment failed!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 