<?php
require_once '../auth.php';
requireAdmin();
require_once '../connectdb.php';

$pageTitle = 'Member Reports';

// Fetching members with their package details
// We use a LEFT JOIN so members without a package still show up as 'None'
$query = "SELECT m.full_name, m.email, m.phone, m.expiry_date, p.package_name 
          FROM members m 
          LEFT JOIN packages p ON m.package_id = p.package_id 
          ORDER BY m.full_name ASC";

$result = $conn->query($query);
$reportData = ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7f6; padding: 20px; }
        .container { max-width: 1100px; margin: auto; }
        .card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; margin-bottom: 20px; }
        
        .report-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .report-table th { background: #34495e; color: white; padding: 12px; text-align: left; }
        .report-table td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; }
        .report-table tr:hover { background: #f9f9f9; }
        
        .status-expired { color: #e74c3c; font-weight: bold; }
        .status-active { color: #27ae60; font-weight: bold; }
        
        .no-print { margin-bottom: 20px; }
        .btn-print { background: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; font-weight: bold; }
        
        @media print {
            .no-print { display: none; }
            body { background: white; padding: 0; }
            .card { box-shadow: none; border: none; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="no-print">
        <a href="dashboard.php" style="text-decoration: none; color: #7f8c8d;">&larr; Back to Dashboard</a>
    </div>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1>Member Status Report</h1>
            <button onclick="window.print()" class="btn-print no-print">Print Report</button>
        </div>
        
        <div class="table-responsive">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Package</th>
                        <th>Expiry Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reportData)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No member records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reportData as $m): 
                            $isExpired = (isset($m['expiry_date']) && strtotime($m['expiry_date']) < time());
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($m['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($m['email']); ?></td>
                                <td><?php echo htmlspecialchars($m['phone']); ?></td>
                                <td><?php echo htmlspecialchars($m['package_name'] ?? 'None'); ?></td>
                                <td><?php echo $m['expiry_date'] ?? '-'; ?></td>
                                <td>
                                    <?php if (!$m['expiry_date']): ?>
                                        <span style="color: #95a5a6;">N/A</span>
                                    <?php elseif ($isExpired): ?>
                                        <span class="status-expired">Expired</span>
                                    <?php else: ?>
                                        <span class="status-active">Active</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
// Check if footer exists before including to avoid errors
if (file_exists('../includes/footer.php')) {
    require_once '../includes/footer.php'; 
}
?>
</body>
</html>