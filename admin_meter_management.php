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

// --- Admin Token Generation Logic ---
$KPLC_RATE = 26; // KES per unit
$admin_token_msg = '';
if (isset($_POST['admin_generate_token'])) {
    $admin_meter_id = intval($_POST['admin_meter_id']);
    $admin_amount = floatval($_POST['admin_amount']);
    $admin_units = round($admin_amount / $KPLC_RATE, 2);
    $admin_token = '';
    for ($i = 0; $i < 20; $i++) $admin_token .= mt_rand(0, 9);
    // Get user_id for the meter
    $stmt = $pdo->prepare('SELECT user_id FROM meters WHERE id = ?');
    $stmt->execute([$admin_meter_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $admin_user_id = $row['user_id'];
        // Ensure meter_tokens table has 'used' column
        $pdo->exec("ALTER TABLE meter_tokens ADD COLUMN IF NOT EXISTS used TINYINT(1) DEFAULT 0");
        // Store token
        $pdo->exec("CREATE TABLE IF NOT EXISTS meter_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            meter_id INT NOT NULL,
            token VARCHAR(20) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            units DECIMAL(10,2) NOT NULL,
            user_id INT NOT NULL,
            used TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $stmt = $pdo->prepare('INSERT INTO meter_tokens (meter_id, token, amount, units, user_id) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$admin_meter_id, $admin_token, $admin_amount, $admin_units, $admin_user_id]);
        // Notify user
        $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            message TEXT NOT NULL,
            type VARCHAR(50) DEFAULT 'info',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $notif_msg = "Admin has generated a prepaid token for you!\nToken: $admin_token\nUnits: $admin_units\nAmount: KES $admin_amount";
        $stmt = $pdo->prepare('INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)');
        $stmt->execute([$admin_user_id, $notif_msg, 'token']);
        $admin_token_msg = '<span style="color:green;">Token generated and sent to user!</span><br><strong>Token:</strong> ' . $admin_token . '<br><strong>Units:</strong> ' . $admin_units;
    } else {
        $admin_token_msg = '<span style="color:red;">Invalid meter selected.</span>';
    }
}
// Fetch all users and their prepaid meters for the form
$users = $pdo->query('SELECT id, username, first_name, last_name FROM users ORDER BY username')->fetchAll(PDO::FETCH_ASSOC);
$meters_by_user = [];
foreach ($users as $u) {
    $stmt = $pdo->prepare('SELECT id, meter_number FROM meters WHERE user_id = ? AND meter_type = "prepaid"');
    $stmt->execute([$u['id']]);
    $meters_by_user[$u['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch all pending meter requests
$stmt = $pdo->query('SELECT mr.*, u.username, u.email, u.first_name, u.last_name FROM meter_requests mr JOIN users u ON mr.user_id = u.id WHERE mr.status = "pending" ORDER BY mr.id ASC');
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
        <!-- Meter Management Navigation -->
        <div class="card mb-md" style="display: flex; flex-wrap: wrap; gap: 1rem; justify-content: center; align-items: center; margin-bottom: 2rem;">
            <span class="btn btn-primary" style="min-width: 160px; text-align: center; pointer-events: none; opacity: 0.7;"><i class="fa fa-tasks"></i> Requests</span>
            <a href="admin_all_meters.php" class="btn btn-outline" style="min-width: 160px; text-align: center;"><i class="fa fa-list"></i> All Meters</a>
            <button class="btn btn-primary" onclick="openAdminTokenModal()" style="min-width: 160px; text-align: center;"><i class="fa fa-key"></i> Generate Token</button>
            <a href="admin_billing.php" class="btn btn-outline" style="min-width: 160px; text-align: center;"><i class="fa fa-file-invoice-dollar"></i> Billing</a>
        </div>
        <div id="adminTokenModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
  <div class="modal-content" style="background:#fff; padding:2em; border-radius:12px; max-width:500px; margin:auto;">
    <h3>Admin Generate Prepaid Token</h3>
    <form method="post">
      <div class="form-group">
        <label for="admin_user_id">Select User</label>
        <select id="admin_user_id" name="admin_user_id" class="form-select" required onchange="updateMetersDropdown()">
          <option value="">-- Select User --</option>
          <?php foreach ($users as $u): ?>
            <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars(trim($u['first_name'] . ' ' . $u['last_name']) ?: $u['username']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label for="admin_meter_id">Select Prepaid Meter</label>
        <select id="admin_meter_id" name="admin_meter_id" class="form-select" required>
          <option value="">-- Select Meter --</option>
          <?php foreach ($meters_by_user as $uid => $meters): ?>
            <?php foreach ($meters as $m): ?>
              <option value="<?php echo $m['id']; ?>" data-user="<?php echo $uid; ?>"><?php echo htmlspecialchars($m['meter_number']); ?></option>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label for="admin_amount">Amount (KES)</label>
        <input type="number" id="admin_amount" name="admin_amount" min="10" step="1" required class="form-input">
      </div>
      <button type="submit" name="admin_generate_token" class="btn btn-primary">Generate Token</button>
      <button type="button" class="btn btn-outline" onclick="closeAdminTokenModal()">Cancel</button>
    </form>
    <?php if (!empty($admin_token_msg)) echo '<div class="mt-md">' . $admin_token_msg . '</div>'; ?>
  </div>
</div>
<script>
function openAdminTokenModal() {
  document.getElementById('adminTokenModal').style.display = 'flex';
}
function closeAdminTokenModal() {
  document.getElementById('adminTokenModal').style.display = 'none';
}
function updateMetersDropdown() {
  var userId = document.getElementById('admin_user_id').value;
  var meterSelect = document.getElementById('admin_meter_id');
  for (var i = 0; i < meterSelect.options.length; i++) {
    var opt = meterSelect.options[i];
    if (!opt.value) continue;
    opt.style.display = (opt.getAttribute('data-user') === userId) ? '' : 'none';
  }
  meterSelect.value = '';
}
</script>
        <div class="card mb-md">
            <h2 class="h2 mb-sm"><i class="fa fa-tasks"></i> Pending Requests</h2>
            <?php if ($message) echo "<div style='margin-bottom:1em;'>$message</div>"; ?>
            <?php if (empty($requests)): ?>
                <div style="text-align:center;">No pending requests.</div>
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
                            <td><?php 
        $full_name = trim($req['first_name'] . ' ' . $req['last_name']);
        echo htmlspecialchars($full_name !== '' ? $full_name : $req['username']); 
    ?></td>
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
        </div>
        <a href="admin_dashboard.php" class="btn btn-outline">&larr; Back to Admin Dashboard</a>
    </div>
</body>
</html> 