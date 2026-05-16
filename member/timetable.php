<?php
require_once '../auth.php';       
requireMember();
require_once '../connectdb.php'; 

$pageTitle = 'My Timetable';

$stmt = $conn->prepare("SELECT member_id FROM members WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$member = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$member) {
    die("Member not found.");
}

// Handle cancel
if (isset($_GET['cancel'])) {
    $cancelId = (int)$_GET['cancel'];
    $cancelStmt = $conn->prepare("UPDATE session_bookings SET booking_status = 'Cancelled' WHERE booking_id = ? AND member_id = ? AND booking_status IN ('Pending','Approved')");
    $cancelStmt->bind_param("ii", $cancelId, $member['member_id']);
    $cancelStmt->execute();
    $cancelStmt->close();
    header("Location: timetable.php?msg=Session cancelled successfully");
    exit();
}

// Get current week offset
$weekOffset = isset($_GET['week']) ? (int)$_GET['week'] : 0;

// Calculate the start of the week (Monday)
$today = new DateTime();
$today->modify("{$weekOffset} week");
$dayOfWeek = (int)$today->format('N'); // 1=Mon, 7=Sun
$today->modify('-' . ($dayOfWeek - 1) . ' days');
$weekStart = clone $today;
$weekEnd = clone $today;
$weekEnd->modify('+6 days');

$startStr = $weekStart->format('Y-m-d');
$endStr = $weekEnd->format('Y-m-d');

