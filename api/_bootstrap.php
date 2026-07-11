<?php
/**
 * Shared bootstrap for every api/*.php endpoint.
 *
 * Reuses the site's existing PDO connection (config/db.php) and constants
 * (UPLOAD_DIR, UPLOAD_URL, MAX_IMAGES_PER_AD, AD_EXPIRY_DAYS) so the mobile
 * app and the website read/write exactly the same data.
 */

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');
// Adjust this to your app's actual origin(s) in production if you want to
// lock it down; '*' is fine for a mobile app (no browser cookies involved).
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function jsonInput(): array {
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/** Marks any Active ad past its ExpiryDate as Expired. Mirrors the same
 *  helper in the website's includes/functions.php - duplicated here so the
 *  api/ folder has no dependency on that file. */
function expireOldAds(PDO $pdo): void {
    $pdo->prepare(
        "UPDATE advertisements SET Status = 'Expired'
         WHERE Status = 'Active' AND ExpiryDate IS NOT NULL AND ExpiryDate < NOW()"
    )->execute();
}

/** Merge JSON body + POST + GET so endpoints work whether the app sends
 *  application/json or multipart/form-data (needed for image uploads). */
function input(): array {
    static $merged = null;
    if ($merged === null) {
        $merged = array_merge($_GET, $_POST, jsonInput());
    }
    return $merged;
}

function respond($data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function fail(string $message, int $code = 400): void {
    respond(['success' => false, 'error' => $message], $code);
}

function ok($data = []): void {
    respond(array_merge(['success' => true], $data));
}

function bearerToken(): ?string {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/Bearer\s+(\S+)/i', $header, $m)) {
        return $m[1];
    }
    return null;
}

/** Returns the authenticated user row, or null if no/invalid token. */
function authUser(PDO $pdo): ?array {
    $token = bearerToken();
    if (!$token) return null;
    $stmt = $pdo->prepare(
        'SELECT u.* FROM api_tokens t
         JOIN users u ON u.UserID = t.UserID
         WHERE t.Token = ? AND t.ExpiryDate > NOW()'
    );
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    return $user ?: null;
}

/** Ends the request with 401 if there's no valid token. */
function requireAuthUser(PDO $pdo): array {
    $user = authUser($pdo);
    if (!$user) fail('Not authenticated. Please log in again.', 401);
    return $user;
}

function issueToken(PDO $pdo, int $userId): string {
    $token = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare(
        'INSERT INTO api_tokens (UserID, Token, ExpiryDate) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))'
    );
    $stmt->execute([$userId, $token]);
    return $token;
}

function userPublic(array $u): array {
    return [
        'id' => (int)$u['UserID'],
        'name' => $u['Name'],
        'email' => $u['Email'],
        'mobile' => $u['Mobile'],
        'city' => $u['City'],
        'state' => $u['State'],
        'profilePhoto' => $u['ProfilePhoto'] ? UPLOAD_URL . $u['ProfilePhoto'] : null,
        'role' => $u['Role'],
        'status' => $u['Status'],
        'createdDate' => $u['CreatedDate'],
    ];
}

function adPublic(PDO $pdo, array $ad, ?int $currentUserId = null): array {
    $imgStmt = $pdo->prepare('SELECT ImagePath FROM advertisement_images WHERE AdID = ? ORDER BY SequenceNo ASC');
    $imgStmt->execute([$ad['AdID']]);
    $images = array_map(fn($r) => UPLOAD_URL . $r['ImagePath'], $imgStmt->fetchAll());

    $isFav = false;
    if ($currentUserId) {
        $favStmt = $pdo->prepare('SELECT 1 FROM favorites WHERE AdID = ? AND UserID = ?');
        $favStmt->execute([$ad['AdID'], $currentUserId]);
        $isFav = (bool)$favStmt->fetch();
    }

    $catStmt = $pdo->prepare('SELECT Name FROM categories WHERE CategoryID = ?');
    $catStmt->execute([$ad['CategoryID']]);
    $catName = $catStmt->fetchColumn() ?: null;

    $sellerStmt = $pdo->prepare('SELECT Name, Mobile, Email FROM users WHERE UserID = ?');
    $sellerStmt->execute([$ad['UserID']]);
    $seller = $sellerStmt->fetch();

    return [
        'id' => (int)$ad['AdID'],
        'userId' => (int)$ad['UserID'],
        'categoryId' => (int)$ad['CategoryID'],
        'categoryName' => $catName,
        'title' => $ad['Title'],
        'description' => $ad['Description'],
        'price' => (float)$ad['Price'],
        'condition' => $ad['Condition'],
        'city' => $ad['City'],
        'state' => $ad['State'],
        'status' => $ad['Status'],
        'rejectReason' => $ad['RejectReason'],
        'viewCount' => (int)$ad['ViewCount'],
        'postedDate' => $ad['PostedDate'],
        'expiryDate' => $ad['ExpiryDate'],
        'images' => $images,
        'isFavorite' => $isFav,
        'sellerName' => $seller['Name'] ?? null,
        'sellerMobile' => $seller['Mobile'] ?? null,
        'sellerEmail' => $seller['Email'] ?? null,
    ];
}
