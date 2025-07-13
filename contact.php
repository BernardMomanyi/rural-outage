<?php
session_start();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message_text = trim($_POST['message'] ?? '');
    
    if (!$name || !$email || !$message_text) {
        $error = 'Please fill in all required fields.';
    } else {
        // In a real application, you would save to database or send email
        $message = 'Thank you for your message! We will get back to you soon.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us - OutageSys</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    body { min-height: 100vh; }
    .contact-bg {
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
    .breadcrumb-link {
      color: var(--color-primary, #2563eb);
      text-decoration: none;
      transition: color 0.2s;
      background: transparent;
    }
    .breadcrumb-link:hover {
      color: var(--color-accent, #f43f5e);
      text-decoration: underline;
    }
    body.dark-mode .breadcrumb-link {
      color: #90cdf4;
    }
    .breadcrumbs-nav {
      width: 100%;
      padding-left: 1.5rem;
      padding-right: 1.5rem;
      background: transparent;
    }
    @media (max-width: 600px) {
      .breadcrumbs-nav { font-size: 0.95rem; padding-left: 0.5rem; padding-right: 0.5rem; }
      .breadcrumbs { font-size: 0.95rem; }
    }
  </style>
</head>
<body>
  <!-- Breadcrumbs navigation -->
  <nav aria-label="Breadcrumb" class="breadcrumbs-nav" style="margin: 1.5rem auto 0 auto; max-width:900px; background:transparent;">
    <ol class="breadcrumbs" style="display:flex; flex-wrap:wrap; gap:0.5em; list-style:none; padding:0; margin:0; font-size:1rem; background:transparent;">
      <li><a href="user_dashboard.php" class="breadcrumb-link"><i class="fa fa-tachometer-alt"></i> Dashboard</a></li>
      <li style="color:var(--color-secondary);">&gt;</li>
      <li class="breadcrumb-current" style="color:var(--color-primary); font-weight:600;"><i class="fa fa-envelope"></i> Contact</li>
    </ol>
  </nav>
  <button class="toggle-mode" id="darkModeToggle">
    <i class="fa fa-moon" id="modeIcon"></i> 
    <span id="modeText">Dark Mode</span>
  </button>
  <div class="contact-bg">
    <div class="container">
      <div class="card" style="max-width: 500px; margin: 0 auto;">
        <h1 class="h1 text-center mb-md"><i class="fa fa-envelope"></i> Contact Us</h1>
        <?php if ($error): ?>
          <div class="error-msg mb-md"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($message): ?>
          <div class="success-msg mb-md"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="POST" action="" class="mb-md">
          <div class="form-group mb-md">
            <label for="name" class="mb-xs">Name</label>
            <input type="text" id="name" name="name" required />
          </div>
          <div class="form-group mb-md">
            <label for="email" class="mb-xs">Email</label>
            <input type="email" id="email" name="email" required />
          </div>
          <div class="form-group mb-md">
            <label for="message" class="mb-xs">Message</label>
            <textarea id="message" name="message" required rows="5"></textarea>
          </div>
          <button type="submit" class="btn btn-primary w-100"><i class="fa fa-paper-plane"></i> Send Message</button>
        </form>
        <a href="index.php" class="btn btn-outline"><i class="fa fa-arrow-left"></i> Back to Home</a>
        <div class="contact-info mt-md">
          <h3 class="h3 mb-xs"><i class="fa fa-phone"></i> Contact Info</h3>
          <p class="mb-xs">Email: support@outagesys.com</p>
          <p class="mb-xs">Phone: +254 700 000 000</p>
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
  </script>
</body>
</html> 