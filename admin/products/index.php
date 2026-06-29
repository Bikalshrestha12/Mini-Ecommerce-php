<?php
require_once __DIR__ . '/../../includes/session.php';
requireSuperAdmin();
require_once __DIR__ . '/../../includes/helpers.php';
$pdo = getDB();
$pageTitle = 'Products';
require_once __DIR__ . '/../partials/admin_header.php';
require_once __DIR__ . '/../partials/admin_sidebar.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $product_id = $_POST['product_id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = trim($_POST['price'] ?? '');
        $category_id = trim($_POST['category_id'] ?? '');
        $stock = (int)($_POST['stock'] ?? 0);
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($name)) $errors[] = 'Product name is required.';
        if (empty($slug)) $slug = createSlug($name);
        if (!is_numeric($price) || $price <= 0) $errors[] = 'Valid price is required.';
        if (empty($category_id)) $errors[] = 'Category is required.';

        if (empty($errors)) {
            $imagePath = '';
            $brochurePath = '';

            if (!empty($_FILES['image']['name'])) {
                $img = uploadFile($_FILES['image'], 'products', ['jpg','jpeg','png','webp','gif']);
                if ($img) $imagePath = $img;
                else $errors[] = 'Image upload failed. Check file type/size.';
            }

            if (!empty($_FILES['brochure']['name'])) {
                $bro = uploadFile($_FILES['brochure'], 'brochures', ['pdf','doc','docx']);
                if ($bro) $brochurePath = $bro;
                else $errors[] = 'Brochure upload failed. Check file type/size.';
            }

            if (empty($errors)) {
                if ($action === 'add') {
                    $stmt = $pdo->prepare("INSERT INTO products (name, slug, description, price, category_id, stock, is_featured, is_active, image, brochure, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([$name, $slug, $description, $price, $category_id, $stock, $is_featured, $is_active, $imagePath ?: null, $brochurePath ?: null]);
                    $success = 'Product added successfully.';
                } else {
                    $existing = $pdo->prepare("SELECT image, brochure FROM products WHERE product_id = ?");
                    $existing->execute([$product_id]);
                    $old = $existing->fetch();

                    $finalImage = $imagePath ?: $old['image'];
                    $finalBrochure = $brochurePath ?: $old['brochure'];

                    $stmt = $pdo->prepare("UPDATE products SET name=?, slug=?, description=?, price=?, category_id=?, stock=?, is_featured=?, is_active=?, image=?, brochure=?, updated_at=NOW() WHERE product_id=?");
                    $stmt->execute([$name, $slug, $description, $price, $category_id, $stock, $is_featured, $is_active, $finalImage, $finalBrochure, $product_id]);
                    $success = 'Product updated successfully.';
                }
            }
        }
    }

    if ($action === 'delete') {
        $product_id = $_POST['product_id'] ?? '';
        if ($product_id) {
            $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
            $stmt->execute([$product_id]);
            $success = 'Product deleted successfully.';
        }
    }
}

$stmt = $pdo->query("SELECT p.*, pc.name as category_name FROM products p LEFT JOIN product_categories pc ON p.category_id = pc.category_id ORDER BY p.product_id DESC");
$products = $stmt->fetchAll();
$categories = $pdo->query("SELECT * FROM product_categories WHERE is_active = 1 ORDER BY name")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold">Products</h5>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#productModal" onclick="resetProductForm()">
        <i class="fas fa-plus"></i> Add Product
    </button>
</div>

