<?php
require_once 'auth.php';
require_once 'connectdb.php';

$pageTitle = 'Login';
$pageStyles = ['login.css?v=1.4'];
$error = '';
require_once 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        // Prepare the statement
        $stmt = $conn->prepare("SELECT user_id, username, password, role FROM users WHERE username = ?");
        
        // Bind the username variable
        $stmt->bind_param("s", $username);
        
        // Execute the statement
        $stmt->execute();
        
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($remember) {
                setcookie('remember_user', $user['username'], time() + (86400 * 30), '/');
            }

            if ($user['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: member/dashboard.php");
            }
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
        $stmt->close(); 
    }
}
?>
<section class="hero">
    <h1>Welcome Back to FitZone</h1>
</section>

<div class="auth-wrapper">
    <div class="auth-card fade-in">
        <h2><i class="fas fa-sign-in-alt"></i> Login</h2>
        <p class="subtitle">Access your fitness dashboard</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="login_username">Username</label>
                <input type="text" id="login_username" name="username" required value="<?php echo htmlspecialchars($username ?? ''); ?>" placeholder="Enter your username">
            </div>
            
            <div class="form-group">
                <label for="login_password">Password</label>
                <input type="password" id="login_password" name="password" required placeholder="Enter your password">
            </div>

            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember me for 30 days</label>
            </div>

            <button type="submit" class="btn-primary">Login to Account</button>
        </form>
        
        <div class="auth-footer">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<style>
/* Instant fix block */
.nav-menu, .footer-col ul { list-style: none !important; padding: 0; margin: 0; }
.nav-container { display: flex !important; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; padding: 0 20px; }
.nav-menu { display: flex !important; gap: 25px; }
.footer-grid { display: flex !important; flex-wrap: wrap; justify-content: space-between; gap: 30px; max-width: 1200px; margin: 0 auto; padding: 0 20px; }
.footer-col { flex: 1; min-width: 220px; }
.social-links { display: flex !important; gap: 15px; }
.nav-logo, .footer-logo { display: flex !important; align-items: center; gap: 10px; text-decoration: none; font-weight: 700; color: #4a148c; }
.nav-menu li a { text-decoration: none; color: #4a148c; font-weight: 500; }
.footer-col ul li a { text-decoration: none; color: #666; }
</style>
