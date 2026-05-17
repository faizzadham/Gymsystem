<?php
require_once '../auth.php';
requireAdmin();
require_once '../connectdb.php';

$pageTitle = 'Manage Packages';

// Fetch all packages using MySQLi
$result = $conn->query("SELECT * FROM membership_packages ORDER BY package_id");
$packages = ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];

// If you don't have header/footer files yet, you can comment these out
// require_once '../header.php'; 
?>

<style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7f6; padding: 20px; }
    .container { max-width: 900px; margin: auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th { background: #2c3e50; color: white; padding: 12px; text-align: left; }
    td { padding: 12px; border-bottom: 1px solid #eee; }
    
    .btn { padding: 8px 15px; border-radius: 5px; text-decoration: none; color: white; font-size: 14px; display: inline-block; cursor: pointer; border: none; }
    .btn-primary { background: #2ecc71; }
    .btn-secondary { background: #3498db; }
    .btn-danger { background: #e74c3c; }
    .btn-sm { padding: 5px 10px; font-size: 12px; }
    
    .alert-success { padding: 10px; background: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 15px; }
    .text-muted { color: #6c757d; }
</style>

<div class="container">
    <div class="page-header">
        <div>
            <h1>Membership Packages</h1>
            <p class="text-muted">Manage gym membership tiers</p>
        </div>
        <a href="package_add.php" class="btn btn-primary">+ Add Package</a>
    </div>

    <!-- Success Message -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <strong>Success!</strong> <?php echo htmlspecialchars($_GET['msg']); ?>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Package Name</th>
                <th>Duration</th>
                <th>Price (RM)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($packages)): ?>
                <tr>
                    <td colspan="5" style="text-align:center; padding: 20px;" class="text-muted">No packages found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($packages as $p): ?>
                <tr>
                    <td><?php echo $p['package_id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($p['package_name']); ?></strong></td>
                    <td><?php echo $p['duration']; ?> Month<?php echo $p['duration'] > 1 ? 's' : ''; ?></td>
                    <td>RM <?php echo number_format($p['price'], 2); ?></td>
                    <td>
                        <a href="package_edit.php?id=<?php echo $p['package_id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                        <a href="package_delete.php?id=<?php echo $p['package_id']; ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Deleting this package might affect members assigned to it. Continue?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        <a href="dashboard.php" style="color: #34495e; text-decoration: none;">← Back to Dashboard</a>
    </div>
</div>

<?php // require_once '../footer.php'; ?>