<?php
// ============================================================
// product/details.php – Product Detail Page
// ============================================================

require_once __DIR__ . '/../includes/session.php';
requireLogin();

$pdo = getDB();
$id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare('SELECT * FROM products WHERE product_id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: ' . APP_URL . '/product/products.php');
    exit;
}

// Related products (same category, different id, limit 4)
$stmt = $pdo->prepare(
    'SELECT * FROM products WHERE category = ? AND product_id != ? ORDER BY RAND() LIMIT 4'
);
$stmt->execute([$product['category'], $id]);
$related = $stmt->fetchAll();

$cartMsg   = $_SESSION['cart_msg']   ?? '';
$cartError = $_SESSION['cart_error'] ?? '';
unset($_SESSION['cart_msg'], $_SESSION['cart_error']);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">

    <?php if ($cartMsg): ?>
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($cartMsg) ?></div>
    <?php endif; ?>
    <?php if ($cartError): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-circle-xmark"></i> <?= htmlspecialchars($cartError) ?></div>
    <?php endif; ?>

    <nav class="breadcrumb" aria-label="breadcrumb">
        <a href="<?= APP_URL ?>/product/products.php">Products</a>
        <span>/</span>
        <a href="<?= APP_URL ?>/product/products.php?category=<?= urlencode($product['category']) ?>">
            <?= htmlspecialchars($product['category']) ?>
        </a>
        <span>/</span>
        <span><?= htmlspecialchars($product['name']) ?></span>
    </nav>

    <!-- ── Product detail layout ── -->
    <div class="product-detail-grid">

        <!-- Image -->
        <div class="product-detail-image">
            <div class="detail-img-wrap">
                <img src="<?= APP_URL ?>/assets/images/<?= htmlspecialchars($product['image'] ?: 'placeholder.jpg') ?>"
                     alt="<?= htmlspecialchars($product['name']) ?>"
                     onerror="this.src='<?= APP_URL ?>/assets/images/placeholder.jpg'">
                <?php if ($product['stock'] === 0): ?>
                <div class="overlay-out">Out of Stock</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Info -->
        <div class="product-detail-info">
            <span class="product-cat-badge"><?= htmlspecialchars($product['category']) ?></span>
            <h1 class="detail-name"><?= htmlspecialchars($product['name']) ?></h1>

            <div class="detail-price">$<?= number_format($product['price'], 2) ?></div>

            <div class="detail-stock <?= $product['stock'] > 0 ? 'in-stock' : 'no-stock' ?>">
                <i class="fa-solid fa-<?= $product['stock'] > 0 ? 'circle-check' : 'circle-xmark' ?>"></i>
                <?php if ($product['stock'] > 10): ?>
                In Stock (<?= $product['stock'] ?> available)
                <?php elseif ($product['stock'] > 0): ?>
                Low Stock – Only <?= $product['stock'] ?> left!
                <?php else: ?>
                Out of Stock
                <?php endif; ?>
            </div>

            <p class="detail-description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

            <?php if ($product['stock'] > 0): ?>
            <form method="POST" action="<?= APP_URL ?>/cart/add.php" class="detail-add-form">
                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                <div class="qty-row">
                    <label for="qty">Quantity:</label>
                    <div class="qty-control">
                        <button type="button" onclick="changeQty(-1)">−</button>
                        <input type="number" id="qty" name="quantity"
                               value="1" min="1" max="<?= $product['stock'] ?>">
                        <button type="button" onclick="changeQty(1)">+</button>
                    </div>
                    <span class="max-hint">Max: <?= $product['stock'] ?></span>
                </div>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fa-solid fa-cart-plus"></i> Add to Cart
                </button>
            </form>
            <?php else: ?>
            <button class="btn btn-secondary btn-lg" disabled>
                <i class="fa-solid fa-cart-shopping"></i> Out of Stock
            </button>
            <?php endif; ?>

            <div class="detail-meta">
                <span><i class="fa-solid fa-tag"></i> ID: #<?= $product['product_id'] ?></span>
                <span><i class="fa-solid fa-folder"></i> <?= htmlspecialchars($product['category']) ?></span>
            </div>

            <div class="detail-admin-actions">
                <a href="<?= APP_URL ?>/product/manage.php?action=edit&id=<?= $product['product_id'] ?>"
                   class="btn btn-warning btn-sm">
                    <i class="fa-solid fa-pen"></i> Edit Product
                </a>
            </div>
        </div>
    </div>

    <!-- ── Related products ── -->
    <?php if (!empty($related)): ?>
    <section class="related-section">
        <h2 class="section-title">Related Products</h2>
        <div class="product-grid product-grid--small">
            <?php foreach ($related as $r): ?>
            <div class="product-card">
                <a href="<?= APP_URL ?>/product/details.php?id=<?= $r['product_id'] ?>"
                   class="product-img-link">
                    <div class="product-img-wrap">
                        <img src="<?= APP_URL ?>/assets/images/<?= htmlspecialchars($r['image'] ?: 'placeholder.jpg') ?>"
                             alt="<?= htmlspecialchars($r['name']) ?>"
                             onerror="this.src='<?= APP_URL ?>/assets/images/placeholder.jpg'"
                             loading="lazy">
                    </div>
                </a>
                <div class="product-info">
                    <h3 class="product-name">
                        <a href="<?= APP_URL ?>/product/details.php?id=<?= $r['product_id'] ?>">
                            <?= htmlspecialchars($r['name']) ?>
                        </a>
                    </h3>
                    <div class="product-footer">
                        <span class="product-price">$<?= number_format($r['price'], 2) ?></span>
                    </div>
                    <form method="POST" action="<?= APP_URL ?>/cart/add.php">
                        <input type="hidden" name="product_id" value="<?= $r['product_id'] ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn btn-primary btn-sm"
                            <?= $r['stock'] === 0 ? 'disabled' : '' ?>>
                            <i class="fa-solid fa-cart-plus"></i> Add to Cart
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

</div>

<script>
function changeQty(delta) {
    const input = document.getElementById('qty');
    const max   = parseInt(input.max, 10);
    let val = parseInt(input.value, 10) + delta;
    if (val < 1)   val = 1;
    if (val > max) val = max;
    input.value = val;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
