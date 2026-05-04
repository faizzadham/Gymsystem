<?php
include("connectdb.php");
include("auth.php");
?>

<section class="hero">
    <div class="hero-content">
        <h1>Tranform Your Body, Transform Your life</h1>
        <p> Join FitZone Gym and start your fitness journey today. Professional equipment, expert trainers, and a supportive community await you. </P>
        <div class="hero-buttons">
            <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i>
                Get Started</a>
                 <a href="login.php" class="btn btn-secondary"><i class="fas fa-sign-in alt"></i>Member Login</a>
                 <?php else:
                 if (getUserRole() === 'admin'): ?>
                 <a href="admin/dashboard.php" class="btn btn-primary"><i class="fas fa-tachometer-alt"></i>Go to Dashboard</a>
                 <?php else: ?>
                 <a href="member/dashboard.php" class="btn btn-primary"><i class="fas fa-tachmometer-alt"><i> My Dashboard</a>
                 <?php endif; ?>
                 <?php endif; ?>
        </div>
                 </div>
                 </section>

