<?php
require_once '../auth.php';       
requireMember();
require_once '../connectdb.php'; 

$pageTitle = 'Membership Details';
$userId = $_SESSION['user_id'];
$success = '';
$error = '';

$getMemberData = function($conn, $id) {
    $sql = "SELECT m.*, p.package_name, p.duration, p.price 
            FROM members m 
            LEFT JOIN membership_packages p ON m.package_id = p.package_id 
            WHERE m.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $member = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $member;
};

$member = $getMemberData($conn, $userId);
$packagesResult = $conn->query("SELECT * FROM membership_packages");
$packages = $packagesResult ? $packagesResult->fetch_all(MYSQLI_ASSOC) : [];

// Handle Membership Renewal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['renew_package'])) {
    $pkgId = (int)$_POST['renew_package'];
    
    // 1. Corrected Fetch selected package details
    $pkgStmt = $conn->prepare("SELECT * FROM membership_packages WHERE package_id = ?");
    $pkgStmt->bind_param("i", $pkgId); // Added bind_param
    $pkgStmt->execute();
    $pkgResult = $pkgStmt->get_result(); // Used get_result for MySQLi
    $pkgData = $pkgResult->fetch_assoc();
    $pkgStmt->close();

    if ($pkgData && $member) {
        $newExpiry = date('Y-m-d', strtotime("+" . $pkgData['duration'] . " months"));
        
        // 2. Corrected Update Member Record
        $updateSql = "UPDATE members SET package_id = ?, status = 'active', expiry_date = ? WHERE member_id = ?";
        $updStmt = $conn->prepare($updateSql);
        $updStmt->bind_param("isi", $pkgId, $newExpiry, $member['member_id']);
        
        if ($updStmt->execute()) {
            // 3. Corrected Record Payment
            $paySql = "INSERT INTO payments (member_id, payment_date, amount, payment_method, payment_status) 
                       VALUES (?, CURDATE(), ?, 'Online', 'Paid')";
            $payStmt = $conn->prepare($paySql);
            $payStmt->bind_param("id", $member['member_id'], $pkgData['price']);
            $payStmt->execute();
            $payStmt->close();
            
            $success = 'Membership renewed successfully!';
            // Refresh member data for display
            $member = $getMemberData($conn, $userId);
        } else {
            $error = 'Failed to update membership.';
        }
        $updStmt->close();
    }
}

require_once '../header.php';
?>

<div class="container fade-in">
    <div class="page-header">
        <h1><i class="fas fa-box"></i> Membership Details</h1>
        <p>View your current membership and renew</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= $success ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= $error ?>
        </div>
    <?php endif; ?>

    <div class="card" style="max-width: 600px; margin-bottom: 2rem;">
        <h3 style="margin-bottom: 1rem;">Current Membership</h3>
        <div class="stats-grid" style="grid-template-columns: 1fr 1fr; display: grid; gap: 1rem;">
            <div>
                <p class="text-label">Package</p>
                <p class="text-value"><?= htmlspecialchars($member['package_name'] ?? 'None') ?></p>
            </div>
            <div>
                <p class="text-label">Status</p>
                <p>
                    <span class="badge <?= (isset($member['status']) && $member['status'] === 'active') ? 'badge-success' : 'badge-danger' ?>">
                        <?= ucfirst($member['status'] ?? 'None') ?>
                    </span>
                </p>
            </div>
            <div>
                <p class="text-label">Duration</p>
                <p class="text-value"><?= ($member['duration'] ?? '-') ?> Month(s)</p>
            </div>
            <div>
                <p class="text-label">Expires</p>
                <p class="text-value"><?= $member['expiry_date'] ?? 'N/A' ?></p>
            </div>
        </div>
    </div>

    <div class="card" style="max-width: 600px;">
        <h3 style="margin-bottom: 1rem;">Renew / Change Package</h3>
        <div class="features-grid" style="grid-template-columns: 1fr; display: grid; gap: 1rem;">
            <?php foreach ($packages as $p): ?>
                <div class="card package-item" style="border: 1px solid #eee; padding: 1rem; border-radius: 8px;">
                    <div>
                        <h4 style="margin: 0;"><?= htmlspecialchars($p['package_name']) ?></h4>
                        <p class="package-meta">
                            <?= $p['duration'] ?> Month<?= $p['duration'] > 1 ? 's' : '' ?> — 
                            <strong>RM <?= number_format($p['price'], 2) ?></strong>
                        </p>
                    </div>
                    <form method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to select this package?');">
                        <button type="submit" name="renew_package" value="<?= $p['package_id'] ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-sync-alt"></i> Select
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
    .text-label { color: #64748b; font-size: 0.85rem; margin-bottom: 0.2rem; }
    .text-value { font-size: 1.1rem; font-weight: 600; margin: 0; }
    .package-item { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        gap: 1rem; 
    }
    .package-meta { color: #64748b; font-size: 0.9rem; margin: 0.5rem 0 0; }
    .badge-success { background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; }
    .badge-danger { background: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; }
</style>