<?php if ($errors): ?>
    <div class="alert alert-danger"><?= implode('<br>', array_map('h', $errors)) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= h($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable" id="productsTable">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Featured</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?= $p['product_id'] ?></td>
                        <td>
                            <?php if ($p['image']): ?>
                                <img src="<?= APP_URL . '/' . h($p['image']) ?>" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:4px;">
                            <?php else: ?>
                                <span class="text-muted"><i class="fas fa-image fa-2x"></i></span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= h($p['name']) ?></strong></td>
                        <td><?= h($p['category_name'] ?? $p['category']) ?></td>
                        <td>$<?= number_format($p['price'], 2) ?></td>
                        <td>
                            <span class="badge bg-<?= $p['stock'] > 0 ? 'success' : 'danger' ?>">
                                <?= $p['stock'] ?>
                            </span>
                        </td>
                        <td><?= $p['is_featured'] ? '<span class="badge bg-warning text-dark"><i class="fas fa-star"></i></span>' : '-' ?></td>
                        <td>
                            <span class="badge bg-<?= $p['is_active'] ? 'success' : 'secondary' ?>">
                                <?= $p['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editProduct(<?= $p['product_id'] ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $p['product_id'] ?>, <?= h(json_encode($p['name'])) ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="product_id" id="productId" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="productName" class="form-control" required onkeyup="document.getElementById('productSlug').value = createSlug(this.value)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" id="productSlug" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="productDescription" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price" id="productPrice" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category_id" id="productCategory" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['category_id'] ?>"><?= h($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" id="productStock" class="form-control" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" id="productImage" class="form-control" accept="image/*">
                            <div id="imagePreview" class="mt-2"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Brochure (PDF)</label>
                            <input type="file" name="brochure" class="form-control" accept=".pdf,.doc,.docx">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="productFeatured" value="1">
                                <label class="form-check-label" for="productFeatured">Featured Product</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="is_active" id="productActive" value="1" checked>
                                <label class="form-check-label" for="productActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form method="post" id="deleteForm">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="product_id" id="deleteId" value="">
</form>

<script>
const APP_URL = '<?= APP_URL ?>';

function resetProductForm() {
    document.getElementById('formAction').value = 'add';
    document.getElementById('modalTitle').textContent = 'Add Product';
    document.getElementById('productId').value = '';
    document.getElementById('productName').value = '';
    document.getElementById('productSlug').value = '';
    document.getElementById('productDescription').value = '';
    document.getElementById('productPrice').value = '';
    document.getElementById('productCategory').value = '';
    document.getElementById('productStock').value = '0';
    document.getElementById('productFeatured').checked = false;
    document.getElementById('productActive').checked = true;
    document.getElementById('imagePreview').innerHTML = '';
    document.getElementById('productImage').value = '';
}

function createSlug(str) {
    return str.toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-').replace(/^-|-\$/g, '');
}

function editProduct(id) {
    fetch('get_product.php?id=' + id)
        .then(r => r.json())
        .then(data => {
            if (data && data.product_id) {
                document.getElementById('formAction').value = 'edit';
                document.getElementById('modalTitle').textContent = 'Edit Product';
                document.getElementById('productId').value = data.product_id;
                document.getElementById('productName').value = data.name || '';
                document.getElementById('productSlug').value = data.slug || '';
                document.getElementById('productDescription').value = data.description || '';
                document.getElementById('productPrice').value = data.price || '';
                document.getElementById('productCategory').value = data.category_id || '';
                document.getElementById('productStock').value = data.stock || 0;
                document.getElementById('productFeatured').checked = data.is_featured == 1;
                document.getElementById('productActive').checked = data.is_active == 1;
                if (data.image) {
                    document.getElementById('imagePreview').innerHTML = '<img src="' + APP_URL + '/' + data.image + '" style="max-width:120px;border-radius:4px;border:1px solid #ddd;">';
                } else {
                    document.getElementById('imagePreview').innerHTML = '';
                }
                var modal = new bootstrap.Modal(document.getElementById('productModal'));
                modal.show();
            } else {
                Swal.fire('Error', 'Product not found!', 'error');
            }
        })
        .catch(function() {
            Swal.fire('Error', 'Failed to load product data!', 'error');
        });
}

function confirmDelete(id, name) {
    Swal.fire({
        title: 'Delete "' + name + '"?',
        text: 'This cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteForm').submit();
        }
    });
}
</script>

<?php require_once __DIR__ . '/../partials/admin_footer.php'; ?>
