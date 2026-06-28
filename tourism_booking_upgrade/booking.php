<?php
require_once __DIR__ . '/includes/functions.php';

if (!current_user()) {
    flash_set('warning', 'Please log in or create an account before booking tickets.');
    redirect(app_path('login.php?redirect=' . rawurlencode(app_path('booking.php'))));
}

$pageTitle = 'Booking | ' . $site['brand'];
require_once __DIR__ . '/includes/header.php';

$currentUser = current_user();
$selectedPackageId = trim($_GET['package_id'] ?? '');
$selectedPackageName = trim($_GET['package_name'] ?? ($_GET['package'] ?? ''));
$selectedCountry = trim($_GET['country'] ?? '');
$selectedAmount = trim($_GET['amount'] ?? '');
$selectedType = trim($_GET['booking_type'] ?? 'ticket');
$packages = read_packages();

if ($selectedPackageId !== '' && $selectedPackageName === '') {
    foreach ($packages as $pkg) {
        if (($pkg['id'] ?? '') === $selectedPackageId) {
            $selectedPackageName = $pkg['title'] ?? '';
            $selectedCountry = $pkg['country'] ?? $selectedCountry;
            $selectedAmount = preg_replace('/[^0-9.]/', '', (string)($pkg['price'] ?? ''));
            break;
        }
    }
}

