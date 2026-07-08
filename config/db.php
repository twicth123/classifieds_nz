<?php
/**
 * Database connection (PDO)
 * Update the credentials below to match your hosting environment.
 */
$DB_HOST = 'localhost';
$DB_NAME = 'classifieds_portal';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}

// Site-wide settings
define('SITE_NAME', 'classifieds');
define('BASE_URL', '/classifieds'); // e.g. '/classifieds' if hosted in a subfolder
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', BASE_URL . '/uploads/');
define('MAX_IMAGES_PER_AD', 5);
define('AD_EXPIRY_DAYS', 30);
