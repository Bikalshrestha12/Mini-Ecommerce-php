<?php
require_once __DIR__ . '/../../includes/session.php';
requireSuperAdmin();
require_once __DIR__ . '/../../includes/helpers.php';

$pdo = getDB();
$errors = [];
$success = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- Add User ---
    if ($action === 'add') {
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $gender  = $_POST['gender'] ?? 'Other';
        $role_id = (int)($_POST['role_id'] ?? 1);
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (!$name || !$email || !$password) {
            $errors[] = 'Name, email, and password are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address.';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        } else {
            $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetchColumn() > 0) {
                $errors[] = 'Email already exists.';
            } else {
                $userId = 'USR-' . bin2hex(random_bytes(8));
                $hash   = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (user_id, name, email, password_hash, gender, role_id, phone, address, confirm_status, is_active) VALUES (?,?,?,?,?,?,?,?,1,1)");
                $stmt->execute([$userId, $name, $email, $hash, $gender, $role_id, $phone, $address]);
                $success = 'User created successfully.';
            }
        }
    }

    // --- Edit User ---
    elseif ($action === 'edit') {
        $user_id = $_POST['user_id'] ?? '';
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $gender  = $_POST['gender'] ?? 'Other';
        $role_id = (int)($_POST['role_id'] ?? 1);
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (!$name || !$email) {
            $errors[] = 'Name and email are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address.';
        } else {
            $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND user_id != ?");
            $check->execute([$email, $user_id]);
            if ($check->fetchColumn() > 0) {
                $errors[] = 'Email already in use by another user.';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, gender=?, role_id=?, phone=?, address=? WHERE user_id=?");
                $stmt->execute([$name, $email, $gender, $role_id, $phone, $address, $user_id]);
                $success = 'User updated successfully.';
            }
        }
    }

    // --- Delete User ---
    elseif ($action === 'delete') {
        $user_id = $_POST['user_id'] ?? '';
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        echo json_encode(['success' => true, 'message' => 'User deleted successfully.']);
        exit;
    }

    // --- Toggle Status ---
    elseif ($action === 'toggle_status') {
        $user_id = $_POST['user_id'] ?? '';
        $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $status = $pdo->prepare("SELECT is_active FROM users WHERE user_id = ?");
        $status->execute([$user_id]);
        $row = $status->fetch();
        echo json_encode(['success' => true, 'is_active' => (int)$row['is_active']]);
        exit;
    }

    // --- Reset Password ---
    elseif ($action === 'reset_password') {
        $user_id  = $_POST['user_id'] ?? '';
        $password = $_POST['password'] ?? '';
        if (strlen($password) < 8) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters.']);
            exit;
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $stmt->execute([$hash, $user_id]);
        echo json_encode(['success' => true, 'message' => 'Password reset successfully.']);
        exit;
    }

    // Flash messages for non-AJAX
    if ($action !== 'delete' && $action !== 'toggle_status' && $action !== 'reset_password') {
        if ($success) $_SESSION['flash_success'] = $success;
        if ($errors)  $_SESSION['flash_error']  = implode('\\n', $errors);
        header('Location: ' . APP_URL . '/admin/users/');
        exit;
    }
}

$users = $pdo->query("SELECT u.*, r.role_name FROM users u LEFT JOIN roles r ON r.role_id = u.role_id ORDER BY u.created_at DESC")->fetchAll();
$roles = $pdo->query("SELECT * FROM roles ORDER BY role_id")->fetchAll();

$pageTitle = 'User Management';
include_once __DIR__ . '/../../admin/partials/admin_header.php';
include_once __DIR__ . '/../../admin/partials/admin_sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="fas fa-users me-2"></i>User Management</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fas fa-plus"></i> Add User
    </button>
</div>

