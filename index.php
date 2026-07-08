<?php
require_once __DIR__ . '/includes/functions.php';
expireOldAds($pdo);

// ---- Read filters ----
$q          = trim($_GET['q'] ?? '');
$categoryId = $_GET['category'] ?? '';
$subCatId   = $_GET['subcategory'] ?? '';
$city       = trim($_GET['city'] ?? '');
$minPrice   = $_GET['min_price'] ?? '';
$maxPrice   = $_GET['max_price'] ?? '';
$condition  = $_GET['condition'] ?? '';
$postedToday = isset($_GET['posted_today']);
$sort       = $_GET['sort'] ?? 'latest';
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = 12;

// ---- Build query ----
$where = ["a.Status = 'Active'"];
$params = [];

if ($q !== '') {
    $where[] = '(a.Title LIKE ? OR a.Description LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($subCatId !== '') {
    $where[] = 'a.CategoryID = ?';
    $params[] = $subCatId;
} elseif ($categoryId !== '') {
    $where[] = 'a.CategoryID IN (SELECT CategoryID FROM categories WHERE CategoryID = ? OR ParentCategoryID = ?)';
    $params[] = $categoryId;
    $params[] = $categoryId;
}
if ($city !== '') {
    $where[] = 'a.City LIKE ?';
    $params[] = "%$city%";
}
if ($minPrice !== '') {
    $where[] = 'a.Price >= ?';
    $params[] = $minPrice;
}
if ($maxPrice !== '') {
    $where[] = 'a.Price <= ?';
    $params[] = $maxPrice;
}
if ($condition !== '' && in_array($condition, ['New', 'Used'])) {
    $where[] = 'a.Condition = ?';
    $params[] = $condition;
}
if ($postedToday) {
    $where[] = 'DATE(a.PostedDate) = CURDATE()';
}

$orderBy = match ($sort) {
    'oldest'   => 'a.PostedDate ASC',
    'price_low'  => 'a.Price ASC',
    'price_high' => 'a.Price DESC',
    default    => 'a.PostedDate DESC',
};

$whereSql = implode(' AND ', $where);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM advertisements a WHERE $whereSql");
$countStmt->execute($params);
$totalAds = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalAds / $perPage));
$offset = ($page - 1) * $perPage;

$sql = "SELECT a.*, 
               (SELECT ImagePath FROM advertisement_images WHERE AdID = a.AdID ORDER BY SequenceNo LIMIT 1) AS Thumb
        FROM advertisements a
        WHERE $whereSql
        ORDER BY $orderBy
        LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ads = $stmt->fetchAll();

$parentCategories = getParentCategories($pdo);
$favoriteAdIds = [];
if (isLoggedIn()) {
    $favStmt = $pdo->prepare('SELECT AdID FROM favorites WHERE UserID = ?');
    $favStmt->execute([currentUserId()]);
    $favoriteAdIds = $favStmt->fetchAll(PDO::FETCH_COLUMN);
}

