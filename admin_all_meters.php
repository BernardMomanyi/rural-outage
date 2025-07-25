<?php
session_start();
require_once 'db.php';

// Only allow admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Fetch all meters with user info
$stmt = $pdo->query('SELECT m.*, u.username, u.email, u.first_name, u.last_name FROM meters m JOIN users u ON m.user_id = u.id ORDER BY m.id DESC');
$meters = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - All Meters</title>
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
            <li class="breadcrumb-current" style="color:var(--color-primary); font-weight:600;"><i class="fa fa-list"></i> All Meters</li>
          </ol>
        </nav>
        <div class="card mb-md" style="margin-bottom:2rem;">
            <h2 class="h2 mb-sm"><i class="fa fa-list"></i> All Meters</h2>
            <?php if (empty($meters)): ?>
                <div style="text-align:center;">No meters found in the system.</div>
            <?php else: ?>
                <table class="styled-table mb-md" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Meter Number</th>
                            <th>Type</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Balance/Bill</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($meters as $meter): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($meter['meter_number']); ?></td>
                                <td><?php echo ucfirst($meter['meter_type']); ?></td>
                                <td><?php 
                                    $full_name = trim($meter['first_name'] . ' ' . $meter['last_name']);
                                    echo htmlspecialchars($full_name !== '' ? $full_name : $meter['username']); 
                                ?></td>
                                <td><?php echo htmlspecialchars($meter['email']); ?></td>
                                <td>
                                    <?php if (isset($meter['meter_type']) && $meter['meter_type'] === 'prepaid'): ?>
                                        <?php echo isset($meter['credit_balance']) ? number_format($meter['credit_balance'], 2) : '0.00'; ?> kWh
                                    <?php else: ?>
                                        <?php echo isset($meter['bill_due']) ? 'KSh ' . number_format($meter['bill_due'], 2) : 'KSh 0.00'; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            <a href="admin_meter_management.php" class="btn btn-outline">&larr; Back to Meter Management</a>
        </div>
    </div>
</body>
</html> 