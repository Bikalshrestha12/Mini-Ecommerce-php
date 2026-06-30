<?php
require_once __DIR__ . '/../../includes/session.php';
requireSuperAdmin();
require_once __DIR__ . '/../../includes/helpers.php';
$pdo = getDB();
$pageTitle = 'Careers';
require_once __DIR__ . '/../partials/admin_header.php';
require_once __DIR__ . '/../partials/admin_sidebar.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $job_id = $_POST['job_id'] ?? '';
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $employment_type = trim($_POST['employment_type'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $requirements = trim($_POST['requirements'] ?? '');
        $responsibilities = trim($_POST['responsibilities'] ?? '');
        $salary_range = trim($_POST['salary_range'] ?? '');
        $application_deadline = trim($_POST['application_deadline'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($title)) $errors[] = 'Job title is required.';
        if (empty($slug)) $slug = createSlug($title);

        if (empty($errors)) {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO career_jobs (title, slug, department, location, employment_type, description, requirements, responsibilities, salary_range, application_deadline, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))");
                $stmt->execute([$title, $slug, $department, $location, $employment_type, $description, $requirements, $responsibilities, $salary_range, $application_deadline ?: null, $is_active]);
                $success = 'Job added successfully.';
            } else {
                $stmt = $pdo->prepare("UPDATE career_jobs SET title=?, slug=?, department=?, location=?, employment_type=?, description=?, requirements=?, responsibilities=?, salary_range=?, application_deadline=?, is_active=?, updated_at=datetime('now') WHERE job_id=?");
                $stmt->execute([$title, $slug, $department, $location, $employment_type, $description, $requirements, $responsibilities, $salary_range, $application_deadline ?: null, $is_active, $job_id]);
                $success = 'Job updated successfully.';
            }
        }
    }

    if ($action === 'delete') {
        $job_id = $_POST['job_id'] ?? '';
        if ($job_id) {
            $stmt = $pdo->prepare("DELETE FROM career_jobs WHERE job_id = ?");
            $stmt->execute([$job_id]);
            $success = 'Job deleted successfully.';
        }
    }
}

$jobs = $pdo->query("SELECT j.*, (SELECT COUNT(*) FROM career_applications WHERE job_id = j.job_id) as app_count FROM career_jobs j ORDER BY j.created_at DESC")->fetchAll();
$employmentTypes = ['Full-Time', 'Part-Time', 'Contract', 'Freelance', 'Internship', 'Remote'];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold">Job Listings</h5>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#jobModal" onclick="resetForm()">
        <i class="fas fa-plus"></i> Add Job
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
                        <th>Department</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Deadline</th>
                        <th>Applications</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $j): ?>
                    <tr>
                        <td><?= $j['job_id'] ?></td>
                        <td><strong><?= h($j['title']) ?></strong></td>
                        <td><?= h($j['department'] ?? '-') ?></td>
                        <td><?= h($j['location'] ?? '-') ?></td>
                        <td><span class="badge bg-info text-dark"><?= h($j['employment_type'] ?? '-') ?></span></td>
                        <td><?= $j['application_deadline'] ? date('M d, Y', strtotime($j['application_deadline'])) : '-' ?></td>
                        <td>
                            <a href="applications.php?job_id=<?= $j['job_id'] ?>" class="badge bg-primary text-decoration-none">
                                <?= $j['app_count'] ?> applications
                            </a>
                        </td>
                        <td><span class="badge bg-<?= $j['is_active'] ? 'success' : 'secondary' ?>"><?= $j['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editJob(<?= $j['job_id'] ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="viewApplications(<?= $j['job_id'] ?>)">
                                <i class="fas fa-users"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $j['job_id'] ?>, <?= h(json_encode($j['title'])) ?>)">
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

<div class="modal fade" id="jobModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="job_id" id="jobId" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Job</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="jobTitle" class="form-control" required onkeyup="document.getElementById('jobSlug').value = createSlug(this.value)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" id="jobSlug" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Department</label>
                            <input type="text" name="department" id="jobDept" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" id="jobLocation" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Employment Type</label>
                            <select name="employment_type" id="jobType" class="form-select">
                                <option value="">Select</option>
                                <?php foreach ($employmentTypes as $et): ?>
                                    <option value="<?= $et ?>"><?= $et ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Salary Range</label>
                            <input type="text" name="salary_range" id="jobSalary" class="form-control" placeholder="e.g. $50k-$70k">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Application Deadline</label>
                            <input type="date" name="application_deadline" id="jobDeadline" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="jobDesc" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Requirements</label>
                            <textarea name="requirements" id="jobRequirements" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Responsibilities</label>
                            <textarea name="responsibilities" id="jobResp" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="jobActive" value="1" checked>
                                <label class="form-check-label" for="jobActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Job</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form method="post" id="deleteForm">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="job_id" id="deleteId" value="">
</form>

<script>
function resetForm() {
    document.getElementById('formAction').value = 'add';
    document.getElementById('modalTitle').textContent = 'Add Job';
    document.getElementById('jobId').value = '';
    document.getElementById('jobTitle').value = '';
    document.getElementById('jobSlug').value = '';
    document.getElementById('jobDept').value = '';
    document.getElementById('jobLocation').value = '';
    document.getElementById('jobType').value = '';
    document.getElementById('jobDesc').value = '';
    document.getElementById('jobRequirements').value = '';
    document.getElementById('jobResp').value = '';
    document.getElementById('jobSalary').value = '';
    document.getElementById('jobDeadline').value = '';
    document.getElementById('jobActive').checked = true;
}

function createSlug(str) {
    return str.toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
}

function editJob(id) {
    fetch('get_job.php?id=' + id)
        .then(r => r.json())
        .then(data => {
            if (data) {
                document.getElementById('formAction').value = 'edit';
                document.getElementById('modalTitle').textContent = 'Edit Job';
                document.getElementById('jobId').value = data.job_id;
                document.getElementById('jobTitle').value = data.title;
                document.getElementById('jobSlug').value = data.slug;
                document.getElementById('jobDept').value = data.department || '';
                document.getElementById('jobLocation').value = data.location || '';
                document.getElementById('jobType').value = data.employment_type || '';
                document.getElementById('jobDesc').value = data.description || '';
                document.getElementById('jobRequirements').value = data.requirements || '';
                document.getElementById('jobResp').value = data.responsibilities || '';
                document.getElementById('jobSalary').value = data.salary_range || '';
                document.getElementById('jobDeadline').value = data.application_deadline || '';
                document.getElementById('jobActive').checked = data.is_active == 1;
                new bootstrap.Modal(document.getElementById('jobModal')).show();
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

function viewApplications(jobId) {
    window.location.href = 'applications.php?job_id=' + jobId;
}
</script>

<?php require_once __DIR__ . '/../partials/admin_footer.php'; ?>
