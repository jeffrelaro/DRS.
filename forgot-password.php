<?php
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors  = [];
$email   = '';
$sent    = false;
$devLink = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors['form'] = 'Your session expired. Please try again.';
    }

    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!validate_email($email)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (!$errors) {
        try {
            $pdo  = db();
            $addr = strtolower($email);

            $stmt = $pdo->prepare('SELECT id FROM residents WHERE email = ? LIMIT 1');
            $stmt->execute([$addr]);

            if ($stmt->fetch()) {
                $pdo->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$addr]);

                $token = bin2hex(random_bytes(32));
                $pdo->prepare('INSERT INTO password_resets (email, token_hash, expires_at) VALUES (?, ?, ?)')
                    ->execute([
                        $addr,
                        hash('sha256', $token),
                        date('Y-m-d H:i:s', time() + RESET_MINUTES * 60),
                    ]);

                $link = APP_URL . '/reset-password.php?token=' . $token;

                if (DEV_MODE) {
                    $devLink = $link;
                } else {
                    $body = "Hello,\n\nUse this link to reset your Resident Portal password "
                          . "(valid for " . RESET_MINUTES . " minutes):\n\n$link\n\n"
                          . "If you did not request this, you can ignore this email.";
                    @mail($addr, 'Reset your Resident Portal password', $body,
                          'From: no-reply@catarman-portal.local');
                }
            }

            $sent = true;
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            $errors['form'] = 'Something went wrong. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Forgot Password | Resident Portal</title>
  <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
<div class="page">
  <div class="auth-card">
    <?php include __DIR__ . '/includes/brand_panel.php'; ?>

    <main class="form-panel">
      <?php if ($sent): ?>
        <div class="form-inner center">
          <div class="success-icon">
            <svg width="34" height="34" viewBox="0 0 34 34" aria-hidden="true"><rect x="4" y="8" width="26" height="18" rx="3" fill="none" stroke="#fff" stroke-width="2.5"/><path d="M5 10l12 9 12-9" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <h1>Check your email</h1>
          <p class="sub">If an account exists for <strong><?= e($email) ?></strong>,
             a password reset link is on its way. It is valid for <?= RESET_MINUTES ?> minutes.</p>

          <?php if ($devLink): ?>
            <div class="alert info">
              <strong>Dev mode:</strong> no mail server is set up, so here is the link:<br>
              <a href="<?= e($devLink) ?>"><?= e($devLink) ?></a>
            </div>
          <?php endif; ?>

          <a href="login.php" class="btn">Back to Sign In</a>
        </div>

      <?php else: ?>
        <div class="form-inner">
          <a href="login.php" class="back-link">&larr; Back to sign in</a><br>
          <span class="chip">ACCOUNT RECOVERY</span>
          <h1>Forgot password?</h1>
          <p class="sub">Enter the email you used to register and we'll send you a link to reset your password.</p>

          <?php if (isset($errors['form'])): ?>
            <div class="alert error"><?= e($errors['form']) ?></div>
          <?php endif; ?>

          <form method="post" action="forgot-password.php" novalidate>
            <?= csrf_field() ?>

            <div class="<?= field_class($errors, 'email') ?> spaced">
              <label for="email">Email Address</label>
              <input type="email" id="email" name="email" value="<?= e($email) ?>"
                     placeholder="you@example.com" maxlength="100" autocomplete="email">
              <?php field_err($errors, 'email'); ?>
            </div>

            <button type="submit" class="btn">Send Reset Link</button>
          </form>

          <p class="switch">Remembered it? <a href="login.php">Sign in</a></p>
        </div>
      <?php endif; ?>
    </main>
  </div>
</div>
<script src="assets/js/auth.js"></script>
</body>
</html>
