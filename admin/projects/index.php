<?php
require_once __DIR__ . '/../../includes/session.php';
requireSuperAdmin();
require_once __DIR__ . '/../../includes/helpers.php';
$pdo = getDB();
$pageTitle = 'Projects';
require_once __DIR__ . '/../partials/admin_header.php';
require_once __DIR__ . '/../partials/admin_sidebar.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $project_id = $_POST['project_id'] ?? '';
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category_id = trim($_POST['category_id'] ?? '');
        $client_name = trim($_POST['client_name'] ?? '');
        $completion_date = trim($_POST['completion_date'] ?? '');
        $project_url = trim($_POST['project_url'] ?? '');
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($title)) $errors[] = 'Project title is required.';
        if (empty($slug)) $slug = createSlug($title);
        if (empty($category_id)) $errors[] = 'Category is required.';

        if (empty($errors)) {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO projects (title, slug, description, category_id, client_name, completion_date, project_url, is_featured, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$title, $slug, $description, $category_id, $client_name, $completion_date ?: null, $project_url, $is_featured, $is_active]);
                $project_id = $pdo->lastInsertId();
                $success = 'Project added successfully.';
            } else {
                $stmt = $pdo->prepare("UPDATE projects SET title=?, slug=?, description=?, category_id=?, client_name=?, completion_date=?, project_url=?, is_featured=?, is_active=?, updated_at=NOW() WHERE project_id=?");
                $stmt->execute([$title, $slug, $description, $category_id, $client_name, $completion_date ?: null, $project_url, $is_featured, $is_active, $project_id]);
                $success = 'Project updated successfully.';
            }
        }
    }

    if ($action === 'delete') {
        $project_id = $_POST['project_id'] ?? '';
        if ($project_id) {
            $stmt = $pdo->prepare("DELETE FROM projects WHERE project_id = ?");
            $stmt->execute([$project_id]);
            $success = 'Project deleted successfully.';
        }
    }

    if ($action === 'delete_image') {
        $image_id = $_POST['image_id'] ?? '';
        if ($image_id) {
            $stmt = $pdo->prepare("DELETE FROM project_images WHERE image_id = ?");
            $stmt->execute([$image_id]);
            $success = 'Image deleted.';
        }
    }

    if ($action === 'delete_video') {
        $video_id = $_POST['video_id'] ?? '';
        if ($video_id) {
            $stmt = $pdo->prepare("DELETE FROM project_videos WHERE video_id = ?");
            $stmt->execute([$video_id]);
            $success = 'Video deleted.';
        }
    }

    if ($action === 'delete_document') {
        $doc_id = $_POST['doc_id'] ?? '';
        if ($doc_id) {
            $stmt = $pdo->prepare("DELETE FROM project_documents WHERE doc_id = ?");
            $stmt->execute([$doc_id]);
            $success = 'Document deleted.';
        }
    }

    if ($action === 'upload_image') {
        $project_id = $_POST['project_id'] ?? '';
        if ($project_id && !empty($_FILES['media_image']['name'])) {
            $img = uploadFile($_FILES['media_image'], 'projects');
            if ($img) {
                $stmt = $pdo->prepare("INSERT INTO project_images (project_id, image) VALUES (?, ?)");
                $stmt->execute([$project_id, $img]);
                $success = 'Image uploaded.';
            } else {
                $errors[] = 'Image upload failed.';
            }
        }
    }

    if ($action === 'add_video') {
        $project_id = $_POST['project_id'] ?? '';
        $video_url = trim($_POST['video_url'] ?? '');
        $video_title = trim($_POST['video_title'] ?? '');
        if ($project_id && $video_url) {
            $stmt = $pdo->prepare("INSERT INTO project_videos (project_id, video_url, title) VALUES (?, ?, ?)");
            $stmt->execute([$project_id, $video_url, $video_title]);
            $success = 'Video added.';
        }
    }

    if ($action === 'upload_document') {
        $project_id = $_POST['project_id'] ?? '';
        $doc_title = trim($_POST['doc_title'] ?? '');
        if ($project_id && !empty($_FILES['media_document']['name'])) {
            $doc = uploadFile($_FILES['media_document'], 'documents', ['pdf','doc','docx','xls','xlsx','ppt','pptx']);
            if ($doc) {
                $stmt = $pdo->prepare("INSERT INTO project_documents (project_id, document, title) VALUES (?, ?, ?)");
                $stmt->execute([$project_id, $doc, $doc_title]);
                $success = 'Document uploaded.';
            } else {
                $errors[] = 'Document upload failed.';
            }
        }
    }
}

