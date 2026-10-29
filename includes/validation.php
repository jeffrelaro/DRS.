<?php
function validate_name(string $name): bool
{
    return preg_match("/^[\p{L} .'-]{2,50}$/u", $name) === 1;
}

function validate_age(string $age)
{
    return filter_var($age, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 120],
    ]);
}

function validate_email(string $email): bool
{
    return strlen($email) <= 100
        && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function normalize_phone(string $phone): string
{
    return preg_replace('/[\s\-()]/', '', $phone);
}

function validate_phone(string $phone): bool
{
    return preg_match('/^(09\d{9}|\+639\d{9})$/', normalize_phone($phone)) === 1;
}

function password_error(string $password): string
{
    if (strlen($password) < 8) {
        return 'Password must be at least 8 characters.';
    }
    if (strlen($password) > 72) {
        return 'Password must be 72 characters or fewer.';
    }
    if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        return 'Password must include at least one letter and one number.';
    }
    return '';
}
