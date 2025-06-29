<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Welcome to OutageSys</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: var(--bg, #f8fafc);
      margin: 0;
      padding: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      transition: background 0.3s, color 0.3s;
    }
    .landing-container {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 4px 24px rgba(0,0,0,0.08);
      padding: 48px 32px 32px 32px;
      max-width: 400px;
      width: 100%;
      text-align: center;
    }
    .landing-container h1 {
      margin-bottom: 8px;
      font-size: 2.2rem;
      color: #2d3748;
    }
    .landing-container p {
      color: #4a5568;
      margin-bottom: 32px;
    }
    .landing-links {
      display: flex;
      flex-direction: column;
      gap: 16px;
      margin-bottom: 24px;
    }
    .landing-links a {
      display: block;
      padding: 12px 0;
      border-radius: 8px;
      background: #2563eb;
      color: #fff;
      text-decoration: none;
      font-weight: 500;
      font-size: 1.1rem;
      transition: background 0.2s;
    }
    .landing-links a.secondary {
      background: #f1f5f9;
      color: #2563eb;
      border: 1px solid #2563eb;
    }
    .landing-links a:hover {
      background: #1e40af;
      color: #fff;
    }
    .footer {
      margin-top: 32px;
      color: #94a3b8;
      font-size: 0.95rem;
    }
    .toggle-mode {
      position: absolute;
      top: 24px;
      right: 24px;
      background: #f1f5f9;
      border: none;
      border-radius: 20px;
      padding: 8px 16px;
      font-size: 1rem;
      cursor: pointer;
      color: #2563eb;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: background 0.2s, color 0.2s;
    }
    body.dark {
      --bg: #181a1b;
      background: #181a1b;
      color: #f1f5f9;
    }
    body.dark .landing-container {
      background: #23272f;
      color: #f1f5f9;
    }
    body.dark .landing-links a.secondary {
      background: #23272f;
      color: #60a5fa;
      border: 1px solid #60a5fa;
    }
    body.dark .landing-links a {
      background: #2563eb;
      color: #fff;
    }
    body.dark .footer {
      color: #64748b;
    }
  </style>
</head>
<body>
  <button class="toggle-mode" onclick="toggleMode()"><i class="fa fa-moon" id="modeIcon"></i> <span id="modeText">Dark Mode</span></button>
  <div class="landing-container">
    <h1><i class="fa fa-bolt"></i> OutageSys</h1>
    <p>Welcome to OutageSys, your smart rural power outage prediction and management platform.</p>
    <div class="landing-links">
      <a href="login.php"><i class="fa fa-sign-in-alt"></i> Login</a>
      <a href="register.php" class="secondary"><i class="fa fa-user-plus"></i> Register</a>
      <a href="about.php" class="secondary"><i class="fa fa-info-circle"></i> About Us</a>
      <a href="contact.php" class="secondary"><i class="fa fa-envelope"></i> Contact</a>
    </div>
    <div class="footer">&copy; 2024 OutageSys. All rights reserved.</div>
  </div>
  <script>
    function setMode(dark) {
      document.body.classList.toggle('dark', dark);
      document.getElementById('modeIcon').className = dark ? 'fa fa-sun' : 'fa fa-moon';
      document.getElementById('modeText').textContent = dark ? 'Light Mode' : 'Dark Mode';
      localStorage.setItem('darkMode', dark ? '1' : '0');
    }
    function toggleMode() {
      setMode(!document.body.classList.contains('dark'));
    }
    // On load
    if (localStorage.getItem('darkMode') === '1') setMode(true);
  </script>
</body>
</html>
