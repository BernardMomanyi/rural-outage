<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'technician'])) {
  header('Location: login.php');
  exit;
}
require_once 'db.php';

// Simple test data - no complex queries
$outage_months = [['month' => '2024-01', 'count' => 5], ['month' => '2024-02', 'count' => 3]];
$resolved_per_month = [['month' => '2024-01', 'count' => 4], ['month' => '2024-02', 'count' => 2]];
$avg_res_per_month = [['month' => '2024-01', 'avg_minutes' => 120], ['month' => '2024-02', 'avg_minutes' => 90]];

$total_outages = 8;
$resolved_outages = 6;
$open_outages = 2;
$avg_resolution = 1.5;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Analytics MVP - OutageSys</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    body { min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
    .card { background: #fff; border-radius: 16px; padding: 2em; margin-bottom: 2em; box-shadow: 0 8px 32px rgba(0,0,0,0.1); }
    .stat-card { background: #f1f5ff; border-left: 6px solid #2563eb; padding: 1.5em; margin-bottom: 1em; }
    .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; }
    .h2 { color: #2563eb; font-size: 1.5rem; margin-bottom: 1rem; }
    .small { color: #555; font-size: 0.9rem; }
  </style>
</head>
<body>
  <div class="container">
    <h1 style="color: #fff; text-align: center; margin-bottom: 2em;">Analytics MVP</h1>
    
    <!-- Simple Stats -->
    <div class="grid">
      <div class="card stat-card">
        <div style="font-size: 2.2rem; color: #2563eb;"><i class="fa fa-bolt"></i></div>
        <div style="font-size: 2.1rem; font-weight: 700; color: #232946;"><?php echo $total_outages; ?></div>
        <div class="small">Total Outages</div>
      </div>
      <div class="card stat-card">
        <div style="font-size: 2.2rem; color: #06b6d4;"><i class="fa fa-check-circle"></i></div>
        <div style="font-size: 2.1rem; font-weight: 700; color: #232946;"><?php echo $resolved_outages; ?></div>
        <div class="small">Resolved Outages</div>
      </div>
      <div class="card stat-card">
        <div style="font-size: 2.2rem; color: #f59e42;"><i class="fa fa-exclamation-triangle"></i></div>
        <div style="font-size: 2.1rem; font-weight: 700; color: #232946;"><?php echo $open_outages; ?></div>
        <div class="small">Open Outages</div>
      </div>
      <div class="card stat-card">
        <div style="font-size: 2.2rem; color: #22c55e;"><i class="fa fa-clock"></i></div>
        <div style="font-size: 2.1rem; font-weight: 700; color: #232946;"><?php echo $avg_resolution; ?></div>
        <div class="small">Avg. Resolution Time (hrs)</div>
      </div>
    </div>
    
    <!-- Charts -->
    <div class="card">
      <h2 class="h2"><i class="fa fa-chart-line"></i> Outage Trends</h2>
      <p class="small">Monthly breakdown of outages started in the system.</p>
      <canvas id="outageTrendChart" height="120"></canvas>
    </div>
    
    <div class="card">
      <h2 class="h2"><i class="fa fa-check-circle"></i> Outages Resolved Per Month</h2>
      <p class="small">How many outages were resolved each month.</p>
      <canvas id="resolvedChart" height="120"></canvas>
    </div>
    
    <div class="card">
      <h2 class="h2"><i class="fa fa-clock"></i> Avg. Resolution Time Per Month</h2>
      <p class="small">Average time to resolve outages (in hours).</p>
      <canvas id="avgResChart" height="120"></canvas>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    console.log('MVP Script starting...');
    
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
      console.error('Chart.js is not loaded!');
    } else {
      console.log('Chart.js loaded successfully');
    }
    
    try {
      // Test data - hardcoded to avoid PHP issues
      const outageMonths = <?php echo json_encode($outage_months); ?>;
      console.log('Outage months data:', outageMonths);
      
      const labels = outageMonths.map(function(r) { return r.month; });
      const data = outageMonths.map(function(r) { return Number(r.count); });
      console.log('Labels:', labels);
      console.log('Data:', data);
      
      const ctx = document.getElementById('outageTrendChart').getContext('2d');
      console.log('Canvas context:', ctx);
      
      const chart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            label: 'Outages Started',
            data: data,
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false },
            title: { display: true, text: 'Outages Started Per Month' }
          },
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
      
      console.log('Chart created successfully');
      
    } catch (error) {
      console.error('Error creating chart:', error);
    }
  </script>
</body>
</html> 