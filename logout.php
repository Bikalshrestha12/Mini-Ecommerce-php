<?php
// ============================================================
// logout.php – Logout Handler
// ============================================================

require_once __DIR__ . '/includes/session.php';

// Remove remember-me token from DB
if (!empty($_SESSION['user'])) {
    try {
        getDB()->prepare('DELETE FROM user_tokens WHERE user_id = ?')
               ->execute([$_SESSION['user']]);
    } catch (Exception $e) {
        // Silently fail
    }
}

// Destroy session
session_unset();
session_destroy();

// Remove cookie
setcookie(COOKIE_NAME, '', time() - 3600, '/', '', false, true);

// Redirect
header('Location: ' . APP_URL . '/index.php');
exit;
