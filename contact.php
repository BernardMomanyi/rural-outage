<?php
require_once 'csrf.php';
$msg = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    $error = 'Invalid CSRF token.';
  } else {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if (!$name || !$email || !$message) {
      $error = 'All fields are required.';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $error = 'Invalid email address.';
    } else {
      // Here you would normally send the message or store it in DB
      $msg = 'Thank you for contacting us! We will get back to you soon.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Contact - OutageSys</title>
  <link rel="stylesheet" href="css/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet"/>
  <style>
    .contact-container { max-width: 500px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 32px; }
    .contact-container h1 { margin-bottom: 16px; color: #2563eb; }
    .contact-container form { display: flex; flex-direction: column; gap: 16px; }
    .contact-container input, .contact-container textarea { padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 1rem; }
    .contact-container button { background: #2563eb; color: #fff; border: none; padding: 12px; border-radius: 6px; font-size: 1.1rem; cursor: pointer; transition: background 0.2s; }
    .contact-container button:hover { background: #1e40af; }
    .contact-info { margin-top: 24px; color: #4a5568; font-size: 1rem; }
    .contact-container .footer { margin-top: 32px; color: #94a3b8; font-size: 0.95rem; text-align: center; }
  </style>
</head>
<body>
  <main class="main-content" role="main">
    <nav aria-label="Breadcrumb" class="breadcrumb">
      <ol>
        <li><a href="index.php">Home</a></li>
        <li>›</li>
        <li>Contact</li>
      </ol>
    </nav>
    <div class="dashboard-grid">
      <div class="card">
        <h2>Contact Us</h2>
        <?php if ($msg): ?><div class="success-msg"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="error-msg"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="post" action="">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>" />
          <input type="text" name="name" placeholder="Your Name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" />
          <input type="email" name="email" placeholder="Your Email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
          <textarea name="message" rows="5" placeholder="Your Message" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
          <button type="submit" class="btn btn--primary" onclick="showLoadingOverlay()">Send Message</button>
        </form>
        <div class="contact-info">
          <p>Email: support@outagesys.com</p>
          <p>Phone: +254 700 000 000</p>
          <p>Address: Nairobi, Kenya</p>
        </div>
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