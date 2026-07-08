<?php
$pageTitle = 'User Management';
require_once __DIR__ . '/includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $userId = (int)($_POST['user_id'] ?? 0);

    $target = $pdo->prepare('SELECT * FROM users WHERE UserID = ?');
    $target->execute([$userId]);
    $target = $target->fetch();

    if ($target && $target['Role'] !== 'admin') {
        if ($action === 'suspend') {
            $pdo->prepare("UPDATE users SET Status='suspended' WHERE UserID=?")->execute([$userId]);
            setFlash('success', 'User suspended.');
        } elseif ($action === 'activate') {
            $pdo->prepare("UPDATE users SET Status='active' WHERE UserID=?")->execute([$userId]);
            setFlash('success', 'User activated.');
        } elseif ($action === 'delete') {
            $pdo->prepare('DELETE FROM users WHERE UserID=?')->execute([$userId]);
            setFlash('success', 'User deleted.');
        } elseif ($action === 'reset_password') {
            $newPass = substr(bin2hex(random_bytes(4)), 0, 8);
            $hash = password_hash($newPass, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE users SET PasswordHash=? WHERE UserID=?')->execute([$hash, $userId]);
            setFlash('success', "Password reset. Temporary password for {$target['Email']}: {$newPass}");
        }
    } else {
        setFlash('danger', 'Action not permitted on this account.');
    }
    redirect('/admin/users.php');
}

$search = trim($_GET['search'] ?? '');
$sql = "SELECT * FROM users WHERE Role = 'user'";
$params = [];
if ($search !== '') {
    $sql .= " AND (Name LIKE ? OR Email LIKE ? OR Mobile LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}
$sql .= " ORDER BY CreatedDate DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<form method="get" class="d-flex mb-3" style="max-width:400px;">
  <input type="text" name="search" class="form-control me-2" placeholder="Search by name, email, mobile" value="<?= e($search) ?>">
  <button class="btn btn-primary">Search</button>
</form>

<div class="card shadow-sm">
  <div class="card-body">
    <table class="table align-middle">
      <thead><tr><th>Name</th><th>Email</th><th>Mobile</th><th>City</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><?= e($u['Name']) ?></td>
          <td><?= e($u['Email']) ?></td>
          <td><?= e($u['Mobile']) ?></td>
          <td><?= e($u['City']) ?></td>
          <td><span class="badge bg-<?= $u['Status'] === 'active' ? 'success' : 'danger' ?>"><?= e($u['Status']) ?></span></td>
          <td class="small text-muted"><?= formatDate($u['CreatedDate']) ?></td>
          <td>
            <div class="d-flex flex-wrap gap-1">
              <?php if ($u['Status'] === 'active'): ?>
                <form method="post" class="d-inline"><?= csrfField() ?>
                  <input type="hidden" name="user_id" value="<?= $u['UserID'] ?>">
                  <input type="hidden" name="action" value="suspend">
                  <button class="btn btn-sm btn-outline-warning">Suspend</button>
                </form>
              <?php else: ?>
                <form method="post" class="d-inline"><?= csrfField() ?>
                  <input type="hidden" name="user_id" value="<?= $u['UserID'] ?>">
                  <input type="hidden" name="action" value="activate">
                  <button class="btn btn-sm btn-outline-success">Activate</button>
                </form>
              <?php endif; ?>
              <form method="post" class="d-inline" onsubmit="return confirm('Reset password for this user?');"><?= csrfField() ?>
                <input type="hidden" name="user_id" value="<?= $u['UserID'] ?>">
                <input type="hidden" name="action" value="reset_password">
                <button class="btn btn-sm btn-outline-secondary">Reset Password</button>
              </form>
              <form method="post" class="d-inline" onsubmit="return confirm('Permanently delete this user and all their ads?');"><?= csrfField() ?>
                <input type="hidden" name="user_id" value="<?= $u['UserID'] ?>">
                <input type="hidden" name="action" value="delete">
                <button class="btn btn-sm btn-outline-danger">Delete</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$users): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No users found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
