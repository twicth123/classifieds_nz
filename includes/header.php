<?php
require_once __DIR__ . '/functions.php';
$__user = isLoggedIn() ? currentUser($pdo) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? e($pageTitle) . ' - ' . SITE_NAME : SITE_NAME ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="icon" href="data:,">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container">
    <a class="navbar-brand fw-extrabold d-flex align-items-center gap-2" href="<?= BASE_URL ?>/index.php">
      <i class="bi bi-tag-fill text-indigo-300"></i>
      <span><?= SITE_NAME ?></span>
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <form class="d-flex mx-lg-auto my-2 my-lg-0 flex-grow-1 flex-lg-grow-0 nav-search" style="max-width: 480px;" action="<?= BASE_URL ?>/index.php" method="get">
        <i class="bi bi-search nav-search-icon"></i>
        <input class="form-control" type="search" name="q" placeholder="Search ads..." value="<?= e($_GET['q'] ?? '') ?>">
      </form>
      <ul class="navbar-nav ms-lg-3 align-items-lg-center">
        <?php if ($__user): ?>
          <li class="nav-item">
            <a class="btn btn-warning fw-semibold me-lg-3 my-1 d-inline-flex align-items-center gap-1" href="<?= BASE_URL ?>/post_ad.php">
              <i class="bi bi-plus-lg"></i> Post Ad
            </a>
          </li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/favorites.php"><i class="bi bi-heart me-1"></i>Favorites</a></li>
          <?php if (isAdmin()): ?>
            <li class="nav-item"><a class="nav-link text-warning" href="<?= BASE_URL ?>/admin/index.php"><i class="bi bi-shield-lock me-1"></i>Admin</a></li>
          <?php endif; ?>
          <li class="nav-item dropdown ms-lg-2">
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
              <img src="<?= $__user['ProfilePhoto'] ? e(UPLOAD_URL . $__user['ProfilePhoto']) : 'https://ui-avatars.com/api/?name=' . urlencode($__user['Name']) . '&background=6366f1&color=fff' ?>" 
                   class="rounded-circle border border-secondary" width="32" height="32" style="object-fit:cover;">
              <span><?= e($__user['Name']) ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-2">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/change_password.php"><i class="bi bi-key me-2"></i>Change Password</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a></li>
          <li class="nav-item ms-lg-2"><a class="btn btn-outline-light btn-sm my-1 px-3" href="<?= BASE_URL ?>/register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <?php foreach (getFlashes() as $flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show animated-fade-in" role="alert">
      <i class="bi <?= $flash['type'] === 'success' ? 'bi-check-circle-fill' : ($flash['type'] === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill') ?> me-2"></i>
      <?= e($flash['message']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endforeach; ?>
</div>

<div class="container mb-5 pb-5 animated-fade-in">
