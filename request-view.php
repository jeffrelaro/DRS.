<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$request = find_request((int) ($_GET['id'] ?? 0), (int) $_SESSION['resident_id']);

if (!$request) {
    flash_set('error', 'That request was not found.');
    redirect('my-requests.php');
}

$declined = $request['status'] === 'Declined';

$steps = [
    ['Request submitted', $request['created_at'], 'Submitted'],
    ['Verified by barangay staff', $request['verified_at'], 'Pending'],
    ['Ready for pickup', $request['ready_at'], 'Pending'],
];

$pageTitle = $request['document_name'];
$active    = 'requests';
require __DIR__ . '/includes/header.php';
?>
<h1><?= e($request['document_name']) ?></h1>
<p class="lead">Requested <?= e(format_date($request['created_at'])) ?></p>

<a class="back" href="my-requests.php" aria-label="Back to my requests"><?= icon('back', 28) ?></a>

<div class="detail-grid">
  <section class="card">
    <div class="card-head">
      <h2><?= $declined ? 'Status' : 'Status Timeline' ?></h2>
      <span class="badge <?= e(status_class($request['status'])) ?>"><?= e($request['status']) ?></span>
    </div>

    <?php if ($declined): ?>
      <div class="reason"><strong>Reason:</strong> <?= e((string) $request['decline_reason']) ?></div>
    <?php else: ?>
      <ol class="timeline">
        <?php foreach ($steps as [$label, $when, $pending]): ?>
          <li class="<?= $when ? 'done' : '' ?>">
            <span class="dot"><?= $when ? icon('check', 16) : '' ?></span>
            <div>
              <strong><?= e($label) ?></strong>
              <small><?= $when ? e(format_datetime($when)) : e($pending) ?></small>
            </div>
          </li>
        <?php endforeach; ?>
      </ol>
    <?php endif; ?>
  </section>

  <section class="card">
    <ul class="facts">
      <li><span>Document Type</span><strong><?= e($request['document_name']) ?></strong></li>
      <li><span>Purpose</span><strong><?= e($request['purpose']) ?></strong></li>
      <li><span>Copies</span><strong><?= (int) $request['copies'] ?></strong></li>
      <li><span>Reference No.</span><strong><?= e((string) $request['reference_no']) ?></strong></li>
      <?php if ($request['notes'] !== ''): ?>
        <li><span>Notes</span><strong><?= e($request['notes']) ?></strong></li>
      <?php endif; ?>
    </ul>
  </section>
</div>

<div class="actions">
  <?php if ($request['status'] === 'Completed'): ?>
    <a class="btn btn-wide" href="request-print.php?id=<?= (int) $request['id'] ?>" target="_blank" rel="noopener">Download document</a>
  <?php endif; ?>

  <?php if (request_can_resubmit($request)): ?>
    <a class="btn btn-wide" href="request-edit.php?id=<?= (int) $request['id'] ?>">Resubmit Request</a>
  <?php endif; ?>

  <?php if (request_can_edit($request)): ?>
    <a class="btn btn-outline" href="request-edit.php?id=<?= (int) $request['id'] ?>">Edit request</a>

    <form method="post" action="request-cancel.php" data-confirm="Cancel this request? This cannot be undone.">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) $request['id'] ?>">
      <button type="submit" class="btn btn-danger">Cancel request</button>
    </form>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
