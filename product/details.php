<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
$pdo = getDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare('SELECT p.*, pc.name as category_name FROM products p LEFT JOIN product_categories pc ON p.category_id = pc.category_id WHERE p.product_id = ? AND p.is_active = 1');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: ' . APP_URL . '/product/products.php');
    exit;
}

$relStmt = $pdo->prepare('SELECT p.* FROM products p WHERE (p.category_id = ? OR p.category = ?) AND p.product_id != ? AND p.is_active = 1 ORDER BY RAND() LIMIT 4');
$relStmt->execute([$product['category_id'], $product['category'], $id]);
$related = $relStmt->fetchAll();

$cartMsg   = $_SESSION['cart_msg']   ?? '';
$cartError = $_SESSION['cart_error'] ?? '';
unset($_SESSION['cart_msg'], $_SESSION['cart_error']);

require_once __DIR__ . '/../includes/public_header.php';
?>

<section class="section-padding">
    <div class="container">

        <?php if ($cartMsg): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" data-aos="fade-up"><i class="fas fa-check-circle"></i> <?= h($cartMsg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($cartError): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" data-aos="fade-up"><i class="fas fa-exclamation-circle"></i> <?= h($cartError) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <nav aria-label="breadcrumb" data-aos="fade-up">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/product/products.php">Products</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/product/products.php?category=<?= $product['category_id'] ?>"><?= h($product['category_name'] ?? $product['category']) ?></a></li>
                <li class="breadcrumb-item active"><?= h($product['name']) ?></li>
            </ol>
        </nav>

        <div class="row g-5" data-aos="fade-up">
            <div class="col-lg-6">
                <div class="detail-img-wrap rounded-4 shadow-lg position-relative overflow-hidden">
                    <img src="<?= imgUrl($product['image'] ?? '') ?>" alt="<?= h($product['name']) ?>" class="img-fluid w-100 h-100" style="object-fit: cover;" onerror="this.src='<?= APP_URL ?>/assets/images/placeholder.jpg'">
                    <?php if ($product['stock'] == 0): ?>
                    <div class="overlay-out">Out of Stock</div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($product['brochure'])): ?>
                <div class="mt-3">
                    <a href="<?= imgUrl($product['brochure']) ?>" target="_blank" class="btn btn-outline w-100"><i class="fas fa-file-pdf"></i> Download Brochure</a>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-6">
                <span class="product-cat-badge"><?= h($product['category_name'] ?? $product['category']) ?></span>
                <h1 class="detail-name fw-bold mt-3"><?= h($product['name']) ?></h1>
                <div class="detail-price">$<?= number_format($product['price'], 2) ?></div>

                <div class="detail-stock <?= $product['stock'] > 0 ? 'in-stock' : 'no-stock' ?> my-3">
                    <i class="fas fa-<?= $product['stock'] > 0 ? 'circle-check' : 'circle-xmark' ?>"></i>
                    <?php if ($product['stock'] > 10): ?>
                    In Stock (<?= $product['stock'] ?> available)
                    <?php elseif ($product['stock'] > 0): ?>
                    <span class="text-warning">Low Stock – Only <?= $product['stock'] ?> left!</span>
                    <?php else: ?>
                    Out of Stock
                    <?php endif; ?>
                </div>

                <p class="detail-description"><?= nl2br(h($product['description'])) ?></p>

                <?php if (isLoggedIn()): ?>
                    <?php if ($product['stock'] > 0): ?>
                    <form method="POST" action="<?= APP_URL ?>/cart/add.php" class="detail-add-form bg-light rounded-4 p-4">
                        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                        <div class="qty-row">
                            <label for="qty">Quantity:</label>
                            <div class="qty-control">
                                <button type="button" onclick="changeQty(-1)">−</button>
                                <input type="number" id="qty" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                                <button type="button" onclick="changeQty(1)">+</button>
                            </div>
                            <span class="max-hint">Max: <?= $product['stock'] ?></span>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100"><i class="fas fa-cart-plus"></i> Add to Cart</button>
                    </form>
                    <?php else: ?>
                    <button class="btn btn-secondary btn-lg w-100" disabled><i class="fas fa-cart-shopping"></i> Out of Stock</button>
                    <?php endif; ?>
                <?php else: ?>
                <div class="mt-4 p-4 bg-light rounded-4">
                    <a href="<?= APP_URL ?>/user/auth.php" class="btn btn-primary btn-lg w-100"><i class="fas fa-sign-in-alt"></i> Login to Purchase</a>
                </div>
                <?php endif; ?>

                <div class="detail-meta mt-4">
                    <span><i class="fas fa-tag"></i> ID: #<?= $product['product_id'] ?></span>
                    <span><i class="fas fa-folder"></i> <?= h($product['category_name'] ?? $product['category']) ?></span>
                </div>
            </div>
        </div>

        <?php if (!empty($related)): ?>
        <section class="related-section mt-5 pt-5 border-top" data-aos="fade-up">
            <h2 class="section-title fw-bold mb-4">Related Products</h2>
            <div class="row g-3">
                <?php foreach ($related as $ri => $r): ?>
                <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="<?= $ri * 100 ?>">
                    <div class="product-card">
                        <a href="<?= APP_URL ?>/product/details.php?id=<?= $r['product_id'] ?>">
                            <div class="product-img-wrap">
                                <img src="<?= imgUrl($r['image'] ?? '') ?>" alt="<?= h($r['name']) ?>" loading="lazy" onerror="this.src='<?= APP_URL ?>/assets/images/placeholder.jpg'">
                            </div>
                        </a>
                        <div class="product-info">
                            <h3 class="product-name"><a href="<?= APP_URL ?>/product/details.php?id=<?= $r['product_id'] ?>"><?= h($r['name']) ?></a></h3>
                            <div class="product-price">
                                <span class="current-price">$<?= number_format($r['price'], 2) ?></span>
                            </div>
                            <?php if (isLoggedIn()): ?>
                            <form method="POST" action="<?= APP_URL ?>/cart/add.php" class="mt-2">
                                <input type="hidden" name="product_id" value="<?= $r['product_id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn-add-cart" <?= $r['stock'] == 0 ? 'disabled' : '' ?>><i class="fas fa-cart-plus"></i> Add to Cart</button>
                            </form>
                            <?php else: ?>
                            <a href="<?= APP_URL ?>/user/auth.php" class="btn btn-outline btn-sm w-100 mt-2"><i class="fas fa-sign-in-alt"></i> Login</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

    </div>
</section>

<script>
function changeQty(delta) {
    const input = document.getElementById('qty');
    if (!input) return;
    const max = parseInt(input.max, 10);
    let val = parseInt(input.value, 10) + delta;
    if (val < 1) val = 1;
    if (val > max) val = max;
    input.value = val;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
