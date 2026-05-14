<?php
require_once '../auth.php';       
requireMember();
require_once '../connectdb.php'; 
$pageTitle = 'Personal Trainers';

// Fetch all trainers ordered by name
$query = "SELECT * FROM trainers ORDER BY trainer_name";
$trainers = $conn->query($query)->fetchAll();

require_once '../header.php';
?>

<div class="container fade-in">
    <div class="page-header">
        <h1><i class="fas fa-user-tie"></i> Our Personal Trainers</h1>
        <p>Meet our certified trainers and book a session</p>
    </div>

    <div class="features-grid">
        <?php foreach ($trainers as $t): ?>
            <?php 
                $isAvailable = ($t['status'] === 'Available');
                $statusClass = $isAvailable ? 'badge-success' : 'badge-warning';
            ?>
            <div class="card trainer-card">
                <!-- Header: Avatar & Title -->
                <div style="text-align: center; margin-bottom: 1rem;">
                    <div style="width: 70px; height: 70px; border-radius: 50%; background: var(--gradient); display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem; font-size: 1.5rem; color: #fff;">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h3><?= htmlspecialchars($t['trainer_name']) ?></h3>
                    <span class="badge badge-info"><?= htmlspecialchars($t['specialization']) ?></span>
                </div>

                <!-- Body: Trainer Details -->
                <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 1rem;">
                    <p style="margin-bottom: 0.4rem;">
                        <i class="fas fa-calendar-day" style="width: 20px; color: var(--accent);"></i> 
                        <?= htmlspecialchars($t['available_days']) ?>
                    </p>
                    <p style="margin-bottom: 0.4rem;">
                        <i class="fas fa-clock" style="width: 20px; color: var(--accent);"></i> 
                        <?= htmlspecialchars($t['available_time']) ?>
                    </p>
                    <p style="margin-bottom: 0.4rem;">
                        <i class="fas fa-phone" style="width: 20px; color: var(--accent);"></i> 
                        <?= htmlspecialchars($t['contact_number']) ?>
                    </p>
                    <p>
                        <i class="fas fa-tag" style="width: 20px; color: var(--accent);"></i> 
                        RM <?= number_format($t['session_fee'], 2) ?> / session
                    </p>
                </div>

                <!-- Footer: Status & Booking Action -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
                    <span class="badge <?= $statusClass ?>">
                        <?= htmlspecialchars($t['status']) ?>
                    </span>
                    
                    <?php if ($isAvailable): ?>
                        <a href="book_session.php?trainer=<?= $t['trainer_id'] ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-calendar-plus"></i> Book
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

