<?php
// ============================================================
// cart/add.php – Add Product to Cart
// ============================================================

require_once __DIR__ . '/../includes/session.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/product/products.php');
    exit;
}

$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity  = isset($_POST['quantity'])   ? (int)$_POST['quantity']   : 1;

if ($productId <= 0 || $quantity <= 0) {
    $_SESSION['cart_error'] = 'Invalid product or quantity.';
    header('Location: ' . APP_URL . '/product/products.php');
    exit;
}

// Fetch product from DB
$pdo  = getDB();
$stmt = $pdo->prepare('SELECT * FROM products WHERE product_id = ?');
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['cart_error'] = 'Product not found.';
    header('Location: ' . APP_URL . '/product/products.php');
    exit;
}

if ($product['stock'] < $quantity) {
    $_SESSION['cart_error'] = 'Not enough stock available.';
    header('Location: ' . APP_URL . '/product/details.php?id=' . $productId);
    exit;
}

// ── Initialise cart ──────────────────────────────────────────
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ── Check if already in cart ─────────────────────────────────
$found = false;
foreach ($_SESSION['cart'] as &$item) {
    if ((int)$item['product_id'] === $productId) {
        $newQty = $item['quantity'] + $quantity;
        if ($newQty > $product['stock']) {
            $_SESSION['cart_error'] = 'Total quantity exceeds available stock (' . $product['stock'] . ').';
            header('Location: ' . APP_URL . '/product/details.php?id=' . $productId);
            exit;
        }
        $item['quantity'] = $newQty;
        $found = true;
        break;
    }
}
unset($item);

// ── Add new item ─────────────────────────────────────────────
if (!$found) {
    $_SESSION['cart'][] = [
        'product_id' => $productId,
        'name'       => $product['name'],
        'price'      => (float)$product['price'],
        'quantity'   => $quantity,
        'image'      => $product['image'],
        'category'   => $product['category'],
    ];
}

$_SESSION['cart_msg'] = htmlspecialchars($product['name']) . ' added to cart!';

// Redirect back to product detail or referer
$referer = $_SERVER['HTTP_REFERER'] ?? APP_URL . '/product/products.php';
header('Location: ' . $referer);
exit;
