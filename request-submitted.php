<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$request = find_request((int) ($_GET['id'] ?? 0), (int) $_SESSION['resident_id']);

if (!$request) {
    redirect('my-requests.php');
}

$pageTitle = 'Request submitted';
$active    = 'request';
require __DIR__ . '/includes/header.php';
?>
<h1>Request Documents</h1>

<div class="success-box">
  <div class="success-icon"><?= icon('tick', 64) ?></div>
  <h2>Request submitted!</h2>
  <p>
    Your <?= e($request['document_name']) ?> request has been sent.
    You&rsquo;ll be notified once it&rsquo;s ready for pickup.
  </p>
  <p class="ref">Reference No. <strong><?= e((string) $request['reference_no']) ?></strong></p>
  <div class="actions">
    <a class="btn btn-wide" href="request-view.php?id=<?= (int) $request['id'] ?>">Track this request</a>
    <a class="btn btn-outline" href="request-documents.php">Request another document</a>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
