<?php

require_once '../auth.php';
requireAdmin();
require_once '../connectdb.php';

// Members stats
$result = $conn->query("SELECT COUNT(*) AS total FROM members");
$row = $result->fetch_assoc();
$totalMembers = $row['total'];

$result = $conn->query("SELECT COUNT(*) AS total FROM members WHERE status = 'active'");
$row = $result->fetch_assoc();
$activeMembers = $row['total'];

$result = $conn->query("SELECT COUNT(*) AS total FROM members WHERE status = 'expired'");
$row = $result->fetch_assoc();
$expiredMembers = $row['total'];

// Monthly income
$result = $conn->query("
    SELECT COALESCE(SUM(amount), 0) AS total
    FROM payments
    WHERE payment_status = 'Paid'
    AND MONTH(payment_date) = MONTH(CURDATE())
    AND YEAR(payment_date) = YEAR(CURDATE())
");
$row = $result->fetch_assoc();
$monthlyIncome = $row['total'];


// PT Stats
$result = $conn->query("SELECT COUNT(*) AS total FROM session_bookings");
$row = $result->fetch_assoc();
$totalPTSessions = $row['total'];

$result = $conn->query("SELECT COUNT(*) AS total FROM session_bookings WHERE booking_status = 'Approved'");
$row = $result->fetch_assoc();
$activeBookings = $row['total'];

$result = $conn->query("
    SELECT COALESCE(SUM(t.session_fee), 0) AS total
    FROM session_bookings sb
    JOIN trainers t ON sb.trainer_id = t.trainer_id
    WHERE sb.booking_status IN ('Approved','Completed')
    AND MONTH(sb.session_date) = MONTH(CURDATE())
    AND YEAR(sb.session_date) = YEAR(CURDATE())
");
$row = $result->fetch_assoc();
$monthlyPTRevenue = $row['total'];

$result = $conn->query("SELECT COUNT(*) AS total FROM trainers WHERE status = 'Available'");
$row = $result->fetch_assoc();
$availableTrainers = $row['total'];


// Recent members
$result = $conn->query("
    SELECT m.*, p.package_name
    FROM members m
    LEFT JOIN membership_packages p ON m.package_id = p.package_id
    ORDER BY m.join_date DESC
    LIMIT 5
");
$recentMembers = $result->fetch_all(MYSQLI_ASSOC);


// Recent bookings
$result = $conn->query("
    SELECT sb.*, m.full_name, t.trainer_name
    FROM session_bookings sb
    JOIN members m ON sb.member_id = m.member_id
    JOIN trainers t ON sb.trainer_id = t.trainer_id
    ORDER BY sb.created_at DESC
    LIMIT 5
");
$recentBookings = $result->fetch_all(MYSQLI_ASSOC);

require_once '../header.php';
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
    <?php include 'sidebar.php'; ?>
    <div class="admin-content">
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        </div>

        <!-- Membership Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <h3><?php echo $totalMembers; ?></h3>
                    <p>Total Members</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-user-check"></i></div>
                <div class="stat-info">
                    <h3><?php echo $activeMembers; ?></h3>
                    <p>Active Memberships</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-user-times"></i></div>
                <div class="stat-info">
                    <h3><?php echo $expiredMembers; ?></h3>
                    <p>Expired Memberships</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-money-bill-wave"></i></div>
                <div class="stat-info">
                    <h3>RM <?php echo number_format($monthlyIncome, 2); ?></h3>
                    <p>Monthly Income</p>
                </div>
            </div>
        </div>

        <!-- PT Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-dumbbell"></i></div>
                <div class="stat-info">
                    <h3><?php echo $totalPTSessions; ?></h3>
                    <p>Total PT Sessions</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-info">
                    <h3><?php echo $activeBookings; ?></h3>
                    <p>Active Bookings</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-coins"></i></div>
                <div class="stat-info">
                    <h3>RM <?php echo number_format($monthlyPTRevenue, 2); ?></h3>
                    <p>Monthly PT Revenue</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-user-tie"></i></div>
                <div class="stat-info">
                    <h3><?php echo $availableTrainers; ?></h3>
                    <p>Available Trainers</p>
                </div>
            </div>
        </div>

        <!-- Recent Members -->
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card-header">
                <h3>Recent Members</h3>
                <a href="members.php" class="btn btn-sm btn-secondary">View All</a>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Package</th><th>Status</th><th>Joined</th></tr></thead>
                    <tbody>
                        <?php if (empty($recentMembers)): ?>
                            <tr><td colspan="6" style="text-align:center;color:var(--text-muted);">No members yet.</td></tr>
                        <?php else: foreach ($recentMembers as $m): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($m['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($m['email']); ?></td>
                                <td><?php echo htmlspecialchars($m['phone']); ?></td>
                                <td><?php echo htmlspecialchars($m['package_name'] ?? 'None'); ?></td>
                                <td><span class="badge <?php echo $m['status'] === 'active' ? 'badge-success' : 'badge-danger'; ?>"><?php echo ucfirst($m['status']); ?></span></td>
                                <td><?php echo $m['join_date']; ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="card">
            <div class="card-header">
                <h3>Recent PT Bookings</h3>
                <a href="bookings.php" class="btn btn-sm btn-secondary">View All</a>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Member</th><th>Trainer</th><th>Date</th><th>Time</th><th>Type</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php if (empty($recentBookings)): ?>
                            <tr><td colspan="6" style="text-align:center;color:var(--text-muted);">No bookings yet.</td></tr>
                        <?php else: foreach ($recentBookings as $b): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($b['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($b['trainer_name']); ?></td>
                                <td><?php echo $b['session_date']; ?></td>
                                <td><?php echo $b['session_time']; ?></td>
                                <td><?php echo $b['session_type']; ?></td>
                                <td><span class="badge <?php
                                    echo match($b['booking_status']) {
                                        'Approved' => 'badge-success',
                                        'Pending' => 'badge-warning',
                                        'Completed' => 'badge-info',
                                        default => 'badge-danger'
                                    }; ?>"><?php echo $b['booking_status']; ?></span></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../footer.php'; ?>