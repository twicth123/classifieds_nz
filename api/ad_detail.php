<?php
/**
 * GET  /api/ad_detail.php?id=123               -> ad details (increments ViewCount)
 * POST /api/ad_detail.php  (requires Authorization: Bearer <token>, owner only)
 *      action=update        {id, categoryId, title, description, price, condition, city, state}
 *                            + optional multipart images[] to REPLACE all images
 *      action=delete         {id}
 *      action=mark_sold       {id}
 *      action=renew            {id}
 *      action=publish           {id}   (Draft -> Pending Approval)
 */
require_once __DIR__ . '/_bootstrap.php';

expireOldAds($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) fail('Missing ad id.');

    $pdo->prepare('UPDATE advertisements SET ViewCount = ViewCount + 1 WHERE AdID = ?')->execute([$id]);

    $stmt = $pdo->prepare('SELECT * FROM advertisements WHERE AdID = ?');
    $stmt->execute([$id]);
    $ad = $stmt->fetch();
    if (!$ad) fail('This ad could not be found.', 404);

    $currentUser = authUser($pdo);
    ok(['ad' => adPublic($pdo, $ad, $currentUser['UserID'] ?? null)]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = requireAuthUser($pdo);
    $in = input();
    $id = (int)($in['id'] ?? 0);
    $action = $in['action'] ?? 'update';
    if (!$id) fail('Missing ad id.');

    $stmt = $pdo->prepare('SELECT * FROM advertisements WHERE AdID = ?');
    $stmt->execute([$id]);
    $ad = $stmt->fetch();
    if (!$ad) fail('This ad could not be found.', 404);
    if ((int)$ad['UserID'] !== (int)$user['UserID']) fail('You do not own this ad.', 403);

    switch ($action) {
        case 'update': {
            $categoryId = (int)($in['categoryId'] ?? $ad['CategoryID']);
            $title = trim($in['title'] ?? $ad['Title']);
            $description = trim($in['description'] ?? $ad['Description']);
            $price = (float)($in['price'] ?? $ad['Price']);
            $condition = in_array($in['condition'] ?? '', ['New', 'Used'], true) ? $in['condition'] : $ad['Condition'];
            $city = trim($in['city'] ?? $ad['City']);
            $state = trim($in['state'] ?? $ad['State']);

            $pdo->prepare(
                'UPDATE advertisements SET CategoryID=?, Title=?, Description=?, Price=?, Condition=?, City=?, State=? WHERE AdID=?'
            )->execute([$categoryId, $title, $description, $price, $condition, $city, $state, $id]);

            if (!empty($_FILES['images']['tmp_name'])) {
                $pdo->prepare('DELETE FROM advertisement_images WHERE AdID = ?')->execute([$id]);
                $seq = 0;
                foreach ($_FILES['images']['tmp_name'] as $i => $tmpName) {
                    if ($seq >= MAX_IMAGES_PER_AD) break;
                    if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                    $file = [
                        'type' => $_FILES['images']['type'][$i],
                        'size' => $_FILES['images']['size'][$i],
                        'tmp_name' => $tmpName,
                    ];
                    $result = handleImageUpload($file, 'ad');
                    if (isset($result['path'])) {
                        $pdo->prepare('INSERT INTO advertisement_images (AdID, ImagePath, SequenceNo) VALUES (?, ?, ?)')
                            ->execute([$id, $result['path'], $seq]);
                        $seq++;
                    }
                }
            }
            break;
        }
        case 'delete':
            $pdo->prepare('DELETE FROM advertisements WHERE AdID = ?')->execute([$id]);
            ok();
            break;
        case 'mark_sold':
            $pdo->prepare("UPDATE advertisements SET Status = 'Sold' WHERE AdID = ?")->execute([$id]);
            break;
        case 'renew':
            $expiry = date('Y-m-d H:i:s', strtotime('+' . AD_EXPIRY_DAYS . ' days'));
            $pdo->prepare("UPDATE advertisements SET Status = 'Active', ExpiryDate = ?, PostedDate = NOW() WHERE AdID = ?")
                ->execute([$expiry, $id]);
            break;
        case 'publish':
            $expiry = date('Y-m-d H:i:s', strtotime('+' . AD_EXPIRY_DAYS . ' days'));
            $pdo->prepare("UPDATE advertisements SET Status = 'Pending Approval', ExpiryDate = ?, PostedDate = NOW() WHERE AdID = ?")
                ->execute([$expiry, $id]);
            break;
        default:
            fail('Unknown action.');
    }

    $stmt = $pdo->prepare('SELECT * FROM advertisements WHERE AdID = ?');
    $stmt->execute([$id]);
    ok(['ad' => adPublic($pdo, $stmt->fetch(), (int)$user['UserID'])]);
}

fail('Method not allowed.', 405);
