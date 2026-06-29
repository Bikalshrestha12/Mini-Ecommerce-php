<?php
require_once __DIR__ . '/../../includes/session.php';
requireSuperAdmin();
require_once __DIR__ . '/../../includes/helpers.php';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_testimonial'])) {
        $testimonial_id = (int)($_POST['testimonial_id'] ?? 0);
        $client_name = $_POST['client_name'] ?? '';
        $designation = $_POST['designation'] ?? '';
        $company = $_POST['company'] ?? '';
        $content = $_POST['content'] ?? '';
        $rating = (int)($_POST['rating'] ?? 5);
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($client_name) || empty($content)) {
            $_SESSION['flash_error'] = 'Client name and content are required.';
            header('Location: index.php');
            exit;
        }

        $avatar = null;
        if (!empty($_FILES['avatar']['name'])) {
            $avatar = uploadFile($_FILES['avatar'], 'testimonials');
        }

        if ($testimonial_id > 0) {
            $sql = "UPDATE testimonials SET client_name=?, designation=?, company=?, content=?, rating=?, sort_order=?, is_active=?";
            $params = [$client_name, $designation, $company, $content, $rating, $sort_order, $is_active];
            if ($avatar) { $sql .= ", avatar=?"; $params[] = $avatar; }
            $sql .= " WHERE testimonial_id=?";
            $params[] = $testimonial_id;
        } else {
            $sql = "INSERT INTO testimonials (client_name, designation, company, content, avatar, rating, sort_order, is_active) VALUES (?,?,?,?,?,?,?,?)";
            $params = [$client_name, $designation, $company, $content, $avatar, $rating, $sort_order, $is_active];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $_SESSION['flash_success'] = $testimonial_id > 0 ? 'Testimonial updated successfully.' : 'Testimonial added successfully.';
        header('Location: index.php');
        exit;
    }

    if (isset($_POST['delete_testimonial'])) {
        $testimonial_id = (int)$_POST['testimonial_id'];
        $stmt = $pdo->prepare("SELECT avatar FROM testimonials WHERE testimonial_id=?");
        $stmt->execute([$testimonial_id]);
        $t = $stmt->fetch();
        if ($t && $t['avatar']) {
            $path = __DIR__ . '/../../' . $t['avatar'];
            if (file_exists($path)) unlink($path);
        }
        $stmt = $pdo->prepare("DELETE FROM testimonials WHERE testimonial_id=?");
        $stmt->execute([$testimonial_id]);
        $_SESSION['flash_success'] = 'Testimonial deleted successfully.';
        header('Location: index.php');
        exit;
    }
}

$testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY sort_order")->fetchAll();

$pageTitle = 'Testimonials';
require_once __DIR__ . '/../partials/admin_header.php';
require_once __DIR__ . '/../partials/admin_sidebar.php';
?>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title mb-0">Testimonials</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#testimonialModal">
            <i class="fas fa-plus"></i> Add Testimonial
        </button>
    </div>

    <?= flashMessage() ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="testimonialsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Avatar</th>
                            <th>Client Name</th>
                            <th>Designation</th>
                            <th>Company</th>
                            <th>Rating</th>
                            <th>Sort</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($testimonials as $t): ?>
                        <tr>
                            <td><?= $t['testimonial_id'] ?></td>
                            <td>
                                <?php if ($t['avatar']): ?>
                                <img src="<?= APP_URL ?>/<?= h($t['avatar']) ?>" class="avatar-thumb" alt="Avatar">
                                <?php else: ?>
                                <div class="avatar-thumb d-inline-flex align-items-center justify-content-center bg-light rounded-circle text-muted" style="width:40px;height:40px"><i class="fas fa-user"></i></div>
                                <?php endif; ?>
                            </td>
                            <td><?= h($t['client_name']) ?></td>
                            <td><?= h($t['designation']) ?></td>
                            <td><?= h($t['company']) ?></td>
                            <td>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?= $i <= $t['rating'] ? ' text-warning' : ' text-muted' ?>" style="font-size:0.8rem"></i>
                                <?php endfor; ?>
                            </td>
                            <td><?= (int)$t['sort_order'] ?></td>
                            <td><span class="status-badge <?= $t['is_active'] ? 'status-active' : 'status-inactive' ?>"><?= $t['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-testimonial"
                                    data-id="<?= $t['testimonial_id'] ?>"
                                    data-client_name="<?= h($t['client_name'], ENT_QUOTES) ?>"
                                    data-designation="<?= h($t['designation'], ENT_QUOTES) ?>"
                                    data-company="<?= h($t['company'], ENT_QUOTES) ?>"
                                    data-content="<?= h($t['content'], ENT_QUOTES) ?>"
                                    data-rating="<?= $t['rating'] ?>"
                                    data-sort_order="<?= $t['sort_order'] ?>"
                                    data-is_active="<?= $t['is_active'] ?>"
                                    data-avatar="<?= h($t['avatar']) ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-testimonial" data-id="<?= $t['testimonial_id'] ?>">
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

<div class="modal fade" id="testimonialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="testimonialModalLabel">Add Testimonial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="testimonial_id" id="testimonial_id" value="0">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Client Name <span class="text-danger">*</span></label>
                            <input type="text" name="client_name" id="test_client_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Designation</label>
                            <input type="text" name="designation" id="test_designation" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company</label>
                            <input type="text" name="company" id="test_company" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rating (1-5)</label>
                            <select name="rating" id="test_rating" class="form-select">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?> Star<?= $i > 1 ? 's' : '' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content <span class="text-danger">*</span></label>
                        <textarea name="content" id="test_content" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Avatar</label>
                            <input type="file" name="avatar" class="form-control" accept="image/*">
                            <div id="test_avatar_preview" class="mt-2"></div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="test_sort_order" class="form-control" value="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" name="is_active" class="form-check-input" id="test_is_active" checked>
                                <label class="form-check-label" for="test_is_active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_testimonial" class="btn btn-primary"><i class="fas fa-save"></i> Save Testimonial</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form method="post" id="deleteForm">
    <input type="hidden" name="testimonial_id" id="delete_id">
    <input type="hidden" name="delete_testimonial">
</form>

<?php $footerExtra = <<<JS
<script>
$(document).ready(function() {
    $('#testimonialsTable').DataTable({
        order: [[6, 'asc']],
        pageLength: 25
    });

    $('.edit-testimonial').click(function() {
        const d = $(this).data();
        $('#testimonialModalLabel').text('Edit Testimonial');
        $('#testimonial_id').val(d.id);
        $('#test_client_name').val(d.client_name);
        $('#test_designation').val(d.designation);
        $('#test_company').val(d.company);
        $('#test_content').val(d.content);
        $('#test_rating').val(d.rating);
        $('#test_sort_order').val(d.sort_order);
        $('#test_is_active').prop('checked', d.is_active == 1);
        if (d.avatar) {
            $('#test_avatar_preview').html('<img src="' + APP_URL + '/' + d.avatar + '" class="avatar-thumb rounded"><small class="text-muted d-block">Leave empty to keep current</small>');
        } else {
            $('#test_avatar_preview').empty();
        }
        $('#testimonialModal').modal('show');
    });

    $('#testimonialModal').on('hidden.bs.modal', function() {
        if ($('#testimonial_id').val() == 0) {
            $(this).find('form')[0].reset();
            $('#test_avatar_preview').empty();
            $('#testimonialModalLabel').text('Add Testimonial');
        }
    });

    $('.delete-testimonial').click(function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Testimonial?',
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
