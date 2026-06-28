<?php
// ============================================================
// index.php – Landing / Login / Sign-up Page
// ============================================================

require_once __DIR__ . '/includes/session.php';

// Already logged in → go to products
redirectIfLoggedIn('/product/products.php');

$loginError  = $_SESSION['login_error']  ?? '';
$signupError = $_SESSION['signup_error'] ?? '';
$successMsg  = $_SESSION['success_msg']  ?? '';

unset($_SESSION['login_error'], $_SESSION['signup_error'], $_SESSION['success_msg']);

$activeForm = (!empty($signupError)) ? 'signup' : 'login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(APP_NAME) ?> – Welcome</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #e0e7ff;
            --bg: #f8fafc;
        }
        
        body.auth-page {
            background: linear-gradient(135deg, #e0e7ff 0%, #f1f5f9 50%, #dbeafe 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            display: flex;
            align-items: center;
        }
        
        .auth-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
            width: 100%;
        }
        
        .auth-brand {
            background: linear-gradient(135deg, var(--primary) 0%, #818cf8 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 3rem 2rem;
            gap: 1rem;
        }
        
        .brand-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .auth-brand h1 {
            font-size: 2rem;
            font-weight: 800;
            margin: 0;
        }
        
        .auth-brand p {
            opacity: 0.9;
            font-size: 1rem;
            margin: 0;
        }
        
        .brand-badges {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 1rem;
        }
        
        .brand-badges span {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.4rem 0.9rem;
            border-radius: 999px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        
        .auth-forms-panel {
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 2.5rem 2rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .auth-tabs {
            display: flex;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 1.5rem;
            gap: 0;
        }
        
        .auth-tab {
            flex: 1;
            padding: 0.75rem 1rem;
            background: none;
            border: none;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            font-size: 0.95rem;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .auth-tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }
        
        .auth-form {
            display: none;
            flex-direction: column;
            gap: 1rem;
        }
        
        .auth-form.form-active {
            display: flex;
        }
        
        .form-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }
        
        .form-group label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        
        .form-group input,
        .form-group select {
            padding: 0.75rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            font-family: inherit;
            color: #1e293b;
            background: #f8fafc;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
            background: #fff;
        }
        
        .input-password-wrap {
            position: relative;
        }
        
        .input-password-wrap input {
            padding-right: 3rem;
        }
        
        .toggle-pw {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            font-size: 1rem;
            padding: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.88rem;
            cursor: pointer;
            color: #1e293b;
        }
        
        .checkbox-label input {
            width: auto;
            margin: 0;
            border: 1.5px solid #e2e8f0;
            padding: 0 !important;
            background: none !important;
            cursor: pointer;
        }
        
        .link-sm {
            font-size: 0.85rem;
            color: var(--primary);
            text-decoration: none;
        }
        
        .link-sm:hover {
            text-decoration: underline;
        }
        
        .switch-link {
            text-align: center;
            color: #64748b;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .switch-link a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }
        
        .switch-link a:hover {
            text-decoration: underline;
        }
        
        .btn-auth {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 100%;
        }
        
        .btn-primary-auth {
            background: var(--primary);
            color: #fff;
        }
        
        .btn-primary-auth:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
        }
        
        @media (max-width: 768px) {
            .auth-wrapper {
                grid-template-columns: 1fr;
            }
            
            .auth-brand {
                display: none;
            }
            
            .auth-forms-panel {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body class="auth-page">

<div class="auth-wrapper">

    <!-- ── Brand panel ─────────────────────────── -->
    <div class="auth-brand">
        <i class="fa-solid fa-bag-shopping brand-icon"></i>
        <h1><?= htmlspecialchars(APP_NAME) ?></h1>
        <p>Your modern shopping destination.<br>Great products, great prices.</p>
        <div class="brand-badges">
            <span><i class="fa-solid fa-shield-halved"></i> Secure</span>
            <span><i class="fa-solid fa-truck"></i> Fast Delivery</span>
            <span><i class="fa-solid fa-tags"></i> Best Deals</span>
        </div>
    </div>

    <!-- ── Auth forms panel ─────────────────────── -->
    <div class="auth-forms-panel">

        <!-- Tab switcher -->
        <div class="auth-tabs">
            <button class="auth-tab <?= $activeForm === 'login'  ? 'active' : '' ?>"
                    onclick="showForm('login')">Login</button>
            <button class="auth-tab <?= $activeForm === 'signup' ? 'active' : '' ?>"
                    onclick="showForm('signup')">Sign Up</button>
        </div>

        <!-- Global messages -->
        <?php if ($successMsg): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($successMsg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- ── LOGIN FORM ── -->
        <form id="loginForm"
              class="auth-form <?= $activeForm === 'login' ? 'form-active' : '' ?>"
              action="<?= APP_URL ?>/user/login.php"
              method="POST">

            <h2 class="form-title">Welcome Back</h2>

            <?php if ($loginError): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($loginError) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="login_email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="login_email" name="email"
                       class="form-control"
                       placeholder="Enter your email" required
                       value="<?= htmlspecialchars($_SESSION['prefill_email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="login_password"><i class="fas fa-lock"></i> Password</label>
                <div class="input-password-wrap">
                    <input type="password" id="login_password" name="password"
                           class="form-control"
                           placeholder="Enter your password" required>
                    <button type="button" class="toggle-pw" onclick="togglePw('login_password', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-row">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember_me" value="1">
                    Remember me
                </label>
                <a href="#" class="link-sm">Forgot password?</a>
            </div>

            <button type="submit" class="btn-auth btn-primary-auth">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>

            <p class="switch-link">
                Don't have an account?
                <a onclick="showForm('signup')">Sign up here</a>
            </p>
        </form>

        <!-- ── SIGN-UP FORM ── -->
        <form id="signupForm"
              class="auth-form <?= $activeForm === 'signup' ? 'form-active' : '' ?>"
              action="<?= APP_URL ?>/user/signup.php"
              method="POST">

            <h2 class="form-title">Create Account</h2>

            <?php if ($signupError): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($signupError) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="su_name"><i class="fas fa-user"></i> Full Name</label>
                <input type="text" id="su_name" name="name"
                       class="form-control"
                       placeholder="Enter your full name" required
                       value="<?= htmlspecialchars($_SESSION['prefill_name'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="su_email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="su_email" name="email"
                       class="form-control"
                       placeholder="Enter your email" required
                       value="<?= htmlspecialchars($_SESSION['prefill_signup_email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="su_gender"><i class="fas fa-venus-mars"></i> Gender</label>
                <select id="su_gender" name="gender" class="form-control" required>
                    <option value="">Select gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="su_password"><i class="fas fa-lock"></i> Password</label>
                <div class="input-password-wrap">
                    <input type="password" id="su_password" name="password"
                           class="form-control"
                           placeholder="Min. 8 characters" required minlength="8">
                    <button type="button" class="toggle-pw" onclick="togglePw('su_password', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="su_confirm"><i class="fas fa-lock"></i> Confirm Password</label>
                <div class="input-password-wrap">
                    <input type="password" id="su_confirm" name="confirm_password"
                           class="form-control"
                           placeholder="Re-enter password" required>
                    <button type="button" class="toggle-pw" onclick="togglePw('su_confirm', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-auth btn-primary-auth">
                <i class="fas fa-user-plus"></i> Create Account
            </button>

            <p class="switch-link">
                Already have an account?
                <a onclick="showForm('login')">Login here</a>
            </p>
        </form>

    </div><!-- /.auth-forms-panel -->
</div><!-- /.auth-wrapper -->

<?php
unset($_SESSION['prefill_email'], $_SESSION['prefill_name'], $_SESSION['prefill_signup_email']);
?>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function showForm(formName) {
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    const loginTab = document.querySelectorAll('.auth-tab')[0];
    const signupTab = document.querySelectorAll('.auth-tab')[1];
    
    if (formName === 'login') {
        loginForm.classList.add('form-active');
        signupForm.classList.remove('form-active');
        loginTab.classList.add('active');
        signupTab.classList.remove('active');
    } else {
        signupForm.classList.add('form-active');
        loginForm.classList.remove('form-active');
        signupTab.classList.add('active');
        loginTab.classList.remove('active');
    }
}

function togglePw(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
</body>
</html>
