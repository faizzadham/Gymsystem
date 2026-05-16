<?php
require_once '../auth.php';       
requireMember();
require_once '../connectdb.php'; 

$pageTitle = 'Payment History';
$userId = $_SESSION['user_id'];

// Fetch payments using a JOIN to handle everything in one trip to the DB
$query = "SELECT p.* FROM payments p 
          JOIN members m ON p.member_id = m.member_id 
          WHERE m.user_id = ? 
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

// 1. The Header loads FIRST with its own styles
require_once '../header.php';
?>

<link rel="stylesheet" href="payments.css">

<div class="payments-page-wrapper">
    
    <div class="payment-hero-banner">
        <h1><i class="fas fa-credit-card"></i> Payment History</h1>
        <p>View all your payment records</p>
    </div>

    <div class="payment-container fade-in">
        <div class="payment-table-card">
            <table>
                <thead>
                    <tr>
                        <th style="width: 8%;">#</th>
                        <th style="width: 27%;">Date</th>
                        <th style="width: 25%;">Amount (RM)</th>
                        <th style="width: 23%;">Method</th>
                        <th style="width: 17%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center; color:#6c757d; padding: 40px 0;">
                                No payment records found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $index => $p): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars(date('Y-m-d', strtotime($p['payment_date']))) ?></td>
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
    
</div>