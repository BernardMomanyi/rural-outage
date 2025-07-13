<?php
session_start();
require_once 'db.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch user data
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch key stats
$total_users = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$total_tickets = $pdo->query('SELECT COUNT(*) FROM user_outages')->fetchColumn();
$total_substations = $pdo->query('SELECT COUNT(*) FROM substations')->fetchColumn();
$pending_tickets = $pdo->query('SELECT COUNT(*) FROM user_outages WHERE status = "Submitted"')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - OutageSys</title>
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
      background: var(--color-bg-gradient);
      padding: 0;
      transition: background var(--transition);
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
  </style>
</head>
<body>
  <!-- Breadcrumbs navigation -->
  <nav aria-label="Breadcrumb" class="breadcrumbs-nav" style="margin: 1.5rem auto 0 auto; max-width:900px; background:transparent;">
    <ol class="breadcrumbs" style="display:flex; flex-wrap:wrap; gap:0.5em; list-style:none; padding:0; margin:0; font-size:1rem; background:transparent;">
      <li class="breadcrumb-current"><i class="fa fa-tachometer-alt"></i> Dashboard</li>
    </ol>
  </nav>
  <button class="toggle-mode" id="darkModeToggle">
    <i class="fa fa-moon" id="modeIcon"></i> 
    <span id="modeText">Dark Mode</span>
  </button>
  <div class="dashboard-bg">
    <div class="container" style="width:100%;">
      <div class="grid grid-3 mb-md">
        <div class="card">
          <h2 class="h2 mb-sm"><i class="fa fa-user-shield"></i> Admin Home</h2>
          <p class="mb-sm">Overview and quick access to admin features.</p>
          <a href="admin_dashboard.php" class="btn btn-primary">Admin Home</a>
        </div>
        <div class="card">
          <h2 class="h2 mb-sm"><i class="fa fa-ticket-alt"></i> Tickets</h2>
          <p class="mb-sm">Manage all outage tickets.</p>
          <a href="admin_tickets.php" class="btn btn-outline">Manage Tickets</a>
        </div>
        <div class="card">
          <h2 class="h2 mb-sm"><i class="fa fa-comments"></i> Communication Center</h2>
          <p class="mb-sm">Send notifications and manage user feedback.</p>
          <a href="communication_center.php" class="btn btn-outline">Open Communication Center</a>
        </div>
        <div class="card">
          <h2 class="h2 mb-sm"><i class="fa fa-users-cog"></i> Technicians</h2>
          <p class="mb-sm">Manage and assign technicians.</p>
          <a href="admin_technicians.php" class="btn btn-outline">Manage Technicians</a>
        </div>
        <div class="card">
          <h2 class="h2 mb-sm"><i class="fa fa-bolt"></i> Substations</h2>
          <p class="mb-sm">View and manage substations.</p>
          <a href="substations.php" class="btn btn-outline">Manage Substations</a>
        </div>
        <div class="card">
          <h2 class="h2 mb-sm"><i class="fa fa-file-alt"></i> Reports</h2>
          <p class="mb-sm">View and download system reports.</p>
          <a href="reports.php" class="btn btn-outline">View Reports</a>
        </div>
        <div class="card">
          <h2 class="h2 mb-sm"><i class="fa fa-chart-bar"></i> Analytics</h2>
          <p class="mb-sm">View system analytics and trends.</p>
          <a href="analytics.php" class="btn btn-outline">View Analytics</a>
        </div>
        <div class="card">
          <h2 class="h2 mb-sm"><i class="fa fa-map"></i> Map View</h2>
          <p class="mb-sm">Visualize substations and outages on the map.</p>
          <a href="map.php" class="btn btn-outline">Map View</a>
        </div>
        <div class="card">
          <h2 class="h2 mb-sm"><i class="fa fa-users"></i> Users</h2>
          <p class="mb-sm">Manage user accounts and permissions.</p>
          <a href="users.php" class="btn btn-outline">Manage Users</a>
        </div>
        <div class="card">
          <h2 class="h2 mb-sm"><i class="fa fa-cogs"></i> Settings</h2>
          <p class="mb-sm">System and account settings.</p>
          <a href="settings.php" class="btn btn-outline">System Settings</a>
        </div>
      </div>
      <div class="footer mt-md small text-center" style="color: var(--color-secondary);">
        &copy; 2024 OutageSys. All rights reserved.
      </div>
    </div>
  </div>
  <script src="js/dark-mode.js"></script>
</body>
</html> 