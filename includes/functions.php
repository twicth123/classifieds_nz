<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

/* ---------------------------------------------------------
 * Basic helpers
 * ------------------------------------------------------- */
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header('Location: ' . BASE_URL . $path);
    exit;
}

function old($key) {
    return e($_SESSION['old_input'][$key] ?? '');
}

function setFlash($type, $message) {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function getFlashes() {
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

/* ---------------------------------------------------------
 * CSRF protection
 * ------------------------------------------------------- */
function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

function verifyCsrf() {
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Invalid or expired form submission. Please go back and try again.');
    }
}

/* ---------------------------------------------------------
 * Auth helpers
 * ------------------------------------------------------- */
function isLoggedIn() {
    return !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('warning', 'Please login to continue.');
        redirect('/login.php');
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        setFlash('danger', 'Admin access required.');
        redirect('/login.php');
    }
}

function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function currentUser($pdo) {
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare('SELECT * FROM users WHERE UserID = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/* ---------------------------------------------------------
 * Validation helpers
 * ------------------------------------------------------- */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function isValidMobile($mobile) {
    return (bool) preg_match('/^[0-9]{10}$/', $mobile);
}

/* ---------------------------------------------------------
 * Image upload helper
 * ------------------------------------------------------- */
function handleImageUpload($file, $prefix = 'ad') {
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    if (!isset($allowed[$file['type']])) {
        return ['error' => 'Unsupported image type. Use JPG, PNG, WEBP or GIF.'];
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['error' => 'Image must be smaller than 5MB.'];
    }
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    $ext = $allowed[$file['type']];
    $filename = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = UPLOAD_DIR . $filename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['error' => 'Failed to save uploaded image.'];
    }
    return ['path' => $filename];
}

/* ---------------------------------------------------------
 * Formatting helpers
 * ------------------------------------------------------- */
function formatPrice($price) {
    return '₹' . number_format((float)$price, 0);
}

function formatDate($datetime) {
    return date('d M Y', strtotime($datetime));
}

function timeAgo($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 2592000) return floor($diff / 86400) . 'd ago';
    return formatDate($datetime);
}

function adStatusBadge($status) {
    $map = [
        'Draft'            => 'secondary',
        'Pending Approval' => 'warning',
        'Active'           => 'success',
        'Rejected'         => 'danger',
        'Sold'             => 'dark',
        'Expired'          => 'secondary',
    ];
    $color = $map[$status] ?? 'secondary';
    return '<span class="badge bg-' . $color . '">' . e($status) . '</span>';
}

/* ---------------------------------------------------------
 * Expire old ads (lightweight, runs opportunistically)
 * ------------------------------------------------------- */
function expireOldAds($pdo) {
    $pdo->prepare("UPDATE advertisements SET Status = 'Expired'
                   WHERE Status = 'Active' AND ExpiryDate IS NOT NULL AND ExpiryDate < NOW()")
        ->execute();
}

/* ---------------------------------------------------------
 * Category helpers
 * ------------------------------------------------------- */
function getParentCategories($pdo) {
    $stmt = $pdo->query("SELECT * FROM categories WHERE ParentCategoryID IS NULL ORDER BY DisplayOrder, Name");
    return $stmt->fetchAll();
}

function getSubCategories($pdo, $parentId) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE ParentCategoryID = ? ORDER BY DisplayOrder, Name");
    $stmt->execute([$parentId]);
    return $stmt->fetchAll();
}

function getCategoryName($pdo, $categoryId) {
    $stmt = $pdo->prepare("SELECT Name FROM categories WHERE CategoryID = ?");
    $stmt->execute([$categoryId]);
    $row = $stmt->fetch();
    return $row ? $row['Name'] : '';
}
