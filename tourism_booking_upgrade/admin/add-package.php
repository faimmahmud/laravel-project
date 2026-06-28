<?php
require_once __DIR__ . '/../includes/functions.php';
ensure_storage();
if (!is_admin()) {
    flash_set('danger', 'Admin access required.');
    redirect(app_path('login.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf(app_path('admin/add-package.php'));
    $packages = read_packages();
    $id = uniqid('pkg_', true);
    $title = trim($_POST['title'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $rating = trim($_POST['rating'] ?? '5.0');
    $days = trim($_POST['days'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? 'city');
    $details = array_values(array_filter(array_map('trim', explode("\n", trim($_POST['details'] ?? '')))));

    $image = trim($_POST['image'] ?? '');
    $uploaded = handle_image_upload('image_file', '');
    if ($uploaded !== '') {
        $image = $uploaded;
    }

    if ($title !== '' && $image !== '') {
        $packages[] = compact('id', 'title', 'country', 'price', 'rating', 'days', 'image', 'description', 'category');
        $packages[count($packages)-1]['details'] = $details;
        write_packages($packages);
        flash_set('success', 'Package added successfully.');
        redirect(app_path('admin/index.php'));
    } else {
        flash_set('danger', 'Title and image are required.');
    }
}

$pageTitle = 'Add Package | ' . $site['brand'];
require_once __DIR__ . '/../includes/header.php';
?>
<section class="arc-section mt-0">
  <div class="container">
    <div class="form-shell p-4 p-lg-5 reveal">
      <div class="section-kicker">Admin</div>
      <h1 class="section-title mb-3">Add tour package</h1>
      <form method="post" enctype="multipart/form-data" class="row g-3">
        <?= csrf_field() ?>
        <div class="col-md-6"><input class="form-control" name="title" placeholder="Title" required></div>
        <div class="col-md-6"><input class="form-control" name="country" placeholder="Country"></div>
        <div class="col-md-4"><input class="form-control" name="price" placeholder="Price" required></div>
        <div class="col-md-4"><input class="form-control" name="rating" placeholder="Rating" value="5.0"></div>
        <div class="col-md-4"><input class="form-control" name="days" placeholder="Days" value="7 Days"></div>
        <div class="col-12"><textarea class="form-control" name="description" rows="4" placeholder="Description"></textarea></div>
        <div class="col-md-6">
          <label class="form-label">Image URL</label>
          <input class="form-control" name="image" placeholder="https://...">
        </div>
        <div class="col-md-6">
          <label class="form-label">Or upload image</label>
          <input class="form-control" type="file" name="image_file" accept="image/*">
        </div>
        <div class="col-md-6"><input class="form-control" name="category" placeholder="Category" value="city"></div>
        <div class="col-12"><textarea class="form-control" name="details" rows="4" placeholder="One feature per line"></textarea></div>
        <div class="col-12">
          <button class="btn btn-gold px-4" type="submit">Save package</button>
          <a class="btn btn-outline-dark px-4" href="<?= e(app_path('admin/index.php')) ?>">Back</a>
        </div>
      </form>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
