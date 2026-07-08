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
    setFlash('danger', 'Advertisement not found or you do not have permission to modify it.');
    redirect('/dashboard.php');
}

$pdo->prepare("UPDATE advertisements SET Status = 'Sold' WHERE AdID = ?")->execute([$adId]);
setFlash('success', 'Advertisement marked as sold.');
redirect('/dashboard.php');
