<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/index.php');
verifyCsrf();

$adId    = (int)($_POST['ad_id'] ?? 0);
$reason  = $_POST['reason'] ?? '';
$comments = trim($_POST['comments'] ?? '');
$validReasons = ['Spam', 'Fraud', 'Duplicate', 'Offensive', 'Wrong Category'];

$stmt = $pdo->prepare('SELECT AdID FROM advertisements WHERE AdID = ?');
$stmt->execute([$adId]);
if (!$stmt->fetch() || !in_array($reason, $validReasons)) {
    setFlash('danger', 'Unable to submit report.');
    redirect('/index.php');
}

$pdo->prepare('INSERT INTO reports (UserID, AdID, Reason, Comments) VALUES (?,?,?,?)')
    ->execute([currentUserId(), $adId, $reason, $comments ?: null]);

setFlash('success', 'Thank you. Your report has been submitted for review.');
redirect('/ad_details.php?id=' . $adId);
