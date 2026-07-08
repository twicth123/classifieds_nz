<?php
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/json');

$categoryId = (int)($_GET['category_id'] ?? 0);
echo json_encode(getSubCategories($pdo, $categoryId));
