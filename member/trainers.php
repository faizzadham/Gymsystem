<?php
require_once '../auth.php';       
requireMember();
require_once '../connectdb.php'; 
$pageTitle = 'Personal Trainers';

// Base layout variables parsed by your root header.php
$baseUrl = '/Gymsystem'; 
$pageStyles = [
    'login.css?v=2.0',          /* Outlines the structural alignment of header & footer */
    'member/trainers.css?v=1.2' /* Outlines your core personal trainer layout cards grid */
];

// Fetch all trainers ordered by name
$query = "SELECT * FROM trainers ORDER BY trainer_name";
$result = $conn->query($query);
$trainers = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

require_once '../header.php';
?>

<div class="container fade-in">
    <div class="page-header">
        <h1>
            <i class="fas fa-user-tie"></i>
            Our Personal Trainers
        </h1>
        <p>
            Meet our certified trainers and book a session
        </p>
    </div>

    <div class="trainer-grid">

        <?php foreach ($trainers as $t): ?>

            <?php
                $isAvailable = ($t['status'] === 'Available');

                $statusClass = $isAvailable
                    ? 'badge-success'
                    : 'badge-warning';
            ?>

            <div class="trainer-card">

                <div class="avatar-wrapper">
                    <i class="fas fa-user-tie"></i>
                </div>

                <div class="trainer-header">
                    <h3>
                        <?= htmlspecialchars($t['trainer_name']) ?>
                    </h3>

                    <span class="badge-info">
                        <?= htmlspecialchars($t['specialization']) ?>
                    </span>
                </div>

                <div class="trainer-meta-list">

                    <div class="trainer-meta-item">
                        <i class="fas fa-calendar-day"></i>

                        <span>
                            <?= htmlspecialchars($t['available_days']) ?>
                        </span>
                    </div>

                    <div class="trainer-meta-item">
                        <i class="fas fa-clock"></i>

                        <span>
                            <?= htmlspecialchars($t['available_time']) ?>
                        </span>
                    </div>

                    <div class="trainer-meta-item">
                        <i class="fas fa-phone"></i>

                        <span>
                            <?= htmlspecialchars($t['contact_number']) ?>
                        </span>
                    </div>

                    <div class="trainer-meta-item fee-highlight">
                        <i class="fas fa-tag"></i>

                        <span>
                            RM <?= number_format($t['session_fee'], 2) ?>

                            <span class="fee-period">
                                / session
                            </span>
                        </span>
                    </div>
                </div>

                <div class="trainer-card-footer">

                    <span class="<?= $statusClass ?>">
                        <?= htmlspecialchars($t['status']) ?>
                    </span>

                    <?php if ($isAvailable): ?>
                        <a
                            href="booking_session.php?trainer=<?= $t['trainer_id'] ?>"
                            class="btn-book-session">
                            <i class="fas fa-calendar-plus"></i>
                            Book Session
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php 
require_once '../footer.php'; 
?>