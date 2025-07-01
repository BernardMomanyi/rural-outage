<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$role = $_SESSION['role'];
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard - OutageSys</title>
  <link rel="stylesheet" href="css/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
</head>
<body class="<?php echo htmlspecialchars($role); ?>">
  <div class="dashboard">
    <?php include 'sidebar.php'; ?>
    <main class="main-content" role="main">
      <div class="dashboard-grid">
        <div class="card">
          <h2>Dashboard Overview</h2>
          <header>
            <h1>Dashboard Overview</h1>
            <div class="user-info">Welcome, <?php echo htmlspecialchars($username); ?> (<?php echo htmlspecialchars($role); ?>)</div>
          </header>
          <section class="stats">
            <div class="card"><i class="fa fa-bolt"></i> Total Substations: <span id="totalSubstations">47</span></div>
            <div class="card"><i class="fa fa-chart-line"></i> Outage Predictions: <span id="predictionsCount">12</span></div>
            <div class="card"><i class="fa fa-exclamation-triangle"></i> Critical Alerts: <span id="criticalAlerts">3</span></div>
          </section>
          <section class="charts">
            <canvas id="outageTrendChart"></canvas>
          </section>
          <section class="notifications">
            <h2>Live Notifications</h2>
            <ul id="notificationsList">
              <li>High risk detected at Naivasha Substation.</li>
              <li>New outage prediction for Mandera County.</li>
              <li>Critical alert: Mombasa Substation offline.</li>
            </ul>
          </section>
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
  <script src="js/main.js"></script>
</body>
</html> 