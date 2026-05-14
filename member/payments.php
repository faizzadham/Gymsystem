<?php
require_once '../auth.php';       
requireMember();
require_once '../connectdb.php'; 

$pageTitle = 'Payment History';
$userId = $_SESSION['user_id'];

// Fetch payments using a JOIN to handle everything in one trip to the DB
$query = "SELECT p.* FROM payments p 
          JOIN members m ON p.member_id = m.member_id 
        S  WHERE m.user_id = ? 
          ORDER BY p.payment_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$payments = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

function getStatusClass($status) {
    return match($status) {
        'Paid'    => 'badge-success',
        'Pending' => 'badge-warning',
        default   => 'badge-danger',
    };
}

require_once '../header.php';
?>

<div class="container fade-in">
    <div class="page-header">
        <h1><i class="fas fa-credit-card"></i> Payment History</h1>
        <p>View all your payment records</p>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Amount (RM)</th>
                    <th>Method</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; color:var(--text-muted);">
                            No payment records found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($payments as $index => $p): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($p['payment_date']) ?></td>
                            <td><?= number_format($p['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($p['payment_method']) ?></td>
                            <td>
                                <span class="badge <?= getStatusClass($p['payment_status']) ?>">
                                    <?= htmlspecialchars($p['payment_status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

