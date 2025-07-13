<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
require_once 'db.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Get user profile data
$stmt = $pdo->prepare("
  SELECT id, username, email, phone, role, status, first_name, last_name, 
         department, position, bio, avatar, two_factor, created_at, last_login
  FROM users WHERE id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Calculate profile completion
$profileFields = ['first_name', 'last_name', 'department', 'position', 'bio', 'phone', 'avatar'];
$completedFields = 0;
foreach ($profileFields as $field) {
  if (!empty($user[$field])) $completedFields++;
}
$completionPercentage = round(($completedFields / count($profileFields)) * 100);

$dashboard_link = 'user_dashboard.php';
if ($role === 'admin') $dashboard_link = 'admin_dashboard.php';
if ($role === 'technician') $dashboard_link = 'technician_dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile - OutageSys</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    body { min-height: 100vh; }
    .profile-bg {
      min-height: 100vh;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 2rem 0;
    }
    
    .profile-container {
      max-width: 1000px;
      margin: 0 auto;
      padding: 0 1rem;
    }
    
    .profile-header {
      background: rgba(255,255,255,0.95);
      border-radius: 20px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: 0 8px 32px rgba(0,0,0,0.1);
      backdrop-filter: blur(10px);
    }
    
    .profile-avatar {
      position: relative;
      display: inline-block;
      margin-bottom: 1rem;
    }
    
    .profile-avatar img {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      border: 4px solid #ec4899;
      object-fit: cover;
      cursor: pointer;
      transition: transform 0.3s ease;
    }
    
    .profile-avatar img:hover {
      transform: scale(1.05);
    }
    
    .avatar-upload-btn {
      position: absolute;
      bottom: 0;
      right: 0;
      background: #ec4899;
      color: white;
      border: none;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      cursor: pointer;
      font-size: 16px;
      box-shadow: 0 4px 12px rgba(236,72,153,0.3);
    }
    
    .completion-card {
      background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%);
      border-radius: 16px;
      padding: 1.5rem;
      margin-bottom: 2rem;
      border-left: 6px solid #ec4899;
    }
    
    .completion-bar {
      background: #e5e7eb;
      height: 12px;
      border-radius: 6px;
      overflow: hidden;
      margin: 1rem 0;
    }
    
    .completion-fill {
      background: linear-gradient(90deg, #ec4899, #f472b6);
      height: 100%;
      border-radius: 6px;
      transition: width 0.5s ease;
    }
    
    .profile-section {
      background: rgba(255,255,255,0.95);
      border-radius: 16px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    }
    
    .profile-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
    }
    
    .form-group {
      margin-bottom: 1.5rem;
    }
    
    .form-label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: #374151;
    }
    
    .form-input {
      width: 100%;
      padding: 0.75rem;
      border: 2px solid #e5e7eb;
      border-radius: 8px;
      font-size: 1rem;
      transition: border-color 0.2s;
    }
    
    .form-input:focus {
      outline: none;
      border-color: #ec4899;
      box-shadow: 0 0 0 3px rgba(236,72,153,0.1);
    }
    
    .btn-save {
      background: linear-gradient(135deg, #ec4899, #f472b6);
      color: white;
      border: none;
      padding: 0.75rem 2rem;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: transform 0.2s;
    }
    
    .btn-save:hover {
      transform: translateY(-2px);
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-top: 1rem;
    }
    
    .stat-card {
      background: rgba(255,255,255,0.8);
      padding: 1rem;
      border-radius: 12px;
      text-align: center;
      border-left: 4px solid #ec4899;
    }
    
    .stat-number {
      font-size: 2rem;
      font-weight: 700;
      color: #ec4899;
      margin-bottom: 0.5rem;
    }
    
    .stat-label {
      font-size: 0.9rem;
      color: #6b7280;
    }
    
    /* Dark mode support */
    body.dark-mode .profile-header,
    body.dark-mode .profile-section {
      background: rgba(45,55,72,0.95);
      color: #e2e8f0;
    }
    
    body.dark-mode .form-label {
      color: #e2e8f0;
    }
    
    body.dark-mode .form-input {
      background: #2d3748;
      border-color: #4a5568;
      color: #e2e8f0;
    }
    
    body.dark-mode .stat-card {
      background: rgba(45,55,72,0.8);
      color: #e2e8f0;
    }
  </style>
</head>
<body>
  <!-- Breadcrumbs navigation -->
  <nav aria-label="Breadcrumb" class="breadcrumbs-nav" style="margin: 1.5rem auto 0 auto; max-width:900px; background:transparent;">
    <ol class="breadcrumbs" style="display:flex; flex-wrap:wrap; gap:0.5em; list-style:none; padding:0; margin:0; font-size:1rem; background:transparent;">
      <li><a href="<?php echo $dashboard_link; ?>" class="breadcrumb-link"><i class="fa fa-tachometer-alt"></i> Dashboard</a></li>
      <li style="color:var(--color-secondary);">&gt;</li>
      <li class="breadcrumb-current" style="color:var(--color-primary); font-weight:600;"><i class="fa fa-user"></i> My Profile</li>
    </ol>
  </nav>

  <div class="profile-bg">
    <div class="profile-container">
      <!-- Profile Header -->
      <div class="profile-header">
        <div style="text-align: center;">
          <div class="profile-avatar">
            <img id="profileAvatar" src="<?php echo $user['avatar'] ? 'uploads/avatars/' . $user['avatar'] : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxjaXJjbGUgY3g9IjUwIiBjeT0iMzUiIHI9IjE1IiBmaWxsPSIjOEM5Q0FBIi8+CjxwYXRoIGQ9Ik0yMCA4MEMyMCA2NS4zNzIgMzEuMzcyIDU0IDQ2IDU0SDU0QzY4LjYyOCA1NCA4MCA2NS4zNzIgODAgODBWNzBIMjBWOThaIiBmaWxsPSIjOEM5Q0FBIi8+Cjwvc3ZnPgo='; ?>" alt="Profile Picture">
            <button class="avatar-upload-btn" id="changeAvatarBtn">
              <i class="fa fa-camera"></i>
            </button>
          </div>
          <h1 style="margin: 0 0 0.5rem 0; color: #374151; font-size: 2rem;">
            <?php echo $user['first_name'] && $user['last_name'] ? $user['first_name'] . ' ' . $user['last_name'] : $user['username']; ?>
          </h1>
          <p style="margin: 0 0 1rem 0; color: #6b7280; font-size: 1.1rem;">
            @<?php echo $user['username']; ?> • <?php echo ucfirst($user['role']); ?>
          </p>
          <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <span class="badge badge-<?php echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'technician' ? 'primary' : 'success'); ?>">
              <?php echo ucfirst($user['role']); ?>
            </span>
            <span class="badge badge-<?php echo $user['status'] === 'active' ? 'success' : ($user['status'] === 'inactive' ? 'warning' : 'danger'); ?>">
              <?php echo ucfirst($user['status']); ?>
            </span>
          </div>
        </div>
      </div>

      <!-- Profile Completion -->
      <div class="completion-card">
        <h3 style="margin-bottom: 1rem; color: #ec4899; font-size: 1.3rem;">
          <i class="fa fa-chart-pie"></i>
          Profile Completion
        </h3>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
          <span style="font-size: 1.1rem; color: #374151;">Complete your profile to unlock more features</span>
          <span style="font-size: 1.5rem; font-weight: 700; color: #ec4899;"><?php echo $completionPercentage; ?>%</span>
        </div>
        <div class="completion-bar">
          <div class="completion-fill" style="width: <?php echo $completionPercentage; ?>%;"></div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
          <div style="background: rgba(255,255,255,0.8); padding: 1rem; border-radius: 8px; text-align: center;">
            <div style="font-size: 1.5rem; font-weight: 700; color: #10b981;"><?php echo $completedFields; ?></div>
            <div style="font-size: 0.9rem; color: #6b7280;">Completed Fields</div>
          </div>
          <div style="background: rgba(255,255,255,0.8); padding: 1rem; border-radius: 8px; text-align: center;">
            <div style="font-size: 1.5rem; font-weight: 700; color: #f59e0b;"><?php echo count($profileFields) - $completedFields; ?></div>
            <div style="font-size: 0.9rem; color: #6b7280;">Remaining Fields</div>
          </div>
        </div>
      </div>

      <!-- Profile Information -->
      <div class="profile-section">
        <h3 style="margin-bottom: 2rem; color: #374151; font-size: 1.5rem;">
          <i class="fa fa-user-edit"></i>
          Profile Information
        </h3>
        
        <form id="profileForm">
          <div class="profile-grid">
            <!-- Basic Information -->
            <div>
              <h4 style="margin-bottom: 1rem; color: #374151; font-size: 1.2rem;">
                <i class="fa fa-user"></i>
                Basic Information
              </h4>
              
              <div class="form-group">
                <label class="form-label">First Name</label>
                <input type="text" id="firstName" class="form-input" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" placeholder="Enter your first name">
              </div>
              
              <div class="form-group">
                <label class="form-label">Last Name</label>
                <input type="text" id="lastName" class="form-input" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" placeholder="Enter your last name">
              </div>
              
              <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" id="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" readonly style="background: #f3f4f6;">
              </div>
              
              <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="text" id="phone" class="form-input" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="Enter your phone number">
              </div>
            </div>
            
            <!-- Professional Information -->
            <div>
              <h4 style="margin-bottom: 1rem; color: #374151; font-size: 1.2rem;">
                <i class="fa fa-briefcase"></i>
                Professional Information
              </h4>
              
              <div class="form-group">
                <label class="form-label">Department</label>
                <input type="text" id="department" class="form-input" value="<?php echo htmlspecialchars($user['department'] ?? ''); ?>" placeholder="Enter your department">
              </div>
              
              <div class="form-group">
                <label class="form-label">Position</label>
                <input type="text" id="position" class="form-input" value="<?php echo htmlspecialchars($user['position'] ?? ''); ?>" placeholder="Enter your position">
              </div>
              
              <div class="form-group">
                <label class="form-label">Bio</label>
                <textarea id="bio" class="form-input" rows="4" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
              </div>
            </div>
          </div>
          
          <div style="text-align: center; margin-top: 2rem;">
            <button type="submit" class="btn-save">
              <i class="fa fa-save"></i>
              Save Profile Changes
            </button>
          </div>
        </form>
      </div>

      <!-- Account Statistics -->
      <div class="profile-section">
        <h3 style="margin-bottom: 2rem; color: #374151; font-size: 1.5rem;">
          <i class="fa fa-chart-bar"></i>
          Account Statistics
        </h3>
        
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-number"><?php echo $completionPercentage; ?>%</div>
            <div class="stat-label">Profile Complete</div>
          </div>
          <div class="stat-card">
            <div class="stat-number"><?php echo date('M Y', strtotime($user['created_at'])); ?></div>
            <div class="stat-label">Member Since</div>
          </div>
          <div class="stat-card">
            <div class="stat-number"><?php echo $user['last_login'] ? date('M j', strtotime($user['last_login'])) : 'Never'; ?></div>
            <div class="stat-label">Last Login</div>
          </div>
          <div class="stat-card">
            <div class="stat-number"><?php echo ucfirst($user['two_factor']); ?></div>
            <div class="stat-label">Two-Factor Auth</div>
          </div>
        </div>
      </div>

      <div class="footer mt-md small text-center" style="color: var(--color-secondary);">
        &copy; 2024 OutageSys. All rights reserved.
      </div>
    </div>
  </div>

  <input type="file" id="avatarUpload" accept="image/*" style="display: none;">
  
  <script src="js/dark-mode.js"></script>
  <script>
    // Profile management functionality
    const profileForm = document.getElementById('profileForm');
    const changeAvatarBtn = document.getElementById('changeAvatarBtn');
    const avatarUpload = document.getElementById('avatarUpload');
    const profileAvatar = document.getElementById('profileAvatar');
    
    // Avatar upload
    changeAvatarBtn.addEventListener('click', () => {
      avatarUpload.click();
    });
    
    avatarUpload.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          profileAvatar.src = e.target.result;
          showNotification('Avatar updated! Save your profile to keep the changes.', 'info');
        };
        reader.readAsDataURL(file);
      }
    });
    
    // Profile form submission
    profileForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const formData = new FormData();
      formData.append('action', 'update_profile');
      formData.append('user_id', '<?php echo $user_id; ?>');
      formData.append('first_name', document.getElementById('firstName').value);
      formData.append('last_name', document.getElementById('lastName').value);
      formData.append('department', document.getElementById('department').value);
      formData.append('position', document.getElementById('position').value);
      formData.append('bio', document.getElementById('bio').value);
      formData.append('phone', document.getElementById('phone').value);
      
      // Add avatar if changed
      if (profileAvatar.src && !profileAvatar.src.includes('data:image/svg+xml')) {
        formData.append('avatar_data', profileAvatar.src);
      }
      
      try {
        const response = await fetch('api/users.php', {
          method: 'POST',
          body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
          showNotification('Profile updated successfully!', 'success');
          // Reload page to update completion percentage
          setTimeout(() => location.reload(), 1500);
        } else {
          showNotification(result.error || 'Failed to update profile', 'error');
        }
      } catch (error) {
        console.error('Error:', error);
        showNotification('Network error occurred', 'error');
      }
    });
    
    // Profile completion tracking
    const profileFields = ['firstName', 'lastName', 'department', 'position', 'bio', 'phone'];
    
    function updateCompletion() {
      let completed = 0;
      profileFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field && field.value.trim() !== '') completed++;
      });
      
      const percentage = Math.round((completed / profileFields.length) * 100);
      document.querySelector('.completion-fill').style.width = percentage + '%';
      document.querySelector('.completion-card .completion-fill').nextElementSibling.textContent = percentage + '%';
    }
    
    // Add event listeners for completion tracking
    profileFields.forEach(fieldId => {
      const field = document.getElementById(fieldId);
      if (field) {
        field.addEventListener('input', updateCompletion);
        field.addEventListener('change', updateCompletion);
      }
    });
    
    // Initialize completion
    updateCompletion();
  </script>
</body>
</html> 