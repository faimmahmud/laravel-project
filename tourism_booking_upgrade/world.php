<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'World Explorer | ' . $site['brand'];
require_once __DIR__ . '/includes/header.php';
?>
<section class="hero-shell" style="min-height:76vh;">
  <div class="hero-bg active" style="background-image:url('assets/world.jpg'); opacity:1"></div>
  <div class="hero-gradient"></div>
  <div class="container hero-content">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <span class="hero-kicker reveal" style="color: #ff6e0d;">World Explorer</span>
        
      </div>
    </div>
  </div>
</section>

<section class="arc-section arc-top">
  <div class="container">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
      <div>
        <div class="section-kicker reveal">Featured countries</div>
        <h2 class="section-title reveal">World travel collection</h2>
      </div>
      <div class="pill-filter reveal">
        <button type="button" class="active" data-filter="all">All</button>
        <button type="button" data-filter="Europe">Europe</button>
        <button type="button" data-filter="Asia">Asia</button>
        <button type="button" data-filter="Africa">Africa</button>
        <button type="button" data-filter="Americas">Americas</button>
      </div>
    </div>

    <div class="d-grid gap-4">
      <?php foreach ($countries as $country): ?>
        <article class="country-card reveal" data-item="<?= e($country['region']) ?>" data-search="<?= e($country['name'] . ' ' . $country['region'] . ' ' . $country['reason']) ?>">
          <div class="country-image" style="background-image:url('<?= e($country['image']) ?>')"></div>
          <div class="country-content">
            <span class="badge-soft mb-3"><?= e($country['flag']) ?> • <?= e($country['region']) ?></span>
            <h4 class="display-6"><?= e($country['name']) ?></h4>
            <p class="mb-4"><?= e($country['reason']) ?></p>
            <div class="d-flex flex-wrap gap-2">
              <a href="<?= e(app_path('booking.php')) ?>" class="btn btn-gold">Plan travel</a>
              <span class="price-chip text-dark bg-white">Scenic views</span>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
