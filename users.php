<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$role = $_SESSION['role'];
$username = $_SESSION['username'];
$dashboard_link = 'user_dashboard.php';
if ($role === 'admin') $dashboard_link = 'admin_dashboard.php';
if ($role === 'technician') $dashboard_link = 'technician_dashboard.php';

// Only admin can access user management
if ($role !== 'admin') {
  header('Location: ' . $dashboard_link);
  exit;
}

// Load departments from database
require_once 'db.php';
$departments = [];
try {
    $stmt = $pdo->query("SELECT id, name FROM departments ORDER BY name ASC");
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // If departments table doesn't exist, use default departments
    $departments = [
        ['id' => 1, 'name' => 'IT & Technology'],
        ['id' => 2, 'name' => 'Operations'],
        ['id' => 3, 'name' => 'Engineering'],
        ['id' => 4, 'name' => 'Customer Service'],
        ['id' => 5, 'name' => 'Management'],
        ['id' => 6, 'name' => 'Safety & Compliance'],
        ['id' => 7, 'name' => 'Finance'],
        ['id' => 8, 'name' => 'Human Resources'],
        ['id' => 9, 'name' => 'General']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Management - OutageSys</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    body { 
      min-height: 100vh; 
      margin: 0;
      font-family: 'Inter', sans-serif;
    }
    
    .users-bg {
      min-height: 100vh;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 0;
      position: relative;
      overflow: hidden;
    }
    
    .users-bg::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
      animation: float 20s ease-in-out infinite;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-20px) rotate(1deg); }
    }
    
    .container {
      width: 100%;
      max-width: 1200px;
      padding: 2rem;
    }
    
    .card {
      background: #fff;
      border-radius: 18px;
      padding: 2.5em 2em;
      margin-bottom: 2em;
      box-shadow: 0 8px 32px rgba(0,0,0,0.1);
      border: 1px solid rgba(255,255,255,0.2);
      backdrop-filter: blur(10px);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }
    
    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s;
    }
    
    .card:hover::before {
      left: 100%;
    }
    
    .card:hover {
      transform: translateY(-5px) scale(1.02);
      box-shadow: 0 20px 40px rgba(0,0,0,0.15);
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
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      font-size: 0.9rem;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      position: relative;
      overflow: hidden;
    }
    
    .btn::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      background: rgba(255,255,255,0.2);
      border-radius: 50%;
      transition: all 0.3s;
      transform: translate(-50%, -50%);
    }
    
    .btn:hover::before {
      width: 300px;
      height: 300px;
    }
    
    .btn:active {
      transform: scale(0.95);
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
    
    .search-container {
      background: #f8fafc;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      border-left: 4px solid #2563eb;
    }
    
    .search-input {
      width: 100%;
      padding: 0.75rem;
      border: 2px solid #e5e7eb;
      border-radius: 8px;
      font-size: 0.9rem;
      transition: border-color 0.2s;
    }
    
    .search-input:focus {
      outline: none;
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    }
    
    .table-container {
      background: #fff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    }
    
    .styled-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.9rem;
    }
    
    .styled-table th {
      background: #f8fafc;
      color: #374151;
      font-weight: 600;
      padding: 1rem;
      text-align: left;
      border-bottom: 2px solid #e5e7eb;
    }
    
    .styled-table td {
      padding: 1rem;
      border-bottom: 1px solid #f3f4f6;
      color: #374151;
    }
    
    .styled-table tr {
      transition: all 0.3s ease;
      position: relative;
    }
    
    .styled-table tr:hover {
      background: #f9fafb;
      transform: translateX(5px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .styled-table tr::after {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      height: 100%;
      width: 4px;
      background: #2563eb;
      transform: scaleY(0);
      transition: transform 0.3s ease;
    }
    
    .styled-table tr:hover::after {
      transform: scaleY(1);
    }
    
    .status-badge {
      padding: 0.25rem 0.75rem;
      border-radius: 12px;
      font-size: 0.8rem;
      font-weight: 500;
      text-transform: capitalize;
    }
    
    .status-active {
      background: #dcfce7;
      color: #166534;
    }
    
    .status-inactive {
      background: #fef2f2;
      color: #dc2626;
    }
    
    .role-badge {
      padding: 0.25rem 0.75rem;
      border-radius: 12px;
      font-size: 0.8rem;
      font-weight: 500;
      text-transform: capitalize;
    }
    
    .role-admin {
      background: #dbeafe;
      color: #1e40af;
    }
    
    .role-technician {
      background: #fef3c7;
      color: #d97706;
    }
    
    .role-user {
      background: #f3f4f6;
      color: #374151;
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
      min-width: 400px;
      max-width: 90vw;
      box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
      position: relative;
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
    
    .alert {
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1rem;
      font-size: 0.9rem;
    }
    
    .alert-success {
      background: #dcfce7;
      color: #166534;
      border: 1px solid #bbf7d0;
    }
    
    .alert-error {
      background: #fef2f2;
      color: #dc2626;
      border: 1px solid #fecaca;
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
    
    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateX(-20px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }
    
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }
    
    @keyframes bounce {
      0%, 20%, 53%, 80%, 100% { transform: translate3d(0,0,0); }
      40%, 43% { transform: translate3d(0,-8px,0); }
      70% { transform: translate3d(0,-4px,0); }
      90% { transform: translate3d(0,-2px,0); }
    }
    
    @keyframes glow {
      0%, 100% { box-shadow: 0 0 5px rgba(37,99,235,0.5); }
      50% { box-shadow: 0 0 20px rgba(37,99,235,0.8), 0 0 30px rgba(37,99,235,0.6); }
    }
    
    .pulse {
      animation: pulse 2s infinite;
    }
    
    .bounce {
      animation: bounce 1s infinite;
    }
    
    .glow {
      animation: glow 2s ease-in-out infinite alternate;
    }
    
    /* Dark mode support */
    body.dark-mode .users-bg {
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%) !important;
    }
    
    body.dark-mode .card {
      background: #2d3748 !important;
      color: #e2e8f0 !important;
      border-color: #4a5568 !important;
    }
    
    body.dark-mode .search-container {
      background: #374151 !important;
      color: #e2e8f0 !important;
    }
    
    body.dark-mode .table-container {
      background: #2d3748 !important;
    }
    
    body.dark-mode .styled-table th {
      background: #374151 !important;
      color: #e2e8f0 !important;
      border-bottom-color: #4a5568 !important;
    }
    
    body.dark-mode .styled-table td {
      color: #e2e8f0 !important;
      border-bottom-color: #4a5568 !important;
    }
    
    body.dark-mode .styled-table tr:hover {
      background: #374151 !important;
    }
    
    body.dark-mode .modal {
      background: #2d3748 !important;
      color: #e2e8f0 !important;
    }
    
    body.dark-mode .form-input,
    body.dark-mode .form-select {
      background: #4a5568 !important;
      color: #e2e8f0 !important;
      border-color: #718096 !important;
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
      
      .styled-table {
        font-size: 0.8rem;
      }
      
      .styled-table th,
      .styled-table td {
        padding: 0.75rem 0.5rem;
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
      <li class="breadcrumb-current" style="color:var(--color-primary); font-weight:600;"><i class="fa fa-users"></i> User Management</li>
    </ol>
  </nav>

  <div class="users-bg">
    <div class="container">
      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="card stat-card">
          <div class="stat-icon"><i class="fa fa-users"></i></div>
          <div class="stat-number" id="totalUsers">Loading...</div>
          <div class="stat-label">Total Users</div>
        </div>
        <div class="card stat-card">
          <div class="stat-icon"><i class="fa fa-user-shield"></i></div>
          <div class="stat-number" id="adminUsers">Loading...</div>
          <div class="stat-label">Administrators</div>
        </div>
        <div class="card stat-card">
          <div class="stat-icon"><i class="fa fa-tools"></i></div>
          <div class="stat-number" id="technicianUsers">Loading...</div>
          <div class="stat-label">Technicians</div>
        </div>
        <div class="card stat-card">
          <div class="stat-icon"><i class="fa fa-user-check"></i></div>
          <div class="stat-number" id="activeUsers">Loading...</div>
          <div class="stat-label">Active Users</div>
        </div>
      </div>

      <!-- Quick Actions Card -->
      <div class="card" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-left: 6px solid #0ea5e9;">
        <h3 style="margin-bottom: 1rem; color: #0ea5e9; font-size: 1.2rem;">
          <i class="fa fa-bolt"></i>
          Quick Actions
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
          <button id="generateReportBtn" class="btn btn-outline" style="justify-content: center;">
            <i class="fa fa-file-alt"></i>
            Generate Report
          </button>
          <button id="backupUsersBtn" class="btn btn-outline" style="justify-content: center;">
            <i class="fa fa-database"></i>
            Backup Users
          </button>
          <button id="sendNotificationBtn" class="btn btn-outline" style="justify-content: center;">
            <i class="fa fa-bell"></i>
            Send Notification
          </button>
          <button id="refreshDataBtn" class="btn btn-outline" style="justify-content: center;">
            <i class="fa fa-sync-alt"></i>
            Refresh Data
          </button>
          <button id="bulkAssignBtn" class="btn btn-outline" style="justify-content: center;">
            <i class="fa fa-users-cog"></i>
            Bulk Assign
          </button>
          <button id="analyticsBtn" class="btn btn-outline" style="justify-content: center;">
            <i class="fa fa-chart-bar"></i>
            Analytics
          </button>
        </div>
        
        <!-- Live Activity Feed -->
        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(0,0,0,0.1);">
          <h4 style="margin-bottom: 1rem; color: #374151; font-size: 1rem;">
            <i class="fa fa-broadcast-tower"></i>
            Live Activity Feed
          </h4>
          <div id="activityFeed" style="max-height: 200px; overflow-y: auto; background: rgba(255,255,255,0.8); border-radius: 8px; padding: 1rem;">
            <div class="activity-item" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; padding: 0.5rem; background: rgba(37,99,235,0.1); border-radius: 4px; animation: slideIn 0.5s ease;">
              <i class="fa fa-user-plus" style="color: #2563eb;"></i>
              <span style="font-size: 0.9rem;">New user registered: John Doe</span>
              <span style="font-size: 0.8rem; color: #6b7280;">2 min ago</span>
            </div>
            <div class="activity-item" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; padding: 0.5rem; background: rgba(34,197,94,0.1); border-radius: 4px; animation: slideIn 0.5s ease 0.1s both;">
              <i class="fa fa-user-check" style="color: #22c55e;"></i>
              <span style="font-size: 0.9rem;">User status updated: Active</span>
              <span style="font-size: 0.8rem; color: #6b7280;">5 min ago</span>
            </div>
            <div class="activity-item" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; padding: 0.5rem; background: rgba(245,158,11,0.1); border-radius: 4px; animation: slideIn 0.5s ease 0.2s both;">
              <i class="fa fa-bell" style="color: #f59e0b;"></i>
              <span style="font-size: 0.9rem;">Notification sent to all users</span>
              <span style="font-size: 0.8rem; color: #6b7280;">8 min ago</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Search and Add User -->
      <div class="card search-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
          <h3 style="margin: 0; color: #2563eb; font-size: 1.2rem;">
            <i class="fa fa-search"></i>
            Search & Filter Users
          </h3>
          <div style="display: flex; gap: 0.5rem;">
            <button id="exportUsersBtn" class="btn btn-outline" title="Export Users">
              <i class="fa fa-download"></i>
              Export
            </button>
            <button id="addUserBtn" class="btn btn-primary">
              <i class="fa fa-user-plus"></i>
              Add New User
            </button>
          </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr auto auto; gap: 1rem; align-items: end;">
          <input type="text" id="searchInput" class="search-input" placeholder="Search by username, email, or role...">
          <select id="roleFilter" class="form-select" style="width: auto;">
            <option value="all">All Roles</option>
            <option value="admin">Administrators</option>
            <option value="technician">Technicians</option>
            <option value="user">Users</option>
          </select>
          <button id="clearFiltersBtn" class="btn btn-outline" style="width: auto;">
            <i class="fa fa-times"></i>
            Clear
          </button>
        </div>
        
        <!-- Advanced Search Panel -->
        <div id="advancedSearchPanel" style="display: none; margin-top: 1rem; padding: 1rem; background: rgba(255,255,255,0.8); border-radius: 8px;">
          <h4 style="margin-bottom: 1rem; color: #374151; font-size: 1rem;">
            <i class="fa fa-search-plus"></i>
            Advanced Search
          </h4>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div>
              <label class="form-label">Date Range</label>
              <select id="dateFilter" class="form-select">
                <option value="all">All Time</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
                <option value="custom">Custom Range</option>
              </select>
            </div>
            <div>
              <label class="form-label">Status</label>
              <select id="statusFilter" class="form-select">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="recent">Recently Active</option>
              </select>
            </div>
            <div>
              <label class="form-label">Sort By</label>
              <select id="sortBy" class="form-select">
                <option value="username">Username</option>
                <option value="role">Role</option>
                <option value="email">Email</option>
                <option value="created">Created Date</option>
                <option value="lastLogin">Last Login</option>
              </select>
            </div>
            <div>
              <label class="form-label">Results Per Page</label>
              <select id="perPage" class="form-select">
                <option value="10">10</option>
                <option value="25" selected>25</option>
                <option value="50">50</option>
                <option value="100">100</option>
              </select>
            </div>
          </div>
          
          <!-- Saved Search Presets -->
          <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
            <h5 style="margin-bottom: 0.5rem; color: #374151; font-size: 0.9rem;">Saved Searches</h5>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
              <button class="btn btn-outline btn-sm" onclick="loadSearchPreset('admins')">
                <i class="fa fa-user-shield"></i> Admins Only
              </button>
              <button class="btn btn-outline btn-sm" onclick="loadSearchPreset('recent')">
                <i class="fa fa-clock"></i> Recently Active
              </button>
              <button class="btn btn-outline btn-sm" onclick="loadSearchPreset('inactive')">
                <i class="fa fa-user-slash"></i> Inactive Users
              </button>
              <button class="btn btn-outline btn-sm" onclick="loadSearchPreset('new')">
                <i class="fa fa-user-plus"></i> New This Month
              </button>
            </div>
          </div>
        </div>
        
        <!-- Toggle Advanced Search -->
        <div style="margin-top: 1rem; text-align: center;">
          <button id="toggleAdvancedSearch" class="btn btn-outline btn-sm">
            <i class="fa fa-search-plus"></i>
            Advanced Search
          </button>
        </div>
        
        <!-- Bulk Actions -->
        <div id="bulkActions" style="display: none; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
          <div style="display: flex; align-items: center; gap: 1rem;">
            <span id="selectedCount" style="font-weight: 500; color: #2563eb;"></span>
            <button id="bulkDeleteBtn" class="btn btn-danger btn-sm">
              <i class="fa fa-trash"></i>
              Delete Selected
            </button>
            <button id="bulkExportBtn" class="btn btn-outline btn-sm">
              <i class="fa fa-download"></i>
              Export Selected
            </button>
          </div>
        </div>
      </div>

      <!-- Users Table -->
      <div class="card table-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
          <div>
            <h2 class="h2">
              <i class="fa fa-users"></i>
              User Management
            </h2>
            <p class="small">Manage system users, roles, and permissions.</p>
          </div>
          <div style="display: flex; gap: 0.5rem; align-items: center;">
            <span id="tableInfo" style="font-size: 0.9rem; color: #6b7280;">
              Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> users
            </span>
          </div>
        </div>
        
        <div id="userMsg"></div>
        
        <table class="styled-table">
          <thead>
            <tr>
              <th><input type="checkbox" id="selectAllCheckbox"></th>
              <th>ID</th>
              <th>Username</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Role</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="userTableBody">
            <tr>
              <td colspan="8" style="text-align: center; padding: 2rem;">
                <div class="loading"></div>
                <div style="margin-top: 0.5rem; color: #6b7280;">Loading users...</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- User Activity Dashboard -->
      <div class="card" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-left: 6px solid #f59e0b;">
        <h3 style="margin-bottom: 1rem; color: #f59e0b; font-size: 1.2rem;">
          <i class="fa fa-chart-line"></i>
          User Activity Dashboard
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
          <div style="background: rgba(255,255,255,0.8); padding: 1rem; border-radius: 8px; text-align: center;">
            <div style="font-size: 2rem; color: #f59e0b; margin-bottom: 0.5rem;">
              <i class="fa fa-clock"></i>
            </div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #1f2937;" id="activeToday">0</div>
            <div style="font-size: 0.9rem; color: #6b7280;">Active Today</div>
          </div>
          <div style="background: rgba(255,255,255,0.8); padding: 1rem; border-radius: 8px; text-align: center;">
            <div style="font-size: 2rem; color: #10b981; margin-bottom: 0.5rem;">
              <i class="fa fa-user-check"></i>
            </div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #1f2937;" id="recentLogins">0</div>
            <div style="font-size: 0.9rem; color: #6b7280;">Recent Logins (7d)</div>
          </div>
          <div style="background: rgba(255,255,255,0.8); padding: 1rem; border-radius: 8px; text-align: center;">
            <div style="font-size: 2rem; color: #3b82f6; margin-bottom: 0.5rem;">
              <i class="fa fa-chart-pie"></i>
            </div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #1f2937;" id="avgSessionTime">0m</div>
            <div style="font-size: 0.9rem; color: #6b7280;">Avg Session Time</div>
          </div>
          <div style="background: rgba(255,255,255,0.8); padding: 1rem; border-radius: 8px; text-align: center;">
            <div style="font-size: 2rem; color: #8b5cf6; margin-bottom: 0.5rem;">
              <i class="fa fa-trending-up"></i>
            </div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #1f2937;" id="growthRate">+0%</div>
            <div style="font-size: 0.9rem; color: #6b7280;">User Growth (30d)</div>
          </div>
        </div>
        
        <!-- Activity Chart -->
        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(0,0,0,0.1);">
          <h4 style="margin-bottom: 1rem; color: #374151; font-size: 1.1rem;">
            <i class="fa fa-calendar"></i>
            User Activity (Last 7 Days)
          </h4>
          <div style="height: 200px; background: rgba(255,255,255,0.8); border-radius: 8px; padding: 1rem; position: relative;">
            <canvas id="activityChart" style="width: 100%; height: 100%;"></canvas>
          </div>
        </div>
      </div>



      <!-- Floating Action Button -->
      <div id="fab" style="position: fixed; bottom: 30px; right: 30px; z-index: 1000;">
        <button onclick="showQuickActions()" style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none; color: white; font-size: 1.5rem; cursor: pointer; box-shadow: 0 8px 25px rgba(37,99,235,0.3); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); animation: pulse 2s infinite;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
          <i class="fa fa-plus"></i>
        </button>
      </div>

      <div class="footer mt-md small text-center" style="color: var(--color-secondary);">
        &copy; 2024 OutageSys. All rights reserved.
      </div>
    </div>
  </div>

  <!-- User Modal -->
  <div class="modal-bg" id="userModalBg">
    <div class="modal" id="userModal" style="max-width: 600px;">
      <div class="modal-header">
        <h3 class="modal-title" id="modalTitle">Add New User</h3>
        <button class="modal-close" id="modalClose">&times;</button>
      </div>
      
      <div id="modalAlert" style="display: none;"></div>
      
      <form id="userForm">
        <input type="hidden" id="userId" />
        

        
        <!-- Basic Information -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
          <div class="form-group">
            <label class="form-label">Username</label>
            <input type="text" id="userUsername" class="form-input" required />
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" id="userEmail" class="form-input" required />
          </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
          <div class="form-group">
            <label class="form-label">Phone Number</label>
            <input type="text" id="userPhone" class="form-input" />
          </div>
          <div class="form-group">
            <label class="form-label">Role</label>
            <select id="userRole" class="form-select">
              <option value="admin">Administrator</option>
              <option value="technician">Technician</option>
              <option value="user">User</option>
            </select>
          </div>
        </div>
        
        <!-- Detailed Profile Information -->
        <div style="margin-bottom: 1rem; padding: 1rem; background: #f9fafb; border-radius: 8px;">
          <h4 style="margin-bottom: 1rem; color: #374151; font-size: 1rem;">
            <i class="fa fa-user-plus"></i>
            Detailed Profile
          </h4>
          
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group">
              <label class="form-label">First Name</label>
              <input type="text" id="userFirstName" class="form-input" />
            </div>
            <div class="form-group">
              <label class="form-label">Last Name</label>
              <input type="text" id="userLastName" class="form-input" />
            </div>
          </div>
          
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group">
              <label class="form-label">Department</label>
              <select id="userDepartment" class="form-select">
                <option value="">Select Department</option>
                <?php foreach ($departments as $dept): ?>
                  <option value="<?php echo htmlspecialchars($dept['name']); ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Position</label>
              <input type="text" id="userPosition" class="form-input" />
            </div>
          </div>
          
          <div class="form-group">
            <label class="form-label">Bio</label>
            <textarea id="userBio" class="form-input" rows="3" placeholder="Tell us about yourself..."></textarea>
          </div>
        </div>
        
        <!-- Security Section -->
        <div style="margin-bottom: 1rem; padding: 1rem; background: #fef2f2; border-radius: 8px;">
          <h4 style="margin-bottom: 1rem; color: #374151; font-size: 1rem;">
            <i class="fa fa-shield-alt"></i>
            Security Settings
          </h4>
          
          <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" id="userPassword" class="form-input" placeholder="Leave blank to keep current password" />
          </div>
          
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
              <label class="form-label">Two-Factor Auth</label>
              <select id="userTwoFactor" class="form-select">
                <option value="disabled">Disabled</option>
                <option value="enabled">Enabled</option>
                <option value="required">Required</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Account Status</label>
              <select id="userStatus" class="form-select">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
              </select>
            </div>
          </div>
        </div>
        
        <div class="modal-actions">
          <button type="button" class="btn btn-outline" id="userCancelBtn">Cancel</button>
          <button type="submit" class="btn btn-primary" id="userSaveBtn">
            <i class="fa fa-save"></i>
            Save User
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="js/dark-mode.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Global variables
    let allUsers = [];
    let filteredUsers = [];
    let activityChart;
    
    // Initialize
    fetchUsers();
    initializeActivityChart();
    initializeCoolFeatures();
    
    function initializeCoolFeatures() {
      // Add floating particles
      createFloatingParticles();
      
      // Initialize smart search
      initializeSmartSearch();
      
      // Add auto-refresh with cool effects
      setInterval(() => {
        updateActivityFeed();
        updateStatsWithAnimation();
      }, 30000);
      
      // Add keyboard shortcuts
      initializeKeyboardShortcuts();
      
      // Add voice commands (simulated)
      initializeVoiceCommands();
    }
    
    function createFloatingParticles() {
      const container = document.querySelector('.users-bg');
      for (let i = 0; i < 20; i++) {
        const particle = document.createElement('div');
        particle.style.cssText = `
          position: absolute;
          width: 4px;
          height: 4px;
          background: rgba(255,255,255,0.3);
          border-radius: 50%;
          pointer-events: none;
          animation: float ${Math.random() * 10 + 10}s linear infinite;
          left: ${Math.random() * 100}%;
          top: ${Math.random() * 100}%;
        `;
        container.appendChild(particle);
      }
    }
    
    function initializeSmartSearch() {
      const searchInput = document.getElementById('searchInput');
      let searchTimeout;
      
      searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
          applyFilters();
          addSearchEffect();
        }, 300);
      });
    }
    
    function addSearchEffect() {
      const searchInput = document.getElementById('searchInput');
      searchInput.style.animation = 'glow 0.5s ease-in-out';
      setTimeout(() => {
        searchInput.style.animation = '';
      }, 500);
    }
    
    function initializeKeyboardShortcuts() {
      document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K for search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
          e.preventDefault();
          document.getElementById('searchInput').focus();
        }
        
        // Ctrl/Cmd + N for new user
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
          e.preventDefault();
          openAddUser();
        }
        
        // Ctrl/Cmd + R for refresh
        if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
          e.preventDefault();
          fetchUsers();
          showNotification('Data refreshed! 🚀', 'success');
        }
      });
    }
    
    function initializeVoiceCommands() {
      // Simulated voice commands
      console.log('🎤 Voice commands ready! Say "refresh data" or "add user"');
    }
    
    function showQuickActions() {
      const actions = [
        { icon: 'fa-user-plus', label: 'Add User', action: () => openAddUser() },
        { icon: 'fa-download', label: 'Export Data', action: () => document.getElementById('exportUsersBtn').click() },
        { icon: 'fa-sync-alt', label: 'Refresh', action: () => fetchUsers() },
        { icon: 'fa-chart-bar', label: 'Analytics', action: () => showAnalyticsModal() }
      ];
      
      const modal = document.createElement('div');
      modal.style.cssText = `
        position: fixed;
        bottom: 100px;
        right: 30px;
        background: white;
        border-radius: 16px;
        padding: 1rem;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        z-index: 1001;
        animation: slideIn 0.3s ease;
        backdrop-filter: blur(10px);
      `;
      
      modal.innerHTML = `
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
          ${actions.map(action => `
            <button onclick="this.closest('.quick-actions').remove(); ${action.action.toString()}()" 
                    style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; background: none; border: none; border-radius: 8px; cursor: pointer; transition: all 0.2s; min-width: 150px;" 
                    onmouseover="this.style.background='#f3f4f6'" 
                    onmouseout="this.style.background='transparent'">
              <i class="fa ${action.icon}" style="color: #2563eb; width: 20px;"></i>
              <span style="color: #374151; font-weight: 500;">${action.label}</span>
            </button>
          `).join('')}
        </div>
      `;
      
      modal.className = 'quick-actions';
      document.body.appendChild(modal);
      
      // Close on outside click
      setTimeout(() => {
        document.addEventListener('click', function closeQuickActions(e) {
          if (!modal.contains(e.target) && e.target.closest('#fab') === null) {
            modal.remove();
            document.removeEventListener('click', closeQuickActions);
          }
        });
      }, 100);
    }
    
    function initializeActivityChart() {
      const ctx = document.getElementById('activityChart').getContext('2d');
      activityChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
          datasets: [{
            label: 'Active Users',
            data: [12, 19, 15, 25, 22, 18, 24],
            borderColor: '#f59e0b',
            backgroundColor: 'rgba(245, 158, 11, 0.1)',
            tension: 0.4,
            fill: true
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              grid: {
                color: 'rgba(0,0,0,0.1)'
              }
            },
            x: {
              grid: {
                display: false
              }
            }
          }
        }
      });
    }
    
    function updateActivityMetrics() {
      // Simulate activity data (in real app, this would come from database)
      const activeToday = Math.floor(Math.random() * 20) + 10;
      const recentLogins = Math.floor(Math.random() * 50) + 30;
      const avgSessionTime = Math.floor(Math.random() * 45) + 15;
      const growthRate = Math.floor(Math.random() * 15) + 5;
      
      // Animate the numbers
      animateNumber('activeToday', activeToday);
      animateNumber('recentLogins', recentLogins);
      animateNumber('avgSessionTime', avgSessionTime + 'm');
      animateNumber('growthRate', '+' + growthRate + '%');
      
      // Update chart with new data
      const newData = Array.from({length: 7}, () => Math.floor(Math.random() * 30) + 10);
      activityChart.data.datasets[0].data = newData;
      activityChart.update();
    }
    
    function animateNumber(elementId, targetValue) {
      const element = document.getElementById(elementId);
      const currentValue = parseInt(element.textContent) || 0;
      const increment = (targetValue - currentValue) / 20;
      let current = currentValue;
      
      const animation = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= targetValue) || (increment < 0 && current <= targetValue)) {
          element.textContent = targetValue;
          clearInterval(animation);
        } else {
          element.textContent = Math.floor(current);
        }
      }, 50);
    }
    
    function updateActivityFeed() {
      const activities = [
        { icon: 'fa-user-plus', text: 'New user registered', color: '#2563eb', time: '1 min ago' },
        { icon: 'fa-user-check', text: 'User status updated', color: '#22c55e', time: '3 min ago' },
        { icon: 'fa-bell', text: 'Notification sent', color: '#f59e0b', time: '5 min ago' },
        { icon: 'fa-edit', text: 'User profile updated', color: '#8b5cf6', time: '7 min ago' },
        { icon: 'fa-download', text: 'Report generated', color: '#06b6d4', time: '10 min ago' }
      ];
      
      const randomActivity = activities[Math.floor(Math.random() * activities.length)];
      const feed = document.getElementById('activityFeed');
      
      const newItem = document.createElement('div');
      newItem.className = 'activity-item';
      newItem.style.cssText = `
        display: flex; 
        align-items: center; 
        gap: 0.5rem; 
        margin-bottom: 0.5rem; 
        padding: 0.5rem; 
        background: rgba(${randomActivity.color},0.1); 
        border-radius: 4px; 
        animation: slideIn 0.5s ease;
        transform: translateX(-20px);
        opacity: 0;
      `;
      
      newItem.innerHTML = `
        <i class="fa ${randomActivity.icon}" style="color: ${randomActivity.color};"></i>
        <span style="font-size: 0.9rem;">${randomActivity.text}</span>
        <span style="font-size: 0.8rem; color: #6b7280;">${randomActivity.time}</span>
      `;
      
      feed.insertBefore(newItem, feed.firstChild);
      
      // Animate in
      setTimeout(() => {
        newItem.style.transform = 'translateX(0)';
        newItem.style.opacity = '1';
      }, 100);
      
      // Remove old items if too many
      const items = feed.querySelectorAll('.activity-item');
      if (items.length > 5) {
        items[items.length - 1].remove();
      }
    }
    
    function updateStatsWithAnimation() {
      const statCards = document.querySelectorAll('.stat-card');
      statCards.forEach((card, index) => {
        setTimeout(() => {
          card.style.animation = 'pulse 0.5s ease-in-out';
          setTimeout(() => {
            card.style.animation = '';
          }, 500);
        }, index * 100);
      });
    }
    
    function fetchUsers(query = '', roleFilter = 'all') {
      let url = 'api/users.php?action=get_users';
      if (query) {
        url += '&q=' + encodeURIComponent(query);
      }
      if (roleFilter !== 'all') {
        url += '&role=' + encodeURIComponent(roleFilter);
      }
      
      console.log('Fetching users from:', url);
      
      fetch(url, { credentials: 'same-origin' })
        .then(res => {
          console.log('Response status:', res.status);
          if (!res.ok) {
            throw new Error(`HTTP ${res.status}: ${res.statusText}`);
          }
          return res.json();
        })
        .then(data => {
          console.log('Users loaded:', data);
          
          // Handle the API response format: {success: true, users: [...]}
          let users = [];
          if (data && data.success && Array.isArray(data.users)) {
            users = data.users;
            console.log('Number of users:', users.length);
          } else if (Array.isArray(data)) {
            // Fallback for direct array response
            users = data;
            console.log('Number of users:', users.length);
          } else {
            console.error('Invalid data format received:', data);
            showNotification('Error: Invalid data format received', 'error');
            return;
          }
          
          allUsers = users;
          filteredUsers = users;
          
          updateUserTable();
          updateStats();
          updateTableInfo();
        })
        .catch(error => {
          console.error('Error loading users:', error);
          showNotification('Error loading users: ' + error.message, 'error');
          
          // Show error in table
          const tbody = document.getElementById('userTableBody');
          tbody.innerHTML = `
            <tr>
              <td colspan="7" style="text-align: center; padding: 2rem; color: #ef4444;">
                <i class="fa fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                Error loading users. Please check the console for details.
              </td>
            </tr>
          `;
        });
    }
    
    function updateUserTable() {
      const tbody = document.getElementById('userTableBody');
      tbody.innerHTML = '';
      
      if (!filteredUsers.length) {
        tbody.innerHTML = `
          <tr>
            <td colspan="7" style="text-align: center; padding: 2rem; color: #6b7280;">
              <i class="fa fa-search" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
              No users found matching your search criteria.
            </td>
          </tr>
        `;
        return;
      }
      
      filteredUsers.forEach(user => {
        const row = `
          <tr>
            <td><input type="checkbox" class="select-checkbox" value="${user.id}"></td>
            <td>${user.id}</td>
            <td><strong>${user.username}</strong></td>
            <td>${user.email || '-'}</td>
            <td>${user.phone || '-'}</td>
            <td><span class="role-badge role-${user.role}">${user.role}</span></td>
            <td><span class="status-badge status-active">Active</span></td>
            <td>
              <div style="display: flex; gap: 0.5rem;">
                <button class="btn btn-outline btn-sm" onclick="openEditUser(${JSON.stringify(user).replace(/"/g, '&quot;')})">
                  <i class="fa fa-edit"></i> Edit
                </button>
                <button class="btn btn-danger btn-sm" onclick="deleteUser(${user.id})">
                  <i class="fa fa-trash"></i> Delete
                </button>
              </div>
            </td>
          </tr>
        `;
        tbody.innerHTML += row;
      });
      
      // Re-attach event listeners to new checkboxes
      document.querySelectorAll('.select-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
      });
    }
    
    function updateStats() {
      const totalUsers = allUsers.length;
      const adminUsers = allUsers.filter(u => u.role === 'admin').length;
      const technicianUsers = allUsers.filter(u => u.role === 'technician').length;
      const activeUsers = allUsers.length; // All users are considered active
      
      document.getElementById('totalUsers').textContent = totalUsers;
      document.getElementById('adminUsers').textContent = adminUsers;
      document.getElementById('technicianUsers').textContent = technicianUsers;
      document.getElementById('activeUsers').textContent = activeUsers;
    }
    
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
        background: ${type === 'success' ? '#22c55e' : type === 'error' ? '#ef4444' : type === 'info' ? '#3b82f6' : '#f59e0b'};
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
      `;
      
      notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 0.75rem;">
          <span style="font-size: 1.2rem;">${icons[type] || icons.info}</span>
          <span style="flex: 1;">${message}</span>
          <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem; opacity: 0.7; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">×</button>
        </div>
      `;
      
      document.body.appendChild(notification);
      
      // Animate in with bounce effect
      setTimeout(() => {
        notification.style.transform = 'translateX(0) scale(1)';
      }, 100);
      
      // Auto remove after 4 seconds
      setTimeout(() => {
        notification.style.transform = 'translateX(100%) scale(0.8)';
        setTimeout(() => {
          if (document.body.contains(notification)) {
            document.body.removeChild(notification);
          }
        }, 400);
      }, 4000);
    }
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const roleFilterSelect = document.getElementById('roleFilter');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');

    // Export functionality
    document.getElementById('exportUsersBtn').addEventListener('click', function() {
      const query = searchInput.value;
      const role = roleFilterSelect.value;
      let url = 'api/users.php?export=1';
      
      if (query) {
        url += '&q=' + encodeURIComponent(query);
      }
      if (role !== 'all') {
        url += '&role=' + encodeURIComponent(role);
      }
      
      window.location.href = url;
      showNotification('Exporting users...');
    });
    
    // Update table info
    function updateTableInfo() {
      document.getElementById('showingCount').textContent = filteredUsers.length;
      document.getElementById('totalCount').textContent = allUsers.length;
    }
    
    // Quick Actions functionality
    document.getElementById('generateReportBtn').addEventListener('click', function() {
      showNotification('Generating user report...', 'info');
      setTimeout(() => {
        const reportData = {
          totalUsers: allUsers.length,
          adminUsers: allUsers.filter(u => u.role === 'admin').length,
          technicianUsers: allUsers.filter(u => u.role === 'technician').length,
          userUsers: allUsers.filter(u => u.role === 'user').length,
          generatedAt: new Date().toLocaleString()
        };
        
        const reportText = `User Management Report\n\n` +
          `Total Users: ${reportData.totalUsers}\n` +
          `Administrators: ${reportData.adminUsers}\n` +
          `Technicians: ${reportData.technicianUsers}\n` +
          `Users: ${reportData.userUsers}\n` +
          `Generated: ${reportData.generatedAt}`;
        
        const blob = new Blob([reportText], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'user_report_' + new Date().toISOString().split('T')[0] + '.txt';
        a.click();
        URL.revokeObjectURL(url);
        
        showNotification('Report generated and downloaded!');
      }, 1000);
    });
    
    document.getElementById('backupUsersBtn').addEventListener('click', function() {
      showNotification('Creating user backup...', 'info');
      setTimeout(() => {
        const backupData = JSON.stringify(allUsers, null, 2);
        const blob = new Blob([backupData], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'users_backup_' + new Date().toISOString().split('T')[0] + '.json';
        a.click();
        URL.revokeObjectURL(url);
        
        showNotification('User backup created and downloaded!');
      }, 1000);
    });
    
    document.getElementById('sendNotificationBtn').addEventListener('click', function() {
      const message = prompt('Enter notification message to send to all users:');
      if (message) {
        showNotification('Sending notification to all users...', 'info');
        setTimeout(() => {
          showNotification(`Notification sent: "${message}"`);
        }, 1500);
      }
    });
    
    document.getElementById('refreshDataBtn').addEventListener('click', function() {
      const btn = this;
      const originalText = btn.innerHTML;
      btn.innerHTML = '<div class="loading"></div> Refreshing...';
      btn.disabled = true;
      
      fetchUsers();
      
      setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        showNotification('Data refreshed successfully!');
      }, 1000);
    });
    
    // Advanced Search functionality
    document.getElementById('toggleAdvancedSearch').addEventListener('click', function() {
      const panel = document.getElementById('advancedSearchPanel');
      const isVisible = panel.style.display !== 'none';
      panel.style.display = isVisible ? 'none' : 'block';
      this.innerHTML = isVisible ? 
        '<i class="fa fa-search-plus"></i> Advanced Search' : 
        '<i class="fa fa-search-minus"></i> Hide Advanced Search';
    });
    
    // Search presets
    function loadSearchPreset(preset) {
      switch(preset) {
        case 'admins':
          roleFilterSelect.value = 'admin';
          break;
        case 'recent':
          document.getElementById('statusFilter').value = 'recent';
          break;
        case 'inactive':
          document.getElementById('statusFilter').value = 'inactive';
          break;
        case 'new':
          document.getElementById('dateFilter').value = 'month';
          break;
      }
      applyFilters();
      showNotification(`Applied "${preset}" search preset`);
    }
    
    // Enhanced applyFilters function
    function applyFilters() {
      const query = searchInput.value.toLowerCase();
      const selectedRole = roleFilterSelect.value;
      const dateFilter = document.getElementById('dateFilter').value;
      const statusFilter = document.getElementById('statusFilter').value;
      const sortBy = document.getElementById('sortBy').value;
      
      filteredUsers = allUsers.filter(user => {
        const matchesSearch = !query || 
          user.username.toLowerCase().includes(query) ||
          (user.email && user.email.toLowerCase().includes(query)) ||
          user.role.toLowerCase().includes(query);
        
        const matchesRole = selectedRole === 'all' || user.role === selectedRole;
        
        // Simulate date and status filtering
        const matchesDate = dateFilter === 'all' || true; // In real app, check actual dates
        const matchesStatus = statusFilter === 'all' || true; // In real app, check actual status
        
        return matchesSearch && matchesRole && matchesDate && matchesStatus;
      });
      
      // Sort users
      filteredUsers.sort((a, b) => {
        switch(sortBy) {
          case 'username':
            return a.username.localeCompare(b.username);
          case 'role':
            return a.role.localeCompare(b.role);
          case 'email':
            return (a.email || '').localeCompare(b.email || '');
          default:
            return 0;
        }
      });
      
      updateUserTable();
      updateStats();
      updateTableInfo();
    }
    

    
    
    
    // Update activity metrics periodically
    setInterval(updateActivityMetrics, 30000); // Update every 30 seconds
    
    // Initialize activity metrics
    updateActivityMetrics();
    
    // Bulk actions functionality
    function updateBulkActions() {
      const selectedCheckboxes = document.querySelectorAll('.select-checkbox:checked');
      const selectedCount = selectedCheckboxes.length;
      const bulkActions = document.getElementById('bulkActions');
      
      if (selectedCount > 0) {
        selectedCountSpan.textContent = `${selectedCount} user(s) selected`;
        bulkActions.style.display = 'block';
      } else {
        bulkActions.style.display = 'none';
      }
    }
    
    // Update table to include checkboxes
    function updateUserTable() {
      const tbody = document.getElementById('userTableBody');
      tbody.innerHTML = '';
      
      if (!filteredUsers.length) {
        tbody.innerHTML = `
          <tr>
            <td colspan="7" style="text-align: center; padding: 2rem; color: #6b7280;">
              <i class="fa fa-search" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
              No users found matching your search criteria.
            </td>
          </tr>
        `;
        return;
      }
      
      filteredUsers.forEach(user => {
        const row = `
          <tr>
            <td><input type="checkbox" class="select-checkbox" value="${user.id}"></td>
            <td>${user.id}</td>
            <td><strong>${user.username}</strong></td>
            <td>${user.email || '-'}</td>
            <td>${user.phone || '-'}</td>
            <td><span class="role-badge role-${user.role}">${user.role}</span></td>
            <td><span class="status-badge status-active">Active</span></td>
            <td>
              <div style="display: flex; gap: 0.5rem;">
                <button class="btn btn-outline btn-sm" onclick="openEditUser(${JSON.stringify(user).replace(/"/g, '&quot;')})">
                  <i class="fa fa-edit"></i> Edit
                </button>
                <button class="btn btn-danger btn-sm" onclick="deleteUser(${user.id})">
                  <i class="fa fa-trash"></i> Delete
                </button>
              </div>
            </td>
          </tr>
        `;
        tbody.innerHTML += row;
      });
      
      // Re-attach event listeners to new checkboxes
      document.querySelectorAll('.select-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
      });
    }
    
    // Select all functionality
    selectAllCheckbox.addEventListener('change', function() {
      const checkboxes = document.querySelectorAll('.select-checkbox');
      checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
      });
      updateBulkActions();
    });
    
    // Bulk delete functionality
    bulkDeleteBtn.addEventListener('click', function() {
      const selectedCheckboxes = document.querySelectorAll('.select-checkbox:checked');
      const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
      
      if (selectedIds.length === 0) {
        showNotification('No users selected for deletion.', 'info');
        return;
      }
      
      if (!confirm(`Are you sure you want to delete ${selectedIds.length} selected user(s)? This action cannot be undone.`)) {
        return;
      }
      
      fetch('api/users.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ ids: selectedIds })
      })
      .then(res => res.json())
      .then(resp => {
        if (resp.success) {
          showNotification(`${resp.deleted_count || selectedIds.length} user(s) deleted successfully!`);
          fetchUsers();
        } else {
          showNotification(resp.error || 'Bulk delete failed.', 'error');
        }
      })
      .catch(error => {
        console.error('Error bulk deleting users:', error);
        showNotification('Network error. Please try again.', 'error');
      });
    });
    
    // Bulk export functionality
    bulkExportBtn.addEventListener('click', function() {
      const selectedCheckboxes = document.querySelectorAll('.select-checkbox:checked');
      const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
      
      if (selectedIds.length === 0) {
        showNotification('No users selected for export.', 'info');
        return;
      }
      
      const url = 'api/users.php?export=1&ids=' + selectedIds.join(',');
      window.location.href = url;
      showNotification('Exporting selected users...');
    });
    
    // Enhanced bulk assign functionality
    document.getElementById('bulkAssignBtn').addEventListener('click', function() {
      const selectedCheckboxes = document.querySelectorAll('.select-checkbox:checked');
      const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
      
      if (selectedIds.length === 0) {
        showNotification('No users selected for bulk assignment.', 'info');
        return;
      }
      
      // Show cool bulk assign modal
      showBulkAssignModal(selectedIds);
    });
    
    // Analytics functionality
    document.getElementById('analyticsBtn').addEventListener('click', function() {
      showAnalyticsModal();
    });
    
    function showBulkAssignModal(userIds) {
      const modal = document.createElement('div');
      modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0,0,0,0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2000;
        backdrop-filter: blur(10px);
      `;
      
      modal.innerHTML = `
        <div style="background: white; border-radius: 16px; padding: 2rem; max-width: 500px; width: 90%; position: relative; animation: slideIn 0.3s ease;">
          <h3 style="margin-bottom: 1rem; color: #2563eb;">
            <i class="fa fa-users-cog"></i>
            Bulk Assign Users (${userIds.length})
          </h3>
          <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Assign to Department:</label>
            <select id="bulkDepartment" style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px;">
              <option value="">Select Department</option>
              <option value="IT & Technology">IT & Technology</option>
              <option value="Operations">Operations</option>
              <option value="Engineering">Engineering</option>
              <option value="Customer Service">Customer Service</option>
              <option value="Management">Management</option>
            </select>
          </div>
          <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Assign Role:</label>
            <select id="bulkRole" style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px;">
              <option value="">Keep Current Role</option>
              <option value="user">User</option>
              <option value="technician">Technician</option>
              <option value="admin">Administrator</option>
            </select>
          </div>
          <div style="display: flex; gap: 1rem; justify-content: flex-end;">
            <button onclick="this.closest('.modal-bg').remove()" class="btn btn-outline">Cancel</button>
            <button onclick="executeBulkAssign(${JSON.stringify(userIds)})" class="btn btn-primary">
              <i class="fa fa-magic"></i>
              Apply Changes
            </button>
          </div>
        </div>
      `;
      
      document.body.appendChild(modal);
      
      // Close on outside click
      modal.addEventListener('click', function(e) {
        if (e.target === modal) {
          modal.remove();
        }
      });
    }
    
    function executeBulkAssign(userIds) {
      const department = document.getElementById('bulkDepartment').value;
      const role = document.getElementById('bulkRole').value;
      
      if (!department && !role) {
        showNotification('Please select at least one option to assign.', 'error');
        return;
      }
      
      // Simulate bulk assignment with cool effects
      showNotification(`🔄 Processing ${userIds.length} users...`, 'info');
      
      setTimeout(() => {
        // Add success animation
        const checkmarks = userIds.map(() => '✅').join('');
        showNotification(`✨ Successfully assigned ${userIds.length} users! ${checkmarks}`, 'success');
        
        // Refresh data
        fetchUsers();
        
        // Close modal
        document.querySelector('.modal-bg').remove();
      }, 2000);
    }
    
    function showAnalyticsModal() {
      const modal = document.createElement('div');
      modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0,0,0,0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2000;
        backdrop-filter: blur(10px);
      `;
      
      modal.innerHTML = `
        <div style="background: white; border-radius: 16px; padding: 2rem; max-width: 800px; width: 90%; position: relative; animation: slideIn 0.3s ease;">
          <h3 style="margin-bottom: 1rem; color: #2563eb;">
            <i class="fa fa-chart-bar"></i>
            User Analytics Dashboard
          </h3>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 1rem; border-radius: 8px; text-align: center;">
              <div style="font-size: 2rem; color: #0ea5e9; margin-bottom: 0.5rem;">📈</div>
              <div style="font-size: 1.5rem; font-weight: 700;">${allUsers.length}</div>
              <div style="font-size: 0.9rem; color: #6b7280;">Total Users</div>
            </div>
            <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 1rem; border-radius: 8px; text-align: center;">
              <div style="font-size: 2rem; color: #f59e0b; margin-bottom: 0.5rem;">🚀</div>
              <div style="font-size: 1.5rem; font-weight: 700;">+12%</div>
              <div style="font-size: 0.9rem; color: #6b7280;">Growth Rate</div>
            </div>
            <div style="background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); padding: 1rem; border-radius: 8px; text-align: center;">
              <div style="font-size: 2rem; color: #22c55e; margin-bottom: 0.5rem;">⭐</div>
              <div style="font-size: 1.5rem; font-weight: 700;">98%</div>
              <div style="font-size: 0.9rem; color: #6b7280;">Satisfaction</div>
            </div>
          </div>
          <div style="text-align: center;">
            <button onclick="this.closest('.modal-bg').remove()" class="btn btn-primary">
              <i class="fa fa-download"></i>
              Export Analytics
            </button>
          </div>
        </div>
      `;
      
      document.body.appendChild(modal);
      
      // Close on outside click
      modal.addEventListener('click', function(e) {
        if (e.target === modal) {
          modal.remove();
        }
      });
    }
    
    // Modal functionality
    const modalBg = document.getElementById('userModalBg');
    const modalTitle = document.getElementById('modalTitle');
    const modalAlert = document.getElementById('modalAlert');
    const userForm = document.getElementById('userForm');
    
    function openModal(title) {
      modalTitle.textContent = title;
      modalAlert.style.display = 'none';
      modalBg.style.display = 'flex';
    }
    
    function closeModal() {
      modalBg.style.display = 'none';
      userForm.reset();
    }
    
    function openAddUser() {
      document.getElementById('userId').value = '';
      userForm.reset();
      openModal('Add New User');
    }
    
    function openEditUser(user) {
      document.getElementById('userId').value = user.id;
      document.getElementById('userUsername').value = user.username;
      document.getElementById('userEmail').value = user.email || '';
      document.getElementById('userPhone').value = user.phone || '';
      document.getElementById('userRole').value = user.role;
      document.getElementById('userPassword').value = ''; // Clear password for edit
      openModal('Edit User');
    }
    
    // Event listeners
    document.getElementById('addUserBtn').addEventListener('click', openAddUser);
    document.getElementById('modalClose').addEventListener('click', closeModal);
    document.getElementById('userCancelBtn').addEventListener('click', closeModal);
    
    // Close modal when clicking outside
    modalBg.addEventListener('click', function(e) {
      if (e.target === modalBg) {
        closeModal();
      }
    });
    
    // Enhanced form submission with profile data
    userForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const userId = document.getElementById('userId').value;
      const formData = new FormData();
      
      // Basic user data
      formData.append('username', document.getElementById('userUsername').value);
      formData.append('email', document.getElementById('userEmail').value);
      formData.append('role', document.getElementById('userRole').value);
      formData.append('phone', document.getElementById('userPhone').value);
      formData.append('status', document.getElementById('userStatus').value);
      formData.append('two_factor', document.getElementById('userTwoFactor').value);
      
      // Profile data
      formData.append('first_name', document.getElementById('userFirstName').value);
      formData.append('last_name', document.getElementById('userLastName').value);
      formData.append('department', document.getElementById('userDepartment').value);
      formData.append('position', document.getElementById('userPosition').value);
      formData.append('bio', document.getElementById('userBio').value);
      
      // Password (only if provided)
      const password = document.getElementById('userPassword').value;
      if (password) {
        formData.append('password', password);
      }
      

      
      try {
        const action = userId ? 'update_user' : 'create_user';
        formData.append('action', action);
        if (userId) {
          formData.append('user_id', userId);
        }
        
        const response = await fetch('api/users.php', {
          method: 'POST',
          body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
          showNotification(userId ? 'User updated successfully!' : 'User created successfully!', 'success');
          closeModal();
          loadUsers(); // Refresh the user list
        } else {
          showNotification(result.error || 'Operation failed', 'error');
        }
      } catch (error) {
        console.error('Error:', error);
        showNotification('Network error occurred', 'error');
      }
    });
    
    // Delete user
    function deleteUser(id) {
      if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        return;
      }
      
      fetch('api/users.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ id })
      })
      .then(res => res.json())
      .then(resp => {
        if (resp.success) {
          showNotification('User deleted successfully!');
          fetchUsers();
        } else {
          showNotification(resp.error || 'Delete failed.', 'error');
        }
      })
      .catch(error => {
        console.error('Error deleting user:', error);
        showNotification('Network error. Please try again.', 'error');
      });
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
      // Escape to close modal
      if (e.key === 'Escape' && modalBg.style.display === 'flex') {
        closeModal();
      }
      
      // Ctrl/Cmd + N to add new user
      if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        e.preventDefault();
        openAddUser();
      }
    });
    
    // User form functions
    function openAddUser() {
      document.getElementById('userId').value = '';
      userForm.reset();
      openModal('Add New User');
    }
    
    function openEditUser(user) {
      document.getElementById('userId').value = user.id;
      document.getElementById('userUsername').value = user.username;
      document.getElementById('userEmail').value = user.email || '';
      document.getElementById('userPhone').value = user.phone || '';
      document.getElementById('userRole').value = user.role;
      document.getElementById('userStatus').value = user.status || 'active';
      document.getElementById('userPassword').value = '';
      
      // Set profile fields
      document.getElementById('userFirstName').value = user.first_name || '';
      document.getElementById('userLastName').value = user.last_name || '';
      document.getElementById('userDepartment').value = user.department || '';
      document.getElementById('userPosition').value = user.position || '';
      document.getElementById('userBio').value = user.bio || '';
      document.getElementById('userTwoFactor').value = user.two_factor || 'disabled';
      
      openModal('Edit User');
    }
    
    // Load users with enhanced profile data
    async function loadUsers() {
      try {
        console.log('Loading users...');
        
        // Use the main API
        const response = await fetch('api/users.php?action=get_users');
        console.log('Response status:', response.status);
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log('API result:', result);
        
        if (result.success) {
          const userList = document.getElementById('userList');
          userList.innerHTML = '';
          
          console.log('Found users:', result.users.length);
          
          result.users.forEach(user => {
            const userCard = document.createElement('div');
            userCard.className = 'user-card';
            userCard.style.cssText = `
              background: #fff;
              border-radius: 12px;
              padding: 1.5rem;
              box-shadow: 0 2px 8px rgba(0,0,0,0.1);
              border-left: 4px solid ${getRoleColor(user.role)};
              transition: transform 0.2s, box-shadow 0.2s;
              cursor: pointer;
            `;
            
            // Avatar display
            const avatarSrc = user.avatar_url || avatarOptions[0].src;
            const displayName = user.first_name && user.last_name ? 
              `${user.first_name} ${user.last_name}` : user.username;
            
            userCard.innerHTML = `
              <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                <img src="${avatarSrc}" alt="Avatar" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid #e5e7eb;">
                <div style="flex: 1;">
                  <h4 style="margin: 0 0 0.25rem 0; color: #374151; font-size: 1.1rem;">${displayName}</h4>
                  <p style="margin: 0; color: #6b7280; font-size: 0.9rem;">@${user.username}</p>
                </div>
                <div style="text-align: right;">
                  <span class="badge badge-${getRoleBadge(user.role)}">${user.role}</span>
                  <span class="badge badge-${getStatusBadge(user.status)}">${user.status}</span>
                </div>
              </div>
              
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; font-size: 0.9rem;">
                <div>
                  <strong style="color: #374151;">Email:</strong>
                  <span style="color: #6b7280;">${user.email || 'N/A'}</span>
                </div>
                <div>
                  <strong style="color: #374151;">Phone:</strong>
                  <span style="color: #6b7280;">${user.phone || 'N/A'}</span>
                </div>
                <div>
                  <strong style="color: #374151;">Department:</strong>
                  <span style="color: #6b7280;">${user.department || 'N/A'}</span>
                </div>
                <div>
                  <strong style="color: #374151;">Position:</strong>
                  <span style="color: #6b7280;">${user.position || 'N/A'}</span>
                </div>
              </div>
              
              ${user.bio ? `<p style="color: #6b7280; font-size: 0.9rem; margin-bottom: 1rem; font-style: italic;">"${user.bio}"</p>` : ''}
              
              <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                <button class="btn btn-outline btn-sm" onclick="openEditUser(${JSON.stringify(user).replace(/"/g, '&quot;')})">
                  <i class="fa fa-edit"></i>
                  Edit
                </button>
                <button class="btn btn-outline btn-sm" onclick="viewUserProfile(${user.id})">
                  <i class="fa fa-user"></i>
                  Profile
                </button>
                <button class="btn btn-outline btn-sm" onclick="deleteUser(${user.id})">
                  <i class="fa fa-trash"></i>
                  Delete
                </button>
              </div>
            `;
            
            userCard.addEventListener('mouseenter', () => {
              userCard.style.transform = 'translateY(-2px)';
              userCard.style.boxShadow = '0 4px 16px rgba(0,0,0,0.15)';
            });
            
            userCard.addEventListener('mouseleave', () => {
              userCard.style.transform = 'translateY(0)';
              userCard.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
            });
            
            userList.appendChild(userCard);
          });
          
          updateUserStats(result.users);
          showNotification(`Successfully loaded ${result.users.length} users`, 'success');
        } else {
          showNotification('Failed to load users: ' + (result.error || 'Unknown error'), 'error');
        }
      } catch (error) {
        console.error('Error loading users:', error);
        showNotification('Network error occurred. Please check your connection. Error: ' + error.message, 'error');
      }
    }
    
    // View user profile
    function viewUserProfile(userId) {
      // For MVP, show a simplified profile view
      showNotification('Profile view feature coming soon!', 'info');
    }
    
    // Helper functions for badges and colors
    function getRoleColor(role) {
      const colors = {
        admin: '#dc2626',
        technician: '#2563eb',
        user: '#059669'
      };
      return colors[role] || '#6b7280';
    }
    
    function getRoleBadge(role) {
      const badges = {
        admin: 'danger',
        technician: 'primary',
        user: 'success'
      };
      return badges[role] || 'secondary';
    }
    
    function getStatusBadge(status) {
      const badges = {
        active: 'success',
        inactive: 'warning',
        suspended: 'danger'
      };
      return badges[status] || 'secondary';
    }
  </script>
</body>
</html> 