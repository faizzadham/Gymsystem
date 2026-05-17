<?php
require_once '../auth.php';
requireAdmin();
require_once '../connectdb.php';

if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Start transaction because we need to delete from two tables
    $conn->begin_transaction();

    try {
        // 1. Delete from members table first (child table)
        $stmt1 = $conn->prepare("DELETE FROM members WHERE user_id = ?");
        $stmt1->bind_param("i", $userId);
        $stmt1->execute();

        // 2. Delete from users table (parent table)
        $stmt2 = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt2->bind_param("i", $userId);
        $stmt2->execute();

        $conn->commit();
        header("Location: members.php?msg=Member deleted successfully");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        header("Location: members.php?err=Could not delete member");
        exit();
    }
} else {
    header("Location: members.php");
    exit();
}