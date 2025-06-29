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
  <title>Substations - OutageSys</title>
  <link rel="stylesheet" href="css/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    .modal-bg { display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); z-index:1000; align-items:center; justify-content:center; }
    .modal { background:#fff; border-radius:12px; padding:24px; min-width:320px; box-shadow:0 2px 16px #0002; }
    .modal input, .modal select { width:100%; margin-bottom:12px; padding:8px; border-radius:6px; border:1px solid #e0eafc; }
    .modal .modal-actions { display:flex; gap:12px; justify-content:flex-end; }
    .modal .error-msg, .modal .success-msg { margin-bottom:10px; }
  </style>
</head>
<body class="<?php echo htmlspecialchars($role); ?>">
  <div class="dashboard">
    <?php include 'sidebar.php'; ?>
    <main class="main-content page-transition" role="main">
      <h1>Substation Management</h1>
      <?php if ($role === 'admin' || $role === 'technician'): ?>
      <button class="btn btn--primary" id="addSubBtn" style="margin-bottom: 18px;"><i class="fa fa-plus"></i> Add New Substation</button>
      <?php endif; ?>
      <div id="substationMsg"></div>
      <form method="get" style="margin-bottom:16px;"><input type="search" name="q" placeholder="Search substations..." aria-label="Search substations" style="padding:6px 12px;border-radius:6px;border:1px solid #ccc;max-width:250px;" /></form>
      <table class="styled-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>County</th>
            <th>Status</th>
            <th>Risk</th>
            <th>Latitude</th>
            <th>Longitude</th>
            <?php if ($role === 'admin' || $role === 'technician'): ?><th>Actions</th><?php endif; ?>
          </tr>
        </thead>
        <tbody id="substationTableBody">
          <tr><td colspan="8" style="text-align:center;">Loading...</td></tr>
        </tbody>
      </table>
      <footer class="footer">
        <div>&copy; 2024 OutageSys</div>
      </footer>
    </main>
  </div>
  <div class="modal-bg" id="subModalBg">
    <div class="modal" id="subModal">
      <form id="subForm">
        <input type="hidden" id="subId" />
        <div class="error-msg" id="subFormError" style="display:none;"></div>
        <div class="success-msg" id="subFormSuccess" style="display:none;"></div>
        <label>Name</label>
        <input type="text" id="subName" required />
        <label>County</label>
        <input type="text" id="subCounty" required />
        <label>Status</label>
        <select id="subStatus">
          <option value="Online">Online</option>
          <option value="Offline">Offline</option>
        </select>
        <label>Risk</label>
        <select id="subRisk">
          <option value="Low">Low</option>
          <option value="Medium">Medium</option>
          <option value="High">High</option>
        </select>
        <label>Latitude</label>
        <input type="number" id="subLat" step="any" required />
        <label>Longitude</label>
        <input type="number" id="subLng" step="any" required />
        <div class="btn-group">
          <button type="submit" class="btn btn--primary" id="subSaveBtn"><i class="fa fa-save"></i> Save</button>
          <button type="button" class="btn btn--secondary" id="subCancelBtn">Cancel</button>
        </div>
      </form>
    </div>
  </div>
  <div class="loading-overlay" id="globalLoading" aria-hidden="true"><span class="custom-loader"></span></div>
  <script>
    const role = '<?php echo $role; ?>';
    function fetchSubstations() {
      fetch('api/substations.php', { credentials: 'same-origin' })
        .then(res => res.json())
        .then(data => {
          const tbody = document.getElementById('substationTableBody');
          tbody.innerHTML = '';
          if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;">No substations found.</td></tr>';
            return;
          }
          data.forEach(sub => {
            let row = `<tr>
              <td>${sub.id}</td>
              <td>${sub.name}</td>
              <td>${sub.county}</td>
              <td class="status-${sub.status.toLowerCase()}">${sub.status}</td>
              <td class="risk-${sub.risk.toLowerCase()}">${sub.risk}</td>
              <td>${sub.latitude}</td>
              <td>${sub.longitude}</td>`;
            if (role === 'admin' || role === 'technician') {
              row += `<td>
                <div class="btn-group">
                  <button class='btn btn--secondary' onclick='openEditSub(${JSON.stringify(sub)})'><i class="fa fa-edit"></i> Edit</button>
                  <button class='btn btn--danger' onclick='showLoadingOverlay();deleteSub(${sub.id})'><i class="fa fa-trash"></i> Delete</button>
                </div>
              </td>`;
            }
            row += '</tr>';
            tbody.innerHTML += row;
          });
        });
    }
    fetchSubstations();
    // Modal logic
    const modalBg = document.getElementById('subModalBg');
    const subForm = document.getElementById('subForm');
    const subFormError = document.getElementById('subFormError');
    const subFormSuccess = document.getElementById('subFormSuccess');
    function openEditSub(sub) {
      document.getElementById('subId').value = sub.id;
      document.getElementById('subName').value = sub.name;
      document.getElementById('subCounty').value = sub.county;
      document.getElementById('subStatus').value = sub.status;
      document.getElementById('subRisk').value = sub.risk;
      document.getElementById('subLat').value = sub.latitude;
      document.getElementById('subLng').value = sub.longitude;
      subFormError.style.display = 'none';
      subFormSuccess.style.display = 'none';
      modalBg.style.display = 'flex';
    }
    function openAddSub() {
      document.getElementById('subId').value = '';
      subForm.reset();
      subFormError.style.display = 'none';
      subFormSuccess.style.display = 'none';
      modalBg.style.display = 'flex';
    }
    document.getElementById('addSubBtn')?.addEventListener('click', openAddSub);
    document.getElementById('subCancelBtn').onclick = () => { modalBg.style.display = 'none'; };
    subForm.onsubmit = function(e) {
      e.preventDefault();
      subFormError.style.display = 'none';
      subFormSuccess.style.display = 'none';
      const id = document.getElementById('subId').value;
      const payload = {
        name: document.getElementById('subName').value,
        county: document.getElementById('subCounty').value,
        status: document.getElementById('subStatus').value,
        risk: document.getElementById('subRisk').value,
        latitude: parseFloat(document.getElementById('subLat').value),
        longitude: parseFloat(document.getElementById('subLng').value)
      };
      let method = 'POST';
      if (id) {
        payload.id = id;
        method = 'PUT';
      }
      fetch('api/substations.php', {
        method,
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(payload)
      })
      .then(res => res.json())
      .then(resp => {
        if (resp.success) {
          subFormSuccess.textContent = 'Saved!';
          subFormSuccess.style.display = 'block';
          setTimeout(() => { modalBg.style.display = 'none'; fetchSubstations(); }, 800);
        } else {
          subFormError.textContent = resp.error || 'Error saving.';
          subFormError.style.display = 'block';
        }
      });
    };
    window.deleteSub = function(id) {
      if (!confirm('Delete this substation?')) return;
      fetch('api/substations.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ id })
      })
      .then(res => res.json())
      .then(resp => {
        if (resp.success) {
          fetchSubstations();
        } else {
          alert(resp.error || 'Delete failed.');
        }
      });
    };
    function showLoadingOverlay() {
      const overlay = document.getElementById('globalLoading');
      overlay.setAttribute('aria-hidden', 'false');
      setTimeout(() => overlay.setAttribute('aria-hidden', 'true'), 2000); // Demo: hide after 2s
    }
  </script>
</body>
</html> 