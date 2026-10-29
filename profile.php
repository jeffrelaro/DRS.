<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$resident = current_resident();
$photo    = photo_url((int) $resident['id']);
$fullName = $resident['first_name'] . ' ' . $resident['last_name'];

$pageTitle = 'My Profile';
$active    = 'profile';
require __DIR__ . '/includes/header.php';
?>
<h1>My Profile</h1>
<p class="lead">View and manage your account information.</p>

<div class="profile-grid">
  <aside class="profile-side">
    <div class="avatar">
      <?php if ($photo): ?><img src="<?= e($photo) ?>" alt="Profile photo"><?php else: ?><?= icon('user', 72) ?><?php endif; ?>
    </div>
    <h2><?= e($fullName) ?></h2>
    <div class="role">Resident</div>
    <ul>
      <li><?= icon('mail', 18) ?><?= e($resident['email']) ?></li>
      <li><?= icon('phone', 18) ?><?= e($resident['phone']) ?></li>
      <li><?= icon('pin', 18) ?><?= e($resident['address']) ?></li>
    </ul>
  </aside>

  <section class="profile-main">
    <h2>Account Information</h2>
    <ul class="facts">
      <li><span>Full Name</span><strong><?= e($fullName) ?></strong></li>
      <li><span>Email Address</span><strong><?= e($resident['email']) ?></strong></li>
      <li><span>Phone Number</span><strong><?= e($resident['phone']) ?></strong></li>
      <li><span>Age</span><strong><?= (int) $resident['age'] ?></strong></li>
      <li><span>Address</span><strong><?= e($resident['address']) ?></strong></li>
      <li><span>Account Type</span><strong>Resident</strong></li>
      <li><span>Member Since</span><strong><?= e(format_date($resident['created_at'])) ?></strong></li>
    </ul>
  </section>
</div>

<div class="shortcuts">
  <a class="shortcut" href="profile-edit.php">
    <span class="big"><?= icon('edit', 40) ?></span>
    <span class="grow"><strong>Edit Profile</strong><small>Update your personal information.</small></span>
    <?= icon('chevron', 20) ?>
  </a>
  <a class="shortcut" href="change-password.php">
    <span class="big"><?= icon('lock', 40) ?></span>
    <span class="grow"><strong>Change Password</strong><small>Update your account password.</small></span>
    <?= icon('chevron', 20) ?>
  </a>
</div>

<p class="danger-link"><a href="account-delete.php">Delete my account</a></p>
<?php require __DIR__ . '/includes/footer.php'; ?>
