<?php
require_once '../auth.php';
requireAdmin();
require_once '../connectdb.php';

$pageTitle = 'Payment Management';

// 1. Handle Form Submission (Record New Payment)
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_payment'])) {
    $memberId = (int)($_POST['member_id'] ?? 0);
    $paymentDate = date('Y-m-d'); // Current date
    $amount = (float)($_POST['amount'] ?? 0);
    $method = $_POST['payment_method'] ?? 'Cash';
    $status = 'Paid'; // Default status for quick add

    if ($memberId <= 0 || $amount <= 0) {
        $errors[] = 'Please select a member and enter a valid amount.';
    } else {
        // FIXED: Using member_id to match image_775416.png
        $stmt = $conn->prepare("INSERT INTO payments (member_id, payment_date, amount, payment_method, payment_status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isdss", $memberId, $paymentDate, $amount, $method, $status);
        
        if ($stmt->execute()) {
            header("Location: payments.php?msg=Payment recorded successfully");
            exit();
        } else {
            $errors[] = "Database Error: " . $conn->error;
        }
    }
}

// 2. Fetch Members for the Dropdown
$memberQuery = $conn->query("SELECT member_id, full_name FROM members ORDER BY full_name");
$members = ($memberQuery) ? $memberQuery->fetch_all(MYSQLI_ASSOC) : [];

// 3. Fetch Transaction History
// FIXED: JOIN on member_id to ensure history displays correctly
$historyQuery = "SELECT py.*, m.full_name 
                 FROM payments py 
                 JOIN members m ON py.member_id = m.member_id 
                 ORDER BY py.payment_date DESC, py.payment_id DESC";
$historyResult = $conn->query($historyQuery);
$history = ($historyResult) ? $historyResult->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f7f6; padding: 20px; color: #333; }
        .container { max-width: 1000px; margin: auto; }
        .card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 30px; }
        h1 { margin-bottom: 25px; font-size: 28px; }
        h2 { font-size: 18px; margin-top: 0; margin-bottom: 20px; }
        
        /* Form Layout */
        .payment-form { display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; }
        .form-group { flex: 1; min-width: 200px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; }
        select, input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        
        .btn-save { background: #27ae60; color: white; padding: 11px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn-save:hover { background: #219150; }

        /* Table Design */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #34495e; color: white; text-align: left; padding: 12px; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f9f9f9; }
        
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .status-paid { background: #d4edda; color: #155724; }
        
        .actions a { text-decoration: none; margin-right: 10px; font-size: 14px; }
        .edit { color: #3498db; }
        .delete { color: #e74c3c; }
        .empty-msg { text-align: center; color: #7f8c8d; padding: 20px; }
    </style>
</head>
<body>

<div class="container">
    <h1>Payment Management</h1>

    <!-- 1. Quick Add Form -->
    <div class="card">
        <h2>Record New Payment</h2>
        <form class="payment-form" method="POST">
            <div class="form-group">
                <label>Member</label>
                <select name="member_id" required>
                    <option value="">-- Select Member --</option>
                    <?php foreach ($members as $m): ?>
                        <option value="<?php echo $m['member_id']; ?>"><?php echo htmlspecialchars($m['full_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Amount (RM)</label>
                <input type="number" name="amount" step="0.01" placeholder="0.00" required>
            </div>
            <div class="form-group">
                <label>Method</label>
                <select name="payment_method">
                    <option value="Cash">Cash</option>
                    <option value="Card">Card</option>
                    <option value="Online Transfer">Online Transfer</option>
                </select>
            </div>
            <button type="submit" name="save_payment" class="btn-save">Save Payment</button>
        </form>
    </div>

    <!-- 2. Transaction History Table -->
    <div class="card">
        <h2>Transaction History</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Member Name</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($history)): ?>
                    <tr>
                        <td colspan="6" class="empty-msg">No payments recorded yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($history as $row): ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($row['payment_date'])); ?></td>
                            <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                            <td>RM <?php echo number_format($row['amount'], 2); ?></td>
                            <td><?php echo $row['payment_method']; ?></td>
                            <td><span class="status-badge status-paid"><?php echo $row['payment_status']; ?></span></td>
                            <td class="actions">
                                <a href="payment_edit.php?id=<?php echo $row['payment_id']; ?>" class="edit">Edit</a>
                                <a href="payment_delete.php?id=<?php echo $row['payment_id']; ?>" class="delete" onclick="return confirm('Delete this record?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>