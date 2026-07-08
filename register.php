<?php
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) redirect('/dashboard.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $mobile  = trim($_POST['mobile'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $state   = trim($_POST['state'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    $_SESSION['old_input'] = compact('name', 'email', 'mobile', 'city', 'state');

    if ($name === '') $errors[] = 'Name is required.';
    if (!isValidEmail($email)) $errors[] = 'A valid email is required.';
    if (!isValidMobile($mobile)) $errors[] = 'Mobile number must be 10 digits.';
    if ($city === '') $errors[] = 'City is required.';
    if ($state === '') $errors[] = 'State is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT UserID FROM users WHERE Email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with this email already exists.';
        }
    }

    $photoPath = null;
    if (!$errors && !empty($_FILES['profile_photo']['name'])) {
        $result = handleImageUpload($_FILES['profile_photo'], 'profile');
        if (isset($result['error'])) {
            $errors[] = $result['error'];
        } else {
            $photoPath = $result['path'];
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (Name, Email, Mobile, PasswordHash, City, State, ProfilePhoto) VALUES (?,?,?,?,?,?,?)');
        $stmt->execute([$name, $email, $mobile, $hash, $city, $state, $photoPath]);
        unset($_SESSION['old_input']);
        setFlash('success', 'Registration successful! Please login to continue.');
        redirect('/login.php');
    }
}

$pageTitle = 'Register';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h3 class="mb-3">Create your account</h3>

        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" novalidate>
          <?= csrfField() ?>
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" value="<?= old('name') ?>" required>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" value="<?= old('email') ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Mobile Number</label>
              <input type="text" name="mobile" class="form-control" value="<?= old('mobile') ?>" placeholder="10 digit number" required>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">City</label>
              <input type="text" name="city" class="form-control" value="<?= old('city') ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">State</label>
              <input type="text" name="state" class="form-control" value="<?= old('state') ?>" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Profile Photo (optional)</label>
            <input type="file" name="profile_photo" class="form-control" accept="image/*">
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Confirm Password</label>
              <input type="password" name="confirm_password" class="form-control" required>
            </div>
          </div>
          <button class="btn btn-primary w-100" type="submit">Register</button>
        </form>
        <p class="text-center mt-3 mb-0">Already have an account? <a href="login.php">Login here</a></p>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
