<?php
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$old = [
    'first_name' => '',
    'last_name'  => '',
    'email'      => '',
    'phone'      => '',
    'age'        => '',
    'address'    => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors['form'] = 'Your session expired. Please try again.';
    }

    foreach ($old as $field => $_) {
        $old[$field] = trim($_POST[$field] ?? '');
    }
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $terms    = isset($_POST['terms']);

    if ($old['first_name'] === '') {
        $errors['first_name'] = 'First name is required.';
    } elseif (!validate_name($old['first_name'])) {
        $errors['first_name'] = 'First name must be 2-50 letters.';
    }

    if ($old['last_name'] === '') {
        $errors['last_name'] = 'Last name is required.';
    } elseif (!validate_name($old['last_name'])) {
        $errors['last_name'] = 'Last name must be 2-50 letters.';
    }

    if ($old['email'] === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!validate_email($old['email'])) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($old['phone'] === '') {
        $errors['phone'] = 'Phone number is required.';
    } elseif (!validate_phone($old['phone'])) {
        $errors['phone'] = 'Enter a valid mobile number (09XX XXX XXXX).';
    }

    if ($old['age'] === '') {
        $errors['age'] = 'Age is required.';
    } elseif (validate_age($old['age']) === false) {
        $errors['age'] = 'Age must be a whole number from 1 to 120.';
    }

    if ($old['address'] === '') {
        $errors['address'] = 'Address is required.';
    } elseif (strlen($old['address']) > 150) {
        $errors['address'] = 'Address must be 150 characters or fewer.';
    }

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

    if (!$terms) {
        $errors['terms'] = 'You must agree to continue.';
    }

    if (!$errors) {
        try {
            $pdo   = db();
            $email = strtolower($old['email']);

            $check = $pdo->prepare('SELECT id FROM residents WHERE email = ? LIMIT 1');
            $check->execute([$email]);

            if ($check->fetch()) {
                $errors['email'] = 'This email is already registered.';
            } else {
                $insert = $pdo->prepare(
                    'INSERT INTO residents (first_name, last_name, email, phone, age, address, password_hash)
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                $insert->execute([
                    $old['first_name'],
                    $old['last_name'],
                    $email,
                    normalize_phone($old['phone']),
                    (int) $old['age'],
                    $old['address'],
                    password_hash($password, PASSWORD_DEFAULT),
                ]);

                flash_set('success', 'Account created! You can now sign in.');
                redirect('login.php');
            }
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') {
                $errors['email'] = 'This email is already registered.';
            } else {
                error_log($ex->getMessage());
                $errors['form'] = 'Something went wrong. Please try again later.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Create Account | Resident Portal</title>
  <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
<div class="page">
  <div class="auth-card">
    <?php include __DIR__ . '/includes/brand_panel.php'; ?>

    <main class="form-panel">
      <div class="form-inner">
        <span class="chip">NEW RESIDENT</span>
        <h1>Create your account</h1>
        <p class="sub">Use your real information so the barangay can verify you.</p>

        <?php if (isset($errors['form'])): ?>
          <div class="alert error"><?= e($errors['form']) ?></div>
        <?php endif; ?>

        <form method="post" action="signup.php" novalidate>
          <?= csrf_field() ?>

          <div class="row">
            <div class="<?= field_class($errors, 'first_name') ?>">
              <label for="first_name">First Name</label>
              <input type="text" id="first_name" name="first_name" value="<?= e($old['first_name']) ?>"
                     placeholder="Jash Vincent" maxlength="50" autocomplete="given-name">
              <?php field_err($errors, 'first_name'); ?>
            </div>
            <div class="<?= field_class($errors, 'last_name') ?>">
              <label for="last_name">Last Name</label>
              <input type="text" id="last_name" name="last_name" value="<?= e($old['last_name']) ?>"
                     placeholder="Tanyo" maxlength="50" autocomplete="family-name">
              <?php field_err($errors, 'last_name'); ?>
            </div>
          </div>

          <div class="<?= field_class($errors, 'email') ?>">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?= e($old['email']) ?>"
                   placeholder="you@example.com" maxlength="100" autocomplete="email">
            <?php field_err($errors, 'email'); ?>
          </div>

          <div class="row">
            <div class="<?= field_class($errors, 'phone') ?>">
              <label for="phone">Phone Number</label>
              <input type="tel" id="phone" name="phone" value="<?= e($old['phone']) ?>"
                     placeholder="09XX XXX XXXX" maxlength="20" autocomplete="tel">
              <?php field_err($errors, 'phone'); ?>
            </div>
            <div class="<?= field_class($errors, 'age') ?>">
              <label for="age">Age</label>
              <input type="number" id="age" name="age" value="<?= e($old['age']) ?>"
                     placeholder="25" min="1" max="120">
              <?php field_err($errors, 'age'); ?>
            </div>
          </div>

          <div class="<?= field_class($errors, 'address') ?>">
            <label for="address">Address (Purok / Street)</label>
            <input type="text" id="address" name="address" value="<?= e($old['address']) ?>"
                   placeholder="Purok 1, Catarman" maxlength="150" autocomplete="street-address">
            <?php field_err($errors, 'address'); ?>
          </div>

          <div class="<?= field_class($errors, 'password') ?>">
            <label for="password">Password</label>
            <div class="input-wrap">
              <input type="password" id="password" name="password"
                     placeholder="At least 8 characters" autocomplete="new-password">
              <button type="button" class="toggle-pw">Show</button>
            </div>
            <div class="meter" id="meter" data-s="0"><i></i><i></i><i></i><i></i></div>
            <div class="meter-label" id="meterLabel"></div>
            <?php field_err($errors, 'password'); ?>
          </div>

          <div class="<?= field_class($errors, 'confirm_password') ?>">
            <label for="confirm_password">Confirm Password</label>
            <div class="input-wrap">
              <input type="password" id="confirm_password" name="confirm_password"
                     placeholder="Re-enter your password" autocomplete="new-password">
              <button type="button" class="toggle-pw">Show</button>
            </div>
            <?php field_err($errors, 'confirm_password'); ?>
          </div>

          <div class="<?= field_class($errors, 'terms') ?> spaced">
            <label class="check">
              <input type="checkbox" name="terms" value="1" <?= isset($_POST['terms']) ? 'checked' : '' ?>>
              <span>I agree to the <a href="#">Terms</a> and <a href="#">Privacy Policy</a></span>
            </label>
            <?php field_err($errors, 'terms'); ?>
          </div>

          <button type="submit" class="btn">Create Account</button>
        </form>

        <p class="switch">Already have an account? <a href="login.php">Sign in</a></p>
      </div>
    </main>
  </div>
</div>
<script src="assets/js/auth.js"></script>
</body>
</html>
