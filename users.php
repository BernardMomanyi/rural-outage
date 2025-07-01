<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$role = $_SESSION['role'];
$dashboard_link = 'user_dashboard.php';
$dashboard_label = 'Dashboard';
if ($role === 'admin') {
  $dashboard_link = 'admin_dashboard.php';
  $dashboard_label = 'Assignments';
}
if ($role === 'technician') $dashboard_link = 'technician_dashboard.php';
if ($role !== 'admin') {
  if ($role === 'technician') {
    header('Location: technician_dashboard.php');
    exit;
  } else {
    header('Location: user_dashboard.php');
    exit;
  }
}
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>User Management - OutageSys</title>
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
      <div aria-live="polite" id="ariaLiveRegion"></div>
      <nav aria-label="Breadcrumb" class="breadcrumb">
        <ol>
          <li><a href="index.php">Home</a></li>
          <li>›</li>
          <li>Users</li>
        </ol>
      </nav>
      <button class="cta-btn margin-bottom" id="addUserBtn"><i class="fa fa-user-plus"></i> Add New User</button>
      <div id="userMsg"></div>
      <form method="get" class="search-form">
        <input type="search" name="q" placeholder="Search users..." aria-label="Search users" class="search-input" />
      </form>
      <table class="styled-table">
        <thead>
          <tr><th>ID</th><th>Username</th><th>Role</th><th>Email</th><th>Action</th></tr>
        </thead>
        <tbody id="userTableBody">
          <tr><td colspan="5" class="table-loading">Loading...</td></tr>
        </tbody>
      </table>
      <div class="card">
        <h2>Loading Example</h2>
        <div class="skeleton skeleton-80"></div>
        <div class="skeleton skeleton-60"></div>
        <div class="skeleton skeleton-90"></div>
      </div>
      <footer class="footer" role="contentinfo">
        <div>&copy; 2024 OutageSys | <a href="privacy_policy.html">Privacy Policy</a></div>
      </footer>
    </main>
  </div>
  <div class="modal-bg" id="userModalBg">
    <div class="modal" id="userModal">
      <form id="userForm">
        <input type="hidden" id="userId" />
        <div class="error-msg display-none" id="userFormError"></div>
        <div class="success-msg display-none" id="userFormSuccess"></div>
        <label>Username</label>
        <input type="text" id="userUsername" required />
        <label>Password (leave blank to keep current)</label>
        <input type="password" id="userPassword" />
        <label>Role</label>
        <select id="userRole">
          <option value="admin">Admin</option>
          <option value="technician">Technician</option>
          <option value="user">User</option>
        </select>
        <label>Status</label>
        <select id="userStatus">
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
        <div class="modal-actions">
          <div class="btn-group">
            <button type="submit" class="btn btn--primary" id="userSaveBtn"><i class="fa fa-save"></i> Save</button>
            <button type="button" class="btn btn--secondary" id="userCancelBtn">Cancel</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  <div class="loading-overlay" id="globalLoading" aria-hidden="true"><span class="custom-loader"></span></div>
  <script>
    function fetchUsers() {
      fetch('api/users.php', { credentials: 'same-origin' })
        .then(res => res.json())
        .then(data => {
          const tbody = document.getElementById('userTableBody');
          tbody.innerHTML = '';
          if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="table-empty">No users found.</td></tr>';
            return;
          }
          data.forEach(user => {
            let row = `<tr>
              <td>${user.id}</td>
              <td>${user.username}</td>
              <td>${user.role}</td>
              <td class="status-${user.status}">${user.status}</td>
              <td>
                <div class="btn-group">
                  <button class='btn btn--secondary' onclick='openEditUser(${JSON.stringify(user)})'><i class="fa fa-edit"></i> Edit</button>
                  <button class='btn btn--danger' onclick='showLoadingOverlay();deleteUser(${user.id})'><i class="fa fa-trash"></i> Delete</button>
                </div>
              </td>
            </tr>`;
            tbody.innerHTML += row;
          });
        });
    }
    fetchUsers();
    // Modal logic
    const modalBg = document.getElementById('userModalBg');
    const userForm = document.getElementById('userForm');
    const userFormError = document.getElementById('userFormError');
    const userFormSuccess = document.getElementById('userFormSuccess');
    function openEditUser(user) {
      document.getElementById('userId').value = user.id;
      document.getElementById('userUsername').value = user.username;
      document.getElementById('userRole').value = user.role;
      document.getElementById('userStatus').value = user.status;
      document.getElementById('userPassword').value = '';
      userFormError.style.display = 'none';
      userFormSuccess.style.display = 'none';
      modalBg.style.display = 'flex';
    }
    function openAddUser() {
      document.getElementById('userId').value = '';
      userForm.reset();
      userFormError.style.display = 'none';
      userFormSuccess.style.display = 'none';
      modalBg.style.display = 'flex';
    }
    document.getElementById('addUserBtn')?.addEventListener('click', openAddUser);
    document.getElementById('userCancelBtn').onclick = () => { modalBg.style.display = 'none'; };
    userForm.onsubmit = function(e) {
      e.preventDefault();
      userFormError.style.display = 'none';
      userFormSuccess.style.display = 'none';
      const id = document.getElementById('userId').value;
      const payload = {
        username: document.getElementById('userUsername').value,
        password: document.getElementById('userPassword').value,
        role: document.getElementById('userRole').value,
        status: document.getElementById('userStatus').value
      };
      let method = 'POST';
      if (id) {
        payload.id = id;
        method = 'PUT';
      }
      fetch('api/users.php', {
        method,
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(payload)
      })
      .then(res => res.json())
      .then(resp => {
        if (resp.success) {
          userFormSuccess.textContent = 'Saved!';
          userFormSuccess.style.display = 'block';
          setTimeout(() => { modalBg.style.display = 'none'; fetchUsers(); }, 800);
        } else {
          userFormError.textContent = resp.error || 'Error saving.';
          userFormError.style.display = 'block';
        }
      });
    };
    window.deleteUser = function(id) {
      if (!confirm('Delete this user?')) return;
      fetch('api/users.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ id })
      })
      .then(res => res.json())
      .then(resp => {
        if (resp.success) {
          fetchUsers();
        } else {
          alert(resp.error || 'Delete failed.');
        }
      });
    };
    // Cookie consent banner
    if (!localStorage.getItem('cookieConsent')) {
      const banner = document.createElement('div');
      banner.className = 'cookie-banner';
      banner.innerHTML = 'This site uses cookies for analytics and user experience. <button class="cookie-btn" onclick="localStorage.setItem(\'cookieConsent\',1);this.parentNode.remove();">OK</button>';
      document.body.appendChild(banner);
    }
    function showLoadingOverlay() {
      const overlay = document.getElementById('globalLoading');
      overlay.setAttribute('aria-hidden', 'false');
      setTimeout(() => overlay.setAttribute('aria-hidden', 'true'), 2000); // Demo: hide after 2s
    }
    function showFeedback(type) {
      const region = document.getElementById('ariaLiveRegion');
      if (type === 'success') {
        document.getElementById('feedbackSuccess').classList.add('show');
        document.getElementById('feedbackSuccess').setAttribute('aria-hidden', 'false');
        region.textContent = 'Action successful!';
        setTimeout(() => {
          document.getElementById('feedbackSuccess').classList.remove('show');
          document.getElementById('feedbackSuccess').setAttribute('aria-hidden', 'true');
        }, 2000);
      } else {
        document.getElementById('feedbackError').classList.add('show');
        document.getElementById('feedbackError').setAttribute('aria-hidden', 'false');
        region.textContent = 'Action failed!';
        setTimeout(() => {
          document.getElementById('feedbackError').classList.remove('show');
          document.getElementById('feedbackError').setAttribute('aria-hidden', 'true');
        }, 2000);
      }
    }
  </script>
</body>
</html> 