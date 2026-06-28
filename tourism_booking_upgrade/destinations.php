<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Destinations | ' . $site['brand'];
require_once __DIR__ . '/includes/header.php';

$filters = ['all' => 'All', 'city' => 'City', 'beach' => 'Beach', 'nature' => 'Nature', 'mountain' => 'Mountain'];
$destinationCategories = [
    'Santorini' => 'beach',
    'Kyoto' => 'city',
    'Queenstown' => 'mountain',
    'Marrakesh' => 'city',
    'Cape Town' => 'nature',
    'Reykjavik' => 'nature',
    'Bora Bora' => 'beach',
    'Singapore' => 'city',
];
?>
<section class="hero-shell" style="min-height:78vh;">
  <div class="hero-bg active" style="background-image:url('assets/desa.jpg'); opacity:1"></div>
  <div class="hero-gradient"></div>
  <div class="container hero-content">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <span class="hero-kicker reveal" style="color: #5b17d8;">Destinations</span>
       
      </div>
    </div>
  </div>
</section>

<section class="arc-section arc-top">
  <div class="container">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
      <div>
        <div class="section-kicker reveal">Browse by vibe</div>
        <h2 class="section-title reveal">Luxury destinations, one full view at a time</h2>
      </div>
      <div class="pill-filter reveal">
        <?php foreach ($filters as $key => $label): ?>
          <button type="button" class="<?= $key === 'all' ? 'active' : '' ?>" data-filter="<?= e($key) ?>"><?= e($label) ?></button>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="d-grid gap-4">
      <?php foreach ($destinations as $dest): ?>
        <article class="destination-card reveal" data-item="<?= e($destinationCategories[$dest['name']] ?? 'city') ?>" data-search="<?= e($dest['name'] . ' ' . $dest['country'] . ' ' . $dest['tag']) ?>">
          <div class="destination-image" style="background-image:url('<?= e($dest['image']) ?>')"></div>
          <div class="destination-content">
            <span class="badge-soft mb-3"><?= e($dest['tag']) ?></span>
            <h4 class="display-6"><?= e($dest['name']) ?></h4>
            <p class="mb-4"><?= e($dest['summary']) ?></p>
            <div class="d-flex flex-wrap gap-2">
              <span class="price-chip text-dark bg-white"><?= e($dest['country']) ?></span>
              <a class="btn btn-gold" href="<?= e(app_path('booking.php')) ?>">Book a trip</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
