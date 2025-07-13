<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$dashboard_link = 'user_dashboard.php';
if ($role === 'admin') $dashboard_link = 'admin_dashboard.php';
if ($role === 'technician') $dashboard_link = 'technician_dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Tickets - OutageSys</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    body { 
      min-height: 100vh; 
      margin: 0;
      font-family: 'Inter', sans-serif;
    }
    
    .my-tickets-bg {
      min-height: 100vh;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 0;
    }
    
    .container {
      width: 100%;
      max-width: 1000px;
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
    
    /* Dark mode support */
    body.dark-mode .my-tickets-bg {
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%) !important;
    }
    
    body.dark-mode .card {
      background: #2d3748 !important;
      color: #e2e8f0 !important;
      border-color: #4a5568 !important;
    }
    
    body.dark-mode .h2 {
      color: #63b3ed !important;
    }
    
    body.dark-mode .small {
      color: #cbd5e0 !important;
    }
    
    body.dark-mode .stat-card {
      background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%) !important;
      color: #e2e8f0 !important;
    }
    
    body.dark-mode .stat-number {
      color: #f7fafc !important;
    }
    
    body.dark-mode .stat-label {
      color: #a0aec0 !important;
    }
    
    body.dark-mode .ticket-card {
      background: #2d3748 !important;
      color: #e2e8f0 !important;
      border-color: #4a5568 !important;
    }
    
    body.dark-mode .ticket-card.urgent {
      background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%) !important;
      border-left-color: #fc8181 !important;
    }
    
    body.dark-mode .ticket-card.high {
      background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%) !important;
      border-left-color: #f6ad55 !important;
    }
    
    body.dark-mode .ticket-card.medium {
      background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%) !important;
      border-left-color: #63b3ed !important;
    }
    
    body.dark-mode .ticket-card.low {
      background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%) !important;
      border-left-color: #68d391 !important;
    }
    
    body.dark-mode .priority-urgent {
      background: #742a2a !important;
      color: #fc8181 !important;
    }
    
    body.dark-mode .priority-high {
      background: #744210 !important;
      color: #f6ad55 !important;
    }
    
    body.dark-mode .priority-medium {
      background: #2c5282 !important;
      color: #63b3ed !important;
    }
    
    body.dark-mode .priority-low {
      background: #22543d !important;
      color: #68d391 !important;
    }
    
    body.dark-mode .status-pending {
      background: #744210 !important;
      color: #f6ad55 !important;
    }
    
    body.dark-mode .status-assigned {
      background: #2c5282 !important;
      color: #63b3ed !important;
    }
    
    body.dark-mode .status-in_progress {
      background: #744210 !important;
      color: #f6ad55 !important;
    }
    
    body.dark-mode .status-resolved {
      background: #22543d !important;
      color: #68d391 !important;
    }
    
    body.dark-mode .status-closed {
      background: #4a5568 !important;
      color: #a0aec0 !important;
    }
    
    body.dark-mode .modal {
      background: #2d3748 !important;
      color: #e2e8f0 !important;
    }
    
    body.dark-mode .modal-header {
      border-bottom-color: #4a5568 !important;
    }
    
    body.dark-mode .modal-title {
      color: #f7fafc !important;
    }
    
    body.dark-mode .modal-close {
      color: #a0aec0 !important;
    }
    
    body.dark-mode .modal-close:hover {
      background: #4a5568 !important;
      color: #e2e8f0 !important;
    }
    
    body.dark-mode .modal-actions {
      border-top-color: #4a5568 !important;
    }
    
    body.dark-mode .btn-outline {
      color: #63b3ed !important;
      border-color: #63b3ed !important;
    }
    
    body.dark-mode .btn-outline:hover {
      background: #63b3ed !important;
      color: #1a202c !important;
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
      <li><a href="<?php echo $dashboard_link; ?>" class="breadcrumb-link"><i class="fa fa-tachometer-alt"></i> Dashboard</a></li>
      <li style="color:var(--color-secondary);">&gt;</li>
      <li class="breadcrumb-current" style="color:var(--color-primary); font-weight:600;"><i class="fa fa-ticket-alt"></i> My Tickets</li>
    </ol>
  </nav>

  <div class="my-tickets-bg">
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
          <div class="stat-label">Pending</div>
        </div>
        <div class="card stat-card">
          <div class="stat-icon"><i class="fa fa-user-cog"></i></div>
          <div class="stat-number" id="assignedTickets">Loading...</div>
          <div class="stat-label">Assigned</div>
        </div>
        <div class="card stat-card">
          <div class="stat-icon"><i class="fa fa-check-circle"></i></div>
          <div class="stat-number" id="resolvedTickets">Loading...</div>
          <div class="stat-label">Resolved</div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="card" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-left: 6px solid #0ea5e9;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
          <h3 style="margin: 0; color: #0ea5e9; font-size: 1.2rem;">
            <i class="fa fa-bolt"></i>
            Quick Actions
          </h3>
          <button id="refreshTicketsBtn" class="btn btn-outline">
            <i class="fa fa-sync-alt"></i>
            Refresh
          </button>
        </div>
        
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
          <a href="submit_ticket.php" class="btn btn-primary">
            <i class="fa fa-plus"></i>
            Submit New Ticket
          </a>
          <button id="exportTicketsBtn" class="btn btn-outline">
            <i class="fa fa-download"></i>
            Export My Tickets
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="card" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-left: 6px solid #f59e0b;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
          <h3 style="margin: 0; color: #f59e0b; font-size: 1.2rem;">
            <i class="fa fa-filter"></i>
            Filter Tickets
          </h3>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
          <div>
            <label class="form-label">Status Filter</label>
            <select id="statusFilter" class="form-select">
              <option value="">All Status</option>
              <option value="pending">Pending</option>
              <option value="assigned">Assigned</option>
              <option value="in_progress">In Progress</option>
              <option value="resolved">Resolved</option>
              <option value="closed">Closed</option>
            </select>
          </div>
          <div>
            <label class="form-label">Priority Filter</label>
            <select id="priorityFilter" class="form-select">
              <option value="">All Priorities</option>
              <option value="urgent">Urgent</option>
              <option value="high">High</option>
              <option value="medium">Medium</option>
              <option value="low">Low</option>
            </select>
          </div>
          <div>
            <label class="form-label">Category Filter</label>
            <select id="categoryFilter" class="form-select">
              <option value="">All Categories</option>
              <option value="technical">Technical</option>
              <option value="billing">Billing</option>
              <option value="service">Service</option>
              <option value="general">General</option>
              <option value="outage">Outage</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Tickets List -->
      <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
          <div>
            <h2 class="h2">
              <i class="fa fa-ticket-alt"></i>
              My Support Tickets
            </h2>
            <p class="small">Track the status of your support requests and contact information for assigned technicians.</p>
          </div>
          <div style="display: flex; gap: 0.5rem; align-items: center;">
            <span id="ticketInfo" style="font-size: 0.9rem; color: #6b7280;">
              Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> tickets
            </span>
          </div>
        </div>
        
        <div id="ticketMsg"></div>
        
        <div id="ticketsList">
          <div style="text-align: center; padding: 2rem; color: #6b7280;">
            <div class="loading"></div>
            <div style="margin-top: 0.5rem;">Loading your tickets...</div>
          </div>
        </div>
      </div>

      <div class="footer mt-md small text-center" style="color: var(--color-secondary);">
        &copy; 2024 OutageSys. All rights reserved.
      </div>
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
        <a href="submit_ticket.php" class="btn btn-primary">
          <i class="fa fa-plus"></i>
          Submit New Ticket
        </a>
      </div>
    </div>
  </div>

  <script>
    // Global variables
    let allTickets = [];
    let filteredTickets = [];
    
    // Initialize
    loadTickets();
    loadStats();
    
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
            <i class="fa fa-ticket-alt" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
            No tickets found. <a href="submit_ticket.php" style="color: #2563eb; text-decoration: none;">Submit your first ticket</a>
          </div>
        `;
        return;
      }
      
      container.innerHTML = filteredTickets.map(ticket => `
        <div class="ticket-card ${ticket.priority}" data-ticket-id="${ticket.id}">
          <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <div style="flex: 1;">
              <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                <h4 style="margin: 0; color: #374151; font-size: 1.1rem;">${ticket.subject}</h4>
                <span class="priority-badge priority-${ticket.priority}">${ticket.priority}</span>
                <span class="status-badge status-${ticket.status}">${ticket.status.replace('_', ' ')}</span>
              </div>
              <div style="color: #6b7280; font-size: 0.9rem; margin-bottom: 0.5rem;">
                <strong>Ticket:</strong> ${ticket.ticket_number} • 
                <strong>Category:</strong> ${ticket.category} • 
                <strong>Created:</strong> ${new Date(ticket.created_at).toLocaleDateString()}
              </div>
              ${ticket.assigned_technician_name ? `
                <div style="color: #059669; font-size: 0.9rem; background: #ecfdf5; padding: 0.5rem; border-radius: 4px; margin-bottom: 0.5rem;">
                  <strong>Assigned to:</strong> ${ticket.assigned_technician_name}
                  ${ticket.assigned_technician_phone ? ` • Phone: ${ticket.assigned_technician_phone}` : ''}
                  ${ticket.assigned_technician_email ? ` • Email: ${ticket.assigned_technician_email}` : ''}
                </div>
              ` : `
                <div style="color: #f59e0b; font-size: 0.9rem; background: #fffbeb; padding: 0.5rem; border-radius: 4px; margin-bottom: 0.5rem;">
                  <i class="fa fa-clock"></i> Awaiting assignment to a technician
                </div>
              `}
            </div>
            <div style="display: flex; gap: 0.5rem;">
              <button class="btn btn-outline btn-sm" onclick="viewTicket(${ticket.id})" title="View Details">
                <i class="fa fa-eye"></i>
                View
              </button>
            </div>
          </div>
          <div style="color: #374151; font-size: 0.9rem;">
            ${ticket.description.substring(0, 150)}${ticket.description.length > 150 ? '...' : ''}
          </div>
        </div>
      `).join('');
    }
    
    // Update ticket info
    function updateTicketInfo() {
      document.getElementById('showingCount').textContent = filteredTickets.length;
      document.getElementById('totalCount').textContent = allTickets.length;
    }
    
    // Show notification
    function showNotification(message, type = 'success') {
      const notification = document.createElement('div');
      notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#22c55e' : '#ef4444'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1001;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        max-width: 300px;
        word-wrap: break-word;
      `;
      notification.textContent = message;
      document.body.appendChild(notification);
      
      setTimeout(() => {
        notification.style.transform = 'translateX(0)';
      }, 100);
      
      setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
          document.body.removeChild(notification);
        }, 300);
      }, 3000);
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
        
        ${ticket.assigned_technician_name ? `
          <div style="margin-bottom: 1rem;">
            <h4 style="margin-bottom: 0.5rem; color: #374151;">Assigned Technician</h4>
            <div style="background: #ecfdf5; padding: 1rem; border-radius: 8px; border-left: 4px solid #10b981;">
              <div><strong>Name:</strong> ${ticket.assigned_technician_name}</div>
              <div><strong>Email:</strong> ${ticket.assigned_technician_email || 'Not provided'}</div>
              <div><strong>Phone:</strong> ${ticket.assigned_technician_phone || 'Not provided'}</div>
              <div style="margin-top: 0.5rem; font-size: 0.9rem; color: #059669;">
                <i class="fa fa-info-circle"></i> You can contact this technician directly for updates on your ticket.
              </div>
            </div>
          </div>
        ` : `
          <div style="margin-bottom: 1rem;">
            <h4 style="margin-bottom: 0.5rem; color: #374151;">Assignment Status</h4>
            <div style="background: #fffbeb; padding: 1rem; border-radius: 8px; border-left: 4px solid #f59e0b;">
              <div style="color: #d97706;">
                <i class="fa fa-clock"></i> Your ticket is pending assignment to a technician.
              </div>
              <div style="margin-top: 0.5rem; font-size: 0.9rem; color: #92400e;">
                You will be notified when a technician is assigned to your ticket.
              </div>
            </div>
          </div>
        `}
        
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
    
    // Export tickets
    document.getElementById('exportTicketsBtn').addEventListener('click', function() {
      const csvContent = 'data:text/csv;charset=utf-8,' + 
        'Ticket Number,Subject,Status,Priority,Category,Created,Assigned To\n' +
        filteredTickets.map(ticket => 
          `${ticket.ticket_number},"${ticket.subject}","${ticket.status}","${ticket.priority}","${ticket.category}","${ticket.created_at}","${ticket.assigned_technician_name || 'Unassigned'}"`
        ).join('\n');
      
      const encodedUri = encodeURI(csvContent);
      const link = document.createElement('a');
      link.setAttribute('href', encodedUri);
      link.setAttribute('download', 'my_tickets_export.csv');
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      
      showNotification('Tickets exported successfully!', 'success');
    });
    
    // Modal close handlers
    document.getElementById('viewModalClose').addEventListener('click', function() {
      document.getElementById('viewModalBg').style.display = 'none';
    });
    
    document.getElementById('viewCloseBtn').addEventListener('click', function() {
      document.getElementById('viewModalBg').style.display = 'none';
    });
    
    // Close modal when clicking outside
    document.getElementById('viewModalBg').addEventListener('click', function(e) {
      if (e.target === this) {
        this.style.display = 'none';
      }
    });
  </script>
  <script src="js/dark-mode.js"></script>
</body>
</html> 