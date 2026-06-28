<?php
// ============================================================
// user/verify.php – Email Verification
// ============================================================

require_once __DIR__ . '/../includes/session.php';

// Session security – must have arrived from signup flow
if (empty($_SESSION['verify_user_id'])) {
    header('Location: ' . APP_URL . '/index.php');
    exit;
}

$userId    = $_SESSION['verify_user_id'];
$userEmail = $_SESSION['verify_email'] ?? '';
$userName  = $_SESSION['verify_name']  ?? '';
$demoCode  = $_SESSION['demo_code']    ?? '';
$error     = '';
$success   = '';

// ── Handle POST (code submission) ───────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredCode = trim($_POST['code'] ?? '');

    if (empty($enteredCode)) {
        $error = 'Please enter the verification code.';
    } else {
        $pdo  = getDB();
        $stmt = $pdo->prepare(
            'SELECT id FROM confirm_codes
              WHERE user_id = ? AND confirmation_code = ?
              ORDER BY created_at DESC LIMIT 1'
        );
        $stmt->execute([$userId, $enteredCode]);
        $row = $stmt->fetch();

        if ($row) {
            // Mark account as verified
            $pdo->prepare('UPDATE users SET confirm_status = 1 WHERE user_id = ?')
                ->execute([$userId]);

            // Remove used code
            $pdo->prepare('DELETE FROM confirm_codes WHERE user_id = ?')
                ->execute([$userId]);

            // Clean session
            unset($_SESSION['verify_user_id'], $_SESSION['verify_email'],
                  $_SESSION['verify_name'],  $_SESSION['demo_code']);

            $_SESSION['success_msg'] = 'Account verified! You can now login.';
            header('Location: ' . APP_URL . '/index.php');
            exit;
        } else {
            $error = 'Invalid verification code. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Account – <?= htmlspecialchars(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="auth-page">

<div class="auth-wrapper auth-wrapper--narrow">
    <div class="auth-forms-panel">

        <div class="verify-icon">
            <i class="fa-solid fa-envelope-circle-check"></i>
        </div>

        <h2 class="form-title">Verify Your Account</h2>
        <p class="verify-sub">
            Hi <strong><?= htmlspecialchars($userName) ?></strong>,
            enter the 6-digit code sent to
            <strong><?= htmlspecialchars($userEmail) ?></strong>.
        </p>

        <!-- DEMO HINT – remove in production -->
        <?php if ($demoCode): ?>
        <div class="alert alert-info">
            <i class="fa-solid fa-circle-info"></i>
            <strong>Demo mode:</strong> Your code is <strong><?= htmlspecialchars($demoCode) ?></strong>
            (in production this would be emailed to you).
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fa-solid fa-circle-xmark"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="code">
                    <i class="fa-solid fa-key"></i> Verification Code
                </label>
                <input type="text" id="code" name="code"
                       placeholder="e.g. 482916"
                       maxlength="6" pattern="\d{6}"
                       inputmode="numeric" autocomplete="one-time-code"
                       required>
            </div>

            <button type="submit" class="btn btn-primary btn-full">
                <i class="fa-solid fa-circle-check"></i> Verify Account
            </button>
        </form>

        <p class="switch-link">
            <a href="<?= APP_URL ?>/index.php">← Back to Login</a>
        </p>

    </div>
</div>

<script src="<?= APP_URL ?>/assets/js/script.js"></script>
</body>
</html>
