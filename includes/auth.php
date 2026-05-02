<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';

// Check if user is logged in
function requireAuth() {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Check admin role
function requireAdmin() {
    requireAuth();
    if ($_SESSION['user_role'] !== 'admin') {
        setFlash('error', 'Acesso restrito a administradores.');
        header('Location: index.php');
        exit;
    }
}

// Get current user
function currentUser() {
    global $pdo;
    if (empty($_SESSION['user_id'])) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Login user
function login($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email'] = $user['email'];
        return true;
    }
    return false;
}

// Logout
function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}
