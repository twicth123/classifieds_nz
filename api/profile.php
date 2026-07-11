<?php
/**
 * GET  /api/profile.php               -> current user
 * POST /api/profile.php  action=update       {name, mobile, city, state}
 * POST /api/profile.php  action=change_password  {newPassword}
 */
require_once __DIR__ . '/_bootstrap.php';

$user = requireAuthUser($pdo);
$in = input();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    ok(['user' => userPublic($user)]);
}

$action = $in['action'] ?? 'update';

if ($action === 'change_password') {
    $newPassword = (string)($in['newPassword'] ?? '');
    if (strlen($newPassword) < 6) fail('Password must be at least 6 characters.');
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $pdo->prepare('UPDATE users SET PasswordHash = ? WHERE UserID = ?')->execute([$hash, $user['UserID']]);
    ok();
}

if ($action === 'update') {
    $name = trim($in['name'] ?? $user['Name']);
    $mobile = trim($in['mobile'] ?? $user['Mobile']);
    $city = trim($in['city'] ?? ($user['City'] ?? ''));
    $state = trim($in['state'] ?? ($user['State'] ?? ''));

    $pdo->prepare('UPDATE users SET Name = ?, Mobile = ?, City = ?, State = ? WHERE UserID = ?')
        ->execute([$name, $mobile, $city ?: null, $state ?: null, $user['UserID']]);

    $stmt = $pdo->prepare('SELECT * FROM users WHERE UserID = ?');
    $stmt->execute([$user['UserID']]);
    ok(['user' => userPublic($stmt->fetch())]);
}

fail('Unknown action.');
