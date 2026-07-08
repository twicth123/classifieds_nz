<?php
$pageTitle = 'Advertisement Management';
require_once __DIR__ . '/includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $adId   = (int)($_POST['ad_id'] ?? 0);

    if ($action === 'approve') {
        $expiry = date('Y-m-d H:i:s', strtotime('+' . AD_EXPIRY_DAYS . ' days'));
        $pdo->prepare("UPDATE advertisements SET Status='Active', ExpiryDate=?, RejectReason=NULL WHERE AdID=?")->execute([$expiry, $adId]);
        setFlash('success', 'Advertisement approved and published.');
    } elseif ($action === 'reject') {
        $reason = trim($_POST['reject_reason'] ?? 'Does not meet posting guidelines');
        $pdo->prepare("UPDATE advertisements SET Status='Rejected', RejectReason=? WHERE AdID=?")->execute([$reason, $adId]);
        setFlash('success', 'Advertisement rejected.');
    } elseif ($action === 'delete') {
        $imgStmt = $pdo->prepare('SELECT ImagePath FROM advertisement_images WHERE AdID = ?');
        $imgStmt->execute([$adId]);
        foreach ($imgStmt->fetchAll() as $img) @unlink(UPLOAD_DIR . $img['ImagePath']);
        $pdo->prepare('DELETE FROM advertisements WHERE AdID=?')->execute([$adId]);
        setFlash('success', 'Advertisement deleted.');
    }
    redirect('/admin/ads.php' . (!empty($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
}

$status = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');
$sql = "SELECT a.*, u.Name AS SellerName, u.Email AS SellerEmail FROM advertisements a JOIN users u ON u.UserID = a.UserID WHERE 1=1";
$params = [];
if ($status !== '') {
    $sql .= " AND a.Status = ?";
    $params[] = $status;
}
if ($search !== '') {
    $sql .= " AND (a.Title LIKE ? OR u.Name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql .= " ORDER BY a.PostedDate DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ads = $stmt->fetchAll();

$statuses = ['', 'Draft', 'Pending Approval', 'Active', 'Rejected', 'Sold', 'Expired'];
?>

<div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
  <form method="get" class="d-flex gap-2">
    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width:auto">
      <?php foreach ($statuses as $s): ?>
        <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= $s === '' ? 'All Statuses' : e($s) ?></option>
      <?php endforeach; ?>
    </select>
    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search title or seller" value="<?= e($search) ?>">
    <button class="btn btn-sm btn-primary">Filter</button>
  </form>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <table class="table align-middle">
      <thead><tr><th>Title</th><th>Seller</th><th>Category</th><th>Price</th><th>Status</th><th>Posted</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($ads as $ad): ?>
        <tr>
          <td><a href="../ad_details.php?id=<?= $ad['AdID'] ?>" target="_blank"><?= e($ad['Title']) ?></a></td>
          <td><?= e($ad['SellerName']) ?><br><span class="small text-muted"><?= e($ad['SellerEmail']) ?></span></td>
          <td><?= e(getCategoryName($pdo, $ad['CategoryID'])) ?></td>
          <td><?= formatPrice($ad['Price']) ?></td>
          <td><?= adStatusBadge($ad['Status']) ?>
            <?php if ($ad['Status'] === 'Rejected' && $ad['RejectReason']): ?>
              <div class="small text-danger"><?= e($ad['RejectReason']) ?></div>
            <?php endif; ?>
          </td>
          <td class="small text-muted"><?= formatDate($ad['PostedDate']) ?></td>
          <td>
            <div class="d-flex flex-wrap gap-1">
              <?php if (in_array($ad['Status'], ['Pending Approval', 'Rejected'])): ?>
                <form method="post" class="d-inline"><?= csrfField() ?>
                  <input type="hidden" name="ad_id" value="<?= $ad['AdID'] ?>">
                  <input type="hidden" name="action" value="approve">
                  <button class="btn btn-sm btn-outline-success">Approve</button>
                </form>
              <?php endif; ?>
              <?php if ($ad['Status'] !== 'Rejected'): ?>
                <button class="btn btn-sm btn-outline-warning" data-bs-toggle="collapse" data-bs-target="#reject<?= $ad['AdID'] ?>">Reject</button>
              <?php endif; ?>
              <form method="post" class="d-inline" onsubmit="return confirm('Permanently delete this ad?');"><?= csrfField() ?>
                <input type="hidden" name="ad_id" value="<?= $ad['AdID'] ?>">
                <input type="hidden" name="action" value="delete">
                <button class="btn btn-sm btn-outline-danger">Delete</button>
              </form>
            </div>
            <div class="collapse mt-2" id="reject<?= $ad['AdID'] ?>">
              <form method="post" class="d-flex gap-1">
                <?= csrfField() ?>
                <input type="hidden" name="ad_id" value="<?= $ad['AdID'] ?>">
                <input type="hidden" name="action" value="reject">
                <input type="text" name="reject_reason" class="form-control form-control-sm" placeholder="Reason for rejection" required>
                <button class="btn btn-sm btn-warning">Confirm</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$ads): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No advertisements found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
