<?php
/**
 * POST /api/report_ad.php  {adId, reason, comments}
 * reason must be one of: Spam, Fraud, Duplicate, Offensive, Wrong Category
 */
require_once __DIR__ . '/_bootstrap.php';

$user = requireAuthUser($pdo);
$in = input();

$adId = (int)($in['adId'] ?? 0);
$reason = $in['reason'] ?? '';
$comments = trim($in['comments'] ?? '') ?: null;

$validReasons = ['Spam', 'Fraud', 'Duplicate', 'Offensive', 'Wrong Category'];
if (!$adId || !in_array($reason, $validReasons, true)) {
    fail('Please choose a valid ad and reason.');
}

$pdo->prepare('INSERT INTO reports (UserID, AdID, Reason, Comments) VALUES (?, ?, ?, ?)')
    ->execute([$user['UserID'], $adId, $reason, $comments]);

ok();
