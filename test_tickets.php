<?php
session_start();
require_once 'db.php';

// Only allow admin to run this test
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die('Access denied. Admin only.');
}

// Sample tickets data
$sampleTickets = [
    [
        'user_id' => 1,
        'user_name' => 'John Doe',
        'user_email' => 'john@example.com',
        'user_phone' => '555-0101',
        'subject' => 'Power outage in downtown area',
        'description' => 'Complete power loss in the downtown business district. Affecting multiple buildings and traffic lights. Need immediate attention.',
        'priority' => 'urgent',
        'category' => 'outage',
        'status' => 'pending'
    ],
    [
        'user_id' => 2,
        'user_name' => 'Jane Smith',
        'user_email' => 'jane@example.com',
        'user_phone' => '555-0102',
        'subject' => 'Billing inquiry - incorrect charges',
        'description' => 'Received a bill that seems to have incorrect charges. The amount is much higher than usual and I would like this reviewed.',
        'priority' => 'medium',
        'category' => 'billing',
        'status' => 'pending'
    ],
    [
        'user_id' => 3,
        'user_name' => 'Mike Johnson',
        'user_email' => 'mike@example.com',
        'user_phone' => '555-0103',
        'subject' => 'Service upgrade request',
        'description' => 'Would like to upgrade my service to a higher tier. Currently on basic plan, interested in premium features.',
        'priority' => 'low',
        'category' => 'service',
        'status' => 'pending'
    ],
    [
        'user_id' => 1,
        'user_name' => 'John Doe',
        'user_email' => 'john@example.com',
        'user_phone' => '555-0101',
        'subject' => 'Technical issue with online portal',
        'description' => 'Unable to log into the customer portal. Getting error message "Invalid credentials" even though password is correct.',
        'priority' => 'high',
        'category' => 'technical',
        'status' => 'assigned',
        'assigned_technician_id' => 4,
        'assigned_technician_name' => 'Tech Support',
        'assigned_technician_phone' => '555-0201',
        'assigned_technician_email' => 'tech@outagesys.com'
    ],
    [
        'user_id' => 2,
        'user_name' => 'Jane Smith',
        'user_email' => 'jane@example.com',
        'user_phone' => '555-0102',
        'subject' => 'Intermittent power fluctuations',
        'description' => 'Experiencing random power flickers throughout the day. Lights dim and appliances restart. This has been happening for the past week.',
        'priority' => 'high',
        'category' => 'technical',
        'status' => 'in_progress',
        'assigned_technician_id' => 5,
        'assigned_technician_name' => 'Field Technician',
        'assigned_technician_phone' => '555-0202',
        'assigned_technician_email' => 'field@outagesys.com'
    ],
    [
        'user_id' => 3,
        'user_name' => 'Mike Johnson',
        'user_email' => 'mike@example.com',
        'user_phone' => '555-0103',
        'subject' => 'General inquiry about maintenance schedule',
        'description' => 'I would like to know when the next scheduled maintenance is planned for my area. Need to plan accordingly.',
        'priority' => 'low',
        'category' => 'general',
        'status' => 'resolved',
        'assigned_technician_id' => 4,
        'assigned_technician_name' => 'Tech Support',
        'assigned_technician_phone' => '555-0201',
        'assigned_technician_email' => 'tech@outagesys.com'
    ]
];

// Generate ticket numbers and insert tickets
$insertedCount = 0;
$errors = [];

foreach ($sampleTickets as $ticket) {
    try {
        // Generate unique ticket number
        $prefix = 'TKT';
        $year = date('Y');
        $month = date('m');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        $ticket_number = $prefix . $year . $month . $random;
        
        // Prepare SQL
        $sql = "INSERT INTO tickets (ticket_number, user_id, user_name, user_email, user_phone, subject, description, priority, category, status";
        $values = "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?";
        $params = [$ticket_number, $ticket['user_id'], $ticket['user_name'], $ticket['user_email'], $ticket['user_phone'], $ticket['subject'], $ticket['description'], $ticket['priority'], $ticket['category'], $ticket['status']];
        
        // Add technician info if assigned
        if (isset($ticket['assigned_technician_id'])) {
            $sql .= ", assigned_technician_id, assigned_technician_name, assigned_technician_phone, assigned_technician_email";
            $values .= ", ?, ?, ?, ?";
            $params[] = $ticket['assigned_technician_id'];
            $params[] = $ticket['assigned_technician_name'];
            $params[] = $ticket['assigned_technician_phone'];
            $params[] = $ticket['assigned_technician_email'];
        }
        
        $sql .= ") " . $values . ")";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $insertedCount++;
        
        echo "✓ Created ticket: {$ticket_number} - {$ticket['subject']}<br>";
        
    } catch (Exception $e) {
        $errors[] = "Error creating ticket '{$ticket['subject']}': " . $e->getMessage();
    }
}

echo "<br><strong>Test Results:</strong><br>";
echo "Successfully created {$insertedCount} sample tickets<br>";

if (!empty($errors)) {
    echo "<br><strong>Errors:</strong><br>";
    foreach ($errors as $error) {
        echo "✗ {$error}<br>";
    }
}

echo "<br><a href='admin_tickets.php'>View Tickets in Admin Panel</a><br>";
echo "<a href='my_tickets.php'>View Tickets as User</a><br>";
echo "<a href='technician_tickets.php'>View Tickets as Technician</a><br>";
echo "<a href='submit_ticket.php'>Submit New Ticket</a><br>";
?> 