<?php
function validate_profile_form(array $input, int $residentId): array
{
    $values = [];
    foreach (['first_name', 'last_name', 'email', 'phone', 'age', 'address'] as $field) {
        $values[$field] = trim($input[$field] ?? '');
    }
    $errors = [];

    foreach (['first_name' => 'First name', 'last_name' => 'Last name'] as $field => $label) {
        if ($values[$field] === '') {
            $errors[$field] = $label . ' is required.';
        } elseif (!validate_name($values[$field])) {
            $errors[$field] = $label . ' must be 2-50 letters.';
        }
    }

    if ($values['email'] === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!validate_email($values['email'])) {
        $errors['email'] = 'Please enter a valid email address.';
    } else {
        $stmt = db()->prepare('SELECT id FROM residents WHERE email = ? AND id <> ? LIMIT 1');
        $stmt->execute([strtolower($values['email']), $residentId]);
        if ($stmt->fetch()) {
            $errors['email'] = 'This email is already used by another account.';
        }
    }

    if ($values['phone'] === '') {
        $errors['phone'] = 'Phone number is required.';
    } elseif (!validate_phone($values['phone'])) {
        $errors['phone'] = 'Enter a valid mobile number (09XX XXX XXXX).';
    }

    if ($values['age'] === '') {
        $errors['age'] = 'Age is required.';
    } elseif (validate_age($values['age']) === false) {
        $errors['age'] = 'Age must be a whole number from 1 to 120.';
    }

    if ($values['address'] === '') {
        $errors['address'] = 'Address is required.';
    } elseif (mb_strlen($values['address']) > 150) {
        $errors['address'] = 'Address must be 150 characters or fewer.';
    }

    return [$values, $errors];
}

function update_resident(int $id, array $values): void
{
    db()->prepare(
        'UPDATE residents SET first_name = ?, last_name = ?, email = ?, phone = ?, age = ?, address = ? WHERE id = ?'
    )->execute([
        $values['first_name'],
        $values['last_name'],
        strtolower($values['email']),
        normalize_phone($values['phone']),
        (int) $values['age'],
        $values['address'],
        $id,
    ]);
}

function update_resident_password(int $id, string $password): void
{
    db()->prepare('UPDATE residents SET password_hash = ? WHERE id = ?')
        ->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
}

function delete_resident(int $id): bool
{
    $stmt = db()->prepare('DELETE FROM residents WHERE id = ?');
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        delete_photo($id);
        return true;
    }
    return false;
}
