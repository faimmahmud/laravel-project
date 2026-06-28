<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Tour Packages | ' . $site['brand'];
require_once __DIR__ . '/includes/header.php';

$packageData = read_packages();
if (!$packageData) {
    $packageData = $featuredTours;
}

$filterSet = ['all' => 'All', 'beach' => 'Beach', 'city' => 'City', 'mountain' => 'Mountain', 'nature' => 'Nature'];
?>
<section class="hero-shell" style="min-height:74vh;">
  <div class="hero-bg active" style="background-image:url('assets/package.jpg'); opacity:1;"></div>
  <div class="hero-gradient"></div>

  <div class="container hero-content">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <span class="hero-kicker reveal" style="color: #5005ff;">Tour packages</span>
       
      </div>
    </div>
  </div>
</section>

<section class="arc-section arc-top" id="packages">
  <div class="container">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
      <div>
        <div class="section-kicker reveal">Filter tours</div>
        <h2 class="section-title reveal">Dynamic packages loaded from file storage</h2>
      </div>
      <div class="pill-filter reveal">
        <?php foreach ($filterSet as $key => $label): ?>
          <button type="button" class="<?= $key === 'all' ? 'active' : '' ?>" data-filter="<?= e($key) ?>"><?= e($label) ?></button>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="d-grid gap-4">
      <?php foreach ($packageData as $pkg): ?>
        <article class="package-card reveal" data-item="<?= e($pkg['category'] ?? 'all') ?>" data-search="<?= e($pkg['title'] . ' ' . ($pkg['country'] ?? '') . ' ' . ($pkg['description'] ?? '')) ?>">
          <div class="package-media">
            <div class="package-image h-100" style="background-image:url('<?= e($pkg['image']) ?>')"></div>
          </div>
          <div class="package-content">
            <div class="d-flex align-items-start justify-content-between gap-3">
              <div>
                <span class="badge-soft mb-3"><?= e($pkg['country'] ?? 'Travel') ?></span>
                <h3 class="h1 fw-bold mb-2"><?= e($pkg['title']) ?></h3>
              </div>
              <span class="price-chip"><?= e($pkg['price']) ?></span>
            </div>
            <p class="text-muted mb-1"><?= e($pkg['description'] ?? '') ?></p>
            <div class="d-flex flex-wrap align-items-center gap-3">
              <span class="rating"><i class="bi bi-star-fill text-warning"></i> <?= e($pkg['rating'] ?? '5.0') ?></span>
              <span><i class="bi bi-calendar3 me-1"></i> <?= e($pkg['days'] ?? '7 Days') ?></span>
            </div>
            <div class="feature-list">
              <?php foreach (($pkg['details'] ?? []) as $feature): ?>
                <span><?= e($feature) ?></span>
              <?php endforeach; ?>
            </div>
            <?php
              $bookingUrl = app_path('booking.php?booking_type=package&package_id=' . urlencode($pkg['id']) . '&package_name=' . urlencode($pkg['title']) . '&country=' . urlencode($pkg['country'] ?? '') . '&amount=' . urlencode(preg_replace('/[^0-9.]/', '', $pkg['price'] ?? '')));
            ?>
            <div class="mt-2 d-flex flex-wrap gap-2">
              <a class="btn btn-gold px-4" href="<?= e($bookingUrl) ?>">Book now</a>
              <a class="btn btn-outline-dark px-4" href="<?= e(app_path('destinations.php')) ?>">See destinations</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
