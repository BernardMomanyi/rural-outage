<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'technician'])) {
  header('Location: login.php');
  exit;
}
$role = $_SESSION['role'];
$username = $_SESSION['username'];
$dashboard_link = 'user_dashboard.php';
if ($role === 'admin') $dashboard_link = 'admin_dashboard.php';
if ($role === 'technician') $dashboard_link = 'technician_dashboard.php';

require_once 'db.php';
// Fetch all reports
// Fetch report counts per month for the chart

// Date range filter logic
$start = isset($_GET['start']) ? $_GET['start'] : '';
$end = isset($_GET['end']) ? $_GET['end'] : '';
$where = '';
$params = [];
if ($start && $end) {
  $where = 'WHERE created_at BETWEEN ? AND ?';
  $params = [$start . ' 00:00:00', $end . ' 23:59:59'];
}

// Chart data
if ($where) {
  $stmt = $pdo->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM reports $where GROUP BY month ORDER BY month ASC");
  $stmt->execute($params);
  $report_months = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
  $stmt = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM reports GROUP BY month ORDER BY month ASC");
  $report_months = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Table data
if ($where) {
  $stmt = $pdo->prepare("SELECT * FROM reports $where ORDER BY id DESC");
  $stmt->execute($params);
  $all_reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
  $stmt = $pdo->query("SELECT * FROM reports ORDER BY id DESC");
  $all_reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports - OutageSys</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    body { min-height: 100vh; }
    .reports-bg {
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
  </style>
</head>
<body>
  <!-- Breadcrumbs navigation -->
  <nav aria-label="Breadcrumb" class="breadcrumbs-nav" style="margin: 1.5rem auto 0 auto; max-width:900px; background:transparent;">
    <ol class="breadcrumbs" style="display:flex; flex-wrap:wrap; gap:0.5em; list-style:none; padding:0; margin:0; font-size:1rem; background:transparent;">
      <li><a href="<?php echo $dashboard_link; ?>" class="breadcrumb-link"><i class="fa fa-tachometer-alt"></i> <?php echo ($role === 'admin') ? 'Admin Dashboard' : (($role === 'technician') ? 'Technician Dashboard' : 'Dashboard'); ?></a></li>
      <li style="color:var(--color-secondary);">&gt;</li>
      <li class="breadcrumb-current" style="color:var(--color-primary); font-weight:600;"><i class="fa fa-file-alt"></i> Reports</li>
    </ol>
  </nav>
  <button class="toggle-mode" id="darkModeToggle">
    <i class="fa fa-moon" id="modeIcon"></i> 
    <span id="modeText">Dark Mode</span>
  </button>
  <div class="reports-bg">
    <div class="container" style="width:100%;">
      <div class="card mb-md">
        <h2 class="h2 mb-sm"><i class="fa fa-file-alt"></i> Reports</h2>
        <p class="mb-md">View and analyze outage and maintenance reports.</p>
        <div style="display:flex; gap:1em; margin-bottom:1em; flex-wrap:wrap;">
          <?php if (in_array($role, ['admin', 'technician'])): ?>
            <form method="post" action="generate_report.php" style="display:inline;">
              <button type="submit" class="btn btn-success"><i class="fa fa-plus"></i> Generate Outage Report (CSV)</button>
            </form>
          <?php endif; ?>
          <button id="downloadImageBtn" class="btn btn-outline"><i class="fa fa-image"></i> Download Chart Image</button>
          <button id="downloadCsvBtn" class="btn btn-outline"><i class="fa fa-file-csv"></i> Download CSV</button>
          <form id="dateRangeForm" method="get" style="display:flex; gap:0.5em; align-items:center; flex-wrap:wrap;">
            <label for="rangePreset" class="small" style="font-weight:600;">Date Range:</label>
            <select id="rangePreset" name="preset" class="btn btn-outline small">
              <option value="all" <?php if(!$start && !$end) echo 'selected'; ?>>All</option>
              <option value="30days" <?php if(isset($_GET['preset']) && $_GET['preset']==='30days') echo 'selected'; ?>>Last 30 Days</option>
              <option value="year" <?php if(isset($_GET['preset']) && $_GET['preset']==='year') echo 'selected'; ?>>This Year</option>
              <option value="custom" <?php if(isset($_GET['preset']) && $_GET['preset']==='custom') echo 'selected'; ?>>Custom</option>
            </select>
            <input type="date" id="startDate" name="start" value="<?php echo htmlspecialchars($start); ?>" class="btn btn-outline small" style="display:none;" />
            <span id="toLabel" style="display:none;">to</span>
            <input type="date" id="endDate" name="end" value="<?php echo htmlspecialchars($end); ?>" class="btn btn-outline small" style="display:none;" />
            <button type="submit" class="btn btn-primary small">Apply</button>
          </form>
        </div>
        <div style="width:100%; max-width:700px; margin:2em auto 1em auto;">
          <canvas id="reportsChart" height="120"></canvas>
        </div>
        <div style="width:100%; max-width:900px; margin:2em auto 1em auto;">
          <h3 class="h3 mb-sm"><i class="fa fa-database"></i> Reports Record</h3>
          <table class="styled-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Download</th>
                <th>Created At</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($all_reports as $report): ?>
                <tr>
                  <td><?php echo htmlspecialchars($report['id']); ?></td>
                  <td><?php echo htmlspecialchars($report['name']); ?></td>
                  <td><a href="<?php echo htmlspecialchars($report['file_path']); ?>" class="btn btn-outline" download><i class="fa fa-download"></i> Download</a></td>
                  <td><?php echo htmlspecialchars($report['created_at']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    function setMode(dark) {
      document.body.classList.toggle('dark-mode', dark);
      document.getElementById('modeIcon').className = dark ? 'fa fa-sun' : 'fa fa-moon';
      document.getElementById('modeText').textContent = dark ? 'Light Mode' : 'Dark Mode';
      localStorage.setItem('darkMode', dark ? '1' : '0');
    }
    document.getElementById('darkModeToggle').onclick = function() {
      setMode(!document.body.classList.contains('dark-mode'));
    };
    if (localStorage.getItem('darkMode') === '1') setMode(true);

    // Date range picker logic
    const preset = document.getElementById('rangePreset');
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    const toLabel = document.getElementById('toLabel');
    function updateDateInputs() {
      if (preset.value === 'custom') {
        startDate.style.display = '';
        endDate.style.display = '';
        toLabel.style.display = '';
      } else {
        startDate.style.display = 'none';
        endDate.style.display = 'none';
        toLabel.style.display = 'none';
      }
    }
    preset.onchange = function() {
      updateDateInputs();
      if (preset.value === '30days') {
        const today = new Date();
        const prior = new Date();
        prior.setDate(today.getDate() - 29);
        startDate.value = prior.toISOString().slice(0,10);
        endDate.value = today.toISOString().slice(0,10);
      } else if (preset.value === 'year') {
        const today = new Date();
        startDate.value = today.getFullYear() + '-01-01';
        endDate.value = today.getFullYear() + '-12-31';
      } else if (preset.value === 'all') {
        startDate.value = '';
        endDate.value = '';
      }
    };
    updateDateInputs();
    // On page load, show custom if custom selected
    if (preset.value === 'custom') updateDateInputs();

    // Chart.js - Reports per Month
    const reportMonths = <?php echo json_encode($report_months); ?>;
    const labels = reportMonths.map(r => r.month);
    const data = reportMonths.map(r => Number(r.count));
    const ctx = document.getElementById('reportsChart').getContext('2d');
    const chart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Reports Created',
          data: data,
          backgroundColor: 'rgba(54, 162, 235, 0.7)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 2,
          borderRadius: 8,
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          title: { display: true, text: 'Reports Created Per Month', color: '#333', font: { size: 18, weight: 'bold' } }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { color: document.body.classList.contains('dark-mode') ? '#fff' : '#333' },
            grid: { color: '#e0eafc' }
          },
          x: {
            ticks: { color: document.body.classList.contains('dark-mode') ? '#fff' : '#333' },
            grid: { color: '#f4f6f9' }
          }
        }
      }
    });
  </script>
</body>
</html> 