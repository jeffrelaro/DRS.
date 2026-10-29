<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$request = find_request((int) ($_GET['id'] ?? 0), (int) $_SESSION['resident_id']);

if (!$request || $request['status'] !== 'Completed') {
    flash_set('error', 'This document is not available yet.');
    redirect('my-requests.php');
}

$statements = [
    'BC' => 'has no derogatory record on file in this barangay and is hereby granted this Barangay Clearance.',
    'CR' => 'is a bona fide resident of Barangay Catarman, Cordova, Cebu.',
    'IC' => 'belongs to an indigent family residing in this barangay.',
    'BP' => 'is endorsed by this barangay to apply for or renew a business permit.',
    'CD' => 'is a resident of this barangay and is certified for community tax certificate (Cedula) purposes.',
    'BI' => 'is a registered resident of this barangay and is issued this barangay identification.',
];

$fullName  = $request['first_name'] . ' ' . $request['last_name'];
$statement = $statements[$request['document_code']] ?? 'is a resident of this barangay.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($request['document_name']) ?> | <?= e((string) $request['reference_no']) ?></title>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
  <div class="print-bar">
    <button type="button" class="btn" onclick="window.print()">Print / Save as PDF</button>
    <a class="btn btn-outline" href="request-view.php?id=<?= (int) $request['id'] ?>">Back</a>
  </div>

  <article class="certificate">
    <div>Republic of the Philippines<br>Barangay Catarman, Cordova, Cebu</div>
    <h1><?= e($request['document_name']) ?></h1>

    <p>This is to certify that <strong><?= e($fullName) ?></strong>, <?= (int) $request['age'] ?> years old,
       residing at <?= e($request['address']) ?>, <?= e($statement) ?></p>

    <p>This document is issued upon the request of the above-named person for
       <strong><?= e(strtolower($request['purpose'])) ?></strong>
       (<?= (int) $request['copies'] ?> <?= (int) $request['copies'] === 1 ? 'copy' : 'copies' ?>).</p>

    <p>Issued on <?= e(format_date($request['ready_at'])) ?> at Barangay Catarman, Cordova, Cebu.</p>

    <div class="sign">______________________________<br>Punong Barangay</div>
    <div class="meta">Reference No.: <?= e((string) $request['reference_no']) ?></div>
  </article>
</body>
</html>
