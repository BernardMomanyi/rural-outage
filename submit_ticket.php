<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
require_once 'db.php';

// Block admins from creating tickets for themselves unless creating for a user
if (
  isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && !isset($_POST['selected_user_id'])
) {
  // Fetch all non-admin users for the dropdown
  $users = $pdo->query("SELECT id, username, email FROM users WHERE role != 'admin' ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);
  echo "<div class='card' style='margin:2em auto;max-width:500px;text-align:center;'>";
  echo "<h3 style='color:#d97706;'><i class='fa fa-user-shield'></i> Admin: Create Ticket for User</h3>";
  echo "<form method='post' style='margin-top:1em;'>";
  echo "<label for='selected_user_id' class='form-label'>Select User</label>";
  echo "<select name='selected_user_id' id='selected_user_id' class='form-select' required>";
  echo "<option value=''>-- Choose a user --</option>";
  foreach ($users as $user) {
    echo "<option value='{$user['id']}'>{$user['username']} ({$user['email']})</option>";
  }
  echo "</select>";
  echo "<button type='submit' class='btn btn-primary' style='margin-top:1em;'>Proceed to Ticket Form</button>";
  echo "</form>";
  echo "<p style='color:#888; margin-top:1em;'>Admins cannot create tickets for themselves. Please select a user to create a ticket on their behalf.</p>";
  echo "</div>";
  exit;
}

// If admin is creating for a user, override user_id, username, and email
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && isset($_POST['selected_user_id'])) {
  $selected_user_id = intval($_POST['selected_user_id']);
  $user_stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE id = ?");
  $user_stmt->execute([$selected_user_id]);
  $selected_user = $user_stmt->fetch(PDO::FETCH_ASSOC);
  if ($selected_user) {
    $user_id = $selected_user['id'];
    $username = $selected_user['username'];
    $user_email = $selected_user['email'];
    // Set a flag so the form uses these values
    $_SESSION['admin_ticket_for_user'] = $selected_user;
  } else {
    echo "<p style='color:red;'>Invalid user selected.</p>";
    exit;
  }
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$user_email = $_SESSION['email'] ?? '';
$role = $_SESSION['role'];
$dashboard_link = 'user_dashboard.php';
if ($role === 'admin') $dashboard_link = 'admin_dashboard.php';
if ($role === 'technician') $dashboard_link = 'technician_dashboard.php';

// Fetch substations for location dropdown
$substations = $pdo->query('SELECT id, name FROM substations ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Submit Support Ticket - OutageSys</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    body { 
      min-height: 100vh; 
      margin: 0;
      font-family: 'Inter', sans-serif;
    }
    
    .submit-ticket-bg {
      min-height: 100vh;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 0;
    }
    
    .container {
      width: 100%;
      max-width: 800px;
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
    
    .form-group {
      margin-bottom: 1.5rem;
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
    
    .priority-info {
      background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 2rem;
      border-left: 6px solid #0ea5e9;
    }
    
    .priority-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-top: 1rem;
    }
    
    .priority-item {
      background: rgba(255,255,255,0.8);
      padding: 1rem;
      border-radius: 8px;
      text-align: center;
    }
    
    .priority-urgent {
      border-left: 4px solid #ef4444;
    }
    
    .priority-high {
      border-left: 4px solid #f59e0b;
    }
    
    .priority-medium {
      border-left: 4px solid #3b82f6;
    }
    
    .priority-low {
      border-left: 4px solid #10b981;
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
    
    /* Dark mode support */
    body.dark-mode .submit-ticket-bg {
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%) !important;
    }
    
    body.dark-mode .card {
      background: #2d3748 !important;
      color: #e2e8f0 !important;
      border-color: #4a5568 !important;
    }
    
    body.dark-mode .h2 {
      color: #63b3ed !important;
    }
    
    body.dark-mode .small {
      color: #cbd5e0 !important;
    }
    
    body.dark-mode .form-label {
      color: #e2e8f0 !important;
    }
    
    body.dark-mode .form-input,
    body.dark-mode .form-select {
      background: #4a5568 !important;
      color: #e2e8f0 !important;
      border-color: #718096 !important;
    }
    
    body.dark-mode .form-input:focus,
    body.dark-mode .form-select:focus {
      border-color: #63b3ed !important;
      box-shadow: 0 0 0 3px rgba(99,179,237,0.1) !important;
    }
    
    body.dark-mode .priority-info {
      background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%) !important;
      color: #e2e8f0 !important;
    }
    
    body.dark-mode .priority-item {
      background: rgba(45,55,72,0.8) !important;
      color: #e2e8f0 !important;
    }
    
    body.dark-mode .btn-outline {
      color: #63b3ed !important;
      border-color: #63b3ed !important;
    }
    
    body.dark-mode .btn-outline:hover {
      background: #63b3ed !important;
      color: #1a202c !important;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
      .container {
        padding: 1rem;
      }
      
      .card {
        padding: 1.5rem 1rem;
      }
      
      .priority-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <!-- Breadcrumbs navigation -->
  <nav aria-label="Breadcrumb" class="breadcrumbs-nav" style="margin: 1.5rem auto 0 auto; max-width:900px; background:transparent;">
    <ol class="breadcrumbs" style="display:flex; flex-wrap:wrap; gap:0.5em; list-style:none; padding:0; margin:0; font-size:1rem; background:transparent;">
      <li><a href="<?php echo $dashboard_link; ?>" class="breadcrumb-link"><i class="fa fa-tachometer-alt"></i> Dashboard</a></li>
      <li style="color:var(--color-secondary);">&gt;</li>
      <li class="breadcrumb-current" style="color:var(--color-primary); font-weight:600;"><i class="fa fa-ticket-alt"></i> Submit Ticket</li>
    </ol>
  </nav>

  <div class="submit-ticket-bg">
    <div class="container">
      <!-- Priority Information -->
      <div class="card priority-info">
        <h3 style="margin-bottom: 1rem; color: #0ea5e9; font-size: 1.2rem;">
          <i class="fa fa-info-circle"></i>
          Priority Guidelines
        </h3>
        <p style="margin-bottom: 1rem; color: #374151;">
          Choose the appropriate priority level for your issue to help us respond efficiently:
        </p>
        <div class="priority-grid">
          <div class="priority-item priority-urgent">
            <div style="font-weight: 600; color: #dc2626; margin-bottom: 0.5rem;">Urgent</div>
            <div style="font-size: 0.8rem; color: #6b7280;">Critical system failure, safety issues, complete service outage</div>
          </div>
          <div class="priority-item priority-high">
            <div style="font-weight: 600; color: #d97706; margin-bottom: 0.5rem;">High</div>
            <div style="font-size: 0.8rem; color: #6b7280;">Significant service disruption, billing issues</div>
          </div>
          <div class="priority-item priority-medium">
            <div style="font-weight: 600; color: #2563eb; margin-bottom: 0.5rem;">Medium</div>
            <div style="font-size: 0.8rem; color: #6b7280;">General inquiries, minor issues, feature requests</div>
          </div>
          <div class="priority-item priority-low">
            <div style="font-weight: 600; color: #059669; margin-bottom: 0.5rem;">Low</div>
            <div style="font-size: 0.8rem; color: #6b7280;">General questions, feedback, non-critical issues</div>
          </div>
        </div>
      </div>

      <!-- Submit Ticket Form -->
      <div class="card">
        <h2 class="h2">
          <i class="fa fa-ticket-alt"></i>
          Submit Support Ticket
        </h2>
        <p class="small">Please provide detailed information about your issue to help us assist you quickly.</p>
        
        <div id="alertMessage" style="display: none;"></div>
        
        <form id="ticketForm">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
            <div class="form-group">
              <label class="form-label">Subject *</label>
              <input type="text" id="ticketSubject" class="form-input" placeholder="Brief description of your issue" required />
            </div>
            <div class="form-group">
              <label class="form-label">Category *</label>
              <select id="ticketCategory" class="form-select" required>
                <option value="">Select Category</option>
                <option value="power_outage">Power Outage</option>
                <option value="equipment">Equipment Issue</option>
                <option value="billing">Billing Question</option>
                <option value="service">Service Request</option>
                <option value="technical">Technical Issue</option>
                <option value="safety">Safety Concern</option>
                <option value="general">General Inquiry</option>
              </select>
            </div>
          </div>
          
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
            <div class="form-group">
              <label class="form-label">Priority *</label>
              <select id="ticketPriority" class="form-select" required>
                <option value="">Select Priority</option>
                <option value="urgent">Urgent</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Contact Phone</label>
              <input type="tel" id="userPhone" class="form-input" placeholder="Optional - for faster contact" />
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Location *</label>
            <select id="ticketLocation" class="form-select" required onchange="handleLocationChange()">
              <option value="">Select Location</option>
              <?php foreach ($substations as $sub): ?>
                <option value="<?php echo htmlspecialchars($sub['name']); ?>"><?php echo htmlspecialchars($sub['name']); ?></option>
              <?php endforeach; ?>
              <option value="other">Other (Specify...)</option>
            </select>
            <input type="text" id="customLocation" class="form-input" placeholder="Enter custom location" style="display:none; margin-top:0.5rem;" />
          </div>
          <div class="form-group">
            <label class="form-label">Description *</label>
            <textarea id="ticketDescription" class="form-input" rows="6" placeholder="Please provide detailed information about your issue, including any error messages, steps to reproduce, and what you were trying to accomplish." required></textarea>
          </div>
          
          <div style="display: flex; gap: 1rem; justify-content: flex-end;">
            <button type="button" class="btn btn-outline" onclick="window.history.back()">
              <i class="fa fa-arrow-left"></i>
              Cancel
            </button>
            <button type="submit" class="btn btn-primary" id="submitBtn">
              <i class="fa fa-paper-plane"></i>
              Submit Ticket
            </button>
          </div>
        </form>
      </div>

      <!-- My Tickets -->
      <div class="card">
        <h3 style="margin-bottom: 1rem; color: #374151; font-size: 1.2rem;">
          <i class="fa fa-history"></i>
          My Recent Tickets
        </h3>
        <div id="myTickets">
          <div style="text-align: center; color: #6b7280; padding: 1rem;">
            <div class="loading"></div>
            <div style="margin-top: 0.5rem;">Loading your tickets...</div>
          </div>
        </div>
      </div>

      <div class="footer mt-md small text-center" style="color: var(--color-secondary);">
        &copy; 2024 OutageSys. All rights reserved.
      </div>
    </div>
  </div>

  <script>
    // Load user's tickets
    function loadMyTickets() {
      fetch('api/tickets.php')
        .then(res => res.json())
        .then(data => {
          if (Array.isArray(data)) {
            displayMyTickets(data);
          } else {
            document.getElementById('myTickets').innerHTML = `
              <div style="text-align: center; color: #6b7280; padding: 1rem;">
                <i class="fa fa-ticket-alt" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                No tickets found
              </div>
            `;
          }
        })
        .catch(error => {
          console.error('Error loading tickets:', error);
          document.getElementById('myTickets').innerHTML = `
            <div style="text-align: center; color: #ef4444; padding: 1rem;">
              <i class="fa fa-exclamation-triangle"></i>
              Error loading tickets
            </div>
          `;
        });
    }
    
    // Display user's tickets
    function displayMyTickets(tickets) {
      const container = document.getElementById('myTickets');
      
      if (!tickets.length) {
        container.innerHTML = `
          <div style="text-align: center; color: #6b7280; padding: 1rem;">
            <i class="fa fa-ticket-alt" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
            No tickets found
          </div>
        `;
        return;
      }
      
      container.innerHTML = tickets.slice(0, 5).map(ticket => `
        <div style="background: #f9fafb; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border-left: 4px solid #3b82f6;">
          <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
            <div>
              <strong style="color: #374151;">${ticket.subject}</strong>
              <span style="color: #6b7280; font-size: 0.8rem; margin-left: 1rem;">${ticket.ticket_number}</span>
            </div>
            <div style="display: flex; gap: 0.5rem;">
              <span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 500; text-transform: capitalize; background: ${getPriorityColor(ticket.priority)}; color: white;">
                ${ticket.priority}
              </span>
              <span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 500; text-transform: capitalize; background: ${getStatusColor(ticket.status)}; color: white;">
                ${ticket.status.replace('_', ' ')}
              </span>
            </div>
          </div>
          <div style="color: #6b7280; font-size: 0.9rem; margin-bottom: 0.5rem;">
            <strong>Category:</strong> ${ticket.category} • 
            <strong>Created:</strong> ${new Date(ticket.created_at).toLocaleDateString()}
          </div>
          ${ticket.assigned_technician_name ? `
            <div style="color: #059669; font-size: 0.9rem;">
              <strong>Assigned to:</strong> ${ticket.assigned_technician_name}
              ${ticket.assigned_technician_phone ? ` • Phone: ${ticket.assigned_technician_phone}` : ''}
              ${ticket.assigned_technician_email ? ` • Email: ${ticket.assigned_technician_email}` : ''}
            </div>
          ` : ''}
        </div>
      `).join('');
    }
    
    // Helper functions for colors
    function getPriorityColor(priority) {
      const colors = {
        urgent: '#ef4444',
        high: '#f59e0b',
        medium: '#3b82f6',
        low: '#10b981'
      };
      return colors[priority] || '#6b7280';
    }
    
    function getStatusColor(status) {
      const colors = {
        pending: '#f59e0b',
        assigned: '#3b82f6',
        in_progress: '#f59e0b',
        resolved: '#10b981',
        closed: '#6b7280'
      };
      return colors[status] || '#6b7280';
    }
    
    // Show notification
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
    
    // Form submission
    document.getElementById('ticketForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const submitBtn = document.getElementById('submitBtn');
      const originalText = submitBtn.innerHTML;
      submitBtn.innerHTML = '<div class="loading"></div> Submitting...';
      submitBtn.disabled = true;
      
      const locationSelect = document.getElementById('ticketLocation');
      const customLocationInput = document.getElementById('customLocation');
      let locationValue = locationSelect.value === 'other' ? customLocationInput.value : locationSelect.value;
      const formData = {
        action: 'create',
        user_name: '<?php echo htmlspecialchars($username); ?>',
        user_email: '<?php echo htmlspecialchars($user_email); ?>',
        user_phone: document.getElementById('userPhone').value,
        subject: document.getElementById('ticketSubject').value,
        description: document.getElementById('ticketDescription').value,
        priority: document.getElementById('ticketPriority').value,
        category: document.getElementById('ticketCategory').value,
        location: locationValue
      };
      
      fetch('api/tickets.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(formData)
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showNotification(`Ticket submitted successfully! Your ticket number is: ${data.ticket_number}`, 'success');
          document.getElementById('ticketForm').reset();
          loadMyTickets(); // Refresh the tickets list
        } else {
          showNotification(data.error || 'Failed to submit ticket', 'error');
        }
      })
      .catch(error => {
        console.error('Error submitting ticket:', error);
        showNotification('Network error occurred', 'error');
      })
      .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
      });
    });
    
    // Handle URL parameters for auto-fill
    function handleUrlParameters() {
      const urlParams = new URLSearchParams(window.location.search);
      const category = urlParams.get('category');
      const priority = urlParams.get('priority');
      
      if (category) {
        const categorySelect = document.getElementById('ticketCategory');
        categorySelect.value = category;
        
        // Auto-fill subject based on category
        const subjectInput = document.getElementById('ticketSubject');
        const descriptions = {
          'power_outage': 'Power Outage Report',
          'equipment': 'Equipment Issue',
          'billing': 'Billing Question',
          'service': 'Service Request',
          'technical': 'Technical Issue',
          'safety': 'Safety Concern',
          'general': 'General Inquiry'
        };
        
        if (descriptions[category]) {
          subjectInput.value = descriptions[category];
        }
        
        // Auto-fill description template for outages
        if (category === 'power_outage') {
          const descriptionInput = document.getElementById('ticketDescription');
          descriptionInput.value = `Power outage details:
• Location: [Please specify your location/substation]
• Time started: [When did the outage begin?]
• Affected areas: [Which areas are affected?]
• Any visible damage: [Describe any visible damage to equipment]
• Additional details: [Any other relevant information]`;
        }
      }
      
      if (priority) {
        document.getElementById('ticketPriority').value = priority;
      }
      
      // Auto-focus on description if it's an outage
      if (category === 'power_outage') {
        setTimeout(() => {
          document.getElementById('ticketDescription').focus();
        }, 500);
      }
    }

    function handleLocationChange() {
      const locationSelect = document.getElementById('ticketLocation');
      const customLocationInput = document.getElementById('customLocation');

      if (locationSelect.value === 'other') {
        customLocationInput.style.display = 'block';
        customLocationInput.setAttribute('required', 'required');
      } else {
        customLocationInput.style.display = 'none';
        customLocationInput.removeAttribute('required');
        customLocationInput.value = ''; // Clear custom location if not selected
      }
    }
    
    // Initialize
    loadMyTickets();
    handleUrlParameters();
  </script>
  <script src="js/dark-mode.js"></script>
</body>
</html> 