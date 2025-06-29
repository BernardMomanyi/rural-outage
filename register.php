<?php
session_start();
require_once 'db.php';
require_once 'csrf.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    $error = 'Invalid CSRF token.';
  } else {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if (!$username || !$password || !$email || !$phone) {
      $error = 'All fields are required.';
    } else {
      $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
      $stmt->execute([$username]);
      if ($stmt->fetch()) {
        $error = 'Username already exists.';
      } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user';
        $status = 'pending';
        $stmt = $pdo->prepare('INSERT INTO users (username, password, role, status, email, phone) VALUES (?, ?, ?, ?, ?, ?)');
        try {
          $stmt->execute([$username, $hash, $role, $status, $email, $phone]);
          header('Location: login.php?registered=1');
          exit;
        } catch (Exception $e) {
          $error = 'Registration failed: ' . htmlspecialchars($e->getMessage());
        }
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register - OutageSys</title>
  <link rel="stylesheet" href="css/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    .password-toggle { position: relative; }
    .password-toggle .toggle-eye {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #2563eb;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-card">
      <h2><i class="fa fa-user-plus"></i> Register</h2>
      <?php if ($error): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>" />
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" />
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
        <label for="phone">Phone Number</label>
        <input type="text" id="phone" name="phone" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" />
        <label for="password">Password</label>
        <div class="password-toggle">
          <input type="password" id="password" name="password" required />
          <span class="toggle-eye" onclick="togglePassword('password', 'eyeIcon1')"><i class="fa fa-eye" id="eyeIcon1"></i></span>
        </div>
        <button type="submit" class="btn btn--primary" onclick="showLoadingOverlay()"><i class="fa fa-user-plus"></i> Register</button>
      </form>
      <a href="login.php" class="back-link">&larr; Back to Login</a>
    </div>
  </div>
  <div class="loading-overlay" id="globalLoading" aria-hidden="true"><span class="spinner"></span></div>
  <script>
    function togglePassword(inputId, eyeId) {
      const passwordInput = document.getElementById(inputId);
      const eyeIcon = document.getElementById(eyeId);
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
      } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
      }
    }

    function showLoadingOverlay() {
      const overlay = document.getElementById('globalLoading');
      overlay.setAttribute('aria-hidden', 'false');
      setTimeout(() => overlay.setAttribute('aria-hidden', 'true'), 2000); // Demo: hide after 2s
    }
  </script>
</body>
</html> 