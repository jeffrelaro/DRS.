<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$residentId = (int) $_SESSION['resident_id'];
$counts     = request_counts($residentId);
$rows       = resident_requests($residentId, '', '', 3);

$pageTitle = 'Dashboard';
$active    = 'dashboard';
require __DIR__ . '/includes/header.php';
?>
<h1>Welcome, <?= e($_SESSION['resident_name']) ?>!</h1>

<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-top"><?= icon('file', 46) ?><strong><?= $counts['total'] ?></strong></div>
    <h3>My Request</h3>
    <p>View your request status</p>
    <a href="my-requests.php">View All <?= icon('arrow', 18) ?></a>
  </div>

  <div class="stat-card">
    <div class="stat-top"><?= icon('clock', 46) ?><strong><?= $counts['In Progress'] ?></strong></div>
    <h3>In Progress</h3>
    <p>Requests in process</p>
    <a href="my-requests.php?status=In+Progress">View All <?= icon('arrow', 18) ?></a>
  </div>

  <div class="stat-card">
    <div class="stat-top"><?= icon('check', 46) ?><strong><?= $counts['Completed'] ?></strong></div>
    <h3>Completed</h3>
    <p>Requests completed</p>
    <a href="my-requests.php?status=Completed">View All <?= icon('arrow', 18) ?></a>
  </div>
</div>

<section class="panel">
  <div class="panel-head">
    <h2>Recent Document Requests</h2>
    <a href="my-requests.php">View All</a>
  </div>
  <?php
  $emptyMessage = 'You have no requests yet. Start with Request Documents.';
  require __DIR__ . '/includes/request_table.php';
  ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
