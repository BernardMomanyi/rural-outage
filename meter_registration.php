<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $meter_type = $_POST['meter_type'];
    $request_details = trim($_POST['request_details'] ?? '');

    // Insert request into meter_requests table
    $stmt = $pdo->prepare("INSERT INTO meter_requests (user_id, request_details, status) VALUES (?, ?, 'pending')");
    if ($stmt->execute([$user_id, "Type: $meter_type\nDetails: $request_details"])) {
        $message = '<span style="color:green;">Your meter request has been submitted and is pending admin approval.</span>';
    } else {
        $message = '<span style="color:red;">Error submitting request. Please try again.</span>';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Request New Meter</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container" style="max-width:400px;margin:2em auto;">
        <h2>Request a New Meter</h2>
        <?php if ($message) echo "<div>$message</div>"; ?>
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
            <button type="submit" class="btn btn-primary">Request Meter</button>
        </form>
    </div>
</body>
</html> 