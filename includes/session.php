<?php
// ============================================================
// includes/session.php – Session & Auth Middleware
// ============================================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => false,   // set true on HTTPS
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

/**
 * Check whether the current visitor is authenticated.
 * Checks session first, then remember-me cookie.
 * Returns true if authenticated, false otherwise.
 */
function isLoggedIn(): bool {
    if (!empty($_SESSION['user'])) {
        return true;
    }

    // Remember-me cookie fallback
    if (!empty($_COOKIE[COOKIE_NAME])) {
        $token = $_COOKIE[COOKIE_NAME];
        $pdo   = getDB();
        $stmt  = $pdo->prepare(
            'SELECT u.user_id, u.name, u.email
               FROM user_tokens t
               JOIN users u ON u.user_id = t.user_id
              WHERE t.token = ? AND t.expires_at > NOW()
              LIMIT 1'
        );
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        if ($row) {
            $_SESSION['user']  = $row['user_id'];
            $_SESSION['name']  = $row['name'];
            $_SESSION['email'] = $row['email'];
            return true;
        }

        // Invalid / expired cookie – clean up
        setcookie(COOKIE_NAME, '', time() - 3600, '/', '', false, true);
    }

    return false;
}

/**
 * Require login – redirect to index if not authenticated.
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/index.php');
        exit;
    }
}

/**
 * Redirect already-logged-in users away from guest pages.
 */
function redirectIfLoggedIn(string $to = '/product/products.php'): void {
    if (isLoggedIn()) {
        header('Location: ' . APP_URL . $to);
        exit;
    }
}