$selectedAmount = $selectedAmount !== '' ? $selectedAmount : '0';
$displayPackageName = $selectedPackageName !== '' ? $selectedPackageName : 'General ticket booking';
$userName = $currentUser['name'] ?? '';
$userEmail = $currentUser['email'] ?? '';
$userPhone = $currentUser['phone'] ?? '';
?>
<section class="hero-shell" style="min-height:72vh;">
  <div class="hero-bg active" style="background-image:url('<?= e(travel_img('booking-hero')) ?>');opacity:1"></div>
  <div class="hero-gradient"></div>
  <div class="container hero-content">
    <div class="row align-items-end g-4">
      <div class="col-lg-8">
        <span class="hero-kicker reveal">Fast checkout</span>
        <h1 class="hero-title mt-3 reveal" style="max-width:12ch;">Book in a cleaner, smarter flow</h1>
        <p class="hero-lead mt-3 reveal">
          Your trip details stay focused on what matters: route, date, tickets, and payment method.
          The admin gets a structured request instead of a messy form dump.
        </p>
      </div>
      <div class="col-lg-4">
        <div class="booking-summary reveal">
          <span class="summary-chip mb-3"><i class="bi bi-shield-check"></i> Secure request</span>
          <h2 class="summary-title">Short form. Clear review. Faster confirmation.</h2>
          <div class="mt-4 d-flex flex-wrap gap-2">
            <span class="summary-chip"><i class="bi bi-person-check"></i> Prefilled profile</span>
            <span class="summary-chip"><i class="bi bi-clock-history"></i> Admin review</span>
            <span class="summary-chip"><i class="bi bi-receipt"></i> Payment note</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="arc-section arc-top" id="booking">
  <div class="container">
    <div class="row g-4 align-items-stretch">
      <div class="col-lg-5">
        <div class="full-hero-card reveal" style="min-height:100%; min-height:560px;">
          <div class="card-image" style="background-image:url('<?= e(travel_img('booking-side')) ?>')"></div>
          <div class="content">
            <span class="eyebrow">Simple booking</span>
            <h3 class="mt-3">Less friction. More completed orders.</h3>
            <p class="mt-2">
              The request is intentionally short. The booking still keeps the important travel and payment details,
              but it stops forcing the user to fight the layout.
            </p>
            <div class="feature-list">
              <span>Prefilled profile</span>
              <span>Pending review</span>
              <span>Admin notification</span>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-7">
        <div class="form-shell reveal">
          <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-start mb-4">
            <div>
              <div class="section-kicker">Booking request</div>
              <h2 class="section-title mb-1">Tell us the trip details</h2>
              <p class="section-lead mb-0">Keep only the fields that matter. Everything else stays optional.</p>
            </div>
            <div class="text-md-end">
              <div class="small text-muted">Logged in as</div>
              <strong><?= e($currentUser['name'] ?? $currentUser['email'] ?? 'Guest') ?></strong>
            </div>
          </div>

          <div id="bookingSuccess" class="alert alert-success d-none"></div>
          <div id="bookingError" class="alert alert-danger d-none"></div>

          <form id="bookingForm" action="<?= e(app_path('includes/booking-submit.php')) ?>" method="post" class="row g-3">
            <?= csrf_field() ?>
            <input type="hidden" name="booking_type" value="<?= e($selectedType) ?>">
            <input type="hidden" name="idempotency_key" value="<?= e(bin2hex(random_bytes(16))) ?>">
            <input type="hidden" name="package_id" value="<?= e($selectedPackageId) ?>">
            <input type="hidden" name="package_name" value="<?= e($displayPackageName) ?>">
            <input type="hidden" name="country" value="<?= e($selectedCountry) ?>">
            <input type="hidden" name="amount" value="<?= e($selectedAmount) ?>">
            <input type="hidden" name="currency" value="USD">

            <div class="col-12"><div class="field-card"><label class="form-label">Trip / ticket</label><input type="text" class="form-control" name="package_name_display" value="<?= e($displayPackageName) ?>" readonly><div class="field-hint">This is the trip or ticket name the admin will review.</div></div></div>
            <div class="col-md-6"><div class="field-card"><label class="form-label">Travel date</label><input type="date" name="travel_date" class="form-control" required></div></div>
            <div class="col-md-6"><div class="field-card"><label class="form-label">Travel time</label><input type="time" name="travel_time" class="form-control" required></div></div>
            <div class="col-md-6"><div class="field-card"><label class="form-label">Return / leave date</label><input type="date" name="leave_date" class="form-control" required></div></div>
            <div class="col-md-6"><div class="field-card"><label class="form-label">Return / leave time</label><input type="time" name="leave_time" class="form-control" required></div></div>
            <div class="col-md-6"><div class="field-card"><label class="form-label">Tickets / travelers</label><input type="number" min="1" name="guests" class="form-control" value="1" required></div></div>
            <div class="col-md-6"><div class="field-card"><label class="form-label">Payment method</label><select name="payment_method" class="form-select" required><option value="cash">Cash on confirmation</option><option value="bkash">bKash</option><option value="nagad">Nagad</option><option value="rocket">Rocket</option><option value="card">Card</option><option value="bank">Bank transfer</option><option value="paypal">PayPal</option></select></div></div>
            <div class="col-md-6"><div class="field-card"><label class="form-label">Payment reference</label><input type="text" name="payment_reference" class="form-control" placeholder="Transaction ID / note"></div></div>
            <div class="col-md-6"><div class="field-card"><label class="form-label">Your name</label><input type="text" name="customer_name" class="form-control" value="<?= e($userName) ?>" required></div></div>
            <div class="col-md-6"><div class="field-card"><label class="form-label">Email</label><input type="email" name="customer_email" class="form-control" value="<?= e($userEmail) ?>" required></div></div>
            <div class="col-md-6"><div class="field-card"><label class="form-label">Phone</label><input type="text" name="customer_phone" class="form-control" value="<?= e($userPhone) ?>" required></div></div>
            <div class="col-12"><div class="field-card"><label class="form-label">Message</label><textarea name="message" rows="4" class="form-control" placeholder="Anything the admin should know?"></textarea></div></div>

            <div class="col-12">
              <div class="p-3 rounded-4 bg-light border">
                <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center">
                  <div>
                    <div class="text-muted small text-uppercase letter-wide">Payment route</div>
                    <strong>Admin review first, then confirmation</strong>
                  </div>
                  <div class="text-end">
                    <div class="text-muted small">Estimated amount</div>
                    <strong><?= e($selectedAmount) ?> USD</strong>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 d-flex flex-wrap gap-2">
              <button type="submit" class="btn btn-gold px-4">Submit booking request</button>
              <a href="<?= e(app_path('packages.php')) ?>" class="btn btn-outline-dark px-4">Browse packages</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
