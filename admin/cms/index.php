<?php
require_once __DIR__ . '/../../includes/session.php';
requireSuperAdmin();
require_once __DIR__ . '/../../includes/helpers.php';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_section'])) {
    $section_id = (int)$_POST['section_id'];
    $title = $_POST['title'] ?? '';
    $subtitle = $_POST['subtitle'] ?? '';
    $content = $_POST['content'] ?? '';
    $extra_json = $_POST['extra_json'] ?? '';
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $image = null;
    if (!empty($_FILES['image']['name'])) {
        $image = uploadFile($_FILES['image'], 'cms');
    }

    $sql = "UPDATE cms_sections SET title = ?, subtitle = ?, content = ?, extra_json = ?, sort_order = ?, is_active = ?";
    $params = [$title, $subtitle, $content, $extra_json, $sort_order, $is_active];

    if ($image) {
        $sql .= ", image = ?";
        $params[] = $image;
    }
    $sql .= " WHERE section_id = ?";
    $params[] = $section_id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $_SESSION['flash_success'] = 'CMS section updated successfully.';
    header('Location: index.php');
    exit;
}

$pageTitle = 'CMS Pages';
require_once __DIR__ . '/../partials/admin_header.php';
require_once __DIR__ . '/../partials/admin_sidebar.php';

$stmt = $pdo->query("SELECT DISTINCT page FROM cms_sections ORDER BY page");
$pages = $stmt->fetchAll();

$sectionsByPage = [];
foreach ($pages as $p) {
    $stmt = $pdo->prepare("SELECT * FROM cms_sections WHERE page = ? ORDER BY sort_order");
    $stmt->execute([$p['page']]);
    $sectionsByPage[$p['page']] = $stmt->fetchAll();
}
?>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title mb-0">CMS Pages</h1>
    </div>

    <?= flashMessage() ?>

    <ul class="nav nav-tabs" id="cmsTabs" role="tablist">
        <?php $first = true; foreach ($sectionsByPage as $page => $sections): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $first ? 'active' : '' ?>" id="tab-<?= h($page) ?>" data-bs-toggle="tab" data-bs-target="#page-<?= h($page) ?>" type="button" role="tab">
                <i class="fas fa-file"></i> <?= ucfirst(h($page)) ?>
            </button>
        </li>
        <?php $first = false; endforeach; ?>
    </ul>

    <div class="tab-content" id="cmsTabContent">
        <?php $first = true; foreach ($sectionsByPage as $page => $sections): ?>
        <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="page-<?= h($page) ?>" role="tabpanel">
            <?php foreach ($sections as $section): ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><strong><?= h($section['section_key']) ?></strong> — <?= h($section['title']) ?></span>
                    <span class="status-badge <?= $section['is_active'] ? 'status-active' : 'status-inactive' ?>">
                        <?= $section['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <?php if ($section['subtitle']): ?>
                            <p class="text-muted mb-1"><strong>Subtitle:</strong> <?= h($section['subtitle']) ?></p>
                            <?php endif; ?>
                            <p class="mb-1"><strong>Content:</strong></p>
                            <div class="bg-light p-3 rounded mb-2"><?= nl2br(h(mb_substr($section['content'] ?? '', 0, 500))) ?></div>
                            <p class="text-muted small mb-0">Sort: <?= (int)$section['sort_order'] ?></p>
                        </div>
                        <div class="col-md-4">
                            <?php if ($section['image']): ?>
                            <img src="<?= APP_URL ?>/<?= h($section['image']) ?>" class="preview-img img-fluid" alt="Section image">
                            <?php endif; ?>
                            <div class="mt-2">
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $section['section_id'] ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="editModal<?= $section['section_id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form method="post" enctype="multipart/form-data">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Section: <?= h($section['section_key']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="section_id" value="<?= $section['section_id'] ?>">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Title</label>
                                        <input type="text" name="title" class="form-control" value="<?= h($section['title']) ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Subtitle</label>
                                        <input type="text" name="subtitle" class="form-control" value="<?= h($section['subtitle']) ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Content</label>
                                    <textarea name="content" class="form-control" rows="5"><?= h($section['content']) ?></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Image</label>
                                        <input type="file" name="image" class="form-control" accept="image/*">
                                        <?php if ($section['image']): ?>
                                        <div class="mt-2">
                                            <img src="<?= APP_URL ?>/<?= h($section['image']) ?>" style="max-height:80px" class="rounded">
                                            <small class="text-muted d-block">Leave empty to keep current</small>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Sort Order</label>
                                        <input type="number" name="sort_order" class="form-control" value="<?= (int)$section['sort_order'] ?>">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <div class="form-check form-switch mt-2">
                                            <input type="checkbox" name="is_active" class="form-check-input" id="active<?= $section['section_id'] ?>" <?= $section['is_active'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="active<?= $section['section_id'] ?>">Active</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Extra JSON</label>
                                    <textarea name="extra_json" class="form-control" rows="3"><?= h($section['extra_json']) ?></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="update_section" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Section
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php $first = false; endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/admin_footer.php'; ?>
