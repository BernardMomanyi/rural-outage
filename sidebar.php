<?php
if (!isset($role)) {
  $role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';
}
$current = basename($_SERVER['PHP_SELF']);
?>
<aside class="card sidebar" style="min-width:180px; max-width:220px; margin:var(--space-md) 0;">
  <div class="sidebar-header">
    <h2 class="h4" style="margin:0;">OutageSys</h2>
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle navigation">
      <span class="bar"></span>
      <span class="bar"></span>
      <span class="bar"></span>
    </button>
  </div>
  <nav class="sidebar-nav" id="sidebarNav">
    <ul style="list-style:none; padding:0; margin:0;">
      <?php if ($role === 'admin'): ?>
        <li class="mb-sm"><a href="admin_dashboard.php" class="btn btn-outline w-100<?php echo ($current === 'admin_dashboard.php' ? ' active' : ''); ?>"><i class="fa fa-user-shield"></i> Admin Dashboard</a></li>
        <li class="mb-sm"><a href="admin_tickets.php" class="btn btn-outline w-100<?php echo ($current === 'admin_tickets.php' ? ' active' : ''); ?>"><i class="fa fa-ticket-alt"></i> Manage Tickets</a></li>
        <li class="mb-sm"><a href="communication_center.php" class="btn btn-outline w-100<?php echo ($current === 'communication_center.php' ? ' active' : ''); ?>"><i class="fa fa-comments"></i> Communication Center</a></li>
        <li class="mb-sm"><a href="admin_technicians.php" class="btn btn-outline w-100<?php echo ($current === 'admin_technicians.php' ? ' active' : ''); ?>"><i class="fa fa-users-cog"></i> Technicians</a></li>
        <li class="mb-sm"><a href="substations.php" class="btn btn-outline w-100<?php echo ($current === 'substations.php' ? ' active' : ''); ?>"><i class="fa fa-bolt"></i> Substations</a></li>
        <li class="mb-sm"><a href="reports.php" class="btn btn-outline w-100<?php echo ($current === 'reports.php' ? ' active' : ''); ?>"><i class="fa fa-file-alt"></i> Reports</a></li>
        <li class="mb-sm"><a href="analytics.php" class="btn btn-outline w-100<?php echo ($current === 'analytics.php' ? ' active' : ''); ?>"><i class="fa fa-chart-bar"></i> Analytics</a></li>
        <li class="mb-sm"><a href="map.php" class="btn btn-outline w-100<?php echo ($current === 'map.php' ? ' active' : ''); ?>"><i class="fa fa-map"></i> Map View</a></li>
        <li class="mb-sm"><a href="users.php" class="btn btn-outline w-100<?php echo ($current === 'users.php' ? ' active' : ''); ?>"><i class="fa fa-users"></i> Users</a></li>
        <li class="mb-sm"><a href="settings.php" class="btn btn-outline w-100<?php echo ($current === 'settings.php' ? ' active' : ''); ?>"><i class="fa fa-cogs"></i> Settings</a></li>
      <?php elseif ($role === 'technician'): ?>
        <li class="mb-sm"><a href="technician_dashboard.php" class="btn btn-outline w-100<?php echo ($current === 'technician_dashboard.php' ? ' active' : ''); ?>"><i class="fa fa-tools"></i> Technician Dashboard</a></li>
        <li class="mb-sm"><a href="technician_tickets.php" class="btn btn-outline w-100<?php echo ($current === 'technician_tickets.php' ? ' active' : ''); ?>"><i class="fa fa-ticket-alt"></i> My Assigned Tickets</a></li>
        <li class="mb-sm"><a href="substations.php" class="btn btn-outline w-100<?php echo ($current === 'substations.php' ? ' active' : ''); ?>"><i class="fa fa-bolt"></i> Substations</a></li>
        <li class="mb-sm"><a href="reports.php" class="btn btn-outline w-100<?php echo ($current === 'reports.php' ? ' active' : ''); ?>"><i class="fa fa-file-alt"></i> Reports</a></li>
        <li class="mb-sm"><a href="analytics.php" class="btn btn-outline w-100<?php echo ($current === 'analytics.php' ? ' active' : ''); ?>"><i class="fa fa-chart-bar"></i> Analytics</a></li>
        <li class="mb-sm"><a href="map.php" class="btn btn-outline w-100<?php echo ($current === 'map.php' ? ' active' : ''); ?>"><i class="fa fa-map"></i> Map View</a></li>
        <li class="mb-sm"><a href="settings.php" class="btn btn-outline w-100<?php echo ($current === 'settings.php' ? ' active' : ''); ?>"><i class="fa fa-cogs"></i> Settings</a></li>
      <?php else: ?>
        <li class="mb-sm"><a href="user_dashboard.php" class="btn btn-outline w-100<?php echo ($current === 'user_dashboard.php' ? ' active' : ''); ?>"><i class="fa fa-tachometer-alt"></i> Dashboard</a></li>
        <li class="mb-sm"><a href="profile.php" class="btn btn-outline w-100<?php echo ($current === 'profile.php' ? ' active' : ''); ?>"><i class="fa fa-user"></i> My Profile</a></li>
        <li class="mb-sm"><a href="submit_ticket.php" class="btn btn-outline w-100<?php echo ($current === 'submit_ticket.php' ? ' active' : ''); ?>"><i class="fa fa-plus"></i> Submit Ticket</a></li>
        <li class="mb-sm"><a href="my_tickets.php" class="btn btn-outline w-100<?php echo ($current === 'my_tickets.php' ? ' active' : ''); ?>"><i class="fa fa-ticket-alt"></i> My Tickets</a></li>
        <li class="mb-sm"><a href="map.php" class="btn btn-outline w-100<?php echo ($current === 'map.php' ? ' active' : ''); ?>"><i class="fa fa-map"></i> Map View</a></li>
        <li class="mb-sm"><a href="settings.php" class="btn btn-outline w-100<?php echo ($current === 'settings.php' ? ' active' : ''); ?>"><i class="fa fa-cogs"></i> Settings</a></li>
      <?php endif; ?>
      <li class="mt-md">
        <form action="logout.php" method="post" style="margin:0;">
          <button type="submit" class="btn btn-danger w-100"><i class="fa fa-sign-out-alt"></i> Logout</button>
        </form>
      </li>
    </ul>
  </nav>
</aside>
<script>
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebarNav = document.getElementById('sidebarNav');
if (sidebarToggle && sidebarNav) {
  sidebarToggle.addEventListener('click', function() {
    sidebarNav.classList.toggle('open');
  });
}
</script>
<style>
.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
}
.sidebar-toggle {
  display: none;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  width: 36px;
  height: 36px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  margin-left: 8px;
}
.sidebar-toggle .bar {
  width: 24px;
  height: 3px;
  background: #222;
  margin: 3px 0;
  border-radius: 2px;
  transition: all 0.3s;
}
@media (max-width: 900px) {
  .sidebar-toggle {
    display: flex;
  }
  .sidebar nav {
    display: none;
    flex-direction: column;
    width: 100%;
    background: #f4f4f4;
    position: absolute;
    left: 0;
    top: 56px;
    z-index: 1001;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border-radius: 0 0 12px 12px;
  }
  .sidebar nav.open {
    display: flex;
  }
  .sidebar-header {
    position: relative;
    z-index: 1002;
  }
}
</style> 