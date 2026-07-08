<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/admin_header.php';
require_once __DIR__ . '/../includes/functions.php';
expireOldAds($pdo);

$stats = [];
$stats['total_users']  = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE Role = 'user'")->fetchColumn();
$stats['active_users'] = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE Role = 'user' AND Status = 'active'")->fetchColumn();
$stats['total_ads']    = (int)$pdo->query("SELECT COUNT(*) FROM advertisements")->fetchColumn();
$stats['pending_ads']  = (int)$pdo->query("SELECT COUNT(*) FROM advertisements WHERE Status = 'Pending Approval'")->fetchColumn();
$stats['active_ads']   = (int)$pdo->query("SELECT COUNT(*) FROM advertisements WHERE Status = 'Active'")->fetchColumn();
$stats['sold_ads']     = (int)$pdo->query("SELECT COUNT(*) FROM advertisements WHERE Status = 'Sold'")->fetchColumn();
$stats['reports']      = (int)$pdo->query("SELECT COUNT(*) FROM reports WHERE ReportStatus = 'Pending'")->fetchColumn();

$recentAds = $pdo->query("SELECT a.*, u.Name AS SellerName FROM advertisements a JOIN users u ON u.UserID = a.UserID ORDER BY a.PostedDate DESC LIMIT 8")->fetchAll();
?>

<div class="row g-3 mb-4">
  <div class="col-md-3 col-sm-6">
    <div class="card stat-card shadow-sm"><div class="card-body">
      <div class="text-muted small">Total Users</div>
      <div class="fs-3 fw-bold"><?= $stats['total_users'] ?></div>
    </div></div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="card stat-card shadow-sm"><div class="card-body">
      <div class="text-muted small">Active Users</div>
      <div class="fs-3 fw-bold"><?= $stats['active_users'] ?></div>
    </div></div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="card stat-card shadow-sm"><div class="card-body">
      <div class="text-muted small">Total Ads</div>
      <div class="fs-3 fw-bold"><?= $stats['total_ads'] ?></div>
    </div></div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="card stat-card shadow-sm border-warning"><div class="card-body">
      <div class="text-muted small">Pending Ads</div>
      <div class="fs-3 fw-bold text-warning"><?= $stats['pending_ads'] ?></div>
      <a href="ads.php?status=Pending+Approval" class="small">Review now &rarr;</a>
    </div></div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="card stat-card shadow-sm"><div class="card-body">
      <div class="text-muted small">Active Ads</div>
      <div class="fs-3 fw-bold"><?= $stats['active_ads'] ?></div>
    </div></div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="card stat-card shadow-sm"><div class="card-body">
      <div class="text-muted small">Sold Ads</div>
      <div class="fs-3 fw-bold"><?= $stats['sold_ads'] ?></div>
    </div></div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="card stat-card shadow-sm border-danger"><div class="card-body">
      <div class="text-muted small">Pending Reports</div>
      <div class="fs-3 fw-bold text-danger"><?= $stats['reports'] ?></div>
      <a href="reports.php" class="small">View reports &rarr;</a>
    </div></div>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <h6 class="mb-3">Recently Posted Ads</h6>
    <table class="table table-sm align-middle">
      <thead><tr><th>Title</th><th>Seller</th><th>Price</th><th>Status</th><th>Posted</th></tr></thead>
      <tbody>
        <?php foreach ($recentAds as $ad): ?>
        <tr>
          <td><a href="../ad_details.php?id=<?= $ad['AdID'] ?>"><?= e($ad['Title']) ?></a></td>
          <td><?= e($ad['SellerName']) ?></td>
          <td><?= formatPrice($ad['Price']) ?></td>
          <td><?= adStatusBadge($ad['Status']) ?></td>
          <td class="small text-muted"><?= timeAgo($ad['PostedDate']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
