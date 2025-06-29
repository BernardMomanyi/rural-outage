<?php
header('Content-Type: application/json');
session_start();
require_once 'db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$response = ['success' => false, 'message' => 'Invalid action', 'data' => null];

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
}

switch ($action) {
    case 'login':
        // POST: username, password
        if ($method === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            if ($username && $password) {
                $stmt = $conn->prepare('SELECT id, username, password, role FROM users WHERE username = ?');
                $stmt->bind_param('s', $username);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    if (password_verify($password, $row['password'])) {
                        $_SESSION['user_id'] = $row['id'];
                        $_SESSION['username'] = $row['username'];
                        $_SESSION['role'] = $row['role'];
                        $response = ['success' => true, 'message' => 'Login successful', 'data' => ['username' => $row['username'], 'role' => $row['role']]];
                    } else {
                        $response['message'] = 'Invalid credentials';
                    }
                } else {
                    $response['message'] = 'User not found';
                }
                $stmt->close();
            } else {
                $response['message'] = 'Username and password required';
            }
        } else {
            $response['message'] = 'Invalid request method';
        }
        break;
    case 'logout':
        // POST: logout user
        session_destroy();
        $response = ['success' => true, 'message' => 'Logged out'];
        break;
    case 'register':
        // POST: username, password, role
        if ($method === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'user';
            if ($username && $password) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
                $stmt->bind_param('sss', $username, $hash, $role);
                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Registration successful'];
                } else {
                    $response['message'] = 'Registration failed: ' . $conn->error;
                }
                $stmt->close();
            } else {
                $response['message'] = 'Username and password required';
            }
        } else {
            $response['message'] = 'Invalid request method';
        }
        break;
    case 'substations':
        require_login();
        if ($method === 'GET') {
            // List substations
            $result = $conn->query('SELECT * FROM substations');
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            $response = ['success' => true, 'data' => $data];
        } elseif ($method === 'POST') {
            // Add substation
            $name = $_POST['name'] ?? '';
            $location = $_POST['location'] ?? '';
            if ($name && $location) {
                $stmt = $conn->prepare('INSERT INTO substations (name, location) VALUES (?, ?)');
                $stmt->bind_param('ss', $name, $location);
                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Substation added'];
                } else {
                    $response['message'] = 'Add failed: ' . $conn->error;
                }
                $stmt->close();
            } else {
                $response['message'] = 'Name and location required';
            }
        } elseif ($method === 'PUT') {
            // Update substation
            parse_str(file_get_contents('php://input'), $_PUT);
            $id = $_PUT['id'] ?? '';
            $name = $_PUT['name'] ?? '';
            $location = $_PUT['location'] ?? '';
            if ($id && $name && $location) {
                $stmt = $conn->prepare('UPDATE substations SET name=?, location=? WHERE id=?');
                $stmt->bind_param('ssi', $name, $location, $id);
                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Substation updated'];
                } else {
                    $response['message'] = 'Update failed: ' . $conn->error;
                }
                $stmt->close();
            } else {
                $response['message'] = 'ID, name, and location required';
            }
        } elseif ($method === 'DELETE') {
            // Delete substation
            parse_str(file_get_contents('php://input'), $_DELETE);
            $id = $_DELETE['id'] ?? '';
            if ($id) {
                $stmt = $conn->prepare('DELETE FROM substations WHERE id=?');
                $stmt->bind_param('i', $id);
                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Substation deleted'];
                } else {
                    $response['message'] = 'Delete failed: ' . $conn->error;
                }
                $stmt->close();
            } else {
                $response['message'] = 'ID required';
            }
        }
        break;
    case 'predictions':
        require_login();
        if ($method === 'GET') {
            $result = $conn->query('SELECT * FROM predictions WHERE status = "active"');
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            $response = ['success' => true, 'data' => $data];
        } elseif ($method === 'POST') {
            $substation_id = $_POST['substation_id'] ?? '';
            $risk_level = $_POST['risk_level'] ?? '';
            if ($substation_id && $risk_level) {
                $stmt = $conn->prepare('INSERT INTO predictions (substation_id, risk_level, status) VALUES (?, ?, "active")');
                $stmt->bind_param('is', $substation_id, $risk_level);
                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Prediction added'];
                } else {
                    $response['message'] = 'Add failed: ' . $conn->error;
                }
                $stmt->close();
            } else {
                $response['message'] = 'Substation ID and risk level required';
            }
        }
        break;
    case 'alerts':
        require_login();
        if ($method === 'GET') {
            $result = $conn->query('SELECT * FROM alerts WHERE resolved = 0');
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            $response = ['success' => true, 'data' => $data];
        } elseif ($method === 'POST') {
            $id = $_POST['id'] ?? '';
            if ($id) {
                $stmt = $conn->prepare('UPDATE alerts SET resolved=1 WHERE id=?');
                $stmt->bind_param('i', $id);
                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Alert resolved'];
                } else {
                    $response['message'] = 'Resolve failed: ' . $conn->error;
                }
                $stmt->close();
            } else {
                $response['message'] = 'ID required';
            }
        }
        break;
    case 'notifications':
        require_login();
        if ($method === 'GET') {
            $result = $conn->query('SELECT * FROM notifications ORDER BY created_at DESC LIMIT 10');
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            $response = ['success' => true, 'data' => $data];
        } elseif ($method === 'POST') {
            $message = $_POST['message'] ?? '';
            if ($message) {
                $stmt = $conn->prepare('INSERT INTO notifications (message, created_at) VALUES (?, NOW())');
                $stmt->bind_param('s', $message);
                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Notification added'];
                } else {
                    $response['message'] = 'Add failed: ' . $conn->error;
                }
                $stmt->close();
            } else {
                $response['message'] = 'Message required';
            }
        }
        break;
    case 'reports':
        require_login();
        if ($method === 'GET') {
            $result = $conn->query('SELECT * FROM reports ORDER BY created_at DESC');
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            $response = ['success' => true, 'data' => $data];
        }
        // Add more report generation logic as needed
        break;
    case 'users':
        require_login();
        if ($_SESSION['role'] !== 'admin') {
            $response['message'] = 'Admin only';
            break;
        }
        if ($method === 'GET') {
            $result = $conn->query('SELECT id, username, role FROM users');
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            $response = ['success' => true, 'data' => $data];
        } elseif ($method === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'user';
            if ($username && $password) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
                $stmt->bind_param('sss', $username, $hash, $role);
                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'User added'];
                } else {
                    $response['message'] = 'Add failed: ' . $conn->error;
                }
                $stmt->close();
            } else {
                $response['message'] = 'Username and password required';
            }
        } elseif ($method === 'PUT') {
            parse_str(file_get_contents('php://input'), $_PUT);
            $id = $_PUT['id'] ?? '';
            $role = $_PUT['role'] ?? '';
            if ($id && $role) {
                $stmt = $conn->prepare('UPDATE users SET role=? WHERE id=?');
                $stmt->bind_param('si', $role, $id);
                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'User updated'];
                } else {
                    $response['message'] = 'Update failed: ' . $conn->error;
                }
                $stmt->close();
            } else {
                $response['message'] = 'ID and role required';
            }
        } elseif ($method === 'DELETE') {
            parse_str(file_get_contents('php://input'), $_DELETE);
            $id = $_DELETE['id'] ?? '';
            if ($id) {
                $stmt = $conn->prepare('DELETE FROM users WHERE id=?');
                $stmt->bind_param('i', $id);
                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'User deleted'];
                } else {
                    $response['message'] = 'Delete failed: ' . $conn->error;
                }
                $stmt->close();
            } else {
                $response['message'] = 'ID required';
            }
        }
        break;
    case 'upload':
        require_login();
        if ($method === 'POST' && isset($_FILES['file'])) {
            $target_dir = 'uploads/';
            if (!is_dir($target_dir)) mkdir($target_dir);
            $target_file = $target_dir . basename($_FILES['file']['name']);
            if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
                $response = ['success' => true, 'message' => 'File uploaded', 'data' => $target_file];
            } else {
                $response['message'] = 'Upload failed';
            }
        } else {
            $response['message'] = 'No file uploaded';
        }
        break;
    case 'settings':
        require_login();
        if ($_SESSION['role'] !== 'admin') {
            $response['message'] = 'Admin only';
            break;
        }
        if ($method === 'GET') {
            $result = $conn->query('SELECT * FROM settings');
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[$row['key']] = $row['value'];
            }
            $response = ['success' => true, 'data' => $data];
        } elseif ($method === 'POST') {
            $key = $_POST['key'] ?? '';
            $value = $_POST['value'] ?? '';
            if ($key && $value) {
                $stmt = $conn->prepare('REPLACE INTO settings (`key`, `value`) VALUES (?, ?)');
                $stmt->bind_param('ss', $key, $value);
                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Setting updated'];
                } else {
                    $response['message'] = 'Update failed: ' . $conn->error;
                }
                $stmt->close();
            } else {
                $response['message'] = 'Key and value required';
            }
        }
        break;
    case 'map':
        require_login();
        // Example: fetch substations and outages for map
        $substations = [];
        $outages = [];
        $result = $conn->query('SELECT id, name, location FROM substations');
        while ($row = $result->fetch_assoc()) {
            $substations[] = $row;
        }
        $result = $conn->query('SELECT id, substation_id, start_time, end_time FROM outages WHERE end_time IS NULL');
        while ($row = $result->fetch_assoc()) {
            $outages[] = $row;
        }
        $response = ['success' => true, 'data' => ['substations' => $substations, 'outages' => $outages]];
        break;
    default:
        $response['message'] = 'Unknown or missing action.';
}

$conn->close();
echo json_encode($response); 