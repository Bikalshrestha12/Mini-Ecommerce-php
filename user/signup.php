<?php
// ============================================================
// user/signup.php – Registration Handler (POST only)
// ============================================================

require_once __DIR__ . '/../includes/session.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/user/auth.php');
    exit;
}

redirectIfLoggedIn('/product/products.php');

$name             = trim($_POST['name']             ?? '');
$email            = trim($_POST['email']            ?? '');
$gender           = trim($_POST['gender']           ?? '');
$password         = $_POST['password']              ?? '';
$confirm_password = $_POST['confirm_password']      ?? '';

// ── Validate inputs ──────────────────────────────────────────
$errors = [];

if (empty($name)) {
    $errors[] = 'Full name is required.';
} elseif (strlen($name) < 2 || strlen($name) > 100) {
    $errors[] = 'Name must be between 2 and 100 characters.';
}

if (empty($email)) {
    $errors[] = 'Email is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

if (!in_array($gender, ['Male', 'Female', 'Other'])) {
    $errors[] = 'Please select a valid gender.';
}

if (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters long.';
}

if ($password !== $confirm_password) {
    $errors[] = 'Passwords do not match.';
}

if (!empty($errors)) {
    $_SESSION['signup_error']        = implode(' ', $errors);
    $_SESSION['prefill_name']        = $name;
    $_SESSION['prefill_signup_email'] = $email;
    header('Location: ' . APP_URL . '/user/auth.php');
    exit;
}

// ── Check duplicate email ────────────────────────────────────
$pdo  = getDB();
$stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);

if ($stmt->fetch()) {
    $_SESSION['signup_error']        = 'This email is already registered. Please login.';
    $_SESSION['prefill_signup_email'] = $email;
    header('Location: ' . APP_URL . '/user/auth.php');
    exit;
}

// ── Create account ───────────────────────────────────────────
$userId       = 'USR-' . strtoupper(bin2hex(random_bytes(8)));
$passwordHash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare(
    'INSERT INTO users (user_id, name, email, password_hash, gender, role_id, confirm_status)
     VALUES (?, ?, ?, ?, ?, 1, 0)'
);
$stmt->execute([$userId, $name, $email, $passwordHash, $gender]);

// ── Generate 6-digit verification code ──────────────────────
$code = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

$stmt = $pdo->prepare(
    'INSERT INTO confirm_codes (user_id, confirmation_code) VALUES (?, ?)'
);
$stmt->execute([$userId, $code]);

// ── Store in session for verify page ────────────────────────
$_SESSION['verify_user_id'] = $userId;
$_SESSION['verify_email']   = $email;
$_SESSION['verify_name']    = $name;

// In a real app you'd email the code; here we pass it in session for demo
$_SESSION['demo_code'] = $code;

header('Location: ' . APP_URL . '/user/verify.php');
exit;
