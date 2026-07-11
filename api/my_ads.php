<?php
/**
 * GET /api/my_ads.php?status=Active   (status optional; requires Authorization: Bearer <token>)
 * -> all of the current user's ads, optionally filtered by status
 */
require_once __DIR__ . '/_bootstrap.php';

expireOldAds($pdo);
$user = requireAuthUser($pdo);

$where = ['UserID = ?'];
$args = [$user['UserID']];

$status = $_GET['status'] ?? '';
if ($status !== '') {
    $where[] = 'Status = ?';
    $args[] = $status;
}

$sql = 'SELECT * FROM advertisements WHERE ' . implode(' AND ', $where) . ' ORDER BY PostedDate DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($args);
$ads = array_map(fn($row) => adPublic($pdo, $row, (int)$user['UserID']), $stmt->fetchAll());

ok(['ads' => $ads]);
