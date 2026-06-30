<?php
// ============================================================
// includes/session.php – Session & Auth Middleware with RBAC
// ============================================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

function isLoggedIn(): bool {
    if (!empty($_SESSION['user'])) {
        return true;
    }
    if (!empty($_COOKIE[COOKIE_NAME])) {
        $token = $_COOKIE[COOKIE_NAME];
        $pdo   = getDB();
        $stmt  = $pdo->prepare(
            "SELECT u.user_id, u.name, u.email, u.role_id, r.role_name
               FROM user_tokens t
               JOIN users u ON u.user_id = t.user_id
               JOIN roles r ON r.role_id = u.role_id
              WHERE t.token = ? AND t.expires_at > datetime('now')
              LIMIT 1"
        );
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        if ($row) {
            $_SESSION['user']     = $row['user_id'];
            $_SESSION['name']     = $row['name'];
            $_SESSION['email']    = $row['email'];
            $_SESSION['role_id']  = $row['role_id'];
            $_SESSION['role']     = $row['role_name'];
            return true;
        }
        setcookie(COOKIE_NAME, '', time() - 3600, '/', '', false, true);
    }
    return false;
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        $_SESSION['login_error'] = 'Please login to continue.';
        header('Location: ' . APP_URL . '/index.php');
        exit;
    }
    // Check if account is active
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT is_active FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user']]);
    $user = $stmt->fetch();
    if (!$user || !$user['is_active']) {
        session_destroy();
        $_SESSION['login_error'] = 'Your account has been deactivated. Contact admin.';
        header('Location: ' . APP_URL . '/index.php');
        exit;
    }
}

function requireSuperAdmin(): void {
    requireLogin();
    if (!isset($_SESSION['role_id']) || (int)$_SESSION['role_id'] !== 2) {
        header('Location: ' . APP_URL . '/index.php');
        exit;
    }
}

function redirectIfLoggedIn(string $to = '/product/products.php'): void {
    if (isLoggedIn()) {
        header('Location: ' . APP_URL . $to);
        exit;
    }
}
