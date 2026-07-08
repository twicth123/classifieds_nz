<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$user = currentUser($pdo);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!password_verify($current, $user['PasswordHash'])) $errors[] = 'Current password is incorrect.';
    if (strlen($new) < 6) $errors[] = 'New password must be at least 6 characters.';
    if ($new !== $confirm) $errors[] = 'New passwords do not match.';

    if (!$errors) {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE users SET PasswordHash=? WHERE UserID=?')->execute([$hash, $user['UserID']]);
        setFlash('success', 'Password changed successfully.');
        redirect('/profile.php');
    }
}

$pageTitle = 'Change Password';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-5">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h3 class="mb-3">Change Password</h3>

        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
          </div>
        <?php endif; ?>

        <form method="post">
          <?= csrfField() ?>
          <div class="mb-3">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
          </div>
          <button class="btn btn-primary w-100" type="submit">Update Password</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
