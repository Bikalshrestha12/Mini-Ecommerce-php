<?php
require_once __DIR__ . '/../../includes/session.php';
requireSuperAdmin();
require_once __DIR__ . '/../../includes/helpers.php';
$pdo = getDB();
$pageTitle = 'Admission Programs';
require_once __DIR__ . '/../partials/admin_header.php';
require_once __DIR__ . '/../partials/admin_sidebar.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $program_id = $_POST['program_id'] ?? '';
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $duration = trim($_POST['duration'] ?? '');
        $eligibility = trim($_POST['eligibility'] ?? '');
        $fee = trim($_POST['fee'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($title)) $errors[] = 'Program title is required.';
        if (empty($slug)) $slug = createSlug($title);

        if (empty($errors)) {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO admission_programs (title, slug, description, duration, eligibility, fee, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now'))");
                $stmt->execute([$title, $slug, $description, $duration, $eligibility, $fee, $is_active]);
                $success = 'Program added successfully.';
            } else {
                $stmt = $pdo->prepare("UPDATE admission_programs SET title=?, slug=?, description=?, duration=?, eligibility=?, fee=?, is_active=?, updated_at=datetime('now') WHERE program_id=?");
                $stmt->execute([$title, $slug, $description, $duration, $eligibility, $fee, $is_active, $program_id]);
                $success = 'Program updated successfully.';
            }
        }
    }

    if ($action === 'delete') {
        $program_id = $_POST['program_id'] ?? '';
        if ($program_id) {
            $stmt = $pdo->prepare("DELETE FROM admission_programs WHERE program_id = ?");
            $stmt->execute([$program_id]);
            $success = 'Program deleted successfully.';
        }
    }
}

$programs = $pdo->query("SELECT p.*, (SELECT COUNT(*) FROM admission_applications WHERE program_id = p.program_id) as app_count FROM admission_programs p ORDER BY p.created_at DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold">Admission Programs</h5>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#programModal" onclick="resetForm()">
        <i class="fas fa-plus"></i> Add Program
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
                        <th>Title</th>
                        <th>Duration</th>
                        <th>Fee</th>
                        <th>Applications</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($programs as $pr): ?>
                    <tr>
                        <td><?= $pr['program_id'] ?></td>
                        <td><strong><?= h($pr['title']) ?></strong></td>
                        <td><?= h($pr['duration'] ?? '-') ?></td>
                        <td><?= h($pr['fee'] ?? '-') ?></td>
                        <td>
                            <a href="applications.php?program_id=<?= $pr['program_id'] ?>" class="badge bg-primary text-decoration-none">
                                <?= $pr['app_count'] ?> applications
                            </a>
                        </td>
                        <td><span class="badge bg-<?= $pr['is_active'] ? 'success' : 'secondary' ?>"><?= $pr['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editProgram(<?= $pr['program_id'] ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="window.location.href='applications.php?program_id=<?= $pr['program_id'] ?>'">
                                <i class="fas fa-users"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $pr['program_id'] ?>, <?= h(json_encode($pr['title'])) ?>)">
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

<div class="modal fade" id="programModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="program_id" id="programId" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="progTitle" class="form-control" required onkeyup="document.getElementById('progSlug').value = createSlug(this.value)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" id="progSlug" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Duration</label>
                            <input type="text" name="duration" id="progDuration" class="form-control" placeholder="e.g. 4 Years">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fee</label>
                            <input type="text" name="fee" id="progFee" class="form-control" placeholder="e.g. $10,000/year">
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="is_active" id="progActive" value="1" checked>
                                <label class="form-check-label" for="progActive">Active</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="progDesc" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Eligibility</label>
                            <textarea name="eligibility" id="progEligibility" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Program</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form method="post" id="deleteForm">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="program_id" id="deleteId" value="">
</form>

<script>
function resetForm() {
    document.getElementById('formAction').value = 'add';
    document.getElementById('modalTitle').textContent = 'Add Program';
    document.getElementById('programId').value = '';
    document.getElementById('progTitle').value = '';
    document.getElementById('progSlug').value = '';
    document.getElementById('progDuration').value = '';
    document.getElementById('progFee').value = '';
    document.getElementById('progDesc').value = '';
    document.getElementById('progEligibility').value = '';
    document.getElementById('progActive').checked = true;
}

function createSlug(str) {
    return str.toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
}

function editProgram(id) {
    fetch('get_program.php?id=' + id)
        .then(r => r.json())
        .then(data => {
            if (data) {
                document.getElementById('formAction').value = 'edit';
                document.getElementById('modalTitle').textContent = 'Edit Program';
                document.getElementById('programId').value = data.program_id;
                document.getElementById('progTitle').value = data.title;
                document.getElementById('progSlug').value = data.slug;
                document.getElementById('progDuration').value = data.duration || '';
                document.getElementById('progFee').value = data.fee || '';
                document.getElementById('progDesc').value = data.description || '';
                document.getElementById('progEligibility').value = data.eligibility || '';
                document.getElementById('progActive').checked = data.is_active == 1;
                new bootstrap.Modal(document.getElementById('programModal')).show();
            }
        });
}

function confirmDelete(id, title) {
    Swal.fire({
        title: 'Delete "' + title + '"?',
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
