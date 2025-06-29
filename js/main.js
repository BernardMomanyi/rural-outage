document.addEventListener('DOMContentLoaded', () => {
  // Dashboard stats fetch
  loadDashboardStats();

  // Substation data for all regions in Kenya (sample, can be expanded)
  const substations = [
    { id: '001', name: 'Kapsabet', county: 'Nandi', status: 'Online', risk: 'Low', coords: [-0.3408, 35.1092] },
    { id: '002', name: 'Naivasha', county: 'Nakuru', status: 'Offline', risk: 'High', coords: [-0.7167, 36.4333] },
    { id: '003', name: 'Garissa', county: 'Garissa', status: 'Online', risk: 'Medium', coords: [-0.4532, 39.6460] },
    { id: '004', name: 'Kisumu', county: 'Kisumu', status: 'Online', risk: 'Low', coords: [-0.0917, 34.7680] },
    { id: '005', name: 'Mombasa', county: 'Mombasa', status: 'Offline', risk: 'High', coords: [-4.0435, 39.6682] },
    { id: '006', name: 'Eldoret', county: 'Uasin Gishu', status: 'Online', risk: 'Low', coords: [0.5143, 35.2698] },
    { id: '007', name: 'Meru', county: 'Meru', status: 'Online', risk: 'Medium', coords:  [0.0470, 37.6496] },
    { id: '008', name: 'Kitale', county: 'Trans Nzoia', status: 'Online', risk: 'Low', coords: [1.0157, 35.0061] },
    { id: '009', name: 'Machakos', county: 'Machakos', status: 'Offline', risk: 'Medium', coords: [-1.5177, 37.2634] },
    { id: '010', name: 'Nyeri', county: 'Nyeri', status: 'Online', risk: 'Low', coords: [-0.4201, 36.9476] },
    { id: '011', name: 'Wajir', county: 'Wajir', status: 'Online', risk: 'Medium', coords: [1.7471, 40.0573] },
    { id: '012', name: 'Lodwar', county: 'Turkana', status: 'Online', risk: 'Low', coords: [3.1191, 35.5973] },
    { id: '013', name: 'Narok', county: 'Narok', status: 'Offline', risk: 'High', coords: [-1.0854, 35.8711] },
    { id: '014', name: 'Voi', county: 'Taita Taveta', status: 'Online', risk: 'Low', coords: [-3.3964, 38.5561] },
    { id: '015', name: 'Isiolo', county: 'Isiolo', status: 'Online', risk: 'Medium', coords: [0.3546, 37.5822] },
    { id: '016', name: 'Marsabit', county: 'Marsabit', status: 'Online', risk: 'Low', coords: [2.3346, 37.9906] },
    { id: '017', name: 'Bungoma', county: 'Bungoma', status: 'Online', risk: 'Low', coords: [0.5635, 34.5606] },
    { id: '018', name: 'Embu', county: 'Embu', status: 'Online', risk: 'Medium', coords: [-0.5376, 37.4575] },
    { id: '019', name: 'Kakamega', county: 'Kakamega', status: 'Online', risk: 'Low', coords: [0.2827, 34.7519] },
    { id: '020', name: 'Mandera', county: 'Mandera', status: 'Offline', risk: 'High', coords: [3.9373, 41.8569] },
    // ... add more substations for all 47 counties as needed
  ];

  // Populate table
  const tableBody = document.getElementById('substationTableBody');
  tableBody.innerHTML = '';
  substations.forEach(sub => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${sub.id}</td>
      <td>${sub.name}</td>
      <td>${sub.county}</td>
      <td class="status-${sub.status.toLowerCase()}">${sub.status}</td>
      <td class="risk-${sub.risk.toLowerCase()}">${sub.risk}</td>
    `;
    tableBody.appendChild(tr);
  });

  // Chart.js - Outage Trend
  const ctx = document.getElementById('outageTrendChart').getContext('2d');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      datasets: [{
        label: 'Predicted Outages',
        data: [2, 3, 4, 6, 3, 1, 2, 5, 4, 3, 2, 1],
        borderColor: 'rgba(0, 123, 255, 1)',
        backgroundColor: 'rgba(0, 123, 255, 0.1)',
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#fff',
        pointBorderColor: 'rgba(0, 123, 255, 1)',
        pointRadius: 5
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: true }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: '#e0eafc' }
        },
        x: {
          grid: { color: '#f4f6f9' }
        }
      }
    }
  });

  // Map logic for map.php and dashboard.php
  function loadMapSubstations() {
    const mapDiv = document.getElementById('map');
    if (!mapDiv) return;
    const map = L.map('map').setView([-0.0236, 37.9062], 6.2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 18,
      attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    fetch('api/substations.php', { credentials: 'same-origin' })
      .then(res => res.json())
      .then(substations => {
        const iconColors = { High: 'red', Medium: 'orange', Low: 'green' };
        substations.forEach(sub => {
          const marker = L.circleMarker([sub.latitude, sub.longitude], {
            radius: 10,
            color: iconColors[sub.risk] || 'blue',
            fillColor: iconColors[sub.risk] || 'blue',
            fillOpacity: 0.85,
            weight: 2
          }).addTo(map);
          marker.bindPopup(`<b>${sub.name} Substation</b><br>County: ${sub.county}<br>Status: <span style='color:${sub.status==='Online'?'#28a745':'#ff4d4f'}'>${sub.status}</span><br>Risk: <span style='color:${iconColors[sub.risk]}'>${sub.risk}</span>`);
        });
      });
  }

  loadMapSubstations();
});

