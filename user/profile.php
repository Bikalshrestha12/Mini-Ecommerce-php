<?php
require_once __DIR__ . '/../includes/session.php';
requireLogin();
require_once __DIR__ . '/../includes/helpers.php';

$pdo    = getDB();
$userId = $_SESSION['user'];
$msg    = '';
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'update_profile') {
        $name   = trim($_POST['name']   ?? '');
        $gender = trim($_POST['gender'] ?? '');
        $phone  = trim($_POST['phone']  ?? '');
        $address = trim($_POST['address'] ?? '');

        if (empty($name) || strlen($name) < 2) {
            $error = 'Name must be at least 2 characters.';
        } elseif (!in_array($gender, ['Male', 'Female', 'Other'])) {
            $error = 'Please select a valid gender.';
        } else {
            $pdo->prepare('UPDATE users SET name = ?, gender = ?, phone = ?, address = ? WHERE user_id = ?')
                ->execute([$name, $gender, $phone ?: null, $address ?: null, $userId]);
            $_SESSION['name'] = $name;
            $msg = 'Profile updated successfully.';
        }
    }

    if ($_POST['action'] === 'upload_avatar') {
        if (!empty($_FILES['avatar']['name'])) {
            $avatar = uploadFile($_FILES['avatar'], 'avatars', ['jpg','jpeg','png','webp','gif']);
            if ($avatar) {
                $stmt = $pdo->prepare("SELECT avatar FROM users WHERE user_id = ?");
                $stmt->execute([$userId]);
                $old = $stmt->fetchColumn();
                $pdo->prepare('UPDATE users SET avatar = ? WHERE user_id = ?')->execute([$avatar, $userId]);
                if ($old && file_exists(__DIR__ . '/../' . $old)) {
                    unlink(__DIR__ . '/../' . $old);
                }
                $msg = 'Avatar updated successfully.';
            } else {
                $error = 'Avatar upload failed. Only jpg, png, webp, gif allowed (max 5MB).';
            }
        } else {
            $error = 'Please select an image to upload.';
        }
    }

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

$stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

$stmt = $pdo->prepare('SELECT COUNT(*) AS total, COALESCE(SUM(total_amount), 0) AS spent FROM orders WHERE user_id = ?');
$stmt->execute([$userId]);
$stats = $stmt->fetch();

$roleName = getUserRole($user['role_id'] ?? null);

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.profile-header-bg {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border-radius: 1.25rem;
  padding: 2.5rem;
  position: relative;
  overflow: hidden;
}
.profile-header-bg::before {
  content: '';
  position: absolute;
  inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
  opacity: 0.3;
}
.avatar-preview-wrap {
  width: 130px; height: 130px; border-radius: 50%;
  border: 4px solid rgba(255,255,255,0.3);
  overflow: hidden;
  position: relative;
  cursor: pointer;
}
.avatar-preview-wrap img {
  width: 100%; height: 100%; object-fit: cover;
}
.avatar-preview-wrap .overlay {
  position: absolute; inset: 0;
  background: rgba(0,0,0,0.4);
  display: flex; align-items: center; justify-content: center;
  opacity: 0; transition: opacity 0.3s;
  color: #fff; font-size: 0.85rem;
}
.avatar-preview-wrap:hover .overlay { opacity: 1; }
.avatar-placeholder-lg {
  width: 130px; height: 130px; border-radius: 50%;
  background: rgba(255,255,255,0.2);
  display: flex; align-items: center; justify-content: center;
  font-size: 3rem; color: #fff;
}
.profile-stat-card {
  background: #fff;
  border-radius: 0.75rem;
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,.06);
  transition: all 0.3s ease;
}
.profile-stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,.08); }
.profile-stat-card .ps-icon {
  width: 44px; height: 44px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem; flex-shrink: 0;
}
.form-floating-custom {
  position: relative;
  margin-bottom: 1.25rem;
}
.form-floating-custom label {
  position: absolute;
  top: 0.75rem;
  left: 0.875rem;
  font-size: 0.85rem;
  color: #94a3b8;
  transition: all 0.2s ease;
  pointer-events: none;
  background: #fff;
  padding: 0 0.25rem;
}
.form-floating-custom .form-control:focus ~ label,
.form-floating-custom .form-control:not(:placeholder-shown) ~ label,
.form-floating-custom .form-select:focus ~ label,
.form-floating-custom .form-select:not([value=""]):valid ~ label {
  top: -0.6rem;
  left: 0.75rem;
  font-size: 0.75rem;
  color: #6366f1;
}
.profile-card {
  background: #fff;
  border-radius: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,.06);
  overflow: hidden;
}
.profile-card .card-head {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
  font-weight: 700;
  color: #1e293b;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.profile-card .card-body-custom {
  padding: 1.5rem;
}
</style>

