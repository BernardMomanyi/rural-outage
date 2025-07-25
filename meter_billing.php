<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['request_meter'])) {
        // Handle meter request
        $meter_type = $_POST['meter_type'];
        $request_details = trim($_POST['request_details'] ?? '');
        $stmt = $pdo->prepare("INSERT INTO meter_requests (user_id, request_details, status) VALUES (?, ?, 'pending')");
        if ($stmt->execute([$user_id, "Type: $meter_type\nDetails: $request_details"])) {
            $message = '<span style="color:green;">Your meter request has been submitted and is pending admin approval.</span>';
        } else {
            $message = '<span style="color:red;">Error submitting request. Please try again.</span>';
        }
    } elseif (isset($_POST['link_meter'])) {
        // Handle meter linking
        $meter_number = trim($_POST['meter_number']);
        $meter_type = $_POST['meter_type'];
        // Check if meter already exists
        $stmt = $pdo->prepare("SELECT id, user_id FROM meters WHERE meter_number = ?");
        $stmt->execute([$meter_number]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            if ($existing['user_id'] == $user_id) {
                $message = '<span style="color:orange;">This meter is already linked to your account.</span>';
            } else {
                $message = '<span style="color:red;">This meter is already linked to another user.</span>';
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO meters (user_id, meter_number, meter_type) VALUES (?, ?, ?)");
            if ($stmt->execute([$user_id, $meter_number, $meter_type])) {
                $message = '<span style="color:green;">Meter linked to your account successfully!</span>';
            } else {
                $message = '<span style="color:red;">Error linking meter. Please try again.</span>';
            }
        }
    }
}

