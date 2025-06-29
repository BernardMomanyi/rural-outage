<?php
session_start();
require_once 'db.php';
require_once 'csrf.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] !== 'active') {
                $error = 'Your account is pending approval by an admin.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: admin_dashboard.php');
                } elseif ($user['role'] === 'technician') {
                    header('Location: technician_dashboard.php');
                } else {
                    header('Location: user_dashboard.php');
                }
                exit;
            }
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - OutageSys</title>
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
      <h2><i class="fa fa-user-circle"></i> Login to OutageSys</h2>
      <?php if ($error): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>" />
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" />
        <label for="password">Password</label>
        <div class="password-toggle">
          <input type="password" id="password" name="password" required />
          <span class="toggle-eye" onclick="togglePassword()"><i class="fa fa-eye" id="eyeIcon"></i></span>
        </div>
        <button type="submit" class="btn btn--primary" onclick="showLoadingOverlay()"><i class="fa fa-sign-in-alt"></i> Login</button>
      </form>
      <a href="register.php" class="back-link"><i class="fa fa-user-plus"></i> Register</a>
      <a href="index.php" class="back-link">&larr; Back to Home</a>
    </div>
  </div>
  <div class="loading-overlay" id="globalLoading" aria-hidden="true"><span class="spinner"></span></div>
  <script>
    function togglePassword() {
      const passwordInput = document.getElementById('password');
      const eyeIcon = document.getElementById('eyeIcon');
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