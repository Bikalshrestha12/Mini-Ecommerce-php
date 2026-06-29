<?php
require_once __DIR__ . '/../../includes/session.php';
requireSuperAdmin();
require_once __DIR__ . '/../../includes/helpers.php';
$pdo = getDB();
$pageTitle = 'Product Categories';
require_once __DIR__ . '/../partials/admin_header.php';
require_once __DIR__ . '/../partials/admin_sidebar.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $category_id = $_POST['category_id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) $errors[] = 'Category name is required.';
        if (empty($slug)) $slug = createSlug($name);

        if (empty($errors)) {
            $imagePath = '';
            if (!empty($_FILES['image']['name'])) {
                $img = uploadFile($_FILES['image'], 'categories', ['jpg','jpeg','png','webp','gif']);
                if ($img) $imagePath = $img;
                else $errors[] = 'Image upload failed.';
            }

            if (empty($errors)) {
                if ($action === 'add') {
                    $stmt = $pdo->prepare("INSERT INTO product_categories (name, slug, description, image) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $slug, $description, $imagePath ?: null]);
                    $success = 'Category added successfully.';
                } else {
                    if ($imagePath) {
                        $stmt = $pdo->prepare("UPDATE product_categories SET name=?, slug=?, description=?, image=? WHERE category_id=?");
                        $stmt->execute([$name, $slug, $description, $imagePath, $category_id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE product_categories SET name=?, slug=?, description=? WHERE category_id=?");
                        $stmt->execute([$name, $slug, $description, $category_id]);
                    }
                    $success = 'Category updated successfully.';
                }
            }
        }
    }

    if ($action === 'delete') {
        $category_id = $_POST['category_id'] ?? '';
        if ($category_id) {
            $stmt = $pdo->prepare("DELETE FROM product_categories WHERE category_id = ?");
            $stmt->execute([$category_id]);
            $success = 'Category deleted successfully.';
        }
    }
}

$categories = $pdo->query("SELECT * FROM product_categories ORDER BY name")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold">Product Categories</h5>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="resetForm()">
        <i class="fas fa-plus"></i> Add Category
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
            <table class="table table-hover datatable">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $c): ?>
                    <tr>
                        <td><?= $c['category_id'] ?></td>
                        <td>
                            <?php if ($c['image']): ?>
                                <img src="<?= APP_URL . '/' . h($c['image']) ?>" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:4px;">
                            <?php else: ?>
                                <span class="text-muted"><i class="fas fa-folder fa-2x"></i></span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= h($c['name']) ?></strong></td>
                        <td><?= h($c['slug']) ?></td>
                        <td><?= h(substr($c['description'] ?? '', 0, 60)) ?></td>
                        <td>
                            <span class="badge bg-<?= $c['is_active'] ? 'success' : 'secondary' ?>">
                                <?= $c['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editCategory(<?= htmlspecialchars(json_encode($c)) ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $c['category_id'] ?>, <?= h(json_encode($c['name'])) ?>)">
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

<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="category_id" id="categoryId" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="categoryName" class="form-control" required onkeyup="document.getElementById('categorySlug').value = createSlug(this.value)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" id="categorySlug" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="categoryDesc" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <div id="catImagePreview" class="mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form method="post" id="deleteForm">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="category_id" id="deleteId" value="">
</form>

<script>
function resetForm() {
    document.getElementById('formAction').value = 'add';
    document.getElementById('modalTitle').textContent = 'Add Category';
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryName').value = '';
    document.getElementById('categorySlug').value = '';
    document.getElementById('categoryDesc').value = '';
    document.getElementById('catImagePreview').innerHTML = '';
}

function createSlug(str) {
    return str.toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
}

function editCategory(data) {
    document.getElementById('formAction').value = 'edit';
    document.getElementById('modalTitle').textContent = 'Edit Category';
    document.getElementById('categoryId').value = data.category_id;
    document.getElementById('categoryName').value = data.name;
    document.getElementById('categorySlug').value = data.slug;
    document.getElementById('categoryDesc').value = data.description || '';
    if (data.image) {
        document.getElementById('catImagePreview').innerHTML = '<img src="<?= APP_URL ?>/' + data.image + '" style="max-width:100px;border-radius:4px;border:1px solid #ddd;">';
    }
    new bootstrap.Modal(document.getElementById('categoryModal')).show();
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
