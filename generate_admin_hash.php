<?php
$hash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['password'])) {
    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Generate Admin Password Hash</title>
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>
  <div class="login-container">
    <div class="login-card">
      <h2>Generate Admin Password Hash</h2>
      <form method="POST" action="">
        <label for="password">Enter Password</label>
        <input type="text" id="password" name="password" required />
        <button type="submit" class="btn">Generate Hash</button>
      </form>
      <?php if ($hash): ?>
        <div class="success-msg">
          <strong>Hash:</strong>
          <pre><?php echo htmlspecialchars($hash); ?></pre>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html> 