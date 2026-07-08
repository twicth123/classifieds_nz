<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$adId = (int)($_GET['id'] ?? $_POST['ad_id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM advertisements WHERE AdID = ?');
$stmt->execute([$adId]);
$ad = $stmt->fetch();

if (!$ad || $ad['UserID'] != currentUserId()) {
    setFlash('danger', 'Advertisement not found or you do not have permission to edit it.');
    redirect('/dashboard.php');
}

$errors = [];
$parentCategories = getParentCategories($pdo);

// Determine current parent/sub category for pre-filling dropdowns
$catRow = $pdo->prepare('SELECT * FROM categories WHERE CategoryID = ?');
$catRow->execute([$ad['CategoryID']]);
$catRow = $catRow->fetch();
$currentParent = $catRow['ParentCategoryID'] ?: $catRow['CategoryID'];
$currentSub = $catRow['ParentCategoryID'] ? $catRow['CategoryID'] : '';

$imgStmt = $pdo->prepare('SELECT * FROM advertisement_images WHERE AdID = ? ORDER BY SequenceNo');
$imgStmt->execute([$adId]);
$images = $imgStmt->fetchAll();

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
    $finalCategoryId = $subCatId !== '' ? $subCatId : $categoryId;
    $removeImages = $_POST['remove_images'] ?? [];

    if ($title === '') $errors[] = 'Title is required.';
    if ($description === '') $errors[] = 'Description is required.';
    if (!is_numeric($price) || $price < 0) $errors[] = 'A valid price is required.';
    if (!in_array($condition, ['New', 'Used'])) $errors[] = 'Please select a condition.';
    if ($finalCategoryId === '') $errors[] = 'Please select a category.';
    if ($city === '') $errors[] = 'City is required.';
    if ($state === '') $errors[] = 'State is required.';

    $remainingCount = count($images) - count($removeImages);
    $newFilesCount = !empty($_FILES['images']['name'][0]) ? count(array_filter($_FILES['images']['name'])) : 0;

    if (($remainingCount + $newFilesCount) === 0) {
        $errors[] = 'At least one image is required.';
    }
    if (($remainingCount + $newFilesCount) > MAX_IMAGES_PER_AD) {
        $errors[] = 'You can have a maximum of ' . MAX_IMAGES_PER_AD . ' images.';
    }

    $uploadedFiles = [];
    if (!$errors && $newFilesCount > 0) {
        for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
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

    if (!$errors) {
        // Re-submitted ads that were rejected go back to pending approval
        $newStatus = $ad['Status'] === 'Rejected' ? 'Pending Approval' : $ad['Status'];

        $pdo->prepare('UPDATE advertisements SET Title=?, Description=?, Price=?, `Condition`=?, CategoryID=?, City=?, State=?, Status=?, RejectReason=NULL WHERE AdID=?')
            ->execute([$title, $description, $price, $condition, $finalCategoryId, $city, $state, $newStatus, $adId]);

        foreach ($removeImages as $imgId) {
            $imgId = (int)$imgId;
            $row = $pdo->prepare('SELECT ImagePath FROM advertisement_images WHERE ImageID = ? AND AdID = ?');
            $row->execute([$imgId, $adId]);
            $r = $row->fetch();
            if ($r) {
                @unlink(UPLOAD_DIR . $r['ImagePath']);
                $pdo->prepare('DELETE FROM advertisement_images WHERE ImageID = ?')->execute([$imgId]);
            }
        }

        $maxSeq = $pdo->prepare('SELECT COALESCE(MAX(SequenceNo), -1) FROM advertisement_images WHERE AdID = ?');
        $maxSeq->execute([$adId]);
        $seq = (int)$maxSeq->fetchColumn() + 1;
        foreach ($uploadedFiles as $path) {
            $pdo->prepare('INSERT INTO advertisement_images (AdID, ImagePath, SequenceNo) VALUES (?,?,?)')
                ->execute([$adId, $path, $seq++]);
        }

        setFlash('success', 'Advertisement updated successfully.');
        redirect('/dashboard.php');
    }
}

$pageTitle = 'Edit Advertisement';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h3 class="mb-3">Edit Advertisement</h3>

        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
          </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" novalidate>
          <?= csrfField() ?>
          <input type="hidden" name="ad_id" value="<?= $ad['AdID'] ?>">

          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="<?= e($_POST['title'] ?? $ad['Title']) ?>" maxlength="150" required>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Category</label>
              <select name="category" id="categorySelect" class="form-select" required>
                <option value="">Select category</option>
                <?php foreach ($parentCategories as $cat): ?>
                  <option value="<?= $cat['CategoryID'] ?>" <?= (string)$currentParent === (string)$cat['CategoryID'] ? 'selected' : '' ?>>
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
            <textarea name="description" id="description" class="form-control" rows="5" required><?= e($_POST['description'] ?? $ad['Description']) ?></textarea>
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Price (₹)</label>
              <input type="number" step="0.01" min="0" name="price" class="form-control" value="<?= e($_POST['price'] ?? $ad['Price']) ?>" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Condition</label>
              <select name="condition" class="form-select" required>
                <option value="New" <?= ($_POST['condition'] ?? $ad['Condition']) === 'New' ? 'selected' : '' ?>>New</option>
                <option value="Used" <?= ($_POST['condition'] ?? $ad['Condition']) === 'Used' ? 'selected' : '' ?>>Used</option>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">City</label>
              <input type="text" name="city" class="form-control" value="<?= e($_POST['city'] ?? $ad['City']) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">State</label>
              <input type="text" name="state" class="form-control" value="<?= e($_POST['state'] ?? $ad['State']) ?>" required>
            </div>
          </div>

          <?php if ($images): ?>
          <div class="mb-3">
            <label class="form-label">Current Images (check to remove)</label>
            <div class="d-flex gap-3 flex-wrap">
              <?php foreach ($images as $img): ?>
                <div class="text-center">
                  <img src="<?= e(UPLOAD_URL . $img['ImagePath']) ?>" width="90" height="70" style="object-fit:cover;border-radius:6px;">
                  <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="remove_images[]" value="<?= $img['ImageID'] ?>" id="rm<?= $img['ImageID'] ?>">
                    <label class="form-check-label small text-danger" for="rm<?= $img['ImageID'] ?>">Remove</label>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-label">Add More Images</label>
            <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
          </div>

          <button type="submit" class="btn btn-primary">Save Changes</button>
          <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
const categorySelect = document.getElementById('categorySelect');
const subcategorySelect = document.getElementById('subcategorySelect');
const preselectedSub = <?= json_encode((string)$currentSub) ?>;

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
