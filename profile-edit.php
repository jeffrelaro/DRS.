<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$resident   = current_resident();
$residentId = (int) $resident['id'];
$photo      = photo_url($residentId);

$errors = [];
$values = [
    'first_name' => $resident['first_name'],
    'last_name'  => $resident['last_name'],
    'email'      => $resident['email'],
    'phone'      => $resident['phone'],
    'age'        => (string) $resident['age'],
    'address'    => $resident['address'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors['form'] = 'Your session expired. Please try again.';
    }

    [$values, $fieldErrors] = validate_profile_form($_POST, $residentId);
    $errors += $fieldErrors;

    $upload = $_FILES['photo'] ?? ['error' => UPLOAD_ERR_NO_FILE];
    $hasNew = $upload['error'] !== UPLOAD_ERR_NO_FILE;

    if ($hasNew && ($message = photo_error($upload)) !== '') {
        $errors['photo'] = $message;
    }

    if (!$errors) {
        try {
            update_resident($residentId, $values);
            $_SESSION['resident_name'] = $values['first_name'];

            if ($hasNew) {
                save_photo($residentId, $upload);
            } elseif (isset($_POST['remove_photo'])) {
                delete_photo($residentId);
            }

            flash_set('success', 'Your profile was updated.');
            redirect('profile.php');
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            $errors['form'] = 'Something went wrong. Please try again later.';
        }
    }
}

$pageTitle = 'Edit Profile';
$active    = 'profile';
require __DIR__ . '/includes/header.php';
?>
<h1>Edit Profile</h1>
<p class="lead">Update your personal information.</p>

<a class="back" href="profile.php" aria-label="Back to profile"><?= icon('back', 28) ?></a>

<?php if (isset($errors['form'])): ?>
  <div class="alert error"><?= e($errors['form']) ?></div>
<?php endif; ?>

<form class="edit-layout" method="post" action="profile-edit.php" enctype="multipart/form-data" novalidate>
  <?= csrf_field() ?>

  <div class="form">
    <div class="<?= field_class($errors, 'first_name') ?>">
      <label for="first_name">First Name</label>
      <input type="text" id="first_name" name="first_name" value="<?= e($values['first_name']) ?>" maxlength="50">
      <?php field_err($errors, 'first_name'); ?>
    </div>

    <div class="<?= field_class($errors, 'last_name') ?>">
      <label for="last_name">Last Name</label>
      <input type="text" id="last_name" name="last_name" value="<?= e($values['last_name']) ?>" maxlength="50">
      <?php field_err($errors, 'last_name'); ?>
    </div>

    <div class="<?= field_class($errors, 'email') ?>">
      <label for="email">Email Address</label>
      <input type="email" id="email" name="email" value="<?= e($values['email']) ?>" maxlength="100">
      <?php field_err($errors, 'email'); ?>
    </div>

    <div class="<?= field_class($errors, 'phone') ?>">
      <label for="phone">Phone Number</label>
      <input type="tel" id="phone" name="phone" value="<?= e($values['phone']) ?>" maxlength="20">
      <?php field_err($errors, 'phone'); ?>
    </div>

    <div class="<?= field_class($errors, 'age') ?>">
      <label for="age">Age</label>
      <input type="number" id="age" name="age" value="<?= e($values['age']) ?>" min="1" max="120">
      <?php field_err($errors, 'age'); ?>
    </div>

    <div class="<?= field_class($errors, 'address') ?>">
      <label for="address">Address (Purok / Street)</label>
      <input type="text" id="address" name="address" value="<?= e($values['address']) ?>" maxlength="150">
      <?php field_err($errors, 'address'); ?>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-wide">Save Changes</button>
      <a class="btn btn-outline" href="profile.php">Cancel</a>
    </div>
  </div>

  <div class="photo-box">
    <label class="photo-pick" for="photo">
      <span class="avatar avatar-lg" id="photo-preview">
        <?php if ($photo): ?><img src="<?= e($photo) ?>" alt="Profile photo"><?php else: ?><?= icon('user', 80) ?><?php endif; ?>
      </span>
      <span class="photo-hint"><?= icon('camera', 18) ?>Click to change photo</span>
    </label>
    <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp" hidden>
    <small class="muted">JPG, PNG or WEBP, up to 2 MB.</small>
    <?php if ($photo): ?>
      <label class="check"><input type="checkbox" name="remove_photo" value="1"> Remove current photo</label>
    <?php endif; ?>
    <?php field_err($errors, 'photo'); ?>
  </div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
