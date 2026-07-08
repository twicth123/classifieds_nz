<?php
require_once __DIR__ . '/includes/functions.php';

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$errors = [];
$success = false;

if (!$token) {
    setFlash('danger', 'Invalid password reset link.');
    redirect('/forgot_password.php');
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE ResetToken = ? AND ResetTokenExpiry > NOW()');
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('danger', 'This password reset link is invalid or has expired.');
    redirect('/forgot_password.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE users SET PasswordHash = ?, ResetToken = NULL, ResetTokenExpiry = NULL WHERE UserID = ?')
            ->execute([$hash, $user['UserID']]);
        $success = true;
    }
}

$pageTitle = 'Reset Password';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-5">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h3 class="mb-3">Reset Password</h3>

        <?php if ($success): ?>
          <div class="alert alert-success">Your password has been reset. You can now <a href="login.php">login</a>.</div>
        <?php else: ?>
          <?php if ($errors): ?>
            <div class="alert alert-danger">
              <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
          <?php endif; ?>
          <form method="post" novalidate>
            <?= csrfField() ?>
            <input type="hidden" name="token" value="<?= e($token) ?>">
            <div class="mb-3">
              <label class="form-label">New Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Confirm New Password</label>
              <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100" type="submit">Reset Password</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
