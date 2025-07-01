<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Settings - OutageSys</title>
  <link rel="stylesheet" href="css/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    .settings-tabs { display: flex; gap: 12px; margin-bottom: 24px; }
    .settings-tab { padding: 10px 24px; border-radius: 8px 8px 0 0; background: #f1f5f9; color: #2563eb; cursor: pointer; border: none; font-size: 1rem; font-weight: 500; transition: background 0.2s; }
    .settings-tab.active { background: #2563eb; color: #fff; }
    .settings-section { display: none; background: #fff; border-radius: 0 0 12px 12px; box-shadow: 0 2px 8px #0001; padding: 32px; }
    .settings-section.active { display: block; }
    .settings-form label { display: block; margin-top: 18px; font-weight: 500; }
    .settings-form input, .settings-form select, .settings-form textarea { width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; margin-top: 6px; }
    .settings-form input[type="checkbox"] { width: auto; }
    .settings-form .form-group { margin-bottom: 18px; }
    .settings-form button { background: #2563eb; color: #fff; border: none; border-radius: 6px; padding: 10px 24px; font-size: 1rem; cursor: pointer; margin-top: 18px; }
    .settings-form .success { color: #16a34a; margin-bottom: 12px; }
    .avatar { width: 64px; height: 64px; border-radius: 50%; margin-bottom: 12px; }
    .section-title { margin-top: 32px; color: #1e293b; font-size: 1.1rem; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
  </style>
</head>
<body class="admin">
  <div class="dashboard">
    <?php include 'sidebar.php'; ?>
    <main class="main-content" role="main">
      <nav aria-label="Breadcrumb" class="breadcrumb">
        <ol>
          <li><a href="index.php">Home</a></li>
          <li>›</li>
          <li>Settings</li>
        </ol>
      </nav>
      <div class="dashboard-grid">
        <div class="card">
          <h2>Profile</h2>
          <img src="<?php echo $user['avatar']; ?>" class="avatar" alt="Avatar" loading="lazy" />
          <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" />
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" />
          </div>
          <div class="form-group">
            <label>Language</label>
            <select name="language">
              <option <?php if($user['language']==='English') echo 'selected'; ?>>English</option>
              <option <?php if($user['language']==='Swahili') echo 'selected'; ?>>Swahili</option>
            </select>
          </div>
          <div class="form-group">
            <label>Theme</label>
            <select name="theme">
              <option value="system" <?php if($user['theme']==='system') echo 'selected'; ?>>System</option>
              <option value="light" <?php if($user['theme']==='light') echo 'selected'; ?>>Light</option>
              <option value="dark" <?php if($user['theme']==='dark') echo 'selected'; ?>>Dark</option>
            </select>
          </div>
        </div>
        <div class="card">
          <h2>Security</h2>
          <div class="form-group">
            <label>Change Password</label>
            <input type="password" name="current_password" placeholder="Current password" />
            <input type="password" name="new_password" placeholder="New password" />
          </div>
          <div class="form-group">
            <label>Enable 2FA (UI only)</label>
            <input type="checkbox" name="enable_2fa" />
          </div>
          <div class="form-group">
            <label>Recent Login Activity (Demo)</label>
            <textarea readonly>2024-06-01 10:00 - Windows, Nairobi
2024-05-31 21:12 - Android, Eldoret</textarea>
          </div>
        </div>
        <div class="card">
          <h2>Customization</h2>
          <div class="form-group">
            <label>Dashboard Widgets</label>
            <input type="text" name="dashboard_widgets" value="<?php echo htmlspecialchars(implode(',', $settings['dashboard_widgets'])); ?>" />
          </div>
          <div class="form-group">
            <label>Default Landing Page</label>
            <input type="text" name="default_landing" value="<?php echo htmlspecialchars($settings['default_landing']); ?>" />
          </div>
          <div class="form-group">
            <label>Color Scheme</label>
            <select name="color_scheme">
              <option value="default">Default</option>
              <option value="blue">Blue</option>
              <option value="green">Green</option>
              <option value="custom">Custom</option>
            </select>
          </div>
        </div>
        <div class="card">
          <h2>Organization</h2>
          <div class="form-group">
            <label>Organization Name</label>
            <input type="text" name="org_name" value="<?php echo htmlspecialchars($settings['org_name']); ?>" />
          </div>
          <div class="form-group">
            <label>Logo URL</label>
            <input type="text" name="org_logo" value="<?php echo htmlspecialchars($settings['org_logo']); ?>" />
          </div>
          <div class="form-group">
            <label>Address</label>
            <input type="text" name="org_address" value="<?php echo htmlspecialchars($settings['org_address']); ?>" />
          </div>
          <div class="form-group">
            <label>Working Hours</label>
            <input type="text" name="working_hours" value="<?php echo htmlspecialchars($settings['working_hours']); ?>" />
          </div>
          <div class="form-group">
            <label>Maintenance Window</label>
            <input type="text" name="maintenance_window" value="<?php echo htmlspecialchars($settings['maintenance_window']); ?>" />
          </div>
        </div>
        <div class="card">
          <h2>Notifications</h2>
          <div class="form-group">
            <label>Notification Rules</label>
            <input type="text" name="notification_rules" value="<?php echo htmlspecialchars($settings['notification_rules']); ?>" />
          </div>
          <div class="form-group">
            <label>Notification Channels</label>
            <input type="text" name="notification_channels" value="<?php echo htmlspecialchars(implode(',', $settings['notification_channels'])); ?>" />
          </div>
        </div>
        <div class="card">
          <h2>Data & Privacy</h2>
          <div class="form-group">
            <label>Export My Data</label>
            <button type="button"><i class="fa fa-download"></i> Download Data</button>
          </div>
          <div class="form-group">
            <label>Request Account Deletion</label>
            <button type="button" class="delete-account-btn"><i class="fa fa-trash"></i> Delete My Account</button>
          </div>
        </div>
        <div class="card">
          <h2>Loading Example</h2>
          <div class="skeleton skeleton-80"></div>
          <div class="skeleton skeleton-60"></div>
          <div class="skeleton skeleton-90"></div>
        </div>
      </div>
      <footer class="footer" role="contentinfo">
        <div>&copy; 2024 OutageSys | <a href="privacy_policy.html">Privacy Policy</a></div>
      </footer>
    </main>
  </div>
  <script>
    // Tab switching logic
    document.querySelectorAll('.settings-tab').forEach(tab => {
      tab.addEventListener('click', function() {
        document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.settings-section').forEach(s => s.classList.remove('active'));
        this.classList.add('active');
        document.getElementById(this.dataset.tab).classList.add('active');
      });
    });
    // Cookie consent banner
    if (!localStorage.getItem('cookieConsent')) {
      const banner = document.createElement('div');
      banner.className = 'cookie-banner';
      banner.innerHTML = 'This site uses cookies for analytics and user experience. <button class="cookie-btn" onclick="localStorage.setItem(\'cookieConsent\',1);this.parentNode.remove();">OK</button>';
      document.body.appendChild(banner);
    }
    // Show skeleton while loading (simulate async)
    const settingsSection = document.getElementById('settings-loading');
    settingsSection.style.display = 'block';
    setTimeout(() => { settingsSection.style.display = 'none'; }, 1200);
    // Lucide icons
    lucide.createIcons();
    // Dark mode toggle
    function toggleDarkMode() {
      document.body.classList.toggle('dark');
      localStorage.setItem('theme', document.body.classList.contains('dark') ? 'dark' : 'light');
    }
    if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark');
  </script>
</body>
</html> 