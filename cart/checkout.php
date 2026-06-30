<?php
// ============================================================
// cart/checkout.php – Checkout & Payment
// ============================================================

require_once __DIR__ . '/../includes/session.php';
requireLogin();

$pdo    = getDB();
$userId = $_SESSION['user'];
$cart   = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    header('Location: ' . APP_URL . '/cart/cart.php');
    exit;
}

$errors     = [];
$orderSuccess = false;
$orderId    = '';
$total      = 0.0;

foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

// ── Process checkout on POST ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = trim($_POST['payment_method'] ?? '');
    $cardNumber    = trim($_POST['card_number']    ?? '');
    $cardName      = trim($_POST['card_name']      ?? '');
    $cardExpiry    = trim($_POST['card_expiry']    ?? '');
    $cardCvv       = trim($_POST['card_cvv']       ?? '');

    $allowedPayments = ['Cash On Delivery', 'Card Payment', 'eSewa', 'Khalti'];
    if (!in_array($paymentMethod, $allowedPayments)) {
        $errors[] = 'Please select a valid payment method.';
    }

    // Card validation (simulation)
    if ($paymentMethod === 'Card Payment') {
        if (empty($cardNumber) || strlen(preg_replace('/\s+/', '', $cardNumber)) < 16)
            $errors[] = 'Enter a valid 16-digit card number.';
        if (empty($cardName))
            $errors[] = 'Cardholder name is required.';
        if (empty($cardExpiry) || !preg_match('/^\d{2}\/\d{2}$/', $cardExpiry))
            $errors[] = 'Enter card expiry in MM/YY format.';
        if (empty($cardCvv) || !preg_match('/^\d{3,4}$/', $cardCvv))
            $errors[] = 'Enter a valid CVV (3–4 digits).';
    }

    if (empty($errors)) {
        $pdo->beginTransaction();
        try {
            // 1. Re-verify stock
            foreach ($cart as $item) {
                // $stmt = $pdo->prepare('SELECT stock, name FROM products WHERE product_id = ? FOR UPDATE');
                // $stmt->execute([(int)$item['product_id']]);
                // $p = $stmt->fetch();
                $stmt = $pdo->prepare('SELECT stock, name FROM products WHERE product_id = ?');
                $stmt->execute([(int)$item['product_id']]);
                $p = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$p) throw new Exception('Product #' . $item['product_id'] . ' not found.');
                if ($p['stock'] < $item['quantity']) {
                    throw new Exception(
                        '"' . $p['name'] . '" only has ' . $p['stock'] . ' units in stock.'
                    );
                }
            }

            // 2. Create order
            $orderId       = 'ORD-' . strtoupper(bin2hex(random_bytes(6)));
            $paymentStatus = ($paymentMethod === 'Cash On Delivery') ? 'Pending' : 'Completed';

            $pdo->prepare(
                'INSERT INTO orders (order_id, user_id, total_amount, payment_method, payment_status)
                 VALUES (?, ?, ?, ?, ?)'
            )->execute([$orderId, $userId, $total, $paymentMethod, $paymentStatus]);

            // 3. Insert order_items & reduce stock
            foreach ($cart as $item) {
                $pdo->prepare(
                    'INSERT INTO order_items (order_id, product_id, quantity, price)
                     VALUES (?, ?, ?, ?)'
                )->execute([$orderId, (int)$item['product_id'], (int)$item['quantity'], (float)$item['price']]);

                $pdo->prepare(
                    'UPDATE products SET stock = stock - ? WHERE product_id = ?'
                )->execute([(int)$item['quantity'], (int)$item['product_id']]);
            }

            $pdo->commit();

            // 4. Clear cart
            $_SESSION['cart'] = [];
            $orderSuccess = true;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Order failed: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1><i class="fa-solid fa-credit-card"></i> Checkout</h1>
</div>

<div class="container">

<?php if ($orderSuccess): ?>
<!-- ── Success ── -->
<div class="checkout-success">
    <div class="success-icon"><i class="fa-solid fa-circle-check"></i></div>
    <h2>Order Placed Successfully!</h2>
    <p>Your order <strong><?= htmlspecialchars($orderId) ?></strong> has been confirmed.</p>
    <p>Thank you for shopping with us.</p>
    <div class="success-actions">
        <a href="<?= APP_URL ?>/orders/history.php" class="btn btn-primary">
            <i class="fa-solid fa-box"></i> View Orders
        </a>
        <a href="<?= APP_URL ?>/product/products.php" class="btn btn-secondary">
            <i class="fa-solid fa-store"></i> Continue Shopping
        </a>
    </div>
</div>

<?php else: ?>

<?php if ($errors): ?>
<div class="alert alert-danger">
    <i class="fa-solid fa-circle-xmark"></i>
    <ul class="mb-0">
        <?php foreach ($errors as $e): ?>
        <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST" action="" id="checkoutForm">
<div class="checkout-layout">

    <!-- ── Left: Payment ── -->
    <div class="checkout-main">

        <div class="card">
            <div class="card-header"><h3><i class="fa-solid fa-wallet"></i> Payment Method</h3></div>
            <div class="card-body">

                <div class="payment-options">

                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="Cash On Delivery"
                               <?= ($_POST['payment_method'] ?? '') === 'Cash On Delivery' ? 'checked' : '' ?>
                               onchange="showPaymentFields(this.value)" required>
                        <div class="payment-option-content">
                            <i class="fa-solid fa-truck"></i>
                            <div>
                                <strong>Cash On Delivery</strong>
                                <span>Pay when your order arrives</span>
                            </div>
                        </div>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="Card Payment"
                               <?= ($_POST['payment_method'] ?? '') === 'Card Payment' ? 'checked' : '' ?>
                               onchange="showPaymentFields(this.value)">
                        <div class="payment-option-content">
                            <i class="fa-solid fa-credit-card"></i>
                            <div>
                                <strong>Card Payment</strong>
                                <span>Visa / Mastercard / Amex</span>
                            </div>
                        </div>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="eSewa"
                               <?= ($_POST['payment_method'] ?? '') === 'eSewa' ? 'checked' : '' ?>
                               onchange="showPaymentFields(this.value)">
                        <div class="payment-option-content">
                            <span class="pay-logo pay-esewa">e</span>
                            <div>
                                <strong>eSewa</strong>
                                <span>Nepal's leading digital wallet</span>
                            </div>
                        </div>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="Khalti"
                               <?= ($_POST['payment_method'] ?? '') === 'Khalti' ? 'checked' : '' ?>
                               onchange="showPaymentFields(this.value)">
                        <div class="payment-option-content">
                            <span class="pay-logo pay-khalti">K</span>
                            <div>
                                <strong>Khalti</strong>
                                <span>Fast & secure digital payment</span>
                            </div>
                        </div>
                    </label>

                </div><!-- /.payment-options -->

                <!-- Card fields (shown conditionally) -->
                <div id="cardFields" class="card-fields" style="display:none">
                    <h4><i class="fa-solid fa-credit-card"></i> Card Details</h4>
                    <div class="form-group">
                        <label>Card Number</label>
                        <input type="text" name="card_number" maxlength="19"
                               placeholder="1234 5678 9012 3456"
                               value="<?= htmlspecialchars($_POST['card_number'] ?? '') ?>"
                               oninput="formatCard(this)">
                    </div>
                    <div class="form-group">
                        <label>Cardholder Name</label>
                        <input type="text" name="card_name"
                               placeholder="Full name as on card"
                               value="<?= htmlspecialchars($_POST['card_name'] ?? '') ?>">
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>Expiry Date</label>
                            <input type="text" name="card_expiry" maxlength="5"
                                   placeholder="MM/YY"
                                   value="<?= htmlspecialchars($_POST['card_expiry'] ?? '') ?>"
                                   oninput="formatExpiry(this)">
                        </div>
                        <div class="form-group">
                            <label>CVV</label>
                            <input type="password" name="card_cvv" maxlength="4"
                                   placeholder="•••">
                        </div>
                    </div>
                    <div class="card-simulation-note">
                        <i class="fa-solid fa-shield-halved"></i>
                        This is a <strong>simulated</strong> payment. Enter any 16-digit number.
                    </div>
                </div>

                <!-- eSewa simulation -->
                <div id="esewaFields" class="digital-wallet-fields" style="display:none">
                    <div class="wallet-simulation">
                        <span class="pay-logo pay-esewa pay-logo--lg">e</span>
                        <p>You will be redirected to <strong>eSewa</strong> to complete payment.</p>
                        <p class="sim-note"><i class="fa-solid fa-circle-info"></i> Simulation mode – no real transaction.</p>
                    </div>
                </div>

                <!-- Khalti simulation -->
                <div id="khaltiFields" class="digital-wallet-fields" style="display:none">
                    <div class="wallet-simulation">
                        <span class="pay-logo pay-khalti pay-logo--lg">K</span>
                        <p>You will be redirected to <strong>Khalti</strong> to complete payment.</p>
                        <p class="sim-note"><i class="fa-solid fa-circle-info"></i> Simulation mode – no real transaction.</p>
                    </div>
                </div>

            </div>
        </div>

        <!-- Order items summary -->
        <div class="card">
            <div class="card-header"><h3><i class="fa-solid fa-bag-shopping"></i> Items in Order</h3></div>
            <div class="card-body">
                <table class="data-table data-table--compact">
                    <thead>
                        <tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cart as $item): ?>
                    <tr>
                        <td>
                            <div class="cart-product-cell">
                                <img src="<?= APP_URL ?>/assets/images/<?= htmlspecialchars($item['image'] ?: 'placeholder.jpg') ?>"
                                     alt="" class="cart-thumb-sm"
                                     onerror="this.src='<?= APP_URL ?>/assets/images/placeholder.jpg'">
                                <?= htmlspecialchars($item['name']) ?>
                            </div>
                        </td>
                        <td><?= (int)$item['quantity'] ?></td>
                        <td>$<?= number_format($item['price'], 2) ?></td>
                        <td><strong>$<?= number_format($item['price'] * $item['quantity'], 2) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div><!-- /.checkout-main -->

    <!-- ── Right: Summary ── -->
    <aside class="cart-summary">
        <div class="card sticky-card">
            <div class="card-header"><h3>Order Total</h3></div>
            <div class="card-body">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>$<?= number_format($total, 2) ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span class="text-success">Free</span>
                </div>
                <div class="summary-row">
                    <span>Tax (0%)</span>
                    <span>$0.00</span>
                </div>
                <hr>
                <div class="summary-row summary-total">
                    <span>Grand Total</span>
                    <span>$<?= number_format($total, 2) ?></span>
                </div>

                <button type="submit" class="btn btn-primary btn-full mt-1" id="placeOrderBtn">
                    <i class="fa-solid fa-lock"></i> Place Order
                </button>
                <a href="<?= APP_URL ?>/cart/cart.php" class="btn btn-secondary btn-full mt-05">
                    <i class="fa-solid fa-arrow-left"></i> Back to Cart
                </a>
            </div>
        </div>
    </aside>

</div><!-- /.checkout-layout -->
</form>

<?php endif; ?>

</div><!-- /.container -->

<script>
function showPaymentFields(method) {
    document.getElementById('cardFields').style.display   = 'none';
    document.getElementById('esewaFields').style.display  = 'none';
    document.getElementById('khaltiFields').style.display = 'none';
    if (method === 'Card Payment') document.getElementById('cardFields').style.display   = 'block';
    if (method === 'eSewa')        document.getElementById('esewaFields').style.display  = 'block';
    if (method === 'Khalti')       document.getElementById('khaltiFields').style.display = 'block';
}

function formatCard(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 16);
    input.value = v.replace(/(.{4})/g, '$1 ').trim();
}

function formatExpiry(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 4);
    if (v.length >= 3) v = v.substring(0,2) + '/' + v.substring(2);
    input.value = v;
}

// Restore card fields if page reloads with errors
(function() {
    const checked = document.querySelector('input[name="payment_method"]:checked');
    if (checked) showPaymentFields(checked.value);
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
