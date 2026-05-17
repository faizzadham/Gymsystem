<?php
session_start();


$baseUrl = '/Gymsystem';


function isLoggedIn() {
    return isset($_SESSION['user_id']);
}


function getUserRole() {
    return $_SESSION['role'] ?? null;
}


function requireLogin() {
    global $baseUrl;
    if (!isLoggedIn()) {
        header("Location: {$baseUrl}/login.php");
        exit();
    }
}


function requireAdmin() {
    global $baseUrl;
    requireLogin();
    if (getUserRole() !== 'admin') {
        header("Location: {$baseUrl}/login.php");
        exit();
    }
}


function requireMember() {
    global $baseUrl;
    requireLogin();
    if (getUserRole() !== 'member') {
        header("Location: {$baseUrl}/login.php");
        exit();
    }
}


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
