<?php
// 1. Error reporting to debug the "Blank Screen"
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../auth.php'; 
requireMember(); 
require_once '../connectdb.php';

$pageTitle = 'My Dashboard';
$userId = $_SESSION['user_id'];


$member_sql = "SELECT m.*, p.package_name, p.price 
               FROM members m 
               LEFT JOIN membership_packages p ON m.package_id = p.package_id 
               WHERE m.user_id = ?";
$stmt = mysqli_prepare($conn, $member_sql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$member = mysqli_fetch_assoc($result);

$recentPayments = [];
$upcomingSessions = [];

if ($member) {
    $memberId = $member['member_id'];


    $pay_sql = "SELECT * FROM payments WHERE member_id = ? ORDER BY payment_date DESC LIMIT 5";
    $pay_stmt = mysqli_prepare($conn, $pay_sql);
    mysqli_stmt_bind_param($pay_stmt, "i", $memberId);
    mysqli_stmt_execute($pay_stmt);
    $pay_res = mysqli_stmt_get_result($pay_stmt);
    while ($row = mysqli_fetch_assoc($pay_res)) {
        $recentPayments[] = $row;
    }

    $sess_sql = "SELECT sb.*, t.trainer_name 
                 FROM session_bookings sb 
                 JOIN trainers t ON sb.trainer_id = t.trainer_id 
                 WHERE sb.member_id = ? 
                 AND sb.session_date >= CURDATE() 
                 AND sb.booking_status IN ('Pending','Approved') 
                 ORDER BY sb.session_date ASC LIMIT 3";
    $sess_stmt = mysqli_prepare($conn, $sess_sql);
    mysqli_stmt_bind_param($sess_stmt, "i", $memberId);
    mysqli_stmt_execute($sess_stmt);
    $sess_res = mysqli_stmt_get_result($sess_stmt);
    while ($row = mysqli_fetch_assoc($sess_res)) {
        $upcomingSessions[] = $row;
    }
}

require_once '../header.php'; // Ensure path is correct
?>

<div class="container fade-in">
    <div class="page-header">
        <h1>Welcome, <?php echo htmlspecialchars($member['full_name'] ?? $_SESSION['username']); ?>!</h1>
        <p>Your membership overview</p>
    </div>

    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 2rem;">
        <div class="stat-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <div class="stat-info">
                <h3><?php echo htmlspecialchars($member['package_name'] ?? 'None'); ?></h3>
                <p>Current Package</p>
            </div>
        </div>
        <div class="stat-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <div class="stat-info">
                <h3 style="color: <?php echo ($member['status'] ?? '') === 'active' ? 'green' : 'red'; ?>">
                    <?php echo ucfirst($member['status'] ?? 'N/A'); ?>
                </h3>
                <p>Membership Status</p>
            </div>
        </div>
        <div class="stat-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <div class="stat-info">
                <h3><?php echo count($upcomingSessions); ?></h3>
                <p>Upcoming PT Sessions</p>
            </div>
        </div>
    </div>

    <div style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:2rem;">
        <a href="book_session.php" style="padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px;">Book Session</a>
        <a href="timetable.php" style="padding: 10px 20px; background: #95a5a6; color: white; text-decoration: none; border-radius: 5px;">My Timetable</a>
    </div>

    <!-- Upcoming PT Sessions -->
    <div class="card" style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 1.5rem;">
        <h3>Upcoming PT Sessions</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead><tr style="text-align: left; border-bottom: 1px solid #eee;"><th>Date</th><th>Trainer</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($upcomingSessions as $s): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px 0;"><?php echo $s['session_date']; ?></td>
                    <td><?php echo htmlspecialchars($s['trainer_name']); ?></td>
                    <td><?php echo $s['booking_status']; ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($upcomingSessions)) echo "<tr><td colspan='3' style='padding:10px;'>No sessions.</td></tr>"; ?>
            </tbody>
        </table>
    </div>

    <!-- Recent Payments -->
    <div class="card" style="background: white; padding: 20px; border-radius: 8px;">
        <h3>Recent Payments</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead><tr style="text-align: left; border-bottom: 1px solid #eee;"><th>Date</th><th>Amount (RM)</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($recentPayments as $p): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px 0;"><?php echo $p['payment_date']; ?></td>
                    <td><?php echo number_format($p['amount'], 2); ?></td>
                    <td><?php echo $p['payment_status']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once '../footer.php'; ?>