<?= flashMessage() ?>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover datatable mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Gender</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><code><?= h($u['user_id']) ?></code></td>
                        <td><strong><?= h($u['name']) ?></strong></td>
                        <td><?= h($u['email']) ?></td>
                        <td><?= h($u['gender']) ?></td>
                        <td><span class="badge bg-<?= $u['role_id'] == 2 ? 'primary' : 'secondary' ?>"><?= h($u['role_name'] ?? 'User') ?></span></td>
                        <td>
                            <span class="badge bg-<?= $u['is_active'] ? 'success' : 'danger' ?> status-badge-<?= h($u['user_id']) ?>">
                                <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td class="small"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-info text-white edit-user" data-user='<?= h(json_encode($u)) ?>' title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-<?= $u['is_active'] ? 'warning' : 'success' ?> text-white toggle-status" data-id="<?= h($u['user_id']) ?>" title="<?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                    <i class="fas fa-<?= $u['is_active'] ? 'ban' : 'check' ?>"></i>
                                </button>
                                <button class="btn btn-secondary reset-pw" data-id="<?= h($u['user_id']) ?>" data-name="<?= h($u['name']) ?>" title="Reset Password">
                                    <i class="fas fa-key"></i>
                                </button>
                                <button class="btn btn-danger delete-user" data-id="<?= h($u['user_id']) ?>" data-name="<?= h($u['name']) ?>" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" class="modal-content">
            <input type="hidden" name="action" value="add">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                        <small class="text-muted">Min. 8 characters</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other" selected>Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role</label>
                        <select name="role_id" class="form-select">
                            <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['role_id'] ?>"><?= h($r['role_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" class="modal-content">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="user_id" id="edit_user_id">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gender</label>
                        <select name="gender" id="edit_gender" class="form-select">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role</label>
                        <select name="role_id" id="edit_role_id" class="form-select">
                            <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['role_id'] ?>"><?= h($r['role_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" id="edit_phone" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea name="address" id="edit_address" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-info text-white"><i class="fas fa-save"></i> Update User</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // --- Edit User ---
    $(document).on('click', '.edit-user', function() {
        var user = $(this).data('user');
        $('#edit_user_id').val(user.user_id);
        $('#edit_name').val(user.name);
        $('#edit_email').val(user.email);
        $('#edit_gender').val(user.gender);
        $('#edit_role_id').val(user.role_id);
        $('#edit_phone').val(user.phone);
        $('#edit_address').val(user.address);
        $('#editUserModal').modal('show');
    });

    // --- Delete User ---
    $(document).on('click', '.delete-user', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        Swal.fire({
            title: 'Delete User?',
            html: 'Are you sure you want to delete <strong>' + name + '</strong>?<br>This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: '<i class="fas fa-trash"></i> Delete',
            cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.post('', { action: 'delete', user_id: id }, function(res) {
                    if (res.success) {
                        Swal.fire('Deleted!', res.message, 'success').then(function() {
                            location.reload();
                        });
                    }
                }, 'json');
            }
        });
    });

    // --- Toggle Status ---
    $(document).on('click', '.toggle-status', function() {
        var btn = $(this);
        var id = btn.data('id');
        $.post('', { action: 'toggle_status', user_id: id }, function(res) {
            if (res.success) {
                var badge = $('.status-badge-' + id);
                if (res.is_active) {
                    badge.removeClass('bg-danger').addClass('bg-success').text('Active');
                    btn.removeClass('btn-success').addClass('btn-warning').attr('title', 'Deactivate');
                    btn.find('i').removeClass('fa-check').addClass('fa-ban');
                } else {
                    badge.removeClass('bg-success').addClass('bg-danger').text('Inactive');
                    btn.removeClass('btn-warning').addClass('btn-success').attr('title', 'Activate');
                    btn.find('i').removeClass('fa-ban').addClass('fa-check');
                }
            }
        }, 'json');
    });

    // --- Reset Password ---
    $(document).on('click', '.reset-pw', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        Swal.fire({
            title: 'Reset Password for ' + name,
            html: '<div class="mb-3"><label class="form-label text-start d-block">New Password</label><input type="password" id="new-password" class="form-control" placeholder="Min. 8 characters" minlength="8"></div>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#6366f1',
            confirmButtonText: '<i class="fas fa-key"></i> Reset',
            cancelButtonText: 'Cancel',
            preConfirm: function() {
                var pw = $('#new-password').val();
                if (!pw || pw.length < 8) {
                    Swal.showValidationMessage('Password must be at least 8 characters');
                }
                return pw;
            }
        }).then(function(result) {
            if (result.isConfirmed) {
                $.post('', { action: 'reset_password', user_id: id, password: result.value }, function(res) {
                    if (res.success) {
                        Swal.fire('Success!', res.message, 'success');
                    } else {
                        Swal.fire('Error!', res.message, 'error');
                    }
                }, 'json');
            }
        });
    });
});
</script>

<?php include_once __DIR__ . '/../../admin/partials/admin_footer.php'; ?>
