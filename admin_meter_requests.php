<?php
session_start();
require_once 'db.php';

// Only allow admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';

// Handle Approve/Reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = intval($_POST['request_id'] ?? 0);
    if (isset($_POST['approve'])) {
        $meter_type = $_POST['meter_type'];
        // Generate unique 11-digit meter number
        do {
            $meter_number = str_pad(strval(mt_rand(0, 99999999999)), 11, '0', STR_PAD_LEFT);
            $stmt = $pdo->prepare('SELECT id FROM meters WHERE meter_number = ?');
            $stmt->execute([$meter_number]);
        } while ($stmt->fetch());
        // Get user_id from request
        $stmt = $pdo->prepare('SELECT user_id FROM meter_requests WHERE id = ?');
        $stmt->execute([$request_id]);
        $req = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($req) {
            $user_id = $req['user_id'];
            // Insert meter
            $stmt = $pdo->prepare('INSERT INTO meters (user_id, meter_number, meter_type) VALUES (?, ?, ?)');
            if ($stmt->execute([$user_id, $meter_number, $meter_type])) {
                // Mark request as approved
                $pdo->prepare('UPDATE meter_requests SET status = ? WHERE id = ?')->execute(['approved', $request_id]);
                $message = '<span style="color:green;">Meter assigned and request approved.</span>';
            } else {
                $message = '<span style="color:red;">Error assigning meter.</span>';
            }
        }
    } elseif (isset($_POST['reject'])) {
        $reason = trim($_POST['reject_reason'] ?? '');
        $pdo->prepare('UPDATE meter_requests SET status = ?, request_details = CONCAT(request_details, "\nRejected Reason: ", ?) WHERE id = ?')->execute(['rejected', $reason, $request_id]);
        $message = '<span style="color:orange;">Request rejected.</span>';
    }
}

// Fetch all pending meter requests
$stmt = $pdo->query('SELECT mr.*, u.name, u.email FROM meter_requests mr JOIN users u ON mr.user_id = u.id WHERE mr.status = "pending" ORDER BY mr.id ASC');
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Meter Management</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container" style="max-width:900px;margin:2em auto;">
        <nav aria-label="Breadcrumb" class="breadcrumbs-nav" style="margin: 1.5rem auto 0 auto; max-width:900px; background:transparent;">
          <ol class="breadcrumbs" style="display:flex; flex-wrap:wrap; gap:0.5em; list-style:none; padding:0; margin:0; font-size:1rem; background:transparent;">
            <li><a href="admin_dashboard.php" class="breadcrumb-link"><i class="fa fa-tachometer-alt"></i> Admin Dashboard</a></li>
            <li style="color:var(--color-secondary);">&gt;</li>
            <li class="breadcrumb-current" style="color:var(--color-primary); font-weight:600;"><i class="fa fa-bolt"></i> Meter Management</li>
          </ol>
        </nav>
        <h2>Pending Meter Management</h2>
        <?php if ($message) echo "<div style='margin-bottom:1em;'>$message</div>"; ?>
        <?php if (empty($requests)): ?>
            <div class="card mb-md" style="text-align:center;">No pending meter management tasks.</div>
        <?php else: ?>
            <table class="styled-table mb-md" style="width:100%;">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Request Details</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($requests as $req): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($req['name']); ?></td>
                        <td><?php echo htmlspecialchars($req['email']); ?></td>
                        <td style="white-space:pre-line;"><?php echo htmlspecialchars($req['request_details']); ?></td>
                        <td>
                            <form method="post" style="margin-bottom:0.5em;">
                                <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                <select name="meter_type" class="form-select" required style="margin-bottom:0.5em;">
                                    <option value="">Select Type</option>
                                    <option value="prepaid">Prepaid (Token)</option>
                                    <option value="postpaid">Postpaid (Bill)</option>
                                </select>
                                <button type="submit" name="approve" class="btn btn-primary btn-sm" style="margin-bottom:0.25em;">Approve & Assign Meter</button>
                            </form>
                            <form method="post">
                                <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                <input type="text" name="reject_reason" class="form-input" placeholder="Reason (optional)" style="margin-bottom:0.25em;">
                                <button type="submit" name="reject" class="btn btn-outline btn-sm">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <a href="admin_dashboard.php" class="btn btn-outline">&larr; Back to Admin Dashboard</a>
    </div>
</body>
</html> 