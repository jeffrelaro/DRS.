<?php
const REQUEST_PURPOSES = [
    'Employment requirement',
    'School requirement',
    'Business',
    'Legal or government transaction',
    'Scholarship or financial assistance',
    'Medical',
    'Travel',
    'Others',
];

const MAX_COPIES = 10;

const REQUESTS_PER_PAGE = 8;

function now(): string
{
    return date('Y-m-d H:i:s');
}

function format_date(?string $value): string
{
    return $value ? date('F d, Y', strtotime($value)) : '';
}

function format_datetime(?string $value): string
{
    return $value ? date('F d, Y', strtotime($value)) . ' · ' . date('g:i A', strtotime($value)) : '';
}

function status_class(string $status): string
{
    return strtolower(str_replace(' ', '-', $status));
}

function document_categories(): array
{
    return db()->query('SELECT DISTINCT category FROM document_types ORDER BY category')
        ->fetchAll(PDO::FETCH_COLUMN);
}

function document_types(string $search = '', string $category = ''): array
{
    $sql    = 'SELECT * FROM document_types WHERE 1 = 1';
    $params = [];

    if ($search !== '') {
        $sql     .= ' AND (name LIKE ? OR description LIKE ?)';
        $like     = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
    }
    if ($category !== '') {
        $sql     .= ' AND category = ?';
        $params[] = $category;
    }

    $stmt = db()->prepare($sql . ' ORDER BY id');
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function find_document_type(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM document_types WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function validate_request_form(array $input): array
{
    $values = [
        'purpose' => trim($input['purpose'] ?? ''),
        'copies'  => trim($input['copies'] ?? ''),
        'notes'   => trim($input['notes'] ?? ''),
    ];
    $errors = [];

    if ($values['purpose'] === '') {
        $errors['purpose'] = 'Purpose is required.';
    } elseif (!in_array($values['purpose'], REQUEST_PURPOSES, true)) {
        $errors['purpose'] = 'Please choose a purpose from the list.';
    }

    if ($values['copies'] === '') {
        $errors['copies'] = 'Number of copies is required.';
    } else {
        $copies = filter_var($values['copies'], FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => MAX_COPIES],
        ]);
        if ($copies === false) {
            $errors['copies'] = 'Copies must be a whole number from 1 to ' . MAX_COPIES . '.';
        }
    }

    if (mb_strlen($values['notes']) > 255) {
        $errors['notes'] = 'Notes must be 255 characters or fewer.';
    }

    return [$values, $errors];
}

function has_open_request(int $residentId, int $typeId, int $exceptId = 0): bool
{
    $stmt = db()->prepare(
        "SELECT COUNT(*) FROM requests
         WHERE resident_id = ? AND document_type_id = ? AND status = 'In Progress' AND id <> ?"
    );
    $stmt->execute([$residentId, $typeId, $exceptId]);
    return (int) $stmt->fetchColumn() > 0;
}

function create_request(int $residentId, array $type, array $values): int
{
    $pdo = db();
    $now = now();

    $pdo->beginTransaction();
    try {
        $pdo->prepare(
            'INSERT INTO requests (resident_id, document_type_id, purpose, copies, notes, status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $residentId,
            $type['id'],
            $values['purpose'],
            (int) $values['copies'],
            $values['notes'],
            'In Progress',
            $now,
            $now,
        ]);

        $id        = (int) $pdo->lastInsertId();
        $reference = sprintf('%s-%s-%04d', $type['code'], date('Y'), $id);

        $pdo->prepare('UPDATE requests SET reference_no = ? WHERE id = ?')->execute([$reference, $id]);
        $pdo->commit();

        return $id;
    } catch (Throwable $ex) {
        $pdo->rollBack();
        throw $ex;
    }
}

