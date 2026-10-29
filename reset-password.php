<?php
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$token  = trim($_GET['token'] ?? $_POST['token'] ?? '');
$reset  = null;

try {
    $reset = find_reset($token);
} catch (PDOException $ex) {
    error_log($ex->getMessage());
    $errors['form'] = 'Something went wrong. Please try again later.';
}

if ($reset && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors['form'] = 'Your session expired. Please try again.';
    }

    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    } elseif (($msg = password_error($password)) !== '') {
        $errors['password'] = $msg;
    }

    if ($confirm === '') {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($confirm !== $password) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (!$errors) {
        try {
            $pdo = db();
            $pdo->beginTransaction();

            $pdo->prepare('UPDATE residents SET password_hash = ? WHERE email = ?')
                ->execute([password_hash($password, PASSWORD_DEFAULT), $reset['email']]);

            $pdo->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$reset['email']]);

            $pdo->commit();

            flash_set('success', 'Password updated. You can now sign in.');
            redirect('login.php');
        } catch (PDOException $ex) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
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
  <title>Reset Password | Resident Portal</title>
  <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
<div class="page">
  <div class="auth-card">
    <?php include __DIR__ . '/includes/brand_panel.php'; ?>

    <main class="form-panel">
      <?php if (!$reset): ?>
        <div class="form-inner center">
          <h1>Link expired</h1>
          <p class="sub">This password reset link is invalid or has expired. Please request a new one.</p>
          <?php if (isset($errors['form'])): ?>
            <div class="alert error"><?= e($errors['form']) ?></div>
          <?php endif; ?>
          <a href="forgot-password.php" class="btn">Request a new link</a>
          <p class="switch"><a href="login.php">&larr; Back to sign in</a></p>
        </div>

      <?php else: ?>
        <div class="form-inner">
          <span class="chip">ACCOUNT RECOVERY</span>
          <h1>Set a new password</h1>
          <p class="sub">Your new password must be at least 8 characters and include a letter and a number.</p>

          <?php if (isset($errors['form'])): ?>
            <div class="alert error"><?= e($errors['form']) ?></div>
          <?php endif; ?>

          <form method="post" action="reset-password.php" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= e($token) ?>">

            <div class="<?= field_class($errors, 'password') ?>">
              <label for="password">New Password</label>
              <div class="input-wrap">
                <input type="password" id="password" name="password"
                       placeholder="At least 8 characters" autocomplete="new-password">
                <button type="button" class="toggle-pw">Show</button>
              </div>
              <div class="meter" id="meter" data-s="0"><i></i><i></i><i></i><i></i></div>
              <div class="meter-label" id="meterLabel"></div>
              <?php field_err($errors, 'password'); ?>
            </div>

            <div class="<?= field_class($errors, 'confirm_password') ?> spaced">
              <label for="confirm_password">Confirm New Password</label>
              <div class="input-wrap">
                <input type="password" id="confirm_password" name="confirm_password"
                       placeholder="Re-enter your new password" autocomplete="new-password">
                <button type="button" class="toggle-pw">Show</button>
              </div>
              <?php field_err($errors, 'confirm_password'); ?>
            </div>

            <button type="submit" class="btn">Reset Password</button>
          </form>

          <p class="switch"><a href="login.php">&larr; Back to sign in</a></p>
        </div>
      <?php endif; ?>
    </main>
  </div>
</div>
<script src="assets/js/auth.js"></script>
</body>
</html>
