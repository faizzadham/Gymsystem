<?php
require_once '../auth.php';
requireAdmin();
require_once '../connectdb.php';

$pageTitle = 'Edit Package';


$id = $_GET['id'] ?? 0;


$stmt = $conn->prepare("SELECT * FROM membership_packages WHERE package_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$pkg = $result->fetch_assoc();


if (!$pkg) {
    header("Location: packages.php");
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['package_name'] ?? '');
    $duration = (int)($_POST['duration'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);

    if (empty($name) || $duration <= 0 || $price <= 0) {
        $errors[] = 'All fields are required and must have valid values.';
    } else {
        
        $updateStmt = $conn->prepare("UPDATE membership_packages SET package_name = ?, duration = ?, price = ? WHERE package_id = ?");
        $updateStmt->bind_param("sidi", $name, $duration, $price, $id);
        
        if ($updateStmt->execute()) {
            header("Location: packages.php?msg=Package updated successfully");
            exit();
        } else {
            $errors[] = "Update failed: " . $conn->error;
        }
    }
}
?>

<style>
    body { font-family: sans-serif; background: 
    .card { max-width: 500px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .form-group { margin-bottom: 15px; }
    label { display: block; margin-bottom: 5px; font-weight: bold; }
    input { width: 100%; padding: 10px; border: 1px solid 
    .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; color: white; text-decoration: none; display: inline-block; }
    .btn-primary { background: 
    .btn-secondary { background: 
    .alert-danger { background: 
</style>

<div class="admin-layout">
    <div class="card">
        <h1>Edit Package</h1>
        <p style="color: #666;">Modifying: <strong><?php echo htmlspecialchars($pkg['package_name']); ?></strong></p>
        <hr>
        
        <?php foreach ($errors as $err): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
        <?php endforeach; ?>

        <form method="POST">
            <div class="form-group">
                <label for="package_name">Package Name *</label>
                <input type="text" id="package_name" name="package_name" 
                       value="<?php echo htmlspecialchars($pkg['package_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="duration">Duration (months) *</label>
                <input type="number" id="duration" name="duration" min="1" 
                       value="<?php echo $pkg['duration']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="price">Price (RM) *</label>
                <input type="number" id="price" name="price" step="0.01" min="0.01" 
                       value="<?php echo $pkg['price']; ?>" required>
            </div>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Update Package</button>
                <a href="packages.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>