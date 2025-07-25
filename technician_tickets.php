<?php
// Minimal session check for demonstration
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
  header('Location: login.php');
  exit;
}
$username = $_SESSION['username'];
$dashboard_link = 'technician_dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Technician Tickets - MVP</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      background: #f4f6fb;
      font-family: 'Inter', sans-serif;
      margin: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }
    .container {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 24px rgba(0,0,0,0.07);
      padding: 2.5rem 2rem;
      max-width: 400px;
      width: 100%;
      text-align: center;
    }
    h1 {
      color: #2563eb;
      font-size: 2rem;
      margin-bottom: 0.5rem;
    }
    .subtitle {
      color: #64748b;
      font-size: 1.1rem;
      margin-bottom: 2rem;
    }
    .dashboard-link {
      display: inline-block;
      margin-bottom: 2rem;
      color: #2563eb;
      text-decoration: none;
      font-weight: 600;
      border-bottom: 1px solid #2563eb;
      transition: color 0.2s;
    }
    .dashboard-link:hover {
      color: #1d4ed8;
      border-bottom: 1px solid #1d4ed8;
    }
    .empty-state {
      color: #94a3b8;
      font-size: 1.1rem;
      margin-top: 2rem;
    }
  </style>
</head>
<body>
  <div class="container">
    <a href="<?php echo $dashboard_link; ?>" class="dashboard-link">&larr; Back to Dashboard</a>
    <h1>Technician Tickets</h1>
    <div class="subtitle">Welcome, <?php echo htmlspecialchars($username); ?>!<br>Your assigned tickets will appear here.</div>
    <div class="empty-state">
      <span style="font-size:2rem;">📭</span><br>
      No tickets assigned yet.
    </div>
  </div>
</body>
</html> 