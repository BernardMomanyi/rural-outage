<?php
session_start();
require_once 'db.php';

// Only allow admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Fetch all prepaid meters
$stmt = $pdo->query("SELECT m.*, u.username, u.first_name, u.last_name FROM meters m JOIN users u ON m.user_id = u.id WHERE m.meter_type = 'prepaid' ORDER BY m.id DESC");
$meters = $stmt->fetchAll(PDO::FETCH_ASSOC);

$token = '';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['meter_id'])) {
    $meter_id = intval($_POST['meter_id']);
    // Generate a random 8-digit token
    $token = str_pad(strval(mt_rand(0, 99999999)), 8, '0', STR_PAD_LEFT);
    $message = '<span style="color:green;">Token generated: <strong>' . htmlspecialchars($token) . '</strong></span>';
    // Here you could also save the token to a tokens table if needed
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Generate Token</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container" style="max-width:900px;margin:2em auto;">
        <nav aria-label="Breadcrumb" class="breadcrumbs-nav" style="margin: 1.5rem auto 0 auto; max-width:900px; background:transparent;">
          <ol class="breadcrumbs" style="display:flex; flex-wrap:wrap; gap:0.5em; list-style:none; padding:0; margin:0; font-size:1rem; background:transparent;">
            <li><a href="admin_dashboard.php" class="breadcrumb-link"><i class="fa fa-tachometer-alt"></i> Admin Dashboard</a></li>
            <li style="color:var(--color-secondary);">&gt;</li>
            <li><a href="admin_meter_management.php" class="breadcrumb-link"><i class="fa fa-bolt"></i> Meter Management</a></li>
            <li style="color:var(--color-secondary);">&gt;</li>
            <li class="breadcrumb-current" style="color:var(--color-primary); font-weight:600;"><i class="fa fa-key"></i> Generate Token</li>
          </ol>
        </nav>
        <div class="card mb-md" style="margin-bottom:2rem;">
            <h2 class="h2 mb-sm"><i class="fa fa-key"></i> Generate Token</h2>
            <?php if ($message) echo '<div class="mb-md">' . $message . '</div>'; ?>
            <form method="post" style="max-width:400px;margin:auto;">
                <div class="form-group mb-md">
                    <label for="meter_id" class="form-label">Select Prepaid Meter</label>
                    <select name="meter_id" id="meter_id" class="form-select" required>
                        <option value="">-- Select Meter --</option>
                        <?php foreach ($meters as $meter): ?>
                            <option value="<?php echo $meter['id']; ?>">
                                <?php 
                                    $full_name = trim($meter['first_name'] . ' ' . $meter['last_name']);
                                    echo htmlspecialchars($meter['meter_number'] . ' - ' . ($full_name !== '' ? $full_name : $meter['username']));
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Generate Token</button>
            </form>
            <?php if ($token): ?>
                <div class="mt-md" style="text-align:center;">
                    <strong>Token:</strong> <span style="font-size:1.5em; color:var(--color-primary); letter-spacing:2px;"><?php echo htmlspecialchars($token); ?></span>
                </div>
            <?php endif; ?>
            <a href="admin_meter_management.php" class="btn btn-outline mt-md">&larr; Back to Meter Management</a>
        </div>
    </div>
</body>
</html> 