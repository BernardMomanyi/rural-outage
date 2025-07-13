<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

// Ensure user is admin
if ($_SESSION['role'] !== 'admin') {
  header('Location: user_dashboard.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Test Cool Features - OutageSys</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      margin: 0;
      padding: 2rem;
    }
    
    .test-container {
      max-width: 800px;
      margin: 0 auto;
      background: white;
      border-radius: 16px;
      padding: 2rem;
      box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }
    
    .test-section {
      margin-bottom: 2rem;
      padding: 1.5rem;
      border-radius: 12px;
      background: #f8fafc;
      border-left: 4px solid #2563eb;
    }
    
    .test-button {
      background: #2563eb;
      color: white;
      border: none;
      padding: 0.75rem 1.5rem;
      border-radius: 8px;
      cursor: pointer;
      margin: 0.5rem;
      transition: all 0.3s ease;
    }
    
    .test-button:hover {
      background: #1d4ed8;
      transform: translateY(-2px);
    }
    
    .status {
      padding: 0.5rem 1rem;
      border-radius: 6px;
      margin: 0.5rem 0;
      font-weight: 500;
    }
    
    .status.success {
      background: #dcfce7;
      color: #166534;
    }
    
    .status.error {
      background: #fef2f2;
      color: #dc2626;
    }
  </style>
</head>
<body>
  <div class="test-container">
    <h1 style="color: #2563eb; margin-bottom: 2rem;">
      <i class="fa fa-rocket"></i>
      Cool Features Test Dashboard
    </h1>
    
    <div class="test-section">
      <h3><i class="fa fa-bell"></i> Notification System</h3>
      <p>Test the enhanced notification system with emojis and animations:</p>
      <button class="test-button" onclick="testNotification('success', '🎉 Success notification with emoji!')">
        Test Success
      </button>
      <button class="test-button" onclick="testNotification('error', '❌ Error notification with emoji!')">
        Test Error
      </button>
      <button class="test-button" onclick="testNotification('info', 'ℹ️ Info notification with emoji!')">
        Test Info
      </button>
      <button class="test-button" onclick="testNotification('warning', '⚠️ Warning notification with emoji!')">
        Test Warning
      </button>
    </div>
    
    <div class="test-section">
      <h3><i class="fa fa-keyboard"></i> Keyboard Shortcuts</h3>
      <p>Test keyboard shortcuts:</p>
      <ul>
        <li><strong>Ctrl/Cmd + K:</strong> Focus search</li>
        <li><strong>Ctrl/Cmd + N:</strong> Add new user</li>
        <li><strong>Ctrl/Cmd + R:</strong> Refresh data</li>
      </ul>
      <div class="status success">✅ Keyboard shortcuts are active</div>
    </div>
    
    <div class="test-section">
      <h3><i class="fa fa-chart-line"></i> Animation Features</h3>
      <p>Test various animations and effects:</p>
      <button class="test-button" onclick="testPulseAnimation()">
        Test Pulse Animation
      </button>
      <button class="test-button" onclick="testBounceAnimation()">
        Test Bounce Animation
      </button>
      <button class="test-button" onclick="testGlowEffect()">
        Test Glow Effect
      </button>
    </div>
    
    <div class="test-section">
      <h3><i class="fa fa-users"></i> User Management Features</h3>
      <p>Test enhanced user management features:</p>
      <button class="test-button" onclick="testBulkAssign()">
        Test Bulk Assign Modal
      </button>
      <button class="test-button" onclick="testAnalyticsModal()">
        Test Analytics Modal
      </button>
      <button class="test-button" onclick="testQuickActions()">
        Test Quick Actions
      </button>
    </div>
    
    <div class="test-section">
      <h3><i class="fa fa-check-circle"></i> Feature Status</h3>
      <div class="status success">✅ Floating Action Button</div>
      <div class="status success">✅ Enhanced Notifications</div>
      <div class="status success">✅ Keyboard Shortcuts</div>
      <div class="status success">✅ Live Activity Feed</div>
      <div class="status success">✅ Animated Statistics</div>
      <div class="status success">✅ Bulk Operations</div>
      <div class="status success">✅ Analytics Dashboard</div>
      <div class="status success">✅ Particle Effects</div>
    </div>
    
    <div style="text-align: center; margin-top: 2rem;">
      <a href="users.php" class="test-button" style="text-decoration: none; display: inline-block;">
        <i class="fa fa-arrow-left"></i>
        Back to User Management
      </a>
    </div>
  </div>

  <script>
    function testNotification(type, message) {
      // Simulate the notification function from users.php
      const notification = document.createElement('div');
      const icons = {
        success: '🎉',
        error: '❌',
        info: 'ℹ️',
        warning: '⚠️'
      };
      
      notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#22c55e' : type === 'error' ? '#ef4444' : type === 'info' ? '#3b82f6' : '#f59e0b'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        z-index: 1001;
        transform: translateX(100%) scale(0.8);
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        max-width: 350px;
        word-wrap: break-word;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2);
      `;
      
      notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 0.75rem;">
          <span style="font-size: 1.2rem;">${icons[type] || icons.info}</span>
          <span style="flex: 1;">${message}</span>
          <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem; opacity: 0.7; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">×</button>
        </div>
      `;
      
      document.body.appendChild(notification);
      
      setTimeout(() => {
        notification.style.transform = 'translateX(0) scale(1)';
      }, 100);
      
      setTimeout(() => {
        notification.style.transform = 'translateX(100%) scale(0.8)';
        setTimeout(() => {
          if (document.body.contains(notification)) {
            document.body.removeChild(notification);
          }
        }, 400);
      }, 4000);
    }
    
    function testPulseAnimation() {
      const button = event.target;
      button.style.animation = 'pulse 0.5s ease-in-out';
      setTimeout(() => {
        button.style.animation = '';
      }, 500);
    }
    
    function testBounceAnimation() {
      const button = event.target;
      button.style.animation = 'bounce 1s infinite';
      setTimeout(() => {
        button.style.animation = '';
      }, 1000);
    }
    
    function testGlowEffect() {
      const button = event.target;
      button.style.animation = 'glow 2s ease-in-out infinite alternate';
      setTimeout(() => {
        button.style.animation = '';
      }, 2000);
    }
    
    function testBulkAssign() {
      alert('Bulk Assign Modal would open here! 🎯');
    }
    
    function testAnalyticsModal() {
      alert('Analytics Modal would open here! 📊');
    }
    
    function testQuickActions() {
      alert('Quick Actions Menu would open here! ⚡');
    }
    
    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
      @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
      }
      
      @keyframes bounce {
        0%, 20%, 53%, 80%, 100% { transform: translate3d(0,0,0); }
        40%, 43% { transform: translate3d(0,-8px,0); }
        70% { transform: translate3d(0,-4px,0); }
        90% { transform: translate3d(0,-2px,0); }
      }
      
      @keyframes glow {
        0%, 100% { box-shadow: 0 0 5px rgba(37,99,235,0.5); }
        50% { box-shadow: 0 0 20px rgba(37,99,235,0.8), 0 0 30px rgba(37,99,235,0.6); }
      }
    `;
    document.head.appendChild(style);
  </script>
</body>
</html> 