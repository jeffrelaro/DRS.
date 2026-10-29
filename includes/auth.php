<?php
const ADMIN_MAX_ATTEMPTS = 5;
const ADMIN_LOCK_MINUTES = 15;

function is_logged_in(): bool
{
    return !empty($_SESSION['resident_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function current_resident(): array
{
    require_login();

    $stmt = db()->prepare('SELECT * FROM residents WHERE id = ? LIMIT 1');
    $stmt->execute([(int) $_SESSION['resident_id']]);
    $resident = $stmt->fetch();

    if (!$resident) {
        redirect('logout.php');
    }
    return $resident;
}

function is_admin(): bool
{
    return !empty($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!is_admin()) {
        redirect('login.php');
    }
}

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 0, 45);
}

function admin_is_locked(): bool
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM admin_login_attempts WHERE ip = ? AND attempted_at > ?');
    $stmt->execute([client_ip(), date('Y-m-d H:i:s', time() - ADMIN_LOCK_MINUTES * 60)]);
    return (int) $stmt->fetchColumn() >= ADMIN_MAX_ATTEMPTS;
}

function admin_record_failure(): void
{
    db()->prepare('INSERT INTO admin_login_attempts (ip, attempted_at) VALUES (?, ?)')
        ->execute([client_ip(), date('Y-m-d H:i:s')]);
}

function admin_clear_failures(): void
{
    db()->prepare('DELETE FROM admin_login_attempts WHERE ip = ?')->execute([client_ip()]);
}

function find_reset(string $token): ?array
{
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM password_resets WHERE token_hash = ? LIMIT 1');
    $stmt->execute([hash('sha256', $token)]);
    $row = $stmt->fetch();

    if (!$row || strtotime($row['expires_at']) < time()) {
        return null;
    }
    return $row;
}
