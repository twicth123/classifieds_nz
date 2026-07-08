<?php
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();
$__admin = currentUser($pdo);
$__current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? e($pageTitle) . ' - Admin' : 'Admin' ?> - <?= SITE_NAME ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="icon" href="data:,">
<style>
  .admin-sidebar { min-height: 100vh; background: #1e2530; }
  .admin-sidebar a { color: #c7cdd6; display: block; padding: .65rem 1.25rem; text-decoration: none; }
  .admin-sidebar a:hover, .admin-sidebar a.active { background: #0d6efd; color: #fff; }
  .admin-sidebar .brand { color: #fff; font-weight: 700; padding: 1rem 1.25rem; font-size: 1.1rem; }
</style>
</head>
<body>
<div class="d-flex">
  <div class="admin-sidebar" style="width: 230px;">
    <div class="brand"><?= SITE_NAME ?> Admin</div>
    <a href="index.php" class="<?= $__current === 'index.php' ? 'active' : '' ?>">📊 Dashboard</a>
    <a href="users.php" class="<?= $__current === 'users.php' ? 'active' : '' ?>">👤 Users</a>
    <a href="ads.php" class="<?= $__current === 'ads.php' ? 'active' : '' ?>">📢 Advertisements</a>
    <a href="categories.php" class="<?= $__current === 'categories.php' ? 'active' : '' ?>">🗂 Categories</a>
    <a href="reports.php" class="<?= $__current === 'reports.php' ? 'active' : '' ?>">🚩 Reports</a>
    <hr class="text-secondary mx-3">
    <a href="../index.php">🌐 View Site</a>
    <a href="../logout.php">🚪 Logout</a>
  </div>
  <div class="flex-grow-1">
    <div class="bg-white border-bottom px-4 py-2 d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><?= isset($pageTitle) ? e($pageTitle) : '' ?></h5>
      <span class="text-muted small">Logged in as <?= e($__admin['Name']) ?></span>
    </div>
    <div class="p-4">
      <?php foreach (getFlashes() as $flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
          <?= e($flash['message']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endforeach; ?>
