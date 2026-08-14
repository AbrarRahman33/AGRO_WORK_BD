<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/db.php';

function base_url(string $path = ''): string
{
    $root = '/agro_work';
    return $root . ($path ? '/' . ltrim($path, '/') : '');
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . base_url($path));
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function pull_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function login_user(array $row): void
{
    $_SESSION['user'] = [
        'person_id' => (int)$row['person_id'],
        'name' => $row['name'],
        'role' => $row['role'],
        'nid' => $row['nid'],
    ];
}

function require_login(): array
{
    $user = current_user();
    if (!$user) redirect('login.php');
    return $user;
}

function require_role(string ...$roles): array
{
    $user = require_login();
    if (!in_array($user['role'], $roles, true)) {
        flash('error', 'You do not have permission to open that page.');
        redirect('dashboard.php');
    }
    return $user;
}

function scalar(PDO $pdo, string $sql, array $params = [])
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function db_error_message(Throwable $e): string
{
    if ($e instanceof PDOException && ($e->errorInfo[1] ?? null) === 1451) {
        return 'This record is already used by another record, so it cannot be deleted yet.';
    }
    if ($e instanceof PDOException && ($e->errorInfo[1] ?? null) === 1062) {
        return 'A record with the same unique value already exists.';
    }
    return 'The operation could not be completed because of a database rule.';
}
