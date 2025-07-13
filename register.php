<?php
session_start();
require_once 'db.php';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    if (!$username || !$password || !$email || !$phone) {
        $error = 'All fields are required.';
    } else {
        // Check if username already exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Username already exists.';
        } else {
            // Check if email already exists
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $role = 'user';
                $status = 'pending';
                
                $stmt = $pdo->prepare('INSERT INTO users (username, password, role, status, email, phone) VALUES (?, ?, ?, ?, ?, ?)');
                try {
                    $stmt->execute([$username, $hash, $role, $status, $email, $phone]);
                    $success = 'Registration successful! Your account is pending admin approval.';
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
    .register-container {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 20px;
    }
    .register-card {
      background: white;
      border-radius: 12px;
      padding: 40px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 450px;
    }
    .register-card h2 {
      text-align: center;
      margin-bottom: 30px;
      color: #2563eb;
    }
    .error-msg {
      background: #fee2e2;
      color: #dc2626;
      padding: 12px;
      border-radius: 6px;
      margin-bottom: 20px;
      text-align: center;
    }
    .success-msg {
      background: #d1fae5;
      color: #065f46;
      padding: 12px;
      border-radius: 6px;
      margin-bottom: 20px;
      text-align: center;
    }
    .back-link {
      display: block;
      text-align: center;
      margin-top: 20px;
      color: #2563eb;
      text-decoration: none;
    }
    .back-link:hover {
      text-decoration: underline;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: 500;
      color: #374151;
    }
    .form-group input {
      width: 100%;
      padding: 12px;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      font-size: 16px;
      box-sizing: border-box;
    }
    .form-group input:focus {
      outline: none;
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }
  </style>
</head>
<body>
  <div class="register-container">
    <div class="register-card">
      <h2><i class="fa fa-user-plus"></i> Register for OutageSys</h2>
      <?php if ($error): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="success-msg"><?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>
      <form method="POST" action="">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" required autocomplete="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" />
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required autocomplete="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
        </div>
        <div class="form-group">
          <label for="phone">Phone Number</label>
          <input type="tel" id="phone" name="phone" required autocomplete="tel" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" />
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required autocomplete="new-password" />
        </div>
        <button type="submit" class="btn btn--primary" style="width: 100%;"><i class="fa fa-user-plus"></i> Register</button>
      </form>
      <a href="login.php" class="back-link"><i class="fa fa-sign-in-alt"></i> Already have an account? Login</a>
      <a href="index.php" class="back-link">&larr; Back to Home</a>
    </div>
  </div>
</body>
</html> 