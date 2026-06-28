<?php
// ============================================================
// product/products.php – Product Listing + Filter + Sort + CRUD Admin
// ============================================================

require_once __DIR__ . '/../includes/session.php';
requireLogin();

$pdo = getDB();
$msg   = $_SESSION['cart_msg']    ?? '';
$error = $_SESSION['cart_error']  ?? '';
unset($_SESSION['cart_msg'], $_SESSION['cart_error']);

$adminMsg   = $_SESSION['admin_msg']   ?? '';
$adminError = $_SESSION['admin_error'] ?? '';
unset($_SESSION['admin_msg'], $_SESSION['admin_error']);

// ── ADMIN: Delete product ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product_id'])) {
    $delId = (int)$_POST['delete_product_id'];
    try {
        // Remove from cart sessions if present (best-effort; session-based carts need no DB cleanup)
        $pdo->prepare('DELETE FROM products WHERE product_id = ?')->execute([$delId]);
        $_SESSION['admin_msg'] = 'Product deleted successfully.';
    } catch (PDOException $e) {
        $_SESSION['admin_error'] = 'Cannot delete: product may be referenced in existing orders.';
    }
    header('Location: ' . APP_URL . '/product/products.php');
    exit;
}

// ── Filtering & Sorting ──────────────────────────────────────
$allowedCategories = ['All', 'Top', 'Bottom', 'Shoe', 'Accessories'];
$allowedSorts      = ['default', 'price_asc', 'price_desc', 'name_asc', 'stock_asc'];

$filterCat = $_GET['category'] ?? 'All';
$sortBy    = $_GET['sort']     ?? 'default';
$search    = trim($_GET['search'] ?? '');

if (!in_array($filterCat, $allowedCategories)) $filterCat = 'All';
if (!in_array($sortBy,    $allowedSorts))      $sortBy    = 'default';

// Build query
$conditions = [];
$params     = [];

if ($filterCat !== 'All') {
    $conditions[] = 'category = ?';
    $params[]     = $filterCat;
}

