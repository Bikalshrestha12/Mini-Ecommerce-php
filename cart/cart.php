<?php
// ============================================================
// cart/cart.php – Shopping Cart View
// ============================================================

require_once __DIR__ . '/../includes/session.php';
requireLogin();

$pdo = getDB();

// Handle inline quantity update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    $index   = (int)($_POST['index'] ?? -1);
    $newQty  = (int)($_POST['quantity'] ?? 1);

    if ($index >= 0 && isset($_SESSION['cart'][$index]) && $newQty >= 1) {
        $pid  = (int)$_SESSION['cart'][$index]['product_id'];
        $stmt = $pdo->prepare('SELECT stock FROM products WHERE product_id = ?');
        $stmt->execute([$pid]);
        $row  = $stmt->fetch();
        $stock = $row ? (int)$row['stock'] : 0;

        if ($newQty > $stock) {
            $_SESSION['cart_error'] = 'Only ' . $stock . ' units available.';
        } else {
            $_SESSION['cart'][$index]['quantity'] = $newQty;
            $_SESSION['cart_msg'] = 'Cart updated.';
        }
    }
    header('Location: ' . APP_URL . '/cart/cart.php');
    exit;
}

$msg   = $_SESSION['cart_msg']   ?? '';
$error = $_SESSION['cart_error'] ?? '';
unset($_SESSION['cart_msg'], $_SESSION['cart_error']);

$cart  = $_SESSION['cart'] ?? [];
$total = 0.0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1><i class="fa-solid fa-cart-shopping"></i> Shopping Cart</h1>
    <a href="<?= APP_URL ?>/product/products.php" class="btn btn-secondary btn-sm">
        <i class="fa-solid fa-arrow-left"></i> Continue Shopping
    </a>
</div>

<div class="container">

    <?php if ($msg): ?>
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-circle-xmark"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (empty($cart)): ?>
    <div class="empty-state">
        <i class="fa-solid fa-cart-shopping"></i>
        <h3>Your cart is empty</h3>
        <p>Add some products to get started.</p>
        <a href="<?= APP_URL ?>/product/products.php" class="btn btn-primary">
            <i class="fa-solid fa-store"></i> Browse Products
        </a>
    </div>

    <?php else: ?>

    <div class="cart-layout">

        <!-- Cart Table -->
        <div class="cart-items-section">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cart as $i => $item):
                          $subtotal = $item['price'] * $item['quantity'];
                    ?>
                    <tr>
                        <!-- Product -->
                        <td>
                            <div class="cart-product-cell">
                                <img src="<?= APP_URL ?>/assets/images/<?= htmlspecialchars($item['image'] ?: 'placeholder.jpg') ?>"
                                     alt="<?= htmlspecialchars($item['name']) ?>"
                                     onerror="this.src='<?= APP_URL ?>/assets/images/placeholder.jpg'"
                                     class="cart-thumb">
                                <div>
                                    <strong><?= htmlspecialchars($item['name']) ?></strong>
                                    <small><?= htmlspecialchars($item['category'] ?? '') ?></small>
                                </div>
                            </div>
                        </td>

                        <!-- Unit price -->
                        <td>$<?= number_format($item['price'], 2) ?></td>

                        <!-- Quantity (inline update) -->
                        <td>
                            <form method="POST" action="" class="qty-form">
                                <input type="hidden" name="index" value="<?= $i ?>">
                                <div class="qty-control-sm">
                                    <button type="button" onclick="changeQtySm(this,-1)">−</button>
                                    <input type="number" name="quantity"
                                           value="<?= (int)$item['quantity'] ?>"
                                           min="1" class="qty-input-sm"
                                           onchange="this.form.submit()">
                                    <button type="button" onclick="changeQtySm(this,1)">+</button>
                                </div>
                                <input type="hidden" name="update_qty" value="1">
                            </form>
                        </td>

                        <!-- Subtotal -->
                        <td><strong>$<?= number_format($subtotal, 2) ?></strong></td>

                        <!-- Remove -->
                        <td>
                            <form method="POST" action="<?= APP_URL ?>/cart/remove.php">
                                <input type="hidden" name="index" value="<?= $i ?>">
                                <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Remove this item?')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Cart Summary -->
        <aside class="cart-summary">
            <div class="card">
                <div class="card-header"><h3>Order Summary</h3></div>
                <div class="card-body">
                    <div class="summary-row">
                        <span>Items (<?= array_sum(array_column($cart, 'quantity')) ?>)</span>
                        <span>$<?= number_format($total, 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span class="text-success">Free</span>
                    </div>
                    <hr>
                    <div class="summary-row summary-total">
                        <span>Total</span>
                        <span>$<?= number_format($total, 2) ?></span>
                    </div>
                    <a href="<?= APP_URL ?>/cart/checkout.php"
                       class="btn btn-primary btn-full mt-1">
                        <i class="fa-solid fa-credit-card"></i> Proceed to Checkout
                    </a>
                    <a href="<?= APP_URL ?>/product/products.php"
                       class="btn btn-secondary btn-full mt-05">
                        <i class="fa-solid fa-store"></i> Continue Shopping
                    </a>
                </div>
            </div>
        </aside>

    </div><!-- /.cart-layout -->
    <?php endif; ?>

</div><!-- /.container -->

<script>
function changeQtySm(btn, delta) {
    const input = btn.parentElement.querySelector('.qty-input-sm');
    let val = parseInt(input.value, 10) + delta;
    if (val < 1) val = 1;
    input.value = val;
    input.form.submit();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
