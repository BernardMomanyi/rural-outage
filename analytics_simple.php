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

// Simple test data
$outage_months = [['month' => '2024-01', 'count' => 5], ['month' => '2024-02', 'count' => 3]];
$resolved_per_month = [['month' => '2024-01', 'count' => 4], ['month' => '2024-02', 'count' => 2]];
$avg_res_per_month = [['month' => '2024-01', 'avg_minutes' => 120], ['month' => '2024-02', 'avg_minutes' => 90]];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Analytics Test - OutageSys</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
</head>
<body>
  <div style="padding: 20px;">
    <h1>Analytics Test</h1>
    
    <div style="margin: 20px 0;">
      <h2>Outage Trends</h2>
      <canvas id="outageTrendChart" width="400" height="200"></canvas>
    </div>
    
    <div style="margin: 20px 0;">
      <h2>Resolved Outages</h2>
      <canvas id="resolvedChart" width="400" height="200"></canvas>
    </div>
    
    <div style="margin: 20px 0;">
      <h2>Avg Resolution Time</h2>
      <canvas id="avgResChart" width="400" height="200"></canvas>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    console.log('Script starting...');
    
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
      console.error('Chart.js is not loaded!');
    } else {
      console.log('Chart.js loaded successfully');
    }
    
    try {
      // Test data
      const outageMonths = <?php echo json_encode($outage_months); ?>;
      console.log('Outage months data:', outageMonths);
      
      const labels = outageMonths.map(r => r.month);
      const data = outageMonths.map(r => Number(r.count));
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