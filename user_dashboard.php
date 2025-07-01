<?php
session_start();
require_once 'db.php';
$user = $user ?? null;
if (!$user && isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id=?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
$avatar = isset($user['avatar']) ? $user['avatar'] : '';
$name = isset($user['name']) ? $user['name'] : (isset($_SESSION['username']) ? $_SESSION['username'] : 'U');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
  header('Location: login.php');
  exit;
}
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
// Fetch user profile (simulate for now)
$user = [
  'name' => $username,
  'location' => 'Nairobi',
  'email' => 'user@email.com',
  'phone' => '0700000000',
];
// Fetch region power status (simulate)
$region_status = 'Normal'; // or 'Outage', 'Predicted Outage'
// Fetch user's outage reports (simulate)
$my_reports = [
  ['id' => 'RPT-001', 'location' => 'Nairobi', 'status' => 'Submitted', 'technician' => '', 'desc' => 'Transformer noise', 'feedback' => '', 'can_reopen' => true],
  ['id' => 'RPT-002', 'location' => 'Nairobi', 'status' => 'Assigned', 'technician' => 'Tech John', 'desc' => 'No power since morning', 'feedback' => '', 'can_reopen' => false],
  ['id' => 'RPT-003', 'location' => 'Nairobi', 'status' => 'Resolved', 'technician' => 'Tech Jane', 'desc' => 'Frequent outages', 'feedback' => 'Thanks!', 'can_reopen' => true],
];
// Fetch notifications (simulate)
$notifications = [
  'Scheduled maintenance on 2024-07-01 02:00-04:00',
  'Technician en route for report RPT-002',
  'Power restoration update: Area restored at 10:30am',
];
// Insights (simulate)
$outage_count = 5;
$energy_tips = [
  'Switch off appliances when not in use.',
  'Use LED bulbs to save energy.',
];
// Handle outage report submission
$report_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_outage'])) {
  $loc = trim($_POST['location'] ?? '');
  $time = $_POST['time_started'] ?? '';
  $desc = trim($_POST['description'] ?? '');
  if ($loc && $time) {
    $stmt = $pdo->prepare('INSERT INTO user_outages (user_id, location, time_started, description) VALUES (?, ?, ?, ?)');
    $stmt->execute([$user_id, $loc, $time, $desc]);
    $report_id = $pdo->lastInsertId();
    $report_msg = 'Outage reported! Your tracking ID is RPT-' . str_pad($report_id, 3, '0', STR_PAD_LEFT);
    echo '<script>fetch("api/user_updates.php", {method: "POST", headers: {"Content-Type": "application/json"}, credentials: "same-origin", body: JSON.stringify({user_id: '.$user_id.', type: "report_update"})});</script>';
  } else {
    $report_msg = 'Please fill all required fields.';
  }
}
// Handle feedback/reopen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_id'])) {
  $fid = intval($_POST['feedback_id']);
  $feedback = trim($_POST['feedback_text'] ?? '');
  if ($feedback) {
    $stmt = $pdo->prepare('UPDATE user_outages SET feedback=? WHERE id=? AND user_id=?');
    $stmt->execute([$feedback, $fid, $user_id]);
    $report_msg = 'Feedback submitted!';
    echo '<script>fetch("api/user_updates.php", {method: "POST", headers: {"Content-Type": "application/json"}, credentials: "same-origin", body: JSON.stringify({user_id: '.$user_id.', type: "report_update"})});</script>';
  }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reopen_id'])) {
  $rid = intval($_POST['reopen_id']);
  $stmt = $pdo->prepare('UPDATE user_outages SET status="Submitted" WHERE id=? AND user_id=?');
  $stmt->execute([$rid, $user_id]);
  $report_msg = 'Report reopened!';
  echo '<script>fetch("api/user_updates.php", {method: "POST", headers: {"Content-Type": "application/json"}, credentials: "same-origin", body: JSON.stringify({user_id: '.$user_id.', type: "report_update"})});</script>';
}
// Handle profile update
$profile_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
  if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    $profile_msg = '<span class="error-text">Invalid CSRF token.</span>';
  } else {
    $new_email = trim($_POST['email'] ?? '');
    $new_phone = trim($_POST['phone'] ?? '');
    if ($new_email && $new_phone) {
      $stmt = $pdo->prepare('UPDATE users SET email=?, phone=? WHERE id=?');
      $stmt->execute([$new_email, $new_phone, $user_id]);
      $profile_msg = 'Profile updated!';
      $user['email'] = $new_email;
      $user['phone'] = $new_phone;
    } else {
      $profile_msg = 'Please fill all fields.';
    }
  }
}
// Fetch user's outage reports from DB
$my_reports = $pdo->prepare('SELECT uo.*, t.username as technician, t.phone as technician_phone FROM user_outages uo LEFT JOIN users t ON uo.technician_id = t.id WHERE uo.user_id=? ORDER BY uo.created_at DESC');
$my_reports->execute([$user_id]);
$my_reports = $my_reports->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>User Dashboard - OutageSys</title>
  <link rel="stylesheet" href="css/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet"/>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
