<?php
require_once '../auth.php';       
requireMember();
require_once '../connectdb.php'; 

$pageTitle = 'My Profile';
$userId = $_SESSION['user_id'];
$success = '';
$errors = [];

/*** Fetch fresh member data*/
$fetchMember = function($db, $uid) {
    $stmt = $db->prepare("SELECT * FROM members WHERE user_id = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    $member = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $member;
};

$member = $fetchMember($conn, $userId);
if (!$member) {
    $errors[] = 'Member profile could not be loaded.';
    $member = [];
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $gender   = $_POST['gender'] ?? ($member['gender'] ?? 'Other');

    if (empty($fullName) || empty($email)) {
        $errors[] = 'Name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } else {
        try {
            $conn->beginTransaction();

            // 1. Update members table
            $updMem = $conn->prepare("UPDATE members SET full_name = ?, email = ?, phone = ?, gender = ? WHERE member_id = ?");
            $updMem->execute([$fullName, $email, $phone, $gender, $member['member_id']]);

            // 2. Sync email to users table
            $updUsr = $conn->prepare("UPDATE users SET email = ? WHERE user_id = ?");
            $updUsr->execute([$email, $userId]);

            $conn->commit();
            
            $success = 'Profile updated successfully!';
            $member = $fetchMember($conn, $userId); // Refresh local data
        } catch (Exception $e) {
            $conn->rollBack();
            $errors[] = 'An error occurred while saving: ' . $e->getMessage();
        }
    }
}

require_once '../header.php';
?>

<div class="container fade-in">
    <div class="page-header">
        <h1><i class="fas fa-user"></i> My Profile</h1>
        <p>View and update your personal information</p>
    </div>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php foreach ($errors as $err): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($err) ?>
            </div>
        <?php endforeach; ?>

        <form method="POST">
            <div class="form-group">
                <label for="full_name">Full Name *</label>
                <input type="text" id="full_name" name="full_name" required 
                       value="<?= htmlspecialchars($member['full_name'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required 
                       value="<?= htmlspecialchars($member['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" 
                       value="<?= htmlspecialchars($member['phone'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Gender</label>
                <div class="radio-group" style="display: flex; gap: 1rem; margin-top: 0.5rem;">
                    <?php foreach (['Male', 'Female', 'Other'] as $opt): ?>
                        <label style="font-weight: normal; cursor: pointer;">
                            <input type="radio" name="gender" value="<?= $opt ?>" 
                                <?= (($member['gender'] ?? '') === $opt) ? 'checked' : '' ?>> 
                            <?= $opt ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label>Member Since</label>
                <input type="text" value="<?= htmlspecialchars($member['join_date'] ?? 'N/A') ?>" disabled style="background: var(--bg-secondary);">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                <i class="fas fa-save"></i> Update Profile
            </button>
        </form>
    </div>
</div>