if (!empty($search)) {
    $conditions[] = '(name LIKE ? OR description LIKE ?)';
    $params[]     = '%' . $search . '%';
    $params[]     = '%' . $search . '%';
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

$orderBy = match($sortBy) {
    'price_asc'  => 'ORDER BY price ASC',
    'price_desc' => 'ORDER BY price DESC',
    'name_asc'   => 'ORDER BY name ASC',
    'stock_asc'  => 'ORDER BY stock ASC',
    default      => 'ORDER BY product_id ASC',
};

$stmt = $pdo->prepare("SELECT * FROM products $where $orderBy");
$stmt->execute($params);
$products = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-store"></i> Our Products</h1>
        <p>Discover our latest collection</p>
    </div>
    <a href="<?= APP_URL ?>/product/manage.php?action=add" class="btn btn-success btn-sm">
        <i class="fas fa-plus"></i> Add Product
    </a>
</div>

<div class="container-fluid px-4">

    <?php if ($msg): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    <?php if ($adminMsg): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($adminMsg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    <?php if ($adminError): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($adminError) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- ── Controls bar ── -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3" id="filterForm">
                <!-- Search -->
                <div class="col-lg-3">
                    <label for="searchInput" class="form-label">Search</label>
                    <input type="text" name="search" id="searchInput"
                       placeholder="Search products…"
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>

            <!-- Category filter -->
            <div class="filter-group">
                <label><i class="fa-solid fa-filter"></i> Category</label>
                <div class="filter-pills">
                    <?php foreach ($allowedCategories as $cat): ?>
                    <a href="?category=<?= urlencode($cat) ?>&sort=<?= urlencode($sortBy) ?>&search=<?= urlencode($search) ?>"
                       class="pill <?= $filterCat === $cat ? 'pill-active' : '' ?>">
                        <?= htmlspecialchars($cat) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Sort -->
            <div class="filter-group">
                <label for="sortSelect"><i class="fa-solid fa-arrow-up-a-z"></i> Sort</label>
                <select id="sortSelect" name="sort"
                        onchange="this.form.submit()">
                    <option value="default"    <?= $sortBy==='default'    ? 'selected':'' ?>>Default</option>
                    <option value="price_asc"  <?= $sortBy==='price_asc'  ? 'selected':'' ?>>Price: Low → High</option>
                    <option value="price_desc" <?= $sortBy==='price_desc' ? 'selected':'' ?>>Price: High → Low</option>
                    <option value="name_asc"   <?= $sortBy==='name_asc'   ? 'selected':'' ?>>Name A–Z</option>
                    <option value="stock_asc"  <?= $sortBy==='stock_asc'  ? 'selected':'' ?>>Stock: Low → High</option>
                </select>
            </div>
        </form>

        <!-- Add Product button -->
        <a href="<?= APP_URL ?>/product/manage.php?action=add"
           class="btn btn-success">
            <i class="fa-solid fa-plus"></i> Add Product
        </a>
    </div>

    <p class="results-count">
        Showing <strong><?= count($products) ?></strong> product<?= count($products) !== 1 ? 's' : '' ?>
        <?= $filterCat !== 'All' ? 'in <strong>' . htmlspecialchars($filterCat) . '</strong>' : '' ?>
        <?= !empty($search) ? 'for "<strong>' . htmlspecialchars($search) . '</strong>"' : '' ?>
    </p>

    <!-- ── Product grid ── -->
    <?php if (empty($products)): ?>
    <div class="empty-state">
        <i class="fa-solid fa-box-open"></i>
        <h3>No products found</h3>
        <p>Try adjusting your filters.</p>
        <a href="<?= APP_URL ?>/product/products.php" class="btn btn-primary">Clear Filters</a>
    </div>
    <?php else: ?>
    <div class="product-grid">
        <?php foreach ($products as $p): ?>
        <div class="product-card" data-category="<?= htmlspecialchars($p['category']) ?>">

            <a href="<?= APP_URL ?>/product/details.php?id=<?= $p['product_id'] ?>"
               class="product-img-link">
                <div class="product-img-wrap">
                    <img src="<?= APP_URL ?>/assets/images/<?= htmlspecialchars($p['image'] ?: 'placeholder.jpg') ?>"
                         alt="<?= htmlspecialchars($p['name']) ?>"
                         onerror="this.src='<?= APP_URL ?>/assets/images/placeholder.jpg'"
                         loading="lazy">
                    <?php if ($p['stock'] === 0): ?>
                    <span class="badge-out">Out of Stock</span>
                    <?php elseif ($p['stock'] <= 5): ?>
                    <span class="badge-low">Only <?= $p['stock'] ?> left</span>
                    <?php endif; ?>
                </div>
            </a>

            <div class="product-info">
                <span class="product-cat"><?= htmlspecialchars($p['category']) ?></span>
                <h3 class="product-name">
                    <a href="<?= APP_URL ?>/product/details.php?id=<?= $p['product_id'] ?>">
                        <?= htmlspecialchars($p['name']) ?>
                    </a>
                </h3>
                <p class="product-desc"><?= htmlspecialchars(mb_substr($p['description'], 0, 80)) ?>…</p>

                <div class="product-footer">
                    <span class="product-price">$<?= number_format($p['price'], 2) ?></span>
                    <span class="product-stock <?= $p['stock'] > 0 ? 'in-stock' : 'no-stock' ?>">
                        <i class="fa-solid fa-warehouse"></i> <?= $p['stock'] ?>
                    </span>
                </div>

                <div class="product-actions">
                    <form method="POST" action="<?= APP_URL ?>/cart/add.php">
                        <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                        <input type="hidden" name="quantity"   value="1">
                        <button type="submit" class="btn btn-primary btn-sm"
                                <?= $p['stock'] === 0 ? 'disabled' : '' ?>>
                            <i class="fa-solid fa-cart-plus"></i>
                            <?= $p['stock'] === 0 ? 'Out of Stock' : 'Add to Cart' ?>
                        </button>
                    </form>

                    <div class="admin-btns">
                        <a href="<?= APP_URL ?>/product/manage.php?action=edit&id=<?= $p['product_id'] ?>"
                           class="btn btn-warning btn-sm" title="Edit">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <form method="POST" action=""
                              onsubmit="return confirm('Delete \'<?= htmlspecialchars(addslashes($p['name'])) ?>\'?')">
                            <input type="hidden" name="delete_product_id" value="<?= $p['product_id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div><!-- /.product-grid -->
    <?php endif; ?>

</div><!-- /.container -->

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
