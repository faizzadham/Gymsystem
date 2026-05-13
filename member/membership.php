<?php
require_once '../auth.php';       
requireMember();
require_once '../connectdb.php'; 

$pageTitle = 'Membership Details';
$userId = $_SESSION['user_id'];
$success = '';

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
    
    // Fetch selected package details
    $pkgStmt = $conn->prepare("SELECT * FROM membership_packages WHERE package_id = ?");
    $pkgStmt->execute([$pkgId]);
    $pkgData = $pkgStmt->fetch();

    if ($pkgData && $member) {
        $newExpiry = date('Y-m-d', strtotime("+" . $pkgData['duration'] . " months"));
        
        // Update Member Record
        $updateSql = "UPDATE members SET package_id = ?, status = 'active', expiry_date = ? WHERE member_id = ?";
        $conn->prepare($updateSql)->execute([$pkgId, $newExpiry, $member['member_id']]);
        
        // Record Payment
        $paySql = "INSERT INTO payments (member_id, payment_date, amount, payment_method, payment_status) 
                   VALUES (?, CURDATE(), ?, 'Online', 'Paid')";
        $conn->prepare($paySql)->execute([$member['member_id'], $pkgData['price']]);
        
        $success = 'Membership renewed successfully!';
        
        // Refresh member data for display
        $member = $getMemberData($conn, $userId);
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

    <!-- Current Membership Card -->
    <div class="card" style="max-width: 600px; margin-bottom: 2rem;">
        <h3 style="margin-bottom: 1rem;">Current Membership</h3>
        <div class="stats-grid" style="grid-template-columns: 1fr 1fr;">
            <div>
                <p class="text-label">Package</p>
                <p class="text-value"><?= htmlspecialchars($member['package_name'] ?? 'None') ?></p>
            </div>
            <div>
                <p class="text-label">Status</p>
                <p>
                    <span class="badge <?= ($member['status'] === 'active') ? 'badge-success' : 'badge-danger' ?>">
                        <?= ucfirst($member['status'] ?? 'unknown') ?>
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

    <!-- Renewal Card -->
    <div class="card" style="max-width: 600px;">
        <h3 style="margin-bottom: 1rem;">Renew / Change Package</h3>
        <div class="features-grid" style="grid-template-columns: 1fr;">
            <?php foreach ($packages as $p): ?>
                <div class="card package-item">
                    <div>
                        <h4><?= htmlspecialchars($p['package_name']) ?></h4>
                        <p class="package-meta">
                            <?= $p['duration'] ?> Month<?= $p['duration'] > 1 ? 's' : '' ?> — 
                            <strong>RM <?= number_format($p['price'], 2) ?></strong>
                        </p>
                    </div>
                    <form method="POST" style="margin: 0;">
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
    /* Added some utility classes for cleaner inline styles */
    .text-label { color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.2rem; }
    .text-value { font-size: 1.1rem; font-weight: 600; margin: 0; }
    .package-item { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        flex-wrap: wrap; 
        gap: 1rem; 
        margin-bottom: 0.5rem;
    }
    .package-meta { color: var(--text-secondary); font-size: 0.9rem; margin: 0; }
</style>

