<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$role = $_SESSION['role'];
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - OutageSys</title>
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
    .breadcrumb-link {
      color: var(--color-primary, #2563eb);
      text-decoration: none;
      transition: color 0.2s;
      background: transparent;
    }
    .breadcrumb-link:hover {
      color: var(--color-accent, #f43f5e);
      text-decoration: underline;
    }
    body.dark-mode .breadcrumb-link {
      color: #90cdf4;
    }
    .breadcrumbs-nav {
      width: 100%;
      padding-left: 1.5rem;
      padding-right: 1.5rem;
      background: transparent;
    }
    @media (max-width: 600px) {
      .breadcrumbs-nav { font-size: 0.95rem; padding-left: 0.5rem; padding-right: 0.5rem; }
      .breadcrumbs { font-size: 0.95rem; }
    }
  </style>
</head>
<body>
  <!-- Breadcrumbs navigation -->
  <nav aria-label="Breadcrumb" class="breadcrumbs-nav" style="margin: 1.5rem auto 0 auto; max-width:900px; background:transparent;">
    <ol class="breadcrumbs" style="display:flex; flex-wrap:wrap; gap:0.5em; list-style:none; padding:0; margin:0; font-size:1rem; background:transparent;">
      <li class="breadcrumb-current" style="color:var(--color-primary); font-weight:600;"><i class="fa fa-tachometer-alt"></i> Dashboard</li>
    </ol>
  </nav>
  <button class="toggle-mode" id="darkModeToggle">
    <i class="fa fa-moon" id="modeIcon"></i> 
    <span id="modeText">Dark Mode</span>
  </button>
  <div class="dashboard-bg">
    <div class="container" style="width:100%;">
      <!-- Example dashboard cards -->
      <div class="grid grid-2 mb-md">
        <div class="card">
          <h2 class="h2 mb-sm"><i class="fa fa-bolt"></i> Outages</h2>
          <p class="mb-sm">View and manage current outages in your area.</p>
          <a href="#" class="btn btn-primary">View Outages</a>
        </div>
        <div class="card">
          <h2 class="h2 mb-sm"><i class="fa fa-chart-bar"></i> Analytics</h2>
          <p class="mb-sm">See analytics and trends for outages and repairs.</p>
          <a href="#" class="btn btn-outline">View Analytics</a>
        </div>
      </div>
      <div class="grid grid-2 mb-md">
        <div class="card">
          <h2 class="h2 mb-sm"><i class="fa fa-users"></i> Technicians</h2>
          <p class="mb-sm">Manage technician assignments and status.</p>
          <a href="#" class="btn btn-outline">Manage Technicians</a>
        </div>
        <div class="card">
          <h2 class="h2 mb-sm"><i class="fa fa-cogs"></i> Settings</h2>
          <p class="mb-sm">Update your account and notification settings.</p>
          <a href="#" class="btn btn-outline">Account Settings</a>
        </div>
      </div>
      <div class="footer mt-md small text-center" style="color: var(--color-secondary);">
        &copy; 2024 OutageSys. All rights reserved.
      </div>
    </div>
  </div>
  <script>
    function setMode(dark) {
      document.body.classList.toggle('dark-mode', dark);
      document.getElementById('modeIcon').className = dark ? 'fa fa-sun' : 'fa fa-moon';
      document.getElementById('modeText').textContent = dark ? 'Light Mode' : 'Dark Mode';
      localStorage.setItem('darkMode', dark ? '1' : '0');
    }
    document.getElementById('darkModeToggle').onclick = function() {
      setMode(!document.body.classList.contains('dark-mode'));
    };
    if (localStorage.getItem('darkMode') === '1') setMode(true);
  </script>
</body>
</html> 