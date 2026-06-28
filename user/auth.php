<?php
// ============================================================
// user/auth.php – Authentication Gate
// ============================================================

require_once __DIR__ . '/../includes/session.php';

if (isLoggedIn()) {
    header('Location: ' . APP_URL . '/product/products.php');
} else {
    header('Location: ' . APP_URL . '/index.php');
}
exit;
