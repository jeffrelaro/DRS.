<?php
function notify(int $residentId, ?int $requestId, string $title, string $message): void
{
    db()->prepare(
        'INSERT INTO notifications (resident_id, request_id, title, message, is_read, created_at) VALUES (?, ?, ?, ?, 0, ?)'
    )->execute([$residentId, $requestId, $title, $message, now()]);
}

function notify_request_update(array $request, string $event, string $reason = ''): void
{
    $name = $request['document_name'];

    $texts = [
        'verified' => [$name . ' verified', 'Your document has been verified by staff and is now being processed.'],
        'ready'    => [$name . ' ready', 'Your document is ready for pickup at the barangay hall.'],
        'declined' => [$name . ' request declined', $reason !== '' ? $reason : 'Please check your request for details.'],
    ];

    if (isset($texts[$event])) {
        notify((int) $request['resident_id'], (int) $request['id'], $texts[$event][0], $texts[$event][1]);
    }
}

function resident_notifications(int $residentId): array
{
    $stmt = db()->prepare('SELECT * FROM notifications WHERE resident_id = ? ORDER BY created_at DESC, id DESC');
    $stmt->execute([$residentId]);
    return $stmt->fetchAll();
}

function unread_notification_count(int $residentId): int
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM notifications WHERE resident_id = ? AND is_read = 0');
    $stmt->execute([$residentId]);
    return (int) $stmt->fetchColumn();
}

function mark_notifications_read(int $residentId): void
{
    db()->prepare('UPDATE notifications SET is_read = 1 WHERE resident_id = ? AND is_read = 0')->execute([$residentId]);
}

function delete_notification(int $id, int $residentId): bool
{
    $stmt = db()->prepare('DELETE FROM notifications WHERE id = ? AND resident_id = ?');
    $stmt->execute([$id, $residentId]);
    return $stmt->rowCount() > 0;
}

function clear_notifications(int $residentId): void
{
    db()->prepare('DELETE FROM notifications WHERE resident_id = ?')->execute([$residentId]);
}
