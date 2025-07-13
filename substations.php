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

require_once 'db.php';

// Handle add substation form
$add_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_substation'])) {
  $name = trim($_POST['name'] ?? '');
  $location = trim($_POST['location'] ?? '');
  if ($name && $location) {
    $stmt = $pdo->prepare('INSERT INTO substations (name, location) VALUES (?, ?)');
    $stmt->execute([$name, $location]);
    $add_msg = '<span style="color:var(--color-success);">Substation added!</span>';
  } else {
    $add_msg = '<span style="color:var(--color-danger);">Please fill all fields.</span>';
  }
}
// Fetch all substations
$stmt = $pdo->query('SELECT * FROM substations ORDER BY id ASC');
$substations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Substations - OutageSys</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    body { min-height: 100vh; }
    .substations-bg {
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
      <li class="breadcrumb-current" style="color:var(--color-primary); font-weight:600;"><i class="fa fa-bolt"></i> Substations</li>
    </ol>
  </nav>
  <button class="toggle-mode" id="darkModeToggle">
    <i class="fa fa-moon" id="modeIcon"></i> 
    <span id="modeText">Dark Mode</span>
  </button>
  <div class="substations-bg">
    <div class="container" style="width:100%;">
      <div class="card mb-md">
        <h2 class="h2 mb-sm"><i class="fa fa-bolt"></i> Substations</h2>
        <p class="mb-md">View and manage all substations in the system.</p>
        <form method="post" class="mb-md" style="display:flex; gap:1em; flex-wrap:wrap; align-items:flex-end;">
          <div>
            <label for="name" class="mb-xs">Name</label><br>
            <input type="text" id="name" name="name" required placeholder="Substation Name" style="padding:8px; border-radius:6px; border:1px solid var(--color-border);">
          </div>
          <div>
            <label for="location" class="mb-xs">Location</label><br>
            <input type="text" id="location" name="location" required placeholder="Location" style="padding:8px; border-radius:6px; border:1px solid var(--color-border);">
          </div>
          <button type="submit" name="add_substation" class="btn btn-primary"><i class="fa fa-plus"></i> Add Substation</button>
        </form>
        <?php if ($add_msg) echo '<div class="mb-md">' . $add_msg . '</div>'; ?>
        <table class="styled-table" style="margin-top:1em;">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Location</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($substations as $sub): ?>
              <tr>
                <td><?php echo htmlspecialchars($sub['id']); ?></td>
                <td><?php echo htmlspecialchars($sub['name']); ?></td>
                <td><?php echo htmlspecialchars($sub['location']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="footer mt-md small text-center" style="color: var(--color-secondary);">
        &copy; 2024 OutageSys. All rights reserved.
      </div>
    </div>
  </div>
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
  </script>
</body>
</html> 