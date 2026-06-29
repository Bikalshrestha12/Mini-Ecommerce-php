<?php
require_once __DIR__ . '/../../includes/session.php';
requireSuperAdmin();
require_once __DIR__ . '/../../includes/helpers.php';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_partner'])) {
        $partner_id = (int)($_POST['partner_id'] ?? 0);
        $name = $_POST['name'] ?? '';
        $website = $_POST['website'] ?? '';
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($name)) {
            $_SESSION['flash_error'] = 'Partner name is required.';
            header('Location: index.php');
            exit;
        }

        $logo = null;
        if (!empty($_FILES['logo']['name'])) {
            $logo = uploadFile($_FILES['logo'], 'partners');
        }

        if ($partner_id > 0) {
            $sql = "UPDATE partners SET name=?, website=?, sort_order=?, is_active=?";
            $params = [$name, $website, $sort_order, $is_active];
            if ($logo) { $sql .= ", logo=?"; $params[] = $logo; }
            $sql .= " WHERE partner_id=?";
            $params[] = $partner_id;
        } else {
            if (!$logo) {
                $_SESSION['flash_error'] = 'Logo is required.';
                header('Location: index.php');
                exit;
            }
            $sql = "INSERT INTO partners (name, logo, website, sort_order, is_active) VALUES (?,?,?,?,?)";
            $params = [$name, $logo, $website, $sort_order, $is_active];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $_SESSION['flash_success'] = $partner_id > 0 ? 'Partner updated successfully.' : 'Partner added successfully.';
        header('Location: index.php');
        exit;
    }

    if (isset($_POST['delete_partner'])) {
        $partner_id = (int)$_POST['partner_id'];
        $stmt = $pdo->prepare("SELECT logo FROM partners WHERE partner_id=?");
        $stmt->execute([$partner_id]);
        $p = $stmt->fetch();
        if ($p && $p['logo']) {
            $path = __DIR__ . '/../../' . $p['logo'];
            if (file_exists($path)) unlink($path);
        }
        $stmt = $pdo->prepare("DELETE FROM partners WHERE partner_id=?");
        $stmt->execute([$partner_id]);
        $_SESSION['flash_success'] = 'Partner deleted successfully.';
        header('Location: index.php');
        exit;
    }
}

$partners = $pdo->query("SELECT * FROM partners ORDER BY sort_order")->fetchAll();

$pageTitle = 'Partners & Clients';
require_once __DIR__ . '/../partials/admin_header.php';
require_once __DIR__ . '/../partials/admin_sidebar.php';
?>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title mb-0">Partners & Clients</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#partnerModal">
            <i class="fas fa-plus"></i> Add Partner
        </button>
    </div>

    <?= flashMessage() ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="partnersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Logo</th>
                            <th>Name</th>
                            <th>Website</th>
                            <th>Sort</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($partners as $p): ?>
                        <tr>
                            <td><?= $p['partner_id'] ?></td>
                            <td>
                                <img src="<?= APP_URL ?>/<?= h($p['logo']) ?>" class="img-thumb" alt="<?= h($p['name']) ?>" style="width:80px;height:auto">
                            </td>
                            <td><?= h($p['name']) ?></td>
                            <td>
                                <?php if ($p['website']): ?>
                                <a href="<?= h($p['website']) ?>" target="_blank" class="text-decoration-none">
                                    <i class="fas fa-external-link-alt"></i> Visit
                                </a>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= (int)$p['sort_order'] ?></td>
                            <td><span class="status-badge <?= $p['is_active'] ? 'status-active' : 'status-inactive' ?>"><?= $p['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-partner"
                                    data-id="<?= $p['partner_id'] ?>"
                                    data-name="<?= h($p['name'], ENT_QUOTES) ?>"
                                    data-website="<?= h($p['website'], ENT_QUOTES) ?>"
                                    data-sort_order="<?= $p['sort_order'] ?>"
                                    data-is_active="<?= $p['is_active'] ?>"
                                    data-logo="<?= h($p['logo']) ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-partner" data-id="<?= $p['partner_id'] ?>">
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

<div class="modal fade" id="partnerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="partnerModalLabel">Add Partner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="partner_id" id="partner_id" value="0">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="partner_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Logo</label>
                        <input type="file" name="logo" class="form-control" accept="image/*" id="partner_logo_input">
                        <div id="partner_logo_preview" class="mt-2"></div>
                        <small class="text-muted">Recommended: 200x100px transparent PNG</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Website URL</label>
                        <input type="url" name="website" id="partner_website" class="form-control" placeholder="https://example.com">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="partner_sort_order" class="form-control" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch mt-4">
                                <input type="checkbox" name="is_active" class="form-check-input" id="partner_is_active" checked>
                                <label class="form-check-label" for="partner_is_active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_partner" class="btn btn-primary"><i class="fas fa-save"></i> Save Partner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form method="post" id="deleteForm">
    <input type="hidden" name="partner_id" id="delete_id">
    <input type="hidden" name="delete_partner">
</form>

<?php $footerExtra = <<<JS
<script>
$(document).ready(function() {
    $('#partnersTable').DataTable({
        order: [[4, 'asc']],
        pageLength: 25
    });

    $('.edit-partner').click(function() {
        const d = $(this).data();
        $('#partnerModalLabel').text('Edit Partner');
        $('#partner_id').val(d.id);
        $('#partner_name').val(d.name);
        $('#partner_website').val(d.website);
        $('#partner_sort_order').val(d.sort_order);
        $('#partner_is_active').prop('checked', d.is_active == 1);
        if (d.logo) {
            $('#partner_logo_preview').html('<img src="' + APP_URL + '/' + d.logo + '" class="preview-img" style="max-height:60px"><small class="text-muted d-block">Leave empty to keep current</small>');
        } else {
            $('#partner_logo_preview').empty();
        }
        $('#partnerModal').modal('show');
    });

    $('#partnerModal').on('hidden.bs.modal', function() {
        if ($('#partner_id').val() == 0) {
            $(this).find('form')[0].reset();
            $('#partner_logo_preview').empty();
            $('#partnerModalLabel').text('Add Partner');
        }
    });

    $('.delete-partner').click(function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Partner?',
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
