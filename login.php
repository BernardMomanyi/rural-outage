<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'db.php';
require_once 'csrf.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!$username || !$password) {
        $error = 'Username and password are required.';
    } else {
        // Check if user exists
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Check account status
            if ($user['status'] !== 'active') {
                $error = 'Your account is pending approval by an admin.';
            } else {
                // Set session variables
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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - OutageSys</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    body { min-height: 100vh; }
    .login-bg {
      min-height: 100vh;
      display: flex;
      align-items: center;
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
  <button class="toggle-mode" id="darkModeToggle">
    <i class="fa fa-moon" id="modeIcon"></i> 
    <span id="modeText">Dark Mode</span>
  </button>
  <div class="login-bg">
    <div class="container">
      <div class="card" style="max-width: 400px; margin: 0 auto;">
        <h2 class="h2 text-center mb-md"><i class="fa fa-user-circle"></i> Login to OutageSys</h2>
        <?php if ($error): ?>
          <div class="error-msg mb-md"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="" class="mb-md">
          <div class="form-group mb-md">
            <label for="username" class="mb-xs">Username</label>
            <input type="text" id="username" name="username" required autocomplete="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" />
          </div>
          <div class="form-group mb-md">
            <label for="password" class="mb-xs">Password</label>
            <div class="password-toggle" style="display: flex; align-items: center; gap: 8px;">
              <input type="password" id="password" name="password" required autocomplete="current-password" style="flex:1;" />
              <span class="toggle-eye" onclick="togglePassword()" style="cursor:pointer;"><i class="fa fa-eye" id="eyeIcon"></i></span>
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100"><i class="fa fa-sign-in-alt"></i> Login</button>
        </form>
        <div class="grid grid-2 mb-md" style="gap: var(--space-xs);">
          <a href="register.php" class="btn btn-outline"><i class="fa fa-user-plus"></i> Register</a>
          <a href="index.php" class="btn btn-outline">&larr; Back to Home</a>
        </div>
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
  </script>
</body>
</html> 