<?php
session_start();
require_once 'db.php';

echo "<h2>🔧 Technician System Check</h2>";

// Check if there are any technicians
try {
    $stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE role = 'technician'");
    $stmt->execute();
    $technicians = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Current Technicians:</h3>";
    if (empty($technicians)) {
        echo "<p style='color: red;'>❌ No technicians found in the system!</p>";
        echo "<p>You need to create technician accounts first.</p>";
        
        // Show how to create a technician
        echo "<h3>How to Create a Technician:</h3>";
        echo "<ol>";
        echo "<li>Go to <a href='users.php'>User Management</a></li>";
        echo "<li>Click 'Add New User'</li>";
        echo "<li>Set Role to 'technician'</li>";
        echo "<li>Fill in username, email, and password</li>";
        echo "<li>Save the user</li>";
        echo "</ol>";
        
        // Quick create technician form
        echo "<h3>Quick Create Technician:</h3>";
        echo "<form method='post' action=''>";
        echo "<p><label>Username: <input type='text' name='username' required></label></p>";
        echo "<p><label>Email: <input type='email' name='email' required></label></p>";
        echo "<p><label>Password: <input type='password' name='password' required></label></p>";
        echo "<input type='hidden' name='action' value='create_technician'>";
        echo "<button type='submit' style='background: #22c55e; color: white; padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer;'>Create Technician</button>";
        echo "</form>";
        
    } else {
        echo "<p style='color: green;'>✅ Found " . count($technicians) . " technician(s):</p>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>";
        foreach ($technicians as $tech) {
            echo "<tr>";
            echo "<td>{$tech['id']}</td>";
            echo "<td>{$tech['username']}</td>";
            echo "<td>{$tech['email']}</td>";
            echo "<td>{$tech['role']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<p><a href='assign_test_tickets.php' style='background: #2563eb; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px;'>🎯 Assign Test Tickets</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking technicians: " . $e->getMessage() . "</p>";
}

// Handle technician creation
if (isset($_POST['action']) && $_POST['action'] === 'create_technician') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($email) || empty($password)) {
        echo "<p style='color: red;'>❌ All fields are required!</p>";
    } else {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                echo "<p style='color: red;'>❌ Username already exists!</p>";
            } else {
                // Create technician
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'technician')");
                $stmt->execute([$username, $email, $hashed_password]);
                
                echo "<p style='color: green;'>✅ Technician created successfully!</p>";
                echo "<p>Username: $username</p>";
                echo "<p>Email: $email</p>";
                echo "<p>Role: technician</p>";
                
                // Redirect to refresh the page
                echo "<script>setTimeout(function(){ window.location.reload(); }, 2000);</script>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error creating technician: " . $e->getMessage() . "</p>";
        }
    }
}

// Check tickets table
echo "<h3>Ticket System Check:</h3>";
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tickets");
    $stmt->execute();
    $total_tickets = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as assigned FROM tickets WHERE assigned_technician_id IS NOT NULL");
    $stmt->execute();
    $assigned_tickets = $stmt->fetch(PDO::FETCH_ASSOC)['assigned'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as pending FROM tickets WHERE status = 'pending'");
    $stmt->execute();
    $pending_tickets = $stmt->fetch(PDO::FETCH_ASSOC)['pending'];
    
    echo "<p>📊 Total Tickets: $total_tickets</p>";
    echo "<p>👤 Assigned Tickets: $assigned_tickets</p>";
    echo "<p>⏳ Pending Tickets: $pending_tickets</p>";
    
    if ($total_tickets == 0) {
        echo "<p style='color: orange;'>⚠️ No tickets in the system!</p>";
        echo "<p><a href='submit_ticket.php'>Create some test tickets</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking tickets: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🔧 Quick Actions:</h3>";
echo "<ul>";
echo "<li><a href='users.php'>📋 Manage Users</a></li>";
echo "<li><a href='submit_ticket.php'>📝 Create Test Ticket</a></li>";
echo "<li><a href='admin_tickets.php'>🎯 Admin Ticket Management</a></li>";
echo "<li><a href='technician_tickets.php'>🔧 Technician Tickets</a></li>";
echo "</ul>";
?> 