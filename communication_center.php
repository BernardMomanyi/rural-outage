<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit;
}
$username = $_SESSION['username'];
$dashboard_link = 'admin_dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Communication Center - OutageSys</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    body { 
      min-height: 100vh; 
      margin: 0;
      font-family: 'Inter', sans-serif;
    }
    
    .communication-bg {
      min-height: 100vh;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 0;
    }
    
    .container {
      width: 100%;
      max-width: 1200px;
      padding: 2rem;
    }
    
    .card {
      background: #fff;
      border-radius: 18px;
      padding: 2.5em 2em;
      margin-bottom: 2em;
      box-shadow: 0 8px 32px rgba(0,0,0,0.1);
      border: 1px solid rgba(255,255,255,0.2);
    }
    
    .h2 {
      color: #2563eb;
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .small {
      color: #555;
      font-size: 0.9rem;
      margin-bottom: 1.5rem;
    }
    
    .btn {
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      font-size: 0.9rem;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .btn-primary {
      background: #2563eb;
      color: white;
    }
    
    .btn-primary:hover {
      background: #1d4ed8;
      transform: translateY(-1px);
    }
    
    .btn-outline {
      background: transparent;
      color: #2563eb;
      border: 2px solid #2563eb;
    }
    
    .btn-outline:hover {
      background: #2563eb;
      color: white;
    }
    
    .btn-success {
      background: #22c55e;
      color: white;
    }
    
    .btn-success:hover {
      background: #16a34a;
    }
    
    .btn-danger {
      background: #ef4444;
      color: white;
    }
    
    .btn-danger:hover {
      background: #dc2626;
    }
    
    .btn-sm {
      padding: 0.5rem 1rem;
      font-size: 0.8rem;
    }
    
    .form-group {
      margin-bottom: 1rem;
    }
    
    .form-label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: #374151;
      font-size: 0.9rem;
    }
    
    .form-input {
      width: 100%;
      padding: 0.75rem;
      border: 2px solid #e5e7eb;
      border-radius: 8px;
      font-size: 0.9rem;
      transition: border-color 0.2s;
      box-sizing: border-box;
    }
    
    .form-input:focus {
      outline: none;
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    }
    
    .form-select {
      width: 100%;
      padding: 0.75rem;
      border: 2px solid #e5e7eb;
      border-radius: 8px;
      font-size: 0.9rem;
      background: #fff;
      transition: border-color 0.2s;
    }
    
    .form-select:focus {
      outline: none;
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    }
    
    .alert {
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1rem;
      font-size: 0.9rem;
    }
    
    .alert-success {
      background: #dcfce7;
      color: #166534;
      border: 1px solid #bbf7d0;
    }
    
    .alert-error {
      background: #fef2f2;
      color: #dc2626;
      border: 1px solid #fecaca;
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }
    
    .stat-card {
      background: linear-gradient(135deg, #f1f5ff 0%, #e0eafc 100%);
      border-radius: 16px;
      padding: 1.5rem;
      border-left: 6px solid #2563eb;
      box-shadow: 0 4px 12px rgba(37,99,235,0.1);
      transition: all 0.3s ease;
    }
    
    .stat-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 25px rgba(37,99,235,0.15);
    }
    
    .stat-icon {
      font-size: 2rem;
      color: #2563eb;
      margin-bottom: 0.5rem;
    }
    
    .stat-number {
      font-size: 2rem;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 0.25rem;
    }
    
    .stat-label {
      color: #64748b;
      font-size: 0.9rem;
      font-weight: 500;
    }
    
    .loading {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid #f3f3f3;
      border-top: 3px solid #2563eb;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
      .container {
        padding: 1rem;
      }
      
      .card {
        padding: 1.5rem 1rem;
      }
      
      .stats-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <!-- Breadcrumbs navigation -->
  <nav aria-label="Breadcrumb" class="breadcrumbs-nav" style="margin: 1.5rem auto 0 auto; max-width:900px; background:transparent;">
    <ol class="breadcrumbs" style="display:flex; flex-wrap:wrap; gap:0.5em; list-style:none; padding:0; margin:0; font-size:1rem; background:transparent;">
      <li><a href="<?php echo $dashboard_link; ?>" class="breadcrumb-link"><i class="fa fa-tachometer-alt"></i> Admin Dashboard</a></li>
      <li style="color:var(--color-secondary);">&gt;</li>
      <li class="breadcrumb-current" style="color:var(--color-primary); font-weight:600;"><i class="fa fa-comments"></i> Communication Center</li>
    </ol>
  </nav>

  <div class="communication-bg">
    <div class="container">
      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="card stat-card">
          <div class="stat-icon"><i class="fa fa-bell"></i></div>
          <div class="stat-number" id="totalNotifications">Loading...</div>
          <div class="stat-label">Notifications Sent</div>
        </div>
        <div class="card stat-card">
          <div class="stat-icon"><i class="fa fa-envelope"></i></div>
          <div class="stat-number" id="totalFeedback">Loading...</div>
          <div class="stat-label">Feedback Messages</div>
        </div>
        <div class="card stat-card">
          <div class="stat-icon"><i class="fa fa-exclamation-circle"></i></div>
          <div class="stat-number" id="newFeedback">Loading...</div>
          <div class="stat-label">New Messages</div>
        </div>
        <div class="card stat-card">
          <div class="stat-icon"><i class="fa fa-reply"></i></div>
          <div class="stat-number" id="repliedFeedback">Loading...</div>
          <div class="stat-label">Replied Messages</div>
        </div>
      </div>

      <!-- Communication Center -->
      <div class="card" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-left: 6px solid #10b981;">
        <h3 style="margin-bottom: 1rem; color: #10b981; font-size: 1.2rem;">
          <i class="fa fa-comments"></i>
          Communication Center
        </h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
          <!-- Send Notifications -->
          <div style="background: rgba(255,255,255,0.8); padding: 1.5rem; border-radius: 8px;">
            <h4 style="margin-bottom: 1rem; color: #374151; font-size: 1.1rem;">
              <i class="fa fa-bell"></i>
              Send Notifications
            </h4>
            <form id="notificationForm">
              <div style="margin-bottom: 1rem;">
                <label class="form-label">Target Audience</label>
                <select id="notificationAudience" class="form-select">
                  <option value="all">All Users</option>
                  <option value="admin">Administrators Only</option>
                  <option value="technician">Technicians Only</option>
                  <option value="user">Regular Users Only</option>
                </select>
              </div>
              <div style="margin-bottom: 1rem;">
                <label class="form-label">Notification Type</label>
                <select id="notificationType" class="form-select">
                  <option value="info">Information</option>
                  <option value="warning">Warning</option>
                  <option value="success">Success</option>
                  <option value="error">Error</option>
                  <option value="maintenance">Maintenance Notice</option>
                  <option value="update">System Update</option>
                </select>
              </div>
              <div style="margin-bottom: 1rem;">
                <label class="form-label">Title</label>
                <input type="text" id="notificationTitle" class="form-input" placeholder="Enter notification title...">
              </div>
              <div style="margin-bottom: 1rem;">
                <label class="form-label">Message</label>
                <textarea id="notificationMessage" class="form-input" rows="4" placeholder="Enter your notification message..."></textarea>
              </div>
              <div style="margin-bottom: 1rem;">
                <label class="form-label">Priority</label>
                <select id="notificationPriority" class="form-select">
                  <option value="low">Low</option>
                  <option value="normal" selected>Normal</option>
                  <option value="high">High</option>
                  <option value="urgent">Urgent</option>
                </select>
              </div>
              <button type="submit" class="btn btn-success" style="width: 100%;">
                <i class="fa fa-paper-plane"></i>
                Send Notification
              </button>
            </form>
          </div>
          
          <!-- User Feedback Management -->
          <div style="background: rgba(255,255,255,0.8); padding: 1.5rem; border-radius: 8px;">
            <h4 style="margin-bottom: 1rem; color: #374151; font-size: 1.1rem;">
              <i class="fa fa-envelope"></i>
              User Feedback
            </h4>
            <div style="margin-bottom: 1rem;">
              <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                <button id="loadFeedbackBtn" class="btn btn-outline btn-sm">
                  <i class="fa fa-refresh"></i>
                  Refresh
                </button>
                <button id="markAllReadBtn" class="btn btn-outline btn-sm">
                  <i class="fa fa-check-double"></i>
                  Mark All Read
                </button>
              </div>
            </div>
            <div id="feedbackList" style="max-height: 300px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 4px; padding: 0.5rem;">
              <div style="text-align: center; color: #6b7280; padding: 1rem;">
                <i class="fa fa-spinner fa-spin"></i>
                Loading feedback...
              </div>
            </div>
          </div>
        </div>
        
        <!-- Communication History -->
        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(0,0,0,0.1);">
          <h4 style="margin-bottom: 1rem; color: #374151; font-size: 1.1rem;">
            <i class="fa fa-history"></i>
            Communication History
          </h4>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
            <!-- Recent Notifications -->
            <div style="background: rgba(255,255,255,0.8); padding: 1rem; border-radius: 8px;">
              <h5 style="margin-bottom: 0.5rem; color: #374151; font-size: 1rem;">
                <i class="fa fa-bell"></i>
                Recent Notifications
              </h5>
              <div id="notificationHistory" style="max-height: 200px; overflow-y: auto;">
                <!-- Notification history will be populated here -->
              </div>
            </div>
            
            <!-- Recent Responses -->
            <div style="background: rgba(255,255,255,0.8); padding: 1rem; border-radius: 8px;">
              <h5 style="margin-bottom: 0.5rem; color: #374151; font-size: 1rem;">
                <i class="fa fa-reply"></i>
                Recent Responses
              </h5>
              <div id="responseHistory" style="max-height: 200px; overflow-y: auto;">
                <!-- Response history will be populated here -->
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="footer mt-md small text-center" style="color: var(--color-secondary);">
        &copy; 2024 OutageSys. All rights reserved.
      </div>
    </div>
  </div>

  <script>
    // Global variables
    let allNotifications = [];
    let allFeedback = [];
    
    // Initialize
    loadStats();
    loadUserFeedback();
    loadNotificationHistory();
    
    // Load statistics
    function loadStats() {
      // Load notification count
      fetch('api/notifications.php?limit=1000')
        .then(res => res.json())
        .then(data => {
          document.getElementById('totalNotifications').textContent = Array.isArray(data) ? data.length : 0;
        })
        .catch(error => {
          console.error('Error loading notification stats:', error);
          document.getElementById('totalNotifications').textContent = 'Error';
        });
      
      // Load feedback stats
      fetch('api/contact_messages.php')
        .then(res => res.json())
        .then(data => {
          if (Array.isArray(data)) {
            document.getElementById('totalFeedback').textContent = data.length;
            document.getElementById('newFeedback').textContent = data.filter(f => f.status === 'new').length;
            document.getElementById('repliedFeedback').textContent = data.filter(f => f.status === 'replied').length;
          } else {
            document.getElementById('totalFeedback').textContent = '0';
            document.getElementById('newFeedback').textContent = '0';
            document.getElementById('repliedFeedback').textContent = '0';
          }
        })
        .catch(error => {
          console.error('Error loading feedback stats:', error);
          document.getElementById('totalFeedback').textContent = '0';
          document.getElementById('newFeedback').textContent = '0';
          document.getElementById('repliedFeedback').textContent = '0';
        });
    }
    
    function showNotification(message, type = 'success') {
      const notification = document.createElement('div');
      notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#22c55e' : '#ef4444'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1001;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        max-width: 300px;
        word-wrap: break-word;
      `;
      notification.textContent = message;
      document.body.appendChild(notification);
      
      setTimeout(() => {
        notification.style.transform = 'translateX(0)';
      }, 100);
      
      setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
          document.body.removeChild(notification);
        }, 300);
      }, 3000);
    }
    
    // Notification form submission
    document.getElementById('notificationForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const audience = document.getElementById('notificationAudience').value;
      const type = document.getElementById('notificationType').value;
      const title = document.getElementById('notificationTitle').value;
      const message = document.getElementById('notificationMessage').value;
      const priority = document.getElementById('notificationPriority').value;
      
      if (!title || !message) {
        showNotification('Please fill in both title and message.', 'error');
        return;
      }
      
      // Send notification via API
      fetch('api/notifications.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({
          message: `${title}: ${message}`,
          target_role: audience,
          type: type,
          priority: priority
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showNotification(`Notification sent successfully to ${data.recipients} recipients!`, 'success');
          document.getElementById('notificationForm').reset();
          loadNotificationHistory(); // Refresh the history
          loadStats(); // Refresh stats
        } else {
          showNotification(data.error || 'Failed to send notification', 'error');
        }
      })
      .catch(error => {
        console.error('Error sending notification:', error);
        showNotification('Network error occurred', 'error');
      });
    });
    
    // Load user feedback
    function loadUserFeedback() {
      fetch('api/contact_messages.php')
        .then(res => res.json())
        .then(data => {
          if (Array.isArray(data)) {
            allFeedback = data;
            displayFeedbackList(data);
          } else {
            console.error('Invalid response format:', data);
            allFeedback = [];
            displayFeedbackList([]);
          }
        })
        .catch(error => {
          console.error('Error loading feedback:', error);
          allFeedback = [];
          document.getElementById('feedbackList').innerHTML = `
            <div style="text-align: center; color: #ef4444; padding: 1rem;">
              <i class="fa fa-exclamation-triangle"></i>
              Error loading feedback
            </div>
          `;
        });
    }
    
    // Display feedback list
    function displayFeedbackList(feedback) {
      const container = document.getElementById('feedbackList');
      
      if (!feedback.length) {
        container.innerHTML = `
          <div style="text-align: center; color: #6b7280; padding: 1rem;">
            <i class="fa fa-inbox"></i>
            No feedback messages yet
          </div>
        `;
        return;
      }
      
      container.innerHTML = feedback.map(msg => `
        <div style="padding: 0.75rem; margin-bottom: 0.5rem; background: ${msg.status === 'new' ? '#fef3c7' : '#f3f4f6'}; border-radius: 4px; border-left: 3px solid ${msg.status === 'new' ? '#f59e0b' : '#6b7280'};">
          <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
            <div>
              <strong style="color: #374151;">${msg.name}</strong>
              <span style="color: #6b7280; font-size: 0.8rem;"> • ${msg.email}</span>
            </div>
            <div style="display: flex; gap: 0.25rem;">
              <button class="btn btn-outline btn-sm" onclick="viewFeedback(${msg.id})" title="View">
                <i class="fa fa-eye"></i>
              </button>
              <button class="btn btn-outline btn-sm" onclick="replyToFeedback(${msg.id})" title="Reply">
                <i class="fa fa-reply"></i>
              </button>
              <button class="btn btn-danger btn-sm" onclick="deleteFeedback(${msg.id})" title="Delete">
                <i class="fa fa-trash"></i>
              </button>
            </div>
          </div>
          <div style="color: #374151; font-size: 0.9rem; margin-bottom: 0.5rem;">
            ${msg.message.substring(0, 100)}${msg.message.length > 100 ? '...' : ''}
          </div>
          <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem;">
            <span style="color: #9ca3af;">${new Date(msg.created_at).toLocaleDateString()}</span>
            <span style="color: ${msg.status === 'new' ? '#f59e0b' : '#6b7280'}; font-weight: 500; text-transform: capitalize;">
              ${msg.status}
            </span>
          </div>
        </div>
      `).join('');
    }
    
    // View feedback details
    function viewFeedback(id) {
      fetch(`api/contact_messages.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
          if (data.error) {
            showNotification(data.error, 'error');
            return;
          }
          
          // Mark as read
          fetch('api/contact_messages.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ id: id, action: 'mark_read' })
          });
          
          // Show modal with feedback details
          showFeedbackModal(data);
        })
        .catch(error => {
          console.error('Error viewing feedback:', error);
          showNotification('Error loading feedback details', 'error');
        });
    }
    
    // Show feedback modal
    function showFeedbackModal(feedback) {
      const modal = document.createElement('div');
      modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
      `;
      
      modal.innerHTML = `
        <div style="background: white; border-radius: 16px; padding: 2rem; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="margin: 0; color: #374151;">Feedback from ${feedback.name}</h3>
            <button onclick="this.closest('.modal-bg').remove()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280;">&times;</button>
          </div>
          <div style="margin-bottom: 1rem;">
            <strong>From:</strong> ${feedback.name} (${feedback.email})
          </div>
          <div style="margin-bottom: 1rem;">
            <strong>Date:</strong> ${new Date(feedback.created_at).toLocaleString()}
          </div>
          <div style="margin-bottom: 1rem;">
            <strong>Status:</strong> <span style="color: ${feedback.status === 'new' ? '#f59e0b' : '#6b7280'}; text-transform: capitalize;">${feedback.status}</span>
          </div>
          <div style="margin-bottom: 1rem;">
            <strong>Message:</strong>
            <div style="background: #f9fafb; padding: 1rem; border-radius: 8px; margin-top: 0.5rem;">
              ${feedback.message}
            </div>
          </div>
          <div style="display: flex; gap: 1rem; justify-content: flex-end;">
            <button class="btn btn-outline" onclick="replyToFeedback(${feedback.id})">Reply</button>
            <button class="btn btn-outline" onclick="this.closest('.modal-bg').remove()">Close</button>
          </div>
        </div>
      `;
      
      document.body.appendChild(modal);
    }
    
    // Reply to feedback
    function replyToFeedback(id) {
      const reply = prompt('Enter your reply to this feedback:');
      if (reply) {
        fetch('api/contact_messages.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'same-origin',
          body: JSON.stringify({ 
            action: 'reply',
            id: id,
            reply: reply
          })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            showNotification('Reply sent successfully!', 'success');
            addToResponseHistory('Reply', reply, `Feedback #${id}`);
            loadUserFeedback(); // Refresh the list
            loadStats(); // Refresh stats
          } else {
            showNotification(data.error || 'Failed to send reply', 'error');
          }
        })
        .catch(error => {
          console.error('Error sending reply:', error);
          showNotification('Network error occurred', 'error');
        });
      }
    }
    
    // Delete feedback
    function deleteFeedback(id) {
      if (!confirm('Are you sure you want to delete this feedback?')) {
        return;
      }
      
      fetch('api/contact_messages.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ id: id })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showNotification('Feedback deleted successfully!', 'success');
          loadUserFeedback();
          loadStats(); // Refresh stats
        } else {
          showNotification(data.error || 'Failed to delete feedback', 'error');
        }
      })
      .catch(error => {
        console.error('Error deleting feedback:', error);
        showNotification('Network error occurred', 'error');
      });
    }
    
    // Refresh feedback button
    document.getElementById('loadFeedbackBtn').addEventListener('click', function() {
      this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Loading...';
      this.disabled = true;
      
      loadUserFeedback();
      
      setTimeout(() => {
        this.innerHTML = '<i class="fa fa-refresh"></i> Refresh';
        this.disabled = false;
      }, 1000);
    });
    
    // Mark all as read button
    document.getElementById('markAllReadBtn').addEventListener('click', function() {
      showNotification('Marking all feedback as read...', 'info');
      
      fetch('api/contact_messages.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ action: 'mark_all_read' })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showNotification(data.message || 'All feedback marked as read!', 'success');
          loadUserFeedback();
          loadStats(); // Refresh stats
        } else {
          showNotification(data.error || 'Failed to mark messages as read', 'error');
        }
      })
      .catch(error => {
        console.error('Error marking messages as read:', error);
        showNotification('Network error occurred', 'error');
      });
    });
    
    // Load notification history
    function loadNotificationHistory() {
      fetch('api/notifications.php?limit=10')
        .then(res => res.json())
        .then(data => {
          const history = document.getElementById('notificationHistory');
          history.innerHTML = '';
          
          if (!Array.isArray(data) || data.length === 0) {
            history.innerHTML = `
              <div style="text-align: center; color: #6b7280; padding: 1rem;">
                <i class="fa fa-bell"></i>
                No notifications sent yet
              </div>
            `;
            return;
          }
          
          data.forEach(notification => {
            const historyItem = document.createElement('div');
            historyItem.style.cssText = `
              padding: 0.5rem;
              margin-bottom: 0.5rem;
              background: rgba(255,255,255,0.8);
              border-radius: 4px;
              border-left: 3px solid #10b981;
              font-size: 0.9rem;
            `;
            
            const priorityColors = {
              'low': '#6b7280',
              'normal': '#10b981',
              'high': '#f59e0b',
              'urgent': '#ef4444'
            };
            
            const typeIcons = {
              'info': 'fa-info-circle',
              'warning': 'fa-exclamation-triangle',
              'success': 'fa-check-circle',
              'error': 'fa-times-circle',
              'maintenance': 'fa-tools',
              'update': 'fa-sync-alt'
            };
            
            // Add null checks and default values
            const notificationType = notification.type || 'info';
            const notificationPriority = notification.priority || 'normal';
            const notificationMessage = notification.message || 'No message';
            const targetRole = notification.target_role || 'all';
            const createdAt = notification.created_at || new Date().toISOString();
            
            historyItem.innerHTML = `
              <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.25rem;">
                <div style="font-weight: 600; color: #374151;">
                  <i class="fa ${typeIcons[notificationType] || 'fa-bell'}" style="color: ${priorityColors[notificationPriority] || '#10b981'};"></i>
                  ${notificationType.toUpperCase()}
                </div>
                <span style="font-size: 0.8rem; color: ${priorityColors[notificationPriority] || '#10b981'}; text-transform: uppercase;">
                  ${notificationPriority}
                </span>
              </div>
              <div style="color: #6b7280; font-size: 0.8rem; margin-bottom: 0.25rem;">
                ${notificationMessage}
              </div>
              <div style="color: #9ca3af; font-size: 0.7rem;">
                To: ${getAudienceLabel(targetRole)} • ${new Date(createdAt).toLocaleString()}
              </div>
            `;
            history.appendChild(historyItem);
          });
        })
        .catch(error => {
          console.error('Error loading notification history:', error);
          document.getElementById('notificationHistory').innerHTML = `
            <div style="text-align: center; color: #ef4444; padding: 1rem;">
              <i class="fa fa-exclamation-triangle"></i>
              Error loading notification history
            </div>
          `;
        });
    }
    
    // Helper functions
    function getAudienceLabel(audience) {
      const labels = {
        'all': 'All Users',
        'admin': 'Administrators',
        'technician': 'Technicians',
        'user': 'Regular Users'
      };
      return labels[audience] || audience;
    }
    
    // Add to response history
    function addToResponseHistory(type, message, recipient) {
      const history = document.getElementById('responseHistory');
      const timestamp = new Date().toLocaleTimeString();
      const historyItem = document.createElement('div');
      historyItem.style.cssText = `
        padding: 0.5rem;
        margin-bottom: 0.5rem;
        background: rgba(255,255,255,0.8);
        border-radius: 4px;
        border-left: 3px solid #3b82f6;
        font-size: 0.9rem;
      `;
      historyItem.innerHTML = `
        <div style="font-weight: 600; color: #374151;">${type}</div>
        <div style="color: #6b7280; font-size: 0.8rem;">${message}</div>
        <div style="color: #9ca3af; font-size: 0.7rem;">To: ${recipient} • ${timestamp}</div>
      `;
      history.insertBefore(historyItem, history.firstChild);
      
      // Keep only last 10 items
      while (history.children.length > 10) {
        history.removeChild(history.lastChild);
      }
    }
  </script>
</body>
</html> 