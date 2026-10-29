<?php
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$email  = '';
$flash  = flash_get();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors['form'] = 'Your session expired. Please try again.';
    }

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!validate_email($email)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    }

    if (!$errors) {
        try {
            $stmt = db()->prepare('SELECT id, first_name, password_hash FROM residents WHERE email = ? LIMIT 1');
            $stmt->execute([strtolower($email)]);
            $user = $stmt->fetch();

            $verified = password_verify($password, $user['password_hash'] ?? DUMMY_HASH);

            if ($user && $verified) {
                session_regenerate_id(true);
                $_SESSION['resident_id']   = (int) $user['id'];
                $_SESSION['resident_name'] = $user['first_name'];

                if ($remember) {
                    setcookie(session_name(), session_id(), [
                        'expires'  => time() + 60 * 60 * 24 * 30,
                        'path'     => '/',
                        'httponly' => true,
                        'samesite' => 'Lax',
                    ]);
                }
                redirect('dashboard.php');
            }

            $errors['form'] = 'Incorrect email or password.';
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
  <title>Sign In | Resident Portal</title>
  <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
<div class="page">
  <div class="auth-card">
    <?php include __DIR__ . '/includes/brand_panel.php'; ?>

    <main class="form-panel">
      <div class="form-inner">
        <span class="chip">RESIDENT ACCESS</span>
        <h1>Welcome back!</h1>
        <p class="sub">Sign in to request and track your barangay documents.</p>

        <?php if ($flash): ?>
          <div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>
        <?php if (isset($errors['form'])): ?>
          <div class="alert error"><?= e($errors['form']) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php" novalidate>
          <?= csrf_field() ?>

          <div class="<?= field_class($errors, 'email') ?>">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>"
                   placeholder="you@example.com" maxlength="100" autocomplete="email">
            <?php field_err($errors, 'email'); ?>
          </div>

          <div class="<?= field_class($errors, 'password') ?>">
            <label for="password">Password</label>
            <div class="input-wrap">
              <input type="password" id="password" name="password"
                     placeholder="Enter your password" autocomplete="current-password">
              <button type="button" class="toggle-pw">Show</button>
            </div>
            <?php field_err($errors, 'password'); ?>
          </div>

          <div class="row-between">
            <label class="check"><input type="checkbox" name="remember" value="1"> Remember me</label>
            <a href="forgot-password.php">Forgot password?</a>
          </div>

          <button type="submit" class="btn">Sign In</button>
        </form>

        <p class="switch">Don't have an account? <a href="signup.php">Sign up</a></p>
      </div>
    </main>
  </div>
</div>
<script src="assets/js/auth.js"></script>
</body>
</html>
