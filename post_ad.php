<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$errors = [];
$parentCategories = getParentCategories($pdo);
$user = currentUser($pdo);

$title       = '';
$description = '';
$price       = '';
$condition   = 'Used';
$categoryId  = '';
$subCatId    = '';
$city        = $user['City'];
$state       = $user['State'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = $_POST['price'] ?? '';
    $condition   = $_POST['condition'] ?? '';
    $categoryId  = $_POST['category'] ?? '';
    $subCatId    = $_POST['subcategory'] ?? '';
    $city        = trim($_POST['city'] ?? '');
    $state       = trim($_POST['state'] ?? '');
    $saveAsDraft = isset($_POST['save_as_draft']);

    $finalCategoryId = $subCatId !== '' ? $subCatId : $categoryId;

    if ($title === '') $errors[] = 'Title is required.';
    if ($description === '') $errors[] = 'Description is required.';
    if (!is_numeric($price) || $price < 0) $errors[] = 'A valid price is required.';
    if (!in_array($condition, ['New', 'Used'])) $errors[] = 'Please select a condition.';
    if ($finalCategoryId === '') $errors[] = 'Please select a category.';
    if ($city === '') $errors[] = 'City is required.';
    if ($state === '') $errors[] = 'State is required.';

    $uploadedFiles = [];
    if (!empty($_FILES['images']['name'][0])) {
        $count = count($_FILES['images']['name']);
        if ($count > MAX_IMAGES_PER_AD) {
            $errors[] = 'You can upload a maximum of ' . MAX_IMAGES_PER_AD . ' images.';
        } else {
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                $file = [
                    'name' => $_FILES['images']['name'][$i],
                    'type' => $_FILES['images']['type'][$i],
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'size' => $_FILES['images']['size'][$i],
                ];
                $result = handleImageUpload($file, 'ad');
                if (isset($result['error'])) {
                    $errors[] = $result['error'];
                    break;
                }
                $uploadedFiles[] = $result['path'];
            }
        }
    } elseif (!$saveAsDraft) {
        $errors[] = 'Please upload at least one image.';
    }

    if (!$errors) {
        $status = $saveAsDraft ? 'Draft' : 'Pending Approval';
        $expiryDate = $saveAsDraft ? null : date('Y-m-d H:i:s', strtotime('+' . AD_EXPIRY_DAYS . ' days'));

        $pdo->beginTransaction();
        $stmt = $pdo->prepare('INSERT INTO advertisements
            (UserID, CategoryID, Title, Description, Price, `Condition`, City, State, Status, ExpiryDate)
            VALUES (?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([currentUserId(), $finalCategoryId, $title, $description, $price, $condition, $city, $state, $status, $expiryDate]);
        $newAdId = $pdo->lastInsertId();

        $seq = 0;
        foreach ($uploadedFiles as $path) {
            $pdo->prepare('INSERT INTO advertisement_images (AdID, ImagePath, SequenceNo) VALUES (?,?,?)')
                ->execute([$newAdId, $path, $seq++]);
        }
        $pdo->commit();

        setFlash('success', $saveAsDraft
            ? 'Ad saved as draft. You can publish it later from your dashboard.'
            : 'Your ad has been submitted and is pending admin approval.');
        redirect('/dashboard.php');
    }
}

$pageTitle = 'Post an Advertisement';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h3 class="mb-3">Post a New Advertisement</h3>

        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
          </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" novalidate>
          <?= csrfField() ?>

          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="<?= e($title) ?>" maxlength="150" required>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Category</label>
              <select name="category" id="categorySelect" class="form-select" required>
                <option value="">Select category</option>
                <?php foreach ($parentCategories as $cat): ?>
                  <option value="<?= $cat['CategoryID'] ?>" <?= (string)$categoryId === (string)$cat['CategoryID'] ? 'selected' : '' ?>>
                    <?= e($cat['Name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Subcategory</label>
              <select name="subcategory" id="subcategorySelect" class="form-select">
                <option value="">Select subcategory</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" rows="5" required><?= e($description) ?></textarea>
            <div id="descCounter" class="form-text"></div>
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Price (₹)</label>
              <input type="number" step="0.01" min="0" name="price" class="form-control" value="<?= e($price) ?>" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Condition</label>
              <select name="condition" class="form-select" required>
                <option value="New" <?= $condition === 'New' ? 'selected' : '' ?>>New</option>
                <option value="Used" <?= $condition === 'Used' ? 'selected' : '' ?>>Used</option>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">City</label>
              <input type="text" name="city" class="form-control" value="<?= e($city) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">State</label>
              <input type="text" name="state" class="form-control" value="<?= e($state) ?>" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Images (up to <?= MAX_IMAGES_PER_AD ?>)</label>
            <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
            <div class="form-text">JPG, PNG, WEBP or GIF. Max 5MB each.</div>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Submit for Approval</button>
            <button type="submit" name="save_as_draft" value="1" class="btn btn-outline-secondary">Save as Draft</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
const subcategoriesCache = {};
const categorySelect = document.getElementById('categorySelect');
const subcategorySelect = document.getElementById('subcategorySelect');
const preselectedSub = <?= json_encode($subCatId ?: '') ?>;

function loadSubcategories(categoryId, preselect) {
  subcategorySelect.innerHTML = '<option value="">Select subcategory</option>';
  if (!categoryId) return;
  fetch('get_subcategories.php?category_id=' + categoryId)
    .then(r => r.json())
    .then(subs => {
      subs.forEach(sub => {
        const opt = document.createElement('option');
        opt.value = sub.CategoryID;
        opt.textContent = sub.Name;
        if (String(sub.CategoryID) === String(preselect)) opt.selected = true;
        subcategorySelect.appendChild(opt);
      });
    });
}

categorySelect.addEventListener('change', () => loadSubcategories(categorySelect.value, ''));
if (categorySelect.value) loadSubcategories(categorySelect.value, preselectedSub);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
