<?php
require_once __DIR__ . '/../includes/functions.php';

$remote = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$isLocal = in_array($remote, ['127.0.0.1', '::1'], true) || PHP_SAPI === 'cli';
if (!$isLocal) {
    http_response_code(403);
    echo 'Installer is available on localhost only.';
    exit;
}

$fresh = ($_SERVER['REQUEST_METHOD'] === 'POST') && csrf_valid() && isset($_POST['fresh']);

$error = null;
$result = [
    'database' => db_config()['name'],
    'seeded' => ['users' => 0, 'packages' => 0, 'bookings' => 0],
    'counts' => ['users' => 0, 'packages' => 0, 'package_features' => 0, 'bookings' => 0, 'payment_transactions' => 0, 'booking_notifications' => 0, 'booking_audit_logs' => 0],
];

try {
    $result = database_install($fresh);
} catch (Throwable $e) {
    $error = $e->getMessage();
}

$pageTitle = 'Database Setup | ' . $site['brand'];
require_once __DIR__ . '/../includes/header.php';
?>
<section class="arc-section mt-0">
  <div class="container">
    <div class="surface p-4 p-lg-5 rounded-5 reveal show">
      <div class="section-kicker">MySQL setup</div>
      <h1 class="section-title mb-3"><?= $error ? 'Database needs attention' : 'Database is ready' ?></h1>
      <p class="section-lead">
        <?php if ($error): ?>
          <?= e($error) ?>
        <?php else: ?>
          Aurelia Travel is connected to MySQL database <strong><?= e($result['database']) ?></strong>. Existing JSON data is imported automatically when a table is empty.
        <?php endif; ?>
      </p>

      <?php if (!$error): ?>
      <div class="row g-3 mt-2">
        <?php foreach ($result['counts'] as $table => $count): ?>
          <div class="col-md-3">
            <div class="p-3 rounded-4 bg-light border h-100">
              <div class="text-muted small text-uppercase letter-wide"><?= e(str_replace('_', ' ', $table)) ?></div>
              <div class="h2 fw-bold mb-0"><?= (int)$count ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="d-flex flex-wrap gap-2 mt-4">
        <a class="btn btn-gold px-4" href="<?= e(app_path('index.php')) ?>">Open website</a>
        <a class="btn btn-outline-dark px-4" href="<?= e(app_path('admin/index.php')) ?>">Open admin</a>
      </div>
      <?php endif; ?>

      <form method="post" class="mt-4" onsubmit="return confirm('Rebuild database from JSON backup files? Current MySQL changes will be replaced.');">
        <?= csrf_field() ?>
        <input type="hidden" name="fresh" value="1">
        <button class="btn btn-sm btn-outline-danger" type="submit">Rebuild from JSON backups</button>
      </form>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
