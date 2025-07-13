<?php
session_start();
require_once 'db.php';

// Check if user is logged in and has user role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
  header('Location: login.php');
  exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch user data
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch user's tickets (instead of separate outage reports)
$stmt = $pdo->prepare('SELECT * FROM tickets WHERE user_id = ? ORDER BY created_at DESC LIMIT 10');
$stmt->execute([$user_id]);
$my_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch admin notifications for user
$stmt = $pdo->prepare('SELECT * FROM notifications WHERE (target_role = ? OR target_role = "all") ORDER BY created_at DESC LIMIT 10');
$stmt->execute(['user']);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch substations for location dropdown
$stmt = $pdo->query('SELECT name FROM substations ORDER BY name ASC');
$substations = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Handle outage report submission - now creates a ticket
$report_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_outage'])) {
  $loc = trim($_POST['location'] ?? '');
  $time = $_POST['time_started'] ?? '';
  $desc = trim($_POST['description'] ?? '');
  
  if ($loc && $time) {
    // Generate ticket number
    $prefix = 'TKT';
    $year = date('Y');
    $month = date('m');
    $random = strtoupper(substr(md5(uniqid()), 0, 6));
    $ticket_number = $prefix . $year . $month . $random;
    
    // Create ticket instead of outage report
    $stmt = $pdo->prepare('INSERT INTO tickets (ticket_number, user_id, user_name, user_email, user_phone, subject, description, priority, category, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
      $ticket_number,
      $user_id,
      $username,
      $user['email'] ?? '',
      $user['phone'] ?? '',
      'Outage Report - ' . $loc,
      "Outage Location: $loc\nOutage Start Time: $time\n\nDescription: $desc",
      'urgent', // Outage reports are urgent by default
      'outage',
      'pending'
    ]);
    
    $ticket_id = $pdo->lastInsertId();
    $report_msg = 'Outage reported successfully! Your ticket number is: ' . $ticket_number;
  } else {
    $report_msg = 'Please fill all required fields.';
  }
}

// Handle feedback submission for tickets
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_id'])) {
  $fid = intval($_POST['feedback_id']);
  $feedback = trim($_POST['feedback_text'] ?? '');
  
  if ($feedback) {
    // For now, we'll add feedback to the ticket description
    $stmt = $pdo->prepare('UPDATE tickets SET description = CONCAT(description, "\n\n--- USER FEEDBACK ---\n", ?) WHERE id = ? AND user_id = ?');
    $stmt->execute([$feedback, $fid, $user_id]);
    $report_msg = 'Feedback submitted!';
  }
}

