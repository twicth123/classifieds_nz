<?php
require_once __DIR__ . '/includes/functions.php';

$adId = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT a.*, u.Name AS SellerName, u.Mobile AS SellerMobile, u.Email AS SellerEmail, u.ProfilePhoto AS SellerPhoto, u.CreatedDate AS SellerSince
                        FROM advertisements a JOIN users u ON u.UserID = a.UserID
                        WHERE a.AdID = ?');
$stmt->execute([$adId]);
$ad = $stmt->fetch();

if (!$ad) {
    setFlash('danger', 'Advertisement not found.');
    redirect('/index.php');
}

$isOwner = isLoggedIn() && currentUserId() == $ad['UserID'];

// Only the owner or an admin can view non-active ads
if ($ad['Status'] !== 'Active' && !$isOwner && !isAdmin()) {
    setFlash('warning', 'This advertisement is not currently available.');
    redirect('/index.php');
}

// Track a view (only count once per session per ad)
if (empty($_SESSION['viewed_ads'][$adId])) {
    $pdo->prepare('UPDATE advertisements SET ViewCount = ViewCount + 1 WHERE AdID = ?')->execute([$adId]);
    $_SESSION['viewed_ads'][$adId] = true;
}

$imgStmt = $pdo->prepare('SELECT * FROM advertisement_images WHERE AdID = ? ORDER BY SequenceNo');
$imgStmt->execute([$adId]);
$images = $imgStmt->fetchAll();

$isFavorited = false;
if (isLoggedIn()) {
    $favStmt = $pdo->prepare('SELECT 1 FROM favorites WHERE UserID = ? AND AdID = ?');
    $favStmt->execute([currentUserId(), $adId]);
    $isFavorited = (bool)$favStmt->fetch();
}

$categoryName = getCategoryName($pdo, $ad['CategoryID']);

