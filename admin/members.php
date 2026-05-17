<?php
require_once '../auth.php';
requireAdmin();
require_once '../connectdb.php';

$pageTitle = 'Manage Members';


$sql = "SELECT m.*, p.package_name 
        FROM members m 
        LEFT JOIN membership_packages p ON m.package_id = p.package_id 
        ORDER BY m.join_date DESC";

$result = $conn->query($sql);
$members = ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];



?>

<style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: 
    .container { max-width: 1100px; margin: auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .flex-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th { background: 
    td { padding: 12px; border-bottom: 1px solid 
    tr:hover { background-color: 

    .btn { padding: 8px 15px; border-radius: 5px; text-decoration: none; color: white; font-size: 14px; display: inline-block; }
    .btn-add { background: 
    .btn-edit { background: 
    .btn-delete { background: 
    
    .badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
    .badge-active { background: 
    .badge-expired { background: 
    
    .msg { padding: 10px; background: 
</style>

<div class="container">
    <div class="flex-header">
        <h1>Gym Members</h1>
        <a href="member_add.php" class="btn btn-add">+ Add New Member</a>
    </div>

    <!-- Show success messages if they exist in the URL -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="msg"><?php echo htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Contact</th>
                <th>Package</th>
                <th>Status</th>
                <th>Expiry</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($members)): ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding: 20px;">No members found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($members as $m): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($m['full_name']); ?></strong></td>
                    <td>
                        <?php echo htmlspecialchars($m['email']); ?><br>
                        <small style="color: #666;"><?php echo htmlspecialchars($m['phone']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($m['package_name'] ?? 'Custom/None'); ?></td>
                    <td>
                        <span class="badge <?php echo ($m['status'] == 'active') ? 'badge-active' : 'badge-expired'; ?>">
                            <?php echo $m['status']; ?>
                        </span>
                    </td>
                    <td><?php echo $m['expiry_date'] ?: 'N/A'; ?></td>
                    <td>
                        <a href="member_edit.php?id=<?php echo $m['user_id']; ?>" class="btn btn-edit">Edit</a>
                        <a href="member_delete.php?id=<?php echo $m['user_id']; ?>" 
                           class="btn btn-delete" 
                           onclick="return confirm('Are you sure you want to delete this member?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div style="margin-top: 20px;">
        <a href="dashboard.php" style="color: #7f8c8d; text-decoration: none;">← Back to Dashboard</a>
    </div>
</div>

<?php 