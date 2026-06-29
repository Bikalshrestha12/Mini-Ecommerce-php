<?php
// ============================================================
// user/login.php – Login Handler (POST only)
// ============================================================

require_once __DIR__ . '/../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/user/auth.php');
    exit;
}

redirectIfLoggedIn('/product/products.php');

$email      = trim($_POST['email']    ?? '');
$password   = $_POST['password']      ?? '';
$rememberMe = !empty($_POST['remember_me']);

// ── Basic validation ─────────────────────────────────────────
if (empty($email) || empty($password)) {
    $_SESSION['login_error']   = 'Email and password are required.';
    $_SESSION['prefill_email'] = $email;
    header('Location: ' . APP_URL . '/user/auth.php');
    exit;
}

// ── Prepared statement lookup ────────────────────────────────
$pdo  = getDB();
$stmt = $pdo->prepare(
    'SELECT u.user_id, u.name, u.email, u.password_hash, u.confirm_status, u.role_id, u.is_active, r.role_name
       FROM users u
       JOIN roles r ON r.role_id = u.role_id
      WHERE u.email = ? LIMIT 1'
);
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    $_SESSION['login_error']   = 'Invalid email or password.';
    $_SESSION['prefill_email'] = $email;
    header('Location: ' . APP_URL . '/user/auth.php');
    exit;
}

if (!$user['confirm_status']) {
    $_SESSION['login_error']   = 'Please verify your email before logging in.';
    $_SESSION['prefill_email'] = $email;
    header('Location: ' . APP_URL . '/user/auth.php');
    exit;
}

if (!$user['is_active']) {
    $_SESSION['login_error']   = 'Your account has been deactivated. Contact admin.';
    $_SESSION['prefill_email'] = $email;
    header('Location: ' . APP_URL . '/user/auth.php');
    exit;
}

// ── Create session ───────────────────────────────────────────
session_regenerate_id(true);
$_SESSION['user']     = $user['user_id'];
$_SESSION['name']     = $user['name'];
$_SESSION['email']    = $user['email'];
$_SESSION['role_id']  = $user['role_id'];
$_SESSION['role']     = $user['role_name'];

// ── Remember Me ──────────────────────────────────────────────
if ($rememberMe) {
    $token     = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + (COOKIE_DAYS * 86400));

    // Remove any old tokens for this user
    $pdo->prepare('DELETE FROM user_tokens WHERE user_id = ?')
        ->execute([$user['user_id']]);

    $pdo->prepare(
        'INSERT INTO user_tokens (user_id, token, expires_at) VALUES (?, ?, ?)'
    )->execute([$user['user_id'], $token, $expiresAt]);

    setcookie(
        COOKIE_NAME,
        $token,
        time() + (COOKIE_DAYS * 86400),
        '/',
        '',
        false,  // secure – set true on HTTPS
        true    // httponly
    );
}

// Redirect based on role
$redirectUrl = ($user['role_id'] == 2) ? APP_URL . '/admin/index.php' : APP_URL . '/product/products.php';
header('Location: ' . $redirectUrl);
exit;
