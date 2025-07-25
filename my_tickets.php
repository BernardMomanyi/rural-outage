<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$dashboard_link = 'user_dashboard.php';
if ($role === 'admin') $dashboard_link = 'admin_dashboard.php';
if ($role === 'technician') $dashboard_link = 'technician_dashboard.php';

require_once 'db.php';

// Handle status update for technicians
$status_msg = '';
if ($role === 'technician' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_id'], $_POST['status_action'])) {
  $ticket_id = intval($_POST['ticket_id']);
  $value = trim($_POST['status_action']);
  $status_options = ['Submitted', 'Assigned', 'Resolved'];
  $action_options = ['Inspected', 'Parts Ordered', 'Escalated', 'Other'];
  if (in_array($value, $status_options, true)) {
    $stmt = $pdo->prepare('UPDATE tickets SET status = ?, action_taken = NULL WHERE id = ? AND assigned_technician_id = ?');
    $stmt->execute([$value, $ticket_id, $user_id]);
    $status_msg = 'Ticket status updated!';
  } elseif (in_array($value, $action_options, true)) {
    $stmt = $pdo->prepare('UPDATE tickets SET action_taken = ? WHERE id = ? AND assigned_technician_id = ?');
    $stmt->execute([$value, $ticket_id, $user_id]);
    $status_msg = 'Ticket action updated!';
  } else {
    $status_msg = 'Invalid selection!';
  }
}

