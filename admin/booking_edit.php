<?php
require_once '../auth.php';
requireAdmin();
require_once '../connectdb.php';

$pageTitle = 'Edit Booking';

// 1. Validate ID and Fetch Existing Data
$bookingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? 0;

$stmt = $conn->prepare("SELECT * FROM session_bookings WHERE booking_id = ?");
$stmt->bind_param("i", $bookingId);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    header("Location: bookings.php");
    exit();
}

// 2. Fetch Dropdown Data
$members = $conn->query("SELECT member_id, full_name FROM members ORDER BY full_name")->fetchAll();
$trainers = $conn->query("SELECT trainer_id, trainer_name FROM trainers ORDER BY trainer_name")->fetchAll();
$errors = [];

// 3. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberId  = filter_input(INPUT_POST, 'member_id', FILTER_VALIDATE_INT);
    $trainerId = filter_input(INPUT_POST, 'trainer_id', FILTER_VALIDATE_INT);
    $date      = $_POST['session_date'] ?? '';
    $time      = $_POST['session_time'] ?? '';
    $type      = $_POST['session_type'] ?? '';
    $status    = $_POST['booking_status'] ?? '';
    $notes     = trim($_POST['notes'] ?? '');

    if (!$memberId || !$trainerId || !$date || !$time) {
        $errors[] = 'Please fill in all required fields marked with (*).';
    } else {
        $updateSql = "UPDATE session_bookings 
                      SET member_id=?, trainer_id=?, session_date=?, session_time=?, session_type=?, booking_status=?, notes=? 
                      WHERE booking_id=?";
        $updateStmt = $conn->prepare($updateSql);
        
        if ($updateStmt->execute([$memberId, $trainerId, $date, $time, $type, $status, $notes, $bookingId])) {
            header("Location: bookings.php?msg=" . urlencode("Booking updated successfully"));
            exit();
        } else {
            $errors[] = "Database error: Could not update booking.";
        }
    }
}

require_once '../includes/header.php'; // Adjusted based on your likely header path
?>

<div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="page-header">
            <h1><i class="fas fa-edit"></i> Edit Booking</h1>
            <p>Modify session details for this appointment</p>
        </div>

        <div class="card" style="max-width:650px;">
            <?php foreach ($errors as $err): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($err); ?>
                </div>
            <?php endforeach; ?>

            <form method="POST" class="fade-in">
                <div class="form-grid">
                    <!-- Member Selection -->
                    <div class="form-group">
                        <label for="member_id">Member *</label>
                        <select id="member_id" name="member_id" required>
                            <?php foreach ($members as $m): ?>
                                <option value="<?php echo $m['member_id']; ?>" <?php echo ($booking['member_id'] == $m['member_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($m['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Trainer Selection -->
                    <div class="form-group">
                        <label for="trainer_id">Trainer *</label>
                        <select id="trainer_id" name="trainer_id" required>
                            <?php foreach ($trainers as $t): ?>
                                <option value="<?php echo $t['trainer_id']; ?>" <?php echo ($booking['trainer_id'] == $t['trainer_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($t['trainer_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Date -->
                    <div class="form-group">
                        <label for="session_date">Session Date *</label>
                        <input type="date" id="session_date" name="session_date" required value="<?php echo $booking['session_date']; ?>">
                    </div>

                    <!-- Time Slot -->
                    <div class="form-group">
                        <label for="session_time">Time Slot *</label>
                        <select id="session_time" name="session_time" required>
                            <?php 
                            $slots = ['8:00 AM','9:00 AM','10:00 AM','11:00 AM','12:00 PM','1:00 PM','2:00 PM','3:00 PM','4:00 PM','5:00 PM','6:00 PM','7:00 PM','8:00 PM'];
                            foreach ($slots as $ts): ?>
                                <option value="<?php echo $ts; ?>" <?php echo ($booking['session_time'] === $ts) ? 'selected' : ''; ?>>
                                    <?php echo $ts; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Session Type -->
                    <div class="form-group">
                        <label for="session_type">Session Type *</label>
                        <select id="session_type" name="session_type" required>
                            <?php 
                            $types = ['Strength','Cardio','Weight Loss','Rehab'];
                            foreach ($types as $st): ?>
                                <option value="<?php echo $st; ?>" <?php echo ($booking['session_type'] === $st) ? 'selected' : ''; ?>>
                                    <?php echo $st; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Booking Status -->
                    <div class="form-group">
                        <label for="booking_status">Status *</label>
                        <select id="booking_status" name="booking_status" required>
                            <?php 
                            $statuses = ['Pending','Approved','Rejected','Cancelled','Completed'];
                            foreach ($statuses as $bs): ?>
                                <option value="<?php echo $bs; ?>" <?php echo ($booking['booking_status'] === $bs) ? 'selected' : ''; ?>>
                                    <?php echo $bs; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Notes -->
                    <div class="form-group full-width">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" rows="3"><?php echo htmlspecialchars($booking['notes'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="btn-group" style="margin-top:1.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="bookings.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>