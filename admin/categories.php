<?php
$pageTitle = 'Category Management';
require_once __DIR__ . '/includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $parentId = $_POST['parent_id'] ?: null;
        $order = (int)($_POST['display_order'] ?? 0);
        if ($name !== '') {
            $pdo->prepare('INSERT INTO categories (Name, ParentCategoryID, DisplayOrder) VALUES (?,?,?)')
                ->execute([$name, $parentId, $order]);
            setFlash('success', 'Category created.');
        }
    } elseif ($action === 'update') {
        $id = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $order = (int)($_POST['display_order'] ?? 0);
        if ($name !== '') {
            $pdo->prepare('UPDATE categories SET Name=?, DisplayOrder=? WHERE CategoryID=?')->execute([$name, $order, $id]);
            setFlash('success', 'Category updated.');
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['category_id'] ?? 0);
        $inUse = $pdo->prepare('SELECT COUNT(*) FROM advertisements WHERE CategoryID = ?');
        $inUse->execute([$id]);
        if ((int)$inUse->fetchColumn() > 0) {
            setFlash('danger', 'Cannot delete a category that has advertisements. Reassign or delete those ads first.');
        } else {
            $pdo->prepare('DELETE FROM categories WHERE CategoryID=?')->execute([$id]);
            setFlash('success', 'Category deleted.');
        }
    }
    redirect('/admin/categories.php');
}

$parents = getParentCategories($pdo);
?>

<div class="row">
  <div class="col-lg-5 mb-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6 class="mb-3">Add Category / Subcategory</h6>
        <form method="post">
          <?= csrfField() ?>
          <input type="hidden" name="action" value="create">
          <div class="mb-2">
            <label class="form-label small">Name</label>
            <input type="text" name="name" class="form-control form-control-sm" required>
          </div>
          <div class="mb-2">
            <label class="form-label small">Parent Category (leave blank for top-level)</label>
            <select name="parent_id" class="form-select form-select-sm">
              <option value="">— Top-level category —</option>
              <?php foreach ($parents as $p): ?>
                <option value="<?= $p['CategoryID'] ?>"><?= e($p['Name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small">Display Order</label>
            <input type="number" name="display_order" class="form-control form-control-sm" value="0">
          </div>
          <button class="btn btn-primary btn-sm" type="submit">Add Category</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <?php foreach ($parents as $parent): $subs = getSubCategories($pdo, $parent['CategoryID']); ?>
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <form method="post" class="d-flex gap-2 align-items-center flex-grow-1">
              <?= csrfField() ?>
              <input type="hidden" name="action" value="update">
              <input type="hidden" name="category_id" value="<?= $parent['CategoryID'] ?>">
              <input type="text" name="name" class="form-control form-control-sm" value="<?= e($parent['Name']) ?>" style="max-width:220px;">
              <input type="number" name="display_order" class="form-control form-control-sm" value="<?= $parent['DisplayOrder'] ?>" style="max-width:90px;">
              <button class="btn btn-sm btn-outline-primary">Save</button>
            </form>
            <form method="post" onsubmit="return confirm('Delete this category?');">
              <?= csrfField() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="category_id" value="<?= $parent['CategoryID'] ?>">
              <button class="btn btn-sm btn-outline-danger ms-2">Delete</button>
            </form>
          </div>
          <?php if ($subs): ?>
          <ul class="list-group list-group-flush mt-2">
            <?php foreach ($subs as $sub): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                <form method="post" class="d-flex gap-2 align-items-center flex-grow-1">
                  <?= csrfField() ?>
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="category_id" value="<?= $sub['CategoryID'] ?>">
                  <span class="text-muted">&#8627;</span>
                  <input type="text" name="name" class="form-control form-control-sm" value="<?= e($sub['Name']) ?>" style="max-width:200px;">
                  <input type="number" name="display_order" class="form-control form-control-sm" value="<?= $sub['DisplayOrder'] ?>" style="max-width:80px;">
                  <button class="btn btn-sm btn-outline-primary">Save</button>
                </form>
                <form method="post" onsubmit="return confirm('Delete this subcategory?');">
                  <?= csrfField() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="category_id" value="<?= $sub['CategoryID'] ?>">
                  <button class="btn btn-sm btn-outline-danger ms-2">Delete</button>
                </form>
              </li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
