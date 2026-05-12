<?php
/**
 * SSVIAS — Auth Guard
 * Include at top of any protected page.
 * Usage:
 *   require_once 'includes/auth_check.php';          // any logged-in user
 *   require_role('admin');                            // specific role
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function current_user(): array {
    return $_SESSION['user'] ?? [];
}

function current_role(): string {
    return $_SESSION['user']['role'] ?? 'public';
}

function require_login(string $redirect = '/login.php'): void {
    if (!is_logged_in()) {
        header('Location: ' . $redirect);
        exit;
    }
}

function require_role(string $role, string $redirect = '/index.php'): void {
    require_login();
    $allowed = [
        'admin'   => ['admin'],
        'officer' => ['admin', 'officer'],
        'owner'   => ['admin', 'officer', 'owner'],
        'public'  => ['admin', 'officer', 'owner', 'public'],
    ];
    if (!in_array(current_role(), $allowed[$role] ?? [])) {
        header('Location: ' . $redirect);
        exit;
    }
}

function can(string $role): bool {
    $allowed = [
        'admin'   => ['admin'],
        'officer' => ['admin', 'officer'],
        'owner'   => ['admin', 'officer', 'owner'],
        'public'  => ['admin', 'officer', 'owner', 'public'],
    ];
    return in_array(current_role(), $allowed[$role] ?? []);
}
