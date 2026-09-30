<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

/** Returns the logged-in user's row, or null if nobody is logged in */
function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $stmt = get_db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        // Session points at a user that no longer exists — clear it.
        session_destroy();
        return null;
    }

    $cached = $user;
    return $user;
}

/** Redirect to login unless someone is signed in */
function require_login(): array
{
    $user = current_user();
    if (!$user) {
        redirect('/login.php');
    }
    return $user;
}

/** Redirect to login unless the signed-in user has the given role */
function require_role(string $role): array
{
    $user = require_login();
    if ($user['role'] !== $role) {
        redirect($role === 'admin' ? '/login.php' : '/dashboard.php');
    }
    return $user;
}

/**
 * Attempt to log a user in by phone + password.
 * Returns the user row on success, or null on failure.
 */
function attempt_login(string $phone, string $password): ?array
{
    $stmt = get_db()->prepare('SELECT * FROM users WHERE phone = ?');
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return null;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];

    return $user;
}

function log_out(): void
{
    $_SESSION = [];
    session_destroy();
}
