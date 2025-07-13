<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
  header('Location: login.php');
  exit;
}
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];
$dashboard_link = 'technician_dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Assigned Tickets - OutageSys</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    body { 
      min-height: 100vh; 
      margin: 0;
      font-family: 'Inter', sans-serif;
    }
    
    .technician-tickets-bg {
      min-height: 100vh;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 0;
      position: relative;
      overflow: hidden;
    }
    
    /* Particle effects */
    .particles {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: 1;
    }
    
    .particle {
      position: absolute;
      width: 4px;
      height: 4px;
      background: rgba(255, 255, 255, 0.3);
      border-radius: 50%;
      animation: float 6s infinite linear;
    }
    
    @keyframes float {
      0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
      10% { opacity: 1; }
      90% { opacity: 1; }
      100% { transform: translateY(-100px) rotate(360deg); opacity: 0; }
    }
    
    .container {
      width: 100%;
      max-width: 1200px;
      padding: 2rem;
      position: relative;
      z-index: 2;
    }
    
    .card {
      background: #fff;
      border-radius: 18px;
      padding: 2.5em 2em;
      margin-bottom: 2em;
      box-shadow: 0 8px 32px rgba(0,0,0,0.1);
      border: 1px solid rgba(255,255,255,0.2);
      backdrop-filter: blur(10px);
      transition: all 0.3s ease;
    }
    
    .card:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 40px rgba(0,0,0,0.15);
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
      position: relative;
      overflow: hidden;
    }
    
    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
      transition: left 0.5s;
    }
    
    .stat-card:hover::before {
      left: 100%;
    }
    
    .stat-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 25px rgba(37,99,235,0.15);
    }
    
    .stat-icon {
      font-size: 2rem;
      color: #2563eb;
      margin-bottom: 0.5rem;
      animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }
    
    .stat-number {
      font-size: 2rem;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 0.25rem;
      animation: countUp 1s ease-out;
    }
    
    @keyframes countUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
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
      position: relative;
      overflow: hidden;
    }
    
    .ticket-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: #2563eb;
      transform: scaleY(0);
      transition: transform 0.3s ease;
    }
    
    .ticket-card:hover::before {
      transform: scaleY(1);
    }
    
    .ticket-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }
    
    .ticket-card.urgent {
      border-left-color: #ef4444;
      background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
      animation: urgentPulse 2s infinite;
    }
    
    @keyframes urgentPulse {
      0%, 100% { box-shadow: 0 2px 8px rgba(239,68,68,0.2); }
      50% { box-shadow: 0 2px 8px rgba(239,68,68,0.4); }
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
      animation: badgeGlow 2s infinite;
    }
    
    @keyframes badgeGlow {
      0%, 100% { box-shadow: 0 0 5px rgba(0,0,0,0.1); }
      50% { box-shadow: 0 0 10px rgba(0,0,0,0.2); }
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
    
    /* Floating Action Button */
    .fab {
      position: fixed;
      bottom: 2rem;
      right: 2rem;
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.5rem;
      cursor: pointer;
      box-shadow: 0 8px 25px rgba(37,99,235,0.3);
      transition: all 0.3s ease;
      z-index: 1000;
      animation: fabFloat 3s ease-in-out infinite;
    }
    
    @keyframes fabFloat {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }
    
    .fab:hover {
      transform: translateY(-5px) scale(1.1);
      box-shadow: 0 12px 35px rgba(37,99,235,0.4);
    }
    
    .fab-menu {
      position: fixed;
      bottom: 5rem;
      right: 2rem;
      background: white;
      border-radius: 12px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
      padding: 0.5rem;
      display: none;
      z-index: 999;
      animation: slideIn 0.3s ease;
    }
    
    @keyframes slideIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .fab-item {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.75rem 1rem;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.2s;
      color: #374151;
      text-decoration: none;
    }
    
    .fab-item:hover {
      background: #f3f4f6;
      transform: translateX(5px);
    }
    
    /* Enhanced Notifications */
    .notification {
      position: fixed;
      top: 20px;
      right: 20px;
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
    }
    
    .notification.success {
      background: linear-gradient(135deg, #22c55e, #16a34a);
      color: white;
    }
    
    .notification.error {
      background: linear-gradient(135deg, #ef4444, #dc2626);
      color: white;
    }
    
    .notification.info {
      background: linear-gradient(135deg, #3b82f6, #2563eb);
      color: white;
    }
    
    .notification.warning {
      background: linear-gradient(135deg, #f59e0b, #d97706);
      color: white;
    }
    
    .notification-close {
      background: none;
      border: none;
      color: white;
      cursor: pointer;
      font-size: 1.2rem;
      opacity: 0.7;
      transition: opacity 0.2s;
      margin-left: auto;
    }
    
    .notification-close:hover {
      opacity: 1;
    }
    
    /* Live Activity Feed */
    .activity-feed {
      position: fixed;
      top: 2rem;
      left: 2rem;
      background: white;
      border-radius: 12px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
      padding: 1rem;
      max-width: 300px;
      z-index: 1000;
      display: none;
      animation: slideInLeft 0.3s ease;
    }
    
    @keyframes slideInLeft {
      from { opacity: 0; transform: translateX(-20px); }
      to { opacity: 1; transform: translateX(0); }
    }
    
    .activity-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem;
      border-radius: 6px;
      margin-bottom: 0.5rem;
      background: #f8fafc;
      font-size: 0.8rem;
    }
    
    .activity-icon {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #22c55e;
      animation: pulse 2s infinite;
    }
    
    /* Quick Actions Menu */
    .quick-actions {
      position: fixed;
      top: 50%;
      right: 2rem;
      transform: translateY(-50%);
      background: white;
      border-radius: 12px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
      padding: 1rem;
      z-index: 1000;
      display: none;
      animation: slideInRight 0.3s ease;
    }
    
    @keyframes slideInRight {
      from { opacity: 0; transform: translateX(20px); }
      to { opacity: 1; transform: translateX(0); }
    }
    
    .quick-action-btn {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem;
      border: none;
      background: none;
      cursor: pointer;
      border-radius: 6px;
      transition: all 0.2s;
      width: 100%;
      text-align: left;
    }
    
    .quick-action-btn:hover {
      background: #f3f4f6;
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
      animation: modalSlideIn 0.3s ease;
    }
    
    @keyframes modalSlideIn {
      from { opacity: 0; transform: scale(0.9); }
      to { opacity: 1; transform: scale(1); }
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
      
      .fab {
        bottom: 1rem;
        right: 1rem;
      }
      
      .activity-feed,
      .quick-actions {
        display: none !important;
      }
    }
  </style>
</head>
<body>
  <!-- Breadcrumbs navigation -->
  <nav aria-label="Breadcrumb" class="breadcrumbs-nav" style="margin: 1.5rem auto 0 auto; max-width:900px; background:transparent;">
    <ol class="breadcrumbs" style="display:flex; flex-wrap:wrap; gap:0.5em; list-style:none; padding:0; margin:0; font-size:1rem; background:transparent;">
      <li><a href="<?php echo $dashboard_link; ?>" class="breadcrumb-link"><i class="fa fa-tachometer-alt"></i> Technician Dashboard</a></li>
      <li style="color:var(--color-secondary);">&gt;</li>
      <li class="breadcrumb-current" style="color:var(--color-primary); font-weight:600;"><i class="fa fa-ticket-alt"></i> My Assigned Tickets</li>
    </ol>
  </nav>

  <!-- Particle Effects -->
  <div class="particles" id="particles"></div>
  
  <div class="technician-tickets-bg">
    <div class="container">
      <!-- Enhanced Stats Cards with Animations -->
      <div class="stats-grid">
        <div class="card stat-card" data-stat="total">
          <div class="stat-icon"><i class="fa fa-ticket-alt"></i></div>
          <div class="stat-number" id="totalAssigned">Loading...</div>
          <div class="stat-label">Total Assigned</div>
        </div>
        <div class="card stat-card" data-stat="pending">
          <div class="stat-icon"><i class="fa fa-clock"></i></div>
          <div class="stat-number" id="pendingTickets">Loading...</div>
          <div class="stat-label">Pending</div>
        </div>
        <div class="card stat-card" data-stat="progress">
          <div class="stat-icon"><i class="fa fa-tools"></i></div>
          <div class="stat-number" id="inProgressTickets">Loading...</div>
          <div class="stat-label">In Progress</div>
        </div>
        <div class="card stat-card" data-stat="resolved">
          <div class="stat-icon"><i class="fa fa-check-circle"></i></div>
          <div class="stat-number" id="resolvedTickets">Loading...</div>
          <div class="stat-label">Resolved</div>
        </div>
      </div>

      <!-- Filters -->
      <div class="card" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-left: 6px solid #0ea5e9;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
          <h3 style="margin: 0; color: #0ea5e9; font-size: 1.2rem;">
            <i class="fa fa-filter"></i>
            Filter Tickets
          </h3>
          <button id="refreshTicketsBtn" class="btn btn-outline">
            <i class="fa fa-sync-alt"></i>
            Refresh
          </button>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
          <div>
            <label class="form-label">Status Filter</label>
            <select id="statusFilter" class="form-select">
              <option value="">All Status</option>
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
              My Assigned Tickets
            </h2>
            <p class="small">Manage tickets assigned to you and update their status.</p>
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
            <div style="margin-top: 0.5rem;">Loading your assigned tickets...</div>
          </div>
        </div>
      </div>

      <div class="footer mt-md small text-center" style="color: var(--color-secondary);">
        &copy; 2024 OutageSys. All rights reserved.
      </div>
    </div>
  </div>
  
  <!-- Floating Action Button -->
  <div class="fab" id="fab" title="Quick Actions">
    <i class="fa fa-plus"></i>
  </div>
  
  <!-- Floating Action Menu -->
  <div class="fab-menu" id="fabMenu">
    <div class="fab-item" onclick="refreshTickets()">
      <i class="fa fa-sync-alt"></i>
      <span>Refresh</span>
    </div>
    <div class="fab-item" onclick="showActivityFeed()">
      <i class="fa fa-bell"></i>
      <span>Activity</span>
    </div>
    <div class="fab-item" onclick="showQuickActions()">
      <i class="fa fa-bolt"></i>
      <span>Quick Actions</span>
    </div>
    <div class="fab-item" onclick="testNotification('success', '🎉 All tickets updated!')">
      <i class="fa fa-rocket"></i>
      <span>Test Features</span>
    </div>
  </div>
  
  <!-- Live Activity Feed -->
  <div class="activity-feed" id="activityFeed">
    <h4 style="margin: 0 0 1rem 0; color: #2563eb;">
      <i class="fa fa-bell"></i> Live Activity
    </h4>
    <div id="activityItems">
      <div class="activity-item">
        <div class="activity-icon"></div>
        <span>System loaded successfully</span>
      </div>
    </div>
  </div>
  
  <!-- Quick Actions Menu -->
  <div class="quick-actions" id="quickActions">
    <h4 style="margin: 0 0 1rem 0; color: #2563eb;">
      <i class="fa fa-bolt"></i> Quick Actions
    </h4>
    <button class="quick-action-btn" onclick="filterByPriority('urgent')">
      <i class="fa fa-exclamation-triangle"></i>
      <span>Show Urgent</span>
    </button>
    <button class="quick-action-btn" onclick="filterByStatus('in_progress')">
      <i class="fa fa-tools"></i>
      <span>In Progress</span>
    </button>
    <button class="quick-action-btn" onclick="filterByStatus('assigned')">
      <i class="fa fa-clock"></i>
      <span>Pending</span>
    </button>
    <button class="quick-action-btn" onclick="clearFilters()">
      <i class="fa fa-times"></i>
      <span>Clear Filters</span>
    </button>
  </div>

  <!-- Update Status Modal -->
  <div class="modal-bg" id="statusModalBg">
    <div class="modal" id="statusModal">
      <div class="modal-header">
        <h3 class="modal-title" id="statusModalTitle">Update Ticket Status</h3>
        <button class="modal-close" id="statusModalClose">&times;</button>
      </div>
      
      <div id="statusModalAlert" style="display: none;"></div>
      
      <form id="statusForm">
        <input type="hidden" id="statusTicketId" />
        
        <div class="form-group">
          <label class="form-label">Ticket Details</label>
          <div id="statusTicketDetails" style="background: #f9fafb; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
            <!-- Ticket details will be populated here -->
          </div>
        </div>
        
        <div class="form-group">
          <label class="form-label">Update Status</label>
          <select id="statusSelect" class="form-select" required>
            <option value="">Select Status</option>
            <option value="in_progress">In Progress</option>
            <option value="resolved">Resolved</option>
            <option value="closed">Closed</option>
          </select>
        </div>
        
        <div class="modal-actions">
          <button type="button" class="btn btn-outline" id="statusCancelBtn">Cancel</button>
          <button type="submit" class="btn btn-primary" id="statusSaveBtn">
            <i class="fa fa-save"></i>
            Update Status
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
        <button type="button" class="btn btn-primary" id="updateStatusBtn">
          <i class="fa fa-edit"></i>
          Update Status
        </button>
      </div>
    </div>
  </div>

  <script>
    // Global variables
    let allTickets = [];
    let filteredTickets = [];
    let fabMenuOpen = false;
    let activityFeedOpen = false;
    let quickActionsOpen = false;
    
    // Initialize
    console.log('Technician ID:', <?php echo $user_id; ?>);
    console.log('Technician Role:', '<?php echo $_SESSION['role']; ?>');
    loadTickets();
    loadStats();
    initParticles();
    initKeyboardShortcuts();
    showEnhancedNotification('success', '🚀 Welcome to your enhanced ticket dashboard!');
    
    // Load statistics
    function loadStats() {
      fetch('api/tickets.php')
        .then(res => res.json())
        .then(data => {
          console.log('Stats response:', data);
          if (Array.isArray(data)) {
            const total = data.length;
            const pending = data.filter(t => t.status === 'assigned').length;
            const inProgress = data.filter(t => t.status === 'in_progress').length;
            const resolved = data.filter(t => t.status === 'resolved' || t.status === 'closed').length;
            
            document.getElementById('totalAssigned').textContent = total;
            document.getElementById('pendingTickets').textContent = pending;
            document.getElementById('inProgressTickets').textContent = inProgress;
            document.getElementById('resolvedTickets').textContent = resolved;
            
            console.log(`Stats: Total=${total}, Pending=${pending}, InProgress=${inProgress}, Resolved=${resolved}`);
          } else {
            document.getElementById('totalAssigned').textContent = '0';
            document.getElementById('pendingTickets').textContent = '0';
            document.getElementById('inProgressTickets').textContent = '0';
            document.getElementById('resolvedTickets').textContent = '0';
            console.error('Invalid stats response format:', data);
          }
        })
        .catch(error => {
          console.error('Error loading stats:', error);
          document.getElementById('totalAssigned').textContent = 'Error';
          document.getElementById('pendingTickets').textContent = 'Error';
          document.getElementById('inProgressTickets').textContent = 'Error';
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
      
      console.log('Loading tickets from:', url);
      
      fetch(url)
        .then(res => res.json())
        .then(data => {
          console.log('Tickets response:', data);
          if (Array.isArray(data)) {
            allTickets = data;
            filteredTickets = data;
            displayTickets();
            updateTicketInfo();
            showEnhancedNotification('info', `📋 Loaded ${data.length} assigned tickets`);
          } else {
            console.error('Invalid response format:', data);
            allTickets = [];
            filteredTickets = [];
            displayTickets();
            showEnhancedNotification('error', '❌ Invalid response format from server');
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
          showEnhancedNotification('error', '❌ Network error loading tickets');
        });
    }
    
    // Display tickets with enhanced animations
    function displayTickets() {
      const container = document.getElementById('ticketsList');
      
      if (!filteredTickets.length) {
        container.innerHTML = `
          <div style="text-align: center; color: #6b7280; padding: 2rem;">
            <i class="fa fa-ticket-alt" style="font-size: 2rem; margin-bottom: 0.5rem; display: block; animation: bounce 2s infinite;"></i>
            <div style="margin-top: 1rem; font-size: 1.1rem;">No tickets assigned to you.</div>
            <div style="margin-top: 0.5rem; color: #9ca3af;">🎉 You're all caught up!</div>
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
              </div>
              <div style="color: #6b7280; font-size: 0.9rem; margin-bottom: 0.5rem;">
                <strong>🎫 Ticket:</strong> ${ticket.ticket_number} • 
                <strong>📂 Category:</strong> ${ticket.category} • 
                <strong>📅 Created:</strong> ${new Date(ticket.created_at).toLocaleDateString()}
              </div>
              <div style="color: #6b7280; font-size: 0.9rem;">
                <strong>👤 From:</strong> ${ticket.user_name} (${ticket.user_email})
                ${ticket.user_phone ? ` • 📞 Phone: ${ticket.user_phone}` : ''}
              </div>
            </div>
            <div style="display: flex; gap: 0.5rem;">
              <button class="btn btn-outline btn-sm" onclick="viewTicket(${ticket.id})" title="View Details" style="transition: all 0.2s;">
                <i class="fa fa-eye"></i>
              </button>
              ${ticket.status !== 'resolved' && ticket.status !== 'closed' ? `
                <button class="btn btn-warning btn-sm" onclick="updateStatus(${ticket.id})" title="Update Status" style="transition: all 0.2s;">
                  <i class="fa fa-edit"></i>
                </button>
              ` : ''}
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
    
    // Initialize particle effects
    function initParticles() {
      const particlesContainer = document.getElementById('particles');
      const particleCount = 20;
      
      for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.animationDelay = Math.random() * 6 + 's';
        particle.style.animationDuration = (Math.random() * 3 + 3) + 's';
        particlesContainer.appendChild(particle);
      }
    }
    
    // Initialize keyboard shortcuts
    function initKeyboardShortcuts() {
      document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K: Focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
          e.preventDefault();
          document.getElementById('statusFilter').focus();
          showEnhancedNotification('info', '⌨️ Search focused!');
        }
        
        // Ctrl/Cmd + R: Refresh
        if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
          e.preventDefault();
          refreshTickets();
        }
        
        // Ctrl/Cmd + Q: Quick actions
        if ((e.ctrlKey || e.metaKey) && e.key === 'q') {
          e.preventDefault();
          showQuickActions();
        }
        
        // Escape: Close modals and menus
        if (e.key === 'Escape') {
          closeAllMenus();
        }
      });
    }
    
    // Enhanced notification with emojis and animations
    function showEnhancedNotification(type, message) {
      const notification = document.createElement('div');
      const icons = {
        success: '🎉',
        error: '❌',
        info: 'ℹ️',
        warning: '⚠️'
      };
      
      notification.className = `notification ${type}`;
      notification.innerHTML = `
        <span style="font-size: 1.2rem;">${icons[type] || icons.info}</span>
        <span style="flex: 1;">${message}</span>
        <button class="notification-close" onclick="this.parentElement.remove()">×</button>
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
    
    // Legacy notification function for compatibility
    function showNotification(message, type = 'success') {
      showEnhancedNotification(type, message);
    }
    
    // Update ticket status
    function updateStatus(ticketId) {
      const ticket = allTickets.find(t => t.id == ticketId);
      if (!ticket) {
        showNotification('Ticket not found', 'error');
        return;
      }
      
      document.getElementById('statusTicketId').value = ticketId;
      document.getElementById('statusTicketDetails').innerHTML = `
        <div><strong>Ticket:</strong> ${ticket.ticket_number}</div>
        <div><strong>Subject:</strong> ${ticket.subject}</div>
        <div><strong>From:</strong> ${ticket.user_name} (${ticket.user_email})</div>
        <div><strong>Current Status:</strong> <span class="status-badge status-${ticket.status}">${ticket.status.replace('_', ' ')}</span></div>
        <div><strong>Priority:</strong> <span class="priority-badge priority-${ticket.priority}">${ticket.priority}</span></div>
      `;
      
      document.getElementById('statusModalBg').style.display = 'flex';
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
    
    // Floating Action Button functionality
    document.getElementById('fab').addEventListener('click', function() {
      const menu = document.getElementById('fabMenu');
      if (fabMenuOpen) {
        menu.style.display = 'none';
        fabMenuOpen = false;
        this.innerHTML = '<i class="fa fa-plus"></i>';
      } else {
        menu.style.display = 'block';
        fabMenuOpen = true;
        this.innerHTML = '<i class="fa fa-times"></i>';
      }
    });
    
    // Close all menus
    function closeAllMenus() {
      document.getElementById('fabMenu').style.display = 'none';
      document.getElementById('activityFeed').style.display = 'none';
      document.getElementById('quickActions').style.display = 'none';
      document.getElementById('fab').innerHTML = '<i class="fa fa-plus"></i>';
      fabMenuOpen = false;
      activityFeedOpen = false;
      quickActionsOpen = false;
    }
    
    // Show activity feed
    function showActivityFeed() {
      const feed = document.getElementById('activityFeed');
      if (activityFeedOpen) {
        feed.style.display = 'none';
        activityFeedOpen = false;
      } else {
        feed.style.display = 'block';
        activityFeedOpen = true;
        addActivityItem('📊 Activity feed opened');
      }
    }
    
    // Show quick actions
    function showQuickActions() {
      const actions = document.getElementById('quickActions');
      if (quickActionsOpen) {
        actions.style.display = 'none';
        quickActionsOpen = false;
      } else {
        actions.style.display = 'block';
        quickActionsOpen = true;
        addActivityItem('⚡ Quick actions opened');
      }
    }
    
    // Add activity item
    function addActivityItem(message) {
      const container = document.getElementById('activityItems');
      const item = document.createElement('div');
      item.className = 'activity-item';
      item.innerHTML = `
        <div class="activity-icon"></div>
        <span>${message}</span>
      `;
      container.insertBefore(item, container.firstChild);
      
      // Keep only last 5 items
      while (container.children.length > 5) {
        container.removeChild(container.lastChild);
      }
    }
    
    // Filter functions
    function filterByPriority(priority) {
      document.getElementById('priorityFilter').value = priority;
      loadTickets();
      showEnhancedNotification('info', `🔍 Filtered by ${priority} priority`);
      addActivityItem(`Filtered by ${priority} priority`);
    }
    
    function filterByStatus(status) {
      document.getElementById('statusFilter').value = status;
      loadTickets();
      showEnhancedNotification('info', `🔍 Filtered by ${status} status`);
      addActivityItem(`Filtered by ${status} status`);
    }
    
    function clearFilters() {
      document.getElementById('statusFilter').value = '';
      document.getElementById('priorityFilter').value = '';
      document.getElementById('categoryFilter').value = '';
      loadTickets();
      showEnhancedNotification('success', '🧹 All filters cleared');
      addActivityItem('All filters cleared');
    }
    
    // Enhanced refresh function
    function refreshTickets() {
      const btn = document.getElementById('refreshTicketsBtn');
      btn.innerHTML = '<div class="loading"></div> Refreshing...';
      btn.disabled = true;
      
      loadTickets();
      loadStats();
      
      setTimeout(() => {
        btn.innerHTML = '<i class="fa fa-sync-alt"></i> Refresh';
        btn.disabled = false;
        showEnhancedNotification('success', '🔄 Data refreshed successfully!');
        addActivityItem('Data refreshed');
      }, 1000);
    }
    
    // Test notification function
    function testNotification(type, message) {
      showEnhancedNotification(type, message);
      addActivityItem('Test notification sent');
    }
    
    // Event listeners
    document.getElementById('refreshTicketsBtn').addEventListener('click', refreshTickets);
    
    // Filter event listeners
    document.getElementById('statusFilter').addEventListener('change', loadTickets);
    document.getElementById('priorityFilter').addEventListener('change', loadTickets);
    document.getElementById('categoryFilter').addEventListener('change', loadTickets);
    
    // Status form submission
    document.getElementById('statusForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const ticketId = document.getElementById('statusTicketId').value;
      const status = document.getElementById('statusSelect').value;
      
      if (!status) {
        showNotification('Please select a status', 'error');
        return;
      }
      
      fetch('api/tickets.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({
          action: 'update_status',
          ticket_id: ticketId,
          status: status
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showEnhancedNotification('success', '✅ Ticket status updated successfully!');
          addActivityItem(`Updated ticket status to ${status}`);
          document.getElementById('statusModalBg').style.display = 'none';
          loadTickets();
          loadStats();
        } else {
          showEnhancedNotification('error', '❌ Failed to update status: ' + (data.error || 'Unknown error'));
        }
      })
      .catch(error => {
        console.error('Error updating status:', error);
        showNotification('Network error occurred', 'error');
      });
    });
    
    // Modal close handlers
    document.getElementById('statusModalClose').addEventListener('click', function() {
      document.getElementById('statusModalBg').style.display = 'none';
    });
    
    document.getElementById('statusCancelBtn').addEventListener('click', function() {
      document.getElementById('statusModalBg').style.display = 'none';
    });
    
    document.getElementById('viewModalClose').addEventListener('click', function() {
      document.getElementById('viewModalBg').style.display = 'none';
    });
    
    document.getElementById('viewCloseBtn').addEventListener('click', function() {
      document.getElementById('viewModalBg').style.display = 'none';
    });
    
    // Update status button in view modal
    document.getElementById('updateStatusBtn').addEventListener('click', function() {
      const ticketId = document.querySelector('#ticketFullDetails').getAttribute('data-ticket-id');
      if (ticketId) {
        updateStatus(ticketId);
        document.getElementById('viewModalBg').style.display = 'none';
      }
    });
    
    // Close modals when clicking outside
    document.getElementById('statusModalBg').addEventListener('click', function(e) {
      if (e.target === this) {
        this.style.display = 'none';
      }
    });
    
    document.getElementById('viewModalBg').addEventListener('click', function(e) {
      if (e.target === this) {
        this.style.display = 'none';
      }
    });
  </script>
</body>
</html> 