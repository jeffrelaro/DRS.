<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $residentId = (int) $_SESSION['resident_id'];

    try {
        if (($_POST['action'] ?? '') === 'delete') {
            delete_notification((int) ($_POST['id'] ?? 0), $residentId);
        } elseif (($_POST['action'] ?? '') === 'clear') {
            clear_notifications($residentId);
        }
    } catch (PDOException $ex) {
        error_log($ex->getMessage());
        flash_set('error', 'The notification could not be deleted.');
    }
}

redirect('notifications.php');
