<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/dashboard.php');
verifyCsrf();

$adId = (int)($_POST['ad_id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM advertisements WHERE AdID = ?');
$stmt->execute([$adId]);
$ad = $stmt->fetch();

if (!$ad || $ad['UserID'] != currentUserId() || $ad['Status'] !== 'Draft') {
    setFlash('danger', 'Unable to publish this advertisement.');
    redirect('/dashboard.php');
}

$imgCount = $pdo->prepare('SELECT COUNT(*) FROM advertisement_images WHERE AdID = ?');
$imgCount->execute([$adId]);
if ((int)$imgCount->fetchColumn() === 0) {
    setFlash('danger', 'Please add at least one image before publishing. Edit the ad to add images.');
    redirect('/dashboard.php');
}

$expiryDate = date('Y-m-d H:i:s', strtotime('+' . AD_EXPIRY_DAYS . ' days'));
$pdo->prepare("UPDATE advertisements SET Status = 'Pending Approval', ExpiryDate = ? WHERE AdID = ?")
    ->execute([$expiryDate, $adId]);

setFlash('success', 'Draft submitted for admin approval.');
redirect('/dashboard.php');
