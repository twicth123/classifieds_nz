<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$stmt = $pdo->prepare("SELECT a.*, (SELECT ImagePath FROM advertisement_images WHERE AdID = a.AdID ORDER BY SequenceNo LIMIT 1) AS Thumb
                        FROM favorites f
                        JOIN advertisements a ON a.AdID = f.AdID
                        WHERE f.UserID = ?
                        ORDER BY f.CreatedDate DESC");
$stmt->execute([currentUserId()]);
$favorites = $stmt->fetchAll();

$pageTitle = 'My Favorites';
require_once __DIR__ . '/includes/header.php';
?>

<h3 class="mb-4">My Favorites</h3>

<?php if (!$favorites): ?>
  <div class="alert alert-light border text-center py-5">
    <p class="mb-2">You haven't saved any advertisements yet.</p>
    <a href="index.php" class="btn btn-primary btn-sm">Browse Ads</a>
  </div>
<?php else: ?>
<div class="row g-3">
  <?php foreach ($favorites as $ad): ?>
    <div class="col-sm-6 col-md-4 ad-card-wrapper">
      <div class="card ad-card shadow-sm position-relative">
        <button class="btn btn-sm btn-light position-absolute top-0 end-0 m-2 favorite-btn text-danger"
                data-ad-id="<?= $ad['AdID'] ?>" data-csrf="<?= csrfToken() ?>" data-remove-on-unfav="1">
          ♥ Saved
        </button>
        <a href="ad_details.php?id=<?= $ad['AdID'] ?>">
          <img src="<?= $ad['Thumb'] ? e(UPLOAD_URL . $ad['Thumb']) : 'https://placehold.co/300x180?text=No+Image' ?>">
        </a>
        <div class="card-body">
          <h6 class="mb-1"><a href="ad_details.php?id=<?= $ad['AdID'] ?>" class="text-decoration-none text-dark"><?= e($ad['Title']) ?></a></h6>
          <div class="ad-price mb-1"><?= formatPrice($ad['Price']) ?></div>
          <div class="small text-muted"><?= e($ad['City']) ?>, <?= e($ad['State']) ?> &middot; <?= adStatusBadge($ad['Status']) ?></div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
