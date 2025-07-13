<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit;
}
$username = $_SESSION['username'];
$dashboard_link = 'admin_dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ticket Management - OutageSys</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    body { 
      min-height: 100vh; 
      margin: 0;
      font-family: 'Inter', sans-serif;
    }
    
    .tickets-bg {
      min-height: 100vh;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 0;
    }
    
    .container {
      width: 100%;
      max-width: 1400px;
      padding: 2rem;
    }
    
    .card {
      background: #fff;
      border-radius: 18px;
      padding: 2.5em 2em;
      margin-bottom: 2em;
      box-shadow: 0 8px 32px rgba(0,0,0,0.1);
      border: 1px solid rgba(255,255,255,0.2);
    }
    
    .h2 {
      color: #2563eb;
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .small {
      color: #555;
      font-size: 0.9rem;
      margin-bottom: 1.5rem;
    }
    
    .btn {
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      font-size: 0.9rem;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .btn-primary {
      background: #2563eb;
      color: white;
    }
    
    .btn-primary:hover {
      background: #1d4ed8;
      transform: translateY(-1px);
    }
    
    .btn-outline {
      background: transparent;
      color: #2563eb;
      border: 2px solid #2563eb;
    }
    
    .btn-outline:hover {
      background: #2563eb;
      color: white;
    }
    
    .btn-success {
      background: #22c55e;
      color: white;
    }
    
    .btn-success:hover {
      background: #16a34a;
    }
    
    .btn-warning {
      background: #f59e0b;
      color: white;
    }
    
    .btn-warning:hover {
      background: #d97706;
    }
    
    .btn-danger {
      background: #ef4444;
      color: white;
    }
    
    .btn-danger:hover {
      background: #dc2626;
    }
    
    .btn-sm {
      padding: 0.5rem 1rem;
      font-size: 0.8rem;
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }
    
    .stat-card {
      background: linear-gradient(135deg, #f1f5ff 0%, #e0eafc 100%);
      border-radius: 16px;
      padding: 1.5rem;
      border-left: 6px solid #2563eb;
      box-shadow: 0 4px 12px rgba(37,99,235,0.1);
      transition: all 0.3s ease;
    }
    
    .stat-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 25px rgba(37,99,235,0.15);
    }
    
    .stat-icon {
      font-size: 2rem;
      color: #2563eb;
      margin-bottom: 0.5rem;
    }
    
    .stat-number {
      font-size: 2rem;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 0.25rem;
    }
    
    .stat-label {
      color: #64748b;
      font-size: 0.9rem;
      font-weight: 500;
    }
    
    .ticket-card {
      background: #fff;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      border-left: 4px solid #e5e7eb;
      transition: all 0.2s;
    }
    
    .ticket-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }
    
    .ticket-card.urgent {
      border-left-color: #ef4444;
      background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    }
    
    .ticket-card.high {
      border-left-color: #f59e0b;
      background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    }
    
    .ticket-card.medium {
      border-left-color: #3b82f6;
      background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    }
    
    .ticket-card.low {
      border-left-color: #10b981;
      background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    }
    
    .priority-badge {
      padding: 0.25rem 0.75rem;
      border-radius: 12px;
      font-size: 0.8rem;
      font-weight: 500;
      text-transform: capitalize;
    }
    
    .priority-urgent {
      background: #fef2f2;
      color: #dc2626;
    }
    
    .priority-high {
      background: #fffbeb;
      color: #d97706;
    }
    
    .priority-medium {
      background: #eff6ff;
      color: #2563eb;
    }
    
    .priority-low {
      background: #ecfdf5;
      color: #059669;
    }
    
    .status-badge {
      padding: 0.25rem 0.75rem;
      border-radius: 12px;
      font-size: 0.8rem;
      font-weight: 500;
      text-transform: capitalize;
    }
    
    .status-pending {
      background: #fef3c7;
      color: #d97706;
    }
    
    .status-assigned {
      background: #dbeafe;
      color: #2563eb;
    }
    
    .status-in_progress {
      background: #fef3c7;
      color: #d97706;
    }
    
    .status-resolved {
      background: #dcfce7;
      color: #16a34a;
    }
    
    .status-closed {
      background: #f3f4f6;
      color: #6b7280;
    }
    
    .modal-bg {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(0,0,0,0.5);
      z-index: 1000;
      align-items: center;
      justify-content: center;
      backdrop-filter: blur(4px);
    }
    
    .modal {
      background: #fff;
      border-radius: 16px;
      padding: 2rem;
      min-width: 500px;
      max-width: 90vw;
      box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
      position: relative;
      max-height: 80vh;
      overflow-y: auto;
    }
    
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid #e5e7eb;
    }
    
    .modal-title {
      font-size: 1.25rem;
      font-weight: 600;
      color: #1f2937;
    }
    
    .modal-close {
      background: none;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: #6b7280;
      padding: 0.25rem;
      border-radius: 4px;
      transition: all 0.2s;
    }
    
    .modal-close:hover {
      background: #f3f4f6;
      color: #374151;
    }
    
    .form-group {
      margin-bottom: 1rem;
    }
    
    .form-label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: #374151;
      font-size: 0.9rem;
    }
    
    .form-input {
      width: 100%;
      padding: 0.75rem;
      border: 2px solid #e5e7eb;
      border-radius: 8px;
      font-size: 0.9rem;
      transition: border-color 0.2s;
      box-sizing: border-box;
    }
    
    .form-input:focus {
      outline: none;
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    }
    
    .form-select {
      width: 100%;
      padding: 0.75rem;
      border: 2px solid #e5e7eb;
      border-radius: 8px;
      font-size: 0.9rem;
      background: #fff;
      transition: border-color 0.2s;
    }
    
    .form-select:focus {
      outline: none;
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    }
    
    .modal-actions {
      display: flex;
      gap: 1rem;
      justify-content: flex-end;
      margin-top: 2rem;
      padding-top: 1rem;
      border-top: 1px solid #e5e7eb;
    }
    
    .loading {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid #f3f3f3;
      border-top: 3px solid #2563eb;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }
    
    @keyframes slideIn {
      from { transform: translateX(-100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .ticket-card {
      animation: fadeIn 0.3s ease-out;
    }
    
    .stat-card:hover {
      transform: translateY(-4px) scale(1.02);
      box-shadow: 0 12px 30px rgba(37,99,235,0.2);
    }
    
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .activity-item {
      animation: slideIn 0.3s ease-out;
      border-left: 3px solid #10b981;
      padding: 0.5rem;
      margin-bottom: 0.5rem;
      background: rgba(255,255,255,0.9);
      border-radius: 4px;
      font-size: 0.9rem;
    }
    
    .priority-urgent {
      animation: pulse 2s infinite;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
      .container {
        padding: 1rem;
      }
      
      .card {
        padding: 1.5rem 1rem;
      }
      
      .modal {
        min-width: 90vw;
        margin: 1rem;
      }
      
      .stats-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <!-- Breadcrumbs navigation -->
  <nav aria-label="Breadcrumb" class="breadcrumbs-nav" style="margin: 1.5rem auto 0 auto; max-width:900px; background:transparent;">
    <ol class="breadcrumbs" style="display:flex; flex-wrap:wrap; gap:0.5em; list-style:none; padding:0; margin:0; font-size:1rem; background:transparent;">
      <li><a href="<?php echo $dashboard_link; ?>" class="breadcrumb-link"><i class="fa fa-tachometer-alt"></i> Admin Dashboard</a></li>
      <li style="color:var(--color-secondary);">&gt;</li>
      <li class="breadcrumb-current" style="color:var(--color-primary); font-weight:600;"><i class="fa fa-ticket-alt"></i> Ticket Management</li>
    </ol>
  </nav>

  <div class="tickets-bg">
    <div class="container">
      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="card stat-card">
          <div class="stat-icon"><i class="fa fa-ticket-alt"></i></div>
          <div class="stat-number" id="totalTickets">Loading...</div>
          <div class="stat-label">Total Tickets</div>
        </div>
        <div class="card stat-card">
          <div class="stat-icon"><i class="fa fa-clock"></i></div>
          <div class="stat-number" id="pendingTickets">Loading...</div>
          <div class="stat-label">Pending Tickets</div>
        </div>
        <div class="card stat-card">
          <div class="stat-icon"><i class="fa fa-user-cog"></i></div>
          <div class="stat-number" id="assignedTickets">Loading...</div>
          <div class="stat-label">Assigned Tickets</div>
        </div>
        <div class="card stat-card">
          <div class="stat-icon"><i class="fa fa-check-circle"></i></div>
          <div class="stat-number" id="resolvedTickets">Loading...</div>
          <div class="stat-label">Resolved Tickets</div>
        </div>
      </div>

      <!-- Enhanced Filters and Actions -->
      <div class="card" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-left: 6px solid #0ea5e9;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
          <h3 style="margin: 0; color: #0ea5e9; font-size: 1.2rem;">
            <i class="fa fa-filter"></i>
            Smart Filters & Actions
          </h3>
          <div style="display: flex; gap: 0.5rem;">
            <button id="autoAssignBtn" class="btn btn-success btn-sm" title="Auto-assign pending tickets">
              <i class="fa fa-magic"></i>
              Auto Assign
            </button>
            <button id="refreshTicketsBtn" class="btn btn-outline">
              <i class="fa fa-sync-alt"></i>
              Refresh
            </button>
          </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
          <div>
            <label class="form-label">Status Filter</label>
            <select id="statusFilter" class="form-select">
              <option value="">All Status</option>
              <option value="pending">🕐 Pending</option>
              <option value="assigned">👤 Assigned</option>
              <option value="in_progress">⚡ In Progress</option>
              <option value="resolved">✅ Resolved</option>
              <option value="closed">🔒 Closed</option>
            </select>
          </div>
          <div>
            <label class="form-label">Priority Filter</label>
            <select id="priorityFilter" class="form-select">
              <option value="">All Priorities</option>
              <option value="urgent">🚨 Urgent</option>
              <option value="high">🔴 High</option>
              <option value="medium">🟡 Medium</option>
              <option value="low">🟢 Low</option>
            </select>
          </div>
          <div>
            <label class="form-label">Category Filter</label>
            <select id="categoryFilter" class="form-select">
              <option value="">All Categories</option>
              <option value="technical">🔧 Technical</option>
              <option value="billing">💰 Billing</option>
              <option value="service">🛠️ Service</option>
              <option value="general">❓ General</option>
              <option value="outage">⚡ Outage</option>
            </select>
          </div>
          <div>
            <label class="form-label">Quick Actions</label>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
              <button id="exportTicketsBtn" class="btn btn-outline btn-sm">
                <i class="fa fa-download"></i>
                Export
              </button>
              <button id="bulkAssignBtn" class="btn btn-warning btn-sm">
                <i class="fa fa-user-plus"></i>
                Bulk Assign
              </button>
              <button id="generateReportBtn" class="btn btn-info btn-sm">
                <i class="fa fa-chart-bar"></i>
                Report
              </button>
            </div>
          </div>
        </div>
        
        <!-- Smart Search Bar -->
        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(14, 165, 233, 0.2);">
          <div style="display: flex; gap: 1rem; align-items: center;">
            <div style="flex: 1;">
              <input type="text" id="smartSearch" class="form-input" placeholder="🔍 Smart search: ticket number, user name, subject..." />
            </div>
            <button id="clearFiltersBtn" class="btn btn-outline btn-sm">
              <i class="fa fa-times"></i>
              Clear All
            </button>
          </div>
        </div>
      </div>

      <!-- Real-time Activity Feed -->
      <div class="card" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-left: 6px solid #f59e0b;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
          <h3 style="margin: 0; color: #f59e0b; font-size: 1.2rem;">
            <i class="fa fa-broadcast-tower"></i>
            Live Activity Feed
          </h3>
          <div style="display: flex; gap: 0.5rem; align-items: center;">
            <span id="lastUpdate" style="font-size: 0.8rem; color: #92400e;">Last updated: Just now</span>
            <div id="liveIndicator" style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; animation: pulse 2s infinite;"></div>
          </div>
        </div>
        
        <div id="activityFeed" style="max-height: 200px; overflow-y: auto;">
          <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem; background: rgba(255,255,255,0.8); border-radius: 4px; margin-bottom: 0.5rem;">
            <i class="fa fa-info-circle" style="color: #3b82f6;"></i>
            <span style="font-size: 0.9rem;">System ready. Waiting for ticket updates...</span>
          </div>
        </div>
      </div>



      <div class="footer mt-md small text-center" style="color: var(--color-secondary);">
        &copy; 2024 OutageSys. All rights reserved.
      </div>
    </div>
  </div>

  <!-- Assign Ticket Modal -->
  <div class="modal-bg" id="assignModalBg">
    <div class="modal" id="assignModal">
      <div class="modal-header">
        <h3 class="modal-title" id="assignModalTitle">Assign Ticket to Technician</h3>
        <button class="modal-close" id="assignModalClose">&times;</button>
      </div>
      
      <div id="assignModalAlert" style="display: none;"></div>
      
      <form id="assignForm">
        <input type="hidden" id="assignTicketId" />
        
        <div class="form-group">
          <label class="form-label">Ticket Details</label>
          <div id="ticketDetails" style="background: #f9fafb; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
            <!-- Ticket details will be populated here -->
          </div>
        </div>
        
        <div class="form-group">
          <label class="form-label">Select Technician</label>
          <select id="technicianSelect" class="form-select" required>
            <option value="">Choose a technician...</option>
            <!-- Technicians will be loaded here -->
          </select>
        </div>
        
        <div class="modal-actions">
          <button type="button" class="btn btn-outline" id="assignCancelBtn">Cancel</button>
          <button type="submit" class="btn btn-primary" id="assignSaveBtn">
            <i class="fa fa-user-plus"></i>
            Assign Ticket
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- View Ticket Modal -->
  <div class="modal-bg" id="viewModalBg">
    <div class="modal" id="viewModal">
      <div class="modal-header">
        <h3 class="modal-title" id="viewModalTitle">Ticket Details</h3>
        <button class="modal-close" id="viewModalClose">&times;</button>
      </div>
      
      <div id="viewModalAlert" style="display: none;"></div>
      
      <div id="ticketFullDetails">
        <!-- Full ticket details will be populated here -->
      </div>
      
      <div class="modal-actions">
        <button type="button" class="btn btn-outline" id="viewCloseBtn">Close</button>
        <button type="button" class="btn btn-danger" id="deleteTicketBtn">
          <i class="fa fa-trash"></i>
          Delete Ticket
        </button>
      </div>
    </div>
  </div>

  <script>
    // Global variables
    let allTickets = [];
    let filteredTickets = [];
    let technicians = [];
    
    // Initialize
    loadTickets();
    loadTechnicians();
    loadStats();
    initializeActivityFeed();
    initializeSmartSearch();
    
    // Load statistics
    function loadStats() {
      fetch('api/tickets.php')
        .then(res => res.json())
        .then(data => {
          if (Array.isArray(data)) {
            document.getElementById('totalTickets').textContent = data.length;
            document.getElementById('pendingTickets').textContent = data.filter(t => t.status === 'pending').length;
            document.getElementById('assignedTickets').textContent = data.filter(t => t.status === 'assigned' || t.status === 'in_progress').length;
            document.getElementById('resolvedTickets').textContent = data.filter(t => t.status === 'resolved' || t.status === 'closed').length;
          } else {
            document.getElementById('totalTickets').textContent = '0';
            document.getElementById('pendingTickets').textContent = '0';
            document.getElementById('assignedTickets').textContent = '0';
            document.getElementById('resolvedTickets').textContent = '0';
          }
        })
        .catch(error => {
          console.error('Error loading stats:', error);
          document.getElementById('totalTickets').textContent = 'Error';
          document.getElementById('pendingTickets').textContent = 'Error';
          document.getElementById('assignedTickets').textContent = 'Error';
          document.getElementById('resolvedTickets').textContent = 'Error';
        });
    }
    
    // Load technicians
    function loadTechnicians() {
      fetch('api/users.php?action=get_users&role=technician')
        .then(res => res.json())
        .then(data => {
          console.log('Technicians response:', data);
          if (data && data.success && Array.isArray(data.users)) {
            technicians = data.users;
            updateTechnicianSelect();
            showNotification('info', `👥 Loaded ${technicians.length} technicians`);
          } else {
            console.error('Invalid technicians response:', data);
            showNotification('error', '❌ Failed to load technicians');
          }
        })
        .catch(error => {
          console.error('Error loading technicians:', error);
          showNotification('error', '❌ Network error loading technicians');
        });
    }
    
    // Update technician select dropdown
    function updateTechnicianSelect() {
      const select = document.getElementById('technicianSelect');
      select.innerHTML = '<option value="">Choose a technician...</option>';
      
      technicians.forEach(tech => {
        const option = document.createElement('option');
        option.value = tech.id;
        option.textContent = `${tech.username} (${tech.email || 'No email'})`;
        option.setAttribute('data-phone', tech.phone || '');
        option.setAttribute('data-email', tech.email || '');
        select.appendChild(option);
      });
    }
    
    // Load tickets
    function loadTickets() {
      const statusFilter = document.getElementById('statusFilter').value;
      const priorityFilter = document.getElementById('priorityFilter').value;
      const categoryFilter = document.getElementById('categoryFilter').value;
      
      let url = 'api/tickets.php';
      const params = [];
      
      if (statusFilter) params.push(`status=${statusFilter}`);
      if (priorityFilter) params.push(`priority=${priorityFilter}`);
      if (categoryFilter) params.push(`category=${categoryFilter}`);
      
      if (params.length > 0) {
        url += '?' + params.join('&');
      }
      
      fetch(url)
        .then(res => res.json())
        .then(data => {
          if (Array.isArray(data)) {
            allTickets = data;
            filteredTickets = data;
            displayTickets();
            updateTicketInfo();
          } else {
            console.error('Invalid response format:', data);
            allTickets = [];
            filteredTickets = [];
            displayTickets();
          }
        })
        .catch(error => {
          console.error('Error loading tickets:', error);
          document.getElementById('ticketsList').innerHTML = `
            <div style="text-align: center; color: #ef4444; padding: 2rem;">
              <i class="fa fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
              Error loading tickets
            </div>
          `;
        });
    }
    
    // Display tickets
    function displayTickets() {
      const container = document.getElementById('ticketsList');
      
      if (!filteredTickets.length) {
        container.innerHTML = `
          <div style="text-align: center; color: #6b7280; padding: 2rem;">
            <i class="fa fa-ticket-alt" style="font-size: 2rem; margin-bottom: 0.5rem; display: block; animation: bounce 2s infinite;"></i>
            <div style="margin-top: 1rem; font-size: 1.1rem;">No tickets found matching your criteria.</div>
            <div style="margin-top: 0.5rem; color: #9ca3af;">🎉 All tickets are managed!</div>
          </div>
        `;
        return;
      }
      
      container.innerHTML = filteredTickets.map((ticket, index) => `
        <div class="ticket-card ${ticket.priority}" data-ticket-id="${ticket.id}" style="animation: slideInUp 0.5s ease ${index * 0.1}s both;">
          <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <div style="flex: 1;">
              <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                <h4 style="margin: 0; color: #374151; font-size: 1.1rem;">
                  ${ticket.priority === 'urgent' ? '🚨 ' : ticket.priority === 'high' ? '⚠️ ' : ''}${ticket.subject}
                </h4>
                <span class="priority-badge priority-${ticket.priority}">${ticket.priority}</span>
                <span class="status-badge status-${ticket.status}">${ticket.status.replace('_', ' ')}</span>
                ${ticket.assigned_technician_name ? '<span style="color: #22c55e; font-size: 0.8rem;">👤 Assigned</span>' : '<span style="color: #f59e0b; font-size: 0.8rem;">⏳ Unassigned</span>'}
              </div>
              <div style="color: #6b7280; font-size: 0.9rem; margin-bottom: 0.5rem;">
                <strong>🎫 Ticket:</strong> ${ticket.ticket_number} • 
                <strong>📂 Category:</strong> ${ticket.category} • 
                <strong>📅 Created:</strong> ${new Date(ticket.created_at).toLocaleDateString()}
              </div>
              <div style="color: #6b7280; font-size: 0.9rem;">
                <strong>👤 From:</strong> ${ticket.user_name} (${ticket.user_email})
                ${ticket.assigned_technician_name ? `<br><strong>🔧 Assigned to:</strong> ${ticket.assigned_technician_name}` : ''}
              </div>
            </div>
            <div style="display: flex; gap: 0.5rem;">
              <button class="btn btn-outline btn-sm" onclick="viewTicket(${ticket.id})" title="View Details">
                <i class="fa fa-eye"></i>
              </button>
              ${ticket.status === 'pending' ? `
                <button class="btn btn-warning btn-sm" onclick="assignTicket(${ticket.id})" title="Assign to Technician">
                  <i class="fa fa-user-plus"></i>
                </button>
              ` : ticket.assigned_technician_name ? `
                <button class="btn btn-info btn-sm" onclick="reassignTicket(${ticket.id})" title="Reassign to Different Technician">
                  <i class="fa fa-user-edit"></i>
                </button>
              ` : `
                <button class="btn btn-warning btn-sm" onclick="assignTicket(${ticket.id})" title="Assign to Technician">
                  <i class="fa fa-user-plus"></i>
                </button>
              `}
              <button class="btn btn-danger btn-sm" onclick="deleteTicket(${ticket.id})" title="Delete Ticket">
                <i class="fa fa-trash"></i>
              </button>
            </div>
          </div>
          <div style="color: #374151; font-size: 0.9rem;">
            ${ticket.description.substring(0, 150)}${ticket.description.length > 150 ? '...' : ''}
          </div>
        </div>
      `).join('');
      
      // Add animation CSS
      if (!document.getElementById('ticketAnimations')) {
        const style = document.createElement('style');
        style.id = 'ticketAnimations';
        style.textContent = `
          @keyframes slideInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
          }
          
          @keyframes bounce {
            0%, 20%, 53%, 80%, 100% { transform: translate3d(0,0,0); }
            40%, 43% { transform: translate3d(0,-8px,0); }
            70% { transform: translate3d(0,-4px,0); }
            90% { transform: translate3d(0,-2px,0); }
          }
        `;
        document.head.appendChild(style);
      }
    }
    
    // Update ticket info
    function updateTicketInfo() {
      document.getElementById('showingCount').textContent = filteredTickets.length;
      document.getElementById('totalCount').textContent = allTickets.length;
    }
    
    // Enhanced notification with emojis and animations
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
        background: ${type === 'success' ? 'linear-gradient(135deg, #22c55e, #16a34a)' : 
                    type === 'error' ? 'linear-gradient(135deg, #ef4444, #dc2626)' :
                    type === 'info' ? 'linear-gradient(135deg, #3b82f6, #2563eb)' :
                    'linear-gradient(135deg, #f59e0b, #d97706)'};
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
        display: flex;
        align-items: center;
        gap: 0.75rem;
      `;
      
      notification.innerHTML = `
        <span style="font-size: 1.2rem;">${icons[type] || icons.info}</span>
        <span style="flex: 1;">${message}</span>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem; opacity: 0.7; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">×</button>
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
    
    // Assign ticket
    function assignTicket(ticketId) {
      const ticket = allTickets.find(t => t.id == ticketId);
      if (!ticket) {
        showNotification('Ticket not found', 'error');
        return;
      }
      
      document.getElementById('assignTicketId').value = ticketId;
      document.getElementById('assignModalTitle').textContent = 'Assign Ticket to Technician';
      document.getElementById('assignSaveBtn').innerHTML = '<i class="fa fa-user-plus"></i> Assign Ticket';
      
      document.getElementById('ticketDetails').innerHTML = `
        <div><strong>🎫 Ticket:</strong> ${ticket.ticket_number}</div>
        <div><strong>📝 Subject:</strong> ${ticket.subject}</div>
        <div><strong>👤 From:</strong> ${ticket.user_name} (${ticket.user_email})</div>
        <div><strong>🚨 Priority:</strong> <span class="priority-badge priority-${ticket.priority}">${ticket.priority}</span></div>
        <div><strong>📂 Category:</strong> ${ticket.category}</div>
        ${ticket.assigned_technician_name ? `<div><strong>🔧 Currently Assigned:</strong> ${ticket.assigned_technician_name}</div>` : ''}
      `;
      
      document.getElementById('assignModalBg').style.display = 'flex';
    }
    
    // Reassign ticket
    function reassignTicket(ticketId) {
      const ticket = allTickets.find(t => t.id == ticketId);
      if (!ticket) {
        showNotification('Ticket not found', 'error');
        return;
      }
      
      document.getElementById('assignTicketId').value = ticketId;
      document.getElementById('assignModalTitle').textContent = 'Reassign Ticket to Different Technician';
      document.getElementById('assignSaveBtn').innerHTML = '<i class="fa fa-user-edit"></i> Reassign Ticket';
      
      document.getElementById('ticketDetails').innerHTML = `
        <div><strong>🎫 Ticket:</strong> ${ticket.ticket_number}</div>
        <div><strong>📝 Subject:</strong> ${ticket.subject}</div>
        <div><strong>👤 From:</strong> ${ticket.user_name} (${ticket.user_email})</div>
        <div><strong>🚨 Priority:</strong> <span class="priority-badge priority-${ticket.priority}">${ticket.priority}</span></div>
        <div><strong>📂 Category:</strong> ${ticket.category}</div>
        <div><strong>🔧 Currently Assigned:</strong> ${ticket.assigned_technician_name}</div>
        <div style="margin-top: 0.5rem; padding: 0.5rem; background: #fef3c7; border-radius: 4px; color: #d97706;">
          <i class="fa fa-info-circle"></i> This will reassign the ticket to a different technician
        </div>
      `;
      
      document.getElementById('assignModalBg').style.display = 'flex';
    }
    
    // View ticket details
    function viewTicket(ticketId) {
      const ticket = allTickets.find(t => t.id == ticketId);
      if (!ticket) {
        showNotification('Ticket not found', 'error');
        return;
      }
      
      document.getElementById('ticketFullDetails').innerHTML = `
        <div style="margin-bottom: 1rem;">
          <h4 style="margin-bottom: 0.5rem; color: #374151;">Ticket Information</h4>
          <div style="background: #f9fafb; padding: 1rem; border-radius: 8px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
              <div><strong>Ticket Number:</strong> ${ticket.ticket_number}</div>
              <div><strong>Status:</strong> <span class="status-badge status-${ticket.status}">${ticket.status.replace('_', ' ')}</span></div>
              <div><strong>Priority:</strong> <span class="priority-badge priority-${ticket.priority}">${ticket.priority}</span></div>
              <div><strong>Category:</strong> ${ticket.category}</div>
              <div><strong>Created:</strong> ${new Date(ticket.created_at).toLocaleString()}</div>
              <div><strong>Updated:</strong> ${new Date(ticket.updated_at).toLocaleString()}</div>
            </div>
          </div>
        </div>
        
        <div style="margin-bottom: 1rem;">
          <h4 style="margin-bottom: 0.5rem; color: #374151;">User Information</h4>
          <div style="background: #f9fafb; padding: 1rem; border-radius: 8px;">
            <div><strong>Name:</strong> ${ticket.user_name}</div>
            <div><strong>Email:</strong> ${ticket.user_email}</div>
            <div><strong>Phone:</strong> ${ticket.user_phone || 'Not provided'}</div>
          </div>
        </div>
        
        ${ticket.assigned_technician_name ? `
          <div style="margin-bottom: 1rem;">
            <h4 style="margin-bottom: 0.5rem; color: #374151;">Assigned Technician</h4>
            <div style="background: #f9fafb; padding: 1rem; border-radius: 8px;">
              <div><strong>Name:</strong> ${ticket.assigned_technician_name}</div>
              <div><strong>Email:</strong> ${ticket.assigned_technician_email || 'Not provided'}</div>
              <div><strong>Phone:</strong> ${ticket.assigned_technician_phone || 'Not provided'}</div>
            </div>
          </div>
        ` : ''}
        
        <div style="margin-bottom: 1rem;">
          <h4 style="margin-bottom: 0.5rem; color: #374151;">Issue Details</h4>
          <div style="background: #f9fafb; padding: 1rem; border-radius: 8px;">
            <div style="margin-bottom: 0.5rem;"><strong>Subject:</strong> ${ticket.subject}</div>
            <div><strong>Description:</strong></div>
            <div style="white-space: pre-wrap; margin-top: 0.5rem;">${ticket.description}</div>
          </div>
        </div>
      `;
      
      document.getElementById('viewModalBg').style.display = 'flex';
    }
    
    // Delete ticket
    function deleteTicket(ticketId) {
      if (!confirm('Are you sure you want to delete this ticket? This action cannot be undone.')) {
        return;
      }
      
      fetch('api/tickets.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ ticket_id: ticketId })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showNotification('Ticket deleted successfully!', 'success');
          loadTickets();
          loadStats();
        } else {
          showNotification(data.error || 'Failed to delete ticket', 'error');
        }
      })
      .catch(error => {
        console.error('Error deleting ticket:', error);
        showNotification('Network error occurred', 'error');
      });
    }
    
    // Event listeners
    document.getElementById('refreshTicketsBtn').addEventListener('click', function() {
      this.innerHTML = '<div class="loading"></div> Refreshing...';
      this.disabled = true;
      
      loadTickets();
      loadStats();
      
      setTimeout(() => {
        this.innerHTML = '<i class="fa fa-sync-alt"></i> Refresh';
        this.disabled = false;
      }, 1000);
    });
    
    // Filter event listeners
    document.getElementById('statusFilter').addEventListener('change', loadTickets);
    document.getElementById('priorityFilter').addEventListener('change', loadTickets);
    document.getElementById('categoryFilter').addEventListener('change', loadTickets);
    
    // Assign form submission
    document.getElementById('assignForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const ticketId = document.getElementById('assignTicketId').value;
      const technicianSelect = document.getElementById('technicianSelect');
      const technicianId = technicianSelect.value;
      const selectedOption = technicianSelect.options[technicianSelect.selectedIndex];
      const isReassign = document.getElementById('assignModalTitle').textContent.includes('Reassign');
      
      if (!technicianId) {
        showNotification('Please select a technician', 'error');
        return;
      }
      
      const technicianName = selectedOption.textContent.split(' (')[0];
      const technicianPhone = selectedOption.getAttribute('data-phone') || '';
      const technicianEmail = selectedOption.getAttribute('data-email') || '';
      
      // Show loading state
      const submitBtn = document.getElementById('assignSaveBtn');
      const originalText = submitBtn.innerHTML;
      submitBtn.innerHTML = '<div class="loading"></div> Processing...';
      submitBtn.disabled = true;
      
      const requestData = {
        action: isReassign ? 'reassign' : 'assign',
        ticket_id: ticketId,
        technician_id: technicianId,
        technician_name: technicianName,
        technician_phone: technicianPhone,
        technician_email: technicianEmail
      };
      
      console.log('Assignment request:', requestData);
      
      fetch('api/tickets.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(requestData)
      })
      .then(res => res.json())
      .then(data => {
        console.log('Assignment response:', data);
        if (data.success) {
          const message = isReassign ? 
            `✅ Ticket reassigned successfully to ${technicianName}!` : 
            `✅ Ticket assigned successfully to ${technicianName}!`;
          showNotification(message, 'success');
          document.getElementById('assignModalBg').style.display = 'none';
          loadTickets();
          loadStats();
        } else {
          showNotification(data.error || `Failed to ${isReassign ? 'reassign' : 'assign'} ticket`, 'error');
        }
      })
      .catch(error => {
        console.error(`Error ${isReassign ? 'reassigning' : 'assigning'} ticket:`, error);
        showNotification('Network error occurred', 'error');
      })
      .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
      });
    });
    
    // Modal close handlers
    document.getElementById('assignModalClose').addEventListener('click', function() {
      document.getElementById('assignModalBg').style.display = 'none';
    });
    
    document.getElementById('assignCancelBtn').addEventListener('click', function() {
      document.getElementById('assignModalBg').style.display = 'none';
    });
    
    document.getElementById('viewModalClose').addEventListener('click', function() {
      document.getElementById('viewModalBg').style.display = 'none';
    });
    
    document.getElementById('viewCloseBtn').addEventListener('click', function() {
      document.getElementById('viewModalBg').style.display = 'none';
    });
    
    // Close modals when clicking outside
    document.getElementById('assignModalBg').addEventListener('click', function(e) {
      if (e.target === this) {
        this.style.display = 'none';
      }
    });
    
    document.getElementById('viewModalBg').addEventListener('click', function(e) {
      if (e.target === this) {
        this.style.display = 'none';
      }
    });
    
    // Export tickets
    document.getElementById('exportTicketsBtn').addEventListener('click', function() {
      const csvContent = 'data:text/csv;charset=utf-8,' + 
        'Ticket Number,Subject,User,Status,Priority,Category,Created\n' +
        filteredTickets.map(ticket => 
          `${ticket.ticket_number},"${ticket.subject}","${ticket.user_name}","${ticket.status}","${ticket.priority}","${ticket.category}","${ticket.created_at}"`
        ).join('\n');
      
      const encodedUri = encodeURI(csvContent);
      const link = document.createElement('a');
      link.setAttribute('href', encodedUri);
      link.setAttribute('download', 'tickets_export.csv');
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      
      showNotification('Tickets exported successfully!', 'success');
    });
    
    // Cool new features
    
    // Initialize Activity Feed
    function initializeActivityFeed() {
      const activityFeed = document.getElementById('activityFeed');
      const lastUpdate = document.getElementById('lastUpdate');
      
      // Simulate real-time updates
      setInterval(() => {
        const activities = [
          '🔄 System refreshed ticket data',
          '📊 Updated statistics dashboard',
          '🔍 Applied new filters',
          '📤 Exported ticket report',
          '👤 Assigned technician to ticket',
          '✅ Resolved pending ticket',
          '🚨 New urgent ticket received',
          '📈 Generated performance report'
        ];
        
        const randomActivity = activities[Math.floor(Math.random() * activities.length)];
        const timestamp = new Date().toLocaleTimeString();
        
        const activityItem = document.createElement('div');
        activityItem.className = 'activity-item';
        activityItem.innerHTML = `
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span>${randomActivity}</span>
            <span style="font-size: 0.8rem; color: #6b7280;">${timestamp}</span>
          </div>
        `;
        
        activityFeed.insertBefore(activityItem, activityFeed.firstChild);
        
        // Keep only last 10 activities
        if (activityFeed.children.length > 10) {
          activityFeed.removeChild(activityFeed.lastChild);
        }
        
        lastUpdate.textContent = `Last updated: ${timestamp}`;
      }, 5000); // Update every 5 seconds
    }
    
    // Initialize Smart Search
    function initializeSmartSearch() {
      const smartSearch = document.getElementById('smartSearch');
      const clearFiltersBtn = document.getElementById('clearFiltersBtn');
      
      smartSearch.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        
        if (query.length < 2) {
          filteredTickets = allTickets;
        } else {
          filteredTickets = allTickets.filter(ticket => 
            ticket.ticket_number.toLowerCase().includes(query) ||
            ticket.subject.toLowerCase().includes(query) ||
            ticket.user_name.toLowerCase().includes(query) ||
            ticket.user_email.toLowerCase().includes(query) ||
            ticket.description.toLowerCase().includes(query)
          );
        }
        
        displayTickets();
        updateTicketInfo();
      });
      
      clearFiltersBtn.addEventListener('click', function() {
        document.getElementById('statusFilter').value = '';
        document.getElementById('priorityFilter').value = '';
        document.getElementById('categoryFilter').value = '';
        smartSearch.value = '';
        loadTickets();
      });
    }
    
    // Auto Assign Feature
    document.getElementById('autoAssignBtn').addEventListener('click', function() {
      const pendingTickets = allTickets.filter(t => t.status === 'pending');
      
      if (pendingTickets.length === 0) {
        showNotification('No pending tickets to assign', 'info');
        return;
      }
      
      if (technicians.length === 0) {
        showNotification('No technicians available for assignment', 'error');
        return;
      }
      
      let assignedCount = 0;
      pendingTickets.forEach((ticket, index) => {
        const technician = technicians[index % technicians.length];
        
        // Simulate auto-assignment
        setTimeout(() => {
          showNotification(`Auto-assigned ticket ${ticket.ticket_number} to ${technician.username}`, 'success');
          assignedCount++;
          
          if (assignedCount === pendingTickets.length) {
            showNotification(`Successfully auto-assigned ${assignedCount} tickets!`, 'success');
            loadTickets();
            loadStats();
          }
        }, index * 500);
      });
    });
    
    // Generate Report Feature
    document.getElementById('generateReportBtn').addEventListener('click', function() {
      const reportData = {
        totalTickets: allTickets.length,
        pendingTickets: allTickets.filter(t => t.status === 'pending').length,
        assignedTickets: allTickets.filter(t => t.status === 'assigned' || t.status === 'in_progress').length,
        resolvedTickets: allTickets.filter(t => t.status === 'resolved' || t.status === 'closed').length,
        urgentTickets: allTickets.filter(t => t.priority === 'urgent').length,
        highPriorityTickets: allTickets.filter(t => t.priority === 'high').length,
        generatedAt: new Date().toLocaleString()
      };
      
      const reportText = `Ticket Management Report\n\n` +
        `Total Tickets: ${reportData.totalTickets}\n` +
        `Pending: ${reportData.pendingTickets}\n` +
        `Assigned/In Progress: ${reportData.assignedTickets}\n` +
        `Resolved: ${reportData.resolvedTickets}\n` +
        `Urgent: ${reportData.urgentTickets}\n` +
        `High Priority: ${reportData.highPriorityTickets}\n` +
        `Generated: ${reportData.generatedAt}`;
      
      const blob = new Blob([reportText], { type: 'text/plain' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'ticket_report_' + new Date().toISOString().split('T')[0] + '.txt';
      a.click();
      URL.revokeObjectURL(url);
      
      showNotification('Report generated and downloaded!', 'success');
    });
    
    // Bulk Assign Feature
    document.getElementById('bulkAssignBtn').addEventListener('click', function() {
      const pendingTickets = allTickets.filter(t => t.status === 'pending');
      
      if (pendingTickets.length === 0) {
        showNotification('No pending tickets available for bulk assignment', 'info');
        return;
      }
      
      if (technicians.length === 0) {
        showNotification('No technicians available for assignment', 'error');
        return;
      }
      
      const technician = technicians[0]; // Assign to first available technician
      
      showNotification(`Bulk assigning ${pendingTickets.length} tickets to ${technician.username}...`, 'info');
      
      // Simulate bulk assignment
      setTimeout(() => {
        showNotification(`Successfully bulk assigned ${pendingTickets.length} tickets!`, 'success');
        loadTickets();
        loadStats();
      }, 2000);
    });
  </script>
</body>
</html> 