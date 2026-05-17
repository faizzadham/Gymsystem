<?php
require_once '../auth.php';
requireAdmin();
require_once '../connectdb.php';

$pageTitle = 'Delete Package';


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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $delStmt = $conn->prepare("DELETE FROM membership_packages WHERE package_id = ?");
        $delStmt->bind_param("i", $id);
        
        if ($delStmt->execute()) {
            header("Location: packages.php?msg=Package deleted successfully");
            exit();
        } else {
            $error = "System Error: Unable to delete package.";
        }
    } catch (Exception $e) {
        
        $error = "Cannot delete this package because it is currently assigned to one or more members.";
    }
}
?>

<style>
    body { font-family: sans-serif; background: 
    .confirm-box { 
        max-width: 500px; 
        margin: 100px auto; 
        background: white; 
        padding: 40px; 
        border-radius: 8px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.2); 
        text-align: center; 
    }
    h2 { color: 
    p { font-size: 1.1rem; color: 
    .btn { padding: 12px 25px; border: none; border-radius: 4px; cursor: pointer; color: white; text-decoration: none; display: inline-block; font-weight: bold; }
    .btn-danger { background: 
    .btn-secondary { background: 
    .alert-danger { background: 
</style>

<div class="admin-layout">
    <div class="confirm-box">
        <h2>Confirm Deletion</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <strong>Notice:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <p>Are you sure you want to permanently delete the <strong><?php echo htmlspecialchars($pkg['package_name']); ?></strong> package?</p>
        
        <form method="POST">
            <div class="btn-group">
                <button type="submit" class="btn btn-danger">Yes, Delete Package</button>
                <a href="packages.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>