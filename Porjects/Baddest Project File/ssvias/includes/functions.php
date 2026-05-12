<?php
/**
 * SSVIAS — Global Helper Functions
 */

require_once __DIR__ . '/auth_check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Sanitize output to prevent XSS */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/** JSON response helper for API endpoints */
function json_response(bool $success, string $message, array $data = [], int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

/** Get POST value safely */
function post(string $key, string $default = ''): string {
    return trim($_POST[$key] ?? $default);
}

/** Get GET value safely */
function get(string $key, string $default = ''): string {
    return trim($_GET[$key] ?? $default);
}

/** Format a timestamp to human-readable */
function time_ago(string $datetime): string {
    $now  = new DateTime();
    $then = new DateTime($datetime);
    $diff = $now->diff($then);
    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

/** Status badge HTML */
function status_badge(string $status): string {
    $map = [
        'active'    => ['class' => 'badge-success', 'icon' => '✓', 'label' => 'Active / Safe'],
        'stolen'    => ['class' => 'badge-danger',  'icon' => '⚠', 'label' => 'STOLEN'],
        'recovered' => ['class' => 'badge-warning', 'icon' => '↩', 'label' => 'Recovered'],
        'pending'   => ['class' => 'badge-warning', 'icon' => '⏳', 'label' => 'Pending'],
        'verified'  => ['class' => 'badge-success', 'icon' => '✓', 'label' => 'Verified'],
        'closed'    => ['class' => 'badge-gray',    'icon' => '✕', 'label' => 'Closed'],
    ];
    $s = $map[$status] ?? ['class' => 'badge-gray', 'icon' => '?', 'label' => ucfirst($status)];
    return '<span class="badge ' . $s['class'] . '">' . $s['icon'] . ' ' . $s['label'] . '</span>';
}

/** Create notification */
function create_notification(PDO $pdo, int $user_id, string $title, string $message, string $type = 'info'): void {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $title, $message, $type]);
}

/** Upload image helper */
function upload_image(array $file, string $dir): string|false {
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($file['type'], $allowed)) return false;
    if ($file['size'] > 5 * 1024 * 1024) return false; // 5MB max

    $upload_dir = __DIR__ . '/../uploads/' . $dir . '/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_', true) . '.' . strtolower($ext);
    $path     = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $path)) {
        return 'uploads/' . $dir . '/' . $filename;
    }
    return false;
}

/** Unread notification count */
function unread_notifications(PDO $pdo, int $user_id): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    return (int)$stmt->fetchColumn();
}

/** Get base URL */
function base_url(string $path = ''): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $docRoot  = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
    $appRoot  = '/';

    if ($docRoot !== '') {
        $currentDir = str_replace('\\', '/', realpath(__DIR__) ?: __DIR__);
        $docRoot = str_replace('\\', '/', $docRoot);
        if (str_starts_with($currentDir, $docRoot)) {
            $relative = substr($currentDir, strlen($docRoot));
            $appRoot = rtrim(dirname($relative), '/');
            if ($appRoot === '' || $appRoot === '.') {
                $appRoot = '/';
            }
        }
    }

    $base = $protocol . '://' . $host . ($appRoot === '/' ? '' : $appRoot);
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}
