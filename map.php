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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    body { 
      min-height: 100vh; 
      margin: 0;
      font-family: 'Inter', sans-serif;
    }
    
    .map-bg {
      min-height: 100vh;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 0;
    }
    
    .container {
      width: 100%;
      max-width: 1200px;
      padding: 2rem;
    }
    
    .card {
      background: #fff;
      border-radius: 18px;
      padding: 2.5em 2em;
      margin-bottom: 2em;
      box-shadow: 0 8px 32px rgba(0,0,0,0.1);
      border: 1px solid rgba(255,255,255,0.2);
    }
    
    .h2 {
      color: #2563eb;
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .small {
      color: #555;
      font-size: 0.9rem;
      margin-bottom: 1.5rem;
    }
    
    #map { 
      height: 600px; 
      border-radius: 16px; 
      margin-bottom: 1.5rem;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    
    .controls-card {
      background: #f8fafc;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      border-left: 4px solid #2563eb;
    }
    
    .controls-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      align-items: end;
    }
    
    .control-group {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }
    
    .control-label {
      font-weight: 600;
      color: #374151;
      font-size: 0.9rem;
    }
    
    .control-input {
      padding: 0.75rem;
      border: 2px solid #e5e7eb;
      border-radius: 8px;
      font-size: 0.9rem;
      transition: border-color 0.2s;
    }
    
    .control-input:focus {
      outline: none;
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    }
    
    .btn {
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      font-size: 0.9rem;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .btn-primary {
      background: #2563eb;
      color: white;
    }
    
    .btn-primary:hover {
      background: #1d4ed8;
      transform: translateY(-1px);
    }
    
    .btn-outline {
      background: transparent;
      color: #2563eb;
      border: 2px solid #2563eb;
    }
    
    .btn-outline:hover {
      background: #2563eb;
      color: white;
    }
    
    .btn-success {
      background: #22c55e;
      color: white;
    }
    
    .btn-success:hover {
      background: #16a34a;
    }
    
    .legend-card {
      background: #f8fafc;
      border-radius: 12px;
      padding: 1.5rem;
      margin-top: 1rem;
      border-left: 4px solid #2563eb;
    }
    
    .legend-title {
      font-weight: 600;
      color: #374151;
      margin-bottom: 1rem;
      font-size: 1.1rem;
    }
    
    .legend-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
    }
    
    .legend-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem;
      border-radius: 8px;
      background: #fff;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
      cursor: pointer;
      transition: all 0.2s;
      position: relative;
    }
    
    .legend-item:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .legend-item.active {
      background: #dbeafe;
      border: 2px solid #2563eb;
    }
    
    .legend-item.inactive {
      opacity: 0.5;
      background: #f3f4f6;
    }
    
    .legend-dot {
      width: 16px;
      height: 16px;
      border-radius: 50%;
      flex-shrink: 0;
    }
    
    .dot-green { background: #22c55e; }
    .dot-orange { background: #f59e42; }
    .dot-red { background: #ef4444; }
    
    .legend-text {
      font-size: 0.9rem;
      color: #374151;
      font-weight: 500;
      flex-grow: 1;
    }
    
    .legend-count {
      font-size: 0.8rem;
      color: #666;
      font-weight: 500;
      margin-left: auto;
      background: #f3f4f6;
      padding: 0.2rem 0.5rem;
      border-radius: 12px;
      min-width: 2rem;
      text-align: center;
    }
    
    .legend-item.active .legend-count {
      background: #2563eb;
      color: white;
    }
    
    /* Enhanced button styles for map controls */
    .btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
    
    .btn:disabled:hover {
      transform: none;
    }
    
    /* Loading animation for buttons */
    .btn.loading {
      position: relative;
      color: transparent;
    }
    
    .btn.loading::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 16px;
      height: 16px;
      margin: -8px 0 0 -8px;
      border: 2px solid transparent;
      border-top: 2px solid currentColor;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    
    /* Filter indicator styles */
    .control-input.active-filter {
      border-color: #2563eb !important;
      background-color: #f0f9ff !important;
    }
    
    .legend-item.active-filter {
      border-color: #2563eb !important;
      background-color: #dbeafe !important;
    }
    
    /* Enhanced notification styles */
    .notification {
      position: fixed;
      top: 20px;
      right: 20px;
      background: #22c55e;
      color: white;
      padding: 1rem 1.5rem;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 1000;
      transform: translateX(100%);
      transition: transform 0.3s ease;
      max-width: 300px;
      word-wrap: break-word;
    }
    
    .notification.show {
      transform: translateX(0);
    }
    
    .notification.error {
      background: #ef4444;
    }
    
    .notification.warning {
      background: #f59e42;
    }
    
    .notification.info {
      background: #3b82f6;
    }
    
    .loading {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid #f3f3f3;
      border-top: 3px solid #2563eb;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }
    
    .stat-card {
      background: linear-gradient(135deg, #f1f5ff 0%, #e0eafc 100%);
      border-radius: 16px;
      padding: 1.5rem;
      border-left: 6px solid #2563eb;
      box-shadow: 0 4px 12px rgba(37,99,235,0.1);
      transition: all 0.3s ease;
    }
    
    .stat-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 25px rgba(37,99,235,0.15);
    }
    
    .stat-icon {
      font-size: 2rem;
      color: #2563eb;
      margin-bottom: 0.5rem;
    }
    
    .stat-number {
      font-size: 2rem;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 0.25rem;
    }
    
    .stat-label {
      color: #64748b;
      font-size: 0.9rem;
      font-weight: 500;
    }
    
    .pulse {
      animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
      0% { opacity: 1; }
      50% { opacity: 0.5; }
      100% { opacity: 1; }
    }
    
    /* Dark mode support */
    body.dark-mode .map-bg {
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%) !important;
    }
    
    body.dark-mode .card {
      background: #2d3748 !important;
      color: #e2e8f0 !important;
      border-color: #4a5568 !important;
    }
    
    body.dark-mode .controls-card {
      background: #374151 !important;
      color: #e2e8f0 !important;
    }
    
    body.dark-mode .legend-card {
      background: #374151 !important;
      color: #e2e8f0 !important;
    }
    
    body.dark-mode .legend-item {
      background: #4a5568 !important;
      color: #e2e8f0 !important;
    }
    
    body.dark-mode .legend-item.active {
      background: #2d3748 !important;
      border-color: #63b3ed !important;
    }
    
    body.dark-mode .stat-card {
      background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%) !important;
      color: #e2e8f0 !important;
    }
    
    body.dark-mode .h2 {
      color: #63b3ed !important;
    }
    
    body.dark-mode .small {
      color: #cbd5e0 !important;
    }
    
    body.dark-mode .legend-title {
      color: #e2e8f0 !important;
    }
    
    body.dark-mode .legend-text {
      color: #e2e8f0 !important;
    }
    
    body.dark-mode .stat-number {
      color: #f7fafc !important;
    }
    
    body.dark-mode .stat-label {
      color: #a0aec0 !important;
    }
    
    body.dark-mode .control-label {
      color: #e2e8f0 !important;
    }
    
    body.dark-mode .control-input {
      background: #4a5568 !important;
      color: #e2e8f0 !important;
      border-color: #718096 !important;
    }
    
    /* Dark mode filter indicators */
    body.dark-mode .control-input.active-filter {
      border-color: #63b3ed !important;
      background-color: #2d3748 !important;
    }
    
    body.dark-mode .legend-item.active-filter {
      border-color: #63b3ed !important;
      background-color: #2d3748 !important;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
      .container {
        padding: 1rem;
      }
      
      .card {
        padding: 1.5rem 1rem;
      }
      
      #map {
        height: 400px;
      }
      
      .legend-grid {
        grid-template-columns: 1fr;
      }
      
      .stats-grid {
        grid-template-columns: 1fr;
      }
      
      .controls-grid {
        grid-template-columns: 1fr;
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
      <li class="breadcrumb-current" style="color:var(--color-primary); font-weight:600;"><i class="fa fa-map"></i> Map View</li>
    </ol>
  </nav>

  <div class="map-bg">
    <div class="container">
      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="card stat-card">
          <div class="stat-icon"><i class="fa fa-map-marker-alt"></i></div>
          <div class="stat-number" id="totalSubstations">Loading...</div>
          <div class="stat-label">Total Substations</div>
        </div>
        <div class="card stat-card">
          <div class="stat-icon"><i class="fa fa-check-circle"></i></div>
          <div class="stat-number" id="onlineSubstations">Loading...</div>
          <div class="stat-label">Online Substations</div>
        </div>
        <div class="card stat-card">
          <div class="stat-icon"><i class="fa fa-exclamation-triangle"></i></div>
          <div class="stat-number" id="highRiskSubstations">Loading...</div>
          <div class="stat-label">High Risk Substations</div>
        </div>
        <div class="card stat-card">
          <div class="stat-icon"><i class="fa fa-clock"></i></div>
          <div class="stat-number" id="offlineSubstations">Loading...</div>
          <div class="stat-label">Offline Substations</div>
        </div>
      </div>

      <!-- Controls Card -->
      <div class="card controls-card">
        <h3 style="margin-bottom: 1rem; color: #2563eb; font-size: 1.2rem;">
          <i class="fa fa-sliders-h"></i>
          Map Controls
        </h3>
        <div class="controls-grid">
          <div class="control-group">
            <label class="control-label">Search Substations</label>
            <input type="text" id="searchInput" class="control-input" placeholder="Search by name or county...">
          </div>
          <div class="control-group">
            <label class="control-label">Filter by Status</label>
            <select id="statusFilter" class="control-input">
              <option value="all">All Status</option>
              <option value="online">Online</option>
              <option value="offline">Offline</option>
            </select>
          </div>
          <div class="control-group">
            <label class="control-label">Filter by Risk</label>
            <select id="riskFilter" class="control-input">
              <option value="all">All Risk Levels</option>
              <option value="low">Low Risk</option>
              <option value="medium">Medium Risk</option>
              <option value="high">High Risk</option>
            </select>
          </div>
          <div class="control-group">
            <label class="control-label">Map Controls</label>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
              <button id="zoomInBtn" class="btn btn-outline" title="Zoom In">
                <i class="fa fa-plus"></i>
              </button>
              <button id="zoomOutBtn" class="btn btn-outline" title="Zoom Out">
                <i class="fa fa-minus"></i>
              </button>
              <button id="fitBoundsBtn" class="btn btn-outline" title="Fit to Markers">
                <i class="fa fa-expand"></i>
              </button>
              <button id="toggleClusteringBtn" class="btn btn-outline" title="Toggle Clustering">
                <i class="fa fa-object-group"></i>
              </button>
            </div>
          </div>
          <div class="control-group">
            <label class="control-label">Actions</label>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
              <button id="refreshBtn" class="btn btn-primary">
                <i class="fa fa-sync-alt"></i>
                Refresh
              </button>
              <button id="exportBtn" class="btn btn-outline">
                <i class="fa fa-download"></i>
                Export
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Map Card -->
      <div class="card">
        <h2 class="h2">
          <i class="fa fa-map"></i>
          Substation Map
        </h2>
        <p class="small">Interactive map showing all substations across the network with real-time status indicators.</p>
        
        <div id="map"></div>
        
        <div class="legend-card">
          <div class="legend-title">
            <i class="fa fa-info-circle"></i>
            Map Legend (Click to filter)
          </div>
          <div class="legend-grid">
            <div class="legend-item active" data-filter="green">
              <span class="legend-dot dot-green"></span>
              <span class="legend-text">Online & Low Risk</span>
              <span class="legend-count">(0)</span>
            </div>
            <div class="legend-item active" data-filter="orange">
              <span class="legend-dot dot-orange"></span>
              <span class="legend-text">Medium Risk</span>
              <span class="legend-count">(0)</span>
            </div>
            <div class="legend-item active" data-filter="red">
              <span class="legend-dot dot-red"></span>
              <span class="legend-text">Offline or High Risk</span>
              <span class="legend-count">(0)</span>
            </div>
          </div>
          <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
            <button id="selectAllLegendBtn" class="btn btn-outline" style="font-size: 0.8rem; padding: 0.5rem 1rem;">
              <i class="fa fa-check-square"></i> Select All
            </button>
            <button id="deselectAllLegendBtn" class="btn btn-outline" style="font-size: 0.8rem; padding: 0.5rem 1rem; margin-left: 0.5rem;">
              <i class="fa fa-square"></i> Deselect All
            </button>
          </div>
        </div>
      </div>

      <div class="footer mt-md small text-center" style="color: var(--color-secondary);">
        &copy; 2024 OutageSys. All rights reserved.
      </div>
    </div>
  </div>

  <!-- Notification -->
  <div id="notification" class="notification" style="display: none;">
    <i class="fa fa-check-circle"></i>
    <span id="notificationText"></span>
  </div>

  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
  <script src="js/dark-mode.js"></script>
  <script>
    // Global variables
    let map, markers = [], markerClusterGroup;
    let allSubstations = [];
    let filteredSubstations = [];
    let clusteringEnabled = true;
    
    // Initialize map and load data
    console.log('Loading map data...');
    
    function showNotification(message, type = 'success') {
      const notification = document.getElementById('notification');
      const notificationText = document.getElementById('notificationText');
      
      notificationText.textContent = message;
      notification.style.background = type === 'success' ? '#22c55e' : '#ef4444';
      notification.style.display = 'block';
      
      setTimeout(() => {
        notification.classList.add('show');
      }, 100);
      
      setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
          notification.style.display = 'none';
        }, 300);
      }, 3000);
    }
    
    function initializeMap() {
      // Initialize map centered on Kenya
      map = L.map('map').setView([0.0236, 37.9062], 6);
      
      // Add tile layer
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '© OpenStreetMap contributors'
      }).addTo(map);
      
      // Initialize marker cluster group
      markerClusterGroup = L.markerClusterGroup({
        chunkedLoading: true,
        maxClusterRadius: 50,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: true,
        zoomToBoundsOnClick: true
      });
      map.addLayer(markerClusterGroup);
    }
    
    function createMarker(substation) {
      let color = 'green';
      let status = substation.status || 'Unknown';
      let risk = substation.risk || 'Unknown';
      
      // Determine marker color based on status and risk
      if (status.toLowerCase() === 'offline' || risk.toLowerCase() === 'high') {
        color = 'red';
      } else if (risk.toLowerCase() === 'medium') {
        color = 'orange';
      }
      
      // Create custom marker
      const icon = L.divIcon({
        className: 'custom-marker',
        html: `<div style="
          width: 20px; 
          height: 20px; 
          background: ${color}; 
          border: 2px solid white; 
          border-radius: 50%; 
          box-shadow: 0 2px 4px rgba(0,0,0,0.3);
          cursor: pointer;
        "></div>`,
        iconSize: [20, 20],
        iconAnchor: [10, 10]
      });
      
      // Add marker to map
      const marker = L.marker([substation.latitude, substation.longitude], { icon })
        .bindPopup(`
          <div style="min-width: 200px;">
            <h3 style="margin: 0 0 8px 0; color: #2563eb; font-size: 1.1rem;">${substation.name}</h3>
            <p style="margin: 4px 0; font-size: 0.9rem;"><strong>Status:</strong> ${status}</p>
            <p style="margin: 4px 0; font-size: 0.9rem;"><strong>Risk Level:</strong> ${risk}</p>
            <p style="margin: 4px 0; font-size: 0.9rem;"><strong>County:</strong> ${substation.county || 'N/A'}</p>
            <p style="margin: 4px 0; font-size: 0.9rem;"><strong>Maintenance:</strong> ${substation.maintenance_date || 'N/A'}</p>
          </div>
        `);
      
      return marker;
    }
    
    function updateStats() {
      let onlineCount = 0;
      let highRiskCount = 0;
      let offlineCount = 0;
      
      filteredSubstations.forEach(s => {
        let status = s.status || 'Unknown';
        let risk = s.risk || 'Unknown';
        
        if (status.toLowerCase() === 'offline' || risk.toLowerCase() === 'high') {
          offlineCount++;
        } else {
          onlineCount++;
        }
        
        if (risk.toLowerCase() === 'high') {
          highRiskCount++;
        }
      });
      
      document.getElementById('totalSubstations').textContent = filteredSubstations.length;
      document.getElementById('onlineSubstations').textContent = onlineCount;
      document.getElementById('highRiskSubstations').textContent = highRiskCount;
      document.getElementById('offlineSubstations').textContent = offlineCount;
    }
    
    function filterMarkers() {
      // Clear existing markers
      markerClusterGroup.clearLayers();
      markers = [];
      
      // Add filtered markers
      filteredSubstations.forEach(s => {
        if (s.latitude && s.longitude) {
          const marker = createMarker(s);
          markers.push(marker);
          markerClusterGroup.addLayer(marker);
        }
      });
      
      // If clustering is disabled, remove from cluster and add directly to map
      if (!clusteringEnabled) {
        markerClusterGroup.clearLayers();
        markers.forEach(marker => map.addLayer(marker));
      }
      
      updateStats();
    }
    
    function applyFilters() {
      const searchTerm = document.getElementById('searchInput').value.toLowerCase();
      const statusFilter = document.getElementById('statusFilter').value;
      const riskFilter = document.getElementById('riskFilter').value;
      
      // Get active legend filters
      const activeLegendFilters = [];
      document.querySelectorAll('.legend-item.active').forEach(item => {
        activeLegendFilters.push(item.dataset.filter);
      });
      
      console.log('Filtering with:', { searchTerm, statusFilter, riskFilter, activeLegendFilters });
      
      // Update visual feedback for active filters
      updateFilterIndicators(searchTerm, statusFilter, riskFilter, activeLegendFilters);
      
      filteredSubstations = allSubstations.filter(s => {
        // Search filter
        const matchesSearch = !searchTerm || 
                            s.name.toLowerCase().includes(searchTerm) || 
                            (s.county && s.county.toLowerCase().includes(searchTerm));
        
        // Status filter
        const matchesStatus = statusFilter === 'all' || 
                            (s.status && s.status.toLowerCase() === statusFilter);
        
        // Risk filter
        const matchesRisk = riskFilter === 'all' || 
                           (s.risk && s.risk.toLowerCase() === riskFilter);
        
        // Legend filter logic - if no legend items are active, show all
        let matchesLegend = true;
        if (activeLegendFilters.length > 0) {
          const markerColor = getMarkerColor(s);
          matchesLegend = activeLegendFilters.includes(markerColor);
        }
        
        const result = matchesSearch && matchesStatus && matchesRisk && matchesLegend;
        
        // Debug logging
        if (searchTerm || statusFilter !== 'all' || riskFilter !== 'all') {
          console.log(`Substation ${s.name}: search=${matchesSearch}, status=${matchesStatus}, risk=${matchesRisk}, legend=${matchesLegend}, result=${result}`);
        }
        
        return result;
      });
      
      filterMarkers();
      updateLegendCounts();
      showNotification(`Showing ${filteredSubstations.length} of ${allSubstations.length} substations`);
    }
    
    function updateFilterIndicators(searchTerm, statusFilter, riskFilter, activeLegendFilters) {
      // Update search input styling
      const searchInput = document.getElementById('searchInput');
      if (searchTerm) {
        searchInput.classList.add('active-filter');
      } else {
        searchInput.classList.remove('active-filter');
      }
      
      // Update status filter styling
      const statusSelect = document.getElementById('statusFilter');
      if (statusFilter !== 'all') {
        statusSelect.classList.add('active-filter');
      } else {
        statusSelect.classList.remove('active-filter');
      }
      
      // Update risk filter styling
      const riskSelect = document.getElementById('riskFilter');
      if (riskFilter !== 'all') {
        riskSelect.classList.add('active-filter');
      } else {
        riskSelect.classList.remove('active-filter');
      }
      
      // Update legend items styling
      document.querySelectorAll('.legend-item').forEach(item => {
        if (item.classList.contains('active')) {
          item.classList.add('active-filter');
        } else {
          item.classList.remove('active-filter');
        }
      });
    }
    
    function getMarkerColor(substation) {
      let status = substation.status || 'Unknown';
      let risk = substation.risk || 'Unknown';
      
      console.log(`Getting color for ${substation.name}: status=${status}, risk=${risk}`);
      
      if (status.toLowerCase() === 'offline' || risk.toLowerCase() === 'high') {
        return 'red';
      } else if (risk.toLowerCase() === 'medium') {
        return 'orange';
      } else {
        return 'green';
      }
    }
    
    function updateLegendCounts() {
      const legendItems = document.querySelectorAll('.legend-item');
      
      legendItems.forEach(item => {
        const filterType = item.dataset.filter;
        let count = 0;
        
        allSubstations.forEach(s => {
          if (getMarkerColor(s) === filterType) {
            count++;
          }
        });
        
        // Update the count display
        const countSpan = item.querySelector('.legend-count');
        if (countSpan) {
          countSpan.textContent = `(${count})`;
        } else {
          // Create count span if it doesn't exist
          const countSpan = document.createElement('span');
          countSpan.className = 'legend-count';
          countSpan.textContent = `(${count})`;
          countSpan.style.cssText = 'margin-left: auto; font-size: 0.8rem; color: #666; font-weight: 500;';
          item.appendChild(countSpan);
        }
      });
    }
    
    function clearAllFilters() {
      // Clear search
      document.getElementById('searchInput').value = '';
      
      // Reset dropdowns
      document.getElementById('statusFilter').value = 'all';
      document.getElementById('riskFilter').value = 'all';
      
      // Activate all legend items
      document.querySelectorAll('.legend-item').forEach(item => {
        item.classList.add('active');
      });
      
      // Reset visual indicators
      updateFilterIndicators('', 'all', 'all', ['green', 'orange', 'red']);
      
      applyFilters();
      showNotification('All filters cleared');
    }
    
    function toggleLegendItem(item) {
      item.classList.toggle('active');
      
      // If no legend items are active, activate all (show everything)
      const activeItems = document.querySelectorAll('.legend-item.active');
      if (activeItems.length === 0) {
        document.querySelectorAll('.legend-item').forEach(item => {
          item.classList.add('active');
        });
      }
      
      applyFilters();
    }
    
    function exportData() {
      const csvContent = "data:text/csv;charset=utf-8," 
        + "Name,Status,Risk,County,Latitude,Longitude,Maintenance Date\n"
        + filteredSubstations.map(s => 
          `"${s.name}","${s.status || 'Unknown'}","${s.risk || 'Unknown'}","${s.county || 'N/A'}","${s.latitude}","${s.longitude}","${s.maintenance_date || 'N/A'}"`
        ).join("\n");
      
      const encodedUri = encodeURI(csvContent);
      const link = document.createElement("a");
      link.setAttribute("href", encodedUri);
      link.setAttribute("download", "substations_export.csv");
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      
      showNotification('Data exported successfully!');
    }
    
    function zoomIn() {
      map.zoomIn();
      showNotification('Zoomed in');
    }
    
    function zoomOut() {
      map.zoomOut();
      showNotification('Zoomed out');
    }
    
    function fitBounds() {
      if (markers.length > 0) {
        const group = L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
        showNotification('Fitted to markers');
      } else {
        showNotification('No markers to fit to', 'error');
      }
    }
    
    function toggleClustering() {
      if (clusteringEnabled) {
        map.removeLayer(markerClusterGroup);
        markers.forEach(marker => map.addLayer(marker));
        clusteringEnabled = false;
        document.getElementById('toggleClusteringBtn').innerHTML = '<i class="fa fa-object-ungroup"></i>';
        showNotification('Clustering disabled');
      } else {
        markers.forEach(marker => map.removeLayer(marker));
        map.addLayer(markerClusterGroup);
        markers.forEach(marker => markerClusterGroup.addLayer(marker));
        clusteringEnabled = true;
        document.getElementById('toggleClusteringBtn').innerHTML = '<i class="fa fa-object-group"></i>';
        showNotification('Clustering enabled');
      }
    }
    
    // Event listeners
    document.getElementById('searchInput').addEventListener('input', applyFilters);
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    document.getElementById('riskFilter').addEventListener('change', applyFilters);
    document.getElementById('refreshBtn').addEventListener('click', loadData);
    document.getElementById('exportBtn').addEventListener('click', exportData);
    document.getElementById('zoomInBtn').addEventListener('click', zoomIn);
    document.getElementById('zoomOutBtn').addEventListener('click', zoomOut);
    document.getElementById('fitBoundsBtn').addEventListener('click', fitBounds);
    document.getElementById('toggleClusteringBtn').addEventListener('click', toggleClustering);
    
    // Enhanced legend filter events
    document.querySelectorAll('.legend-item').forEach(item => {
      item.addEventListener('click', function() {
        toggleLegendItem(this);
      });
    });
    
    // Add clear filters button functionality
    const clearFiltersBtn = document.createElement('button');
    clearFiltersBtn.className = 'btn btn-outline';
    clearFiltersBtn.innerHTML = '<i class="fa fa-times"></i> Clear Filters';
    clearFiltersBtn.style.marginLeft = '0.5rem';
    clearFiltersBtn.addEventListener('click', clearAllFilters);
    
    // Add clear filters button to controls
    const actionsGroup = document.querySelector('.control-group:last-child');
    const actionsDiv = actionsGroup.querySelector('div');
    actionsDiv.appendChild(clearFiltersBtn);

    // Select All/Deselect All Legend functionality
    document.getElementById('selectAllLegendBtn').addEventListener('click', () => {
      document.querySelectorAll('.legend-item').forEach(item => {
        item.classList.add('active');
      });
      applyFilters();
      showNotification('All legend filters selected');
    });

    document.getElementById('deselectAllLegendBtn').addEventListener('click', () => {
      document.querySelectorAll('.legend-item').forEach(item => {
        item.classList.remove('active');
      });
      applyFilters();
      showNotification('All legend filters deselected');
    });
    
    function loadData() {
      const refreshBtn = document.getElementById('refreshBtn');
      const originalContent = refreshBtn.innerHTML;
      refreshBtn.innerHTML = '<div class="loading"></div> Refreshing...';
      refreshBtn.disabled = true;
      
      fetch('api/substations.php', { credentials: 'same-origin' })
        .then(res => res.json())
        .then(subs => {
          console.log('Substations loaded:', subs.length);
          console.log('Sample substation data:', subs.slice(0, 3));
          
          allSubstations = subs;
          filteredSubstations = subs;
          
          filterMarkers();
          updateLegendCounts();
          
          refreshBtn.innerHTML = originalContent;
          refreshBtn.disabled = false;
          
          showNotification(`Loaded ${subs.length} substations successfully!`);
        })
        .catch(error => {
          console.error('Error loading map data:', error);
          showNotification('Error loading data. Please try again.', 'error');
          
          refreshBtn.innerHTML = originalContent;
          refreshBtn.disabled = false;
        });
    }
    
    // Initialize
    initializeMap();
    loadData();
    
    // Auto-refresh every 5 minutes
    setInterval(loadData, 300000);
    
    // Keyboard shortcuts for better UX
    document.addEventListener('keydown', function(e) {
      // Ctrl/Cmd + R to refresh
      if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
        e.preventDefault();
        loadData();
      }
      
      // Ctrl/Cmd + F to focus search
      if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
        e.preventDefault();
        document.getElementById('searchInput').focus();
      }
      
      // Escape to clear search
      if (e.key === 'Escape') {
        document.getElementById('searchInput').value = '';
        applyFilters();
      }
    });
    
    // Add tooltips and better accessibility
    document.querySelectorAll('[title]').forEach(element => {
      element.setAttribute('aria-label', element.getAttribute('title'));
    });
    
    // Show initial notification
    setTimeout(() => {
      showNotification('Map controls are now functional! Try the legend filters and map controls.', 'info');
    }, 1000);
  </script>
</body>
</html> 