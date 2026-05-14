<?php
require_once '../auth.php';       
requireMember();
require_once '../connectdb.php'; 

$pageTitle = 'My Timetable';
$userId = $_SESSION['user_id'];

// 1. Fetch Member ID
$stmt = $conn->prepare("SELECT member_id FROM members WHERE user_id = ?");
$stmt->execute([$userId]);
$member = $stmt->fetch();
$memberId = $member['member_id'];

// 2. Handle Cancellation
if (isset($_GET['cancel'])) {
    $cancelId = (int)$_GET['cancel'];
    $cancelQuery = "UPDATE session_bookings SET booking_status = 'Cancelled' 
                    WHERE booking_id = ? AND member_id = ? 
                    AND booking_status IN ('Pending','Approved')";
    
    $conn->prepare($cancelQuery)->execute([$cancelId, $memberId]);
    header("Location: timetable.php?msg=" . urlencode("Session cancelled successfully"));
    exit();
}

// 3. Define Shared Query Logic
$baseSql = "SELECT sb.*, t.trainer_name, t.session_fee 
            FROM session_bookings sb 
            JOIN trainers t ON sb.trainer_id = t.trainer_id 
            WHERE sb.member_id = ?";

// Fetch Upcoming (Today onwards)
$upcomingQuery = "$baseSql AND sb.session_date >= CURDATE() 
                  AND sb.booking_status IN ('Pending','Approved') 
                  ORDER BY sb.session_date ASC, sb.session_time ASC";
$upcomingStmt = $conn->prepare($upcomingQuery);
$upcomingStmt->execute([$memberId]);
$upcomingSessions = $upcomingStmt->fetchAll();

// Fetch History (Past dates or finalized statuses)
$historyQuery = "$baseSql AND (sb.session_date < CURDATE() 
                 OR sb.booking_status IN ('Completed','Cancelled','Rejected')) 
                 ORDER BY sb.session_date DESC, sb.session_time DESC";
$historyStmt = $conn->prepare($historyQuery);
$historyStmt->execute([$memberId]);
$pastSessions = $historyStmt->fetchAll();

/**
 * Helper: Map status to CSS badge classes
 */
function getBadgeClass($status) {
    return match($status) {
        'Completed' => 'badge-info',
        'Approved'  => 'badge-success',
        'Cancelled', 'Rejected' => 'badge-danger',
        default     => 'badge-warning',
    };
}

require_once '../header.php';
?>

<div class="container fade-in">
    <div class="page-header">
        <h1><i class="fas fa-calendar-alt"></i> My Timetable</h1>
        <p>View your upcoming and past training sessions</p>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['msg']) ?>
        </div>
    <?php endif; ?>

    <div style="margin-bottom: 1.5rem; display: flex; gap: 0.5rem;">
        <a href="book_session.php" class="btn btn-primary"><i class="fas fa-plus"></i> Book New Session</a>
        <a href="trainers.php" class="btn btn-secondary"><i class="fas fa-user-tie"></i> View Trainers</a>
    </div>

    <!-- Upcoming Sessions Section -->
    <section class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3><i class="fas fa-clock" style="color:var(--accent);"></i> Upcoming Sessions</h3>
        </div>
        
        <?php if (empty($upcomingSessions)): ?>
            <p style="text-align:center; color:var(--text-muted); padding:2rem;">
                No upcoming sessions. <a href="book_session.php" style="color:var(--accent);">Book one now!</a>
            </p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th><th>Time</th><th>Trainer</th><th>Type</th><th>Fee</th><th>Status</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcomingSessions as $s): ?>
                            <tr>
                                <td><?= $s['session_date'] ?></td>
                                <td><?= $s['session_time'] ?></td>
                                <td><?= htmlspecialchars($s['trainer_name']) ?></td>
                                <td><?= htmlspecialchars($s['session_type']) ?></td>
                                <td>RM <?= number_format($s['session_fee'], 2) ?></td>
                                <td><span class="badge <?= getBadgeClass($s['booking_status']) ?>"><?= $s['booking_status'] ?></span></td>
                                <td>
                                    <a href="timetable.php?cancel=<?= $s['booking_id'] ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to cancel this session?');">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <!-- Session History Section -->
    <section class="card">
        <div class="card-header">
            <h3><i class="fas fa-history" style="color:var(--text-muted);"></i> Session History</h3>
        </div>
        
        <?php if (empty($pastSessions)): ?>
            <p style="text-align:center; color:var(--text-muted); padding:2rem;">No past records found.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th><th>Time</th><th>Trainer</th><th>Type</th><th>Fee</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pastSessions as $s): ?>
                            <tr>
                                <td><?= $s['session_date'] ?></td>
                                <td><?= $s['session_time'] ?></td>
                                <td><?= htmlspecialchars($s['trainer_name']) ?></td>
                                <td><?= htmlspecialchars($s['session_type']) ?></td>
                                <td>RM <?= number_format($s['session_fee'], 2) ?></td>
                                <td><span class="badge <?= getBadgeClass($s['booking_status']) ?>"><?= $s['booking_status'] ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>

