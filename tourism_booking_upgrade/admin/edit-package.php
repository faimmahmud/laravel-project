<?php
require_once __DIR__ . '/../includes/functions.php';
ensure_storage();
if (!is_admin()) {
    flash_set('danger', 'Admin access required.');
    redirect(app_path('login.php'));
}

$packages = read_packages();
$id = $_GET['id'] ?? ($_POST['id'] ?? '');
$idx = null;
foreach ($packages as $i => $pkg) {
    if (($pkg['id'] ?? '') === $id) { $idx = $i; break; }
}
if ($idx === null) {
    flash_set('danger', 'Package not found.');
    redirect(app_path('admin/index.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf(app_path('admin/edit-package.php?id=' . urlencode((string)$id)));
    $pkg = $packages[$idx];
    $pkg['title'] = trim($_POST['title'] ?? $pkg['title']);
    $pkg['country'] = trim($_POST['country'] ?? $pkg['country']);
    $pkg['price'] = trim($_POST['price'] ?? $pkg['price']);
    $pkg['rating'] = trim($_POST['rating'] ?? $pkg['rating']);
    $pkg['days'] = trim($_POST['days'] ?? $pkg['days']);
    $pkg['description'] = trim($_POST['description'] ?? $pkg['description']);
    $pkg['category'] = trim($_POST['category'] ?? $pkg['category']);
    $pkg['details'] = array_values(array_filter(array_map('trim', explode("\n", trim($_POST['details'] ?? implode("\n", $pkg['details'] ?? []))))));
    $image = trim($_POST['image'] ?? $pkg['image']);
    $uploaded = handle_image_upload('image_file', '');
    if ($uploaded !== '') $image = $uploaded;
    if ($image !== '') $pkg['image'] = $image;
    $packages[$idx] = $pkg;
    write_packages($packages);
    flash_set('success', 'Package updated.');
    redirect(app_path('admin/index.php'));
}

$pkg = $packages[$idx];
$pageTitle = 'Edit Package | ' . $site['brand'];
require_once __DIR__ . '/../includes/header.php';
?>
<section class="arc-section mt-0">
  <div class="container">
    <div class="form-shell p-4 p-lg-5 reveal">
      <div class="section-kicker">Admin</div>
      <h1 class="section-title mb-3">Edit tour package</h1>
      <form method="post" enctype="multipart/form-data" class="row g-3">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= e($pkg['id']) ?>">
        <div class="col-md-6"><input class="form-control" name="title" value="<?= e($pkg['title']) ?>" required></div>
        <div class="col-md-6"><input class="form-control" name="country" value="<?= e($pkg['country'] ?? '') ?>"></div>
        <div class="col-md-4"><input class="form-control" name="price" value="<?= e($pkg['price'] ?? '') ?>"></div>
        <div class="col-md-4"><input class="form-control" name="rating" value="<?= e($pkg['rating'] ?? '5.0') ?>"></div>
        <div class="col-md-4"><input class="form-control" name="days" value="<?= e($pkg['days'] ?? '') ?>"></div>
        <div class="col-12"><textarea class="form-control" name="description" rows="4"><?= e($pkg['description'] ?? '') ?></textarea></div>
        <div class="col-md-6">
          <label class="form-label">Image URL</label>
          <input class="form-control" name="image" value="<?= e($pkg['image'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Or upload image</label>
          <input class="form-control" type="file" name="image_file" accept="image/*">
        </div>
        <div class="col-md-6"><input class="form-control" name="category" value="<?= e($pkg['category'] ?? 'city') ?>"></div>
        <div class="col-12"><textarea class="form-control" name="details" rows="4"><?= e(implode("
", $pkg['details'] ?? [])) ?></textarea></div>
        <div class="col-12">
          <button class="btn btn-gold px-4" type="submit">Update package</button>
          <a class="btn btn-outline-dark px-4" href="<?= e(app_path('admin/index.php')) ?>">Back</a>
        </div>
      </form>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
