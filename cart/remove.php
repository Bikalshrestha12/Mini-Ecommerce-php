<?php
// ============================================================
// cart/remove.php – Remove Item from Cart
// ============================================================

require_once __DIR__ . '/../includes/session.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/cart/cart.php');
    exit;
}

$index = isset($_POST['index']) ? (int)$_POST['index'] : -1;

if ($index >= 0 && isset($_SESSION['cart'][$index])) {
    $removedName = $_SESSION['cart'][$index]['name'];
    unset($_SESSION['cart'][$index]);
    // Reset array indexes
    $_SESSION['cart']     = array_values($_SESSION['cart']);
    $_SESSION['cart_msg'] = htmlspecialchars($removedName) . ' removed from cart.';
} else {
    $_SESSION['cart_error'] = 'Item not found in cart.';
}

header('Location: ' . APP_URL . '/cart/cart.php');
exit;
