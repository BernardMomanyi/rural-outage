<?php
session_start();
require_once 'db.php';
$user = $user ?? null;
if (!$user && isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id=?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
$avatar = isset($user['avatar']) ? $user['avatar'] : '';
$name = isset($user['name']) ? $user['name'] : (isset($_SESSION['username']) ? $_SESSION['username'] : 'U');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
  header('Location: login.php');
  exit;
}
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
// Fetch assigned outage tickets
$assigned_tickets = $pdo->prepare('SELECT uo.*, u.username as user FROM user_outages uo LEFT JOIN users u ON uo.user_id = u.id WHERE uo.technician_id=? ORDER BY uo.created_at DESC');
$assigned_tickets->execute([$user_id]);
$assigned_tickets = $assigned_tickets->fetchAll(PDO::FETCH_ASSOC);
// Fetch new assignments (unassigned or just assigned to this technician)
$new_assignments = $pdo->query('SELECT uo.*, u.username as user FROM user_outages uo LEFT JOIN users u ON uo.user_id = u.id WHERE (uo.technician_id IS NULL OR uo.technician_id=' . intval($user_id) . ') AND uo.status IN ("Submitted","Assigned") ORDER BY uo.created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
// Fetch maintenance tasks (simulate)
$maintenance_tasks = [
  ['substation' => 'Naivasha', 'date' => '2024-07-01', 'task' => 'Transformer check'],
  ['substation' => 'Mombasa', 'date' => '2024-07-03', 'task' => 'Line inspection'],
];
// Fetch equipment/substation logs (simulate)
$equipment_logs = [
  ['substation' => 'Naivasha', 'fault' => 'Transformer replaced', 'date' => '2024-06-10'],
  ['substation' => 'Mombasa', 'fault' => 'Line fault', 'date' => '2024-05-22'],
];
// Fetch work schedule (simulate)
$work_schedule = [
  ['job' => 'Repair outage at Nairobi', 'date' => '2024-07-02', 'status' => 'Scheduled'],
  ['job' => 'Maintenance at Naivasha', 'date' => '2024-07-03', 'status' => 'Scheduled'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Technician Dashboard - OutageSys</title>
  <link rel="stylesheet" href="css/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <style>
    #map { height: 350px; width: 100%; margin-bottom: 24px; border-radius: 12px; }
    .dashboard-section { margin-bottom: 32px; }
    .dashboard-section h2 { color: #2563eb; margin-bottom: 12px; }
    .dashboard-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    .dashboard-table th, .dashboard-table td { border: 1px solid #e5e7eb; padding: 8px 12px; text-align: left; }
    .dashboard-table th { background: #f1f5f9; }
    .toggle-mode { margin: 0 0 18px 0; background: #f1f5f9; border: none; border-radius: 20px; padding: 8px 16px; font-size: 1rem; cursor: pointer; color: #2563eb; display: flex; align-items: center; gap: 8px; transition: background 0.2s, color 0.2s; }
    body.dark .toggle-mode { background: #23272f; color: #60a5fa; }
    .summary { background: #e0eafc; border-radius: 12px; padding: 24px; margin-bottom: 24px; }
    .ticket-table, .styled-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    .ticket-table th, .ticket-table td, .styled-table th, .styled-table td { border: 1px solid #e5e7eb; padding: 8px 12px; text-align: left; }
    .ticket-table th, .styled-table th { background: #f1f5f9; }
    .status-badge { display: inline-block; padding: 4px 12px; border-radius: 8px; font-weight: 600; }
    .status-Submitted { background: #fef9c3; color: #92400e; }
    .status-Assigned { background: #dbeafe; color: #1e40af; }
    .status-InProgress { background: #fef08a; color: #92400e; }
    .status-Resolved { background: #d1fae5; color: #065f46; }
    .maintenance-list, .logs-list, .schedule-list { list-style: none; padding: 0; }
    .maintenance-list li, .logs-list li, .schedule-list li { background: #f1f5f9; margin-bottom: 8px; padding: 10px 14px; border-radius: 8px; }
    .notes-form textarea { width: 100%; margin-bottom: 8px; border-radius: 6px; border: 1px solid #e0eafc; padding: 8px; }
    .notes-form input[type='file'] { margin-bottom: 8px; }
    .notes-form button { background: #2563eb; color: #fff; border: none; border-radius: 6px; padding: 8px 18px; font-size: 1rem; cursor: pointer; }
  </style>
</head>
<body class="technician">
  <!-- Topbar with Home and Avatar -->
  <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 32px;background:var(--card-bg);box-shadow:0 2px 8px var(--shadow);">
    <a href="index.php" style="font-size:1.2rem;font-weight:600;color:var(--primary);text-decoration:none;display:flex;align-items:center;gap:8px;">
      <i data-lucide="home" style="width:22px;height:22px;"></i> Home
    </a>
    <div class="avatar" style="width:40px;height:40px;border-radius:50%;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:1.2rem;">
      <?php if (!empty($user['avatar'])): ?>
        <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" style="width:100%;height:100%;border-radius:50%;object-fit:cover;" loading="lazy" />
      <?php elseif ((isset($user['name']) ? $user['name'] : '') === 'admin'): ?>
        <i data-lucide="user" style="width:24px;height:24px;color:#fff;"></i>
      <?php else: ?>
        <?php echo strtoupper(substr(isset($user['name']) ? $user['name'] : 'T',0,1)); ?>
      <?php endif; ?>
    </div>
  </div>
  <div class="dashboard">
    <?php include 'sidebar.php'; ?>
    <main class="main-content page-transition" role="main">
      <div aria-live="polite" id="ariaLiveRegion"></div>
      <nav aria-label="Breadcrumb" style="margin-bottom:12px;"><ol style="list-style:none;display:flex;gap:8px;padding:0;"><li><a href="index.php">Home</a></li><li>›</li><li>Technician Dashboard</li></ol></nav>
      <div class="dashboard-grid">
        <div class="card">
          <h2>Welcome, <?php echo isset($user['name']) ? htmlspecialchars($user['name']) : 'Technician'; ?> (Technician)</h2>
          <!-- Add any technician summary info here -->
        </div>
        <div class="card">
          <h2>Assigned Substations</h2>
          <table class="dashboard-table" id="assignedTable">
            <thead>
              <tr><th>Name</th><th>County</th><th>Status</th><th>Risk</th><th>Latitude</th><th>Longitude</th><th>Maintenance</th></tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
        <div class="card">
          <h2>Assigned Tickets</h2>
          <table class="ticket-table">
            <thead>
              <tr><th>ID</th><th>User</th><th>User Phone</th><th>Location</th><th>Status</th><th>Description</th><th>Update</th></tr>
            </thead>
            <tbody id="techTickets">
              <!-- Populated by JS -->
            </tbody>
          </table>
        </div>
        <div class="card">
          <h2>Loading Example</h2>
          <div class="skeleton" style="width: 80%; height: 24px;"></div>
          <div class="skeleton" style="width: 60%; height: 18px;"></div>
          <div class="skeleton" style="width: 90%; height: 18px;"></div>
        </div>
      </div>
      <footer class="footer" role="contentinfo">
        <div>&copy; 2024 OutageSys | <a href="privacy_policy.html">Privacy Policy</a></div>
      </footer>
      <button class="btn btn--primary" type="button" onclick="showLoadingOverlay()"><i class="fa fa-plus"></i> Add New</button>
      <div class="btn-group">
        <button class="btn btn--secondary" type="button">Edit</button>
        <button class="btn btn--danger" type="button" onclick="showLoadingOverlay()">Delete</button>
      </div>
    </main>
  </div>
  <div class="loading-overlay" id="globalLoading" aria-hidden="true"><span class="custom-loader"></span></div>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>
    let map;
    let markers = [];
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
    function fetchSubstationsAndAssignments() {
      Promise.all([
        fetch('api/assignments.php', { credentials: 'same-origin' }).then(res => res.json()),
        fetch('api/substations.php', { credentials: 'same-origin' }).then(res => res.json())
      ]).then(([assignments, subs]) => {
        const assignedIds = assignments.map(a => Number(a.substation_id));
        const assigned = subs.filter(s => assignedIds.includes(Number(s.id)));
        renderTable(assigned);
        renderMap(subs, assignedIds);
      });
    }
    function renderTable(subs) {
      const tbody = document.querySelector('#assignedTable tbody');
      tbody.innerHTML = '';
      if (!subs.length) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">No assigned substations.</td></tr>';
        return;
      }
      subs.forEach(s => {
        tbody.innerHTML += `<tr>
          <td>${s.name}</td>
          <td>${s.county}</td>
          <td>${s.status}</td>
          <td>${s.risk}</td>
          <td>${s.latitude}</td>
          <td>${s.longitude}</td>
          <td>${s.maintenance_date || '-'}</td>
        </tr>`;
      });
    }
    function renderMap(subs, assignedIds) {
      if (!map) {
        map = L.map('map').setView([0.0236, 37.9062], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 18,
          attribution: '\u00a9 OpenStreetMap contributors'
        }).addTo(map);
      }
      markers.forEach(m => map.removeLayer(m));
      markers = [];
      subs.forEach(s => {
        if (s.latitude && s.longitude) {
          let marker;
          if (assignedIds.includes(Number(s.id))) {
            marker = L.marker([s.latitude, s.longitude], {
              icon: L.icon({
                iconUrl: 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/images/marker-icon-2x-blue.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowUrl: 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/images/marker-shadow.png',
                shadowSize: [41, 41]
              })
            });
          } else {
            marker = L.marker([s.latitude, s.longitude], {
              icon: L.icon({
                iconUrl: 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/images/marker-icon-2x-red.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowUrl: 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/images/marker-shadow.png',
                shadowSize: [41, 41]
              })
            });
          }
          marker.addTo(map).bindPopup(`<b>${s.name}</b><br>Status: ${s.status}<br>Risk: ${s.risk}<br>Maintenance: ${s.maintenance_date || 'N/A'}`);
          markers.push(marker);
        }
      });
    }
    fetchSubstationsAndAssignments();
    // Remove Data Analytics from sidebar
    document.querySelectorAll('.sidebar nav a').forEach(a => {
      if (a.textContent.includes('Data Analytics')) a.remove();
    });
    // Fetch maintenance tasks for technician
    function fetchTechMaintenance() {
      fetch('api/maintenance.php', { credentials: 'same-origin' })
        .then(res => res.json())
        .then(tasks => {
          const ul = document.getElementById('techMaintenance');
          ul.innerHTML = '';
          if (!tasks.length) {
            ul.innerHTML = '<li>No maintenance tasks.</li>';
            return;
          }
          tasks.forEach(t => {
            ul.innerHTML += `<li>${t.substation} - ${t.task} (${t.date})${t.technician ? ' - ' + t.technician : ''}</li>`;
          });
        });
    }
    fetchTechMaintenance();
    // Fetch equipment/substation logs (simulate for now)
    function fetchTechLogs() {
      const logs = [
        { substation: 'Naivasha', log: 'Transformer replaced', date: '2024-06-10' },
        { substation: 'Mombasa', log: 'Line fault', date: '2024-05-22' }
      ];
      const ul = document.getElementById('techLogs');
      ul.innerHTML = '';
      logs.forEach(l => {
        ul.innerHTML += `<li>${l.substation} - ${l.log} (${l.date})</li>`;
      });
    }
    fetchTechLogs();
    // Suggestion form POST
    document.getElementById('suggestionForm').onsubmit = function(e) {
      e.preventDefault();
      const suggestion = document.getElementById('suggestionText').value;
      fetch('api/notifications.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        credentials: 'same-origin',
        body: 'suggestion=' + encodeURIComponent(suggestion)
      })
      .then(res => res.json())
      .then(resp => {
        document.getElementById('suggestionMsg').textContent = resp.success ? 'Suggestion submitted!' : (resp.error||'Failed');
        this.reset();
      });
    };
    // Fetch current outage tickets for technician
    function fetchTechTickets() {
      fetch('api/tech_tickets.php', { credentials: 'same-origin' })
        .then(res => res.json())
        .then(tickets => {
          const tbody = document.getElementById('techTickets');
          tbody.innerHTML = '';
          if (!tickets.length) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">No tickets found.</td></tr>';
            return;
          }
          tickets.forEach(t => {
            tbody.innerHTML += `<tr>
              <td>RPT-${String(t.id).padStart(3,'0')}</td>
              <td>${t.user}</td>
              <td>${t.user_phone||'-'}</td>
              <td>${t.location}</td>
              <td>${t.status}</td>
              <td>${t.description||'-'}</td>
              <td><button onclick="updateTechTicket(${t.id})">Update</button></td>
            </tr>`;
          });
        });
    }
    fetchTechTickets();
    // Update ticket status (demo, can be expanded)
    window.updateTechTicket = function(id) {
      const status = prompt('Enter new status (InProgress, Resolved, NeedsAction):');
      const notes = prompt('Enter notes (optional):');
      if (!status) return;
      fetch('api/tech_tickets.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ id, status, notes })
      })
      .then(res => res.json())
      .then(resp => {
        alert(resp.success ? 'Ticket updated!' : (resp.error||'Failed'));
        fetchTechTickets();
      });
    }
    // Cookie consent banner
    if (!localStorage.getItem('cookieConsent')) {
      const banner = document.createElement('div');
      banner.innerHTML = '<div style="background:#2563eb;color:#fff;padding:12px;text-align:center;z-index:9999;position:fixed;bottom:0;width:100%;">This site uses cookies for analytics and user experience. <button style="margin-left:12px;padding:4px 12px;border:none;border-radius:4px;background:#fff;color:#2563eb;cursor:pointer;" onclick="localStorage.setItem(\'cookieConsent\',1);this.parentNode.remove();">OK</button></div>';
      document.body.appendChild(banner);
    }
    // Show skeleton while loading (simulate async)
    const dashboardSection = document.getElementById('dashboard-loading');
    dashboardSection.style.display = 'block';
    setTimeout(() => { dashboardSection.style.display = 'none'; }, 1200);
    // Lucide icons
    lucide.createIcons();
    // Dark mode toggle
    function toggleDarkMode() {
      document.body.classList.toggle('dark');
      localStorage.setItem('theme', document.body.classList.contains('dark') ? 'dark' : 'light');
    }
    if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark');
    function showLoadingOverlay() {
      const overlay = document.getElementById('globalLoading');
      overlay.setAttribute('aria-hidden', 'false');
      setTimeout(() => overlay.setAttribute('aria-hidden', 'true'), 2000); // Demo: hide after 2s
    }
  </script>
</body>
</html> 