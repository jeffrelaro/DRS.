<?php
require_once __DIR__ . '/includes/functions.php';

$resident   = current_resident();
$residentId = (int) $resident['id'];
$error      = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    if (!csrf_check()) {
        $error = 'Your session expired. Please try again.';
    } elseif ($password === '') {
        $error = 'Please enter your password to confirm.';
    } elseif (!password_verify($password, $resident['password_hash'])) {
        $error = 'Incorrect password.';
    } else {
        try {
            delete_resident($residentId);
            $_SESSION = [];
            session_regenerate_id(true);
            flash_set('success', 'Your account was deleted.');
            redirect('login.php');
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            $error = 'Something went wrong. Please try again later.';
        }
    }
}

$pageTitle = 'Delete Account';
$active    = 'profile';
require __DIR__ . '/includes/header.php';
?>
<h1>Delete Account</h1>
<p class="lead">This permanently removes your account, your requests and your notifications.</p>

<a class="back" href="profile.php" aria-label="Back to profile"><?= icon('back', 28) ?></a>

<?php if ($error !== ''): ?>
  <div class="alert error"><?= e($error) ?></div>
<?php endif; ?>

<form class="form" method="post" action="account-delete.php" data-confirm="Delete your account permanently? This cannot be undone." novalidate>
  <?= csrf_field() ?>

  <div class="field">
    <label for="password">Confirm with your password</label>
    <input type="password" id="password" name="password" autocomplete="current-password">
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-danger">Delete my account</button>
    <a class="btn btn-outline" href="profile.php">Cancel</a>
  </div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