// Role-based UI logic for all pages except login/404
(function() {
  const role = localStorage.getItem('role');
  if (role) {
    document.body.classList.add(role);
    // Add logout button if not present
    if (!document.querySelector('.logout-btn') && document.querySelector('.sidebar')) {
      const logoutBtn = document.createElement('button');
      logoutBtn.className = 'logout-btn';
      logoutBtn.innerHTML = '<i class="fa fa-sign-out-alt"></i> Logout';
      logoutBtn.onclick = function() {
        localStorage.removeItem('role');
        window.location.href = 'login.html';
      };
      document.querySelector('.sidebar').appendChild(logoutBtn);
    }
    // Hide/show nav links by role
    document.querySelectorAll('.sidebar nav a').forEach(a => {
      a.style.display = '';
    });
    if (role === 'technician') {
      document.querySelectorAll('.nav-admin, .nav-viewer').forEach(e => e.style.display = 'none');
    } else if (role === 'viewer') {
      document.querySelectorAll('.nav-admin, .nav-tech').forEach(e => e.style.display = 'none');
    }
  } else if (window.location.pathname.indexOf('login.html') === -1 && window.location.pathname.indexOf('404.html') === -1) {
    window.location.href = 'login.html';
  }
})();

// Add icons to navigation and stats (run once per page)
document.addEventListener('DOMContentLoaded', () => {
  // Sidebar nav icons
  const navIcons = {
    'Home': 'fa-home',
    'Dashboard': 'fa-tachometer-alt',
    'Substations': 'fa-bolt',
    'Reports': 'fa-file-alt',
    'Upload Data': 'fa-upload',
    'Map View': 'fa-map-marked-alt',
    'Users': 'fa-users-cog',
    'Settings': 'fa-cogs',
    'About': 'fa-info-circle',
    'Contact': 'fa-envelope'
  };
  document.querySelectorAll('.sidebar nav a').forEach(a => {
    const text = a.textContent.trim();
    if (!a.querySelector('.fa') && navIcons[text]) {
      a.innerHTML = `<i class="fa ${navIcons[text]}"></i> <span>${text}</span>`;
    }
  });
  // Stats icons
  const statIcons = [
    '<i class="fa fa-bolt"></i>',
    '<i class="fa fa-chart-line"></i>',
    '<i class="fa fa-exclamation-triangle"></i>',
    '<i class="fa fa-users"></i>'
  ];
  document.querySelectorAll('.stats .card').forEach((card, i) => {
    if (!card.querySelector('.fa')) {
      card.innerHTML = statIcons[i % statIcons.length] + card.innerHTML;
    }
  });
});

// Dashboard stats fetch
function loadDashboardStats() {
  const total = document.getElementById('totalSubstations');
  const pred = document.getElementById('predictionsCount');
  const crit = document.getElementById('criticalAlerts');
  if (!total || !pred || !crit) return;
  fetch('api/dashboard_stats.php', { credentials: 'same-origin' })
    .then(res => res.json())
    .then(stats => {
      total.textContent = stats.totalSubstations;
      pred.textContent = stats.predictionsCount;
      crit.textContent = stats.criticalAlerts;
    });
} 