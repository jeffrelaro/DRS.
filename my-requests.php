<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$filters = [
    ''            => 'All Requests',
    'In Progress' => 'In Progress',
    'Completed'   => 'Completed',
    'Declined'    => 'Declined',
];

$status = $_GET['status'] ?? '';
if (!array_key_exists($status, $filters)) {
    $status = '';
}

$residentId = (int) $_SESSION['resident_id'];
$search     = trim($_GET['q'] ?? '');
$total      = count_resident_requests($residentId, $status, $search);

[$page, $pages] = page_number($total, REQUESTS_PER_PAGE);

$rows = resident_requests($residentId, $status, $search, REQUESTS_PER_PAGE, ($page - 1) * REQUESTS_PER_PAGE);

$pageTitle = 'My Requests';
$active    = 'requests';
require __DIR__ . '/includes/header.php';
?>
<h1>My Requests</h1>
<p class="lead">Track and manage all your request in one place.</p>

<div class="tabs">
  <?php foreach ($filters as $value => $label): ?>
    <a class="tab <?= $status === $value ? 'active' : '' ?>"
       href="my-requests.php<?= $value === '' ? '' : '?status=' . urlencode($value) ?>"><?= e($label) ?></a>
  <?php endforeach; ?>
</div>

<form class="toolbar" method="get" action="my-requests.php">
  <input type="hidden" name="status" value="<?= e($status) ?>">
  <label class="search">
    <?= icon('search', 20) ?>
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search by document or reference no..." maxlength="60">
  </label>
  <button type="submit" class="btn">Search</button>
</form>

<section class="panel">
  <?php
  $emptyMessage = $search !== '' ? 'No requests match your search.'
      : ($status === '' ? 'You have no requests yet.' : 'No ' . strtolower($status) . ' requests.');
  require __DIR__ . '/includes/request_table.php';
  ?>
</section>

<?php if ($pages > 1): ?>
  <nav class="pager" aria-label="Pages">
    <?php if ($page > 1): ?><a href="<?= e(page_link($page - 1)) ?>">Previous</a><?php endif; ?>
    <span>Page <?= $page ?> of <?= $pages ?></span>
    <?php if ($page < $pages): ?><a href="<?= e(page_link($page + 1)) ?>">Next</a><?php endif; ?>
  </nav>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
