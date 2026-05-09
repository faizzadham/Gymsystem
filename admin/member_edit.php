<?php
require_once '../auth.php';
requireAdmin();
require_once '../connectdb.php';

$pageTitle = 'Edit Member';

// Get ID from URL - using user_id to match your members.php links
$id = $_GET['id'] ?? 0;

// Fetch member details using MySQLi
$stmt = $conn->prepare("SELECT * FROM members WHERE user_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();

if (!$member) { 
    header("Location: members.php"); 
    exit(); 
}

// Fetch packages for the dropdown
$packagesResult = $conn->query("SELECT * FROM membership_packages");
$packages = ($packagesResult) ? $packagesResult->fetch_all(MYSQLI_ASSOC) : [];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = $_POST['gender'] ?? 'Male';
    $packageId = $_POST['package_id'] ?? null;
    $status = $_POST['status'] ?? 'active';
    $expiryDate = $_POST['expiry_date'] ?? null;

    if (empty($fullName) || empty($email)) {
        $errors[] = 'Name and email are required.';
    }

    if (empty($errors)) {
        // Prepare the UPDATE statement (Note: we use user_id in the WHERE clause)
        $updateStmt = $conn->prepare("UPDATE members SET full_name=?, email=?, phone=?, gender=?, package_id=?, status=?, expiry_date=? WHERE user_id=?");
        
        // Handle potential null for package_id
        $pId = !empty($packageId) ? $packageId : null;
        
        $updateStmt->bind_param("ssssissi", $fullName, $email, $phone, $gender, $pId, $status, $expiryDate, $id);
        
        if ($updateStmt->execute()) {
            header("Location: members.php?msg=Member updated successfully");
            exit();
        } else {
            $errors[] = "Update failed: " . $conn->error;
        }
    }
}
?>

<style>
    body { font-family: sans-serif; background: #f4f7f6; padding: 20px; }
    .container { max-width: 700px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .form-group { margin-bottom: 15px; }
    label { display: block; margin-bottom: 5px; font-weight: bold; }
    input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
    .radio-group { display: flex; gap: 15px; padding: 10px 0; }
    .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; color: white; text-decoration: none; display: inline-block; }
    .btn-primary { background: #3498db; }
    .btn-secondary { background: #95a5a6; }
    .alert-danger { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
</style>

<div class="container">
    <h1>Edit Member</h1>
    <p>Updating information for: <strong><?php echo htmlspecialchars($member['full_name']); ?></strong></p>
    <hr>

    <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
    <?php endforeach; ?>

    <form method="POST">
        <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="full_name" required value="<?php echo htmlspecialchars($member['full_name']); ?>">
        </div>

        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required value="<?php echo htmlspecialchars($member['email']); ?>">
        </div>

        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($member['phone']); ?>">
        </div>

        <div class="form-group">
            <label>Gender</label>
            <div class="radio-group">
                <label><input type="radio" name="gender" value="Male" <?php echo $member['gender'] === 'Male' ? 'checked' : ''; ?>> Male</label>
                <label><input type="radio" name="gender" value="Female" <?php echo $member['gender'] === 'Female' ? 'checked' : ''; ?>> Female</label>
                <label><input type="radio" name="gender" value="Other" <?php echo $member['gender'] === 'Other' ? 'checked' : ''; ?>> Other</label>
            </div>
        </div>

        <div class="form-group">
            <label>Package</label>
            <select name="package_id">
                <option value="">-- No Package --</option>
                <?php foreach ($packages as $p): ?>
                    <option value="<?php echo $p['package_id']; ?>" <?php echo ($member['package_id'] == $p['package_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['package_name']); ?> (RM<?php echo $p['price']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="active" <?php echo $member['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="expired" <?php echo $member['status'] === 'expired' ? 'selected' : ''; ?>>Expired</option>
            </select>
        </div>

        <div class="form-group">
            <label>Expiry Date</label>
            <input type="date" name="expiry_date" value="<?php echo $member['expiry_date']; ?>">
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary">Update Member</button>
            <a href="members.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>