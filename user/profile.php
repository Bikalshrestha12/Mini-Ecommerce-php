<?php
// ============================================================
// user/profile.php – User Profile (view + edit)
// ============================================================

require_once __DIR__ . '/../includes/session.php';
requireLogin();

$pdo    = getDB();
$userId = $_SESSION['user'];
$msg    = '';
$error  = '';

// ── Handle profile update (POST) ────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // ---- Update Name / Gender ----
    if ($_POST['action'] === 'update_profile') {
        $name   = trim($_POST['name']   ?? '');
        $gender = trim($_POST['gender'] ?? '');

        if (empty($name) || strlen($name) < 2) {
            $error = 'Name must be at least 2 characters.';
        } elseif (!in_array($gender, ['Male', 'Female', 'Other'])) {
            $error = 'Please select a valid gender.';
        } else {
            $pdo->prepare('UPDATE users SET name = ?, gender = ? WHERE user_id = ?')
                ->execute([$name, $gender, $userId]);
            $_SESSION['name'] = $name;
            $msg = 'Profile updated successfully.';
        }
    }

    // ---- Change Password ----
    if ($_POST['action'] === 'change_password') {
        $currentPw = $_POST['current_password'] ?? '';
        $newPw     = $_POST['new_password']     ?? '';
        $confirmPw = $_POST['confirm_password'] ?? '';

        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE user_id = ?');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        if (!password_verify($currentPw, $row['password_hash'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($newPw) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($newPw !== $confirmPw) {
            $error = 'New passwords do not match.';
        } else {
            $hash = password_hash($newPw, PASSWORD_BCRYPT);
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE user_id = ?')
                ->execute([$hash, $userId]);
            $msg = 'Password changed successfully.';
        }
    }
}

// ── Fetch current user data ──────────────────────────────────
$stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

// ── Order stats ──────────────────────────────────────────────
$stmt = $pdo->prepare('SELECT COUNT(*) AS total, SUM(total_amount) AS spent FROM orders WHERE user_id = ?');
$stmt->execute([$userId]);
$stats = $stmt->fetch();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1><i class="fa-solid fa-user-circle"></i> My Profile</h1>
    <p>Manage your account details</p>
</div>

<div class="container">

    <?php if ($msg): ?>
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-circle-xmark"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Stats bar -->
    <div class="profile-stats">
        <div class="stat-card">
            <i class="fa-solid fa-box"></i>
            <span class="stat-value"><?= (int)$stats['total'] ?></span>
            <span class="stat-label">Total Orders</span>
        </div>
        <div class="stat-card">
            <i class="fa-solid fa-dollar-sign"></i>
            <span class="stat-value">$<?= number_format((float)($stats['spent'] ?? 0), 2) ?></span>
            <span class="stat-label">Total Spent</span>
        </div>
        <div class="stat-card">
            <i class="fa-solid fa-calendar-check"></i>
            <span class="stat-value"><?= date('M Y', strtotime($user['created_at'])) ?></span>
            <span class="stat-label">Member Since</span>
        </div>
        <div class="stat-card">
            <i class="fa-solid fa-<?= $user['confirm_status'] ? 'circle-check' : 'circle-xmark' ?>"></i>
            <span class="stat-value"><?= $user['confirm_status'] ? 'Verified' : 'Unverified' ?></span>
            <span class="stat-label">Account Status</span>
        </div>
    </div>

    <div class="profile-grid">

        <!-- ── Edit profile info ── -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-pen-to-square"></i> Account Details</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name"
                               value="<?= htmlspecialchars($user['name']) ?>"
                               required minlength="2">
                    </div>

                    <div class="form-group">
                        <label>Email (read-only)</label>
                        <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender" required>
                            <?php foreach (['Male','Female','Other'] as $g): ?>
                            <option value="<?= $g ?>"
                                <?= $user['gender'] === $g ? 'selected' : '' ?>>
                                <?= $g ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>

        <!-- ── Change password ── -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-lock"></i> Change Password</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="change_password">

                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>

                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password"
                               required minlength="8"
                               placeholder="Min. 8 characters">
                    </div>

                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="btn btn-secondary">
                        <i class="fa-solid fa-key"></i> Change Password
                    </button>
                </form>
            </div>
        </div>

    </div><!-- /.profile-grid -->

</div><!-- /.container -->

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
