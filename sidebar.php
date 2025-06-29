<?php
if (!isset($role)) {
  $role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';
}
?>
<aside class="sidebar">
  <h2>OutageSys</h2>
  <nav aria-label="Main navigation">
    <?php if ($role === 'admin'): ?>
      <a href="admin_dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) === 'admin_dashboard.php' ? 'active' : '' ?>"><i data-lucide="layout-dashboard"></i> Dashboard</a>
      <a href="substations.php" class="<?= basename($_SERVER['PHP_SELF']) === 'substations.php' ? 'active' : '' ?>"><i data-lucide="bolt"></i> Substations</a>
      <a href="reports.php" class="<?= basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : '' ?>"><i data-lucide="file-text"></i> Reports</a>
      <a href="analytics.php" class="<?= basename($_SERVER['PHP_SELF']) === 'analytics.php' ? 'active' : '' ?>"><i data-lucide="bar-chart-3"></i> Data Analytics</a>
      <a href="map.php" class="<?= basename($_SERVER['PHP_SELF']) === 'map.php' ? 'active' : '' ?>"><i data-lucide="map"></i> Map View</a>
      <a href="users.php" class="<?= basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : '' ?>"><i data-lucide="users"></i> Users</a>
      <a href="settings.php" class="<?= basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : '' ?>"><i data-lucide="settings"></i> Settings</a>
    <?php elseif ($role === 'technician'): ?>
      <a href="technician_dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) === 'technician_dashboard.php' ? 'active' : '' ?>"><i data-lucide="layout-dashboard"></i> Dashboard</a>
      <a href="substations.php" class="<?= basename($_SERVER['PHP_SELF']) === 'substations.php' ? 'active' : '' ?>"><i data-lucide="bolt"></i> Substations</a>
      <a href="reports.php" class="<?= basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : '' ?>"><i data-lucide="file-text"></i> Reports</a>
      <a href="analytics.php" class="<?= basename($_SERVER['PHP_SELF']) === 'analytics.php' ? 'active' : '' ?>"><i data-lucide="bar-chart-3"></i> Data Analytics</a>
      <a href="map.php" class="<?= basename($_SERVER['PHP_SELF']) === 'map.php' ? 'active' : '' ?>"><i data-lucide="map"></i> Map View</a>
      <a href="settings.php" class="<?= basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : '' ?>"><i data-lucide="settings"></i> Settings</a>
    <?php else: ?>
      <a href="user_dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) === 'user_dashboard.php' ? 'active' : '' ?>"><i data-lucide="layout-dashboard"></i> Dashboard</a>
      <a href="map.php" class="<?= basename($_SERVER['PHP_SELF']) === 'map.php' ? 'active' : '' ?>"><i data-lucide="map"></i> Map View</a>
      <a href="settings.php" class="<?= basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : '' ?>"><i data-lucide="settings"></i> Settings</a>
    <?php endif; ?>
  </nav>
  <form action="logout.php" method="post" style="margin-top: 18px;">
    <button class="logout-btn" type="submit"><i data-lucide="log-out"></i> Logout</button>
  </form>
</aside> 