<?php
require_once '../auth.php';
requireMember();
require_once '../connectdb.php';

$pageTitle = 'Book Session';
$userId    = $_SESSION['user_id'];
$errors    = [];
$success   = '';

// 1. Fetch Member Data
$stmt = $conn->prepare("SELECT member_id, full_name FROM members WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$memberResult = $stmt->get_result();
$member = $memberResult ? $memberResult->fetch_assoc() : null;
$stmt->close();

// 2. Fetch Available Trainers
$trainerResult = $conn->query("SELECT * FROM trainers WHERE status = 'Available' ORDER BY trainer_name");
$trainers = $trainerResult ? $trainerResult->fetch_all(MYSQLI_ASSOC) : [];
$preselect = $_GET['trainer'] ?? '';

// 3. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trainerId = (int)($_POST['trainer_id'] ?? 0);
    $date      = $_POST['session_date'] ?? '';
    $time      = $_POST['session_time'] ?? '';
    $type      = $_POST['session_type'] ?? 'Strength';
    $notes     = trim($_POST['notes'] ?? '');

    if (!$trainerId || !$date || !$time) {
        $errors[] = 'Please fill in all required fields.';
    } elseif ($date < date('Y-m-d')) {
        $errors[] = 'Session date cannot be in the past.';
    } else {
        $check = $conn->prepare("SELECT COUNT(*) FROM session_bookings WHERE trainer_id = ? AND session_date = ? AND session_time = ? AND booking_status IN ('Pending','Approved')");
        $check->execute([$trainerId, $date, $time]);

        if ($check->fetchColumn() > 0) {
            $errors[] = 'This trainer is already booked for that slot.';
        } else {
            $insert = $conn->prepare("INSERT INTO session_bookings (member_id, trainer_id, session_date, session_time, session_type, booking_status, notes) VALUES (?, ?, ?, ?, ?, 'Pending', ?)");
            $insert->execute([$member['member_id'], $trainerId, $date, $time, $type, $notes]);
            $success = 'Session booked successfully!';
        }
    }
}

require_once '../header.php';
?>

<div class="container fade-in">
    <div class="page-header">
        <h1><i class="fas fa-calendar-plus"></i> Book a Session</h1>
    </div>

    <div class="card" style="max-width:600px;">
        <?php 
        if ($success) { 
        ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <div style="text-align:center;margin-top:1rem;">
                <a href="timetable.php" class="btn btn-primary">View Timetable</a>
                <a href="book_session.php" class="btn btn-secondary">Book Another</a>
            </div>
        <?php 
        } else { 
            foreach ($errors as $err) {
                echo '<div class="alert alert-danger">' . htmlspecialchars($err) . '</div>';
            }
        ?>
            <form method="POST">
                <div class="form-group">
                    <label>Member Name</label>
                    <input type="text" value="<?php echo htmlspecialchars($member['full_name']); ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="trainer_id">Select Trainer *</label>
                    <select id="trainer_id" name="trainer_id" required>
                        <option value="">-- Choose Trainer --</option>
                        <?php foreach ($trainers as $t) { 
                            $selected = ($preselect == $t['trainer_id']) ? 'selected' : '';
                            echo "<option value='{$t['trainer_id']}' {$selected}>" . htmlspecialchars($t['trainer_name']) . " (" . $t['specialization'] . ")</option>";
                        } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="session_date">Session Date *</label>
                    <input type="date" id="session_date" name="session_date" required min="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label for="session_time">Time Slot *</label>
                    <select id="session_time" name="session_time" required>
                        <option value="">-- Choose Time --</option>
                        <?php 
                        $slots = ['8:00 AM','9:00 AM','10:00 AM','11:00 AM','12:00 PM','1:00 PM','2:00 PM','3:00 PM','4:00 PM','5:00 PM','6:00 PM','7:00 PM','8:00 PM'];
                        foreach ($slots as $s) {
                            echo "<option value='$s'>$s</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="session_type">Type</label>
                    <select name="session_type">
                        <option value="Strength">Strength</option>
                        <option value="Cardio">Cardio</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea name="notes"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;">Submit Booking</button>
            </form>
        <?php } ?>
    </div>
</div>

<?php require_once '../footer.php'; ?>