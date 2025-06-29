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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard - OutageSys</title>
  <link rel="stylesheet" href="css/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    .dashboard-section { margin-bottom: 32px; }
    .dashboard-section h2 { color: #2563eb; margin-bottom: 12px; }
    .dashboard-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    .dashboard-table th, .dashboard-table td { border: 1px solid #e5e7eb; padding: 8px 12px; text-align: left; }
    .dashboard-table th { background: #f1f5f9; }
    .assign-form { display: flex; gap: 12px; align-items: center; margin-bottom: 18px; }
    .assign-form select, .assign-form button { padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; }
    .assign-form button { background: #2563eb; color: #fff; border: none; cursor: pointer; transition: background 0.2s; }
    .assign-form button:hover { background: #1e40af; }
    .assignment-list { margin-top: 18px; }
    .assignment-list .remove-btn { color: #e53e3e; cursor: pointer; border: none; background: none; }
    .toggle-mode { margin: 0 0 18px 0; background: #f1f5f9; border: none; border-radius: 20px; padding: 8px 16px; font-size: 1rem; cursor: pointer; color: #2563eb; display: flex; align-items: center; gap: 8px; transition: background 0.2s, color 0.2s; }
    body.dark .toggle-mode { background: #23272f; color: #60a5fa; }
    .msg-area { margin-bottom: 12px; min-height: 24px; }
    .msg-success { color: #16a34a; }
    .msg-error { color: #e53e3e; }
  </style>
</head>
<body class="admin">
  <!-- Topbar with Home and Avatar -->
  <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 32px;background:var(--card-bg);box-shadow:0 2px 8px var(--shadow);">
    <a href="index.php" style="font-size:1.2rem;font-weight:600;color:var(--primary);text-decoration:none;display:flex;align-items:center;gap:8px;">
      <i class="fa fa-home" style="width:22px;height:22px;"></i> Home
    </a>
    <div class="avatar" style="width:40px;height:40px;border-radius:50%;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:1.2rem;">
      <?php if (!empty($avatar)): ?>
        <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" style="width:100%;height:100%;border-radius:50%;object-fit:cover;" loading="lazy" />
      <?php elseif ($name === 'admin'): ?>
        <i class="fa fa-user" style="width:24px;height:24px;color:#fff;"></i>
      <?php else: ?>
        <?php echo strtoupper(substr($name,0,1)); ?>
      <?php endif; ?>
    </div>
  </div>
  <div class="dashboard">
    <?php include 'sidebar.php'; ?>
    <main class="main-content page-transition" role="main">
      <div aria-live="polite" id="ariaLiveRegion"></div>
      <nav aria-label="Breadcrumb" style="margin-bottom:12px;"><ol style="list-style:none;display:flex;gap:8px;padding:0;"><li><a href="index.php">Home</a></li><li>›</li><li>Admin Dashboard</li></ol></nav>
      <div class="dashboard-grid">
        <div class="card">
          <h2>Welcome, <?php echo isset($name) ? htmlspecialchars($name) : 'Admin'; ?> (Admin)</h2>
          <!-- Add any admin summary info here -->
        </div>
        <div class="card">
          <h2>Outage Report Tickets</h2>
          <div class="msg-area" id="ticketMsg"></div>
          <table class="dashboard-table" id="ticketTable">
            <thead>
              <tr><th>ID</th><th>User</th><th>User Phone</th><th>Location</th><th>Time</th><th>Description</th><th>Status</th><th>Technician</th><th>Technician Phone</th><th>Action</th></tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
        <div class="card">
          <h2>Assign Substations to Technicians</h2>
          <div class="msg-area" id="assignMsg"></div>
          <form class="assign-form" id="assignForm">
            <select id="technicianSelect" required></select>
            <select id="substationSelect" required></select>
            <button type="submit"><i class="fa fa-plus"></i> Assign</button>
          </form>
          <div class="assignment-list">
            <h3>Current Assignments</h3>
            <table class="dashboard-table" id="assignmentTable">
              <thead>
                <tr><th>Technician</th><th>Substation</th><th>Action</th></tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
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
    </main>
  </div>
  <div class="loading-overlay" id="globalLoading" aria-hidden="true"><span class="custom-loader"></span></div>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
    function setMode(dark) {
      document.body.classList.toggle('dark', dark);
      document.getElementById('modeIcon').className = dark ? 'fa fa-sun' : 'fa fa-moon';
      document.getElementById('modeText').textContent = dark ? 'Light Mode' : 'Dark Mode';
      localStorage.setItem('theme', dark ? 'dark' : 'light');
    }
    function toggleMode() {
      setMode(!document.body.classList.contains('dark'));
    }
    if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark');

    // Fetch technicians
    function fetchTechnicians() {
      fetch('api/users.php', { credentials: 'same-origin' })
        .then(res => res.json())
        .then(users => {
          const techSelect = document.getElementById('technicianSelect');
          techSelect.innerHTML = '<option value="">Select Technician</option>';
          users.filter(u => u.role === 'technician').forEach(u => {
            techSelect.innerHTML += `<option value="${u.id}">${u.username}</option>`;
          });
        });
    }
    // Fetch substations
    function fetchSubstations() {
      fetch('api/substations.php', { credentials: 'same-origin' })
        .then(res => res.json())
        .then(subs => {
          const subSelect = document.getElementById('substationSelect');
          subSelect.innerHTML = '<option value="">Select Substation</option>';
          subs.forEach(s => {
            subSelect.innerHTML += `<option value="${s.id}">${s.name}</option>`;
          });
        });
    }
    // Fetch assignments
    function fetchAssignments() {
      fetch('api/assignments.php', { credentials: 'same-origin' })
        .then(res => res.json())
        .then(assignments => {
          const tbody = document.querySelector('#assignmentTable tbody');
          tbody.innerHTML = '';
          if (!assignments.length) {
            tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;">No assignments found.</td></tr>';
            return;
          }
          assignments.forEach(a => {
            tbody.innerHTML += `<tr>
              <td>${a.technician}</td>
              <td>${a.substation}</td>
              <td><button class='remove-btn' onclick='removeAssignment(${a.id})'><i class="fa fa-trash"></i> Remove</button></td>
            </tr>`;
          });
        });
    }
    // Assign substation to technician
    document.getElementById('assignForm').onsubmit = function(e) {
      e.preventDefault();
      const technician_id = document.getElementById('technicianSelect').value;
      const substation_id = document.getElementById('substationSelect').value;
      const msg = document.getElementById('assignMsg');
      msg.textContent = '';
      msg.className = 'msg-area';
      if (!technician_id || !substation_id) {
        msg.textContent = 'Please select both a technician and a substation.';
        msg.classList.add('msg-error');
        return;
      }
      fetch('api/assignments.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ technician_id, substation_id })
      })
      .then(res => res.json())
      .then(resp => {
        if (resp.success) {
          msg.textContent = 'Assignment successful!';
          msg.classList.add('msg-success');
          fetchAssignments();
        } else {
          msg.textContent = resp.error || 'Assignment failed.';
          msg.classList.add('msg-error');
        }
      })
      .catch(() => {
        msg.textContent = 'Assignment failed due to network error.';
        msg.classList.add('msg-error');
      });
    };
    // Remove assignment
    function removeAssignment(id) {
      if (!confirm('Remove this assignment?')) return;
      fetch('api/assignments.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ id })
      })
      .then(res => res.json())
      .then(resp => {
        if (resp.success) fetchAssignments();
        else alert('Failed to remove assignment.');
      });
    }
    // Initial fetch
    fetchTechnicians();
    fetchSubstations();
    fetchAssignments();

    // Fetch tickets for admin
    function fetchTickets() {
      fetch('api/tickets.php', { credentials: 'same-origin' })
        .then(res => res.json())
        .then(tickets => {
          const tbody = document.querySelector('#ticketTable tbody');
          tbody.innerHTML = '';
          if (!tickets.length) {
            tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;">No tickets found.</td></tr>';
            return;
          }
          tickets.forEach(t => {
            let techInfo = t.technician ? `${t.technician}` : '-';
            let techPhone = t.technician_phone || '-';
            let userPhone = t.user_phone || '-';
            let techAssign = '';
            if (t.status === 'Submitted') {
              techAssign = `<select id='assignTech_${t.id}'>` +
                allTechnicians.map(tech => `<option value='${tech.id}'>${tech.username} (${tech.phone||'-'})</option>`).join('') +
                `</select> <button onclick='assignTech(${t.id})'>Assign</button>`;
            }
            tbody.innerHTML += `<tr>
              <td>RPT-${String(t.id).padStart(3,'0')}</td>
              <td>${t.username}</td>
              <td>${userPhone}</td>
              <td>${t.location}</td>
              <td>${t.time_started}</td>
              <td>${t.description||'-'}</td>
              <td>${t.status}</td>
              <td>${techInfo}</td>
              <td>${techPhone}</td>
              <td>${techAssign}</td>
            </tr>`;
          });
        });
    }
    fetchTickets();
    // Assign technician to ticket
    window.assignTech = function(id) {
      const techId = document.getElementById('assignTech_' + id).value;
      fetch('api/tickets.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ id, status: 'Assigned', technician_id: techId })
      })
      .then(res => res.json())
      .then(resp => {
        document.getElementById('ticketMsg').textContent = resp.success ? 'Technician assigned!' : (resp.error||'Failed');
        fetchTickets();
      });
    }
    // Fetch technician suggestions
    function fetchSuggestions() {
      fetch('api/notifications.php?suggestions=1', { credentials: 'same-origin' })
        .then(res => res.json())
        .then(suggestions => {
          const tbody = document.querySelector('#suggestionTable tbody');
          tbody.innerHTML = '';
          if (!suggestions.length) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">No suggestions found.</td></tr>';
            return;
          }
          suggestions.forEach(s => {
            tbody.innerHTML += `<tr>
              <td>${s.technician||'-'}</td>
              <td>${s.suggestion}</td>
              <td>${s.created_at||'-'}</td>
              <td><button onclick="deleteSuggestion(${s.id})" style="color:#e53e3e;background:none;border:none;cursor:pointer;">Delete</button></td>
            </tr>`;
          });
        });
    }
    fetchSuggestions();

    // Fetch technicians for assignment dropdown
    let allTechnicians = [];
    function fetchAllTechnicians(cb) {
      fetch('api/users.php', { credentials: 'same-origin' })
        .then(res => res.json())
        .then(users => {
          allTechnicians = users.filter(u => u.role === 'technician');
          if (cb) cb();
        });
    }
    fetchAllTechnicians();
    // Fetch user updates for admin
    function fetchUserUpdates() {
      fetch('api/user_updates.php', { credentials: 'same-origin' })
        .then(res => res.json())
        .then(updates => {
          const tbody = document.querySelector('#userUpdatesTable tbody');
          tbody.innerHTML = '';
          if (!updates.length) {
            tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;">No updates found.</td></tr>';
            return;
          }
          updates.forEach(u => {
            tbody.innerHTML += `<tr>
              <td>${u.username||'-'}</td>
              <td>${u.type}</td>
              <td>${u.created_at||'-'}</td>
            </tr>`;
          });
        });
    }
    fetchUserUpdates();
    // Optionally, refresh every 30 seconds
    setInterval(fetchUserUpdates, 30000);

    // Cookie consent banner
    if (!localStorage.getItem('cookieConsent')) {
      const banner = document.createElement('div');
      banner.innerHTML = '<div style="background:#2563eb;color:#fff;padding:12px;text-align:center;z-index:9999;position:fixed;bottom:0;width:100%;">This site uses cookies for analytics and user experience. <button style="margin-left:12px;padding:4px 12px;border:none;border-radius:4px;background:#fff;color:#2563eb;cursor:pointer;" onclick="localStorage.setItem(\'cookieConsent\',1);this.parentNode.remove();">OK</button></div>';
      document.body.appendChild(banner);
    }

    function showLoadingOverlay() {
      const overlay = document.getElementById('globalLoading');
      overlay.setAttribute('aria-hidden', 'false');
      setTimeout(() => overlay.setAttribute('aria-hidden', 'true'), 2000); // Demo: hide after 2s
    }

    function showFeedback(type) {
      const region = document.getElementById('ariaLiveRegion');
      if (type === 'success') {
        document.getElementById('feedbackSuccess').classList.add('show');
        document.getElementById('feedbackSuccess').setAttribute('aria-hidden', 'false');
        region.textContent = 'Action successful!';
        setTimeout(() => {
          document.getElementById('feedbackSuccess').classList.remove('show');
          document.getElementById('feedbackSuccess').setAttribute('aria-hidden', 'true');
        }, 2000);
      } else {
        document.getElementById('feedbackError').classList.add('show');
        document.getElementById('feedbackError').setAttribute('aria-hidden', 'false');
        region.textContent = 'Action failed!';
        setTimeout(() => {
          document.getElementById('feedbackError').classList.remove('show');
          document.getElementById('feedbackError').setAttribute('aria-hidden', 'true');
        }, 2000);
      }
    }
  </script>
</body>
</html> 