<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit;
}
require_once 'db.php';
$username = $_SESSION['username'];
$role = $_SESSION['role'];
$dashboard_link = 'user_dashboard.php';
if ($role === 'admin') $dashboard_link = 'admin_dashboard.php';
if ($role === 'technician') $dashboard_link = 'technician_dashboard.php';
// Fetch data for analytics
$counties = $pdo->query("SELECT county, COUNT(*) as count FROM substations GROUP BY county")->fetchAll(PDO::FETCH_ASSOC);
$risks = $pdo->query("SELECT risk, COUNT(*) as count FROM substations GROUP BY risk")->fetchAll(PDO::FETCH_ASSOC);
$techs = $pdo->query("SELECT u.username, COUNT(ts.id) as count FROM users u LEFT JOIN technician_substations ts ON u.id = ts.technician_id WHERE u.role='technician' GROUP BY u.id")->fetchAll(PDO::FETCH_ASSOC);
// Outages per month (simulate with created_at if available, else use static demo data)
// $outage_months = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM substations GROUP BY month ORDER BY month")->fetchAll(PDO::FETCH_ASSOC);
$outage_months = [
  ['month' => '2024-01', 'count' => 3],
  ['month' => '2024-02', 'count' => 5],
  ['month' => '2024-03', 'count' => 2],
  ['month' => '2024-04', 'count' => 7],
  ['month' => '2024-05', 'count' => 4],
  ['month' => '2024-06', 'count' => 6],
];
// Top risky substations
$top_risk = $pdo->query("SELECT name, risk FROM substations WHERE risk='High' ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
// Status breakdown
$status = $pdo->query("SELECT status, COUNT(*) as count FROM substations GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Data Analytics - OutageSys</title>
  <link rel="stylesheet" href="css/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .toggle-mode { margin: 0 0 18px 0; background: #f1f5f9; border: none; border-radius: 20px; padding: 8px 16px; font-size: 1rem; cursor: pointer; color: #2563eb; display: flex; align-items: center; gap: 8px; transition: background 0.2s, color 0.2s; }
    body.dark .toggle-mode { background: #23272f; color: #60a5fa; }
    .chart-section { margin-bottom: 36px; }
    .chart-section h2 { color: #2563eb; margin-bottom: 12px; }
    .dashboard { min-height: 100vh; }
    .filter-bar { margin-bottom: 24px; }
    .filter-bar select { padding: 6px 12px; border-radius: 6px; border: 1px solid #cbd5e1; }
  </style>
</head>
<body class="admin">
  <div class="dashboard">
    <?php include 'sidebar.php'; ?>
    <main class="main-content" role="main">
      <nav aria-label="Breadcrumb" class="breadcrumb">
        <ol>
          <li><a href="index.php">Home</a></li>
          <li>›</li>
          <li>Analytics</li>
        </ol>
      </nav>
      <div class="dashboard-grid">
        <div class="card">
          <h2>Data Analytics</h2>
          <div class="filter-bar">
            <label for="countyFilter">Filter by County:</label>
            <select id="countyFilter">
              <option value="">All Counties</option>
              <?php foreach ($counties as $c): ?>
                <option value="<?php echo htmlspecialchars($c['county']); ?>"><?php echo htmlspecialchars($c['county']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <section class="chart-section">
            <h2>Outages by County</h2>
            <canvas id="countyChart"></canvas>
          </section>
          <section class="chart-section">
            <h2>Risk Distribution</h2>
            <canvas id="riskChart"></canvas>
          </section>
          <section class="chart-section">
            <h2>Technician Workload</h2>
            <canvas id="techChart"></canvas>
          </section>
          <section class="chart-section">
            <h2>Outages per Month</h2>
            <canvas id="monthChart"></canvas>
          </section>
          <section class="chart-section">
            <h2>Top Risky Substations</h2>
            <canvas id="topRiskChart"></canvas>
          </section>
          <section class="chart-section">
            <h2>Substation Status Breakdown</h2>
            <canvas id="statusChart"></canvas>
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
  <script>
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
    // Outages by County
    const countyData = <?php echo json_encode($counties); ?>;
    let countyChart = new Chart(document.getElementById('countyChart'), {
      type: 'bar',
      data: {
        labels: countyData.map(c => c.county),
        datasets: [{
          label: 'Outages',
          data: countyData.map(c => c.count),
          backgroundColor: '#2563eb',
        }]
      },
      options: { responsive: true, plugins: { legend: { display: false } } }
    });
    // Risk Distribution
    const riskData = <?php echo json_encode($risks); ?>;
    new Chart(document.getElementById('riskChart'), {
      type: 'pie',
      data: {
        labels: riskData.map(r => r.risk),
        datasets: [{
          label: 'Risk',
          data: riskData.map(r => r.count),
          backgroundColor: ['#16a34a', '#f59e42', '#e53e3e'],
        }]
      },
      options: { responsive: true }
    });
    // Technician Workload
    const techData = <?php echo json_encode($techs); ?>;
    new Chart(document.getElementById('techChart'), {
      type: 'bar',
      data: {
        labels: techData.map(t => t.username),
        datasets: [{
          label: 'Assigned Substations',
          data: techData.map(t => t.count),
          backgroundColor: '#f59e42',
        }]
      },
      options: { responsive: true, plugins: { legend: { display: false } } }
    });
    // Outages per Month (simulated)
    const monthData = <?php echo json_encode($outage_months); ?>;
    new Chart(document.getElementById('monthChart'), {
      type: 'line',
      data: {
        labels: monthData.map(m => m.month),
        datasets: [{
          label: 'Outages',
          data: monthData.map(m => m.count),
          borderColor: '#2563eb',
          backgroundColor: 'rgba(37,99,235,0.1)',
          fill: true,
        }]
      },
      options: { responsive: true }
    });
    // Top Risky Substations
    const topRiskData = <?php echo json_encode($top_risk); ?>;
    new Chart(document.getElementById('topRiskChart'), {
      type: 'bar',
      data: {
        labels: topRiskData.map(r => r.name),
        datasets: [{
          label: 'High Risk',
          data: topRiskData.map(r => 1),
          backgroundColor: '#e53e3e',
        }]
      },
      options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } }
    });
    // Substation Status Breakdown
    const statusData = <?php echo json_encode($status); ?>;
    new Chart(document.getElementById('statusChart'), {
      type: 'doughnut',
      data: {
        labels: statusData.map(s => s.status),
        datasets: [{
          label: 'Status',
          data: statusData.map(s => s.count),
          backgroundColor: ['#16a34a', '#f59e42', '#e53e3e', '#2563eb'],
        }]
      },
      options: { responsive: true }
    });
    // Interactive filtering by county
    document.getElementById('countyFilter').addEventListener('change', function() {
      const val = this.value;
      let filtered = countyData;
      if (val) filtered = countyData.filter(c => c.county === val);
      countyChart.data.labels = filtered.map(c => c.county);
      countyChart.data.datasets[0].data = filtered.map(c => c.count);
      countyChart.update();
    });
  </script>
</body>
</html> 