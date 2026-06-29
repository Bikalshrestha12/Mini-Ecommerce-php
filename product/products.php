<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
$pdo = getDB();

$msg   = $_SESSION['cart_msg']    ?? '';
$error = $_SESSION['cart_error']  ?? '';
unset($_SESSION['cart_msg'], $_SESSION['cart_error']);

$perPage = 12;
$page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset  = ($page - 1) * $perPage;

$filterCat   = $_GET['category'] ?? '';
$sortBy      = $_GET['sort']     ?? 'default';
$search      = trim($_GET['search'] ?? '');

$allowedSorts = ['default', 'price_asc', 'price_desc', 'name_asc'];
if (!in_array($sortBy, $allowedSorts)) $sortBy = 'default';

$conditions = ['p.is_active = 1'];
$params = [];

if (!empty($filterCat)) {
    $conditions[] = 'p.category_id = ?';
    $params[] = (int)$filterCat;
}

if (!empty($search)) {
    $conditions[] = '(p.name LIKE ? OR p.description LIKE ?)';
    $s = '%' . $search . '%';
    $params[] = $s;
    $params[] = $s;
}

$where = 'WHERE ' . implode(' AND ', $conditions);

switch ($sortBy) {
    case 'price_asc':  $orderBy = 'ORDER BY p.price ASC'; break;
    case 'price_desc': $orderBy = 'ORDER BY p.price DESC'; break;
    case 'name_asc':   $orderBy = 'ORDER BY p.name ASC'; break;
    default:           $orderBy = 'ORDER BY p.product_id DESC'; break;
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p $where");
$countStmt->execute($params);
$totalProducts = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalProducts / $perPage));

$stmt = $pdo->prepare("SELECT p.*, pc.name as category_name FROM products p LEFT JOIN product_categories pc ON p.category_id = pc.category_id $where $orderBy LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM product_categories WHERE is_active = 1 ORDER BY name")->fetchAll();

$urlPattern = APP_URL . '/product/products.php?page={page}';
if (!empty($filterCat)) $urlPattern .= '&category=' . urlencode($filterCat);
if (!empty($search)) $urlPattern .= '&search=' . urlencode($search);
if ($sortBy !== 'default') $urlPattern .= '&sort=' . urlencode($sortBy);

require_once __DIR__ . '/../includes/public_header.php';
?>

<div class="page-banner">
    <div class="container">
        <h1 data-aos="fade-up"><i class="fas fa-store"></i> Our Products</h1>
        <p data-aos="fade-up" data-aos-delay="100">Discover our latest collection</p>
    </div>
</div>

<section class="section-padding">
    <div class="container">

        <?php if ($msg): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" data-aos="fade-up"><i class="fas fa-check-circle"></i> <?= h($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" data-aos="fade-up"><i class="fas fa-exclamation-circle"></i> <?= h($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <form method="GET" action="" class="bg-white rounded-4 shadow-sm p-4 mb-4" data-aos="fade-up">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-muted small text-uppercase"><i class="fas fa-search"></i> Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search products..." value="<?= h($search) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted small text-uppercase"><i class="fas fa-tag"></i> Category</label>
                    <select name="category" class="form-select" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['category_id'] ?>" <?= $filterCat == $c['category_id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted small text-uppercase"><i class="fas fa-sort"></i> Sort By</label>
                    <select name="sort" class="form-select" onchange="this.form.submit()">
                        <option value="default" <?= $sortBy === 'default' ? 'selected' : '' ?>>Latest</option>
                        <option value="price_asc" <?= $sortBy === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_desc" <?= $sortBy === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="name_asc" <?= $sortBy === 'name_asc' ? 'selected' : '' ?>>Name A-Z</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted small text-uppercase">&nbsp;</label>
                    <a href="<?= APP_URL ?>/product/products.php" class="btn btn-outline w-100"><i class="fas fa-undo"></i> Reset</a>
                </div>
            </div>
        </form>

        <p class="text-muted mb-4" data-aos="fade-up">Showing <strong class="text-dark"><?= count($products) ?></strong> of <strong class="text-dark"><?= $totalProducts ?></strong> products</p>

        <?php if (empty($products)): ?>
        <div class="empty-state" data-aos="fade-up">
            <i class="fas fa-box-open"></i>
            <h3>No products found</h3>
            <p>Try adjusting your search or filter criteria.</p>
            <a href="<?= APP_URL ?>/product/products.php" class="btn btn-primary">Clear Filters</a>
        </div>
        <?php else: ?>
        <div class="row g-3">
            <?php foreach ($products as $i => $p): ?>
            <div class="col-6 col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="<?= ($i % 12) * 50 ?>">
                <div class="product-card">
                    <a href="<?= APP_URL ?>/product/details.php?id=<?= $p['product_id'] ?>">
                        <div class="product-img-wrap">
                            <img src="<?= imgUrl($p['image'] ?? '') ?>" alt="<?= h($p['name']) ?>" loading="lazy" onerror="this.src='<?= APP_URL ?>/assets/images/placeholder.jpg'">
                            <?php if ($p['stock'] == 0): ?>
                            <span class="product-badge sale">Out of Stock</span>
                            <?php elseif ($p['stock'] <= 5): ?>
                            <span class="product-badge new">Only <?= $p['stock'] ?> left</span>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="product-info">
                        <span class="product-category"><?= h($p['category_name'] ?? $p['category']) ?></span>
                        <h3 class="product-name"><a href="<?= APP_URL ?>/product/details.php?id=<?= $p['product_id'] ?>"><?= h($p['name']) ?></a></h3>
                        <div class="product-price">
                            <span class="current-price">$<?= number_format($p['price'], 2) ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-auto pt-2">
                            <span class="badge <?= $p['stock'] > 0 ? 'badge-success' : 'badge-danger' ?> badge-sm">
                                <i class="fas fa-warehouse me-1"></i> <?= $p['stock'] ?> in stock
                            </span>
                        </div>
                        <?php if (isLoggedIn()): ?>
                        <form method="POST" action="<?= APP_URL ?>/cart/add.php" class="mt-3">
                            <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn-add-cart" <?= $p['stock'] == 0 ? 'disabled' : '' ?>>
                                <i class="fas fa-cart-plus"></i> <?= $p['stock'] == 0 ? 'Out of Stock' : 'Add to Cart' ?>
                            </button>
                        </form>
                        <?php else: ?>
                        <a href="<?= APP_URL ?>/user/auth.php" class="btn btn-outline btn-sm w-100 mt-3">
                            <i class="fas fa-sign-in-alt"></i> Login to Purchase
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?= pagination($page, $totalPages, $urlPattern) ?>
        <?php endif; ?>

    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
