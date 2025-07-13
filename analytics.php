<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'technician'])) {
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
// Date range and substation filter logic
$start = isset($_GET['start']) ? $_GET['start'] : '';
$end = isset($_GET['end']) ? $_GET['end'] : '';
$substation = isset($_GET['substation']) ? $_GET['substation'] : '';
$where = [];
$params = [];
if ($start && $end) {
  $where[] = 'o.start_time BETWEEN ? AND ?';
  $params[] = $start . ' 00:00:00';
  $params[] = $end . ' 23:59:59';
}
if ($substation && $substation !== 'all') {
  $where[] = 'o.substation_id = ?';
  $params[] = $substation;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Substation list for dropdown
$substations = $pdo->query('SELECT id, name FROM substations ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);

// Stats queries (filtered)
$resolved_where = $whereSql ? ($whereSql . " AND o.end_time IS NOT NULL") : "WHERE o.end_time IS NOT NULL";
$open_where = $whereSql ? ($whereSql . " AND o.end_time IS NULL") : "WHERE o.end_time IS NULL";
$avgres_where = $whereSql ? ($whereSql . " AND o.end_time IS NOT NULL") : "WHERE o.end_time IS NOT NULL";
$resolved_per_month_where = $whereSql ? ($whereSql . " AND o.end_time IS NOT NULL") : "WHERE o.end_time IS NOT NULL";
$avg_res_per_month_where = $whereSql ? ($whereSql . " AND o.end_time IS NOT NULL") : "WHERE o.end_time IS NOT NULL";

$total_outages = $pdo->prepare("SELECT COUNT(*) FROM outages o $whereSql");
$total_outages->execute($params);
$total_outages = $total_outages->fetchColumn();
$resolved_outages = $pdo->prepare("SELECT COUNT(*) FROM outages o $resolved_where");
$resolved_outages->execute($params);
$resolved_outages = $resolved_outages->fetchColumn();
$open_outages = $pdo->prepare("SELECT COUNT(*) FROM outages o $open_where");
$open_outages->execute($params);
$open_outages = $open_outages->fetchColumn();
$avg_resolution = $pdo->prepare("SELECT AVG(TIMESTAMPDIFF(MINUTE, o.start_time, o.end_time)) FROM outages o $avgres_where");
$avg_resolution->execute($params);
$avg_resolution = $avg_resolution->fetchColumn();

// Outages per month (filtered)
$outage_months = $pdo->prepare("SELECT DATE_FORMAT(o.start_time, '%Y-%m') as month, COUNT(*) as count FROM outages o $whereSql GROUP BY month ORDER BY month ASC");
$outage_months->execute($params);
$outage_months = $outage_months->fetchAll(PDO::FETCH_ASSOC);

// Debug: Check if we have data
if (empty($outage_months)) {
  $outage_months = [['month' => '2024-01', 'count' => 0], ['month' => '2024-02', 'count' => 0]];
}
// Outages resolved per month (filtered)
$resolved_per_month = $pdo->prepare("SELECT DATE_FORMAT(o.end_time, '%Y-%m') as month, COUNT(*) as count FROM outages o $resolved_per_month_where GROUP BY month ORDER BY month ASC");
$resolved_per_month->execute($params);
$resolved_per_month = $resolved_per_month->fetchAll(PDO::FETCH_ASSOC);

// Debug: Check if we have data
if (empty($resolved_per_month)) {
  $resolved_per_month = [['month' => '2024-01', 'count' => 0], ['month' => '2024-02', 'count' => 0]];
}
// Avg. resolution time per month (filtered)
$avg_res_per_month = $pdo->prepare("SELECT DATE_FORMAT(o.start_time, '%Y-%m') as month, AVG(TIMESTAMPDIFF(MINUTE, o.start_time, o.end_time)) as avg_minutes FROM outages o $avg_res_per_month_where GROUP BY month ORDER BY month ASC");
$avg_res_per_month->execute($params);
$avg_res_per_month = $avg_res_per_month->fetchAll(PDO::FETCH_ASSOC);

// Debug: Check if we have data
if (empty($avg_res_per_month)) {
  $avg_res_per_month = [['month' => '2024-01', 'avg_minutes' => 0], ['month' => '2024-02', 'avg_minutes' => 0]];
}
// Top risky substations
$top_risk = $pdo->query("SELECT name, risk FROM substations WHERE risk='High' ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
// Status breakdown
$status = $pdo->query("SELECT status, COUNT(*) as count FROM substations GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Analytics - OutageSys</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    body { min-height: 100vh; }
    .analytics-bg {
      min-height: 100vh;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 0;
    }

    /* Always show dropdown text as black for readability */
    select, select option, select:focus, select:active {
      color: #111 !important;
      background: #fff !important;
    }
    body.dark-mode select, body.dark-mode select option, body.dark-mode select:focus, body.dark-mode select:active {
      color: #111 !important;
      background: #fff !important;
    }
    
    /* Enhanced MVP Smart Insights Animations */
    @keyframes pulse {
      0%, 100% { transform: scale(1); opacity: 0.5; }
      50% { transform: scale(1.1); opacity: 0.8; }
    }
    
    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
      40% { transform: translateY(-10px); }
      60% { transform: translateY(-5px); }
    }
    
    @keyframes glow {
      0%, 100% { box-shadow: 0 4px 12px rgba(245,158,66,0.4); }
      50% { box-shadow: 0 4px 20px rgba(245,158,66,0.7); }
    }
    
    /* Dark mode fixes for analytics page */
    body.dark-mode .analytics-bg {
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%) !important;
    }
    
    body.dark-mode .card {
      background: #2d3748 !important;
      color: #e2e8f0 !important;
      border-color: #4a5568 !important;
    }
    
    body.dark-mode .stat-card {
      background: #2d3748 !important;
      color: #e2e8f0 !important;
    }
    
    /* Fix stat card text colors in dark mode */
    body.dark-mode .stat-card .small {
      color: #cbd5e0 !important;
    }
    
    body.dark-mode .stat-card div[style*="color:#232946"] {
      color: #f7fafc !important;
    }
    
    body.dark-mode .stat-card div[style*="color:#555"] {
      color: #cbd5e0 !important;
    }
    
    body.dark-mode .smart-insight-mvp {
      background: linear-gradient(135deg, #4c51bf 0%, #553c9a 100%) !important;
    }
    
    /* Ensure smart insights text is visible */
    body.dark-mode .smart-insight-mvp span[style*="color:#fff"] {
      color: #ffffff !important;
    }
    
    body.dark-mode .legend-popup {
      background: #2d3748 !important;
      border-color: #4a5568 !important;
      color: #e2e8f0 !important;
    }
    
    /* Fix legend popup text colors */
    body.dark-mode .legend-popup div[style*="color:#2563eb"] {
      color: #63b3ed !important;
    }
    
    body.dark-mode .legend-popup div[style*="color:#555"] {
      color: #cbd5e0 !important;
    }
    
    body.dark-mode .legend-popup div[style*="color:#374151"] {
      color: #e2e8f0 !important;
    }
    
    body.dark-mode .legend-popup div[style*="color:#666"] {
      color: #a0aec0 !important;
    }
    
    body.dark-mode .breadcrumb-link {
      color: #a0aec0 !important;
    }
    
    body.dark-mode .breadcrumb-current {
      color: #63b3ed !important;
    }
    
    body.dark-mode .btn-outline {
      background: #4a5568 !important;
      color: #e2e8f0 !important;
      border-color: #718096 !important;
    }
    
    body.dark-mode .btn-outline:hover {
      background: #718096 !important;
    }
    
    body.dark-mode .btn-primary {
      background: #3182ce !important;
      color: #fff !important;
    }
    
    body.dark-mode .btn-primary:hover {
      background: #2c5aa0 !important;
    }
    
    body.dark-mode .small {
      color: #cbd5e0 !important;
    }
    
    body.dark-mode .h2 {
      color: #f7fafc !important;
    }
    
    body.dark-mode .footer {
      color: #a0aec0 !important;
    }
    
    /* Fix chart titles and labels in dark mode */
    body.dark-mode canvas {
      filter: invert(0.9) hue-rotate(180deg);
    }
    
    /* Fix form labels and text */
    body.dark-mode label {
      color: #e2e8f0 !important;
    }
    
    body.dark-mode p {
      color: #cbd5e0 !important;
    }
    
    /* Fix any remaining text that might be hard to see */
    body.dark-mode * {
      color: inherit;
    }
    
    /* Ensure proper contrast for all text elements */
    body.dark-mode .card p,
    body.dark-mode .card .mb-md,
    body.dark-mode .card .mb-sm {
      color: #cbd5e0 !important;
    }
    
    /* Fix stat numbers and labels in dark mode */
    body.dark-mode .stat-number {
      color: #f7fafc !important;
    }
    
    body.dark-mode .stat-label {
      color: #cbd5e0 !important;
    }
    
    /* Fix any inline styled text that might be hard to see */
    body.dark-mode div[style*="color:#232946"] {
      color: #f7fafc !important;
    }
    
    body.dark-mode div[style*="color:#555"] {
      color: #cbd5e0 !important;
    }
    
    body.dark-mode div[style*="color:#666"] {
      color: #a0aec0 !important;
    }
    
    /* Ensure all text in cards is visible */
    body.dark-mode .card * {
      color: inherit;
    }
    
    /* Fix breadcrumb text */
    body.dark-mode .breadcrumbs li {
      color: #a0aec0 !important;
    }
    
    body.dark-mode .breadcrumbs .breadcrumb-current {
      color: #63b3ed !important;
    }
    .legend-popup {
      width: 320px !important;
      max-width: 90vw !important;
      min-width: 220px;
      box-sizing: border-box;
      word-break: normal !important;
      white-space: normal !important;
      display: flex;
      flex-direction: column;
      align-items: stretch;
      overflow: auto;
      max-height: 80vh;
    }
    .legend-backdrop {
      background: rgba(0,0,0,0.45) !important;
      backdrop-filter: blur(3px) !important;
    }
    .smart-insight-mvp .stable-insight-text {
      color: #232946 !important;
      text-shadow: 0 2px 8px rgba(0,0,0,0.12),0 1px 0 #fff;
    }
    body.dark-mode .smart-insight-mvp .stable-insight-text {
      color: #f7fafc !important;
      text-shadow: 0 2px 8px rgba(0,0,0,0.4);
    }
  </style>
</head>
<body>
  <!-- Breadcrumbs navigation -->
  <nav aria-label="Breadcrumb" class="breadcrumbs-nav" style="margin: 1.5rem auto 0 auto; max-width:900px; background:transparent;">
    <ol class="breadcrumbs" style="display:flex; flex-wrap:wrap; gap:0.5em; list-style:none; padding:0; margin:0; font-size:1rem; background:transparent;">
      <li><a href="<?php echo $dashboard_link; ?>" class="breadcrumb-link"><i class="fa fa-tachometer-alt"></i> <?php echo ($role === 'admin') ? 'Admin Dashboard' : (($role === 'technician') ? 'Technician Dashboard' : 'Dashboard'); ?></a></li>
      <li style="color:var(--color-secondary);">&gt;</li>
      <li class="breadcrumb-current" style="color:var(--color-primary); font-weight:600;"><i class="fa fa-chart-bar"></i> Analytics</li>
    </ol>
  </nav>

  <div class="analytics-bg">
    <div class="container" style="width:100%; max-width:1000px;">
      <!-- Enhanced MVP Smart Insights -->
      <div class="card smart-insight-mvp" style="display:flex;align-items:center;gap:1.5em;padding:1.5em 2em;margin-bottom:2em;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);border-radius:16px;box-shadow:0 8px 32px rgba(102,126,234,0.25);position:relative;overflow:hidden;">
        <div style="position:absolute;top:-50%;right:-20%;width:200px;height:200px;background:rgba(255,255,255,0.1);border-radius:50%;animation:pulse 3s infinite;"></div>
        <div style="position:absolute;bottom:-30%;left:-10%;width:150px;height:150px;background:rgba(255,255,255,0.08);border-radius:50%;animation:pulse 4s infinite reverse;"></div>
        <div style="display:flex;align-items:center;gap:1em;z-index:1;position:relative;">
          <div style="font-size:3rem;animation:bounce 2s infinite;">
            <i class="fa fa-balance-scale" style="color:#74c0fc;text-shadow:0 2px 8px rgba(116,192,252,0.4);"></i>
          </div>
          <div style="color:#fff;z-index:1;position:relative;">
            <div style="font-size:1.3rem;font-weight:700;margin-bottom:0.3em;text-shadow:0 2px 4px rgba(0,0,0,0.2);">
              <span class="stable-insight-text">⚖️ Outages Remained Stable</span>
            </div>
            <div style="font-size:0.9rem;opacity:0.9;margin-bottom:0.5em;"></div>
            <div style="font-size:0.85rem;opacity:0.8;">
              <span style="color:#51cf66;">✅ Below average (0 avg)</span>
            </div>
          </div>
        </div>
      </div>
      <form id="analyticsFilterForm" method="get" style="display:flex; gap:1em; align-items:center; flex-wrap:wrap; margin-bottom:2em;">
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
        <label for="substation" class="small" style="font-weight:600;">Substation:</label>
        <select id="substation" name="substation" class="btn btn-outline small">
          <option value="all">All Substations</option>
          <?php foreach ($substations as $s): ?>
            <option value="<?php echo $s['id']; ?>" <?php if($substation == $s['id']) echo 'selected'; ?>><?php echo htmlspecialchars($s['name']); ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary small">Apply</button>
      </form>
      <div class="grid stats mb-md" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:24px; margin-bottom:32px;">
        <div class="card stat-card" style="background:#f1f5ff; border-left:6px solid #2563eb; position:relative;">
          <div style="font-size:2.2rem; color:#2563eb;"><i class="fa fa-bolt"></i></div>
          <div style="font-size:2.1rem; font-weight:700; color:#232946;" class="stat-number"><?php echo $total_outages; ?></div>
          <div class="small" style="color:#555;" class="stat-label">Total Outages</div>
        </div>
        <div class="card stat-card" style="background:#e6fffa; border-left:6px solid #06b6d4;">
          <div style="font-size:2.2rem; color:#06b6d4;"><i class="fa fa-check-circle"></i></div>
          <div style="font-size:2.1rem; font-weight:700; color:#232946;" class="stat-number"><?php echo $resolved_outages; ?></div>
          <div class="small" style="color:#555;" class="stat-label">Resolved Outages</div>
        </div>
        <div class="card stat-card" style="background:#fff7ed; border-left:6px solid #f59e42;">
          <div style="font-size:2.2rem; color:#f59e42;"><i class="fa fa-exclamation-triangle"></i></div>
          <div style="font-size:2.1rem; font-weight:700; color:#232946;" class="stat-number"><?php echo $open_outages; ?></div>
          <div class="small" style="color:#555;" class="stat-label">Open Outages</div>
        </div>
        <div class="card stat-card" style="background:#f0fdf4; border-left:6px solid #22c55e;">
          <div style="font-size:2.2rem; color:#22c55e;"><i class="fa fa-clock"></i></div>
          <div style="font-size:2.1rem; font-weight:700; color:#232946;" class="stat-number"><?php echo $avg_resolution ? round($avg_resolution/60,1) : '0'; ?></div>
          <div class="small" style="color:#555;" class="stat-label">Avg. Resolution Time (hrs)</div>
        </div>
      </div>
      <div class="card mb-md" style="box-shadow:0 4px 24px rgba(38,99,235,0.08); border-radius:18px; padding:2.5em 2em; background:#fff; max-width:800px; margin:0 auto 2em auto;">
        <h2 class="h2 mb-sm" style="color:#2563eb;"><i class="fa fa-chart-line"></i> Outage Trends</h2>
        <p class="mb-md">Monthly breakdown of outages started in the system.</p>
        <div style="display:flex; gap:1em; margin-bottom:1em; flex-wrap:wrap;">
          <button id="downloadOutageChartImg" class="btn btn-outline"><i class="fa fa-image"></i> Download Chart</button>
          <button id="downloadOutageChartCsv" class="btn btn-outline"><i class="fa fa-file-csv"></i> Download CSV</button>
          <button class="btn btn-outline" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        </div>
        <div style="width:100%; max-width:700px; margin:2em auto 1em auto;">
          <canvas id="outageTrendChart" height="120"></canvas>
        </div>
      </div>
      <div class="card mb-md" style="box-shadow:0 4px 24px rgba(6,182,212,0.08); border-radius:18px; padding:2.5em 2em; background:#fff; max-width:800px; margin:0 auto 2em auto;">
        <h2 class="h2 mb-sm" style="color:#06b6d4;"><i class="fa fa-check-circle"></i> Outages Resolved Per Month</h2>
        <p class="mb-md">How many outages were resolved each month.</p>
        <div style="display:flex; gap:1em; margin-bottom:1em; flex-wrap:wrap;">
          <button id="downloadResolvedChartImg" class="btn btn-outline"><i class="fa fa-image"></i> Download Chart</button>
          <button id="downloadResolvedChartCsv" class="btn btn-outline"><i class="fa fa-file-csv"></i> Download CSV</button>
          <button class="btn btn-outline" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        </div>
        <div style="width:100%; max-width:700px; margin:2em auto 1em auto;">
          <canvas id="resolvedChart" height="120"></canvas>
        </div>
      </div>
      <div class="card mb-md" style="box-shadow:0 4px 24px rgba(34,197,94,0.08); border-radius:18px; padding:2.5em 2em; background:#fff; max-width:800px; margin:0 auto 2em auto;">
        <h2 class="h2 mb-sm" style="color:#22c55e;"><i class="fa fa-clock"></i> Avg. Resolution Time Per Month</h2>
        <p class="mb-md">Average time to resolve outages (in hours).</p>
        <div style="display:flex; gap:1em; margin-bottom:1em; flex-wrap:wrap;">
          <button id="downloadAvgResChartImg" class="btn btn-outline"><i class="fa fa-image"></i> Download Chart</button>
          <button id="downloadAvgResChartCsv" class="btn btn-outline"><i class="fa fa-file-csv"></i> Download CSV</button>
          <button class="btn btn-outline" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        </div>
        <div style="width:100%; max-width:700px; margin:2em auto 1em auto;">
          <canvas id="avgResChart" height="120"></canvas>
        </div>
      </div>
      <div class="footer mt-md small text-center" style="color: var(--color-secondary);">
        &copy; 2024 OutageSys. All rights reserved.
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="js/dark-mode.js"></script>
  <script>
    // Simple chart creation - no complex functions
    console.log('Starting chart creation...');
    
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
      console.error('Chart.js is not loaded!');
    } else {
      console.log('Chart.js loaded successfully');
    }
    
    // Outage Trend Chart
    try {
      const outageMonths = <?php echo json_encode($outage_months ?: []); ?>;
      const labels = outageMonths.map(function(r) { return r.month; });
      const data = outageMonths.map(function(r) { return Number(r.count); });
      
      const ctx = document.getElementById('outageTrendChart').getContext('2d');
      window.outageChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            label: 'Outages Started',
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
            title: { display: true, text: 'Outages Started Per Month', color: '#333', font: { size: 18, weight: 'bold' } }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: { color: '#333' },
              grid: { color: '#e0eafc' }
            },
            x: {
              ticks: { color: '#333' },
              grid: { color: '#f4f6f9' }
            }
          }
        }
      });
      console.log('Outage chart created');
    } catch (error) {
      console.error('Error creating outage chart:', error);
    }

    // Resolved Chart
    try {
      const resolvedData = <?php echo json_encode($resolved_per_month ?: []); ?>;
      const resolvedLabels = resolvedData.map(function(r) { return r.month; });
      const resolvedCounts = resolvedData.map(function(r) { return Number(r.count); });
      
      const ctxResolved = document.getElementById('resolvedChart').getContext('2d');
      window.resolvedChart = new Chart(ctxResolved, {
        type: 'bar',
        data: {
          labels: resolvedLabels,
          datasets: [{
            label: 'Outages Resolved',
            data: resolvedCounts,
            backgroundColor: 'rgba(6, 182, 212, 0.7)',
            borderColor: 'rgba(6, 182, 212, 1)',
            borderWidth: 2,
            borderRadius: 8,
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false },
            title: { display: true, text: 'Outages Resolved Per Month', color: '#06b6d4', font: { size: 18, weight: 'bold' } }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: { color: '#333' },
              grid: { color: '#e0eafc' }
            },
            x: {
              ticks: { color: '#333' },
              grid: { color: '#f4f6f9' }
            }
          }
        }
      });
      console.log('Resolved chart created');
    } catch (error) {
      console.error('Error creating resolved chart:', error);
    }

    // Avg Resolution Chart
    try {
      const avgResData = <?php echo json_encode($avg_res_per_month ?: []); ?>;
      const avgResLabels = avgResData.map(function(r) { return r.month; });
      const avgResMinutes = avgResData.map(function(r) { return r.avg_minutes ? Math.round(r.avg_minutes/60*10)/10 : 0; });
      
      const ctxAvgRes = document.getElementById('avgResChart').getContext('2d');
      window.avgResChart = new Chart(ctxAvgRes, {
        type: 'line',
        data: {
          labels: avgResLabels,
          datasets: [{
            label: 'Avg. Resolution Time (hrs)',
            data: avgResMinutes,
            backgroundColor: 'rgba(34, 197, 94, 0.2)',
            borderColor: 'rgba(34, 197, 94, 1)',
            borderWidth: 3,
            pointBackgroundColor: '#fff',
            pointBorderColor: 'rgba(34, 197, 94, 1)',
            pointRadius: 5,
            tension: 0.4,
            fill: true
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false },
            title: { display: true, text: 'Avg. Resolution Time Per Month', color: '#22c55e', font: { size: 18, weight: 'bold' } }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: { color: '#333' },
              grid: { color: '#e0eafc' }
            },
            x: {
              ticks: { color: '#333' },
              grid: { color: '#f4f6f9' }
            }
          }
        }
      });
      console.log('Avg resolution chart created');
    } catch (error) {
      console.error('Error creating avg resolution chart:', error);
    }

    // Simple download functions
    document.getElementById('downloadOutageChartImg').onclick = function() {
      const url = window.outageChart.toBase64Image();
      const link = document.createElement('a');
      link.href = url;
      link.download = 'outages_started_chart.png';
      link.click();
    };
    
    document.getElementById('downloadResolvedChartImg').onclick = function() {
      const url = window.resolvedChart.toBase64Image();
      const link = document.createElement('a');
      link.href = url;
      link.download = 'outages_resolved_chart.png';
      link.click();
    };
    
    document.getElementById('downloadAvgResChartImg').onclick = function() {
      const url = window.avgResChart.toBase64Image();
      const link = document.createElement('a');
      link.href = url;
      link.download = 'avg_resolution_chart.png';
      link.click();
    };
    
    console.log('All charts created successfully');
  </script>
  <!-- Legend Backdrop Overlay and Popup moved here as direct children of body -->
  <div class="legend-backdrop"></div>
  <div class="legend-popup">
    <!-- The full content of the legend popup as before -->
  </div>
</body>
</html>