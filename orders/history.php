<?php
// ============================================================
// orders/history.php – Order History with Delivery Countdown
// ============================================================

require_once __DIR__ . '/../includes/session.php';
requireLogin();

$pdo    = getDB();
$userId = $_SESSION['user'];

// Fetch orders with item details
// $stmt = $pdo->prepare(
//     'SELECT o.*,
//             GROUP_CONCAT(p.name ORDER BY oi.item_id SEPARATOR "||") AS product_names,
//             GROUP_CONCAT(oi.quantity ORDER BY oi.item_id SEPARATOR "||") AS quantities,
//             GROUP_CONCAT(oi.price ORDER BY oi.item_id SEPARATOR "||")    AS item_prices,
//             GROUP_CONCAT(p.image ORDER BY oi.item_id SEPARATOR "||")     AS images
//        FROM orders o
//        JOIN order_items oi ON oi.order_id = o.order_id
//        JOIN products   p  ON p.product_id = oi.product_id
//       WHERE o.user_id = ?
//       GROUP BY o.order_id
//       ORDER BY o.order_date DESC'
// );
$stmt = $pdo->prepare(
    'SELECT
        o.*,
        GROUP_CONCAT(p.name, "||") AS product_names,
        GROUP_CONCAT(oi.quantity, "||") AS quantities,
        GROUP_CONCAT(oi.price, "||") AS item_prices,
        GROUP_CONCAT(p.image, "||") AS images
     FROM orders o
     JOIN order_items oi ON oi.order_id = o.order_id
     JOIN products p ON p.product_id = oi.product_id
     WHERE o.user_id = ?
     GROUP BY o.order_id
     ORDER BY o.order_date DESC'
);
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1><i class="fa-solid fa-box"></i> My Orders</h1>
    <p>Track your purchases and delivery status</p>
</div>

<div class="container">

<?php if (empty($orders)): ?>
<div class="empty-state">
    <i class="fa-solid fa-box-open"></i>
    <h3>No orders yet</h3>
    <p>Your order history will appear here after your first purchase.</p>
    <a href="<?= APP_URL ?>/product/products.php" class="btn btn-primary">
        <i class="fa-solid fa-store"></i> Start Shopping
    </a>
</div>

<?php else: ?>

<div class="orders-list">
<?php foreach ($orders as $order):
    $productNames = explode('||', $order['product_names']);
    $quantities   = explode('||', $order['quantities']);
    $itemPrices   = explode('||', $order['item_prices']);
    $images       = explode('||', $order['images'] ?? '');

    // Estimated delivery: 3–5 days from order_date
    $orderTs   = strtotime($order['order_date']);
    $deliverTs = $orderTs + (4 * 86400); // 4 days estimate
    $nowTs     = time();
    $secsLeft  = $deliverTs - $nowTs;
    $delivered = $secsLeft <= 0;
?>
<div class="order-card">
    <div class="order-card-header">
        <div>
            <span class="order-id"><?= htmlspecialchars($order['order_id']) ?></span>
            <span class="order-date">
                <i class="fa-solid fa-calendar"></i>
                <?= date('d M Y, h:i A', strtotime($order['order_date'])) ?>
            </span>
        </div>
        <div class="order-badges">
            <span class="badge badge-payment
                <?= $order['payment_status'] === 'Completed' ? 'badge-success' : 'badge-warning' ?>">
                <i class="fa-solid fa-<?= $order['payment_status'] === 'Completed' ? 'circle-check' : 'clock' ?>"></i>
                <?= htmlspecialchars($order['payment_status']) ?>
            </span>
            <span class="badge badge-method">
                <i class="fa-solid fa-wallet"></i>
                <?= htmlspecialchars($order['payment_method']) ?>
            </span>
        </div>
    </div>

    <div class="order-card-body">

        <!-- Products -->
        <div class="order-products">
            <?php foreach ($productNames as $k => $pName): ?>
            <div class="order-product-row">
                <img src="<?= APP_URL ?>/assets/images/<?= htmlspecialchars($images[$k] ?? 'placeholder.jpg') ?>"
                     alt="<?= htmlspecialchars($pName) ?>"
                     onerror="this.src='<?= APP_URL ?>/assets/images/placeholder.jpg'"
                     class="cart-thumb-sm">
                <div class="order-product-info">
                    <strong><?= htmlspecialchars($pName) ?></strong>
                    <span>Qty: <?= (int)($quantities[$k] ?? 0) ?>
                          &nbsp;×&nbsp;
                          $<?= number_format((float)($itemPrices[$k] ?? 0), 2) ?>
                    </span>
                </div>
                <span class="order-item-sub">
                    $<?= number_format((int)$quantities[$k] * (float)$itemPrices[$k], 2) ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Total -->
        <div class="order-total-row">
            <span>Order Total:</span>
            <strong>$<?= number_format($order['total_amount'], 2) ?></strong>
        </div>

        <!-- Delivery countdown -->
        <div class="delivery-countdown-wrap">
            <?php if ($delivered): ?>
            <div class="delivery-delivered">
                <i class="fa-solid fa-circle-check"></i> Delivered
            </div>
            <?php else: ?>
            <div class="delivery-pending">
                <i class="fa-solid fa-truck-moving"></i>
                <span>Estimated Delivery in:</span>
            </div>
            <div class="countdown-timer"
                 data-delivery-ts="<?= $deliverTs ?>">
                <div class="cd-block">
                    <span class="cd-num" data-unit="days">0</span>
                    <span class="cd-label">Days</span>
                </div>
                <span class="cd-sep">:</span>
                <div class="cd-block">
                    <span class="cd-num" data-unit="hours">0</span>
                    <span class="cd-label">Hours</span>
                </div>
                <span class="cd-sep">:</span>
                <div class="cd-block">
                    <span class="cd-num" data-unit="minutes">0</span>
                    <span class="cd-label">Minutes</span>
                </div>
                <span class="cd-sep">:</span>
                <div class="cd-block">
                    <span class="cd-num" data-unit="seconds">0</span>
                    <span class="cd-label">Seconds</span>
                </div>
            </div>
            <?php endif; ?>
        </div>

    </div><!-- /.order-card-body -->
</div><!-- /.order-card -->
<?php endforeach; ?>
</div><!-- /.orders-list -->

<?php endif; ?>

</div><!-- /.container -->

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