// Similar ads
$similarStmt = $pdo->prepare("SELECT a.*, (SELECT ImagePath FROM advertisement_images WHERE AdID = a.AdID ORDER BY SequenceNo LIMIT 1) AS Thumb
                               FROM advertisements a
                               WHERE a.CategoryID = ? AND a.AdID != ? AND a.Status = 'Active'
                               ORDER BY a.PostedDate DESC LIMIT 4");
$similarStmt->execute([$ad['CategoryID'], $adId]);
$similarAds = $similarStmt->fetchAll();

$pageTitle = $ad['Title'];
require_once __DIR__ . '/includes/header.php';

$whatsappNumber = '91' . preg_replace('/\D/', '', $ad['SellerMobile']);
$whatsappMsg = rawurlencode("Hi, I'm interested in your ad \"{$ad['Title']}\" on " . SITE_NAME);
?>

<div class="mb-3">
  <a href="index.php" class="text-decoration-none d-inline-flex align-items-center gap-1 text-muted hover-indigo fw-semibold">
    <i class="bi bi-arrow-left"></i> Back to listings
  </a>
</div>

<div class="row g-4">
  <!-- Left Column: Media Gallery & Details -->
  <div class="col-lg-8">
    <div class="main-ad-image-container mb-3">
      <?php if ($images): ?>
        <img src="<?= e(UPLOAD_URL . $images[0]['ImagePath']) ?>" class="main-ad-image" alt="<?= e($ad['Title']) ?>">
      <?php else: ?>
        <img src="https://placehold.co/700x420?text=No+Image" class="main-ad-image">
      <?php endif; ?>
    </div>
    
    <?php if ($images && count($images) > 1): ?>
      <div class="d-flex gap-2 thumb-strip flex-wrap mb-4">
        <?php foreach ($images as $i => $img): ?>
          <img src="<?= e(UPLOAD_URL . $img['ImagePath']) ?>" data-full="<?= e(UPLOAD_URL . $img['ImagePath']) ?>" class="img-thumbnail p-0 <?= $i == 0 ? 'active' : '' ?>" style="max-width: 90px; height: 68px; object-fit: cover;">
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mt-3">
      <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
          <span class="badge bg-light text-primary border px-3 py-2 fw-semibold fs-7"><?= e($categoryName) ?></span>
          <div class="small text-muted d-flex align-items-center gap-1">
            <i class="bi bi-eye"></i> <?= (int)$ad['ViewCount'] ?> views
          </div>
        </div>
        
        <h3 class="fw-bold mb-2 text-slate-800"><?= e($ad['Title']) ?></h3>
        
        <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
          <div class="ad-price fs-2"><?= formatPrice($ad['Price']) ?></div>
          <div><?= adStatusBadge($ad['Status']) ?></div>
          <span class="badge bg-light text-indigo border px-2.5 py-1.5"><?= e($ad['Condition']) ?> Condition</span>
        </div>

        <div class="border-top border-bottom py-3 my-4 d-flex flex-wrap gap-4 text-muted small">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-geo-alt-fill text-indigo-400"></i>
            <span><?= e($ad['City']) ?>, <?= e($ad['State']) ?></span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-calendar3 text-indigo-400"></i>
            <span>Posted: <?= formatDate($ad['PostedDate']) ?></span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-hash text-indigo-400"></i>
            <span>Ad ID: #<?= (int)$ad['AdID'] ?></span>
          </div>
        </div>

        <h5 class="fw-bold mb-3 text-slate-800">Description</h5>
        <p class="text-secondary" style="white-space: pre-wrap; line-height: 1.65;"><?= e($ad['Description']) ?></p>
      </div>
    </div>
  </div>

  <!-- Right Column: Seller & Contact Card -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body p-4">
        <h6 class="text-uppercase tracking-wider text-muted fw-bold small mb-3">Seller Details</h6>
        
        <div class="d-flex align-items-center gap-3 mb-4">
          <img src="<?= $ad['SellerPhoto'] ? e(UPLOAD_URL . $ad['SellerPhoto']) : 'https://ui-avatars.com/api/?name=' . urlencode($ad['SellerName']) . '&background=4f46e5&color=fff' ?>"
               class="rounded-circle border border-2 border-indigo-100" width="60" height="60" style="object-fit:cover;">
          <div>
            <div class="fw-bold text-slate-800"><?= e($ad['SellerName']) ?></div>
            <div class="small text-muted d-flex align-items-center gap-1">
              <i class="bi bi-calendar-check"></i> Member since <?= formatDate($ad['SellerSince']) ?>
            </div>
          </div>
        </div>

        <?php if (!isLoggedIn()): ?>
          <a href="login.php" class="btn btn-primary w-100 py-2.5 mb-2 d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-box-arrow-in-right"></i> Login to Contact Seller
          </a>
        <?php elseif ($isOwner): ?>
          <div class="alert alert-info py-2 px-3 mb-0 small d-flex align-items-center gap-2">
            <i class="bi bi-info-circle-fill"></i>
            <span>This is your advertisement listing.</span>
          </div>
        <?php else: ?>
          <div class="d-grid gap-2">
            <a class="btn btn-success py-2.5 d-flex align-items-center justify-content-center gap-2" href="tel:<?= e($ad['SellerMobile']) ?>">
              <i class="bi bi-telephone-fill"></i> Call <?= e($ad['SellerMobile']) ?>
            </a>
            <a class="btn btn-outline-success py-2.5 d-flex align-items-center justify-content-center gap-2" target="_blank" href="https://wa.me/<?= $whatsappNumber ?>?text=<?= $whatsappMsg ?>">
              <i class="bi bi-whatsapp"></i> WhatsApp Seller
            </a>
            <a class="btn btn-outline-primary py-2.5 d-flex align-items-center justify-content-center gap-2" href="mailto:<?= e($ad['SellerEmail']) ?>">
              <i class="bi bi-envelope"></i> Email Seller
            </a>
            <button class="btn btn-outline-danger py-2.5 favorite-btn detail-fav d-flex align-items-center justify-content-center gap-2 <?= $isFavorited ? 'text-danger' : '' ?>"
                    data-ad-id="<?= $ad['AdID'] ?>" data-csrf="<?= csrfToken() ?>">
              <?= $isFavorited ? '<i class="bi bi-heart-fill me-2"></i>Saved to Favorites' : '<i class="bi bi-heart me-2"></i>Save to Favorites' ?>
            </button>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if (isLoggedIn() && !$isOwner): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-body p-3">
        <a class="text-danger small fw-semibold text-decoration-none d-flex align-items-center gap-1" data-bs-toggle="collapse" href="#reportBox">
          <i class="bi bi-flag"></i> Report this listing
        </a>
        <div class="collapse mt-3" id="reportBox">
          <form method="post" action="report_ad.php">
            <?= csrfField() ?>
            <input type="hidden" name="ad_id" value="<?= $ad['AdID'] ?>">
            <div class="mb-2">
              <select name="reason" class="form-select form-select-sm" required>
                <option value="">Select reason...</option>
                <option>Spam</option>
                <option>Fraud</option>
                <option>Duplicate</option>
                <option>Offensive</option>
                <option>Wrong Category</option>
              </select>
            </div>
            <div class="mb-2">
              <textarea name="comments" class="form-control form-control-sm" rows="3" placeholder="Explain details (optional)..."></textarea>
            </div>
            <button class="btn btn-sm btn-danger w-100" type="submit">Submit Report</button>
          </form>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php if ($similarAds): ?>
<div class="mt-5 pt-3">
  <h4 class="fw-bold mb-4 text-slate-800 d-flex align-items-center gap-2">
    <i class="bi bi-grid-fill text-indigo-400"></i> Similar Listings
  </h4>
  <div class="row g-4">
    <?php foreach ($similarAds as $s): ?>
      <div class="col-sm-6 col-md-3">
        <div class="card ad-card border-0 shadow-sm">
          <a href="ad_details.php?id=<?= $s['AdID'] ?>" class="ad-card-img-wrapper d-block">
            <img src="<?= $s['Thumb'] ? e(UPLOAD_URL . $s['Thumb']) : 'https://placehold.co/300x180?text=No+Image' ?>" alt="<?= e($s['Title']) ?>">
          </a>
          <div class="card-body p-3">
            <h6 class="fw-bold mb-1 card-title text-truncate">
              <a href="ad_details.php?id=<?= $s['AdID'] ?>" class="text-decoration-none text-dark hover-indigo"><?= e($s['Title']) ?></a>
            </h6>
            <div class="ad-price"><?= formatPrice($s['Price']) ?></div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<style>
  .hover-indigo:hover { color: var(--primary-color) !important; }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
