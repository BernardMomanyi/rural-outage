<?php
session_start();

// Fix the database include path
if (file_exists('../db.php')) {
    require_once '../db.php';
} else {
    require_once 'db.php';
}

header('Content-Type: application/json');

// Prevent any HTML output
error_reporting(E_ALL);
ini_set('display_errors', 0);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Helper function to check if column exists
function columnExists($pdo, $table, $column) {
    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM $table LIKE ?");
        $stmt->execute([$column]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

// Get all users with profile data (compatible with existing schema)
if ($_GET['action'] === 'get_users') {
    try {
        // Check which columns exist
        $hasFirstName = columnExists($pdo, 'users', 'first_name');
        $hasLastName = columnExists($pdo, 'users', 'last_name');
        $hasDepartment = columnExists($pdo, 'users', 'department');
        $hasPosition = columnExists($pdo, 'users', 'position');
        $hasBio = columnExists($pdo, 'users', 'bio');
        $hasStatus = columnExists($pdo, 'users', 'status');
        $hasAvatar = columnExists($pdo, 'users', 'avatar');
        $hasTwoFactor = columnExists($pdo, 'users', 'two_factor');
        $hasCreatedAt = columnExists($pdo, 'users', 'created_at');
        $hasLastLogin = columnExists($pdo, 'users', 'last_login');
        
        // Build dynamic query based on existing columns
        $selectFields = ['id', 'username', 'email', 'role', 'phone'];
        if ($hasFirstName) $selectFields[] = 'first_name';
        if ($hasLastName) $selectFields[] = 'last_name';
        if ($hasDepartment) $selectFields[] = 'department';
        if ($hasPosition) $selectFields[] = 'position';
        if ($hasBio) $selectFields[] = 'bio';
        if ($hasStatus) $selectFields[] = 'status';
        if ($hasAvatar) $selectFields[] = 'avatar';
        if ($hasTwoFactor) $selectFields[] = 'two_factor';
        if ($hasCreatedAt) $selectFields[] = 'created_at';
        if ($hasLastLogin) $selectFields[] = 'last_login';
        
        // Add role filter if specified
        $whereConditions = [];
        $params = [];
        
        if (isset($_GET['role']) && !empty($_GET['role'])) {
            $whereConditions[] = 'role = ?';
            $params[] = $_GET['role'];
        }
        
        $whereClause = $whereConditions ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        $sql = "SELECT " . implode(', ', $selectFields) . " FROM users $whereClause ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add default values for missing columns
        foreach ($users as &$user) {
            if (!$hasFirstName) $user['first_name'] = '';
            if (!$hasLastName) $user['last_name'] = '';
            if (!$hasDepartment) $user['department'] = '';
            if (!$hasPosition) $user['position'] = '';
            if (!$hasBio) $user['bio'] = '';
            if (!$hasStatus) $user['status'] = 'active';
            if (!$hasAvatar) $user['avatar'] = '';
            if (!$hasTwoFactor) $user['two_factor'] = 'disabled';
            if (!$hasCreatedAt) $user['created_at'] = date('Y-m-d H:i:s');
            if (!$hasLastLogin) $user['last_login'] = null;
            
            // Add avatar URL if avatar exists
            if ($user['avatar']) {
                $user['avatar_url'] = 'uploads/avatars/' . $user['avatar'];
            }
        }
        
        echo json_encode(['success' => true, 'users' => $users]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle user creation (compatible with existing schema)
if ($_POST['action'] === 'create_user') {
    try {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        $phone = $_POST['phone'] ?? '';
        
        // Check which columns exist
        $hasFirstName = columnExists($pdo, 'users', 'first_name');
        $hasLastName = columnExists($pdo, 'users', 'last_name');
        $hasDepartment = columnExists($pdo, 'users', 'department');
        $hasPosition = columnExists($pdo, 'users', 'position');
        $hasBio = columnExists($pdo, 'users', 'bio');
        $hasStatus = columnExists($pdo, 'users', 'status');
        $hasTwoFactor = columnExists($pdo, 'users', 'two_factor');
        
        // Build dynamic insert query
        $fields = ['username', 'email', 'password', 'role', 'phone'];
        $values = [$username, $email, $password, $role, $phone];
        
        if ($hasFirstName) {
            $fields[] = 'first_name';
            $values[] = $_POST['first_name'] ?? '';
        }
        if ($hasLastName) {
            $fields[] = 'last_name';
            $values[] = $_POST['last_name'] ?? '';
        }
        if ($hasDepartment) {
            $fields[] = 'department';
            $values[] = $_POST['department'] ?? '';
        }
        if ($hasPosition) {
            $fields[] = 'position';
            $values[] = $_POST['position'] ?? '';
        }
        if ($hasBio) {
            $fields[] = 'bio';
            $values[] = $_POST['bio'] ?? '';
        }
        if ($hasStatus) {
            $fields[] = 'status';
            $values[] = $_POST['status'] ?? 'active';
        }
        if ($hasTwoFactor) {
            $fields[] = 'two_factor';
            $values[] = $_POST['two_factor'] ?? 'disabled';
        }
        
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        $sql = "INSERT INTO users (" . implode(', ', $fields) . ") VALUES ($placeholders)";
        
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute($values)) {
            $userId = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'user_id' => $userId]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to create user']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle user update (compatible with existing schema)
if ($_POST['action'] === 'update_user') {
    try {
        $userId = $_POST['user_id'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $phone = $_POST['phone'] ?? '';
        
        // Check which columns exist
        $hasFirstName = columnExists($pdo, 'users', 'first_name');
        $hasLastName = columnExists($pdo, 'users', 'last_name');
        $hasDepartment = columnExists($pdo, 'users', 'department');
        $hasPosition = columnExists($pdo, 'users', 'position');
        $hasBio = columnExists($pdo, 'users', 'bio');
        $hasStatus = columnExists($pdo, 'users', 'status');
        $hasTwoFactor = columnExists($pdo, 'users', 'two_factor');
        
        // Build dynamic update query
        $setFields = ['username = ?', 'email = ?', 'role = ?', 'phone = ?'];
        $values = [$username, $email, $role, $phone];
        
        if ($hasFirstName) {
            $setFields[] = 'first_name = ?';
            $values[] = $_POST['first_name'] ?? '';
        }
        if ($hasLastName) {
            $setFields[] = 'last_name = ?';
            $values[] = $_POST['last_name'] ?? '';
        }
        if ($hasDepartment) {
            $setFields[] = 'department = ?';
            $values[] = $_POST['department'] ?? '';
        }
        if ($hasPosition) {
            $setFields[] = 'position = ?';
            $values[] = $_POST['position'] ?? '';
        }
        if ($hasBio) {
            $setFields[] = 'bio = ?';
            $values[] = $_POST['bio'] ?? '';
        }
        if ($hasStatus) {
            $setFields[] = 'status = ?';
            $values[] = $_POST['status'] ?? 'active';
        }
        if ($hasTwoFactor) {
            $setFields[] = 'two_factor = ?';
            $values[] = $_POST['two_factor'] ?? 'disabled';
        }
        
        // Add password update if provided
        if (!empty($_POST['password'])) {
            $setFields[] = 'password = ?';
            $values[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
        
        $values[] = $userId; // For WHERE clause
        $sql = "UPDATE users SET " . implode(', ', $setFields) . " WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute($values)) {
            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update user']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle profile update (compatible with existing schema)
if ($_POST['action'] === 'update_profile') {
    try {
        $userId = $_POST['user_id'];
        
        // Check which columns exist
        $hasFirstName = columnExists($pdo, 'users', 'first_name');
        $hasLastName = columnExists($pdo, 'users', 'last_name');
        $hasDepartment = columnExists($pdo, 'users', 'department');
        $hasPosition = columnExists($pdo, 'users', 'position');
        $hasBio = columnExists($pdo, 'users', 'bio');
        $hasStatus = columnExists($pdo, 'users', 'status');
        $hasTwoFactor = columnExists($pdo, 'users', 'two_factor');
        
        // Build dynamic update query
        $setFields = [];
        $values = [];
        
        if ($hasFirstName) {
            $setFields[] = 'first_name = ?';
            $values[] = $_POST['first_name'] ?? '';
        }
        if ($hasLastName) {
            $setFields[] = 'last_name = ?';
            $values[] = $_POST['last_name'] ?? '';
        }
        if ($hasDepartment) {
            $setFields[] = 'department = ?';
            $values[] = $_POST['department'] ?? '';
        }
        if ($hasPosition) {
            $setFields[] = 'position = ?';
            $values[] = $_POST['position'] ?? '';
        }
        if ($hasBio) {
            $setFields[] = 'bio = ?';
            $values[] = $_POST['bio'] ?? '';
        }
        if ($hasStatus) {
            $setFields[] = 'status = ?';
            $values[] = $_POST['status'] ?? 'active';
        }
        if ($hasTwoFactor) {
            $setFields[] = 'two_factor = ?';
            $values[] = $_POST['two_factor'] ?? 'disabled';
        }
        
        if (!empty($setFields)) {
            $values[] = $userId; // For WHERE clause
            $sql = "UPDATE users SET " . implode(', ', $setFields) . " WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($values)) {
                echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to update profile']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'No profile fields available']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle avatar upload
if ($_POST['action'] === 'upload_avatar') {
    try {
        $userId = $_POST['user_id'];
        $avatarData = $_POST['avatar_data'];
        
        // Remove data URL prefix
        $avatarData = str_replace('data:image/png;base64,', '', $avatarData);
        $avatarData = str_replace('data:image/jpeg;base64,', '', $avatarData);
        $avatarData = str_replace('data:image/jpg;base64,', '', $avatarData);
        
        // Decode base64
        $avatarData = base64_decode($avatarData);
        
        // Generate filename
        $filename = 'avatar_' . $userId . '_' . time() . '.png';
        $filepath = '../uploads/avatars/' . $filename;
        
        // Create directory if it doesn't exist
        if (!is_dir('../uploads/avatars/')) {
            mkdir('../uploads/avatars/', 0755, true);
        }
        
        // Save file
        if (file_put_contents($filepath, $avatarData)) {
            // Update user record if avatar column exists
            if (columnExists($pdo, 'users', 'avatar')) {
                $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                $stmt->execute([$filename, $userId]);
            }
            
            echo json_encode(['success' => true, 'avatar' => $filename]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to save avatar']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Avatar upload error: ' . $e->getMessage()]);
    }
    exit;
}

// Get user profile (compatible with existing schema)
if ($_GET['action'] === 'get_profile') {
    try {
        $userId = $_GET['user_id'];
        
        // Check which columns exist
        $hasFirstName = columnExists($pdo, 'users', 'first_name');
        $hasLastName = columnExists($pdo, 'users', 'last_name');
        $hasDepartment = columnExists($pdo, 'users', 'department');
        $hasPosition = columnExists($pdo, 'users', 'position');
        $hasBio = columnExists($pdo, 'users', 'bio');
        $hasStatus = columnExists($pdo, 'users', 'status');
        $hasAvatar = columnExists($pdo, 'users', 'avatar');
        $hasTwoFactor = columnExists($pdo, 'users', 'two_factor');
        
        // Build dynamic select query
        $selectFields = ['id', 'username', 'email', 'role', 'phone'];
        if ($hasFirstName) $selectFields[] = 'first_name';
        if ($hasLastName) $selectFields[] = 'last_name';
        if ($hasDepartment) $selectFields[] = 'department';
        if ($hasPosition) $selectFields[] = 'position';
        if ($hasBio) $selectFields[] = 'bio';
        if ($hasStatus) $selectFields[] = 'status';
        if ($hasAvatar) $selectFields[] = 'avatar';
        if ($hasTwoFactor) $selectFields[] = 'two_factor';
        
        $sql = "SELECT " . implode(', ', $selectFields) . " FROM users WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Add default values for missing columns
            if (!$hasFirstName) $user['first_name'] = '';
            if (!$hasLastName) $user['last_name'] = '';
            if (!$hasDepartment) $user['department'] = '';
            if (!$hasPosition) $user['position'] = '';
            if (!$hasBio) $user['bio'] = '';
            if (!$hasStatus) $user['status'] = 'active';
            if (!$hasAvatar) $user['avatar'] = '';
            if (!$hasTwoFactor) $user['two_factor'] = 'disabled';
            
            // Add avatar URL if exists
            if ($user['avatar']) {
                $user['avatar_url'] = 'uploads/avatars/' . $user['avatar'];
            }
            
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            echo json_encode(['success' => false, 'error' => 'User not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Get profile completion stats (compatible with existing schema)
if ($_GET['action'] === 'profile_completion') {
    try {
        $userId = $_GET['user_id'];
        
        // Check which columns exist
        $hasFirstName = columnExists($pdo, 'users', 'first_name');
        $hasLastName = columnExists($pdo, 'users', 'last_name');
        $hasDepartment = columnExists($pdo, 'users', 'department');
        $hasPosition = columnExists($pdo, 'users', 'position');
        $hasBio = columnExists($pdo, 'users', 'bio');
        $hasAvatar = columnExists($pdo, 'users', 'avatar');
        
        // Build dynamic select query
        $selectFields = ['phone'];
        if ($hasFirstName) $selectFields[] = 'first_name';
        if ($hasLastName) $selectFields[] = 'last_name';
        if ($hasDepartment) $selectFields[] = 'department';
        if ($hasPosition) $selectFields[] = 'position';
        if ($hasBio) $selectFields[] = 'bio';
        if ($hasAvatar) $selectFields[] = 'avatar';
        
        $sql = "SELECT " . implode(', ', $selectFields) . " FROM users WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $fields = $selectFields;
            $completed = 0;
            
            foreach ($fields as $field) {
                if (!empty($user[$field])) $completed++;
            }
            
            $percentage = round(($completed / count($fields)) * 100);
            
            echo json_encode([
                'success' => true, 
                'completion' => $percentage,
                'completed_fields' => $completed,
                'total_fields' => count($fields)
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'User not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Get avatar gallery
if ($_GET['action'] === 'avatar_gallery') {
    try {
        $avatars = [];
        $avatarDir = '../uploads/avatars/';
        
        if (is_dir($avatarDir)) {
            $files = scandir($avatarDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'png') {
                    $avatars[] = [
                        'id' => pathinfo($file, PATHINFO_FILENAME),
                        'src' => 'uploads/avatars/' . $file,
                        'filename' => $file
                    ];
                }
            }
        }
        
        echo json_encode(['success' => true, 'avatars' => $avatars]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Gallery error: ' . $e->getMessage()]);
    }
    exit;
}

// Default response
echo json_encode(['success' => false, 'error' => 'Invalid action']);
?> 