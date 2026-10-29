<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$residentId = (int) $_SESSION['resident_id'];
$type       = find_document_type((int) ($_GET['type'] ?? $_POST['type'] ?? 0));

if (!$type) {
    flash_set('error', 'That document type does not exist.');
    redirect('request-documents.php');
}

$errors = [];
$values = ['purpose' => '', 'copies' => '1', 'notes' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors['form'] = 'Your session expired. Please try again.';
    }

    [$values, $fieldErrors] = validate_request_form($_POST);
    $errors += $fieldErrors;

    if (!$errors && has_open_request($residentId, (int) $type['id'])) {
        $errors['form'] = 'You already have an open request for this document. Please wait for it to be processed.';
    }

    if (!$errors) {
        try {
            $id      = create_request($residentId, $type, $values);
            redirect('request-submitted.php?id=' . $id);
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            $errors['form'] = 'Something went wrong. Please try again later.';
        }
    }
}

$pageTitle = 'Request Documents';
$active    = 'request';
require __DIR__ . '/includes/header.php';
?>
<h1>Request Documents</h1>

<a class="back" href="request-documents.php" aria-label="Back to documents"><?= icon('back', 28) ?></a>

<div class="form-head">
  <div class="title"><?= icon($type['icon'], 46) ?><span><?= e($type['name']) ?></span></div>
  <div class="info-box">
    <strong>Processing time:</strong> <?= e($type['processing_time']) ?>.<br>
    <?= e($type['description']) ?>
  </div>
</div>

<?php if (isset($errors['form'])): ?>
  <div class="alert error"><?= e($errors['form']) ?></div>
<?php endif; ?>

<form class="form" method="post" action="request-form.php" novalidate>
  <?= csrf_field() ?>
  <input type="hidden" name="type" value="<?= (int) $type['id'] ?>">

  <?php require __DIR__ . '/includes/request_fields.php'; ?>

  <div class="form-actions">
    <button type="submit" class="btn btn-wide">Submit Request</button>
  </div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
