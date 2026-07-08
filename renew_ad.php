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
    setFlash('danger', 'Advertisement not found or you do not have permission to renew it.');
    redirect('/dashboard.php');
}

if (!in_array($ad['Status'], ['Expired', 'Active'])) {
    setFlash('danger', 'Only active or expired ads can be renewed.');
    redirect('/dashboard.php');
}

$newExpiry = date('Y-m-d H:i:s', strtotime('+' . AD_EXPIRY_DAYS . ' days'));
$pdo->prepare("UPDATE advertisements SET Status = 'Active', ExpiryDate = ?, PostedDate = NOW() WHERE AdID = ?")
    ->execute([$newExpiry, $adId]);

setFlash('success', 'Advertisement renewed for another ' . AD_EXPIRY_DAYS . ' days.');
redirect('/dashboard.php');
