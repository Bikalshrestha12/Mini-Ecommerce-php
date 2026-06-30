<?php
require_once __DIR__ . '/../../includes/session.php';
requireSuperAdmin();
require_once __DIR__ . '/../../includes/helpers.php';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_member'])) {
        $member_id = (int)($_POST['member_id'] ?? 0);
        $name = $_POST['name'] ?? '';
        $designation = $_POST['designation'] ?? '';
        $bio = $_POST['bio'] ?? '';
        $email = $_POST['email'] ?? '';
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($name)) {
            $_SESSION['flash_error'] = 'Member name is required.';
            header('Location: index.php');
            exit;
        }

        $image = null;
        if (!empty($_FILES['image']['name'])) {
            $image = uploadFile($_FILES['image'], 'team');
        }

        if ($member_id > 0) {
            $sql = "UPDATE team_members SET name=?, designation=?, bio=?, email=?, sort_order=?, is_active=?";
            $params = [$name, $designation, $bio, $email, $sort_order, $is_active];
            if ($image) { $sql .= ", image=?"; $params[] = $image; }
            $sql .= " WHERE member_id=?";
            $params[] = $member_id;
        } else {
            $sql = "INSERT INTO team_members (name, designation, bio, image, email, sort_order, is_active) VALUES (?,?,?,?,?,?,?)";
            $params = [$name, $designation, $bio, $image, $email, $sort_order, $is_active];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $_SESSION['flash_success'] = $member_id > 0 ? 'Team member updated successfully.' : 'Team member added successfully.';
        header('Location: index.php');
        exit;
    }

    if (isset($_POST['delete_member'])) {
        $member_id = (int)$_POST['member_id'];
        $stmt = $pdo->prepare("SELECT image FROM team_members WHERE member_id=?");
        $stmt->execute([$member_id]);
        $m = $stmt->fetch();
        if ($m && $m['image']) {
            $path = __DIR__ . '/../../' . $m['image'];
            if (file_exists($path)) unlink($path);
        }
        $stmt = $pdo->prepare("DELETE FROM team_members WHERE member_id=?");
        $stmt->execute([$member_id]);
        $_SESSION['flash_success'] = 'Team member deleted successfully.';
        header('Location: index.php');
        exit;
    }
}

$members = $pdo->query("SELECT * FROM team_members ORDER BY sort_order")->fetchAll();

$pageTitle = 'Team Members';
require_once __DIR__ . '/../partials/admin_header.php';
require_once __DIR__ . '/../partials/admin_sidebar.php';
?>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title mb-0">Team Members</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#memberModal">
            <i class="fas fa-plus"></i> Add Member
        </button>
    </div>

    <?= flashMessage() ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="teamTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Designation</th>
                            <th>Email</th>
                            <th>Sort</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $m): ?>
                        <tr>
                            <td><?= $m['member_id'] ?></td>
                            <td>
                                <?php if ($m['image']): ?>
                                <img src="<?= APP_URL ?>/<?= h($m['image']) ?>" class="avatar-thumb" alt="<?= h($m['name']) ?>">
                                <?php else: ?>
                                <div class="avatar-thumb d-inline-flex align-items-center justify-content-center bg-light rounded-circle text-muted" style="width:40px;height:40px"><i class="fas fa-user"></i></div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= h($m['name']) ?></strong></td>
                            <td><?= h($m['designation']) ?></td>
                            <td><?= $m['email'] ? '<a href="mailto:' . h($m['email']) . '">' . h($m['email']) . '</a>' : '<span class="text-muted">—</span>' ?></td>
                            <td><?= (int)$m['sort_order'] ?></td>
                            <td><span class="status-badge <?= $m['is_active'] ? 'status-active' : 'status-inactive' ?>"><?= $m['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-member"
                                    data-id="<?= $m['member_id'] ?>"
                                    data-name="<?= h($m['name'], ENT_QUOTES) ?>"
                                    data-designation="<?= h($m['designation'], ENT_QUOTES) ?>"
                                    data-bio="<?= h($m['bio'], ENT_QUOTES) ?>"
                                    data-email="<?= h($m['email'], ENT_QUOTES) ?>"
                                    data-sort_order="<?= $m['sort_order'] ?>"
                                    data-is_active="<?= $m['is_active'] ?>"
                                    data-image="<?= h($m['image']) ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-member" data-id="<?= $m['member_id'] ?>">
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

<div class="modal fade" id="memberModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="memberModalLabel">Add Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="member_id" id="member_id" value="0">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="member_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Designation</label>
                            <input type="text" name="designation" id="member_designation" class="form-control" placeholder="CEO, Developer, etc.">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" id="member_bio" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Photo</label>
                            <input type="file" name="image" class="form-control" accept="image/*" id="member_image_input">
                            <div id="member_image_preview" class="mt-2"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="member_email" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="member_sort_order" class="form-control" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch mt-4">
                                <input type="checkbox" name="is_active" class="form-check-input" id="member_is_active" checked>
                                <label class="form-check-label" for="member_is_active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_member" class="btn btn-primary"><i class="fas fa-save"></i> Save Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form method="post" id="deleteForm">
    <input type="hidden" name="member_id" id="delete_id">
    <input type="hidden" name="delete_member">
</form>

<?php $footerExtra = <<<JS
<script>
$(document).ready(function() {
    $('#teamTable').DataTable({
        order: [[5, 'asc']],
        pageLength: 25
    });

    $(document).on('click', '.edit-member', function() {
        const d = $(this).data();
        $('#memberModalLabel').text('Edit Member');
        $('#member_id').val(d.id);
        $('#member_name').val(d.name);
        $('#member_designation').val(d.designation);
        $('#member_bio').val(d.bio);
        $('#member_email').val(d.email);
        $('#member_sort_order').val(d.sort_order);
        $('#member_is_active').prop('checked', d.is_active == 1);
        if (d.image) {
            $('#member_image_preview').html('<img src="' + APP_URL + '/' + d.image + '" class="avatar-thumb rounded"><small class="text-muted d-block">Leave empty to keep current</small>');
        } else {
            $('#member_image_preview').empty();
        }
        $('#memberModal').modal('show');
    });

    $('#memberModal').on('hidden.bs.modal', function() {
        if ($('#member_id').val() == 0) {
            $(this).find('form')[0].reset();
            $('#member_image_preview').empty();
            $('#memberModalLabel').text('Add Member');
        }
    });

    $(document).on('click', '.delete-member', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Team Member?',
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
