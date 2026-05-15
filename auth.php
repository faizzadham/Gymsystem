<?php
session_start();

// Base URL for app links and redirects
$baseUrl = '/Gymsystem';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user role
function getUserRole() {
    return $_SESSION['role'] ?? null;
}

// Require login - redirect if not logged in
function requireLogin() {
    global $baseUrl;
    if (!isLoggedIn()) {
        header("Location: {$baseUrl}/login.php");
        exit();
    }
}

// Require admin role
function requireAdmin() {
    global $baseUrl;
    requireLogin();
    if (getUserRole() !== 'admin') {
        header("Location: {$baseUrl}/login.php");
        exit();
    }
}

// Require member role
function requireMember() {
    global $baseUrl;
    requireLogin();
    if (getUserRole() !== 'member') {
        header("Location: {$baseUrl}/login.php");
        exit();
    }
}

// Check remember me cookie on page load
function checkRememberMe() {
    if (!isLoggedIn() && isset($_COOKIE['remember_user'])) {
        require_once __DIR__ . '/connectdb.php';
        $stmt = $conn->prepare("SELECT user_id, username, role FROM users WHERE username = ?");
        $stmt->execute([$_COOKIE['remember_user']]);
        $user = $stmt->fetch();
        if ($user) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
        }
    }
}

checkRememberMe();
?>
