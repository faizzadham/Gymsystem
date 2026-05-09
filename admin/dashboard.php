<?php
require_once '../auth.php';
requireAdmin();
require_once '../connectdb.php';

$pageTitle = 'Admin Dashboard';

/** 
 * HELPER FUNCTION for MySQLi 
 * Because MySQLi doesn't have fetchColumn(), we grab the first row/first value manually.
 */
function getSingleValue($conn, $sql) {
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_row();
        return $row[0] ?? 0;
    }
    return 0;
}

// 1. Membership Stats
$totalMembers   = getSingleValue($conn, "SELECT COUNT(*) FROM members");
$activeMembers  = getSingleValue($conn, "SELECT COUNT(*) FROM members WHERE status = 'active'");
$expiredMembers = getSingleValue($conn, "SELECT COUNT(*) FROM members WHERE status = 'expired'");
$monthlyIncome  = getSingleValue($conn, "SELECT SUM(amount) FROM payments WHERE payment_status = 'Paid' AND MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE())");

// 2. PT Stats
$totalPTSessions   = getSingleValue($conn, "SELECT COUNT(*) FROM session_bookings");
$activeBookings    = getSingleValue($conn, "SELECT COUNT(*) FROM session_bookings WHERE booking_status = 'Approved'");
$availableTrainers = getSingleValue($conn, "SELECT COUNT(*) FROM trainers WHERE status = 'Available'");
$monthlyPTRevenue  = getSingleValue($conn, "SELECT SUM(t.session_fee) FROM session_bookings sb JOIN trainers t ON sb.trainer_id = t.trainer_id WHERE sb.booking_status IN ('Approved','Completed') AND MONTH(sb.session_date) = MONTH(CURDATE()) AND YEAR(sb.session_date) = YEAR(CURDATE())");

// 3. Recent Members (MySQLi fetch_all)
$recentMembers = $conn->query("SELECT m.*, p.package_name FROM members m LEFT JOIN membership_packages p ON m.package_id = p.package_id ORDER BY m.join_date DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// 4. Recent Bookings (MySQLi fetch_all)
$recentBookings = $conn->query("SELECT sb.*, m.full_name, t.trainer_name FROM session_bookings sb JOIN members m ON sb.member_id = m.member_id JOIN trainers t ON sb.trainer_id = t.trainer_id ORDER BY sb.created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// If you don't have these files yet, comment them out like in bookings.php
// require_once '../header.php'; 
?>

<!-- You can reuse the CSS from the bookings.php I gave you earlier -->
<style>
    /* ... Add the same CSS here or link to a stylesheet ... */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: flex; align-items: center; }
    .stat-icon { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: white; font-size: 20px; }
    .blue { background: #3498db; } .green { background: #2ecc71; } .red { background: #e74c3c; } .purple { background: #9b59b6; }
    .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; color: white; }
    .badge-success { background: #2ecc71; } .badge-warning { background: #f1c40f; } .badge-danger { background: #e74c3c; } .badge-info { background: #3498db; }
</style>

<div class="admin-layout">
    <?php // include 'sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></strong>!</p>
        </div>

        <!-- Membership Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">M</div>
                <div class="stat-info">
                    <h3><?php echo $totalMembers; ?></h3>
                    <p>Total Members</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">A</div>
                <div class="stat-info">
                    <h3><?php echo $activeMembers; ?></h3>
                    <p>Active</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">RM</div>
                <div class="stat-info">
                    <h3>RM <?php echo number_format($monthlyIncome, 2); ?></h3>
                    <p>Monthly Income</p>
                </div>
            </div>
        </div>

        <!-- Recent PT Bookings -->
        <div class="card" style="background:white; padding:20px; border-radius:8px;">
            <h3>Recent PT Bookings</h3>
            <table style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align:left; border-bottom: 2px solid #eee;">
                        <th>Member</th>
                        <th>Trainer</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentBookings as $b): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding:10px 0;"><?php echo htmlspecialchars($b['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($b['trainer_name']); ?></td>
                        <td><?php echo $b['session_date']; ?></td>
                        <td>
                            <span class="badge <?php echo ($b['booking_status'] == 'Approved') ? 'badge-success' : 'badge-warning'; ?>">
                                <?php echo $b['booking_status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <br>
            <a href="bookings.php" style="color:#3498db; text-decoration:none;">View All Bookings →</a>
        </div>
    </div>
</div>