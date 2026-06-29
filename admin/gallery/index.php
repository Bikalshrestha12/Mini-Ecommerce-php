<?php
require_once __DIR__ . '/../../includes/session.php';
requireSuperAdmin();
require_once __DIR__ . '/../../includes/helpers.php';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_image'])) {
        $image_id = (int)($_POST['image_id'] ?? 0);
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $category = $_POST['category'] ?? '';
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        $image = null;
        if (!empty($_FILES['image']['name'])) {
            $image = uploadFile($_FILES['image'], 'gallery');
        }

        if ($image_id > 0) {
            $sql = "UPDATE gallery SET title=?, description=?, category=?, sort_order=?, is_active=?";
            $params = [$title, $description, $category, $sort_order, $is_active];
            if ($image) { $sql .= ", image=?"; $params[] = $image; }
            $sql .= " WHERE image_id=?";
            $params[] = $image_id;
        } else {
            if (!$image) {
                $_SESSION['flash_error'] = 'Image is required.';
                header('Location: index.php');
                exit;
            }
            $sql = "INSERT INTO gallery (title, description, image, category, sort_order, is_active) VALUES (?,?,?,?,?,?)";
            $params = [$title, $description, $image, $category, $sort_order, $is_active];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $_SESSION['flash_success'] = $image_id > 0 ? 'Gallery image updated successfully.' : 'Gallery image added successfully.';
        header('Location: index.php');
        exit;
    }

    if (isset($_POST['delete_image'])) {
        $image_id = (int)$_POST['image_id'];
        $stmt = $pdo->prepare("SELECT image FROM gallery WHERE image_id=?");
        $stmt->execute([$image_id]);
        $g = $stmt->fetch();
        if ($g && $g['image']) {
            $path = __DIR__ . '/../../' . $g['image'];
            if (file_exists($path)) unlink($path);
        }
        $stmt = $pdo->prepare("DELETE FROM gallery WHERE image_id=?");
        $stmt->execute([$image_id]);
        $_SESSION['flash_success'] = 'Gallery image deleted successfully.';
        header('Location: index.php');
        exit;
    }
}

$images = $pdo->query("SELECT * FROM gallery ORDER BY sort_order")->fetchAll();
$categories = $pdo->query("SELECT DISTINCT category FROM gallery WHERE category IS NOT NULL AND category != '' ORDER BY category")->fetchAll();

$pageTitle = 'Gallery';
require_once __DIR__ . '/../partials/admin_header.php';
require_once __DIR__ . '/../partials/admin_sidebar.php';
?>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title mb-0">Gallery</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#galleryModal">
            <i class="fas fa-plus"></i> Add Image
        </button>
    </div>

    <?= flashMessage() ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="galleryTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Sort</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($images as $g): ?>
                        <tr>
                            <td><?= $g['image_id'] ?></td>
                            <td>
                                <img src="<?= APP_URL ?>/<?= h($g['image']) ?>" class="img-thumb" alt="<?= h($g['title']) ?>">
                            </td>
                            <td><?= h($g['title']) ?></td>
                            <td><?= h($g['category']) ?: '<span class="text-muted">—</span>' ?></td>
                            <td><?= (int)$g['sort_order'] ?></td>
                            <td><span class="status-badge <?= $g['is_active'] ? 'status-active' : 'status-inactive' ?>"><?= $g['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-gallery"
                                    data-id="<?= $g['image_id'] ?>"
                                    data-title="<?= h($g['title'], ENT_QUOTES) ?>"
                                    data-description="<?= h($g['description'], ENT_QUOTES) ?>"
                                    data-category="<?= h($g['category'], ENT_QUOTES) ?>"
                                    data-sort_order="<?= $g['sort_order'] ?>"
                                    data-is_active="<?= $g['is_active'] ?>"
                                    data-image="<?= h($g['image']) ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-gallery" data-id="<?= $g['image_id'] ?>">
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
</div>

<div class="modal fade" id="galleryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="galleryModalLabel">Add Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="image_id" id="gallery_id" value="0">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" id="gallery_title" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="gallery_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*" id="gallery_image_input">
                            <div id="gallery_image_preview" class="mt-2"></div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" id="gallery_category" class="form-control" list="categoryList">
                            <datalist id="categoryList">
                                <?php foreach ($categories as $c): ?>
                                <option value="<?= h($c['category']) ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="gallery_sort_order" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input type="checkbox" name="is_active" class="form-check-input" id="gallery_is_active" checked>
                        <label class="form-check-label" for="gallery_is_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_image" class="btn btn-primary"><i class="fas fa-save"></i> Save Image</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form method="post" id="deleteForm">
    <input type="hidden" name="image_id" id="delete_id">
    <input type="hidden" name="delete_image">
</form>

<?php $footerExtra = <<<JS
<script>
$(document).ready(function() {
    $('#galleryTable').DataTable({
        order: [[4, 'asc']],
        pageLength: 25
    });

    $('.edit-gallery').click(function() {
        const d = $(this).data();
        $('#galleryModalLabel').text('Edit Image');
        $('#gallery_id').val(d.id);
        $('#gallery_title').val(d.title);
        $('#gallery_description').val(d.description);
        $('#gallery_category').val(d.category);
        $('#gallery_sort_order').val(d.sort_order);
        $('#gallery_is_active').prop('checked', d.is_active == 1);
        if (d.image) {
            $('#gallery_image_preview').html('<img src="' + APP_URL + '/' + d.image + '" class="preview-img" style="max-height:80px"><small class="text-muted d-block">Leave empty to keep current</small>');
        } else {
            $('#gallery_image_preview').empty();
        }
        $('#galleryModal').modal('show');
    });

    $('#galleryModal').on('hidden.bs.modal', function() {
        if ($('#gallery_id').val() == 0) {
            $(this).find('form')[0].reset();
            $('#gallery_image_preview').empty();
            $('#galleryModalLabel').text('Add Image');
        }
    });

    $('.delete-gallery').click(function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Image?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (result.isConfirmed) {
                $('#delete_id').val(id);
                $('#deleteForm').submit();
            }
        });
    });
});
</script>
JS;
?>
<?php require_once __DIR__ . '/../partials/admin_footer.php'; ?>
