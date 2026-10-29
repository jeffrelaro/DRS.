<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check()) {
    redirect('my-requests.php');
}

try {
    $cancelled = cancel_request((int) ($_POST['id'] ?? 0), (int) $_SESSION['resident_id']);
} catch (PDOException $ex) {
    error_log($ex->getMessage());
    $cancelled = false;
}

if ($cancelled) {
    flash_set('success', 'Your request was cancelled.');
} else {
    flash_set('error', 'This request could not be cancelled. It may already be in processing.');
}

redirect('my-requests.php');
