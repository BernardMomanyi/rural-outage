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
  $where[] = 't.created_at BETWEEN ? AND ?';
  $params[] = $start . ' 00:00:00';
  $params[] = $end . ' 23:59:59';
}
// Only add this for tickets table queries
if ($substation && $substation !== 'all') {
  $where[] = 't.location = ?';
  $params[] = $substation;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// For tickets analytics, use location filter instead
$ticket_where = [];
$ticket_params = [];
if ($start && $end) {
  $ticket_where[] = 'created_at BETWEEN ? AND ?';
  $ticket_params[] = $start . ' 00:00:00';
  $ticket_params[] = $end . ' 23:59:59';
}
// Only add this for tickets table queries
if ($substation && $substation !== 'all') {
  $ticket_where[] = 'location = ?';
  $ticket_params[] = $substation;
}
$whereTicketSql = $ticket_where ? ('WHERE ' . implode(' AND ', $ticket_where)) : '';
$baseTicketWhere = $whereTicketSql ? $whereTicketSql : '';
$resolvedTicketWhere = $whereTicketSql ? ($whereTicketSql . ' AND resolved_at IS NOT NULL') : 'WHERE resolved_at IS NOT NULL';
$openTicketWhere = $whereTicketSql ? ($whereTicketSql . ' AND resolved_at IS NULL') : 'WHERE resolved_at IS NULL';
$avgTicketResWhere = $whereTicketSql ? ($whereTicketSql . ' AND resolved_at IS NOT NULL') : 'WHERE resolved_at IS NOT NULL';
$resolvedTicketPerMonthWhere = $whereTicketSql ? ($whereTicketSql . ' AND resolved_at IS NOT NULL') : 'WHERE resolved_at IS NOT NULL';
$avgTicketResPerMonthWhere = $whereTicketSql ? ($whereTicketSql . ' AND resolved_at IS NOT NULL') : 'WHERE resolved_at IS NOT NULL';

$total_tickets = $pdo->prepare("SELECT COUNT(*) FROM tickets t $baseTicketWhere");
$total_tickets->execute($ticket_params);
$total_tickets = $total_tickets->fetchColumn();
$resolved_tickets = $pdo->prepare("SELECT COUNT(*) FROM tickets t $resolvedTicketWhere");
$resolved_tickets->execute($ticket_params);
$resolved_tickets = $resolved_tickets->fetchColumn();
$open_tickets = $pdo->prepare("SELECT COUNT(*) FROM tickets t $openTicketWhere");
$open_tickets->execute($ticket_params);
$open_tickets = $open_tickets->fetchColumn();
$avg_ticket_resolution = $pdo->prepare("SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, resolved_at)) FROM tickets t $avgTicketResWhere");
$avg_ticket_resolution->execute($ticket_params);
$avg_ticket_resolution = $avg_ticket_resolution->fetchColumn();
$ticket_months = $pdo->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM tickets t $baseTicketWhere GROUP BY month ORDER BY month ASC");
$ticket_months->execute($ticket_params);
$ticket_months = $ticket_months->fetchAll(PDO::FETCH_ASSOC);
$resolved_ticket_per_month = $pdo->prepare("SELECT DATE_FORMAT(resolved_at, '%Y-%m') as month, COUNT(*) as count FROM tickets t $resolvedTicketPerMonthWhere GROUP BY month ORDER BY month ASC");
$resolved_ticket_per_month->execute($ticket_params);
$resolved_ticket_per_month = $resolved_ticket_per_month->fetchAll(PDO::FETCH_ASSOC);
$avg_ticket_res_per_month = $pdo->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, AVG(TIMESTAMPDIFF(MINUTE, created_at, resolved_at)) as avg_minutes FROM tickets t $avgTicketResPerMonthWhere GROUP BY month ORDER BY month ASC");
$avg_ticket_res_per_month->execute($ticket_params);
$avg_ticket_res_per_month = $avg_ticket_res_per_month->fetchAll(PDO::FETCH_ASSOC);
// --- END TICKETS ANALYTICS ---

// --- OUTAGES ANALYTICS ---
$outage_where = [];
$outage_params = [];
if ($start && $end) {
  $outage_where[] = 'o.start_time BETWEEN ? AND ?';
  $outage_params[] = $start . ' 00:00:00';
  $outage_params[] = $end . ' 23:59:59';
}
if ($substation && $substation !== 'all') {
  $outage_where[] = 'o.substation_id = ?';
  $outage_params[] = $substation;
}
$outageWhereSql = $outage_where ? ('WHERE ' . implode(' AND ', $outage_where)) : '';
$outage_resolved_where = $outageWhereSql ? ($outageWhereSql . " AND o.end_time IS NOT NULL") : "WHERE o.end_time IS NOT NULL";
$outage_open_where = $outageWhereSql ? ($outageWhereSql . " AND o.end_time IS NULL") : "WHERE o.end_time IS NULL";
$outage_avgres_where = $outageWhereSql ? ($outageWhereSql . " AND o.end_time IS NOT NULL") : "WHERE o.end_time IS NOT NULL";
$outage_resolved_per_month_where = $outageWhereSql ? ($outageWhereSql . " AND o.end_time IS NOT NULL") : "WHERE o.end_time IS NOT NULL";
$outage_avg_res_per_month_where = $outageWhereSql ? ($outageWhereSql . " AND o.end_time IS NOT NULL") : "WHERE o.end_time IS NOT NULL";

$total_outages = $pdo->prepare("SELECT COUNT(*) FROM outages o $outageWhereSql");
$total_outages->execute($outage_params);
$total_outages = $total_outages->fetchColumn();
$resolved_outages = $pdo->prepare("SELECT COUNT(*) FROM outages o $outage_resolved_where");
$resolved_outages->execute($outage_params);
$resolved_outages = $resolved_outages->fetchColumn();
$open_outages = $pdo->prepare("SELECT COUNT(*) FROM outages o $outage_open_where");
$open_outages->execute($outage_params);
$open_outages = $open_outages->fetchColumn();
$avg_outage_resolution = $pdo->prepare("SELECT AVG(TIMESTAMPDIFF(MINUTE, o.start_time, o.end_time)) FROM outages o $outage_avgres_where");
$avg_outage_resolution->execute($outage_params);
$avg_outage_resolution = $avg_outage_resolution->fetchColumn();
$outage_months = $pdo->prepare("SELECT DATE_FORMAT(o.start_time, '%Y-%m') as month, COUNT(*) as count FROM outages o $outageWhereSql GROUP BY month ORDER BY month ASC");
$outage_months->execute($outage_params);
$outage_months = $outage_months->fetchAll(PDO::FETCH_ASSOC);
$resolved_per_month = $pdo->prepare("SELECT DATE_FORMAT(o.end_time, '%Y-%m') as month, COUNT(*) as count FROM outages o $outage_resolved_per_month_where GROUP BY month ORDER BY month ASC");
$resolved_per_month->execute($outage_params);
$resolved_per_month = $resolved_per_month->fetchAll(PDO::FETCH_ASSOC);
$avg_res_per_month = $pdo->prepare("SELECT DATE_FORMAT(o.start_time, '%Y-%m') as month, AVG(TIMESTAMPDIFF(MINUTE, o.start_time, o.end_time)) as avg_minutes FROM outages o $outage_avg_res_per_month_where GROUP BY month ORDER BY month ASC");
$avg_res_per_month->execute($outage_params);
$avg_res_per_month = $avg_res_per_month->fetchAll(PDO::FETCH_ASSOC);
// --- END OUTAGES ANALYTICS ---
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
  </style>
</head>
<body>
  <div class="main-content">
  <!-- Breadcrumbs navigation -->
  <nav aria-label="Breadcrumb" class="breadcrumbs-nav" style="margin: 1.5rem auto 0 auto; max-width:900px; background:transparent;">
    <ol class="breadcrumbs" style="display:flex; flex-wrap:wrap; gap:0.5em; list-style:none; padding:0; margin:0; font-size:1rem; background:transparent;">
      <li><a href="<?php echo $dashboard_link; ?>" class="breadcrumb-link"><i class="fa fa-tachometer-alt"></i> <?php echo ($role === 'admin') ? 'Admin Dashboard' : (($role === 'technician') ? 'Technician Dashboard' : 'Dashboard'); ?></a></li>
      <li style="color:var(--color-secondary);">&gt;</li>
      <li class="breadcrumb-current" style="color:var(--color-primary); font-weight:600;"><i class="fa fa-chart-bar"></i> Analytics</li>
    </ol>
  </nav>

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
  <!-- Wrap all analytics content in a single parent container for consistent centering and sizing -->
<div class="analytics-main-container" style="max-width:900px; margin:2rem auto; background:#fff; border-radius:12px; box-shadow:0 4px 24px rgba(38,99,235,0.08); padding:2rem 2rem 2.5rem 2rem;">
  <div class="grid stats mb-md">
      <div class="card stat-card">
        <div style="font-size:2.2rem; color:#2563eb;"><i class="fa fa-bolt"></i></div>
        <div style="font-size:2.1rem; font-weight:700; color:#232946;" class="stat-number"><?php echo $total_outages; ?></div>
        <div class="small" style="color:#555;" class="stat-label">Total Outages</div>
      </div>
      <div class="card stat-card">
        <div style="font-size:2.2rem; color:#06b6d4;"><i class="fa fa-check-circle"></i></div>
        <div style="font-size:2.1rem; font-weight:700; color:#232946;" class="stat-number"><?php echo $resolved_outages; ?></div>
        <div class="small" style="color:#555;" class="stat-label">Resolved Outages</div>
      </div>
      <div class="card stat-card">
        <div style="font-size:2.2rem; color:#f59e42;"><i class="fa fa-exclamation-triangle"></i></div>
        <div style="font-size:2.1rem; font-weight:700; color:#232946;" class="stat-number"><?php echo $open_outages; ?></div>
        <div class="small" style="color:#555;" class="stat-label">Open Outages</div>
      </div>
      <div class="card stat-card">
        <div style="font-size:2.2rem; color:#22c55e;"><i class="fa fa-clock"></i></div>
        <div style="font-size:2.1rem; font-weight:700; color:#232946;" class="stat-number"><?php echo $avg_outage_resolution ? round($avg_outage_resolution/60,1) : '0'; ?></div>
        <div class="small" style="color:#555;" class="stat-label">Avg. Resolution Time (hrs)</div>
      </div>
    </div>
    <div class="card mb-md">
      <h2 class="h2 mb-sm" style="color:#2563eb;"><i class="fa fa-chart-line"></i> Outage Trends</h2>
      <p class="mb-md">Monthly breakdown of outages reported in the system.</p>
      <div style="display:flex; gap:1em; margin-bottom:1em; flex-wrap:wrap;">
        <button id="downloadOutageChartImg" class="btn btn-outline"><i class="fa fa-image"></i> Download Chart</button>
        <button id="downloadOutageChartCsv" class="btn btn-outline"><i class="fa fa-file-csv"></i> Download CSV</button>
        <button class="btn btn-outline" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
      </div>
      <div>
        <canvas id="outageTrendChart" height="120"></canvas>
      </div>
    </div>
    <div class="card mb-md">
      <h2 class="h2 mb-sm" style="color:#06b6d4;"><i class="fa fa-check-circle"></i> Outages Resolved Per Month</h2>
      <p class="mb-md">How many outages were resolved each month.</p>
      <div style="display:flex; gap:1em; margin-bottom:1em; flex-wrap:wrap;">
        <button id="downloadResolvedOutageChartImg" class="btn btn-outline"><i class="fa fa-image"></i> Download Chart</button>
        <button id="downloadResolvedOutageChartCsv" class="btn btn-outline"><i class="fa fa-file-csv"></i> Download CSV</button>
        <button class="btn btn-outline" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
      </div>
      <div>
        <canvas id="resolvedOutageChart" height="120"></canvas>
      </div>
    </div>
    <div class="card mb-md">
      <h2 class="h2 mb-sm" style="color:#22c55e;"><i class="fa fa-clock"></i> Avg. Outage Resolution Time Per Month</h2>
      <p class="mb-md">Average time to resolve outages (in hours).</p>
      <div style="display:flex; gap:1em; margin-bottom:1em; flex-wrap:wrap;">
        <button id="downloadAvgOutageResChartImg" class="btn btn-outline"><i class="fa fa-image"></i> Download Chart</button>
        <button id="downloadAvgOutageResChartCsv" class="btn btn-outline"><i class="fa fa-file-csv"></i> Download CSV</button>
        <button class="btn btn-outline" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
      </div>
      <div>
        <canvas id="avgOutageResChart" height="120"></canvas>
      </div>
    </div>
  </div>
</div>
  <div class="footer mt-md small text-center" style="color: var(--color-secondary);">
    &copy; 2024 OutageSys. All rights reserved;
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Utility for gradient backgrounds
function getGradient(ctx, color1, color2) {
  const gradient = ctx.createLinearGradient(0, 0, 0, 400);
  gradient.addColorStop(0, color1);
  gradient.addColorStop(1, color2);
  return gradient;
}
// --- OUTAGES CHARTS ---
(function() {
  // Outage Trends
  const data = <?php echo json_encode($outage_months); ?>;
  const labels = data.map(r => r.month);
  const counts = data.map(r => Number(r.count));
  const ctx = document.getElementById('outageTrendChart').getContext('2d');
  const gradient = getGradient(ctx, '#2563eb', '#06b6d4');
  new Chart(ctx, {
    type: 'bar',
    data: { labels: labels, datasets: [{ label: 'Outages Reported', data: counts, backgroundColor: gradient, borderRadius: 12, borderSkipped: false, hoverBackgroundColor: '#2563eb' }] },
    options: { responsive: true, plugins: { legend: { display: false }, title: { display: true, text: 'Outages Reported Per Month', color: '#333', font: { size: 18, weight: 'bold' } }, tooltip: { enabled: true } }, scales: { y: { beginAtZero: true, ticks: { color: '#333' }, grid: { color: '#e0eafc' } }, x: { ticks: { color: '#333' }, grid: { color: '#f4f6f9' } } } }
  });
  // Outages Resolved
  const data2 = <?php echo json_encode($resolved_per_month); ?>;
  const labels2 = data2.map(r => r.month);
  const counts2 = data2.map(r => Number(r.count));
  const ctx2 = document.getElementById('resolvedOutageChart').getContext('2d');
  const gradient2 = getGradient(ctx2, '#06b6d4', '#22c55e');
  new Chart(ctx2, {
    type: 'bar',
    data: { labels: labels2, datasets: [{ label: 'Outages Resolved', data: counts2, backgroundColor: gradient2, borderRadius: 12, borderSkipped: false, hoverBackgroundColor: '#06b6d4' }] },
    options: { responsive: true, plugins: { legend: { display: false }, title: { display: true, text: 'Outages Resolved Per Month', color: '#333', font: { size: 18, weight: 'bold' } }, tooltip: { enabled: true } }, scales: { y: { beginAtZero: true, ticks: { color: '#333' }, grid: { color: '#e0eafc' } }, x: { ticks: { color: '#333' }, grid: { color: '#f4f6f9' } } } }
  });
  // Avg. Outage Resolution Time
  const data3 = <?php echo json_encode($avg_res_per_month); ?>;
  const labels3 = data3.map(r => r.month);
  const mins3 = data3.map(r => Math.round(Number(r.avg_minutes) / 60 * 10) / 10);
  const ctx3 = document.getElementById('avgOutageResChart').getContext('2d');
  const gradient3 = getGradient(ctx3, '#22c55e', '#f0fdf4');
  new Chart(ctx3, {
    type: 'line',
    data: { labels: labels3, datasets: [{ label: 'Avg. Outage Resolution Time (hrs)', data: mins3, fill: true, backgroundColor: gradient3, borderColor: '#22c55e', tension: 0.3, pointRadius: 5, pointBackgroundColor: '#22c55e' }] },
    options: { responsive: true, plugins: { legend: { display: false }, title: { display: true, text: 'Avg. Outage Resolution Time (hrs)', color: '#333', font: { size: 18, weight: 'bold' } }, tooltip: { enabled: true } }, scales: { y: { beginAtZero: true, ticks: { color: '#333' }, grid: { color: '#e0eafc' } }, x: { ticks: { color: '#333' }, grid: { color: '#f4f6f9' } } } }
  });
})();
</script>
</body>
</html>