// Fetch tickets for technician
if ($role === 'technician') {
  $stmt = $pdo->prepare('SELECT * FROM tickets WHERE assigned_technician_id = ? ORDER BY created_at DESC');
  $stmt->execute([$user_id]);
  $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
  $stmt = $pdo->prepare('SELECT * FROM tickets WHERE user_id = ? ORDER BY created_at DESC');
  $stmt->execute([$user_id]);
  $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Calculate analytics for all dropdown options except 'Other'
$status_counts = [
  'submitted' => 0,
  'assigned' => 0,
  'in_progress' => 0,
  'resolved' => 0,
  'closed' => 0,
  'escalated' => 0
];
foreach ($tickets as $ticket) {
  $status = strtolower($ticket['status']);
  if (isset($status_counts[$status])) {
    $status_counts[$status]++;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Tickets - OutageSys</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    body { 
      min-height: 100vh; 
      margin: 0;
      font-family: 'Inter', sans-serif;
    }
    
    .my-tickets-bg {
      min-height: 100vh;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 0;
    }
    
    .container {
      width: 100%;
      max-width: 1000px;
      padding: 2rem;
      overflow-x: auto;
    }
    @media (max-width: 1100px) {
      .container {
        max-width: 100%;
        padding: 1rem;
      }
    }
    
    .card {
      background: #fff;
      border-radius: 18px;
      padding: 2.5em 2em;
      margin-bottom: 2em;
      box-shadow: 0 8px 32px rgba(0,0,0,0.1);
      border: 1px solid rgba(255,255,255,0.2);
    }
    
    .h2 {
      color: #2563eb;
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .small {
      color: #555;
      font-size: 0.9rem;
      margin-bottom: 1.5rem;
    }
    
    .btn {
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      font-size: 0.9rem;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .btn-primary {
      background: #2563eb;
      color: white;
    }
    
    .btn-primary:hover {
      background: #1d4ed8;
      transform: translateY(-1px);
    }
    
    .btn-outline {
      background: transparent;
      color: #2563eb;
      border: 2px solid #2563eb;
    }
    
    .btn-outline:hover {
      background: #2563eb;
      color: white;
    }
    
    .btn-success {
      background: #22c55e;
      color: white;
    }
    
    .btn-success:hover {
      background: #16a34a;
    }
    
    .btn-sm {
      padding: 0.5rem 1rem;
      font-size: 0.8rem;
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }
    
    .stat-card {
      background: linear-gradient(135deg, #f1f5ff 0%, #e0eafc 100%);
      border-radius: 16px;
      padding: 1.5rem;
      border-left: 6px solid #2563eb;
      box-shadow: 0 4px 12px rgba(37,99,235,0.1);
      transition: all 0.3s ease;
    }
    
    .stat-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 25px rgba(37,99,235,0.15);
    }
    
    .stat-icon {
      font-size: 2rem;
      color: #2563eb;
      margin-bottom: 0.5rem;
    }
    
    .stat-number {
      font-size: 2rem;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 0.25rem;
    }
    
    .stat-label {
      color: #64748b;
      font-size: 0.9rem;
      font-weight: 500;
    }
    
    .ticket-card {
      background: #fff;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      border-left: 4px solid #e5e7eb;
      transition: all 0.2s;
    }
    
    .ticket-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }
    
    .ticket-card.urgent {
      border-left-color: #ef4444;
      background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    }
    
    .ticket-card.high {
      border-left-color: #f59e0b;
      background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    }
    
    .ticket-card.medium {
      border-left-color: #3b82f6;
      background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    }
    
    .ticket-card.low {
      border-left-color: #10b981;
      background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    }
    
    .priority-badge {
      padding: 0.25rem 0.75rem;
      border-radius: 12px;
      font-size: 0.8rem;
      font-weight: 500;
      text-transform: capitalize;
    }
    
    .priority-urgent {
      background: #fef2f2;
      color: #dc2626;
    }
    
    .priority-high {
      background: #fffbeb;
      color: #d97706;
    }
    
    .priority-medium {
      background: #eff6ff;
      color: #2563eb;
    }
    
    .priority-low {
      background: #ecfdf5;
      color: #059669;
    }
    
    .status-badge {
      padding: 0.25rem 0.75rem;
      border-radius: 12px;
      font-size: 0.8rem;
      font-weight: 500;
      text-transform: capitalize;
    }
    
    .status-pending {
      background: #fef3c7;
      color: #d97706;
    }
    
    .status-assigned {
      background: #dbeafe;
      color: #2563eb;
    }
    
    .status-in_progress {
      background: #fef3c7;
      color: #d97706;
    }
    
    .status-resolved {
      background: #dcfce7;
      color: #16a34a;
    }
    
    .status-closed {
      background: #f3f4f6;
      color: #6b7280;
    }
    
    .modal-bg {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(0,0,0,0.5);
      z-index: 1000;
      align-items: center;
      justify-content: center;
      backdrop-filter: blur(4px);
    }
    
    .modal {
      background: #fff;
      border-radius: 16px;
      padding: 2rem;
      min-width: 500px;
      max-width: 90vw;
      box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
      position: relative;
      max-height: 80vh;
      overflow-y: auto;
    }
    
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid #e5e7eb;
    }
    
    .modal-title {
      font-size: 1.25rem;
      font-weight: 600;
      color: #1f2937;
    }
    
    .modal-close {
      background: none;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: #6b7280;
      padding: 0.25rem;
      border-radius: 4px;
      transition: all 0.2s;
    }
    
    .modal-close:hover {
      background: #f3f4f6;
      color: #374151;
    }
    
    .modal-actions {
      display: flex;
      gap: 1rem;
      justify-content: flex-end;
      margin-top: 2rem;
      padding-top: 1rem;
      border-top: 1px solid #e5e7eb;
    }
    
    .loading {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid #f3f3f3;
      border-top: 3px solid #2563eb;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    /* Dark mode support */
    body.dark-mode .my-tickets-bg {
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%) !important;
    }
    
    body.dark-mode .card {
      background: #2d3748 !important;
      color: #e2e8f0 !important;
      border-color: #4a5568 !important;
    }
    
    body.dark-mode .h2 {
      color: #63b3ed !important;
    }
    
    body.dark-mode .small {
      color: #cbd5e0 !important;
    }
    
    body.dark-mode .stat-card {
      background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%) !important;
      color: #e2e8f0 !important;
    }
    
    body.dark-mode .stat-number {
      color: #f7fafc !important;
    }
    
    body.dark-mode .stat-label {
      color: #a0aec0 !important;
    }
    
    body.dark-mode .ticket-card {
      background: #2d3748 !important;
      color: #e2e8f0 !important;
      border-color: #4a5568 !important;
    }
    
    body.dark-mode .ticket-card.urgent {
      background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%) !important;
      border-left-color: #fc8181 !important;
    }
    
    body.dark-mode .ticket-card.high {
      background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%) !important;
      border-left-color: #f6ad55 !important;
    }
    
    body.dark-mode .ticket-card.medium {
      background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%) !important;
      border-left-color: #63b3ed !important;
    }
    
    body.dark-mode .ticket-card.low {
      background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%) !important;
      border-left-color: #68d391 !important;
    }
    
    body.dark-mode .priority-urgent {
      background: #742a2a !important;
      color: #fc8181 !important;
    }
    
    body.dark-mode .priority-high {
      background: #744210 !important;
      color: #f6ad55 !important;
    }
    
    body.dark-mode .priority-medium {
      background: #2c5282 !important;
      color: #63b3ed !important;
    }
    
    body.dark-mode .priority-low {
      background: #22543d !important;
      color: #68d391 !important;
    }
    
    body.dark-mode .status-pending {
      background: #744210 !important;
      color: #f6ad55 !important;
    }
    
    body.dark-mode .status-assigned {
      background: #2c5282 !important;
      color: #63b3ed !important;
    }
    
    body.dark-mode .status-in_progress {
      background: #744210 !important;
      color: #f6ad55 !important;
    }
    
    body.dark-mode .status-resolved {
      background: #22543d !important;
      color: #68d391 !important;
    }
    
    body.dark-mode .status-closed {
      background: #4a5568 !important;
      color: #a0aec0 !important;
    }
    
    body.dark-mode .modal {
      background: #2d3748 !important;
      color: #e2e8f0 !important;
    }
    
    body.dark-mode .modal-header {
      border-bottom-color: #4a5568 !important;
    }
    
    body.dark-mode .modal-title {
      color: #f7fafc !important;
    }
    
    body.dark-mode .modal-close {
      color: #a0aec0 !important;
    }
    
    body.dark-mode .modal-close:hover {
      background: #4a5568 !important;
      color: #e2e8f0 !important;
    }
    
    body.dark-mode .modal-actions {
      border-top-color: #4a5568 !important;
    }
    
    body.dark-mode .btn-outline {
      color: #63b3ed !important;
      border-color: #63b3ed !important;
    }
    
    body.dark-mode .btn-outline:hover {
      background: #63b3ed !important;
      color: #1a202c !important;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
      .container {
        padding: 1rem;
      }
      
      .card {
        padding: 1.5rem 1rem;
      }
      
      .modal {
        min-width: 90vw;
        margin: 1rem;
      }
      
      .stats-grid {
        grid-template-columns: 1fr;
      }
    }
    .tickets-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 2em;
    }
    @media (max-width: 900px) {
      .tickets-table th, .tickets-table td {
        font-size: 0.95em;
        padding: 0.5em 0.5em;
      }
    }
    .tickets-table th, .tickets-table td { padding: 0.75em 1em; border-bottom: 1px solid #e5e7eb; text-align: left; }
    .tickets-table th { background: #f1f5ff; color: #2563eb; font-weight: 600; }
    .tickets-table tr:last-child td { border-bottom: none; }
    .status-form { display: flex; gap: 0.5em; align-items: center; margin: 0; }
    .status-form select { padding: 0.3em 0.7em; border-radius: 6px; border: 1px solid #d1d5db; }
    .status-form select { width: 80px; font-size: 0.8em; padding: 0.2em 0.5em; }
    .status-form button { padding: 0.3em 1em; border-radius: 6px; border: none; background: #2563eb; color: #fff; font-weight: 600; cursor: pointer; transition: background 0.2s; }
    .status-form button:hover { background: #1d4ed8; }
    .status-inspected {
      background: #e0e7ff;
      color: #3730a3;
    }
    .status-parts_ordered {
      background: #fef9c3;
      color: #b45309;
    }
    .status-escalated {
      background: #fee2e2;
      color: #b91c1c;
    }
    .status-submitted {
      background: #fef3c7;
      color: #d97706;
    }
  </style>
</head>
<body>
  <nav aria-label="Breadcrumb" class="breadcrumbs-nav" style="margin: 1.5rem auto 0 auto; max-width:900px; background:transparent;">
    <ol class="breadcrumbs" style="display:flex; flex-wrap:wrap; gap:0.5em; list-style:none; padding:0; margin:0; font-size:1rem; background:transparent;">
      <li><a href="<?php echo $dashboard_link; ?>" class="breadcrumb-link"><i class="fa fa-tachometer-alt"></i> Dashboard</a></li>
      <li style="color:var(--color-secondary);">&gt;</li>
      <li class="breadcrumb-current" style="color:var(--color-primary); font-weight:600;"><i class="fa fa-ticket-alt"></i> My Tickets</li>
    </ol>
  </nav>
  <div class="my-tickets-bg">
    <div class="container">
      <?php if (!empty($status_msg)) { echo '<div class="card" style="background:#dcfce7; color:#166534; margin-bottom:1em;">'.$status_msg.'</div>'; } ?>
      <div class="card" style="margin-bottom:1.5em;">
        <div style="display:flex; gap:1em; align-items:center; justify-content:center; flex-wrap:wrap;">
          <span class="status-badge status-submitted">🕐 <?php echo $status_counts['submitted']; ?> Submitted</span>
          <span class="status-badge status-assigned">👤 <?php echo $status_counts['assigned']; ?> Assigned</span>
          <span class="status-badge status-in_progress">⚡ <?php echo $status_counts['in_progress']; ?> In Progress</span>
          <span class="status-badge status-resolved">✅ <?php echo $status_counts['resolved']; ?> Resolved</span>
          <span class="status-badge status-closed">🔒 <?php echo $status_counts['closed']; ?> Closed</span>
          <span class="status-badge status-escalated">❗ <?php echo $status_counts['escalated']; ?> Escalated</span>
        </div>
      </div>
      <div class="card">
        <h2 class="h2"><i class="fa fa-ticket-alt"></i> My Tickets</h2>
        <?php if (empty($tickets)) { ?>
          <div style="text-align:center; color:#6b7280; padding:2em;">
            <i class="fa fa-ticket-alt" style="font-size:2em;"></i><br>No tickets assigned to you yet.
          </div>
        <?php } else { ?>
          <div class="table-scroll">
            <table class="tickets-table">
              <thead>
                <tr>
                  <th>Ticket #</th>
                  <th>Subject</th>
                  <th>Description</th>
                  <?php if ($role === 'technician') echo '<th>Priority</th>'; ?>
                  <th>Status</th>
                  <th>Action</th>
                  <?php if ($role === 'technician') echo '<th></th>'; ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($tickets as $ticket) { ?>
                  <tr>
                    <td><?php echo htmlspecialchars($ticket['ticket_number']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                    <td class="ticket-description">
                      <?php echo htmlspecialchars($ticket['description']); ?>
                    </td>
                    <?php if ($role === 'technician') { ?>
                      <td>
                        <span class="priority-badge priority-<?php echo htmlspecialchars(strtolower($ticket['priority'] ?? 'low')); ?>">
                          <?php echo ucfirst(htmlspecialchars($ticket['priority'] ?? 'Low')); ?>
                        </span>
                      </td>
                    <?php } ?>
                    <td>
                      <span class="status-badge status-<?php echo htmlspecialchars(strtolower(str_replace(' ', '_', $ticket['status']))); ?>">
                        <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($ticket['status']))); ?>
                      </span>
                    </td>
                    <td>
                      <?php if ($role === 'technician') { ?>
                        <form method="post" class="status-form">
                          <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                          <select name="status_action" required>
                            <option value="Submitted" <?php if($ticket['status']==='Submitted')echo'selected';?>>Submitted</option>
                            <option value="Assigned" <?php if($ticket['status']==='Assigned')echo'selected';?>>Assigned</option>
                            <option value="Resolved" <?php if($ticket['status']==='Resolved')echo'selected';?>>Resolved</option>
                            <option value="Inspected" <?php if(($ticket['action_taken'] ?? '')==='Inspected')echo'selected';?>>Inspected</option>
                            <option value="Parts Ordered" <?php if(($ticket['action_taken'] ?? '')==='Parts Ordered')echo'selected';?>>Parts Ordered</option>
                            <option value="Escalated" <?php if(($ticket['action_taken'] ?? '')==='Escalated')echo'selected';?>>Escalated</option>
                          </select>
                          <button type="submit">Update</button>
                        </form>
                      <?php } else { ?>
                        <span style="background:#e0e7ff; color:#3730a3; border-radius:10px; padding:0.3em 0.8em; font-size:0.95em; font-weight:500;">
                          <?php echo !empty($ticket['action_taken']) ? htmlspecialchars($ticket['action_taken']) : htmlspecialchars($ticket['status']); ?>
                        </span>
                      <?php } ?>
                    </td>
                    <?php if ($role === 'technician') echo '<td></td>'; ?>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        <?php } ?>
      </div>
      <div class="footer mt-md small text-center" style="color: var(--color-secondary);">
        &copy; 2024 OutageSys. All rights reserved.
      </div>
    </div>
  </div>
  <script src="js/dark-mode.js"></script>
</body>
</html> 