<?php
/**
 * SSVIAS — Notifications API
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_login();

$action = post('action', get('action', ''));
$userId = (int)$_SESSION['user_id'];

if ($action === 'mark_read') {
    $id = (int)post('id');
    $pdo->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?")->execute([$id, $userId]);
    header('Location: /ssvias/notifications.php');
    exit;
}
if ($action === 'mark_all_read') {
    $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$userId]);
    header('Location: /ssvias/notifications.php?msg=' . urlencode('All notifications marked as read.'));
    exit;
}
if ($action === 'delete') {
    $id = (int)post('id');
    $pdo->prepare("DELETE FROM notifications WHERE id=? AND user_id=?")->execute([$id, $userId]);
    header('Location: /ssvias/notifications.php');
    exit;
}
header('Location: /ssvias/notifications.php');
