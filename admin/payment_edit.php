<?php
require_once '../auth.php';
requireAdmin();
require_once '../connectdb.php';

$pageTitle = 'Edit Payment';


$id = $_GET['id'] ?? 0;



$stmt = $conn->prepare("SELECT py.*, m.full_name FROM payments py JOIN members m ON py.member_id = m.member_id WHERE py.payment_id = ?");
if ($stmt === false) {
    die("SQL Prepare Error: " . $conn->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$pay = $result->fetch_assoc();


if (!$pay) {
    header("Location: payments.php");
    exit();
}


$memberQuery = $conn->query("SELECT member_id, full_name FROM members ORDER BY full_name");
$members = ($memberQuery) ? $memberQuery->fetch_all(MYSQLI_ASSOC) : [];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberId = (int)($_POST['member_id'] ?? 0);
    $paymentDate = $_POST['payment_date'] ?? '';
    $amount = (float)($_POST['amount'] ?? 0);
    $method = $_POST['payment_method'] ?? 'Cash';
    $status = $_POST['payment_status'] ?? 'Pending';

    if ($memberId <= 0 || $amount <= 0 || empty($paymentDate)) {
        $errors[] = 'All fields are required and must have valid values.';
    } else {
        
        
        $updateStmt = $conn->prepare("UPDATE payments SET member_id = ?, payment_date = ?, amount = ?, payment_method = ?, payment_status = ? WHERE payment_id = ?");
        
        if ($updateStmt === false) {
            die("SQL Update Error: " . $conn->error);
        }

        
        $updateStmt->bind_param("isdssi", $memberId, $paymentDate, $amount, $method, $status, $id);
        
        if ($updateStmt->execute()) {
            header("Location: payments.php?msg=Payment updated successfully");
            exit();
        } else {
            $errors[] = "Update failed: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Payment</title>
    <style>
        body { font-family: sans-serif; background: 
        .card { max-width: 500px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 10px; border: 1px solid 
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; color: white; text-decoration: none; display: inline-block; font-weight: bold; }
        .btn-primary { background: 
        .btn-secondary { background: 
        .alert-danger { background: 
        hr { border: 0; border-top: 1px solid 
    </style>
</head>
<body>

<div class="card">
    <h1>Edit Payment</h1>
    <p style="color: #666;">Transaction ID: 
    <hr>

    <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
    <?php endforeach; ?>

    <form method="POST">
        <div class="form-group">
            <label for="member_id">Member *</label>
            <select id="member_id" name="member_id" required>
                <?php foreach ($members as $m): ?>
                    <option value="<?php echo $m['member_id']; ?>" <?php echo ($m['member_id'] == $pay['member_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($m['full_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="payment_date">Payment Date *</label>
            <input type="date" id="payment_date" name="payment_date" value="<?php echo $pay['payment_date']; ?>" required>
        </div>

        <div class="form-group">
            <label for="amount">Amount (RM) *</label>
            <input type="number" id="amount" name="amount" step="0.01" min="0.01" value="<?php echo $pay['amount']; ?>" required>
        </div>

        <div class="form-group">
            <label for="payment_method">Payment Method</label>
            <select id="payment_method" name="payment_method">
                <option value="Cash" <?php echo ($pay['payment_method'] === 'Cash') ? 'selected' : ''; ?>>Cash</option>
                <option value="Card" <?php echo ($pay['payment_method'] === 'Card') ? 'selected' : ''; ?>>Card</option>
                <option value="Online Transfer" <?php echo ($pay['payment_method'] === 'Online Transfer') ? 'selected' : ''; ?>>Online Transfer</option>
            </select>
        </div>

        <div class="form-group">
            <label for="payment_status">Status</label>
            <select id="payment_status" name="payment_status">
                <option value="Paid" <?php echo ($pay['payment_status'] === 'Paid') ? 'selected' : ''; ?>>Paid</option>
                <option value="Pending" <?php echo ($pay['payment_status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="Cancelled" <?php echo ($pay['payment_status'] === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary">Update Payment</button>
            <a href="payments.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

</body>
</html>