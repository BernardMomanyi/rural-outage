<?php
// about.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>About - OutageSys</title>
  <link rel="stylesheet" href="css/style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet"/>
  <style>
    .about-container { max-width: 700px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 40px; }
    .about-container h1 { color: #2563eb; margin-bottom: 16px; }
    .about-container h2 { color: #1e40af; margin-top: 24px; }
    .about-container ul { margin-left: 20px; }
    .about-container p, .about-container ul { color: #4a5568; }
    .about-container .footer { margin-top: 32px; color: #94a3b8; font-size: 0.95rem; text-align: center; }
  </style>
</head>
<body>
  <main class="main-content" role="main">
    <nav aria-label="Breadcrumb" style="margin-bottom:12px;"><ol style="list-style:none;display:flex;gap:8px;padding:0;"><li><a href="index.php">Home</a></li><li>›</li><li>About</li></ol></nav>
    <div class="dashboard-grid">
      <div class="card">
        <h2>About OutageSys</h2>
        <section class="overview">
          <h2>Project Overview</h2>
          <p>OutageSys is a smart, web-based system designed to predict, manage, and reduce power outages in rural Kenya. By leveraging historical data, analytics, and real-time reporting, we aim to improve electricity reliability and empower communities with actionable insights.</p>
        </section>
        <section class="features">
          <h2>Problem Statement</h2>
          <p>Unplanned power outages in rural areas cause economic losses and hinder development. There is a need for a predictive, data-driven approach to minimize these disruptions.</p>
          <h2>Objectives</h2>
          <ul>
            <li>Predict and prevent power outages using advanced analytics.</li>
            <li>Provide real-time monitoring and reporting for substations.</li>
            <li>Enable easy data upload and management for system users.</li>
            <li>Support role-based access and secure system management.</li>
          </ul>
          <h2>Team & Institution</h2>
          <p>Developed by: <b>Bernard Momanyi</b><br>Institution: <b>Jomo Kenyatta University of Agriculture and Technology</b><br>GitHub: <b><a href="https://github.com/BernardMomanyi/outagesys" target="_blank">github.com/BernardMomanyi/outagesys</a></b></p>
          <h2>Current Version</h2>
          <p>Version: <b>1.2.0</b> - Enhanced with advanced analytics, improved security, and better user experience.</p>
        </section>
      </div>
      <div class="card">
        <h2>Loading Example</h2>
        <div class="skeleton" style="width: 80%; height: 24px;"></div>
        <div class="skeleton" style="width: 60%; height: 18px;"></div>
        <div class="skeleton" style="width: 90%; height: 18px;"></div>
      </div>
    </div>
    <footer class="footer" role="contentinfo">
      <div>&copy; 2024 OutageSys | <a href="privacy_policy.html">Privacy Policy</a></div>
    </footer>
  </main>
</body>
</html> 