<?php
require_once __DIR__ . '/../includes/functions.php';
ensure_storage();
if (!is_admin()) {
    flash_set('danger', 'Admin access required.');
    redirect(app_path('login.php'));
}

$q = strtolower(trim($_GET['q'] ?? ''));
$status = strtolower(trim($_GET['status'] ?? 'all'));
$payment = strtolower(trim($_GET['payment'] ?? 'all'));
$type = strtolower(trim($_GET['type'] ?? 'all'));
$all = read_bookings();

$bookings = array_values(array_filter($all, function ($b) use ($q, $status, $payment, $type) {
    if ($status !== 'all' && strtolower($b['booking_status'] ?? '') !== $status) return false;
    if ($payment !== 'all' && strtolower($b['payment_status'] ?? '') !== $payment) return false;
    if ($type !== 'all' && strtolower($b['booking_type'] ?? '') !== $type) return false;
    if ($q === '') return true;
    $hay = strtolower(implode(' ', [
        $b['booking_ref'] ?? '', $b['customer_name'] ?? '', $b['customer_email'] ?? '',
        $b['customer_phone'] ?? '', $b['package_name'] ?? '', $b['country'] ?? '',
        $b['departure_from'] ?? '', $b['destination'] ?? ''
    ]));
    return str_contains($hay, $q);
}));

$pageTitle = 'All Bookings | ' . $site['brand'];
require_once __DIR__ . '/../includes/header.php';

function badge_html(string $text, string $class = 'secondary'): string {
    return '<span class="badge rounded-pill bg-' . e($class) . '">' . e($text) . '</span>';
}
?>
<section class="arc-section mt-0">
  <div class="container">
    <div class="surface p-4 p-lg-5 rounded-5 reveal mb-4">
      <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
        <div>
          <div class="section-kicker">Bookings</div>
          <h1 class="section-title mb-2">Customer booking requests</h1>
          <p class="section-lead mb-0">Search by name, package, email, or reference. Keep the workflow focused on review and approval.</p>
        </div>
        <a href="<?= e(app_path('admin/index.php')) ?>" class="btn btn-outline-dark px-4">Back to dashboard</a>
      </div>
    </div>

    <div class="surface p-4 rounded-5 reveal mb-4">
      <form class="row g-3 align-items-end" method="get">
        <div class="col-lg-4">
          <label class="form-label">Search</label>
          <input type="text" name="q" class="form-control" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Reference, name, email, package">
        </div>
        <div class="col-md-4 col-lg-2">
          <label class="form-label">Booking status</label>
          <select name="status" class="form-select">
            <?php foreach (['all','pending_review','awaiting_payment','confirmed','contacted','completed','cancelled'] as $opt): ?>
              <option value="<?= e($opt) ?>" <?= $status === $opt ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $opt))) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4 col-lg-2">
          <label class="form-label">Payment</label>
          <select name="payment" class="form-select">
            <?php foreach (['all','unpaid','pending_verification','paid','refunded','failed'] as $opt): ?>
              <option value="<?= e($opt) ?>" <?= $payment === $opt ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $opt))) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4 col-lg-2">
          <label class="form-label">Type</label>
          <select name="type" class="form-select">
            <?php foreach (['all','package','ticket','tour','transfer'] as $opt): ?>
              <option value="<?= e($opt) ?>" <?= $type === $opt ? 'selected' : '' ?>><?= e(ucfirst($opt)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-lg-2 d-grid">
          <button class="btn btn-gold" type="submit">Filter</button>
        </div>
      </form>
    </div>

    <div class="surface p-4 rounded-5 reveal">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Ref</th>
              <th>Customer</th>
              <th>Package / ticket</th>
              <th>Travel</th>
              <th>Payment</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($bookings as $b): ?>
              <tr>
                <td>
                  <strong><?= e($b['booking_ref']) ?></strong><br>
                  <span class="text-muted small"><?= e($b['booking_type']) ?></span>
                </td>
                <td>
                  <strong><?= e($b['customer_name']) ?></strong><br>
                  <span class="text-muted small"><?= e($b['customer_email']) ?><br><?= e($b['customer_phone']) ?></span>
                </td>
                <td>
                  <strong><?= e($b['package_name']) ?></strong><br>
                  <span class="text-muted small"><?= e($b['country'] ?: '—') ?> • <?= e($b['departure_from'] ?: '—') ?> → <?= e($b['destination'] ?: '—') ?></span><br>
                  <span class="text-muted small">Qty: <?= (int)($b['guests'] ?? 1) ?></span>
                </td>
                <td>
                  <strong><?= e($b['travel_date'] ?: '—') ?></strong><br>
                  <span class="text-muted small"><?= e($b['travel_time'] ?: '—') ?></span>
                </td>
                <td>
                  <strong><?= e(ucfirst(str_replace('_', ' ', $b['payment_method'] ?? ''))) ?></strong><br>
                  <span class="text-muted small"><?= e($b['payment_reference'] ?: 'No ref') ?></span><br>
                  <?= badge_html(ucfirst(str_replace('_', ' ', $b['payment_status'])), payment_status_badge_class($b['payment_status'])) ?>
                </td>
                <td>
                  <?= badge_html(ucfirst(str_replace('_', ' ', $b['booking_status'])), booking_status_badge_class($b['booking_status'])) ?><br>
                  <span class="text-muted small"><?= e($b['created_at']) ?></span>
                </td>
                <td class="text-end text-nowrap">
                  <a class="btn btn-sm btn-outline-dark" href="<?= e(app_path('admin/booking-view.php?id=' . urlencode((string)$b['id']))) ?>">View</a>
                  <form class="d-inline" method="post" action="<?= e(app_path('admin/booking-delete.php')) ?>" onsubmit="return confirm('Delete this booking?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= e((string)$b['id']) ?>">
                    <button class="btn btn-sm btn-danger">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$bookings): ?>
              <tr><td colspan="7" class="text-center text-muted py-5">No bookings found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
