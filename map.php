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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Map View - OutageSys</title>
  <link rel="stylesheet" href="css/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    #map { height: 500px; border-radius: 16px; margin-bottom: 18px; }
    .legend { max-width: 400px; margin: 0 auto 18px auto; }
    .legend-row { display: flex; gap: 18px; margin-top: 8px; }
    .legend-item { display: flex; align-items: center; }
    .legend-dot { display:inline-block;width:16px;height:16px;border-radius:50%;margin-right:6px; }
    .dot-green { background:green; }
    .dot-orange { background:orange; }
    .dot-red { background:red; }
  </style>
</head>
<body class="<?php echo htmlspecialchars($role); ?>">
  <div class="dashboard">
    <?php include 'sidebar.php'; ?>
    <main class="main-content" role="main">
      <nav aria-label="Breadcrumb" class="breadcrumb">
        <ol>
          <li><a href="index.php">Home</a></li>
          <li>›</li>
          <li>Map View</li>
        </ol>
      </nav>
      <div class="dashboard-grid">
        <div class="card">
          <h2>Map View</h2>
          <div id="map"></div>
          <div class="card legend">
            <b>Legend:</b>
            <div class="legend-row">
              <span class="legend-item"><span class="legend-dot dot-green"></span>Online</span>
              <span class="legend-item"><span class="legend-dot dot-orange"></span>Medium Risk</span>
              <span class="legend-item"><span class="legend-dot dot-red"></span>Offline/High Risk</span>
            </div>
          </div>
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
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script>
    fetch('api/substations.php', { credentials: 'same-origin' })
      .then(res => res.json())
      .then(subs => {
        const map = L.map('map').setView([0.0236, 37.9062], 6); // Center Kenya
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 18,
          attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        subs.forEach(s => {
          if (s.latitude && s.longitude) {
            let color = 'green';
            if (s.status && s.status.toLowerCase() === 'offline' || s.risk && s.risk.toLowerCase() === 'high') color = 'red';
            else if (s.risk && s.risk.toLowerCase() === 'medium') color = 'orange';
            const icon = L.divIcon({
              className: '',
              html: `<span class="map-marker" style="background:${color};"></span>`
            });
            L.marker([s.latitude, s.longitude], { icon })
              .addTo(map)
              .bindPopup(`<b>${s.name}</b><br>Status: ${s.status}<br>Risk: ${s.risk}<br>County: ${s.county}<br>Maintenance: ${s.maintenance_date || 'N/A'}`);
          }
        });
      });
  </script>
</body>
</html> 