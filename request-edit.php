<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$residentId = (int) $_SESSION['resident_id'];
$request    = find_request((int) ($_GET['id'] ?? $_POST['id'] ?? 0), $residentId);

if (!$request) {
    flash_set('error', 'That request was not found.');
    redirect('my-requests.php');
}

$resubmit = request_can_resubmit($request);

if (!$resubmit && !request_can_edit($request)) {
    flash_set('error', 'This request can no longer be edited because staff already started processing it.');
    redirect('request-view.php?id=' . (int) $request['id']);
}

$errors = [];
$values = [
    'purpose' => $request['purpose'],
    'copies'  => (string) $request['copies'],
    'notes'   => $request['notes'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors['form'] = 'Your session expired. Please try again.';
    }

    [$values, $fieldErrors] = validate_request_form($_POST);
    $errors += $fieldErrors;

    if (!$errors && $resubmit && has_open_request($residentId, (int) $request['document_type_id'], (int) $request['id'])) {
        $errors['form'] = 'You already have an open request for this document.';
    }

    if (!$errors) {
        try {
            $saved = $resubmit
                ? resubmit_request((int) $request['id'], $residentId, $values)
                : update_request((int) $request['id'], $residentId, $values);

            flash_set(
                $saved ? 'success' : 'error',
                $saved ? ($resubmit ? 'Request resubmitted.' : 'Request updated.') : 'This request could not be changed.'
            );
            redirect('request-view.php?id=' . (int) $request['id']);
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            $errors['form'] = 'Something went wrong. Please try again later.';
        }
    }
}

$pageTitle = $resubmit ? 'Resubmit Request' : 'Edit Request';
$active    = 'requests';
require __DIR__ . '/includes/header.php';
?>
<h1><?= e($pageTitle) ?></h1>

<a class="back" href="request-view.php?id=<?= (int) $request['id'] ?>" aria-label="Back to request"><?= icon('back', 28) ?></a>

<div class="form-head">
  <div class="title"><?= icon($request['document_icon'], 46) ?><span><?= e($request['document_name']) ?></span></div>
  <?php if ($resubmit): ?>
    <div class="reason"><strong>Declined:</strong> <?= e((string) $request['decline_reason']) ?></div>
  <?php endif; ?>
</div>

<?php if (isset($errors['form'])): ?>
  <div class="alert error"><?= e($errors['form']) ?></div>
<?php endif; ?>

<form class="form" method="post" action="request-edit.php" novalidate>
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int) $request['id'] ?>">

  <?php require __DIR__ . '/includes/request_fields.php'; ?>

  <div class="form-actions">
    <button type="submit" class="btn btn-wide"><?= $resubmit ? 'Resubmit Request' : 'Save Changes' ?></button>
  </div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
