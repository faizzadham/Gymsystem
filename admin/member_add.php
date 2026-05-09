<?php
require_once '../auth.php';
requireAdmin();
require_once '../connectdb.php';

$pageTitle = 'Add Member';

// Fetch packages using MySQLi
$packagesResult = $conn->query("SELECT * FROM membership_packages");
$packages = $packagesResult ? $packagesResult->fetch_all(MYSQLI_ASSOC) : [];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = $_POST['gender'] ?? 'Male';
    $packageId = $_POST['package_id'] ?? null;
    $joinDate = $_POST['join_date'] ?? date('Y-m-d');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($fullName) || empty($email) || empty($username) || empty($password)) {
        $errors[] = 'All required fields must be filled.';
    }

    if (empty($errors)) {
        // Check if username or email exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $errors[] = 'Username or email already exists.';
        } else {
            // Start Transaction
            $conn->begin_transaction();

            try {
                $hashedPass = password_hash($password, PASSWORD_DEFAULT);
                
                // 1. Insert into Users table
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'member')");
                $stmt->bind_param("sss", $username, $email, $hashedPass);
                $stmt->execute();
                $userId = $conn->insert_id;

                // 2. Calculate Expiry Date
                $expiryDate = null;
                if (!empty($packageId)) {
                    $pkgStmt = $conn->prepare("SELECT duration FROM membership_packages WHERE package_id = ?");
                    $pkgStmt->bind_param("i", $packageId);
                    $pkgStmt->execute();
                    $pkgRes = $pkgStmt->get_result()->fetch_assoc();
                    
                    if ($pkgRes && $pkgRes['duration']) {
                        $dur = $pkgRes['duration'];
                        $expiryDate = date('Y-m-d', strtotime($joinDate . " + $dur months"));
                    }
                }

                // 3. Insert into Members table
                $stmt = $conn->prepare("INSERT INTO members (user_id, full_name, email, phone, gender, join_date, package_id, expiry_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssssis", $userId, $fullName, $email, $phone, $gender, $joinDate, $packageId, $expiryDate);
                $stmt->execute();

                $conn->commit();
                header("Location: members.php?msg=Member added successfully");
                exit();

            } catch (Exception $e) {
                $conn->rollback();
                $errors[] = 'Failed to add member: ' . $e->getMessage();
            }
        }
    }
}

// Comment these out if files don't exist yet
// require_once '../header.php'; 
?>

<!-- Include the CSS from your bookings page here to make it look nice -->
<style>
    body { font-family: sans-serif; background: #f4f7f6; padding: 20px; }
    .admin-content { max-width: 700px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .form-group { margin-bottom: 15px; }
    label { display: block; margin-bottom: 5px; font-weight: bold; }
    input[type="text"], input[type="email"], input[type="password"], input[type="date"], select {
        width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;
    }
    .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; color: white; text-decoration: none; display: inline-block; }
    .btn-primary { background: #2ecc71; }
    .btn-secondary { background: #95a5a6; }
    .alert-danger { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
</style>

<div class="admin-layout">
    <div class="admin-content">
        <h1>Add New Member</h1>
        
        <?php foreach ($errors as $err): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
        <?php endforeach; ?>

        <form method="POST">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" required value="<?php echo htmlspecialchars($fullName ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Username *</label>
                <input type="text" name="username" required value="<?php echo htmlspecialchars($username ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group">
                <label>Package</label>
                <select name="package_id">
                    <option value="">-- No Package --</option>
                    <?php foreach ($packages as $p): ?>
                        <option value="<?php echo $p['package_id']; ?>">
                            <?php echo htmlspecialchars($p['package_name']); ?> (RM<?php echo $p['price']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Join Date</label>
                <input type="date" name="join_date" value="<?php echo date('Y-m-d'); ?>">
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Save Member</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>