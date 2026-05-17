<?php
require_once '../auth.php';
requireAdmin();
require_once '../connectdb.php';

$pageTitle = 'Add Package';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['package_name'] ?? '');
    $duration = (int)($_POST['duration'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);

    if (empty($name) || $duration <= 0 || $price <= 0) {
        $errors[] = 'All fields are required with valid values.';
    } else {
        
        $stmt = $conn->prepare("INSERT INTO membership_packages (package_name, duration, price) VALUES (?, ?, ?)");
        
        
        $stmt->bind_param("sid", $name, $duration, $price);
        
        if ($stmt->execute()) {
            header("Location: packages.php?msg=Package added successfully");
            exit();
        } else {
            $errors[] = "Error saving package: " . $conn->error;
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
        <h1>Add New Package</h1>
        <hr>
        
        <?php foreach ($errors as $err): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
        <?php endforeach; ?>

        <form method="POST">
            <div class="form-group">
                <label for="package_name">Package Name *</label>
                <input type="text" id="package_name" name="package_name" placeholder="e.g. Gold Membership" required>
            </div>
            
            <div class="form-group">
                <label for="duration">Duration (months) *</label>
                <input type="number" id="duration" name="duration" min="1" placeholder="e.g. 12" required>
            </div>
            
            <div class="form-group">
                <label for="price">Price (RM) *</label>
                <input type="number" id="price" name="price" step="0.01" min="0.01" placeholder="e.g. 150.00" required>
            </div>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Save Package</button>
                <a href="packages.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php 