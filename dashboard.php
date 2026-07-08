<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
expireOldAds($pdo);

$userId = currentUserId();
$tab = $_GET['tab'] ?? 'active';

$stmt = $pdo->prepare("SELECT a.*, (SELECT ImagePath FROM advertisement_images WHERE AdID = a.AdID ORDER BY SequenceNo LIMIT 1) AS Thumb
                        FROM advertisements a WHERE a.UserID = ? ORDER BY a.PostedDate DESC");
$stmt->execute([$userId]);
$allAds = $stmt->fetchAll();

$grouped = ['active' => [], 'pending' => [], 'draft' => [], 'sold' => [], 'expired' => [], 'rejected' => []];
foreach ($allAds as $ad) {
    switch ($ad['Status']) {
        case 'Active': $grouped['active'][] = $ad; break;
        case 'Pending Approval': $grouped['pending'][] = $ad; break;
        case 'Draft': $grouped['draft'][] = $ad; break;
        case 'Sold': $grouped['sold'][] = $ad; break;
        case 'Expired': $grouped['expired'][] = $ad; break;
        case 'Rejected': $grouped['rejected'][] = $ad; break;
    }
}

$tabs = [
    'active'   => 'Active (' . count($grouped['active']) . ')',
    'pending'  => 'Pending (' . count($grouped['pending']) . ')',
    'draft'    => 'Drafts (' . count($grouped['draft']) . ')',
    'sold'     => 'Sold (' . count($grouped['sold']) . ')',
    'expired'  => 'Expired (' . count($grouped['expired']) . ')',
    'rejected' => 'Rejected (' . count($grouped['rejected']) . ')',
];
if (!isset($grouped[$tab])) $tab = 'active';

$pageTitle = 'My Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h3 class="mb-0">My Dashboard</h3>
  <a href="post_ad.php" class="btn btn-warning fw-semibold">+ Post New Ad</a>
</div>

<ul class="nav nav-tabs mb-4">
  <?php foreach ($tabs as $key => $label): ?>
    <li class="nav-item">
      <a class="nav-link <?= $tab === $key ? 'active' : '' ?>" href="?tab=<?= $key ?>"><?= e($label) ?></a>
    </li>
  <?php endforeach; ?>
</ul>

<?php $ads = $grouped[$tab]; ?>

<?php if (!$ads): ?>
  <div class="alert alert-light border text-center py-5">
    <p class="mb-0">No advertisements in this category.</p>
  </div>
<?php else: ?>
<div class="table-responsive">
<table class="table align-middle bg-white">
  <thead>
    <tr>
      <th>Ad</th><th>Category</th><th>Price</th><th>Status</th><th>Posted</th><th>Views</th><th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($ads as $ad): ?>
      <tr>
        <td class="d-flex align-items-center gap-2">
          <img src="<?= $ad['Thumb'] ? e(UPLOAD_URL . $ad['Thumb']) : 'https://placehold.co/60x45?text=No+Img' ?>" width="60" height="45" style="object-fit:cover;border-radius:4px;">
          <a href="ad_details.php?id=<?= $ad['AdID'] ?>" class="text-decoration-none"><?= e($ad['Title']) ?></a>
        </td>
        <td><?= e(getCategoryName($pdo, $ad['CategoryID'])) ?></td>
        <td><?= formatPrice($ad['Price']) ?></td>
        <td><?= adStatusBadge($ad['Status']) ?>
          <?php if ($ad['Status'] === 'Rejected' && $ad['RejectReason']): ?>
            <div class="small text-danger mt-1"><?= e($ad['RejectReason']) ?></div>
          <?php endif; ?>
        </td>
        <td class="small text-muted"><?= formatDate($ad['PostedDate']) ?></td>
        <td><?= (int)$ad['ViewCount'] ?></td>
        <td>
          <div class="d-flex flex-wrap gap-1">
            <?php if (in_array($ad['Status'], ['Active', 'Draft', 'Rejected', 'Pending Approval'])): ?>
              <a href="edit_ad.php?id=<?= $ad['AdID'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
            <?php endif; ?>

            <?php if ($ad['Status'] === 'Draft'): ?>
              <form method="post" action="publish_ad.php" class="d-inline">
                <?= csrfField() ?><input type="hidden" name="ad_id" value="<?= $ad['AdID'] ?>">
                <button class="btn btn-sm btn-outline-success">Submit for Approval</button>
              </form>
            <?php endif; ?>

            <?php if ($ad['Status'] === 'Active'): ?>
              <form method="post" action="mark_sold.php" class="d-inline">
                <?= csrfField() ?><input type="hidden" name="ad_id" value="<?= $ad['AdID'] ?>">
                <button class="btn btn-sm btn-outline-dark">Mark Sold</button>
              </form>
            <?php endif; ?>

            <?php if (in_array($ad['Status'], ['Active', 'Expired'])): ?>
              <form method="post" action="renew_ad.php" class="d-inline">
                <?= csrfField() ?><input type="hidden" name="ad_id" value="<?= $ad['AdID'] ?>">
                <button class="btn btn-sm btn-outline-secondary">Renew</button>
              </form>
            <?php endif; ?>

            <form method="post" action="delete_ad.php" class="d-inline" onsubmit="return confirm('Delete this ad permanently?');">
              <?= csrfField() ?><input type="hidden" name="ad_id" value="<?= $ad['AdID'] ?>">
              <button class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
