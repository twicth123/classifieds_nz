<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$user = currentUser($pdo);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $name   = trim($_POST['name'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $city   = trim($_POST['city'] ?? '');
    $state  = trim($_POST['state'] ?? '');

    if ($name === '') $errors[] = 'Name is required.';
    if (!isValidMobile($mobile)) $errors[] = 'Mobile number must be 10 digits.';
    if ($city === '') $errors[] = 'City is required.';
    if ($state === '') $errors[] = 'State is required.';

    $photoPath = $user['ProfilePhoto'];
    if (!$errors && !empty($_FILES['profile_photo']['name'])) {
        $result = handleImageUpload($_FILES['profile_photo'], 'profile');
        if (isset($result['error'])) {
            $errors[] = $result['error'];
        } else {
            $photoPath = $result['path'];
        }
    }

    if (!$errors) {
        $pdo->prepare('UPDATE users SET Name=?, Mobile=?, City=?, State=?, ProfilePhoto=? WHERE UserID=?')
            ->execute([$name, $mobile, $city, $state, $photoPath, $user['UserID']]);
        $_SESSION['name'] = $name;
        setFlash('success', 'Profile updated successfully.');
        redirect('/profile.php');
    }
}

$pageTitle = 'My Profile';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h3 class="mb-3">My Profile</h3>

        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
          </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
          <?= csrfField() ?>
          <div class="mb-3 text-center">
            <img src="<?= $user['ProfilePhoto'] ? e(UPLOAD_URL . $user['ProfilePhoto']) : 'https://ui-avatars.com/api/?name=' . urlencode($user['Name']) ?>"
                 class="rounded-circle" width="100" height="100" style="object-fit:cover;">
          </div>
          <div class="mb-3">
            <label class="form-label">Change Photo</label>
            <input type="file" name="profile_photo" class="form-control" accept="image/*">
          </div>
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" value="<?= e($user['Name']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" value="<?= e($user['Email']) ?>" disabled>
            <div class="form-text">Email cannot be changed.</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Mobile Number</label>
            <input type="text" name="mobile" class="form-control" value="<?= e($user['Mobile']) ?>" required>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">City</label>
              <input type="text" name="city" class="form-control" value="<?= e($user['City']) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">State</label>
              <input type="text" name="state" class="form-control" value="<?= e($user['State']) ?>" required>
            </div>
          </div>
          <button class="btn btn-primary" type="submit">Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
