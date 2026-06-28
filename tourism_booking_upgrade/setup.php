<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Setup | ' . $site['brand'];
$error = null;
$result = null;

try {
    $result = database_install(false);
} catch (Throwable $e) {
    $error = $e->getMessage();
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="arc-section mt-0">
  <div class="container">
    <div class="surface p-4 p-lg-5 rounded-5 reveal show">
      <div class="section-kicker">One-step setup</div>
      <h1 class="section-title mb-3"><?= $error ? 'Setup needs MySQL running' : 'Setup complete' ?></h1>
      <p class="section-lead mb-0">
        <?php if ($error): ?>
          <?= e($error) ?>
        <?php else: ?>
          The database is ready and the site is connected. You can open the homepage or admin area now.
        <?php endif; ?>
      </p>

      <?php if ($result && !$error): ?>
        <div class="row g-3 mt-3">
          <?php foreach ($result['counts'] as $table => $count): ?>
            <div class="col-md-3">
              <div class="p-3 rounded-4 bg-light border h-100">
                <div class="text-muted small text-uppercase letter-wide"><?= e(str_replace('_', ' ', $table)) ?></div>
                <div class="h2 fw-bold mb-0"><?= (int)$count ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="d-flex flex-wrap gap-2 mt-4">
        <a class="btn btn-gold px-4" href="<?= e(app_path('index.php')) ?>">Open homepage</a>
        <a class="btn btn-outline-dark px-4" href="<?= e(app_path('admin/index.php')) ?>">Open admin</a>
        <a class="btn btn-outline-secondary px-4" href="<?= e(app_path('database/install.php')) ?>">Open installer</a>
      </div>

      <div class="mt-4 small text-muted">
        Demo admin: <strong>admin@demo.com</strong> / <strong>admin123</strong>
      </div>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
