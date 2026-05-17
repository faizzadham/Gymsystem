<?php
require_once '../auth.php';
requireAdmin();
require_once '../connectdb.php';

$pageTitle = 'Manage Bookings';

$query = "SELECT sb.*, m.full_name, t.trainer_name 
          FROM session_bookings sb 
          JOIN members m ON sb.member_id = m.member_id 
          JOIN trainers t ON sb.trainer_id = t.trainer_id 
          ORDER BY sb.session_date DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?></title>
    <style>
        body { font-family: sans-serif; background: 
        .admin-content { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: 
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid 
        th { background-color: 
        .btn-sm { padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 13px; color: white; display: inline-block; margin-right: 5px; }
        .btn-primary { background: 
        .btn-success { background: 
        .btn-danger { background: 
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; background: 
        .alert-success { padding: 10px; background: 
    </style>
</head>
<body>

<div class="admin-layout">
    <div class="admin-content">
        <div class="page-header">
            <h1>Bookings Management</h1>
            <?php if(isset($_GET['msg'])): ?>
                <div class="alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
            <?php endif; ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Trainer</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['trainer_name']); ?></td>
                        <td><?php echo $row['session_date'] . ' | ' . $row['session_time']; ?></td>
                        <td><span class="status-badge"><?php echo $row['booking_status']; ?></span></td>
                        <td>
                            <a href="booking_edit.php?id=<?php echo $row['booking_id']; ?>" class="btn-sm btn-primary">Edit</a>
                            <a href="booking_action.php?id=<?php echo $row['booking_id']; ?>&action=Approved" class="btn-sm btn-success">Approve</a>
                            <a href="booking_delete.php?id=<?php echo $row['booking_id']; ?>" class="btn-sm btn-danger">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center;">No bookings found in the system.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>