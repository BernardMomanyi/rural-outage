<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
require_once 'db.php';
require_once 'csrf.php';
$username = $_SESSION['username'];
$role = $_SESSION['role'];
$dashboard_link = 'user_dashboard.php';
if ($role === 'admin') $dashboard_link = 'admin_dashboard.php';
if ($role === 'technician') $dashboard_link = 'technician_dashboard.php';
// Simulate settings and user info
$user = [
  'name' => 'Admin User',
  'email' => 'admin@outagesys.com',
  'avatar' => 'https://ui-avatars.com/api/?name=Admin',
  'language' => 'English',
  'theme' => 'system',
];
$settings = [
  'dashboard_widgets' => ['stats', 'charts', 'notifications'],
  'default_landing' => 'admin_dashboard.php',
  'color_scheme' => 'default',
  'org_name' => 'OutageSys',
  'org_logo' => '',
  'org_address' => 'Nairobi, Kenya',
  'working_hours' => '08:00-17:00',
  'maintenance_window' => 'Sunday 02:00-04:00',
  'notification_rules' => 'Alert if >3 substations offline in 1 hour',
  'notification_channels' => ['email'],
  'data_export' => '',
];
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    $msg = '<span class="error-text">Invalid CSRF token.</span>';
  } else {
    $msg = 'Settings updated! (Demo only)';
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings - OutageSys</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    body { min-height: 100vh; }
    .settings-bg {
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
    }
    @media (max-width: 600px) {
      .breadcrumbs-nav { font-size: 0.95rem; padding-left: 0.5rem; padding-right: 0.5rem; }
      .breadcrumbs { font-size: 0.95rem; }
    }
  </style>
</head>
<body>
  <button class="toggle-mode" id="darkModeToggle">
    <i class="fa fa-moon" id="modeIcon"></i> 
    <span id="modeText">Dark Mode</span>
  </button>
  <!-- Breadcrumbs navigation -->
  <nav aria-label="Breadcrumb" class="breadcrumbs-nav" style="margin: 1.5rem auto 0 auto; max-width:900px; background:transparent;">
    <ol class="breadcrumbs" style="display:flex; flex-wrap:wrap; gap:0.5em; list-style:none; padding:0; margin:0; font-size:1rem; background:transparent;">
      <li><a href="user_dashboard.php" class="breadcrumb-link"><i class="fa fa-tachometer-alt"></i> Dashboard</a></li>
      <li style="color:var(--color-secondary);">&gt;</li>
      <li class="breadcrumb-current" style="color:var(--color-primary); font-weight:600;"><i class="fa fa-cogs"></i> Settings</li>
    </ol>
  </nav>
  <div class="settings-bg">
    <div class="container" style="width:100%;">
      <div class="card mb-md">
        <h2 class="h2 mb-sm"><i class="fa fa-user-cog"></i> Account Settings</h2>
        <form>
          <div class="form-group mb-md">
            <label for="username" class="mb-xs">Username</label>
            <input type="text" id="username" name="username" required />
          </div>
          <div class="form-group mb-md">
            <label for="email" class="mb-xs">Email</label>
            <input type="email" id="email" name="email" required />
          </div>
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Changes</button>
        </form>
      </div>
      <div class="card mb-md">
        <h2 class="h2 mb-sm"><i class="fa fa-bell"></i> Notification Settings</h2>
        <form>
          <div class="form-group mb-md">
            <label for="notify-email" class="mb-xs">Email Notifications</label>
            <input type="checkbox" id="notify-email" name="notify-email" /> Enable
          </div>
          <div class="form-group mb-md">
            <label for="notify-sms" class="mb-xs">SMS Notifications</label>
            <input type="checkbox" id="notify-sms" name="notify-sms" /> Enable
          </div>
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Notifications</button>
        </form>
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