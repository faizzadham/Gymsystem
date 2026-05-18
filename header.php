<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="FitZone Gym - Professional Gym Membership Management System">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | FitZone Gym' : 'FitZone Gym'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <?php if (!isset($baseUrl)) { $baseUrl = '/Gymsystem'; } ?>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/style.css">
    
    <?php if (!empty($pageStyles) && is_array($pageStyles)): ?>
        <?php foreach ($pageStyles as $style): ?>
            <link rel="stylesheet" href="<?php echo $baseUrl; ?>/<?php echo htmlspecialchars($style); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="<?php echo $baseUrl; ?>/homepage.php" class="nav-logo">
                <i class="fas fa-dumbbell"></i>
                <span>FitZone</span>
            </a>
            <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
                <span></span><span></span><span></span>
            </button>
            <ul class="nav-menu" id="navMenu">
                <li><a href="<?php echo $baseUrl; ?>/homepage.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="<?php echo $baseUrl; ?>/about.php"><i class="fas fa-info-circle"></i> About</a></li>
                <?php if (isLoggedIn()): ?>
                    <?php if (getUserRole() === 'admin'): ?>
                        <li><a href="<?php echo $baseUrl; ?>/admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo $baseUrl; ?>/member/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo $baseUrl; ?>/logout.php" class="btn-nav btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                <?php else: ?>
                    <li><a href="<?php echo $baseUrl; ?>/login.php" class="btn-nav"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <li><a href="<?php echo $baseUrl; ?>/register.php" class="btn-nav btn-register"><i class="fas fa-user-plus"></i> Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <main class="main-content">
