<?php
session_start();
require_once '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// GET: List all users
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query('SELECT id, username, role, status FROM users');
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// POST: Add new user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['username']) || empty($data['password']) || empty($data['role'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing fields']);
        exit;
    }
    $hash = password_hash($data['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (username, password, role, status) VALUES (?, ?, ?, ?)');
    $stmt->execute([
        $data['username'], $hash, $data['role'], $data['status'] ?? 'active'
    ]);
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    exit;
}

// PUT: Update user
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id']) || empty($data['username']) || empty($data['role'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing fields']);
        exit;
    }
    // If status is being set to active and role is empty or null, set role to 'user'
    if (($data['status'] ?? '') === 'active' && (empty($data['role']) || $data['role'] === '')) {
        $data['role'] = 'user';
    }
    $fields = ['username' => $data['username'], 'role' => $data['role'], 'status' => $data['status'] ?? 'active'];
    if (!empty($data['password'])) {
        $fields['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    $set = [];
    $params = [];
    foreach ($fields as $k => $v) {
        $set[] = "$k=?";
        $params[] = $v;
    }
    $params[] = $data['id'];
    $stmt = $pdo->prepare('UPDATE users SET '.implode(',', $set).' WHERE id=?');
    $stmt->execute($params);
    echo json_encode(['success' => true]);
    exit;
}

// DELETE: Delete user
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare('DELETE FROM users WHERE id=?');
    $stmt->execute([$data['id']]);
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method Not Allowed']); 