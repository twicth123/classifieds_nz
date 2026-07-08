<?php
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'unauthenticated']);
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$adId = (int)($_POST['ad_id'] ?? 0);
$userId = currentUserId();

$stmt = $pdo->prepare('SELECT FavoriteID FROM favorites WHERE UserID = ? AND AdID = ?');
$stmt->execute([$userId, $adId]);
$existing = $stmt->fetch();

if ($existing) {
    $pdo->prepare('DELETE FROM favorites WHERE FavoriteID = ?')->execute([$existing['FavoriteID']]);
    echo json_encode(['status' => 'ok', 'favorited' => false]);
} else {
    $pdo->prepare('INSERT INTO favorites (UserID, AdID) VALUES (?,?)')->execute([$userId, $adId]);
    echo json_encode(['status' => 'ok', 'favorited' => true]);
}
