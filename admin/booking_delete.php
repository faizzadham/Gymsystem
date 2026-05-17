<?php
require_once '../auth.php';       // auth.php is in the parent folder
requireAdmin();
require_once '../connectdb.php';  // Changed from db.php to connectdb.php

$pageTitle = 'Confirm Deletion';
p
$bookingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$bookingId) {
    header("Location: bookings.php");
    exit();
}

// 2. Fetch booking details to show the confirmation message
$query = "SELECT sb.*, m.full_name, t.trainer_name 
          FROM session_bookings sb 
          JOIN members m ON sb.member_id = m.member_id 
          JOIN trainers t ON sb.trainer_id = t.trainer_id 
          WHERE sb.booking_id = ? 
          LIMIT 1";

$stmt = $conn->prepare($query);
$stmt->execute([$bookingId]);
$booking = $stmt->fetch();

// Redirect if the record doesn't exist
if (!$booking) {
    header("Location: bookings.php");
    exit();
}

// 3. Handle the actual deletion (POST request only)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deleteStmt = $conn->prepare("DELETE FROM session_bookings WHERE booking_id = ?");
    
    if ($deleteStmt->execute([$bookingId])) {
        $msg = urlencode("Booking deleted successfully");
        header("Location: bookings.php?msg=$msg");
    } else {
        header("Location: bookings.php?msg=Error+deleting+booking");
    }
    exit();
}

require_once '../includes/header.php';
?>

<main class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <section class="admin-content">
        <div class="confirm-box card fade-in">
            <header>
                <h2><i class="fas fa-trash-alt"></i> Delete Booking</h2>
            </header>
            
            <div class="confirm-body">
                <p>You are about to remove the booking for:</p>
                <p>
                    <strong>User:</strong> <?php echo htmlspecialchars($booking['full_name']); ?><br>
                    <strong>Trainer:</strong> <?php echo htmlspecialchars($booking['trainer_name']); ?><br>
                    <strong>Date:</strong> <?php echo date('M d, Y', strtotime($booking['session_date'])); ?>
                </p>
                <p class="alert-text"><em>This action cannot be undone.</em></p>
            </div>

            <form method="POST">
                <div class="btn-group" style="justify-content:center;">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-check"></i> Yes, Delete
                    </button>
                    <a href="bookings.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </section>
</main>

<?php require_once '../includes/footer.php'; ?>