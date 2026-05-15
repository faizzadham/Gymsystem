<?php
require_once '../auth.php';
requireMember();
require_once '../connectdb.php';

$pageTitle = 'My Dashboard';
$userId = $_SESSION['user_id'];

// 1. Fetch Member & Package Info
$memberStmt = $conn->prepare("
    SELECT m.*, p.package_name, p.price 
    FROM members m 
    LEFT JOIN membership_packages p ON m.package_id = p.package_id 
    WHERE m.user_id = ?
");
$memberStmt->bind_param("i", $userId);
$memberStmt->execute();
$memberResult = $memberStmt->get_result();
$member = $memberResult->fetch_assoc();
$memberStmt->close();

// 2. Fetch Related Data (only if member exists)
$recentPayments = [];
$upcomingSessions = [];

if ($member) {
    $memberId = $member['member_id'];

    // Recent Payments
    $payStmt = $conn->prepare("SELECT * FROM payments WHERE member_id = ? ORDER BY payment_date DESC LIMIT 5");
    $payStmt->bind_param("i", $memberId);
    $payStmt->execute();
    $payResult = $payStmt->get_result();
    $recentPayments = $payResult->fetch_all(MYSQLI_ASSOC);
    $payStmt->close();

    // Upcoming Sessions
    $sessStmt = $conn->prepare("
        SELECT sb.*, t.trainer_name 
        FROM session_bookings sb 
        JOIN trainers t ON sb.trainer_id = t.trainer_id 
        WHERE sb.member_id = ? 
          AND sb.session_date >= CURDATE() 
          AND sb.booking_status IN ('Pending','Approved') 
        ORDER BY sb.session_date ASC LIMIT 3
    ");
    $sessStmt->bind_param("i", $memberId);
    $sessStmt->execute();
    $sessResult = $sessStmt->get_result();
    $upcomingSessions = $sessResult->fetch_all(MYSQLI_ASSOC);
    $sessStmt->close();
}

// 3. UI Helper Logic
$status = ($member && isset($member['status'])) ? $member['status'] : 'inactive';
$statusConfig = [
    'active'   => ['class' => 'green', 'icon' => 'check-circle'],
    'inactive' => ['class' => 'red',   'icon' => 'times-circle'],
    'pending'  => ['class' => 'orange','icon' => 'clock']
];
$currentStatus = $statusConfig[$status] ?? ['class' => 'red', 'icon' => 'times-circle'];

require_once __DIR__ . '/../header.php';
?>

<link rel="stylesheet" href="dashboard.css">

<div class="page-header">
    <div class="container" style="margin: 0 auto; padding: 0;">
        <h1>Welcome Back, <?= htmlspecialchars($member ? ($member['full_name'] ?? $_SESSION['username']) : $_SESSION['username']) ?>!</h1>
        <p>Access your fitness overview</p>
    </div>
</div>

<div class="container fade-in">

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-id-card"></i></div>
            <div class="stat-info">
                <h3><?= htmlspecialchars($member['package_name'] ?? 'None') ?></h3>
                <p>Current Package</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon <?= $currentStatus['class'] ?>">
                <i class="fas fa-<?= $currentStatus['icon'] ?>"></i>
            </div>
            <div class="stat-info">
                <h3><?= ucfirst($status) ?></h3>
                <p>Membership Status</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-calendar-alt"></i></div>
            <div class="stat-info">
                <h3><?= $member['expiry_date'] ?? 'N/A' ?></h3>
                <p>Expiry Date</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-dumbbell"></i></div>
            <div class="stat-info">
                <h3><?= count($upcomingSessions) ?></h3>
                <p>Upcoming PT Sessions</p>
            </div>
        </div>
    </div>

    <div style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:2rem;">
        <a href="profile.php" class="btn btn-primary"><i class="fas fa-user"></i> My Profile</a>
        <a href="membership.php" class="btn btn-secondary"><i class="fas fa-box"></i> Membership</a>
        <a href="payments.php" class="btn btn-secondary"><i class="fas fa-credit-card"></i> Payments</a>
        <a href="trainers.php" class="btn btn-secondary"><i class="fas fa-user-tie"></i> Trainers</a>
        <a href="booking_session.php" class="btn btn-primary"><i class="fas fa-calendar-plus"></i> Book Session</a>
        <a href="timetable.php" class="btn btn-secondary"><i class="fas fa-calendar-alt"></i> My Timetable</a>
    </div>

    <?php if (!empty($upcomingSessions)): ?>
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header">
            <h3><i class="fas fa-dumbbell" style="color:var(--primary);"></i> Upcoming PT Sessions</h3>
            <a href="timetable.php" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Trainer</th>
                        <th>Type</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($upcomingSessions as $s): ?>
                    <tr>
                        <td><?= $s['session_date'] ?></td>
                        <td><?= $s['session_time'] ?></td>
                        <td><?= htmlspecialchars($s['trainer_name']) ?></td>
                        <td><?= $s['session_type'] ?></td>
                        <td>
                            <span class="badge <?= $s['booking_status'] === 'Approved' ? 'badge-success' : 'badge-warning' ?>">
                                <?= $s['booking_status'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h3>Recent Payments</h3>
            <a href="payments.php" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount (RM)</th>
                        <th>Method</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentPayments)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center; color:var(--text-muted);">No payments yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentPayments as $p): ?>
                        <tr>
                            <td><?= $p['payment_date'] ?></td>
                            <td><?= number_format($p['amount'], 2) ?></td>
                            <td><?= $p['payment_method'] ?></td>
                            <td>
                                <?php 
                                    $pStatus = $p['payment_status'];
                                    $pClass = ($pStatus === 'Paid') ? 'badge-success' : (($pStatus === 'Pending') ? 'badge-warning' : 'badge-danger');
                                ?>
                                <span class="badge <?= $pClass ?>"><?= $pStatus ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<a href="logout.php" class="logout-btn">
    <i class="fas fa-sign-out-alt"></i> Logout
</a>

<?php require_once __DIR__ . '/../footer.php'; ?>