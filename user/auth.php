<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
$pdo = getDB();

$loginError  = $_SESSION['login_error']  ?? '';
$signupError = $_SESSION['signup_error'] ?? '';
$successMsg  = $_SESSION['success_msg']  ?? '';
$loginSuccess = $_SESSION['login_success'] ?? '';

unset($_SESSION['login_error'], $_SESSION['signup_error'], $_SESSION['success_msg']);

$activeForm = (!empty($signupError)) ? 'signup' : 'login';

if (isLoggedIn()) {
    header('Location: ' . APP_URL . '/index.php');
    exit;
}

require_once __DIR__ . '/../includes/public_header.php';
?>

<style>
.auth-card {
  background: #fff;
  border-radius: 1.5rem;
  box-shadow: 0 8px 40px rgba(0,0,0,.08);
  overflow: hidden;
  padding: 2.5rem;
}
.auth-card .auth-header {
  text-align: center;
  margin-bottom: 2rem;
}
.auth-card .auth-header .auth-icon {
  width: 64px; height: 64px; border-radius: 50%;
  background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 1rem;
  font-size: 1.5rem; color: #6366f1;
}
.tab-switch {
  display: flex;
  background: #f1f5f9;
  border-radius: 0.75rem;
  padding: 4px;
  position: relative;
  margin-bottom: 2rem;
}
.tab-switch .tab-btn {
  flex: 1;
  border: none;
  background: none;
  padding: 0.7rem 1rem;
  border-radius: 0.6rem;
  font-weight: 600;
  color: #64748b;
  transition: all 0.3s;
  position: relative;
  z-index: 2;
  cursor: pointer;
}
.tab-switch .tab-btn.active {
  color: #fff;
}
.tab-switch .tab-slider {
  position: absolute;
  top: 4px; bottom: 4px;
  width: calc(50% - 4px);
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  border-radius: 0.6rem;
  transition: all 0.3s ease;
  z-index: 1;
}
.tab-switch .tab-slider.right { transform: translateX(100%); }
.floating-group {
  position: relative;
  margin-bottom: 1.25rem;
}
.floating-group .form-control,
.floating-group .form-select {
  height: 54px;
  border-radius: 0.75rem;
  border: 2px solid #e2e8f0;
  padding: 1.4rem 1rem 0.4rem;
  font-size: 0.95rem;
  transition: all 0.2s;
  background: #fff;
}
.floating-group .form-control:focus,
.floating-group .form-select:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99,102,241,.12);
}
.floating-group .form-control::placeholder { color: transparent; }
.floating-group label {
  position: absolute;
  top: 0.95rem;
  left: 1rem;
  font-size: 0.85rem;
  color: #94a3b8;
  transition: all 0.2s ease;
  pointer-events: none;
  background: #fff;
  padding: 0 0.25rem;
}
.floating-group .form-control:focus ~ label,
.floating-group .form-control:not(:placeholder-shown) ~ label,
.floating-group .form-select:focus ~ label,
.floating-group .form-select:not([value=""]):valid ~ label {
  top: 0.25rem;
  left: 0.85rem;
  font-size: 0.7rem;
  color: #6366f1;
}
.btn-gradient {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff;
  border: none;
  border-radius: 0.75rem;
  padding: 0.75rem 1rem;
  font-weight: 600;
  width: 100%;
  transition: all 0.3s;
}
.btn-gradient:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(99,102,241,.35);
  color: #fff;
}
.social-login {
  display: flex;
  gap: 0.75rem;
  justify-content: center;
  margin-top: 1.5rem;
}
.social-login .social-btn {
  width: 44px; height: 44px; border-radius: 50%;
  border: 2px solid #e2e8f0;
  display: flex; align-items: center; justify-content: center;
  color: #64748b;
  transition: all 0.2s;
  cursor: pointer;
  text-decoration: none;
}
.social-login .social-btn:hover {
  border-color: #6366f1;
  color: #6366f1;
  background: #e0e7ff;
}
.divider-text {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin: 1.5rem 0;
  color: #94a3b8;
  font-size: 0.85rem;
}
.divider-text::before,
.divider-text::after {
  content: '';
  flex: 1;
  border-bottom: 1px solid #e2e8f0;
}
.alert-premium {
  border-radius: 0.75rem;
  border: none;
  padding: 0.75rem 1rem;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.alert-premium.alert-success { background: #dcfce7; color: #166534; }
.alert-premium.alert-danger { background: #fee2e2; color: #991b1b; }
</style>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-5">
      <div class="auth-card" data-aos="fade-up">
        <div class="auth-header">
          <div class="auth-icon"><i class="fas fa-store"></i></div>
          <h3 class="fw-bold mb-1" style="color:#1e293b">Welcome</h3>
          <p class="text-muted mb-0">Sign in or create your account</p>
        </div>

        <div class="tab-switch" id="tabSwitch">
          <div class="tab-slider <?= $activeForm === 'signup' ? 'right' : '' ?>" id="tabSlider"></div>
          <button class="tab-btn <?= $activeForm === 'login' ? 'active' : '' ?>" data-tab="login" id="loginTabBtn">Login</button>
          <button class="tab-btn <?= $activeForm === 'signup' ? 'active' : '' ?>" data-tab="signup" id="signupTabBtn">Sign Up</button>
        </div>

        <?php if ($successMsg): ?>
        <div class="alert-premium alert-success mb-3"><?= h($successMsg) ?></div>
        <?php endif; ?>
        <?php if ($loginSuccess): ?>
        <div class="alert-premium alert-success mb-3"><?= h($loginSuccess) ?></div>
        <?php endif; ?>

        <div id="loginForm" class="tab-pane-custom" style="display:<?= $activeForm === 'login' ? 'block' : 'none' ?>">
          <form action="<?= APP_URL ?>/user/login.php" method="POST">
            <?php if ($loginError): ?>
            <div class="alert-premium alert-danger mb-3"><?= h($loginError) ?></div>
            <?php endif; ?>

            <div class="floating-group">
              <input type="email" name="email" class="form-control" placeholder="Email" required value="<?= h($_SESSION['prefill_email'] ?? '') ?>">
              <label>Email</label>
            </div>
            <div class="floating-group">
              <input type="password" name="password" class="form-control" placeholder="Password" required>
              <label>Password</label>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="remember_me" value="1" id="rememberMe">
                <label class="form-check-label small" for="rememberMe" style="color:#64748b">Remember me</label>
              </div>
            </div>
            <button type="submit" class="btn-gradient"><i class="fas fa-sign-in-alt me-1"></i> Login</button>
          </form>
          <div class="divider-text">or continue with</div>
          <div class="social-login">
            <a href="#" class="social-btn"><i class="fab fa-google"></i></a>
            <a href="#" class="social-btn"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-btn"><i class="fab fa-github"></i></a>
          </div>
        </div>

        <div id="signupForm" class="tab-pane-custom" style="display:<?= $activeForm === 'signup' ? 'block' : 'none' ?>">
          <form action="<?= APP_URL ?>/user/signup.php" method="POST">
            <?php if ($signupError): ?>
            <div class="alert-premium alert-danger mb-3"><?= h($signupError) ?></div>
            <?php endif; ?>

            <div class="floating-group">
              <input type="text" name="name" class="form-control" placeholder="Full Name" required value="<?= h($_SESSION['prefill_name'] ?? '') ?>">
              <label>Full Name</label>
            </div>
            <div class="floating-group">
              <input type="email" name="email" class="form-control" placeholder="Email" required value="<?= h($_SESSION['prefill_signup_email'] ?? '') ?>">
              <label>Email</label>
            </div>
            <div class="floating-group">
              <select name="gender" class="form-select" required>
                <option value="" disabled selected></option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
              </select>
              <label>Gender</label>
            </div>
            <div class="floating-group">
              <input type="password" name="password" class="form-control" placeholder="Password" required minlength="8">
              <label>Password</label>
            </div>
            <div class="floating-group">
              <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
              <label>Confirm Password</label>
            </div>
            <button type="submit" class="btn-gradient"><i class="fas fa-user-plus me-1"></i> Create Account</button>
          </form>
          <div class="divider-text">or sign up with</div>
          <div class="social-login">
            <a href="#" class="social-btn"><i class="fab fa-google"></i></a>
            <a href="#" class="social-btn"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-btn"><i class="fab fa-github"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const loginTab = document.getElementById('loginTabBtn');
  const signupTab = document.getElementById('signupTabBtn');
  const slider = document.getElementById('tabSlider');
  const loginForm = document.getElementById('loginForm');
  const signupForm = document.getElementById('signupForm');

  function showLogin() {
    loginTab.classList.add('active');
    signupTab.classList.remove('active');
    slider.classList.remove('right');
    loginForm.style.display = 'block';
    signupForm.style.display = 'none';
  }
  function showSignup() {
    signupTab.classList.add('active');
    loginTab.classList.remove('active');
    slider.classList.add('right');
    signupForm.style.display = 'block';
    loginForm.style.display = 'none';
  }

  if (loginTab) loginTab.addEventListener('click', showLogin);
  if (signupTab) signupTab.addEventListener('click', showSignup);
});
</script>

<?php
unset($_SESSION['prefill_email'], $_SESSION['prefill_name'], $_SESSION['prefill_signup_email']);
?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