// Fetch sessions for this week
$weekSessions = $conn->prepare("SELECT sb.*, t.trainer_name, t.session_fee, t.specialization 
    FROM session_bookings sb 
    JOIN trainers t ON sb.trainer_id = t.trainer_id 
    WHERE sb.member_id = ? 
    AND sb.session_date BETWEEN ? AND ? 
    AND sb.booking_status IN ('Pending','Approved') 
    ORDER BY sb.session_date ASC, sb.session_time ASC");
$weekSessions->bind_param("iss", $member['member_id'], $startStr, $endStr);
$weekSessions->execute();
$sessResult = $weekSessions->get_result();
$sessions = $sessResult ? $sessResult->fetch_all(MYSQLI_ASSOC) : [];
$weekSessions->close();

// Build lookup: date => time => session
$sessionMap = [];
foreach ($sessions as $s) {
    $sessionMap[$s['session_date']][$s['session_time']] = $s;
}

// Get all unique trainer names for search autocomplete
$allTrainers = $conn->prepare("SELECT DISTINCT t.trainer_name FROM session_bookings sb JOIN trainers t ON sb.trainer_id = t.trainer_id WHERE sb.member_id = ? AND sb.booking_status IN ('Pending','Approved')");
$allTrainers->bind_param("i", $member['member_id']);
$allTrainers->execute();
$trainerResult = $allTrainers->get_result();
$trainerNames = $trainerResult ? $trainerResult->fetch_all(MYSQLI_NUM) : [];
$trainerNames = array_column($trainerNames, 0); // Extract just the trainer names
$allTrainers->close();

// Time slots
$timeSlots = ['8:00 AM','9:00 AM','10:00 AM','11:00 AM','12:00 PM','1:00 PM','2:00 PM','3:00 PM','4:00 PM','5:00 PM','6:00 PM','7:00 PM','8:00 PM'];

// Days array
$days = [];
for ($i = 0; $i < 7; $i++) {
    $d = clone $weekStart;
    $d->modify("+{$i} days");
    $days[] = $d;
}

// Past / history
$history = $conn->prepare("SELECT sb.*, t.trainer_name, t.session_fee FROM session_bookings sb JOIN trainers t ON sb.trainer_id = t.trainer_id WHERE sb.member_id = ? AND (sb.session_date < CURDATE() OR sb.booking_status IN ('Completed','Cancelled','Rejected')) ORDER BY sb.session_date DESC LIMIT 20");
$history->bind_param("i", $member['member_id']);
$history->execute();
$historyResult = $history->get_result();
$pastSessions = $historyResult ? $historyResult->fetch_all(MYSQLI_ASSOC) : [];
$history->close();

require_once '../header.php';
?>

<link rel="stylesheet" href="timetable.css">


<div class="container fade-in">
    <div class="page-header">
        <h1><i class="fas fa-calendar-alt"></i> My Timetable</h1>
        <p>View your booked trainers in a weekly calendar view</p>
    </div>

    <div class="action-buttons-row">
        <a href="booking_session.php" class="btn-fit-gradient"><i class="fas fa-plus"></i> Book New Session</a>
        <a href="trainers.php" class="btn-fit-dark"><i class="fas fa-user-tie"></i> View Trainers</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>

    <!-- Search & Week Navigation -->
    <div class="timetable-controls">
        <div class="week-nav">
            <a href="timetable.php?week=<?php echo $weekOffset - 1; ?>" class="btn-week" title="Previous Week">
                <i class="fas fa-chevron-left"></i>
            </a>
            <?php if ($weekOffset !== 0): ?>
                <a href="timetable.php?week=0" class="btn-today">Today</a>
            <?php endif; ?>
            <span class="week-label">
                <?php echo $weekStart->format('M d'); ?> — <?php echo $weekEnd->format('M d, Y'); ?>
            </span>
            <a href="timetable.php?week=<?php echo $weekOffset + 1; ?>" class="btn-week" title="Next Week">
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>
        <div class="trainer-search-box">
            <input type="text" id="trainerSearch" placeholder="Search booked trainer..." autocomplete="off">
            <i class="fas fa-search search-icon"></i>
        </div>
    </div>

    <div id="searchResultsCount" class="search-results-count"></div>

    <!-- Legend -->
    <div class="calendar-legend">
        <div class="legend-item"><span class="legend-dot approved"></span> Approved</div>
        <div class="legend-item"><span class="legend-dot pending"></span> Pending</div>
        <div class="legend-item"><span class="legend-dot highlighted"></span> Search Match</div>
    </div>

    <!-- Calendar Timetable -->
    <div class="calendar-wrapper">
        <table class="calendar-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <?php
                    $todayStr = (new DateTime())->format('Y-m-d');
                    $dayNames = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
                    foreach ($days as $idx => $day):
                        $isToday = $day->format('Y-m-d') === $todayStr;
                    ?>
                    <th class="<?php echo $isToday ? 'today-col' : ''; ?>">
                        <span class="day-name"><?php echo $dayNames[$idx]; ?></span>
                        <span class="day-date"><?php echo $day->format('d'); ?></span>
                    </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($timeSlots as $time): ?>
                <tr>
                    <td><?php echo $time; ?></td>
                    <?php foreach ($days as $day):
                        $dateStr = $day->format('Y-m-d');
                        $isToday = $dateStr === $todayStr;
                        $session = $sessionMap[$dateStr][$time] ?? null;
                    ?>
                    <td class="<?php echo $isToday ? 'today-col' : ''; ?>">
                        <?php if ($session): ?>
                        <div class="session-cell <?php echo $session['booking_status'] === 'Pending' ? 'status-pending' : ''; ?>"
                             data-trainer="<?php echo htmlspecialchars(strtolower($session['trainer_name'])); ?>"
                             title="<?php echo htmlspecialchars($session['trainer_name']); ?> — <?php echo $session['session_type']; ?> (<?php echo $session['booking_status']; ?>)&#10;Fee: RM <?php echo number_format($session['session_fee'], 2); ?>">
                            <div class="session-actions">
                                <a href="timetable.php?cancel=<?php echo $session['booking_id']; ?>&week=<?php echo $weekOffset; ?>" 
                                   class="cancel-btn" 
                                   onclick="return confirm('Cancel this session with <?php echo htmlspecialchars($session['trainer_name']); ?>?');"
                                   title="Cancel Session">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                            <span class="session-trainer"><?php echo htmlspecialchars($session['trainer_name']); ?></span>
                            <span class="session-type"><?php echo $session['session_type']; ?></span>
                            <span class="session-status <?php echo strtolower($session['booking_status']); ?>">
                                <?php echo $session['booking_status']; ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (empty($sessions)): ?>
    <div class="calendar-empty-week">
        <i class="fas fa-calendar-times"></i>
        No sessions booked for this week. <a href="booking_session.php" style="color:var(--accent);">Book one now!</a>
    </div>
    <?php endif; ?>

    <!-- Session History -->
    <div class="card" style="margin-top:1rem;">
        <div class="card-header">
            <h3><i class="fas fa-history" style="color:var(--text-muted);"></i> Session History</h3>
        </div>
        <?php if (empty($pastSessions)): ?>
            <p style="text-align:center;color:var(--text-muted);padding:1rem;">No past sessions.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Date</th><th>Time</th><th>Trainer</th><th>Type</th><th>Fee</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($pastSessions as $s): ?>
                        <tr>
                            <td><?php echo $s['session_date']; ?></td>
                            <td><?php echo $s['session_time']; ?></td>
                            <td><?php echo htmlspecialchars($s['trainer_name']); ?></td>
                            <td><?php echo $s['session_type']; ?></td>
                            <td>RM <?php echo number_format($s['session_fee'], 2); ?></td>
                            <td><span class="badge <?php
                                echo match($s['booking_status']) {
                                    'Completed' => 'badge-info',
                                    'Approved' => 'badge-success',
                                    'Cancelled' => 'badge-danger',
                                    'Rejected' => 'badge-danger',
                                    default => 'badge-warning'
                                }; ?>"><?php echo $s['booking_status']; ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Trainer search & highlight
(function() {
    const searchInput = document.getElementById('trainerSearch');
    const resultsBadge = document.getElementById('searchResultsCount');
    const sessionCells = document.querySelectorAll('.session-cell');

    searchInput.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        let matchCount = 0;

        sessionCells.forEach(cell => {
            cell.classList.remove('highlighted');

            if (query.length > 0) {
                const trainerName = cell.getAttribute('data-trainer') || '';
                if (trainerName.includes(query)) {
                    cell.classList.add('highlighted');
                    matchCount++;
                }
            }
        });

        if (query.length > 0) {
            resultsBadge.innerHTML = '<i class="fas fa-search"></i> ' + matchCount + ' session' + (matchCount !== 1 ? 's' : '') + ' found for "' + this.value.trim() + '"';
            resultsBadge.classList.add('visible');

            // Scroll to first highlighted session
            const firstMatch = document.querySelector('.session-cell.highlighted');
            if (firstMatch) {
                firstMatch.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
            }
        } else {
            resultsBadge.classList.remove('visible');
        }
    });
})();
</script>

