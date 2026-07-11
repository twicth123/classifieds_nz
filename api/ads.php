<?php
/**
 * GET  /api/ads.php?keyword=&categoryId=&subcategoryId=&city=&minPrice=&maxPrice=
 *                   &condition=&postedToday=1&sort=latest|oldest|price_low|price_high
 *      -> browse Active ads (same filters as the website's index.php)
 *
 * POST /api/ads.php  (multipart/form-data, requires Authorization: Bearer <token>)
 *      fields: categoryId, title, description, price, condition, city, state, asDraft (0/1)
 *      files:  images[]  (up to MAX_IMAGES_PER_AD)
 *      -> creates an ad, returns it
 */
require_once __DIR__ . '/_bootstrap.php';

expireOldAds($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $currentUser = authUser($pdo); // optional - only used to flag favorites
    $currentUserId = $currentUser['UserID'] ?? null;

    $where = ['Status = \'Active\''];
    $args = [];

    $keyword = trim($_GET['keyword'] ?? '');
    if ($keyword !== '') {
        $where[] = '(Title LIKE ? OR Description LIKE ?)';
        $args[] = "%$keyword%";
        $args[] = "%$keyword%";
    }

    $subcategoryId = $_GET['subcategoryId'] ?? null;
    $categoryId = $_GET['categoryId'] ?? null;
    if ($subcategoryId) {
        $where[] = 'CategoryID = ?';
        $args[] = (int)$subcategoryId;
    } elseif ($categoryId) {
        $subStmt = $pdo->prepare('SELECT CategoryID FROM categories WHERE ParentCategoryID = ?');
        $subStmt->execute([(int)$categoryId]);
        $ids = array_map(fn($r) => (int)$r['CategoryID'], $subStmt->fetchAll());
        $ids[] = (int)$categoryId;
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $where[] = "CategoryID IN ($placeholders)";
        array_push($args, ...$ids);
    }

    $city = trim($_GET['city'] ?? '');
    if ($city !== '') {
        $where[] = 'City LIKE ?';
        $args[] = "%$city%";
    }

    if (isset($_GET['minPrice']) && $_GET['minPrice'] !== '') {
        $where[] = 'Price >= ?';
        $args[] = (float)$_GET['minPrice'];
    }
    if (isset($_GET['maxPrice']) && $_GET['maxPrice'] !== '') {
        $where[] = 'Price <= ?';
        $args[] = (float)$_GET['maxPrice'];
    }

    $condition = $_GET['condition'] ?? '';
    if (in_array($condition, ['New', 'Used'], true)) {
        $where[] = 'Condition = ?';
        $args[] = $condition;
    }

    if (!empty($_GET['postedToday'])) {
        $where[] = 'PostedDate >= CURDATE()';
    }

    $sortParam = $_GET['sort'] ?? 'latest';
    if ($sortParam === 'oldest') {
        $orderBy = 'PostedDate ASC';
    } elseif ($sortParam === 'price_low') {
        $orderBy = 'Price ASC';
    } elseif ($sortParam === 'price_high') {
        $orderBy = 'Price DESC';
    } else {
        $orderBy = 'PostedDate DESC';
    }

    $sql = 'SELECT * FROM advertisements WHERE ' . implode(' AND ', $where) . " ORDER BY $orderBy LIMIT 200";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($args);
    $ads = array_map(fn($row) => adPublic($pdo, $row, $currentUserId), $stmt->fetchAll());

    ok(['ads' => $ads]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = requireAuthUser($pdo);
    $in = input();

    $categoryId = (int)($in['categoryId'] ?? 0);
    $title = trim($in['title'] ?? '');
    $description = trim($in['description'] ?? '');
    $price = (float)($in['price'] ?? 0);
    $condition = in_array($in['condition'] ?? '', ['New', 'Used'], true) ? $in['condition'] : 'Used';
    $city = trim($in['city'] ?? '');
    $state = trim($in['state'] ?? '');
    $asDraft = !empty($in['asDraft']) && $in['asDraft'] !== '0';

    if (!$categoryId || !$title || !$description || !$city || !$state) {
        fail('Please fill in category, title, description, city and state.');
    }

    $status = $asDraft ? 'Draft' : 'Pending Approval';
    $expiry = $asDraft ? null : date('Y-m-d H:i:s', strtotime('+' . AD_EXPIRY_DAYS . ' days'));

    $stmt = $pdo->prepare(
        'INSERT INTO advertisements (UserID, CategoryID, Title, Description, Price, Condition, City, State, Status, ExpiryDate)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$user['UserID'], $categoryId, $title, $description, $price, $condition, $city, $state, $status, $expiry]);
    $adId = (int)$pdo->lastInsertId();

    $seq = 0;
    foreach ($_FILES['images']['tmp_name'] ?? [] as $i => $tmpName) {
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
                ->execute([$adId, $result['path'], $seq]);
            $seq++;
        }
    }

    $stmt = $pdo->prepare('SELECT * FROM advertisements WHERE AdID = ?');
    $stmt->execute([$adId]);
    ok(['ad' => adPublic($pdo, $stmt->fetch(), (int)$user['UserID'])]);
}

fail('Method not allowed.', 405);
