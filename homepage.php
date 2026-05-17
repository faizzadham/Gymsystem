<?php
require_once 'auth.php';

$pageTitle = 'Home';
$pageStyles = ['homepage.css'];
require_once 'header.php';
?>

<section class="hero">
    <div class="hero-content">
        <h1>Transform Your Body, Transform Your Life</h1>
        <p>Join FitZone Gym and start your fitness journey today. Professional equipment, expert trainers, and a supportive community await you.</p>
        <div class="hero-buttons">
            <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Get Started</a>
                <a href="login.php" class="btn btn-secondary"><i class="fas fa-sign-in-alt"></i> Member Login</a>
            <?php else: ?>
                <?php if (getUserRole() === 'admin'): ?>
                    <a href="admin/dashboard.php" class="btn btn-primary"><i class="fas fa-tachometer-alt"></i> Go to Dashboard</a>
                <?php else: ?>
                    <a href="member/dashboard.php" class="btn btn-primary"><i class="fas fa-tachometer-alt"></i> My Dashboard</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>

