<?php
$pageTitle = $pageTitle ?? 'Admin Panel';
$active    = $active ?? '';
$awaiting  = admin_counts()['awaiting'];
$flash     = flash_get();

$nav = [
    'dashboard' => ['dashboard.php', 'Dashboard', 'grid'],
    'requests'  => ['requests.php', 'Requests', 'file'],
    'residents' => ['residents.php', 'Residents', 'users'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> | Admin Panel</title>
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/admin-app.css">
</head>
<body>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <a class="admin-brand" href="dashboard.php">
      <img src="../assets/images/seal.png" alt="Barangay Catarman seal">
      <span><strong>Resident Portal</strong><small>Admin Panel</small></span>
    </a>

    <nav class="admin-nav">
      <?php foreach ($nav as $key => [$href, $label, $iconName]): ?>
        <a href="<?= e($href) ?>" class="<?= $active === $key ? 'active' : '' ?>">
          <?= icon($iconName, 20) ?><span><?= e($label) ?></span>
          <?php if ($key === 'requests' && $awaiting > 0): ?>
            <span class="nav-count" title="Waiting for verification"><?= $awaiting ?></span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <form method="post" action="logout.php" class="admin-logout">
      <?= csrf_field() ?>
      <button type="submit"><?= icon('logout', 20) ?><span>Logout</span></button>
    </form>
  </aside>

  <div class="admin-content">
    <div class="admin-top">Signed in as <strong><?= e($_SESSION['admin_name']) ?></strong></div>

    <main class="admin-main">
      <?php if ($flash): ?>
        <div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
      <?php endif; ?>
