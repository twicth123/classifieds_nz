<?php
$pageTitle = 'Reports';
require_once __DIR__ . '/includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $reportId = (int)($_POST['report_id'] ?? 0);

    if ($action === 'dismiss') {
        $pdo->prepare("UPDATE reports SET ReportStatus='Dismissed' WHERE ReportID=?")->execute([$reportId]);
        setFlash('success', 'Report dismissed.');
    } elseif ($action === 'reviewed') {
        $pdo->prepare("UPDATE reports SET ReportStatus='Reviewed' WHERE ReportID=?")->execute([$reportId]);
        setFlash('success', 'Report marked as reviewed.');
    } elseif ($action === 'remove_ad') {
        $adId = (int)($_POST['ad_id'] ?? 0);
        $imgStmt = $pdo->prepare('SELECT ImagePath FROM advertisement_images WHERE AdID = ?');
        $imgStmt->execute([$adId]);
        foreach ($imgStmt->fetchAll() as $img) @unlink(UPLOAD_DIR . $img['ImagePath']);
        $pdo->prepare('DELETE FROM advertisements WHERE AdID=?')->execute([$adId]);
        $pdo->prepare("UPDATE reports SET ReportStatus='Reviewed' WHERE ReportID=?")->execute([$reportId]);
        setFlash('success', 'Advertisement removed and report resolved.');
    }
    redirect('/admin/reports.php');
}

$filter = $_GET['status'] ?? 'Pending';
$sql = "SELECT r.*, a.Title AS AdTitle, a.Status AS AdStatus, u.Name AS ReporterName
        FROM reports r
        JOIN advertisements a ON a.AdID = r.AdID
        JOIN users u ON u.UserID = r.UserID";
$params = [];
if ($filter !== '') {
    $sql .= " WHERE r.ReportStatus = ?";
    $params[] = $filter;
}
$sql .= " ORDER BY r.ReportDate DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();
?>

<div class="mb-3">
  <div class="btn-group">
    <a href="?status=Pending" class="btn btn-sm btn-outline-secondary <?= $filter === 'Pending' ? 'active' : '' ?>">Pending</a>
    <a href="?status=Reviewed" class="btn btn-sm btn-outline-secondary <?= $filter === 'Reviewed' ? 'active' : '' ?>">Reviewed</a>
    <a href="?status=Dismissed" class="btn btn-sm btn-outline-secondary <?= $filter === 'Dismissed' ? 'active' : '' ?>">Dismissed</a>
    <a href="?status=" class="btn btn-sm btn-outline-secondary <?= $filter === '' ? 'active' : '' ?>">All</a>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <table class="table align-middle">
      <thead><tr><th>Ad</th><th>Reason</th><th>Comments</th><th>Reported By</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($reports as $r): ?>
        <tr>
          <td><a href="../ad_details.php?id=<?= $r['AdID'] ?>" target="_blank"><?= e($r['AdTitle']) ?></a><br><span class="small text-muted"><?= adStatusBadge($r['AdStatus']) ?></span></td>
          <td><span class="badge bg-secondary"><?= e($r['Reason']) ?></span></td>
          <td class="small"><?= e($r['Comments'] ?: '—') ?></td>
          <td><?= e($r['ReporterName']) ?></td>
          <td class="small text-muted"><?= formatDate($r['ReportDate']) ?></td>
          <td><span class="badge bg-<?= $r['ReportStatus'] === 'Pending' ? 'warning' : ($r['ReportStatus'] === 'Reviewed' ? 'success' : 'secondary') ?>"><?= e($r['ReportStatus']) ?></span></td>
          <td>
            <?php if ($r['ReportStatus'] === 'Pending'): ?>
            <div class="d-flex gap-1">
              <form method="post"><?= csrfField() ?>
                <input type="hidden" name="report_id" value="<?= $r['ReportID'] ?>">
                <input type="hidden" name="action" value="reviewed">
                <button class="btn btn-sm btn-outline-success">Mark Reviewed</button>
              </form>
              <form method="post"><?= csrfField() ?>
                <input type="hidden" name="report_id" value="<?= $r['ReportID'] ?>">
                <input type="hidden" name="action" value="dismiss">
                <button class="btn btn-sm btn-outline-secondary">Dismiss</button>
              </form>
              <form method="post" onsubmit="return confirm('Remove the reported advertisement?');"><?= csrfField() ?>
                <input type="hidden" name="report_id" value="<?= $r['ReportID'] ?>">
                <input type="hidden" name="ad_id" value="<?= $r['AdID'] ?>">
                <input type="hidden" name="action" value="remove_ad">
                <button class="btn btn-sm btn-outline-danger">Remove Ad</button>
              </form>
            </div>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$reports): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No reports found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
