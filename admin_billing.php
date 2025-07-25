<?php
session_start();
require_once 'db.php';

// Only allow admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// --- Admin Set Postpaid Bill Logic ---
$admin_bill_msg = '';
$KPLC_RATE_BANDS = [
    ['min' => 0, 'max' => 30, 'rate' => 12.23],
    ['min' => 31, 'max' => 100, 'rate' => 16.54],
    ['min' => 101, 'max' => PHP_INT_MAX, 'rate' => 19.08],
];
if (isset($_POST['admin_set_bill'])) {
    $admin_meter_id = intval($_POST['admin_meter_id']);
    $kwh = floatval($_POST['admin_kwh']);
    // Determine base rate
    $base_rate = 0;
    foreach ($KPLC_RATE_BANDS as $band) {
        if ($kwh >= $band['min'] && $kwh <= $band['max']) {
            $base_rate = $band['rate'];
            break;
        }
    }
    if ($base_rate === 0) $base_rate = 19.08; // fallback
    $base_charge = $kwh * $base_rate;
    $erc_levy = 0.08 * $kwh;
    $rep_levy = 0.05 * $base_charge;
    $surcharges = 0.30 * $base_charge; // 30% demo surcharge
    $subtotal = $base_charge + $erc_levy + $rep_levy + $surcharges;
    $vat = 0.16 * $subtotal;
    $total_bill = round($subtotal + $vat, 2);
    // Set bill_due
    $stmt = $pdo->prepare('UPDATE meters SET bill_due = ? WHERE id = ?');
    $stmt->execute([$total_bill, $admin_meter_id]);
    $admin_bill_msg = '<span style="color:green;">Bill set: KES ' . number_format($total_bill,2) . ' for ' . $kwh . ' kWh.</span>';
}
// Fetch all users and their postpaid meters for the form
$users = $pdo->query('SELECT id, username, first_name, last_name FROM users ORDER BY username')->fetchAll(PDO::FETCH_ASSOC);
$meters_by_user = [];
foreach ($users as $u) {
    $stmt = $pdo->prepare('SELECT id, meter_number FROM meters WHERE user_id = ? AND meter_type = "postpaid"');
    $stmt->execute([$u['id']]);
    $meters_by_user[$u['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch all postpaid meters
$stmt = $pdo->query("SELECT m.*, u.username, u.first_name, u.last_name FROM meters m JOIN users u ON m.user_id = u.id WHERE m.meter_type = 'postpaid' ORDER BY m.id DESC");
$meters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle mark as paid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_paid'], $_POST['meter_id'])) {
    $meter_id = intval($_POST['meter_id']);
    // Set bill_due to 0 (paid)
    $pdo->prepare('UPDATE meters SET bill_due = 0 WHERE id = ?')->execute([$meter_id]);
    header('Location: admin_billing.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Billing</title>
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
            <li class="breadcrumb-current" style="color:var(--color-primary); font-weight:600;"><i class="fa fa-file-invoice-dollar"></i> Billing</li>
          </ol>
        </nav>
        <div class="card mb-md" style="max-width:600px;margin:2em auto 2em auto; background:linear-gradient(135deg,#e0f7fa 0%,#f9fafb 100%); border-left: 8px solid #0ea5e9; box-shadow:0 2px 8px rgba(30,41,59,0.10);">
          <div style="display:flex; align-items:center; gap:0.75em; margin-bottom:0.5em;">
            <span style="font-size:1.6em; color:#0ea5e9;"><i class="fa fa-lightbulb"></i></span>
            <h4 style="margin:0; color:#0ea5e9; font-weight:700; letter-spacing:0.5px;">Postpaid Billing Calculation Guide</h4>
          </div>
          <ul style="margin:0 0 0.5em 1.5em; padding:0; font-size:1.08em; color:#222; line-height:1.7;">
            <li><span style="color:#0ea5e9;font-weight:600;"><i class="fa fa-calculator"></i> Step 1:</span> <b>kWh Consumed</b> × <b>Band Rate</b> (see below)</li>
            <li><span style="color:#0ea5e9;font-weight:600;"><i class="fa fa-plus-circle"></i> Step 2:</span> Add <b>ERC Levy</b> <span style="color:#0ea5e9; background:#e0f2fe; padding:0.15em 0.5em; border-radius:6px;"><i class="fa fa-bolt"></i> 0.08 KES/kWh</span> and <b>REP Levy</b> <span style="color:#0ea5e9; background:#e0f2fe; padding:0.15em 0.5em; border-radius:6px;"><i class="fa fa-percentage"></i> 5% of base charge</span></li>
            <li><span style="color:#0ea5e9;font-weight:600;"><i class="fa fa-plus-circle"></i> Step 3:</span> Add <b>30% Surcharges</b> <span style="color:#0ea5e9; background:#e0f2fe; padding:0.15em 0.5em; border-radius:6px;"><i class="fa fa-tags"></i> covers FCC, FERFA, IA, WARMA, etc.</span></li>
            <li><span style="color:#0ea5e9;font-weight:600;"><i class="fa fa-plus-circle"></i> Step 4:</span> Add <b>VAT</b> <span style="color:#0ea5e9; background:#e0f2fe; padding:0.15em 0.5em; border-radius:6px;"><i class="fa fa-percentage"></i> 16% of subtotal above</span></li>
            <li style="margin-top:0.5em;"><span style="color:#0ea5e9;font-weight:600;"><i class="fa fa-list-ul"></i> Band Rates:</span>
              <ul style="margin:0.25em 0 0 1.5em;">
                <li><span style="background:#e0f2fe; padding:0.15em 0.5em; border-radius:6px;"><i class="fa fa-tachometer-alt"></i> 0–30 kWh: <b>12.23 KES/kWh</b> <span style="background:#0ea5e9; color:white; padding:0.1em 0.4em; border-radius:4px; font-size:0.8em;">DC0</span></span></li>
                <li><span style="background:#e0f2fe; padding:0.15em 0.5em; border-radius:6px;"><i class="fa fa-tachometer-alt"></i> 31–100 kWh: <b>16.54 KES/kWh</b> <span style="background:#0ea5e9; color:white; padding:0.1em 0.4em; border-radius:4px; font-size:0.8em;">DC1</span></span></li>
                <li><span style="background:#e0f2fe; padding:0.15em 0.5em; border-radius:6px;"><i class="fa fa-tachometer-alt"></i> Above 100 kWh: <b>19.08 KES/kWh</b> <span style="background:#0ea5e9; color:white; padding:0.1em 0.4em; border-radius:4px; font-size:0.8em;">DC2</span></span></li>
              </ul>
            </li>
            <li style="margin-top:0.5em;"><span style="color:#0ea5e9;font-weight:600;"><i class="fa fa-info-circle"></i> Example:</span> <span style="background:#fffbe7; padding:0.15em 0.5em; border-radius:6px;"><i class="fa fa-arrow-right"></i> 50 kWh → (50 × 16.54) + ERC + REP + Surcharges + VAT</span></li>
          </ul>
        </div>
        <div class="card mb-md" style="max-width:600px;margin:2em auto 2em auto;">
          <h3>Admin Set Postpaid Bill</h3>
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
              <label for="admin_meter_id">Select Postpaid Meter</label>
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
              <label for="admin_kwh">kWh Consumed</label>
              <input type="number" id="admin_kwh" name="admin_kwh" min="1" step="1" required class="form-input">
            </div>
            <button type="submit" name="admin_set_bill" class="btn btn-primary">Set Bill</button>
          </form>
          <?php if (!empty($admin_bill_msg)) echo '<div class="mt-md">' . $admin_bill_msg . '</div>'; ?>
        </div>
        <?php if (empty($meters)): ?>
            <div style="text-align:center;">No postpaid meters found in the system.</div>
        <?php else: ?>
            <table class="styled-table mb-md" style="width:100%;">
                <thead>
                    <tr>
                        <th>Meter Number</th>
                        <th>User</th>
                        <th>Bill Due</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($meters as $meter): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($meter['meter_number']); ?></td>
                            <td><?php 
                                $full_name = trim($meter['first_name'] . ' ' . $meter['last_name']);
                                echo htmlspecialchars($full_name !== '' ? $full_name : $meter['username']); 
                            ?></td>
                            <td><?php echo isset($meter['bill_due']) ? 'KSh ' . number_format($meter['bill_due'], 2) : 'KSh 0.00'; ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="meter_id" value="<?php echo $meter['id']; ?>">
                                    <button type="submit" name="mark_paid" class="btn btn-primary btn-sm" <?php echo (empty($meter['bill_due']) || $meter['bill_due'] == 0) ? 'disabled' : ''; ?>>Mark as Paid</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <a href="admin_meter_management.php" class="btn btn-outline">&larr; Back to Meter Management</a>
    </div>
    <script>
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
</body>
</html> 