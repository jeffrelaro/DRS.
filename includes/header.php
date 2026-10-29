<?php
$pageTitle  = $pageTitle ?? 'Resident Portal';
$active     = $active ?? '';
$unread     = unread_notification_count((int) $_SESSION['resident_id']);
$flash      = flash_get();

$nav = [
    'dashboard' => ['dashboard.php', 'Dashboard', 'home'],
    'request'   => ['request-documents.php', 'Request Documents', 'file'],
    'requests'  => ['my-requests.php', 'My Requests', 'clock'],
    'profile'   => ['profile.php', 'My Profile', 'user'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> | Resident Portal</title>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<div class="shell">
  <aside class="sidebar">
    <a class="sidebar-brand" href="dashboard.php">
      <img src="assets/images/seal.png" alt="Barangay Catarman seal">
      <span>Resident<br>Portal</span>
    </a>

    <nav class="sidebar-nav">
      <?php foreach ($nav as $key => [$href, $label, $iconName]): ?>
        <a href="<?= e($href) ?>" class="<?= $active === $key ? 'active' : '' ?>">
          <?= icon($iconName) ?><span><?= e($label) ?></span>
        </a>
      <?php endforeach; ?>
    </nav>

    <form method="post" action="logout.php" class="sidebar-logout">
      <?= csrf_field() ?>
      <button type="submit"><?= icon('logout') ?><span>Logout</span></button>
    </form>
  </aside>

  <div class="content">
    <a class="bell" href="notifications.php" aria-label="Notifications">
      <?= icon('bell', 24) ?>
      <?php if ($unread > 0): ?><span class="bell-badge"><?= $unread ?></span><?php endif; ?>
    </a>

    <main class="main">
      <?php if ($flash): ?>
        <div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
      <?php endif; ?>