// --- Token purchase logic ---
$KPLC_RATE = 26; // KES per unit
$token_msg = '';
if (isset($_POST['buy_token_meter_id'], $_POST['buy_token_amount'])) {
    $meter_id = intval($_POST['buy_token_meter_id']);
    $amount = floatval($_POST['buy_token_amount']);
    $units = round($amount / $KPLC_RATE, 2);
    $token = '';
    // Generate a 20-digit unique token
    for ($i = 0; $i < 20; $i++) $token .= mt_rand(0, 9);
    // Store in DB (create table if not exists)
    $pdo->exec("CREATE TABLE IF NOT EXISTS meter_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        meter_id INT NOT NULL,
        token VARCHAR(20) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        units DECIMAL(10,2) NOT NULL,
        user_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    // Insert token
    $stmt = $pdo->prepare('INSERT INTO meter_tokens (meter_id, token, amount, units, user_id) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$meter_id, $token, $amount, $units, $_SESSION['user_id']]);
    // Automated notification for user
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        message TEXT NOT NULL,
        type VARCHAR(50) DEFAULT 'info',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $notif_msg = "Your prepaid token purchase was successful!\nToken: $token\nUnits: $units\nAmount: KES $amount";
    $stmt = $pdo->prepare('INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)');
    $stmt->execute([$_SESSION['user_id'], $notif_msg, 'token']);
    // Show token on screen
    $token_msg = '<span style="color:green;">Token generated!</span>';
    $token_msg .= '<br><strong>Your Token:</strong> ' . $token . '<br><strong>Units:</strong> ' . $units;
}

// --- Ensure 'used' column exists in meter_tokens ---
$pdo->exec("ALTER TABLE meter_tokens ADD COLUMN IF NOT EXISTS used TINYINT(1) DEFAULT 0");

// --- Handle token recharge submission ---
$recharge_msg = '';
if (isset($_POST['recharge_meter_id'], $_POST['recharge_token'])) {
    $meter_id = intval($_POST['recharge_meter_id']);
    $input_token = trim($_POST['recharge_token']);
    // Check if token exists, belongs to this meter, and is unused
    $stmt = $pdo->prepare('SELECT * FROM meter_tokens WHERE meter_id = ? AND token = ? AND used = 0');
    $stmt->execute([$meter_id, $input_token]);
    $token_row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($token_row) {
        // Update meter balance
        $units = floatval($token_row['units']);
        $stmt = $pdo->prepare('UPDATE meters SET credit_balance = IFNULL(credit_balance,0) + ? WHERE id = ?');
        $stmt->execute([$units, $meter_id]);
        // Mark token as used
        $stmt = $pdo->prepare('UPDATE meter_tokens SET used = 1 WHERE id = ?');
        $stmt->execute([$token_row['id']]);
        $recharge_msg = '<span style="color:green;">Token accepted! ' . $units . ' units added to your balance.</span>';
    } else {
        $recharge_msg = '<span style="color:red;">Invalid or already used token.</span>';
    }
}

// --- Handle postpaid bill payment ---
$postpaid_msg = '';
if (isset($_POST['pay_bill_meter_id'], $_POST['pay_bill_amount'])) {
    $meter_id = intval($_POST['pay_bill_meter_id']);
    $amount = floatval($_POST['pay_bill_amount']);
    // Get current bill_due
    $stmt = $pdo->prepare('SELECT bill_due FROM meters WHERE id = ?');
    $stmt->execute([$meter_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['bill_due'] > 0) {
        $pay = min($amount, $row['bill_due']);
        $stmt = $pdo->prepare('UPDATE meters SET bill_due = bill_due - ? WHERE id = ?');
        $stmt->execute([$pay, $meter_id]);
        $postpaid_msg = '<span style="color:green;">Payment of KES ' . number_format($pay,2) . ' received. Thank you!</span>';
    } else {
        $postpaid_msg = '<span style="color:red;">No outstanding bill or invalid meter.</span>';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Meter Billing & Management</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container" style="max-width:500px;margin:2em auto;">
        <!-- Breadcrumbs navigation -->
        <nav aria-label="Breadcrumb" class="breadcrumbs-nav" style="margin: 1.5rem auto 0 auto; max-width:900px; background:transparent;">
          <ol class="breadcrumbs" style="display:flex; flex-wrap:wrap; gap:0.5em; list-style:none; padding:0; margin:0; font-size:1rem; background:transparent;">
            <li><a href="user_dashboard.php" class="breadcrumb-link"><i class="fa fa-tachometer-alt"></i> Dashboard</a></li>
            <li style="color:var(--color-secondary);">&gt;</li>
            <li class="breadcrumb-current" style="color:var(--color-primary); font-weight:600;"><i class="fa fa-bolt"></i> My Meters</li>
          </ol>
        </nav>
        <h2>Meter Billing & Management</h2>
        <?php if ($message) echo "<div style='margin-bottom:1em;'>$message</div>"; ?>
        <?php if ($action === 'request'): ?>
            <div class="card mb-md">
              <h3>Request a New Meter</h3>
              <form method="post">
                <div class="form-group">
                  <label for="meter_type">Meter Type</label>
                  <select name="meter_type" id="meter_type" required class="form-select">
                    <option value="">Select Type</option>
                    <option value="prepaid">Prepaid (Token)</option>
                    <option value="postpaid">Postpaid (Bill)</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="request_details">Additional Details (optional)</label>
                  <textarea name="request_details" id="request_details" class="form-input" placeholder="e.g. Address, phone, or any info for admin"></textarea>
                </div>
                <button type="submit" name="request_meter" class="btn btn-primary">Request Meter</button>
              </form>
            </div>
        <?php elseif ($action === 'link'): ?>
            <h3>Link an Existing Meter</h3>
            <form method="post">
                <div class="form-group">
                    <label for="meter_number">Meter Number</label>
                    <input type="text" name="meter_number" id="meter_number" required class="form-input">
                </div>
                <div class="form-group">
                    <label for="meter_type">Meter Type</label>
                    <select name="meter_type" id="meter_type" required class="form-select">
                        <option value="">Select Type</option>
                        <option value="prepaid">Prepaid (Token)</option>
                        <option value="postpaid">Postpaid (Bill)</option>
                    </select>
                </div>
                <button type="submit" name="link_meter" class="btn btn-primary">Link Meter</button>
            </form>
        <?php elseif ($action === 'manage'): ?>
            <h3 class="h3 mb-sm"><i class="fa fa-bolt"></i> My Meters</h3>
            <?php
            // Fetch all meters linked to this user
            $stmt = $pdo->prepare('SELECT * FROM meters WHERE user_id = ?');
            $stmt->execute([$user_id]);
            $meters = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $show_link_form = isset($_POST['show_link_form']) || (empty($meters) && isset($_POST['link_meter']));
            $show_request_form = isset($_POST['show_request_form']) || (empty($meters) && isset($_POST['request_meter']));
            ?>
            <?php if (empty($meters)): ?>
                <div class="mb-md">You have no meters linked to your account.</div>
                <div class="card mb-md">
                    <div class="quick-actions" style="display: flex; gap: 1rem; justify-content: center; margin-bottom: 0;">
                        <form method="post" style="display:inline;">
                            <button type="submit" name="show_link_form" class="btn btn-outline" style="display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fa fa-link"></i> I Have a Meter Number
                            </button>
                        </form>
                        <form method="post" style="display:inline;">
                            <button type="submit" name="show_request_form" class="btn btn-primary" style="display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fa fa-plus"></i> I Need a Meter
                            </button>
                        </form>
                    </div>
                </div>
                <?php if ($show_link_form): ?>
                    <div class="card mb-md">
                        <h4 class="h3 mb-sm">Link an Existing Meter</h4>
                        <form method="post">
                            <div class="form-group">
                                <label for="meter_number" class="form-label">Meter Number</label>
                                <input type="text" name="meter_number" id="meter_number" required class="form-input">
                            </div>
                            <div class="form-group">
                                <label for="meter_type" class="form-label">Meter Type</label>
                                <select name="meter_type" id="meter_type" required class="form-select">
                                    <option value="">Select Type</option>
                                    <option value="prepaid">Prepaid (Token)</option>
                                    <option value="postpaid">Postpaid (Bill)</option>
                                </select>
                            </div>
                            <button type="submit" name="link_meter" class="btn btn-primary">Link Meter</button>
                        </form>
                    </div>
                <?php elseif ($show_request_form): ?>
                    <div class="card mb-md">
                        <h4 class="h3 mb-sm">Request a New Meter</h4>
                        <form method="post">
                            <div class="form-group">
                                <label for="meter_type" class="form-label">Meter Type</label>
                                <select name="meter_type" id="meter_type" required class="form-select">
                                    <option value="">Select Type</option>
                                    <option value="prepaid">Prepaid (Token)</option>
                                    <option value="postpaid">Postpaid (Bill)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="request_details" class="form-label">Additional Details (optional)</label>
                                <textarea name="request_details" id="request_details" class="form-input" placeholder="e.g. Address, phone, or any info for admin"></textarea>
                            </div>
                            <button type="submit" name="request_meter" class="btn btn-primary">Request Meter</button>
                        </form>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <table class="styled-table mb-md" style="width:100%;">
                    <tbody>
                        <?php foreach ($meters as $meter): ?>
                            <tr>
                                <td colspan="4">
                                  <div class="card" style="margin-bottom:1em; padding:1em; box-shadow:0 2px 8px rgba(0,0,0,0.07); border-radius:10px;">
                                    <div style="display:flex; flex-direction:column; gap:0.5em;">
                                      <div><strong>Meter Number:</strong> <?php echo htmlspecialchars($meter['meter_number']); ?></div>
                                      <div><strong>Type:</strong> <?php echo ucfirst($meter['meter_type']); ?></div>
                                      <div><strong>Balance/Bill:</strong> <?php if ($meter['meter_type'] === 'prepaid'): ?><?php echo isset($meter['credit_balance']) ? number_format($meter['credit_balance'], 2) : '0.00'; ?> kWh<?php else: ?><?php echo isset($meter['bill_due']) ? 'KSh ' . number_format($meter['bill_due'], 2) : 'KSh 0.00'; ?><?php endif; ?></div>
                                      <div>
                                        <?php if ($meter['meter_type'] === 'prepaid'): ?>
                                          <a href="#" class="btn btn-primary btn-sm" onclick="openBuyTokenModal(<?php echo $meter['id']; ?>); return false;">Buy Token</a>
                                          <form method="post" style="display:inline; margin-top:0.5em;">
                                            <input type="hidden" name="recharge_meter_id" value="<?php echo $meter['id']; ?>">
                                            <input type="text" name="recharge_token" placeholder="Enter token" required style="width:160px;">
                                            <button type="submit" class="btn btn-success btn-sm">Recharge</button>
                                          </form>
                                        <?php else: ?>
                                          <a href="#" class="btn btn-outline btn-sm" onclick="openPayBillModal(<?php echo $meter['id']; ?>, <?php echo isset($meter['bill_due']) ? $meter['bill_due'] : 0; ?>); return false;">Pay Bill</a>
                                        <?php endif; ?>
                                      </div>
                                      <?php if (!empty($recharge_msg) && isset($_POST['recharge_meter_id']) && $_POST['recharge_meter_id'] == $meter['id']) echo '<div>' . $recharge_msg . '</div>'; ?>
                                    </div>
                                  </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="card mb-md">
                    <div class="quick-actions" style="display: flex; gap: 1rem; justify-content: center; margin-bottom: 0;">
                        <form method="post" style="display:inline;">
                            <button type="submit" name="show_link_form" class="btn btn-outline" style="display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fa fa-link"></i> Add Another Meter
                            </button>
                        </form>
                        <form method="post" style="display:inline;">
                            <button type="submit" name="show_request_form" class="btn btn-primary" style="display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fa fa-plus"></i> Request Another Meter
                            </button>
                        </form>
                    </div>
                </div>
                <?php if ($show_link_form): ?>
                    <div class="card mb-md">
                        <h4 class="h3 mb-sm">Link an Existing Meter</h4>
                        <form method="post">
                            <div class="form-group">
                                <label for="meter_number" class="form-label">Meter Number</label>
                                <input type="text" name="meter_number" id="meter_number" required class="form-input">
                            </div>
                            <div class="form-group">
                                <label for="meter_type" class="form-label">Meter Type</label>
                                <select name="meter_type" id="meter_type" required class="form-select">
                                    <option value="">Select Type</option>
                                    <option value="prepaid">Prepaid (Token)</option>
                                    <option value="postpaid">Postpaid (Bill)</option>
                                </select>
                            </div>
                            <button type="submit" name="link_meter" class="btn btn-primary">Link Meter</button>
                        </form>
                    </div>
                <?php elseif ($show_request_form): ?>
                    <div class="card mb-md">
                        <h4 class="h3 mb-sm">Request a New Meter</h4>
                        <form method="post">
                            <div class="form-group">
                                <label for="meter_type" class="form-label">Meter Type</label>
                                <select name="meter_type" id="meter_type" required class="form-select">
                                    <option value="">Select Type</option>
                                    <option value="prepaid">Prepaid (Token)</option>
                                    <option value="postpaid">Postpaid (Bill)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="request_details" class="form-label">Additional Details (optional)</label>
                                <textarea name="request_details" id="request_details" class="form-input" placeholder="e.g. Address, phone, or any info for admin"></textarea>
                            </div>
                            <button type="submit" name="request_meter" class="btn btn-primary">Request Meter</button>
                        </form>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php else: ?>
            <div style="text-align:center;">
                <a href="?action=request" class="btn btn-outline" style="margin:1em;">Request a New Meter</a>
                <a href="?action=link" class="btn btn-primary" style="margin:1em;">Link an Existing Meter</a>
            </div>
        <?php endif; ?>
    </div>

<script>
function openBuyTokenModal(meterId) {
    document.getElementById('buyTokenMeterId').value = meterId;
    document.getElementById('buyTokenModal').style.display = 'flex'; // Use flex for centering
}
function closeBuyTokenModal() {
    document.getElementById('buyTokenModal').style.display = 'none';
}
</script>
<style>
#buyTokenModal { display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center; }
#buyTokenModal .modal-content { background:#fff; padding:2em; border-radius:12px; max-width:400px; margin:auto; }
</style>
<div id="buyTokenModal">
  <div class="modal-content">
    <h3>Buy Prepaid Token</h3>
    <form method="post">
      <input type="hidden" name="buy_token_meter_id" id="buyTokenMeterId">
      <div class="form-group">
        <label>Amount (KES)</label>
        <input type="number" name="buy_token_amount" min="10" step="1" required class="form-input">
      </div>
      <button type="submit" class="btn btn-primary">Generate Token</button>
      <button type="button" class="btn btn-outline" onclick="closeBuyTokenModal()">Cancel</button>
    </form>
    <?php if (!empty($token_msg)) echo '<div class="mt-md">' . $token_msg . '</div>'; ?>
  </div>
</div>

<script>
function openPayBillModal(meterId, billDue) {
    document.getElementById('payBillMeterId').value = meterId;
    document.getElementById('payBillAmount').max = billDue;
    document.getElementById('payBillAmount').value = billDue > 0 ? billDue : '';
    document.getElementById('payBillModal').style.display = 'flex';
}
function closePayBillModal() {
    document.getElementById('payBillModal').style.display = 'none';
}
</script>
<style>
#payBillModal { display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center; }
#payBillModal .modal-content { background:#fff; padding:2em; border-radius:12px; max-width:400px; margin:auto; }
</style>
<div id="payBillModal">
  <div class="modal-content">
    <h3>Pay Postpaid Bill</h3>
    <form method="post">
      <input type="hidden" name="pay_bill_meter_id" id="payBillMeterId">
      <div class="form-group">
        <label>Amount (KES)</label>
        <input type="number" name="pay_bill_amount" id="payBillAmount" min="1" step="1" required class="form-input">
      </div>
      <button type="submit" class="btn btn-primary">Pay</button>
      <button type="button" class="btn btn-outline" onclick="closePayBillModal()">Cancel</button>
    </form>
    <?php if (!empty($postpaid_msg)) echo '<div class=\'mt-md\'>' . $postpaid_msg . '</div>'; ?>
  </div>
</div>
</body>
</html> 