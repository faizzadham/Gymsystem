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

$member = $getMemberData($conn, $userId) ?: [];
$packagesResult = $conn->query("SELECT * FROM membership_packages");
$packages = $packagesResult ? $packagesResult->fetch_all(MYSQLI_ASSOC) : [];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['renew_package'])) {
    $pkgId = (int)$_POST['renew_package'];

    
    $pkgStmt = $conn->prepare("SELECT * FROM membership_packages WHERE package_id = ?");
    $pkgStmt->bind_param("i", $pkgId);
    $pkgStmt->execute();
    $pkgResult = $pkgStmt->get_result();
    $pkgData = $pkgResult ? $pkgResult->fetch_assoc() : null;
    $pkgStmt->close();

    if ($pkgData && $member) {
        $newExpiry = date('Y-m-d', strtotime("+" . $pkgData['duration'] . " months"));

        
        $updateStmt = $conn->prepare("UPDATE members SET package_id = ?, status = 'active', expiry_date = ? WHERE member_id = ?");
        $updateStmt->bind_param("isi", $pkgId, $newExpiry, $member['member_id']);
        $updateStmt->execute();
        $updateStmt->close();

        
        $payStmt = $conn->prepare("INSERT INTO payments (member_id, payment_date, amount, payment_method, payment_status) VALUES (?, CURDATE(), ?, 'Online', 'Paid')");
        $payStmt->bind_param("id", $member['member_id'], $pkgData['price']);
        $payStmt->execute();
        $payStmt->close();

        $success = 'Membership renewed successfully!';

        
        $member = $getMemberData($conn, $userId);
    }
}

require_once '../header.php';
?>

<link rel="stylesheet" href="membership.css?">

<div class="container fade-in">
    <div class="page-header">
        <h1><i class="fas fa-box"></i> Membership Details</h1>
        <p>View your current membership details and renew or update your plan below.</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= $success ?>
        </div>
    <?php endif; ?>

    <div class="card" style="max-width: 600px; margin-bottom: 2rem;">
        <h3>Current Membership</h3>
        <div class="stats-grid" style="grid-template-columns: 1fr 1fr;">
            <div>
                <p class="text-label">Package</p>
                <p class="text-value"><?= htmlspecialchars($member['package_name'] ?? 'None') ?></p>
            </div>
            <div>
                <p class="text-label">Status</p>
                <p>
                    <span class="badge <?= (($member['status'] ?? '') === 'active') ? 'badge-success' : 'badge-danger' ?>">
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

    <div class="card" style="max-width: 600px;">
        <h3>Change Package</h3>
        <div class="features-grid">
            <?php foreach ($packages as $p): ?>
                <div class="package-item">
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