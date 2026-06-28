<?php
require_once __DIR__ . '/functions.php';
ensure_storage();

$pageTitle = $pageTitle ?? $site['brand'];
$currentUser = current_user();
$flash = flash_get();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="Premium tourism website with full-screen luxury design and smooth animations.">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(asset('assets/css/style.css')) ?>">
</head>
<body>
<div class="cursor" aria-hidden="true"></div>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top luxury-nav">
  <div class="container-fluid px-4 px-lg-5">
    <a class="navbar-brand fw-bold text-uppercase letter-wide" href="<?= e(app_path('index.php')) ?>"><?= e($site['brand']) ?></a>
    <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#topNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="topNav">
      <ul class="navbar-nav mx-auto gap-lg-2">
        <li class="nav-item"><a class="nav-link" href="<?= e(app_path('index.php')) ?>">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(app_path('destinations.php')) ?>">Destinations</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(app_path('packages.php')) ?>">Tour Packages</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(app_path('world.php')) ?>">World Explorer</a></li>
        <li class="nav-item">
          <a class="nav-link" href="<?= e($currentUser ? app_path('booking.php') : app_path('login.php?redirect=' . rawurlencode(app_path('booking.php')))) ?>">Booking</a>
        </li>
        <?php if ($currentUser && ($currentUser['role'] ?? 'user') === 'admin'): ?>
          <li class="nav-item"><a class="nav-link" href="<?= e(app_path('admin/index.php')) ?>">Admin</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= e(app_path('admin/bookings.php')) ?>">Bookings</a></li>
        <?php endif; ?>
      </ul>
      <div class="d-flex gap-2 align-items-center">
        <?php if ($currentUser): ?>
          <div class="nav-user">
            <span class="small text-white-50">Welcome</span>
            <strong class="d-block"><?= e($currentUser['name']) ?></strong>
          </div>
          <a class="btn btn-outline-light btn-sm rounded-pill px-3" href="<?= e(app_path('logout.php')) ?>">Logout</a>
        <?php else: ?>
          <a class="btn btn-outline-light btn-sm rounded-pill px-3" href="<?= e(app_path('login.php')) ?>">Login</a>
          <a class="btn btn-gold btn-sm rounded-pill px-3" href="<?= e(app_path('register.php')) ?>">Register</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<?php if ($flash): ?>
<div class="container pt-3">
  <div class="alert alert-<?= e($flash['type']) ?> shadow-sm rounded-4 mb-0"><?= e($flash['message']) ?></div>
</div>
<?php endif; ?>

<main>
