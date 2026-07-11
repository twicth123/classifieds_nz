<?php
/**
 * GET  /api/favorites.php                    -> favorite ads for the logged-in user
 * POST /api/favorites.php  action=add     {adId}
 * POST /api/favorites.php  action=remove  {adId}
 */
require_once __DIR__ . '/_bootstrap.php';

$user = requireAuthUser($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare(
        'SELECT a.* FROM favorites f
         JOIN advertisements a ON a.AdID = f.AdID
         WHERE f.UserID = ? ORDER BY f.CreatedDate DESC'
    );
    $stmt->execute([$user['UserID']]);
    $ads = array_map(fn($row) => adPublic($pdo, $row, (int)$user['UserID']), $stmt->fetchAll());
    ok(['ads' => $ads]);
}

$in = input();
$adId = (int)($in['adId'] ?? 0);
if (!$adId) fail('Missing adId.');

if (($in['action'] ?? '') === 'remove') {
    $pdo->prepare('DELETE FROM favorites WHERE UserID = ? AND AdID = ?')->execute([$user['UserID'], $adId]);
    ok();
}

$stmt = $pdo->prepare('INSERT IGNORE INTO favorites (UserID, AdID) VALUES (?, ?)');
$stmt->execute([$user['UserID'], $adId]);
ok();
