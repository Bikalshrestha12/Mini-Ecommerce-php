<?php
require_once __DIR__ . '/../../includes/session.php';
requireSuperAdmin();
require_once __DIR__ . '/../../includes/helpers.php';
$pdo = getDB();
$pageTitle = 'Job Applications';
require_once __DIR__ . '/../partials/admin_header.php';
require_once __DIR__ . '/../partials/admin_sidebar.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_status') {
        $application_id = $_POST['application_id'] ?? '';
        $status = $_POST['status'] ?? '';
        $validStatuses = ['Pending', 'Reviewed', 'Shortlisted', 'Interviewed', 'Accepted', 'Rejected'];
        if ($application_id && in_array($status, $validStatuses)) {
            $stmt = $pdo->prepare("UPDATE career_applications SET status = ?, updated_at = datetime('now') WHERE application_id = ?");
            $stmt->execute([$status, $application_id]);
            $success = 'Application status updated.';
        }
    }

    if ($action === 'delete') {
        $application_id = $_POST['application_id'] ?? '';
        if ($application_id) {
            $stmt = $pdo->prepare("DELETE FROM career_applications WHERE application_id = ?");
            $stmt->execute([$application_id]);
            $success = 'Application deleted.';
        }
    }
}

$jobFilter = $_GET['job_id'] ?? '';
$sql = "SELECT a.*, j.title as job_title FROM career_applications a LEFT JOIN career_jobs j ON a.job_id = j.job_id";
$params = [];
if ($jobFilter) {
    $sql .= " WHERE a.job_id = ?";
    $params[] = $jobFilter;
}
$sql .= " ORDER BY a.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll();

$jobs = $pdo->query("SELECT job_id, title FROM career_jobs ORDER BY title")->fetchAll();
$statuses = ['Pending', 'Reviewed', 'Shortlisted', 'Interviewed', 'Accepted', 'Rejected'];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold">Job Applications</h5>
    <div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Jobs</a>
    </div>
</div>

<?php if ($errors): ?>
    <div class="alert alert-danger"><?= implode('<br>', array_map('h', $errors)) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= h($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="get" class="row g-2 align-items-center">
            <div class="col-auto">
                <label class="form-label mb-0 fw-bold">Filter by Job:</label>
            </div>
            <div class="col-auto">
                <select name="job_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Jobs</option>
                    <?php foreach ($jobs as $j): ?>
                        <option value="<?= $j['job_id'] ?>" <?= $jobFilter == $j['job_id'] ? 'selected' : '' ?>><?= h($j['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($jobFilter): ?>
                <div class="col-auto">
                    <a href="applications.php" class="btn btn-sm btn-outline-secondary">Clear Filter</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Applicant</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Job Title</th>
                        <th>Resume</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $a): ?>
                    <tr>
                        <td><?= $a['application_id'] ?></td>
                        <td><strong><?= h($a['applicant_name']) ?></strong></td>
                        <td><a href="mailto:<?= h($a['email']) ?>"><?= h($a['email']) ?></a></td>
                        <td><?= h($a['phone'] ?? '-') ?></td>
                        <td><?= h($a['job_title'] ?? '-') ?></td>
                        <td>
                            <?php if ($a['resume_file']): ?>
                                <a href="<?= APP_URL . '/' . h($a['resume_file']) ?>" class="btn btn-sm btn-outline-info" target="_blank">
                                    <i class="fas fa-download"></i> Resume
                                </a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= strtolower($a['status']) === 'accepted' ? 'success' : (strtolower($a['status']) === 'rejected' ? 'danger' : (strtolower($a['status']) === 'pending' ? 'warning text-dark' : 'primary')) ?>">
                                <?= h($a['status']) ?>
                            </span>
                        </td>
                        <td><?= date('M d, Y', strtotime($a['created_at'])) ?></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewApplication(<?= htmlspecialchars(json_encode($a)) ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $a['application_id'] ?>, <?= h(json_encode($a['applicant_name'])) ?>)">
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

<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Application Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="appDetails">
            </div>
            <div class="modal-footer">
                <form method="post" class="d-inline">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="application_id" id="statusAppId" value="">
                    <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                        <option value="">Update Status</option>
                        <?php foreach ($statuses as $s): ?>
                            <option value="<?= $s ?>"><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<form method="post" id="deleteForm">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="application_id" id="deleteId" value="">
</form>

<script>
function viewApplication(data) {
    document.getElementById('statusAppId').value = data.application_id;
    let html = '<table class="table table-bordered table-sm">';
    html += '<tr><th style="width:30%">Name</th><td>' + h(data.applicant_name) + '</td></tr>';
    html += '<tr><th>Email</th><td><a href="mailto:' + h(data.email) + '">' + h(data.email) + '</a></td></tr>';
    html += '<tr><th>Phone</th><td>' + h(data.phone || '-') + '</td></tr>';
    html += '<tr><th>Job</th><td>' + h(data.job_title || '-') + '</td></tr>';
    html += '<tr><th>Status</th><td><span class="badge bg-' + (data.status.toLowerCase() === 'accepted' ? 'success' : (data.status.toLowerCase() === 'rejected' ? 'danger' : (data.status.toLowerCase() === 'pending' ? 'warning text-dark' : 'primary'))) + '">' + h(data.status) + '</span></td></tr>';
    html += '<tr><th>Date</th><td>' + h(data.created_at) + '</td></tr>';
    html += '<tr><th>Cover Letter</th><td>' + h(data.cover_letter || 'N/A') + '</td></tr>';
    html += '<tr><th>Resume</th><td>' + (data.resume_file ? '<a href="<?= APP_URL ?>/' + h(data.resume_file) + '" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-download"></i> Download</a>' : 'N/A') + '</td></tr>';
    html += '</table>';
    document.getElementById('appDetails').innerHTML = html;
    new bootstrap.Modal(document.getElementById('viewModal')).show();
}

function confirmDelete(id, name) {
    Swal.fire({
        title: 'Delete application from "' + name + '"?',
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

function h(str) {
    if (!str) return '';
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

<?php require_once __DIR__ . '/../partials/admin_footer.php'; ?>
