<?php
function admin_requests(string $status = '', string $search = ''): array
{
    $sql = "SELECT r.*, d.name AS document_name, d.icon AS document_icon,
                   CONCAT_WS(' ', p.first_name, p.last_name) AS resident_name
            FROM requests r
            JOIN document_types d ON d.id = r.document_type_id
            JOIN residents p ON p.id = r.resident_id
            WHERE 1 = 1";
    $params = [];

    if ($status !== '') {
        $sql     .= ' AND r.status = ?';
        $params[] = $status;
    }
    if ($search !== '') {
        $sql .= " AND (r.reference_no LIKE ? OR d.name LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ?)";
        $like = '%' . $search . '%';
        array_push($params, $like, $like, $like, $like);
    }

    $stmt = db()->prepare($sql . ' ORDER BY r.created_at DESC, r.id DESC');
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function admin_counts(): array
{
    $pdo    = db();
    $counts = ['total' => 0, 'In Progress' => 0, 'Completed' => 0, 'Declined' => 0, 'awaiting' => 0, 'residents' => 0];

    foreach ($pdo->query('SELECT status, COUNT(*) AS total FROM requests GROUP BY status')->fetchAll() as $row) {
        $counts[$row['status']] = (int) $row['total'];
        $counts['total']       += (int) $row['total'];
    }

    $counts['awaiting']  = (int) $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'In Progress' AND verified_at IS NULL")->fetchColumn();
    $counts['residents'] = (int) $pdo->query('SELECT COUNT(*) FROM residents')->fetchColumn();

    return $counts;
}

function admin_verify_request(int $id): bool
{
    $stmt = db()->prepare(
        "UPDATE requests SET verified_at = ?, updated_at = ?
         WHERE id = ? AND status = 'In Progress' AND verified_at IS NULL"
    );
    $stmt->execute([now(), now(), $id]);
    return $stmt->rowCount() > 0;
}

function admin_ready_request(int $id): bool
{
    $stmt = db()->prepare(
        "UPDATE requests SET status = 'Completed', ready_at = ?, updated_at = ?
         WHERE id = ? AND status = 'In Progress' AND verified_at IS NOT NULL"
    );
    $stmt->execute([now(), now(), $id]);
    return $stmt->rowCount() > 0;
}

function admin_decline_request(int $id, string $reason): bool
{
    $stmt = db()->prepare(
        "UPDATE requests SET status = 'Declined', decline_reason = ?, updated_at = ?
         WHERE id = ? AND status = 'In Progress'"
    );
    $stmt->execute([$reason, now(), $id]);
    return $stmt->rowCount() > 0;
}

function admin_delete_request(int $id): bool
{
    $stmt = db()->prepare('DELETE FROM requests WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->rowCount() > 0;
}

function admin_residents(string $search = ''): array
{
    $sql = "SELECT p.*, (SELECT COUNT(*) FROM requests q WHERE q.resident_id = p.id) AS request_count
            FROM residents p WHERE 1 = 1";
    $params = [];

    if ($search !== '') {
        $sql .= ' AND (p.first_name LIKE ? OR p.last_name LIKE ? OR p.email LIKE ? OR p.address LIKE ?)';
        $like = '%' . $search . '%';
        array_push($params, $like, $like, $like, $like);
    }

    $stmt = db()->prepare($sql . ' ORDER BY p.created_at DESC, p.id DESC');
    $stmt->execute($params);
    return $stmt->fetchAll();
}
