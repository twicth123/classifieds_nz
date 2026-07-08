<?php
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) redirect('/dashboard.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE Email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['PasswordHash'])) {
        $errors[] = 'Invalid email or password.';
    } elseif ($user['Status'] === 'suspended') {
        $errors[] = 'Your account has been suspended. Please contact support.';
    } else {
        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['role']    = $user['Role'];
        $_SESSION['name']    = $user['Name'];
        setFlash('success', 'Welcome back, ' . $user['Name'] . '!');
        redirect($user['Role'] === 'admin' ? '/admin/index.php' : '/dashboard.php');
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-5">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h3 class="mb-3">Login</h3>

        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
          </div>
        <?php endif; ?>

        <form method="post" novalidate>
          <?= csrfField() ?>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <div class="d-flex justify-content-between mb-3">
            <a href="forgot_password.php" class="small">Forgot password?</a>
          </div>
          <button class="btn btn-primary w-100" type="submit">Login</button>
        </form>
        <p class="text-center mt-3 mb-0">New here? <a href="register.php">Create an account</a></p>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
