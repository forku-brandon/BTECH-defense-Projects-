<?php
/**
 * SSVIAS — Auth API
 * POST /api/auth.php?action=login|register|logout
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$action = $_GET['action'] ?? post('action', 'login');

// ─── LOGOUT ──────────────────────────────────────────────
if ($action === 'logout') {
    session_destroy();
    header('Location: /ssvias/login.php?msg=' . urlencode('You have been logged out.'));
    exit;
}

// ─── LOGIN ────────────────────────────────────────────────
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = post('email');
    $password = post('password');

    if (empty($email) || empty($password)) {
        header('Location: /ssvias/login.php?err=' . urlencode('Email and password are required.'));
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        header('Location: /ssvias/login.php?err=' . urlencode('Invalid email or password.'));
        exit;
    }

    // Start session
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user']    = [
        'id'    => $user['id'],
        'name'  => $user['name'],
        'email' => $user['email'],
        'role'  => $user['role'],
        'phone' => $user['phone'],
    ];

    $redirect = in_array($user['role'], ['admin', 'officer']) ? '/ssvias/admin/' : '/ssvias/dashboard.php';
    header('Location: ' . $redirect . '?msg=' . urlencode('Welcome back, ' . explode(' ', $user['name'])[0] . '!'));
    exit;
}

// ─── REGISTER ────────────────────────────────────────────
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = post('name');
    $email    = post('email');
    $phone    = post('phone');
    $password = post('password');
    $confirm  = post('confirm_password');
    $role     = post('role', 'public');

    // Validate
    if (empty($name) || empty($email) || empty($password)) {
        header('Location: /ssvias/register.php?err=' . urlencode('Name, email, and password are required.'));
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: /ssvias/register.php?err=' . urlencode('Invalid email address.'));
        exit;
    }
    if (strlen($password) < 6) {
        header('Location: /ssvias/register.php?err=' . urlencode('Password must be at least 6 characters.'));
        exit;
    }
    if ($password !== $confirm) {
        header('Location: /ssvias/register.php?err=' . urlencode('Passwords do not match.'));
        exit;
    }
    // Only allow safe roles from register page
    if (!in_array($role, ['owner', 'public'])) $role = 'public';

    // Check duplicate email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header('Location: /ssvias/register.php?err=' . urlencode('An account with this email already exists.'));
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $email, $phone, $hash, $role]);
    $userId = (int)$pdo->lastInsertId();

    // Welcome notification
    create_notification($pdo, $userId, 'Welcome to SSVIAS!', 'Your account has been created. Register your vehicles to get started.', 'info');

    // Auto-login
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['user']    = ['id' => $userId, 'name' => $name, 'email' => $email, 'role' => $role, 'phone' => $phone];

    header('Location: /ssvias/dashboard.php?msg=' . urlencode('Welcome, ' . explode(' ', $name)[0] . '! Your account has been created.'));
    exit;
}

// Fallback
header('Location: /ssvias/login.php');
