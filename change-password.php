<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$resident   = current_resident();
$residentId = (int) $resident['id'];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors['form'] = 'Your session expired. Please try again.';
    }

    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($current === '') {
        $errors['current_password'] = 'Current password is required.';
    } elseif (!password_verify($current, $resident['password_hash'])) {
        $errors['current_password'] = 'Current password is incorrect.';
    }

    if ($new === '') {
        $errors['new_password'] = 'New password is required.';
    } elseif (($message = password_error($new)) !== '') {
        $errors['new_password'] = $message;
    } elseif ($new === $current) {
        $errors['new_password'] = 'New password must be different from your current password.';
    }

    if ($confirm === '') {
        $errors['confirm_password'] = 'Please confirm your new password.';
    } elseif ($confirm !== $new) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (!$errors) {
        try {
            update_resident_password($residentId, $new);
            session_regenerate_id(true);

            flash_set('success', 'Your password was changed.');
            redirect('profile.php');
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            $errors['form'] = 'Something went wrong. Please try again later.';
        }
    }
}

$pageTitle = 'Change Password';
$active    = 'profile';
require __DIR__ . '/includes/header.php';
?>
<h1>Change Password</h1>
<p class="lead">Update your account password.</p>

<a class="back" href="profile.php" aria-label="Back to profile"><?= icon('back', 28) ?></a>

<?php if (isset($errors['form'])): ?>
  <div class="alert error"><?= e($errors['form']) ?></div>
<?php endif; ?>

<form class="form" method="post" action="change-password.php" novalidate>
  <?= csrf_field() ?>

  <div class="<?= field_class($errors, 'current_password') ?>">
    <label for="current_password">Current Password</label>
    <input type="password" id="current_password" name="current_password" autocomplete="current-password">
    <?php field_err($errors, 'current_password'); ?>
  </div>

  <div class="<?= field_class($errors, 'new_password') ?>">
    <label for="new_password">New Password</label>
    <input type="password" id="new_password" name="new_password" autocomplete="new-password" data-strength="strength">
    <div class="strength" id="strength" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
    <?php field_err($errors, 'new_password'); ?>
  </div>

  <div class="<?= field_class($errors, 'confirm_password') ?>">
    <label for="confirm_password">Confirm New Password</label>
    <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password">
    <?php field_err($errors, 'confirm_password'); ?>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-wide">Update Password</button>
    <a class="btn btn-outline" href="profile.php">Cancel</a>
  </div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
