<?php
session_start();
require_once 'db.php';

// Check if user is logged in and has technician role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
  header('Location: login.php');
  exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch user data
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch assigned tickets
$stmt = $pdo->prepare('SELECT uo.*, u.username as user, u.phone as user_phone 
                       FROM user_outages uo 
                       LEFT JOIN users u ON uo.user_id = u.id 
                       WHERE uo.technician_id = ? 
                       ORDER BY uo.created_at DESC');
$stmt->execute([$user_id]);
$assigned_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch pending tickets
$stmt = $pdo->prepare('SELECT uo.*, u.username as user, u.phone as user_phone 
                       FROM user_outages uo 
                       LEFT JOIN users u ON uo.user_id = u.id 
                       WHERE uo.status IN ("Submitted", "Assigned") 
                       ORDER BY uo.created_at DESC');
$stmt->execute();
$pending_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle ticket status updates
$update_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_ticket'])) {
  $ticket_id = intval($_POST['ticket_id']);
  $new_status = $_POST['new_status'];
  $notes = trim($_POST['notes'] ?? '');
  
  $stmt = $pdo->prepare('UPDATE user_outages SET status = ?, notes = ? WHERE id = ?');
  $stmt->execute([$new_status, $notes, $ticket_id]);
  $update_msg = 'Ticket status updated successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Technician Dashboard - OutageSys</title>
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
    .profile-menu #profileDropdown {
      background: var(--color-bg-card, #fff);
      color: var(--color-text, #222);
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.12);
      min-width: 200px;
      min-height: 160px;
      transition: background 0.2s, color 0.2s;
    }
    body.dark-mode .profile-menu #profileDropdown {
      background: #232946;
      color: #f4f4f4;
      border-color: #232946;
    }
    .profile-menu .btn-link, .profile-menu .btn-link:visited {
      background: none;
      color: var(--color-primary, #667eea);
      transition: color 0.2s;
      width: 100%;
      text-align: left;
      padding: 12px 20px;
      display: block;
    }
    .profile-menu .btn-link:hover {
      color: var(--color-accent, #f43f5e);
      background: var(--color-bg, #f4f4f4);
    }
    body.dark-mode .profile-menu .btn-link, body.dark-mode .profile-menu .btn-link:visited {
      color: #90cdf4;
      background: none;
    }
    body.dark-mode .profile-menu .btn-link:hover {
      color: #ffe066;
      background: #232946;
    }
  </style>
</head>
<body>
  <!-- Breadcrumbs navigation -->
  <nav aria-label="Breadcrumb" class="breadcrumbs-nav" style="margin: 1.5rem auto 0 auto; max-width:900px; background:transparent;">
    <ol class="breadcrumbs" style="display:flex; flex-wrap:wrap; gap:0.5em; list-style:none; padding:0; margin:0; font-size:1rem; background:transparent;">
      <li class="breadcrumb-current"><i class="fa fa-tachometer-alt"></i> Technician Dashboard</li>
    </ol>
  </nav>
  <div style="position:fixed; top:24px; right:24px; z-index:100;">
    <div class="profile-menu" style="position:relative; display:inline-block;">
      <button id="profileBtn" class="btn btn-outline" style="border-radius:50%; width:44px; height:44px; padding:0; display:flex; align-items:center; justify-content:center; font-size:1.3rem;">
        <i class="fa fa-user-circle"></i>
      </button>
      <div id="profileDropdown" style="display:none; position:absolute; right:0; top:48px; background:var(--color-bg-card,#fff); box-shadow:0 2px 8px rgba(0,0,0,0.12); border-radius:10px; min-width:200px; min-height:160px;">
        <div style="padding:16px 20px 8px 20px; border-bottom:1px solid var(--color-border,#eee); display:flex; align-items:center; gap:12px;">
          <i class="fa fa-user-circle" style="font-size:2rem; color:var(--color-primary,#667eea);"></i>
          <div style="font-weight:600; font-size:1rem; color:var(--color-primary,#333);">
            <?php echo htmlspecialchars($_SESSION['username'] ?? 'Technician'); ?>
          </div>
        </div>
        <a href="technician_dashboard.php" class="btn btn-link w-100" style="text-align:left; padding:12px 20px; display:block;"><i class="fa fa-tachometer-alt"></i> Technician Dashboard</a>
        <button class="btn btn-link w-100" id="darkModeToggle" style="text-align:left; padding:12px 20px; display:block; color:var(--color-primary,#667eea);"><i class="fa fa-moon" id="modeIcon"></i> <span id="modeText">Dark Mode</span></button>
        <a href="settings.php" class="btn btn-link w-100" style="text-align:left; padding:12px 20px; display:block;"><i class="fa fa-cogs"></i> Settings</a>
        <form action="logout.php" method="post" style="margin:0;">
          <button type="submit" class="btn btn-link w-100" style="text-align:left; padding:12px 20px; color:#e53e3e;"><i class="fa fa-sign-out-alt"></i> Logout</button>
        </form>
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
    const profileBtn = document.getElementById('profileBtn');
    const profileDropdown = document.getElementById('profileDropdown');
    if (profileBtn && profileDropdown) {
      profileBtn.onclick = function(e) {
        e.stopPropagation();
        profileDropdown.style.display = (profileDropdown.style.display === 'block') ? 'none' : 'block';
      };
      document.addEventListener('click', function() {
        profileDropdown.style.display = 'none';
      });
      profileDropdown.onclick = function(e) { e.stopPropagation(); };
    }
    // Move dark mode toggle logic here
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
      darkModeToggle.onclick = function(e) {
        e.preventDefault();
        setMode(!document.body.classList.contains('dark-mode'));
      };
      if (localStorage.getItem('darkMode') === '1') setMode(true);
    }
  </script>
  <div class="dashboard-bg">
    <div class="container" style="width:100%;">
      <div class="grid grid-3 mb-md">
        <div class="card">
          <h2 class="h2 mb-sm"><i class="fa fa-tools"></i> Technician Home</h2>
          <p class="mb-sm">Overview and quick access to technician features.</p>
          <a href="technician_dashboard.php" class="btn btn-primary">Technician Home</a>
        </div>
        <div class="card">
          <h2 class="h2 mb-sm"><i class="fa fa-bolt"></i> Substations</h2>
          <p class="mb-sm">View and manage assigned substations.</p>
          <a href="substations.php" class="btn btn-outline">View Substations</a>
        </div>
        <div class="card">
          <h2 class="h2 mb-sm"><i class="fa fa-file-alt"></i> Reports</h2>
          <p class="mb-sm">View and manage outage reports.</p>
          <a href="reports.php" class="btn btn-outline">View Reports</a>
        </div>
        <div class="card">
          <h2 class="h2 mb-sm"><i class="fa fa-chart-bar"></i> Analytics</h2>
          <p class="mb-sm">View analytics and trends.</p>
          <a href="analytics.php" class="btn btn-outline">View Analytics</a>
        </div>
        <div class="card">
          <h2 class="h2 mb-sm"><i class="fa fa-map"></i> Map View</h2>
          <p class="mb-sm">Visualize substations and outages on the map.</p>
          <a href="map.php" class="btn btn-outline">Map View</a>
        </div>
      </div>
      <!-- My Tickets Section -->
      <div class="card" style="margin-top:2em;">
        <h2 class="h2 mb-sm"><i class="fa fa-ticket-alt"></i> My Tickets</h2>
        <div id="ticketsContainer">
          <div style="text-align:center; padding:2em; color:#6b7280;">
            <i class="fa fa-spinner fa-spin" style="font-size:2em;"></i><br>Loading tickets...
          </div>
        </div>
        <!-- Ticket Update Modal -->
        <div id="ticketModalBg" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
          <div id="ticketModal" style="background:#fff; border-radius:16px; padding:2em; min-width:320px; max-width:95vw; box-shadow:0 8px 32px rgba(0,0,0,0.15); position:relative;">
            <h3 id="modalTitle" style="margin-bottom:1em; color:#2563eb;"><i class="fa fa-edit"></i> Update Ticket</h3>
            <div id="modalAlert" style="display:none; margin-bottom:1em;"></div>
            <form id="ticketForm">
              <input type="hidden" id="modalTicketId" />
              <div style="margin-bottom:1em;"><b>Ticket #:</b> <span id="modalTicketNumber"></span></div>
              <div style="margin-bottom:1em;"><b>User:</b> <span id="modalUser"></span> <span id="modalUserPhone"></span></div>
              <div style="margin-bottom:1em;"><b>Subject:</b> <span id="modalSubject"></span></div>
              <div style="margin-bottom:1em;"><b>Description:</b> <span id="modalDescription"></span></div>
              <div style="margin-bottom:1em;">
                <label><b>Status:</b></label><br>
                <select id="modalStatus" class="form-select" style="width:100%;">
                  <option value="pending">Pending</option>
                  <option value="assigned">Assigned</option>
                  <option value="in_progress">In Progress</option>
                  <option value="resolved">Resolved</option>
                  <option value="closed">Closed</option>
                </select>
              </div>
              <div style="margin-bottom:1em;">
                <label><b>Technician Notes:</b></label><br>
                <textarea id="modalNotes" class="form-input" rows="3" placeholder="Add notes..."></textarea>
              </div>
              <div style="display:flex; gap:1em; justify-content:flex-end;">
                <button type="button" class="btn btn-outline" onclick="closeTicketModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
              </div>
            </form>
          </div>
        </div>
        <div id="ticketNotification" style="position:fixed; top:30px; right:30px; z-index:3000; display:none; background:#22c55e; color:#fff; padding:1em 2em; border-radius:12px; box-shadow:0 8px 25px rgba(0,0,0,0.2);"></div>
        <script>
        let technicianTickets = [];
        function fetchTechnicianTickets() {
          fetch('api/tickets.php?limit=100', { credentials: 'same-origin' })
            .then(res => res.json())
            .then(tickets => {
              if (!Array.isArray(tickets)) {
                document.getElementById('ticketsContainer').innerHTML = '<div style="color:#ef4444; text-align:center;">Error loading tickets.</div>';
                return;
              }
              technicianTickets = tickets;
              if (tickets.length === 0) {
                document.getElementById('ticketsContainer').innerHTML = '<div style="color:#6b7280; text-align:center;">No tickets assigned to you yet.</div>';
                return;
              }
              let html = `<table class='styled-table'><thead><tr><th>Ticket #</th><th>User</th><th>Subject</th><th>Description</th><th>Priority</th><th>Status</th><th>Notes</th><th>Created</th><th>Actions</th></tr></thead><tbody>`;
              tickets.forEach(ticket => {
                html += `<tr>
                  <td>${ticket.ticket_number}</td>
                  <td>${ticket.user_name}<br><span style='font-size:0.85em;color:#888;'>${ticket.user_phone || ''}</span></td>
                  <td>${ticket.subject}</td>
                  <td>${ticket.description}</td>
                  <td><span class='status-badge status-${ticket.priority}'>${ticket.priority}</span></td>
                  <td><span class='status-badge status-${ticket.status}'>${ticket.status}</span></td>
                  <td>${ticket.notes ? ticket.notes.replace(/\n/g,'<br>') : '-'}</td>
                  <td>${ticket.created_at}</td>
                  <td><button class='btn btn-outline btn-sm' onclick='showUpdateTicketModal(${ticket.id})'>Update</button></td>
                </tr>`;
              });
              html += '</tbody></table>';
              document.getElementById('ticketsContainer').innerHTML = html;
            })
            .catch(err => {
              document.getElementById('ticketsContainer').innerHTML = '<div style="color:#ef4444; text-align:center;">Error loading tickets.</div>';
            });
        }
        fetchTechnicianTickets();
        // Modal logic
        function showUpdateTicketModal(ticketId) {
          const ticket = technicianTickets.find(t => t.id == ticketId);
          if (!ticket) return;
          document.getElementById('modalTicketId').value = ticket.id;
          document.getElementById('modalTicketNumber').textContent = ticket.ticket_number;
          document.getElementById('modalUser').textContent = ticket.user_name;
          document.getElementById('modalUserPhone').textContent = ticket.user_phone ? ' ('+ticket.user_phone+')' : '';
          document.getElementById('modalSubject').textContent = ticket.subject;
          document.getElementById('modalDescription').textContent = ticket.description;
          document.getElementById('modalStatus').value = ticket.status;
          document.getElementById('modalNotes').value = ticket.notes || '';
          document.getElementById('ticketModalBg').style.display = 'flex';
        }
        function closeTicketModal() {
          document.getElementById('ticketModalBg').style.display = 'none';
        }
        document.getElementById('ticketForm').onsubmit = function(e) {
          e.preventDefault();
          const ticket_id = document.getElementById('modalTicketId').value;
          const status = document.getElementById('modalStatus').value;
          const notes = document.getElementById('modalNotes').value;
          fetch('api/tickets.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ action: 'update_status', ticket_id, status, notes })
          })
          .then(res => res.json())
          .then(resp => {
            if (resp.success) {
              showTicketNotification('Ticket updated!');
              closeTicketModal();
              fetchTechnicianTickets();
            } else {
              showTicketNotification('Error: ' + (resp.error || 'Update failed'), true);
            }
          })
          .catch(() => showTicketNotification('Network error.', true));
        };
        function showTicketNotification(msg, isError) {
          const n = document.getElementById('ticketNotification');
          n.textContent = msg;
          n.style.background = isError ? '#ef4444' : '#22c55e';
          n.style.display = 'block';
          setTimeout(() => { n.style.display = 'none'; }, 3000);
        }
        </script>
      </div>
      <div class="footer mt-md small text-center" style="color: var(--color-secondary);">
        &copy; 2024 OutageSys. All rights reserved.
      </div>
    </div>
  </div>
</body>
</html> 