<?php
// ============================================================
// product/manage.php – Add / Edit Product (CRUD)
// ============================================================

require_once __DIR__ . '/../includes/session.php';
requireLogin();

$pdo    = getDB();
$action = $_GET['action'] ?? 'add';   // 'add' | 'edit'
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$product = [
    'product_id'  => 0,
    'name'        => '',
    'description' => '',
    'price'       => '',
    'category'    => '',
    'stock'       => '',
    'image'       => '',
];

$errors = [];

// ── Load existing product for EDIT ──────────────────────────
if ($action === 'edit') {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE product_id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        $_SESSION['admin_error'] = 'Product not found.';
        header('Location: ' . APP_URL . '/product/products.php');
        exit;
    }
    $product = $row;
}

// ── Handle POST (save) ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']        ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = trim($_POST['price']       ?? '');
    $category    = trim($_POST['category']    ?? '');
    $stock       = trim($_POST['stock']       ?? '');
    $imageName   = $product['image'];

    // Validation
    if (empty($name))                                        $errors[] = 'Product name is required.';
    if (empty($description))                                 $errors[] = 'Description is required.';
    if (!is_numeric($price) || (float)$price < 0)           $errors[] = 'Enter a valid price.';
    if (!in_array($category,['Top','Bottom','Shoe','Accessories'])) $errors[] = 'Select a valid category.';
    if (!ctype_digit($stock) || (int)$stock < 0)            $errors[] = 'Enter a valid stock quantity.';

    // Image upload
    if (!empty($_FILES['image']['name'])) {
        $allowed   = ['jpg','jpeg','png','webp','gif'];
        $ext       = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $maxSize   = 2 * 1024 * 1024; // 2 MB

        if (!in_array($ext, $allowed)) {
            $errors[] = 'Image must be JPG, PNG, WEBP, or GIF.';
        } elseif ($_FILES['image']['size'] > $maxSize) {
            $errors[] = 'Image must be under 2 MB.';
        } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Image upload failed (error ' . $_FILES['image']['error'] . ').';
        } else {
            $safeName  = 'product_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $dest      = __DIR__ . '/../assets/images/' . $safeName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $imageName = $safeName;
            } else {
                $errors[] = 'Failed to move uploaded file.';
            }
        }
    }

    if (empty($errors)) {
        if ($action === 'add') {
            $pdo->prepare(
                'INSERT INTO products (name, description, price, category, stock, image)
                 VALUES (?, ?, ?, ?, ?, ?)'
            )->execute([$name, $description, (float)$price, $category, (int)$stock, $imageName]);
            $_SESSION['admin_msg'] = 'Product added successfully.';
        } else {
            $pdo->prepare(
                'UPDATE products
                    SET name = ?, description = ?, price = ?, category = ?, stock = ?, image = ?
                  WHERE product_id = ?'
            )->execute([$name, $description, (float)$price, $category, (int)$stock, $imageName, $id]);
            $_SESSION['admin_msg'] = 'Product updated successfully.';
        }
        header('Location: ' . APP_URL . '/product/products.php');
        exit;
    }

    // Repopulate on error
    $product = array_merge($product, compact('name','description','price','category','stock'));
}

$pageTitle = $action === 'add' ? 'Add New Product' : 'Edit Product';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>
        <i class="fa-solid fa-<?= $action === 'add' ? 'plus-circle' : 'pen-to-square' ?>"></i>
        <?= $pageTitle ?>
    </h1>
    <a href="<?= APP_URL ?>/product/products.php" class="btn btn-secondary btn-sm">
        <i class="fa-solid fa-arrow-left"></i> Back to Products
    </a>
</div>

<div class="container container--narrow">

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

    <div class="card">
        <div class="card-body">
            <form method="POST" action="" enctype="multipart/form-data">

                <div class="form-grid-2">

                    <div class="form-group">
                        <label>Product Name <span class="required">*</span></label>
                        <input type="text" name="name"
                               value="<?= htmlspecialchars($product['name']) ?>"
                               required placeholder="e.g. Classic Oxford Shirt">
                    </div>

                    <div class="form-group">
                        <label>Category <span class="required">*</span></label>
                        <select name="category" required>
                            <option value="">Select category</option>
                            <?php foreach (['Top','Bottom','Shoe','Accessories'] as $cat): ?>
                            <option value="<?= $cat ?>"
                                <?= $product['category'] === $cat ? 'selected' : '' ?>>
                                <?= $cat ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Price ($) <span class="required">*</span></label>
                        <input type="number" name="price"
                               value="<?= htmlspecialchars($product['price']) ?>"
                               min="0" step="0.01" required placeholder="0.00">
                    </div>

                    <div class="form-group">
                        <label>Stock Quantity <span class="required">*</span></label>
                        <input type="number" name="stock"
                               value="<?= htmlspecialchars($product['stock']) ?>"
                               min="0" required placeholder="0">
                    </div>

                </div>

                <div class="form-group">
                    <label>Description <span class="required">*</span></label>
                    <textarea name="description" rows="4" required
                              placeholder="Describe the product…"><?= htmlspecialchars($product['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Product Image</label>
                    <?php if (!empty($product['image'])): ?>
                    <div class="current-image-preview">
                        <img src="<?= APP_URL ?>/assets/images/<?= htmlspecialchars($product['image']) ?>"
                             alt="Current image"
                             onerror="this.style.display='none'">
                        <span class="img-label">Current: <?= htmlspecialchars($product['image']) ?></span>
                    </div>
                    <?php endif; ?>
                    <input type="file" name="image" id="imageInput"
                           accept="image/jpeg,image/png,image/webp,image/gif"
                           onchange="previewImage(this)">
                    <small class="form-hint">JPG/PNG/WEBP/GIF, max 2 MB. Leave blank to keep current image.</small>
                    <img id="imagePreview" class="img-preview-new" src="" alt="" style="display:none">
                </div>

                <div class="form-actions">
                    <a href="<?= APP_URL ?>/product/products.php"
                       class="btn btn-secondary">
                        <i class="fa-solid fa-xmark"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <?= $action === 'add' ? 'Add Product' : 'Save Changes' ?>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
