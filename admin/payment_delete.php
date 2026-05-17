<?php
require_once '../auth.php';
requireAdmin();
require_once '../connectdb.php';

$pageTitle = 'Delete Payment';


$id = $_GET['id'] ?? 0;



$stmt = $conn->prepare("SELECT py.*, m.full_name FROM payments py JOIN members m ON py.member_id = m.member_id WHERE py.payment_id = ?");

if ($stmt === false) {
    die("SQL Prepare Error: " . $conn->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$pay = $result->fetch_assoc();


if (!$pay) { 
    header("Location: payments.php"); 
    exit(); 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delStmt = $conn->prepare("DELETE FROM payments WHERE payment_id = ?");
    $delStmt->bind_param("i", $id);
    
    if ($delStmt->execute()) {
        header("Location: payments.php?msg=Payment deleted successfully");
        exit();
    } else {
        $error = "Error deleting record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Payment</title>
    <style>
        body { font-family: sans-serif; background: 
        .confirm-box { 
            max-width: 500px; 
            margin: 80px auto; 
            background: white; 
            padding: 40px; 
            border-radius: 8px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
            text-align: center; 
        }
        h2 { color: 
        p { font-size: 1.1rem; color: 
        .btn { padding: 12px 25px; border: none; border-radius: 4px; cursor: pointer; color: white; text-decoration: none; display: inline-block; font-weight: bold; }
        .btn-danger { background: 
        .btn-secondary { background: 
        .amount-tag { color: 
    </style>
</head>
<body>

<div class="confirm-box">
    <h2>Confirm Deletion</h2>
    <hr style="border: 0; border-top: 1px solid #eee; margin-bottom: 20px;">
    
    <p>
        Are you sure you want to delete the payment of <br>
        <span class="amount-tag">RM <?php echo number_format($pay['amount'], 2); ?></span> <br>
        recorded for <strong><?php echo htmlspecialchars($pay['full_name']); ?></strong>?
    </p>
    
    <form method="POST">
        <div class="btn-group">
            <button type="submit" class="btn btn-danger">Yes, Delete Permanently</button>
            <a href="payments.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

</body>
</html>