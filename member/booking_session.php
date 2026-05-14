<?php
require_once '../auth.php';
requireMember();
<<<<<<< HEAD
require_once '../connectdb.php'; // Updated to match admin's connection file
=======
require_once '../connectdb.php';
>>>>>>> main

$pageTitle = 'Book Session';

// 1. Fetch Member Details using the logged-in User ID
$stmt = $conn->prepare("SELECT member_id, full_name FROM members WHERE user_id = ?");
<<<<<<< HEAD
$stmt->execute([$_SESSION['user_id']]);
$member = $stmt->fetch();

// 2. Fetch Available Trainers
$trainersQuery = "SELECT * FROM trainers WHERE status = 'Available' ORDER BY trainer_name";
$trainers = $conn->query($trainersQuery)->fetchAll();
=======
$stmt->bind_param("i", $userId);
$stmt->execute();
$memberResult = $stmt->get_result();
$member = $memberResult ? $memberResult->fetch_assoc() : null;
$stmt->close();

// 2. Fetch Available Trainers
$trainerResult = $conn->query("SELECT * FROM trainers WHERE status = 'Available' ORDER BY trainer_name");
$trainers = $trainerResult ? $trainerResult->fetch_all(MYSQLI_ASSOC) : [];
$preselect = $_GET['trainer'] ?? '';
>>>>>>> main

$preselect = $_GET['trainer'] ?? '';
$errors = [];
$success = '';

// 3. Process Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trainerId = filter_input(INPUT_POST, 'trainer_id', FILTER_VALIDATE_INT);
    $date      = $_POST['session_date'] ?? '';
    $time      = $_POST['session_time'] ?? '';
    $type      = $_POST['session_type'] ?? 'Strength';
    $notes     = trim($_POST['notes'] ?? '');

    if (!$trainerId || !$date || !$time) {
        $errors[] = 'Please fill in all required fields marked with (*).';
    } elseif ($date < date('Y-m-d')) {
        $errors[] = 'Session date cannot be in the past.';
    } else {
        // Check for double booking (Prevent trainer from being booked twice for the same slot)
        $checkQuery = "SELECT COUNT(*) FROM session_bookings 
                       WHERE trainer_id = ? AND session_date = ? AND session_time = ? 
                       AND booking_status IN ('Pending','Approved')";
        $check = $conn->prepare($checkQuery);
        $check->execute([$trainerId, $date, $time]);
        
        if ($check->fetchColumn() > 0) {
            $errors[] = 'This trainer is already booked for that date and time. Please choose another slot.';
        } else {
            // Insert the new booking with 'Pending' status for Admin review
            $insertSql = "INSERT INTO session_bookings 
                          (member_id, trainer_id, session_date, session_time, session_type, booking_status, notes) 
                          VALUES (?, ?, ?, ?, ?, 'Pending', ?)";
            $insertStmt = $conn->prepare($insertSql);
            
            if ($insertStmt->execute([$member['member_id'], $trainerId, $date, $time, $type, $notes])) {
                $success = 'Session booked successfully! Please wait for admin approval.';
            } else {
                $errors[] = 'Database error: Unable to process booking.';
            }
        }
    }
}

require_once '../header.php';
?>

<main class="container fade-in">
    <div class="page-header">
        <h1><i class="fas fa-calendar-plus"></i> Book a Training Session</h1>
        <p>Schedule a personal training session with our expert trainers.</p>
    </div>

    <div class="card" style="max-width:650px; margin: 0 auto;">
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
            <div class="btn-group" style="justify-content:center; margin-top:1.5rem;">
                <a href="timetable.php" class="btn btn-primary">
                    <i class="fas fa-calendar-alt"></i> My Timetable
                </a>
                <a href="booking_session.php" class="btn btn-secondary">
                    <i class="fas fa-plus"></i> Book Another
                </a>
            </div>
        <?php else: ?>
            
            <?php foreach ($errors as $err): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($err); ?>
                </div>
            <?php endforeach; ?>

            <form method="POST" class="form-grid">
                <!-- Member Name (Read Only) -->
                <div class="form-group full-width">
                    <label>Booking For</label>
                    <input type="text" value="<?php echo htmlspecialchars($member['full_name']); ?>" disabled style="background:#eee; cursor:not-allowed;">
                </div>

                <!-- Trainer Selection -->
                <div class="form-group">
                    <label for="trainer_id">Select Trainer *</label>
                    <select id="trainer_id" name="trainer_id" required>
                        <option value="">-- Choose Trainer --</option>
                        <?php foreach ($trainers as $t): ?>
                            <option value="<?php echo $t['trainer_id']; ?>" <?php echo ($preselect == $t['trainer_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($t['trainer_name']); ?> (<?php echo $t['specialization']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Session Type -->
                <div class="form-group">
                    <label for="session_type">Session Type *</label>
                    <select id="session_type" name="session_type" required>
                        <option value="Strength">Strength Training</option>
                        <option value="Cardio">Cardiovascular</option>
                        <option value="Weight Loss">Weight Loss</option>
                        <option value="Rehab">Rehabilitation</option>
                    </select>
                </div>

                <!-- Date -->
                <div class="form-group">
                    <label for="session_date">Date *</label>
                    <input type="date" id="session_date" name="session_date" required min="<?php echo date('Y-m-d'); ?>">
                </div>

                <!-- Time -->
                <div class="form-group">
                    <label for="session_time">Time Slot *</label>
                    <select id="session_time" name="session_time" required>
                        <option value="">-- Select Time --</option>
                        <?php 
                        $slots = ['8:00 AM','9:00 AM','10:00 AM','11:00 AM','12:00 PM','1:00 PM','2:00 PM','3:00 PM','4:00 PM','5:00 PM','6:00 PM','7:00 PM','8:00 PM'];
                        foreach ($slots as $ts): ?>
                            <option value="<?php echo $ts; ?>"><?php echo $ts; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Notes -->
                <div class="form-group full-width">
                    <label for="notes">Additional Notes</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Health conditions or specific goals..."></textarea>
                </div>

                <div class="btn-group full-width" style="margin-top:1rem;">
                    <button type="submit" class="btn btn-primary" style="width:100%;">
                        <i class="fas fa-paper-plane"></i> Confirm Booking Request
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</main>

<?php require_once '../footer.php'; ?>