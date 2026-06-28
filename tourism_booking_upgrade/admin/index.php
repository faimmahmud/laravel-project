<?php
require_once __DIR__ . '/../includes/functions.php';
ensure_storage();
if (!is_admin()) {
    flash_set('danger', 'Admin access required.');
    redirect(app_path('login.php'));
}

$pageTitle = 'Admin Dashboard | ' . $site['brand'];
require_once __DIR__ . '/../includes/header.php';

$packages = read_packages();
$bookings = read_bookings();
$stats = booking_stats();
$recentBookings = array_slice($bookings, 0, 6);
$notifications = read_booking_notifications(6, 'admin');
$unreadNotifications = unread_booking_notification_count('admin');

function status_badge(string $status, string $type = 'booking'): string {
    return '<span class="badge rounded-pill bg-' . e(booking_status_badge_class($status)) . '">' . e(ucfirst(str_replace('_', ' ', $status))) . '</span>';
}
?>
<section class="arc-section mt-0">
  <div class="container">
    <div class="surface p-4 p-lg-5 rounded-5 reveal">
      <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
        <div>
          <div class="section-kicker">Admin panel</div>
          <h1 class="section-title mb-2">Manage bookings, approvals, and payments</h1>
          <p class="section-lead mb-0">Review requests, approve or reject them, and keep the customer informed.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <a class="btn btn-gold px-4" href="<?= e(app_path('admin/add-package.php')) ?>">Add package</a>
          <a class="btn btn-outline-dark px-4" href="<?= e(app_path('admin/bookings.php')) ?>">All bookings</a>
          <a class="btn btn-outline-dark px-4" href="<?= e(app_path('index.php')) ?>">View site</a>
        </div>
      </div>
    </div>

    <div class="row g-3 mt-2">
      <div class="col-md-6 col-xl-3">
        <div class="surface p-4 rounded-5 h-100 reveal">
          <div class="text-muted small text-uppercase letter-wide">Total bookings</div>
          <div class="display-6 fw-bold mt-2"><?= (int)$stats['total'] ?></div>
        </div>
      </div>
      <div class="col-md-6 col-xl-3">
        <div class="surface p-4 rounded-5 h-100 reveal">
          <div class="text-muted small text-uppercase letter-wide">Pending review</div>
          <div class="display-6 fw-bold mt-2"><?= (int)$stats['pending_review'] ?></div>
        </div>
      </div>
      <div class="col-md-6 col-xl-3">
        <div class="surface p-4 rounded-5 h-100 reveal">
          <div class="text-muted small text-uppercase letter-wide">Confirmed</div>
          <div class="display-6 fw-bold mt-2"><?= (int)$stats['confirmed'] ?></div>
        </div>
      </div>
      <div class="col-md-6 col-xl-3">
        <div class="surface p-4 rounded-5 h-100 reveal">
          <div class="text-muted small text-uppercase letter-wide">Unread notifications</div>
          <div class="display-6 fw-bold mt-2"><?= (int)$unreadNotifications ?></div>
        </div>
      </div>
    </div>

    <div class="row g-4 mt-1">
      <div class="col-lg-7">
        <div class="surface p-4 rounded-5 reveal">
          <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
            <h3 class="h4 fw-bold mb-0">Tour packages</h3>
            <span class="text-muted small"><?= count($packages) ?> packages</span>
          </div>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr>
                  <th>Title</th>
                  <th>Country</th>
                  <th>Price</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($packages as $pkg): ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <img src="<?= e($pkg['image']) ?>" alt="" style="width:64px;height:48px;object-fit:cover;border-radius:12px;">
                        <strong><?= e($pkg['title']) ?></strong>
                      </div>
                    </td>
                    <td><?= e($pkg['country'] ?? '') ?></td>
                    <td><?= e($pkg['price'] ?? '') ?></td>
                    <td class="text-end">
                      <a class="btn btn-sm btn-outline-dark" href="<?= e(app_path('admin/edit-package.php?id=' . urlencode($pkg['id']))) ?>">Edit</a>
                      <form class="d-inline" method="post" action="<?= e(app_path('admin/delete-package.php')) ?>" onsubmit="return confirm('Delete this package?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e($pkg['id']) ?>">
                        <button class="btn btn-sm btn-danger">Delete</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="surface p-4 rounded-5 reveal h-100">
          <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
            <h3 class="h4 fw-bold mb-0">Recent bookings</h3>
            <a href="<?= e(app_path('admin/bookings.php')) ?>" class="small text-decoration-none">View all</a>
          </div>
          <div class="vstack gap-3">
            <?php foreach ($recentBookings as $b): ?>
              <div class="p-3 rounded-4 bg-light border">
                <div class="d-flex justify-content-between gap-2 align-items-start">
                  <div>
                    <strong><?= e($b['customer_name']) ?></strong>
                    <div class="text-muted small"><?= e($b['package_name']) ?> • <?= e($b['booking_ref']) ?></div>
                  </div>
                  <div class="text-end">
                    <?= status_badge($b['booking_status']) ?>
                    <div class="small text-muted mt-1"><?= e($b['travel_date']) ?> <?= e($b['travel_time']) ?></div>
                  </div>
                </div>
                <div class="text-muted small mt-2"><?= e($b['customer_email']) ?> • <?= e($b['payment_method']) ?> • <?= e($b['payment_status']) ?></div>
              </div>
            <?php endforeach; ?>
            <?php if (!$recentBookings): ?>
              <p class="text-muted mb-0">No bookings yet.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="surface p-4 rounded-5 reveal mt-4">
      <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
        <h3 class="h4 fw-bold mb-0">Latest notifications</h3>
        <span class="text-muted small">Admins receive booking alerts here</span>
      </div>
      <div class="vstack gap-3">
        <?php foreach ($notifications as $note): ?>
          <div class="p-3 rounded-4 border <?= $note['status'] === 'unread' ? 'bg-light' : 'bg-white' ?>">
            <div class="d-flex justify-content-between gap-2">
              <strong><?= e($note['title']) ?></strong>
              <span class="text-muted small"><?= e($note['created_at']) ?></span>
            </div>
            <div class="text-muted small mt-1"><?= e($note['body']) ?></div>
            <div class="mt-2 d-flex flex-wrap gap-2">
              <span class="badge rounded-pill bg-secondary"><?= e($note['action_type']) ?></span>
              <span class="badge rounded-pill bg-<?= e($note['status'] === 'unread' ? 'warning text-dark' : 'success') ?>"><?= e($note['status']) ?></span>
            </div>
          </div>
        <?php endforeach; ?>
        <?php if (!$notifications): ?>
          <p class="text-muted mb-0">No notifications yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