</head>
<body class="user">
  <!-- Topbar with Home and Avatar -->
  <div class="topbar">
    <a href="index.php" class="home-link">
      <i data-lucide="home" class="lucide-icon"></i> Home
    </a>
    <div class="avatar">
      <?php if (!empty($avatar)): ?>
        <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" loading="lazy" />
      <?php elseif ($name === 'admin'): ?>
        <i data-lucide="user" class="lucide-icon-large"></i>
      <?php else: ?>
        <?php echo strtoupper(substr($name,0,1)); ?>
      <?php endif; ?>
    </div>
  </div>
  <div class="dashboard">
    <?php include 'sidebar.php'; ?>
    <main class="main-content page-transition" role="main">
      <div aria-live="polite" id="ariaLiveRegion"></div>
      <nav aria-label="Breadcrumb" class="breadcrumb">
        <ol>
          <li><a href="index.php">Home</a></li>
          <li>›</li>
          <li>Dashboard</li>
        </ol>
      </nav>
      <div class="dashboard-grid">
        <div class="card">
          <h2>Welcome, <?php echo isset($user['name']) ? htmlspecialchars($user['name']) : 'User'; ?>!</h2>
          <div>Your location: <b><?php echo htmlspecialchars($user['location']); ?></b></div>
          <div>Status of your region's power: <span class="status-badge status-<?php echo str_replace(' ', '', $region_status); ?>"><?php echo $region_status; ?></span></div>
        </div>
        <div class="card">
          <h2>Report an Outage</h2>
          <?php if ($report_msg): ?><div class="success-msg"><?php echo $report_msg; ?></div><?php endif; ?>
          <form class="report-form" method="post">
            <input type="hidden" name="report_outage" value="1" />
            <label>Location</label>
            <input type="text" name="location" value="<?php echo htmlspecialchars($user['location']); ?>" required />
            <label>Time outage started</label>
            <input type="datetime-local" name="time_started" required />
            <label>Description</label>
            <textarea name="description" rows="2" placeholder="Describe the issue (optional)"></textarea>
            <button type="submit"><i class="fa fa-paper-plane"></i> Submit & Get Tracking ID</button>
          </form>
        </div>
        <div class="card">
          <h2>My Outage Reports</h2>
          <table class="styled-table">
            <thead>
              <tr><th>ID</th><th>Location</th><th>Status</th><th>Technician</th><th>Technician Phone</th><th>Description</th><th>Feedback</th><th>Action</th></tr>
            </thead>
            <tbody>
              <?php foreach ($my_reports as $r): ?>
              <tr>
                <td><?php echo 'RPT-' . str_pad($r['id'], 3, '0', STR_PAD_LEFT); ?></td>
                <td><?php echo htmlspecialchars($r['location']); ?></td>
                <td><?php echo $r['status']; ?></td>
                <td><?php echo $r['technician'] ? htmlspecialchars($r['technician']) : '<span class="placeholder-text">-</span>'; ?></td>
                <td><?php echo $r['technician_phone'] ? htmlspecialchars($r['technician_phone']) : '<span class="placeholder-text">-</span>'; ?></td>
                <td>
                  <?php if ($r['status'] === 'Resolved'): ?>
                    <form method="post" class="inline-form">
                      <input type="hidden" name="feedback_id" value="<?php echo $r['id']; ?>" />
                      <input type="text" name="feedback_text" placeholder="Feedback..." required class="inline-input" />
                      <button class="btn btn--primary" type="submit">Submit</button>
                    </form>
                    <?php if ($r['feedback']): ?>
                      <div class="success-msg inline-success"><?php echo htmlspecialchars($r['feedback']); ?></div>
                    <?php endif; ?>
                  <?php else: ?>
                    <?php echo $r['feedback'] ? htmlspecialchars($r['feedback']) : '<span class="placeholder-text">-</span>'; ?>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($r['status'] === 'Resolved' || $r['status'] === 'Assigned'): ?>
                    <form method="post" class="inline-form">
                      <input type="hidden" name="reopen_id" value="<?php echo $r['id']; ?>" />
                      <button class="btn btn--secondary" type="submit">Reopen</button>
                    </form>
                  <?php else: ?>
                    <span class="placeholder-text">-</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
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
  <script src="js/main.js"></script>
  <script>
    // Cookie consent banner
    if (!localStorage.getItem('cookieConsent')) {
      const banner = document.createElement('div');
      banner.className = 'cookie-banner';
      banner.innerHTML = 'This site uses cookies for analytics and user experience. <button class="cookie-btn" onclick="localStorage.setItem(\'cookieConsent\',1);this.parentNode.remove();">OK</button>';
      document.body.appendChild(banner);
    }
  </script>
</body>
</html> 