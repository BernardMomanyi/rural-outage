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

echo "<h2>🔄 Reset Ticket Statuses</h2>";

try {
    // Count tickets before reset
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tickets");
    $stmt->execute();
    $totalTickets = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($totalTickets == 0) {
        echo "<p style='color: orange;'>⚠️ No tickets found in the system.</p>";
        echo "<p><a href='submit_ticket.php'>Create a test ticket first</a></p>";
        exit;
    }
    
    echo "<p>Found $totalTickets tickets in the system.</p>";
    
    // Reset all tickets to pending and remove assignments
    $stmt = $pdo->prepare("UPDATE tickets SET status = 'pending', assigned_technician_id = NULL, assigned_technician_name = NULL, assigned_technician_phone = NULL, assigned_technician_email = NULL WHERE status != 'pending' OR assigned_technician_id IS NOT NULL");
    $result = $stmt->execute();
    $affectedRows = $stmt->rowCount();
    
    if ($result && $affectedRows > 0) {
        echo "<p style='color: green;'>✅ Successfully reset $affectedRows tickets to pending status!</p>";
        echo "<p>All tickets are now unassigned and ready for assignment.</p>";
        
        // Show updated status
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tickets WHERE status = 'pending' AND assigned_technician_id IS NULL");
        $stmt->execute();
        $pendingUnassigned = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        echo "<p style='color: green;'>✅ Now you have $pendingUnassigned pending unassigned tickets</p>";
        echo "<p>Assignment buttons should now be visible in the admin interface.</p>";
        
        echo "<h3>🎯 Next Steps:</h3>";
        echo "<p><a href='admin_tickets.php' style='background: #2563eb; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>Go to Admin Tickets</a></p>";
        echo "<p>You should now see 'Assign to Technician' buttons on all tickets.</p>";
        
    } else {
        echo "<p style='color: orange;'>⚠️ No tickets needed to be reset (they were already in the correct state).</p>";
        echo "<p><a href='admin_tickets.php'>Go to Admin Tickets</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 