// Handle ticket reopening
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reopen_id'])) {
  $rid = intval($_POST['reopen_id']);
  $stmt = $pdo->prepare('UPDATE tickets SET status = "pending" WHERE id = ? AND user_id = ?');
  $stmt->execute([$rid, $user_id]);
  $report_msg = 'Ticket reopened!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Dashboard - OutageSys</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    body { min-height: 100vh; }
    .dashboard-bg {
      min-height: 100vh;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 0;
    }
    .toggle-mode {
      position: absolute;
      top: 24px;
      right: 24px;
      background: var(--color-bg-card, #fff);
      border: none;
      border-radius: 20px;
      padding: 8px 16px;
      font-size: 0.9rem;
      cursor: pointer;
      color: var(--color-primary);
      display: flex;
      align-items: center;
      gap: 8px;
      transition: all 0.2s;
      box-shadow: var(--card-shadow);
      z-index: 10;
    }
    .toggle-mode:hover {
      background: var(--color-bg, #f8f9fa);
      transform: translateY(-1px);
    }
    @media (max-width: 480px) {
      .toggle-mode {
        top: 16px;
        right: 16px;
        padding: 6px 12px;
        font-size: 0.8rem;
      }
    }
    .styled-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: var(--space-md);
      background: var(--color-bg-card);
      border-radius: var(--card-radius);
      overflow: hidden;
      box-shadow: var(--card-shadow);
    }
    .styled-table th, .styled-table td {
      padding: 12px 16px;
      border-bottom: 1px solid var(--color-border);
      text-align: left;
    }
    .styled-table th {
      background: var(--color-bg);
      font-weight: var(--font-weight-bold);
    }
    .styled-table tr:last-child td {
      border-bottom: none;
    }
    .announcement-card {
      background: var(--color-bg-accent, #f5f7ff) !important;
      color: var(--color-text, #222);
      transition: background 0.2s, color 0.2s;
    }
    body.dark-mode .announcement-card {
      background: #232946 !important;
      color: #f4f4f4;
    }
    .profile-menu .btn-link, .profile-menu .btn-link:visited {
      background: none;
      color: var(--color-primary, #667eea);
      transition: color 0.2s;
    }
    .profile-menu .btn-link:hover {
      color: var(--color-accent, #764ba2);
      background: var(--color-bg, #f4f4f4);
    }
    body.dark-mode .profile-menu .btn-link, body.dark-mode .profile-menu .btn-link:visited {
      color: #f4f4f4;
      background: none;
    }
    body.dark-mode .profile-menu .btn-link:hover {
      color: #ffe066;
      background: #232946;
    }
    .profile-menu #profileDropdown {
      background: var(--color-bg-card, #fff);
      color: var(--color-text, #222);
    }
    body.dark-mode .profile-menu #profileDropdown {
      background: #232946;
      color: #f4f4f4;
      border-color: #232946;
    }
    .btn-warning {
      background: #ffe066;
      color: #222;
      border: none;
      border-radius: 6px;
      padding: 4px 12px;
      font-size: 0.95em;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s, color 0.2s;
    }
    .btn-warning:hover {
      background: #ffd43b;
      color: #111;
    }
    body.dark-mode .btn-warning {
      background: #ffe066;
      color: #232946;
    }
    body.dark-mode .btn-warning:hover {
      background: #ffd43b;
      color: #232946;
    }
    .ticket-status {
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 0.8rem;
      font-weight: 600;
      text-transform: uppercase;
    }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-assigned { background: #dbeafe; color: #1e40af; }
    .status-in_progress { background: #fef3c7; color: #92400e; }
    .status-resolved { background: #dcfce7; color: #166534; }
    .status-closed { background: #f3f4f6; color: #374151; }
    .priority-badge {
      padding: 2px 6px;
      border-radius: 4px;
      font-size: 0.7rem;
      font-weight: 600;
      text-transform: uppercase;
    }
    .priority-urgent { background: #fef2f2; color: #dc2626; }
    .priority-high { background: #fffbeb; color: #d97706; }
    .priority-medium { background: #eff6ff; color: #2563eb; }
    .priority-low { background: #ecfdf5; color: #059669; }
    
    /* Enhanced animations for outage reporting */
    @keyframes float {
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-20px) rotate(1deg); }
    }
    
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }
    
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
      20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
    
    @keyframes slideInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .outage-form-enhanced {
      animation: slideInUp 0.6s ease-out;
    }
    
    .form-field-enhanced {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .form-field-enhanced:focus {
      transform: translateY(-2px);
    }
    
    .submit-button-enhanced {
      position: relative;
      overflow: hidden;
    }
    
    .submit-button-enhanced::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      background: rgba(255,255,255,0.2);
      border-radius: 50%;
      transition: all 0.3s;
      transform: translate(-50%, -50%);
    }
    
    .submit-button-enhanced:hover::before {
      width: 300px;
      height: 300px;
    }
    
    .submit-button-enhanced:active {
      transform: scale(0.95);
    }
  </style>
</head>
<body>
  <!-- Remove dark mode button from main UI -->
  <div style="position:fixed; top:24px; right:24px; z-index:100;">
    <div class="profile-menu" style="position:relative; display:inline-block;">
      <button id="profileBtn" class="btn btn-outline" style="border-radius:50%; width:44px; height:44px; padding:0; display:flex; align-items:center; justify-content:center; font-size:1.3rem;">
        <i class="fa fa-user-circle"></i>
      </button>
      <div id="profileDropdown" style="display:none; position:absolute; right:0; top:48px; background:var(--color-bg-card,#fff); box-shadow:0 2px 8px rgba(0,0,0,0.12); border-radius:10px; min-width:200px; min-height:160px;">
        <div style="padding:16px 20px 8px 20px; border-bottom:1px solid var(--color-border,#eee); display:flex; align-items:center; gap:12px;">
          <i class="fa fa-user-circle" style="font-size:2rem; color:var(--color-primary,#667eea);"></i>
          <div style="font-weight:600; font-size:1rem; color:var(--color-primary,#333);">
            <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
          </div>
        </div>
        <button class="btn btn-link w-100" id="darkModeToggle" style="text-align:left; padding:12px 20px; display:block; color:var(--color-primary,#667eea);"><i class="fa fa-moon" id="modeIcon"></i> <span id="modeText">Dark Mode</span></button>
        <a href="settings.php" class="btn btn-link w-100" style="text-align:left; padding:12px 20px; display:block;"><i class="fa fa-cogs"></i> Settings</a>
        <form action="logout.php" method="post" style="margin:0;">
          <button type="submit" class="btn btn-link w-100" style="text-align:left; padding:12px 20px; color:#e53e3e;"><i class="fa fa-sign-out-alt"></i> Logout</button>
        </form>
      </div>
    </div>
  </div>
  <!-- Breadcrumbs navigation -->
  <nav aria-label="Breadcrumb" class="breadcrumbs-nav" style="margin: 1.5rem auto 0 auto; max-width:900px; background:transparent;">
    <ol class="breadcrumbs" style="display:flex; flex-wrap:wrap; gap:0.5em; list-style:none; padding:0; margin:0; font-size:1rem; background:transparent;">
      <li class="breadcrumb-current" style="color:var(--color-primary); font-weight:600;"><i class="fa fa-tachometer-alt"></i> Dashboard</li>
    </ol>
  </nav>
  <script>
    function setMode(dark) {
      document.body.classList.toggle('dark-mode', dark);
      document.getElementById('modeIcon').className = dark ? 'fa fa-sun' : 'fa fa-moon';
      document.getElementById('modeText').textContent = dark ? 'Light Mode' : 'Dark Mode';
      localStorage.setItem('darkMode', dark ? '1' : '0');
    }
    
    // Initialize dark mode
    const savedMode = localStorage.getItem('darkMode');
    if (savedMode !== null) {
      setMode(savedMode === '1');
    }
    
    // Toggle dark mode
    document.getElementById('darkModeToggle').addEventListener('click', function() {
      const isDark = document.body.classList.contains('dark-mode');
      setMode(!isDark);
    });
    
    // Profile dropdown
    document.getElementById('profileBtn').addEventListener('click', function() {
      const dropdown = document.getElementById('profileDropdown');
      dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
      const dropdown = document.getElementById('profileDropdown');
      const profileBtn = document.getElementById('profileBtn');
      if (!profileBtn.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.style.display = 'none';
      }
    });
    
    // Enhanced outage form functionality
    document.addEventListener('DOMContentLoaded', function() {
      const outageForm = document.getElementById('outageForm');
      const submitBtn = document.getElementById('submitOutageBtn');
      const locationSelect = document.getElementById('location');
      const timeInput = document.getElementById('time_started');
      const descriptionTextarea = document.getElementById('description');
      
      // Add enhanced classes
      if (outageForm) {
        outageForm.classList.add('outage-form-enhanced');
      }
      
      // Enhanced form validation with visual feedback
      function validateForm() {
        let isValid = true;
        const fields = [locationSelect, timeInput];
        
        fields.forEach(field => {
          if (!field.value.trim()) {
            field.style.borderColor = '#ef4444';
            field.style.animation = 'shake 0.5s ease-in-out';
            setTimeout(() => {
              field.style.animation = '';
            }, 500);
            isValid = false;
          } else {
            field.style.borderColor = '#10b981';
            setTimeout(() => {
              field.style.borderColor = '#e5e7eb';
            }, 2000);
          }
        });
        
        return isValid;
      }
      
      // Enhanced submit button behavior
      if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
          if (!validateForm()) {
            e.preventDefault();
            showNotification('Please fill in all required fields!', 'error');
            return;
          }
          
          // Add loading state
          const originalText = submitBtn.innerHTML;
          submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Submitting...';
          submitBtn.disabled = true;
          
          // Simulate processing time
          setTimeout(() => {
            submitBtn.innerHTML = '<i class="fa fa-check"></i> Submitted!';
            submitBtn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
            
            setTimeout(() => {
              submitBtn.innerHTML = originalText;
              submitBtn.disabled = false;
              submitBtn.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
            }, 2000);
          }, 1500);
        });
      }
      
      // Auto-fill current time if not set
      if (timeInput && !timeInput.value) {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        timeInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
      }
      
      // Enhanced field focus effects
      [locationSelect, timeInput, descriptionTextarea].forEach(field => {
        if (field) {
          field.classList.add('form-field-enhanced');
          
          field.addEventListener('focus', function() {
            this.parentElement.style.transform = 'translateY(-2px)';
          });
          
          field.addEventListener('blur', function() {
            this.parentElement.style.transform = 'translateY(0)';
          });
        }
      });
      
      // Character counter for description
      if (descriptionTextarea) {
        const counter = document.createElement('div');
        counter.style.cssText = 'font-size: 0.8rem; color: #6b7280; text-align: right; margin-top: 0.5rem;';
        descriptionTextarea.parentElement.appendChild(counter);
        
        function updateCounter() {
          const length = descriptionTextarea.value.length;
          const maxLength = 500;
          counter.textContent = `${length}/${maxLength} characters`;
          counter.style.color = length > maxLength * 0.8 ? '#ef4444' : '#6b7280';
        }
        
        descriptionTextarea.addEventListener('input', updateCounter);
        updateCounter();
      }
    });
    
    // Enhanced notification function
    function showNotification(message, type = 'success') {
      const notification = document.createElement('div');
      const icons = {
        success: '🎉',
        error: '❌',
        info: 'ℹ️',
        warning: '⚠️'
      };
      
      notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#22c55e' : type === 'error' ? '#ef4444' : type === 'info' ? '#3b82f6' : '#f59e0b'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        z-index: 1001;
        transform: translateX(100%) scale(0.8);
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        max-width: 350px;
        word-wrap: break-word;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2);
      `;
      
      notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 0.75rem;">
          <span style="font-size: 1.2rem;">${icons[type] || icons.info}</span>
          <span style="flex: 1;">${message}</span>
          <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem; opacity: 0.7; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">×</button>
        </div>
      `;
      
      document.body.appendChild(notification);
      
      setTimeout(() => {
        notification.style.transform = 'translateX(0) scale(1)';
      }, 100);
      
      setTimeout(() => {
        notification.style.transform = 'translateX(100%) scale(0.8)';
        setTimeout(() => {
          if (document.body.contains(notification)) {
            document.body.removeChild(notification);
          }
        }, 400);
      }, 4000);
    }
  </script>
  <div class="dashboard-bg">
    <div class="container" style="width:100%;">
      <div class="card mb-md">
        <h2 class="h2 mb-sm"><i class="fa fa-tachometer-alt"></i> Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
        <p class="mb-md">Monitor your outage reports and stay updated with system notifications.</p>
        
        <?php if ($report_msg): ?>
          <div class="success-msg mb-md"><?php echo htmlspecialchars($report_msg); ?></div>
        <?php endif; ?>
        
        <!-- Quick Actions -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
          <a href="submit_ticket.php" class="btn btn-primary" style="text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
            <i class="fa fa-plus"></i>
            Submit Ticket
          </a>
          <a href="my_tickets.php" class="btn btn-outline" style="text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
            <i class="fa fa-ticket-alt"></i>
            My Tickets
          </a>
          <a href="map.php" class="btn btn-outline" style="text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
            <i class="fa fa-map"></i>
            View Map
          </a>
        </div>
      </div>



      <!-- Recent Tickets Section -->
      <div class="card mb-md">
        <h3 class="h3 mb-sm"><i class="fa fa-ticket-alt"></i> Recent Tickets</h3>
        <?php if (empty($my_tickets)): ?>
          <p class="mb-md">No tickets found. <a href="submit_ticket.php">Submit your first ticket</a> or report an outage above.</p>
        <?php else: ?>
          <div style="overflow-x: auto;">
            <table class="styled-table">
              <thead>
                <tr>
                  <th>Ticket #</th>
                  <th>Subject</th>
                  <th>Category</th>
                  <th>Priority</th>
                  <th>Status</th>
                  <th>Created</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($my_tickets as $ticket): ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($ticket['ticket_number']); ?></strong></td>
                    <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($ticket['category'])); ?></td>
                    <td><span class="priority-badge priority-<?php echo $ticket['priority']; ?>"><?php echo ucfirst($ticket['priority']); ?></span></td>
                    <td><span class="ticket-status status-<?php echo $ticket['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?></span></td>
                    <td><?php echo date('M j, Y', strtotime($ticket['created_at'])); ?></td>
                    <td>
                      <a href="my_tickets.php" class="btn btn-sm btn-outline">View Details</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div style="margin-top: 1rem; text-align: center;">
            <a href="my_tickets.php" class="btn btn-outline">View All Tickets</a>
          </div>
        <?php endif; ?>
      </div>

      <!-- Notifications Section -->
      <div class="card mb-md">
        <h3 class="h3 mb-sm"><i class="fa fa-bell"></i> System Notifications</h3>
        <?php if (empty($notifications)): ?>
          <p class="mb-md">No notifications at this time.</p>
        <?php else: ?>
          <?php foreach ($notifications as $notification): ?>
            <div class="announcement-card" style="padding: 1rem; margin-bottom: 1rem; border-radius: 8px;">
              <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                <i class="fa fa-bell" style="color: var(--color-primary);"></i>
                <span style="font-weight: 600; color: var(--color-primary);">
                  <?php echo htmlspecialchars($notification['type'] ?? 'Notification'); ?>
                </span>
                <?php if (isset($notification['priority']) && $notification['priority'] !== 'normal'): ?>
                  <span style="padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; 
                        background: <?php echo $notification['priority'] === 'urgent' ? '#fef2f2' : ($notification['priority'] === 'high' ? '#fffbeb' : '#ecfdf5'); ?>; 
                        color: <?php echo $notification['priority'] === 'urgent' ? '#dc2626' : ($notification['priority'] === 'high' ? '#d97706' : '#059669'); ?>;">
                    <?php echo ucfirst($notification['priority']); ?>
                  </span>
                <?php endif; ?>
              </div>
              <p style="margin: 0 0 0.5rem 0; color: var(--color-text);"><?php echo htmlspecialchars($notification['message']); ?></p>
              <small style="color: var(--color-secondary);"><?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?></small>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="footer mt-md small text-center" style="color: var(--color-secondary);">
        &copy; 2024 OutageSys. All rights reserved.
      </div>
    </div>
  </div>
</body>
</html> 