<?php require_once '../footer.php'; ?>

<div class="card-wrapper-layout">
        
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success" style="background: var(--status-approved-blue-bg); color: var(--status-approved-blue); padding: 1rem; border-radius: var(--border-radius-inner); font-weight: 600;"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>

        <div class="timetable-actions-row">
            <a href="booking_session.php" class="btn-primary" style="text-decoration:none;"><i class="fas fa-plus"></i> Book New Session</a>
            <a href="trainers.php" class="btn-primary" style="background: #ffffff !important; border: 1px solid var(--border-light) !important; color: var(--text-secondary) !important; text-decoration:none;"><i class="fas fa-user-tie"></i> View Trainers</a>
        </div>

        <div class="timetable-controls">
            <div class="week-nav">
                <a href="timetable.php?week=<?php echo $weekOffset - 1; ?>" class="btn-week" title="Previous Week">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <?php if ($weekOffset !== 0): ?>
                    <a href="timetable.php?week=0" class="btn-today">Today</a>
                <?php endif; ?>
                <span class="week-label">
                    <?php echo $weekStart->format('M d'); ?> — <?php echo $weekEnd->format('M d, Y'); ?>
                </span>
                <a href="timetable.php?week=<?php echo $weekOffset + 1; ?>" class="btn-week" title="Next Week">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
            <div class="trainer-search-box">
                <input type="text" id="trainerSearch" placeholder="Search booked trainer..." autocomplete="off">
                <i class="fas fa-search search-icon"></i>
            </div>
        </div>

        <div id="searchResultsCount" class="search-results-count"></div>

        <div class="calendar-legend">
            <div class="legend-item"><span class="legend-dot approved"></span> Approved</div>
            <div class="legend-item"><span class="legend-dot pending"></span> Pending</div>
            <div class="legend-item"><span class="legend-dot highlighted"></span> Search Match</div>
        </div>

        <div class="calendar-wrapper">
            <table class="calendar-table">
               </table>
        </div>

        <?php if (empty($sessions)): ?>
        <div class="calendar-empty-week">
            <i class="fas fa-calendar-times"></i>
            No sessions booked for this week. <a href="booking_session.php" style="color:var(--brand-blue);">Book one now!</a>
        </div>
        <?php endif; ?>

        <div class="card">
            <h3><i class="fas fa-history" style="color:var(--text-muted);"></i> Session History</h3>
            <?php if (empty($pastSessions)): ?>
                <p style="text-align:center;color:var(--text-secondary);margin:0;padding:1rem 0;">No past sessions found.</p>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        </table>
                </div>
            <?php endif; ?>
        </div>

       
    </div> 