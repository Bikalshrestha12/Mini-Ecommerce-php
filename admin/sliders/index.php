<?php
require_once __DIR__ . '/../../includes/session.php';
requireSuperAdmin();
require_once __DIR__ . '/../../includes/helpers.php';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_slide'])) {
        $slide_id = (int)($_POST['slide_id'] ?? 0);
        $title = $_POST['title'] ?? '';
        $subtitle = $_POST['subtitle'] ?? '';
        $description = $_POST['description'] ?? '';
        $button_text = $_POST['button_text'] ?? '';
        $button_url = $_POST['button_url'] ?? '';
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        $image = null;
        if (!empty($_FILES['image']['name'])) {
            $image = uploadFile($_FILES['image'], 'sliders');
        }

        if ($slide_id > 0) {
            $sql = "UPDATE hero_sliders SET title=?, subtitle=?, description=?, button_text=?, button_url=?, sort_order=?, is_active=?";
            $params = [$title, $subtitle, $description, $button_text, $button_url, $sort_order, $is_active];
            if ($image) { $sql .= ", image=?"; $params[] = $image; }
            $sql .= " WHERE slide_id=?";
            $params[] = $slide_id;
        } else {
            if (!$image) {
                $_SESSION['flash_error'] = 'Image is required.';
                header('Location: index.php');
                exit;
            }
            $sql = "INSERT INTO hero_sliders (title, subtitle, description, button_text, button_url, image, sort_order, is_active) VALUES (?,?,?,?,?,?,?,?)";
            $params = [$title, $subtitle, $description, $button_text, $button_url, $image, $sort_order, $is_active];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $_SESSION['flash_success'] = $slide_id > 0 ? 'Slide updated successfully.' : 'Slide added successfully.';
        header('Location: index.php');
        exit;
    }

    if (isset($_POST['delete_slide'])) {
        $slide_id = (int)$_POST['slide_id'];
        $stmt = $pdo->prepare("SELECT image FROM hero_sliders WHERE slide_id=?");
        $stmt->execute([$slide_id]);
        $slide = $stmt->fetch();
        if ($slide && $slide['image']) {
            $path = __DIR__ . '/../../' . $slide['image'];
            if (file_exists($path)) unlink($path);
        }
        $stmt = $pdo->prepare("DELETE FROM hero_sliders WHERE slide_id=?");
        $stmt->execute([$slide_id]);
        $_SESSION['flash_success'] = 'Slide deleted successfully.';
        header('Location: index.php');
        exit;
    }
}

$slides = $pdo->query("SELECT * FROM hero_sliders ORDER BY sort_order")->fetchAll();

$pageTitle = 'Hero Sliders';
require_once __DIR__ . '/../partials/admin_header.php';
require_once __DIR__ . '/../partials/admin_sidebar.php';
?>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title mb-0">Hero Sliders</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#slideModal">
            <i class="fas fa-plus"></i> Add Slide
        </button>
    </div>

    <?= flashMessage() ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="slidersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Sort Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($slides as $s): ?>
                        <tr>
                            <td><?= $s['slide_id'] ?></td>
                            <td>
                                <?php if ($s['image']): ?>
                                <img src="<?= APP_URL ?>/<?= h($s['image']) ?>" class="img-thumb" alt="Slide">
                                <?php else: ?>
                                <span class="text-muted">No image</span>
                                <?php endif; ?>
                            </td>
                            <td><?= h($s['title']) ?></td>
                            <td><?= (int)$s['sort_order'] ?></td>
                            <td><span class="status-badge <?= $s['is_active'] ? 'status-active' : 'status-inactive' ?>"><?= $s['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-slide" data-id="<?= $s['slide_id'] ?>" data-title="<?= h($s['title'], ENT_QUOTES) ?>" data-subtitle="<?= h($s['subtitle'], ENT_QUOTES) ?>" data-description="<?= h($s['description'], ENT_QUOTES) ?>" data-button_text="<?= h($s['button_text'], ENT_QUOTES) ?>" data-button_url="<?= h($s['button_url'], ENT_QUOTES) ?>" data-sort_order="<?= $s['sort_order'] ?>" data-is_active="<?= $s['is_active'] ?>" data-image="<?= h($s['image']) ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-slide" data-id="<?= $s['slide_id'] ?>">
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

<div class="modal fade" id="slideModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="slideModalLabel">Add Slide</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="slide_id" id="slide_id" value="0">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" id="slide_title" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Subtitle</label>
                            <input type="text" name="subtitle" id="slide_subtitle" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="slide_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Button Text</label>
                            <input type="text" name="button_text" id="slide_button_text" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Button URL</label>
                            <input type="text" name="button_url" id="slide_button_url" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*" id="slide_image_input">
                            <div id="slide_image_preview" class="mt-2"></div>
                            <small class="text-muted">Recommended: 1920x800px</small>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="slide_sort_order" class="form-control" value="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" name="is_active" class="form-check-input" id="slide_is_active" checked>
                                <label class="form-check-label" for="slide_is_active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_slide" class="btn btn-primary"><i class="fas fa-save"></i> Save Slide</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form method="post" id="deleteForm">
    <input type="hidden" name="slide_id" id="delete_id">
    <input type="hidden" name="delete_slide">
</form>

<?php $footerExtra = <<<JS
<script>
$(document).ready(function() {
    $('#slidersTable').DataTable({
        order: [[3, 'asc']],
        pageLength: 25
    });

    $(document).on('click', '.edit-slide', function() {
        const d = $(this).data();
        $('#slideModalLabel').text('Edit Slide');
        $('#slide_id').val(d.id);
        $('#slide_title').val(d.title);
        $('#slide_subtitle').val(d.subtitle);
        $('#slide_description').val(d.description);
        $('#slide_button_text').val(d.button_text);
        $('#slide_button_url').val(d.button_url);
        $('#slide_sort_order').val(d.sort_order);
        $('#slide_is_active').prop('checked', d.is_active == 1);
        if (d.image) {
            $('#slide_image_preview').html('<img src="' + APP_URL + '/' + d.image + '" class="preview-img" style="max-height:80px"><small class="text-muted d-block">Leave empty to keep current</small>');
        } else {
            $('#slide_image_preview').empty();
        }
        $('#slideModal').modal('show');
    });

    $('#slideModal').on('hidden.bs.modal', function() {
        if ($('#slide_id').val() == 0) {
            $(this).find('form')[0].reset();
            $('#slide_image_preview').empty();
            $('#slideModalLabel').text('Add Slide');
        }
    });

    $(document).on('click', '.delete-slide', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Slide?',
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
