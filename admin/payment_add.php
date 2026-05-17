<?php
require_once '../auth.php';
requireAdmin();
require_once '../connectdb.php';

$pageTitle = 'Add Payment';


$memberQuery = $conn->query("SELECT member_id, full_name FROM members ORDER BY full_name");
$members = ($memberQuery) ? $memberQuery->fetch_all(MYSQLI_ASSOC) : [];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberId = (int)($_POST['member_id'] ?? 0);
    $paymentDate = $_POST['payment_date'] ?? date('Y-m-d');
    $amount = (float)($_POST['amount'] ?? 0);
    $method = $_POST['payment_method'] ?? 'Cash';
    $status = $_POST['payment_status'] ?? 'Pending';

    if ($memberId <= 0 || $amount <= 0) {
        $errors[] = 'Please select a member and enter a valid amount.';
    } else {
        
        $sql = "INSERT INTO payments (member_id, payment_date, amount, payment_method, payment_status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            
            die("SQL Prepare Error: " . $conn->error);
        }

        
        $stmt->bind_param("isdss", $memberId, $paymentDate, $amount, $method, $status);
        
        if ($stmt->execute()) {
            header("Location: payments.php?msg=Payment added successfully");
            exit();
        } else {
            $errors[] = "Database Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: 
        .card { max-width: 500px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        h1 { margin-top: 0; color: 
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; }
        input, select { width: 100%; padding: 12px; border: 1px solid 
        input:focus, select:focus { outline: none; border-color: 
        .btn-group { margin-top: 25px; display: flex; gap: 10px; }
        .btn { padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; color: white; text-decoration: none; font-weight: bold; flex: 1; text-align: center; }
        .btn-primary { background: 
        .btn-primary:hover { background: 
        .btn-secondary { background: 
        .alert-danger { background: 
        hr { border: 0; border-top: 1px solid 
    </style>
</head>
<body>

<div class="card">
    <h1>Add New Payment</h1>
    <hr>

    <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
    <?php endforeach; ?>

    <form method="POST">
        <div class="form-group">
            <label for="member_id">Member *</label>
            <select id="member_id" name="member_id" required>
                <option value="">-- Select Member --</option>
                <?php foreach ($members as $m): ?>
                    <option value="<?php echo $m['member_id']; ?>">
                        <?php echo htmlspecialchars($m['full_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="payment_date">Payment Date</label>
            <input type="date" id="payment_date" name="payment_date" value="<?php echo date('Y-m-d'); ?>">
        </div>

        <div class="form-group">
            <label for="amount">Amount (RM) *</label>
            <input type="number" id="amount" name="amount" step="0.01" min="0.01" required placeholder="0.00">
        </div>

        <div class="form-group">
            <label for="payment_method">Payment Method</label>
            <select id="payment_method" name="payment_method">
                <option value="Cash">Cash</option>
                <option value="Card">Card</option>
                <option value="Online Transfer">Online Transfer</option>
            </select>
        </div>

        <div class="form-group">
            <label for="payment_status">Status</label>
            <select id="payment_status" name="payment_status">
                <option value="Paid">Paid</option>
                <option value="Pending" selected>Pending</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-primary">Save Payment</button>
            <a href="payments.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

</body>
</html>