<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit;
}
$username = $_SESSION['username'];
$avatar = isset($user['avatar']) ? $user['avatar'] : '';
$name = isset($user['name']) ? $user['name'] : (isset($_SESSION['username']) ? $_SESSION['username'] : 'U');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Technicians - OutageSys Admin</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    body { min-height: 100vh; }
    .admin-bg {
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
  </style>
</head>
<body>
  <!-- Breadcrumbs navigation -->
  <nav aria-label="Breadcrumb" class="breadcrumbs-nav" style="margin: 1.5rem auto 0 auto; max-width:900px; background:transparent;">
    <ol class="breadcrumbs" style="display:flex; flex-wrap:wrap; gap:0.5em; list-style:none; padding:0; margin:0; font-size:1rem; background:transparent;">
      <li><a href="admin_dashboard.php" class="breadcrumb-link"><i class="fa fa-tachometer-alt"></i> Admin Dashboard</a></li>
      <li style="color:var(--color-secondary);">&gt;</li>
      <li class="breadcrumb-current" style="color:var(--color-primary); font-weight:600;"><i class="fa fa-tools"></i> Technician Management</li>
    </ol>
  </nav>
  <button class="toggle-mode" id="darkModeToggle">
    <i class="fa fa-moon" id="modeIcon"></i> 
    <span id="modeText">Dark Mode</span>
  </button>
  <div class="admin-bg">
    <div class="container" style="width:100%;">
      <div class="card mb-md">
        <h2 class="h2 mb-sm"><i class="fa fa-users-cog"></i> Technicians</h2>
        <p class="mb-md">Manage and assign technicians to substations and tickets.</p>
        <div id="techniciansTableContainer">
          <table class="styled-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Assigned Substations</th>
              </tr>
            </thead>
            <tbody id="techniciansTableBody">
              <tr><td colspan="4" style="text-align:center;">Loading...</td></tr>
            </tbody>
          </table>
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

    // Fetch and display technicians and their assigned substations
    async function loadTechnicians() {
      const tbody = document.getElementById('techniciansTableBody');
      tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Loading...</td></tr>';
      try {
        const res = await fetch('api/users.php?action=get_users&role=technician');
        const data = await res.json();
        if (data.success && Array.isArray(data.users)) {
          if (data.users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">No technicians found.</td></tr>';
            return;
          }
          tbody.innerHTML = '';
          data.users.forEach(tech => {
            tbody.innerHTML += `<tr>
              <td>${tech.id}</td>
              <td>${tech.first_name || ''} ${tech.last_name || ''} <span style='color:#888;'>@${tech.username}</span></td>
              <td>${tech.email || ''}</td>
              <td>${tech.assigned_substations || '<span style=\'color:#aaa;\'>None</span>'}</td>
            </tr>`;
          });
        } else {
          tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:#c00;">Failed to load technicians.</td></tr>';
        }
      } catch (e) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:#c00;">Error loading technicians.</td></tr>';
      }
    }
    document.addEventListener('DOMContentLoaded', loadTechnicians);
  </script>
</body>
</html> 