$projects = $pdo->query("SELECT p.*, pc.name as category_name FROM projects p LEFT JOIN project_categories pc ON p.category_id = pc.category_id ORDER BY p.project_id DESC")->fetchAll();
$categories = $pdo->query("SELECT * FROM project_categories WHERE is_active = 1 ORDER BY name")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold">Projects</h5>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#projectModal" onclick="resetProjectForm()">
        <i class="fas fa-plus"></i> Add Project
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
                        <th>Category</th>
                        <th>Client</th>
                        <th>Completion</th>
                        <th>Featured</th>
                        <th>Status</th>
                        <th>Media</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $pr): ?>
                    <tr>
                        <td><?= $pr['project_id'] ?></td>
                        <td><strong><?= h($pr['title']) ?></strong></td>
                        <td><?= h($pr['category_name'] ?? '-') ?></td>
                        <td><?= h($pr['client_name'] ?? '-') ?></td>
                        <td><?= $pr['completion_date'] ? date('M d, Y', strtotime($pr['completion_date'])) : '-' ?></td>
                        <td><?= $pr['is_featured'] ? '<span class="badge bg-warning text-dark"><i class="fas fa-star"></i></span>' : '-' ?></td>
                        <td><span class="badge bg-<?= $pr['is_active'] ? 'success' : 'secondary' ?>"><?= $pr['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="showMedia(<?= $pr['project_id'] ?>)">
                                <i class="fas fa-paperclip"></i>
                            </button>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editProject(<?= $pr['project_id'] ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $pr['project_id'] ?>, <?= h(json_encode($pr['title'])) ?>)">
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

<div class="modal fade" id="projectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="project_id" id="projectId" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="projectTitle" class="form-control" required onkeyup="document.getElementById('projectSlug').value = createSlug(this.value)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" id="projectSlug" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="projectDesc" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category_id" id="projectCategory" class="form-select" required>
                                <option value="">Select</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['category_id'] ?>"><?= h($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Client Name</label>
                            <input type="text" name="client_name" id="projectClient" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Completion Date</label>
                            <input type="date" name="completion_date" id="projectDate" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Project URL</label>
                            <input type="url" name="project_url" id="projectUrl" class="form-control" placeholder="https://...">
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="projectFeatured" value="1">
                                <label class="form-check-label" for="projectFeatured">Featured</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="is_active" id="projectActive" value="1" checked>
                                <label class="form-check-label" for="projectActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Project</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="mediaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Project Media</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="mediaContent">
            </div>
        </div>
    </div>
</div>

<form method="post" id="deleteForm">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="project_id" id="deleteId" value="">
</form>

<script>
function resetProjectForm() {
    document.getElementById('formAction').value = 'add';
    document.getElementById('modalTitle').textContent = 'Add Project';
    document.getElementById('projectId').value = '';
    document.getElementById('projectTitle').value = '';
    document.getElementById('projectSlug').value = '';
    document.getElementById('projectDesc').value = '';
    document.getElementById('projectCategory').value = '';
    document.getElementById('projectClient').value = '';
    document.getElementById('projectDate').value = '';
    document.getElementById('projectUrl').value = '';
    document.getElementById('projectFeatured').checked = false;
    document.getElementById('projectActive').checked = true;
}

function createSlug(str) {
    return str.toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
}

function editProject(id) {
    fetch('get_project.php?id=' + id)
        .then(r => r.json())
        .then(data => {
            if (data) {
                document.getElementById('formAction').value = 'edit';
                document.getElementById('modalTitle').textContent = 'Edit Project';
                document.getElementById('projectId').value = data.project_id;
                document.getElementById('projectTitle').value = data.title;
                document.getElementById('projectSlug').value = data.slug;
                document.getElementById('projectDesc').value = data.description;
                document.getElementById('projectCategory').value = data.category_id;
                document.getElementById('projectClient').value = data.client_name || '';
                document.getElementById('projectDate').value = data.completion_date || '';
                document.getElementById('projectUrl').value = data.project_url || '';
                document.getElementById('projectFeatured').checked = data.is_featured == 1;
                document.getElementById('projectActive').checked = data.is_active == 1;
                new bootstrap.Modal(document.getElementById('projectModal')).show();
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

function showMedia(projectId) {
    fetch('get_project.php?id=' + projectId)
        .then(r => r.json())
        .then(project => {
            let html = '<h6 class="fw-bold mb-3">Images</h6>';
            html += '<form method="post" enctype="multipart/form-data" class="mb-3">';
            html += '<input type="hidden" name="action" value="upload_image">';
            html += '<input type="hidden" name="project_id" value="' + projectId + '">';
            html += '<div class="input-group input-group-sm"><input type="file" name="media_image" class="form-control" accept="image/*" required><button class="btn btn-primary" type="submit"><i class="fas fa-upload"></i> Upload</button></div>';
            html += '</form>';

            if (project.images && project.images.length) {
                html += '<div class="row g-2 mb-3">';
                project.images.forEach(img => {
                    html += '<div class="col-md-3"><div class="position-relative border rounded p-1"><img src="<?= APP_URL ?>/' + img.image + '" class="img-fluid" style="height:80px;width:100%;object-fit:cover;">';
                    html += '<form method="post" class="position-absolute" style="top:2px;right:2px;"><input type="hidden" name="action" value="delete_image"><input type="hidden" name="image_id" value="' + img.image_id + '"><button class="btn btn-sm btn-danger" onclick="return confirm(\'Delete this image?\')"><i class="fas fa-times"></i></button></form></div></div>';
                });
                html += '</div>';
            } else {
                html += '<p class="text-muted small">No images yet.</p>';
            }

            html += '<hr><h6 class="fw-bold mb-3">Videos</h6>';
            html += '<form method="post" class="mb-3 row g-2">';
            html += '<input type="hidden" name="action" value="add_video">';
            html += '<input type="hidden" name="project_id" value="' + projectId + '">';
            html += '<div class="col-5"><input type="text" name="video_title" class="form-control form-control-sm" placeholder="Video title"></div>';
            html += '<div class="col-5"><input type="url" name="video_url" class="form-control form-control-sm" placeholder="YouTube/Vimeo URL" required></div>';
            html += '<div class="col-2"><button class="btn btn-sm btn-primary w-100" type="submit"><i class="fas fa-plus"></i></button></div>';
            html += '</form>';

            if (project.videos && project.videos.length) {
                html += '<ul class="list-group list-group-sm mb-3">';
                project.videos.forEach(v => {
                    html += '<li class="list-group-item d-flex justify-content-between align-items-center py-1">';
                    html += '<span><a href="' + h(v.video_url) + '" target="_blank">' + h(v.title || v.video_url) + '</a></span>';
                    html += '<form method="post" class="m-0"><input type="hidden" name="action" value="delete_video"><input type="hidden" name="video_id" value="' + v.video_id + '"><button class="btn btn-sm btn-danger" onclick="return confirm(\'Delete?\')"><i class="fas fa-trash"></i></button></form></li>';
                });
                html += '</ul>';
            } else {
                html += '<p class="text-muted small">No videos yet.</p>';
            }

            html += '<hr><h6 class="fw-bold mb-3">Documents</h6>';
            html += '<form method="post" enctype="multipart/form-data" class="mb-3">';
            html += '<input type="hidden" name="action" value="upload_document">';
            html += '<input type="hidden" name="project_id" value="' + projectId + '">';
            html += '<div class="row g-2"><div class="col-5"><input type="text" name="doc_title" class="form-control form-control-sm" placeholder="Document title"></div><div class="col-5"><input type="file" name="media_document" class="form-control form-control-sm" required></div><div class="col-2"><button class="btn btn-sm btn-primary w-100" type="submit"><i class="fas fa-upload"></i></button></div></div>';
            html += '</form>';

            if (project.documents && project.documents.length) {
                html += '<ul class="list-group list-group-sm">';
                project.documents.forEach(d => {
                    html += '<li class="list-group-item d-flex justify-content-between align-items-center py-1">';
                    html += '<span><a href="<?= APP_URL ?>/' + d.document + '" target="_blank"><i class="fas fa-file"></i> ' + h(d.title || d.document) + '</a></span>';
                    html += '<form method="post" class="m-0"><input type="hidden" name="action" value="delete_document"><input type="hidden" name="doc_id" value="' + d.doc_id + '"><button class="btn btn-sm btn-danger" onclick="return confirm(\'Delete?\')"><i class="fas fa-trash"></i></button></form></li>';
                });
                html += '</ul>';
            } else {
                html += '<p class="text-muted small">No documents yet.</p>';
            }

            document.getElementById('mediaContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('mediaModal')).show();
        });
}

function h(str) {
    if (!str) return '';
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

<?php require_once __DIR__ . '/../partials/admin_footer.php'; ?>