<div class="container-fluid px-4 py-4">

  <div class="profile-header-bg mb-4" data-aos="fade-up">
    <div class="d-flex align-items-center gap-4 position-relative">
      <form method="POST" enctype="multipart/form-data" id="avatarForm">
        <input type="hidden" name="action" value="upload_avatar">
        <div class="avatar-preview-wrap">
          <?php if ($user['avatar']): ?>
            <img src="<?= imgUrl($user['avatar']) ?>" alt="Avatar" id="avatarPreview">
          <?php else: ?>
            <div class="avatar-placeholder-lg" id="avatarPlaceholder"><?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?></div>
          <?php endif; ?>
          <div class="overlay"><i class="fas fa-camera"></i></div>
          <input type="file" name="avatar" id="avatarInput" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none">
        </div>
      </form>
      <div class="text-white">
        <h3 class="fw-bold mb-1"><?= h($user['name']) ?></h3>
        <p class="mb-0 opacity-75"><i class="fas fa-envelope me-1"></i><?= h($user['email']) ?> &middot; <i class="fas fa-tag me-1"></i><?= h($roleName) ?></p>
      </div>
    </div>
  </div>

  <?php if ($msg): ?>
  <div class="alert alert-success d-flex align-items-center gap-2" style="border-radius:0.75rem;border:none;background:#dcfce7;color:#166534" data-aos="fade-up">
    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>
  <?php if ($error): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2" style="border-radius:0.75rem;border:none;background:#fee2e2;color:#991b1b" data-aos="fade-up">
    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <div class="row g-3 mb-4">
    <div class="col-md-3" data-aos="fade-up" data-aos-delay="0">
      <div class="profile-stat-card">
        <div class="ps-icon" style="background:#e0e7ff;color:#6366f1"><i class="fas fa-box"></i></div>
        <div>
          <div class="fw-bold fs-5" style="color:#1e293b"><?= (int)$stats['total'] ?></div>
          <small class="text-muted">Total Orders</small>
        </div>
      </div>
    </div>
    <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
      <div class="profile-stat-card">
        <div class="ps-icon" style="background:#dcfce7;color:#22c55e"><i class="fas fa-dollar-sign"></i></div>
        <div>
          <div class="fw-bold fs-5" style="color:#1e293b">$<?= number_format((float)($stats['spent'] ?? 0), 2) ?></div>
          <small class="text-muted">Total Spent</small>
        </div>
      </div>
    </div>
    <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
      <div class="profile-stat-card">
        <div class="ps-icon" style="background:#dbeafe;color:#3b82f6"><i class="fas fa-calendar-check"></i></div>
        <div>
          <div class="fw-bold fs-5" style="color:#1e293b"><?= date('M Y', strtotime($user['created_at'])) ?></div>
          <small class="text-muted">Member Since</small>
        </div>
      </div>
    </div>
    <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
      <div class="profile-stat-card">
        <div class="ps-icon" style="background:#fef3c7;color:#f59e0b"><i class="fas fa-<?= $user['confirm_status'] ? 'check-circle' : 'times-circle' ?>"></i></div>
        <div>
          <div class="fw-bold fs-5" style="color:#1e293b"><?= $user['confirm_status'] ? 'Verified' : 'Unverified' ?></div>
          <small class="text-muted">Account Status</small>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-6" data-aos="fade-up">
      <div class="profile-card">
        <div class="card-head"><i class="fas fa-pen-to-square" style="color:#6366f1"></i> Account Details</div>
        <div class="card-body-custom">
          <form method="POST" action="">
            <input type="hidden" name="action" value="update_profile">

            <div class="form-floating-custom">
              <input type="text" name="name" class="form-control" placeholder="Full Name" required minlength="2" value="<?= htmlspecialchars($user['name']) ?>">
              <label>Full Name</label>
            </div>

            <div class="form-floating-custom">
              <input type="email" class="form-control" placeholder="Email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
              <label>Email (read-only)</label>
            </div>

            <div class="form-floating-custom">
              <input type="text" name="phone" class="form-control" placeholder="Phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
              <label>Phone</label>
            </div>

            <div class="form-floating-custom">
              <textarea name="address" class="form-control" placeholder="Address" rows="2" style="min-height:80px"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
              <label>Address</label>
            </div>

            <div class="form-floating-custom">
              <select name="gender" class="form-select" required>
                <?php foreach (['Male','Female','Other'] as $g): ?>
                <option value="<?= $g ?>" <?= $user['gender'] === $g ? 'selected' : '' ?>><?= $g ?></option>
                <?php endforeach; ?>
              </select>
              <label>Gender</label>
            </div>

            <div class="form-floating-custom">
              <input type="text" class="form-control" placeholder="Role" value="<?= htmlspecialchars($roleName) ?>" disabled>
              <label>Role</label>
            </div>

            <button type="submit" class="btn w-100" style="background:#6366f1;color:#fff;border-radius:0.5rem;padding:0.6rem 0;font-weight:600">
              <i class="fas fa-floppy-disk me-1"></i> Save Changes
            </button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
      <div class="profile-card">
        <div class="card-head"><i class="fas fa-lock" style="color:#6366f1"></i> Change Password</div>
        <div class="card-body-custom">
          <form method="POST" action="">
            <input type="hidden" name="action" value="change_password">

            <div class="form-floating-custom">
              <input type="password" name="current_password" class="form-control" placeholder="Current Password" required>
              <label>Current Password</label>
            </div>

            <div class="form-floating-custom">
              <input type="password" name="new_password" class="form-control" placeholder="New Password" required minlength="8">
              <label>New Password (min. 8 chars)</label>
            </div>

            <div class="form-floating-custom">
              <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
              <label>Confirm New Password</label>
            </div>

            <button type="submit" class="btn w-100" style="background:#1e293b;color:#fff;border-radius:0.5rem;padding:0.6rem 0;font-weight:600">
              <i class="fas fa-key me-1"></i> Change Password
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const avatarInput = document.getElementById('avatarInput');
  const avatarWrap = document.querySelector('.avatar-preview-wrap');
  if (avatarWrap) {
    avatarWrap.addEventListener('click', function() { avatarInput.click(); });
  }
  if (avatarInput) {
    avatarInput.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          let img = document.querySelector('#avatarPreview');
          if (!img) {
            const placeholder = document.getElementById('avatarPlaceholder');
            if (placeholder) placeholder.remove();
            img = document.createElement('img');
            img.id = 'avatarPreview';
            img.alt = 'Avatar';
            document.querySelector('.avatar-preview-wrap').prepend(img);
          }
          img.src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);
        document.getElementById('avatarForm').submit();
      }
    });
  }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
