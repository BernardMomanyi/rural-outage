<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit;
}
require_once 'db.php';
$username = $_SESSION['username'];
$msg = '';
$role = $_SESSION['role'];
$dashboard_link = 'user_dashboard.php';
if ($role === 'admin') $dashboard_link = 'admin_dashboard.php';
if ($role === 'technician') $dashboard_link = 'technician_dashboard.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['datafile'])) {
  // Handle file upload (demo only)
  $msg = 'File uploaded: ' . htmlspecialchars($_FILES['datafile']['name']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Upload Data - OutageSys</title>
  <link rel="stylesheet" href="css/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    .upload-form { max-width: 400px; margin: 48px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px #0001; padding: 32px; }
    .upload-form h2 { color: #2563eb; margin-bottom: 18px; }
    .upload-form input[type="file"] { margin-bottom: 18px; }
    .upload-form button { background: #2563eb; color: #fff; border: none; border-radius: 6px; padding: 10px 24px; font-size: 1rem; cursor: pointer; }
    .upload-form .success { color: #16a34a; margin-bottom: 12px; }
  </style>
</head>
<body class="admin">
  <div class="dashboard">
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
      <form class="upload-form" method="post" enctype="multipart/form-data">
        <h2>Upload Data File</h2>
        <?php if ($msg): ?><div class="success"><?php echo $msg; ?></div><?php endif; ?>
        <input type="file" name="datafile" accept=".csv,.xlsx,.xls" required />
        <button type="submit" class="btn btn--primary" onclick="showLoadingOverlay()"><i class="fa fa-upload"></i> Upload</button>
      </form>
      <footer class="footer">
        <div>&copy; 2024 OutageSys</div>
      </footer>
    </main>
  </div>
  <div class="loading-overlay" id="globalLoading" aria-hidden="true"><span class="spinner"></span></div>
  <script>
  function showLoadingOverlay() {
    const overlay = document.getElementById('globalLoading');
    overlay.setAttribute('aria-hidden', 'false');
    setTimeout(() => overlay.setAttribute('aria-hidden', 'true'), 2000); // Demo: hide after 2s
  }
  </script>
</body>
</html> 