function find_request(int $id, ?int $residentId = null): ?array
{
    $sql = 'SELECT r.*, d.name AS document_name, d.code AS document_code, d.icon AS document_icon,
                   d.description AS document_description, d.processing_time,
                   p.first_name, p.last_name, p.email, p.phone, p.address, p.age
            FROM requests r
            JOIN document_types d ON d.id = r.document_type_id
            JOIN residents p ON p.id = r.resident_id
            WHERE r.id = ?';
    $params = [$id];

    if ($residentId !== null) {
        $sql     .= ' AND r.resident_id = ?';
        $params[] = $residentId;
    }

    $stmt = db()->prepare($sql . ' LIMIT 1');
    $stmt->execute($params);
    return $stmt->fetch() ?: null;
}

function resident_request_filter(int $residentId, string $status, string $search): array
{
    $where  = 'r.resident_id = ?';
    $params = [$residentId];

    if ($status !== '') {
        $where   .= ' AND r.status = ?';
        $params[] = $status;
    }
    if ($search !== '') {
        $where   .= ' AND (d.name LIKE ? OR r.reference_no LIKE ?)';
        $like     = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
    }

    return [$where, $params];
}

function resident_requests(int $residentId, string $status = '', string $search = '', int $limit = 0, int $offset = 0): array
{
    [$where, $params] = resident_request_filter($residentId, $status, $search);

    $sql = 'SELECT r.*, d.name AS document_name, d.icon AS document_icon
            FROM requests r
            JOIN document_types d ON d.id = r.document_type_id
            WHERE ' . $where . ' ORDER BY r.created_at DESC, r.id DESC';

    if ($limit > 0) {
        $sql .= ' LIMIT ' . $limit . ' OFFSET ' . max(0, $offset);
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function count_resident_requests(int $residentId, string $status = '', string $search = ''): int
{
    [$where, $params] = resident_request_filter($residentId, $status, $search);

    $stmt = db()->prepare(
        'SELECT COUNT(*) FROM requests r JOIN document_types d ON d.id = r.document_type_id WHERE ' . $where
    );
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

function request_counts(int $residentId): array
{
    $counts = ['total' => 0, 'In Progress' => 0, 'Completed' => 0, 'Declined' => 0];

    $stmt = db()->prepare('SELECT status, COUNT(*) AS total FROM requests WHERE resident_id = ? GROUP BY status');
    $stmt->execute([$residentId]);

    foreach ($stmt->fetchAll() as $row) {
        $counts[$row['status']] = (int) $row['total'];
        $counts['total']       += (int) $row['total'];
    }
    return $counts;
}

function request_can_edit(array $request): bool
{
    return $request['status'] === 'In Progress' && $request['verified_at'] === null;
}

function request_can_resubmit(array $request): bool
{
    return $request['status'] === 'Declined';
}

function update_request(int $id, int $residentId, array $values): bool
{
    $stmt = db()->prepare(
        "UPDATE requests SET purpose = ?, copies = ?, notes = ?, updated_at = ?
         WHERE id = ? AND resident_id = ? AND status = 'In Progress' AND verified_at IS NULL"
    );
    $stmt->execute([$values['purpose'], (int) $values['copies'], $values['notes'], now(), $id, $residentId]);
    return $stmt->rowCount() > 0;
}

function resubmit_request(int $id, int $residentId, array $values): bool
{
    $stmt = db()->prepare(
        "UPDATE requests
         SET purpose = ?, copies = ?, notes = ?, status = 'In Progress',
             decline_reason = NULL, verified_at = NULL, ready_at = NULL, updated_at = ?
         WHERE id = ? AND resident_id = ? AND status = 'Declined'"
    );
    $stmt->execute([$values['purpose'], (int) $values['copies'], $values['notes'], now(), $id, $residentId]);
    return $stmt->rowCount() > 0;
}

function cancel_request(int $id, int $residentId): bool
{
    $stmt = db()->prepare(
        "DELETE FROM requests
         WHERE id = ? AND resident_id = ? AND status = 'In Progress' AND verified_at IS NULL"
    );
    $stmt->execute([$id, $residentId]);
    return $stmt->rowCount() > 0;
}
