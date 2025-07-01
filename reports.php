<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit;
}
$role = $_SESSION['role'];
$username = $_SESSION['username'];
$dashboard_link = 'user_dashboard.php';
if ($role === 'admin') $dashboard_link = 'admin_dashboard.php';
if ($role === 'technician') $dashboard_link = 'technician_dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Prediction Reports - OutageSys</title>
  <link rel="stylesheet" href="css/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    .toggle-mode { margin: 0 0 18px 0; background: #f1f5f9; border: none; border-radius: 20px; padding: 8px 16px; font-size: 1rem; cursor: pointer; color: #2563eb; display: flex; align-items: center; gap: 8px; transition: background 0.2s, color 0.2s; }
    body.dark .toggle-mode { background: #23272f; color: #60a5fa; }
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
          <li>Reports</li>
        </ol>
      </nav>
      <div class="model-accuracy">
        <div class="card">Model Accuracy: <span class="accuracy-value">92%</span></div>
      </div>
      <form method="get" class="search-form">
        <input type="search" name="q" placeholder="Search reports..." aria-label="Search reports" class="search-input" />
      </form>
      <div class="dashboard-grid">
        <div class="card">
          <h2>Outage Reports</h2>
          <div class="margin-bottom">
            <button class="cta-btn" onclick="exportCSV()"><i class="fa fa-file-csv"></i> Export CSV</button>
          </div>
          <table class="styled-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Substation</th>
                <th>County</th>
                <th>Risk</th>
                <th>Status</th>
                <th>Latitude</th>
                <th>Longitude</th>
              </tr>
            </thead>
            <tbody id="reportsTableBody">
              <tr><td colspan="7" class="table-loading">Loading...</td></tr>
            </tbody>
          </table>
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
    function setMode(dark) {
      document.body.classList.toggle('dark', dark);
      document.getElementById('modeIcon').className = dark ? 'fa fa-sun' : 'fa fa-moon';
      document.getElementById('modeText').textContent = dark ? 'Light Mode' : 'Dark Mode';
      localStorage.setItem('darkMode', dark ? '1' : '0');
    }
    function toggleMode() {
      setMode(!document.body.classList.contains('dark'));
    }
    if (localStorage.getItem('darkMode') === '1') setMode(true);

    let lastData = [];
    function fetchReports() {
      fetch('api/reports.php', { credentials: 'same-origin' })
        .then(res => res.json())
        .then(data => {
          lastData = data;
          const tbody = document.getElementById('reportsTableBody');
          tbody.innerHTML = '';
          if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="table-empty">No reports found.</td></tr>';
            return;
          }
          data.forEach(r => {
            let row = `<tr>
              <td>${r.id}</td>
              <td>${r.name}</td>
              <td>${r.county}</td>
              <td class="risk-${r.risk.toLowerCase()}">${r.risk}</td>
              <td class="status-${r.status.toLowerCase()}">${r.status}</td>
              <td>${r.latitude}</td>
              <td>${r.longitude}</td>
            </tr>`;
            tbody.innerHTML += row;
          });
        });
    }
    fetchReports();

    function exportCSV() {
      if (!lastData.length) return alert('No data to export!');
      let csv = 'ID,Substation,County,Risk,Status,Latitude,Longitude\n';
      lastData.forEach(r => {
        csv += `${r.id},${r.name},${r.county},${r.risk},${r.status},${r.latitude},${r.longitude}\n`;
      });
      const blob = new Blob([csv], { type: 'text/csv' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'outage_reports.csv';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
    }

    // Cookie consent banner
    if (!localStorage.getItem('cookieConsent')) {
      const banner = document.createElement('div');
      banner.className = 'cookie-banner';
      banner.innerHTML = 'This site uses cookies for analytics and user experience. <button class="cookie-btn" onclick="localStorage.setItem(\'cookieConsent\',1);this.parentNode.remove();">OK</button>';
      document.body.appendChild(banner);
    }
  </script>
</body>
</html> 