<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/dashboard.php');
verifyCsrf();

$adId = (int)($_POST['ad_id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM advertisements WHERE AdID = ?');
$stmt->execute([$adId]);
$ad = $stmt->fetch();

if (!$ad || $ad['UserID'] != currentUserId()) {
    setFlash('danger', 'Advertisement not found or you do not have permission to delete it.');
    redirect('/dashboard.php');
}

$imgStmt = $pdo->prepare('SELECT ImagePath FROM advertisement_images WHERE AdID = ?');
$imgStmt->execute([$adId]);
foreach ($imgStmt->fetchAll() as $img) {
    @unlink(UPLOAD_DIR . $img['ImagePath']);
}

$pdo->prepare('DELETE FROM advertisements WHERE AdID = ?')->execute([$adId]);

setFlash('success', 'Advertisement deleted.');
redirect('/dashboard.php');