$pageTitle = 'Browse Ads';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row">
  <!-- Filters sidebar -->
  <div class="col-lg-3 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-4">
        <h5 class="card-title fw-bold mb-4 d-flex align-items-center gap-2">
          <i class="bi bi-sliders text-primary"></i>
          <span>Filters</span>
        </h5>
        <form method="get">
          <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif; ?>

          <div class="mb-3">
            <label class="form-label text-muted small uppercase">Category</label>
            <select name="category" class="form-select" onchange="this.form.submit()">
              <option value="">All Categories</option>
              <?php foreach ($parentCategories as $cat): ?>
                <option value="<?= $cat['CategoryID'] ?>" <?= (string)$categoryId === (string)$cat['CategoryID'] ? 'selected' : '' ?>>
                  <?= e($cat['Name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <?php if ($categoryId): $subs = getSubCategories($pdo, $categoryId); if ($subs): ?>
          <div class="mb-3">
            <label class="form-label text-muted small uppercase">Subcategory</label>
            <select name="subcategory" class="form-select" onchange="this.form.submit()">
              <option value="">All Subcategories</option>
              <?php foreach ($subs as $sub): ?>
                <option value="<?= $sub['CategoryID'] ?>" <?= (string)$subCatId === (string)$sub['CategoryID'] ? 'selected' : '' ?>>
                  <?= e($sub['Name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; endif; ?>

          <div class="mb-3">
            <label class="form-label text-muted small uppercase">City</label>
            <div class="input-group">
              <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-geo-alt"></i></span>
              <input type="text" name="city" class="form-control border-start-0 ps-0" value="<?= e($city) ?>" placeholder="e.g. Hyderabad">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label text-muted small uppercase">Price Range</label>
            <div class="d-flex gap-2">
              <input type="number" name="min_price" class="form-control" value="<?= e($minPrice) ?>" placeholder="Min">
              <input type="number" name="max_price" class="form-control" value="<?= e($maxPrice) ?>" placeholder="Max">
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label text-muted small uppercase">Condition</label>
            <select name="condition" class="form-select">
              <option value="">Any</option>
              <option value="New" <?= $condition === 'New' ? 'selected' : '' ?>>New</option>
              <option value="Used" <?= $condition === 'Used' ? 'selected' : '' ?>>Used</option>
            </select>
          </div>

          <div class="form-check form-switch mb-4">
            <input class="form-check-input" type="checkbox" name="posted_today" id="postedToday" <?= $postedToday ? 'checked' : '' ?>>
            <label class="form-check-label small fw-semibold text-muted" for="postedToday">Posted Today</label>
          </div>

          <input type="hidden" name="sort" value="<?= e($sort) ?>">
          <button type="submit" class="btn btn-primary w-100 mb-2 py-2"><i class="bi bi-funnel me-1"></i> Apply Filters</button>
          <a href="index.php" class="btn btn-outline-secondary w-100 py-2"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filters</a>
        </form>
      </div>
    </div>
  </div>

  <!-- Results -->
  <div class="col-lg-9">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
      <h5 class="mb-2 fw-bold text-slate-800">
        <?= $totalAds ?> ad<?= $totalAds == 1 ? '' : 's' ?> found<?= $q !== '' ? ' for "' . e($q) . '"' : '' ?>
      </h5>
      <form method="get" class="d-flex align-items-center gap-2 mb-2">
        <?php foreach ($_GET as $k => $v) if ($k !== 'sort' && $k !== 'page'): ?>
          <input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>">
        <?php endif; ?>
        <label class="small text-muted mb-0 fw-semibold">Sort by:</label>
        <select name="sort" class="form-select form-select-sm border-0 bg-transparent fw-bold text-primary py-0 pe-4" style="width:auto; cursor:pointer;" onchange="this.form.submit()">
          <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Latest First</option>
          <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
          <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Lowest Price</option>
          <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Highest Price</option>
        </select>
      </form>
    </div>

    <?php if (!$ads): ?>
      <div class="card border-0 shadow-sm text-center py-5">
        <div class="card-body">
          <i class="bi bi-search fs-1 text-muted mb-3 d-block"></i>
          <p class="mb-0 text-muted">No advertisements match your filters. Try broadening your search terms.</p>
        </div>
      </div>
    <?php else: ?>
    <div class="row g-4">
      <?php foreach ($ads as $ad): ?>
        <div class="col-sm-6 col-md-4 ad-card-wrapper">
          <div class="card ad-card border-0 shadow-sm position-relative">
            <?php if (isLoggedIn()): ?>
              <button class="btn btn-sm btn-light position-absolute top-0 end-0 m-3 favorite-btn <?= in_array($ad['AdID'], $favoriteAdIds) ? 'text-danger' : '' ?>"
                      data-ad-id="<?= $ad['AdID'] ?>" data-csrf="<?= csrfToken() ?>" data-remove-on-unfav="0">
                <?= in_array($ad['AdID'], $favoriteAdIds) ? '<i class="bi bi-heart-fill"></i>' : '<i class="bi bi-heart"></i>' ?>
              </button>
            <?php endif; ?>
            <a href="ad_details.php?id=<?= $ad['AdID'] ?>" class="ad-card-img-wrapper d-block">
              <img src="<?= $ad['Thumb'] ? e(UPLOAD_URL . $ad['Thumb']) : 'https://placehold.co/300x180?text=No+Image' ?>" alt="<?= e($ad['Title']) ?>">
            </a>
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-light text-indigo border"><?= e($ad['Condition']) ?></span>
                <span class="small text-muted"><i class="bi bi-clock me-1"></i><?= timeAgo($ad['PostedDate']) ?></span>
              </div>
              <h6 class="card-title fw-bold mb-2">
                <a href="ad_details.php?id=<?= $ad['AdID'] ?>" class="text-decoration-none text-dark hover-indigo"><?= e($ad['Title']) ?></a>
              </h6>
              <div class="ad-price mb-2"><?= formatPrice($ad['Price']) ?></div>
              <div class="small text-muted"><i class="bi bi-geo-alt me-1"></i><?= e($ad['City']) ?>, <?= e($ad['State']) ?></div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <nav class="mt-5">
      <ul class="pagination justify-content-center gap-2">
        <?php for ($p = 1; $p <= $totalPages; $p++):
          $qs = $_GET; $qs['page'] = $p; ?>
          <li class="page-item <?= $p == $page ? 'active' : '' ?>">
            <a class="page-link rounded-circle border-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" href="?<?= http_build_query($qs) ?>"><?= $p ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<style>
  .pagination .page-item .page-link {
    color: var(--secondary-color);
    background-color: var(--secondary-light);
    font-weight: 600;
    transition: var(--transition-fast);
  }
  .pagination .page-item.active .page-link {
    background-color: var(--primary-color);
    color: white;
  }
  .pagination .page-item:hover .page-link {
    background-color: var(--primary-light);
    color: var(--primary-color);
  }
  .hover-indigo:hover {
    color: var(--primary-color) !important;
  }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
