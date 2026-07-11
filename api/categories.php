<?php
/**
 * GET /api/categories.php -> flat list of all categories (top-level and sub),
 * each with ParentCategoryID so the app can group them itself - same shape
 * as the categories table.
 */
require_once __DIR__ . '/_bootstrap.php';

$rows = $pdo->query('SELECT * FROM categories ORDER BY ParentCategoryID IS NOT NULL, DisplayOrder, Name')->fetchAll();

$categories = array_map(fn($r) => [
    'id' => (int)$r['CategoryID'],
    'name' => $r['Name'],
    'parentCategoryId' => $r['ParentCategoryID'] !== null ? (int)$r['ParentCategoryID'] : null,
    'displayOrder' => (int)$r['DisplayOrder'],
], $rows);

ok(['categories' => $categories]);
