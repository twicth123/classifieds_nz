<?php
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$resetLink = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $email = trim($_POST['email'] ?? '');

    $stmt = $pdo->prepare('SELECT * FROM users WHERE Email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $errors[] = 'No account found with that email address.';
    } else {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $pdo->prepare('UPDATE users SET ResetToken = ?, ResetTokenExpiry = ? WHERE UserID = ?')
            ->execute([$token, $expiry, $user['UserID']]);

        // NOTE: In production this link should be emailed to the user rather than displayed.
        $resetLink = BASE_URL . '/reset_password.php?token=' . $token;
    }
}

$pageTitle = 'Forgot Password';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-5">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h3 class="mb-3">Forgot Password</h3>
        <p class="text-muted">Enter your registered email and we'll help you reset your password.</p>

        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
          </div>
        <?php endif; ?>

        <?php if ($resetLink): ?>
          <div class="alert alert-success">
            A password reset link has been generated. In this MVP (no email service configured) here is your link:<br>
            <a href="<?= e($resetLink) ?>"><?= e($resetLink) ?></a>
          </div>
        <?php else: ?>
        <form method="post" novalidate>
          <?= csrfField() ?>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <button class="btn btn-primary w-100" type="submit">Send Reset Link</button>
        </form>
        <?php endif; ?>
        <p class="text-center mt-3 mb-0"><a href="login.php">Back to login</a></p>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
