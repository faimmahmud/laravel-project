<?php
require_once __DIR__ . '/../includes/functions.php';
ensure_storage();
if (!is_admin()) {
    flash_set('danger', 'Admin access required.');
    redirect(app_path('login.php'));
}

$id = trim((string)($_GET['id'] ?? ($_POST['id'] ?? '')));
$booking = $id !== '' ? find_booking_by_id($id) : null;
if (!$booking) {
    flash_set('danger', 'Booking not found.');
    redirect(app_path('admin/bookings.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valid()) {
    $action = trim((string)($_POST['action'] ?? 'save'));
    $update = [
        'booking_status' => trim((string)($_POST['booking_status'] ?? $booking['booking_status'])),
        'payment_status' => trim((string)($_POST['payment_status'] ?? $booking['payment_status'])),
        'payment_method' => trim((string)($_POST['payment_method'] ?? $booking['payment_method'])),
        'payment_provider' => trim((string)($_POST['payment_provider'] ?? $booking['payment_provider'])),
        'gateway_reference' => trim((string)($_POST['gateway_reference'] ?? $booking['gateway_reference'])),
        'payment_reference' => trim((string)($_POST['payment_reference'] ?? $booking['payment_reference'])),
        'approval_status' => trim((string)($_POST['approval_status'] ?? $booking['approval_status'])),
        'admin_note' => trim((string)($_POST['admin_note'] ?? $booking['admin_note'])),
        'message' => trim((string)($_POST['message'] ?? $booking['message'])),
        'booking_type' => trim((string)($_POST['booking_type'] ?? $booking['booking_type'])),
        'package_name' => trim((string)($_POST['package_name'] ?? $booking['package_name'])),
        'package_id' => trim((string)($_POST['package_id'] ?? $booking['package_id'])),
        'country' => trim((string)($_POST['country'] ?? $booking['country'])),
        'amount' => parse_amount($_POST['amount'] ?? $booking['amount']),
        'currency' => trim((string)($_POST['currency'] ?? $booking['currency'])),
        'departure_from' => trim((string)($_POST['departure_from'] ?? $booking['departure_from'])),
        'destination' => trim((string)($_POST['destination'] ?? $booking['destination'])),
        'travel_date' => trim((string)($_POST['travel_date'] ?? $booking['travel_date'])),
        'travel_time' => trim((string)($_POST['travel_time'] ?? $booking['travel_time'])),
        'leave_date' => trim((string)($_POST['leave_date'] ?? $booking['leave_date'])),
        'leave_time' => trim((string)($_POST['leave_time'] ?? $booking['leave_time'])),
        'guests' => max(1, (int)($_POST['guests'] ?? $booking['guests'])),
        'booked_by' => trim((string)($_POST['booked_by'] ?? $booking['booked_by'])),
        'booked_role' => trim((string)($_POST['booked_role'] ?? $booking['booked_role'])),
        'booking_channel' => trim((string)($_POST['booking_channel'] ?? $booking['booking_channel'])),
        'ip_address' => trim((string)($_POST['ip_address'] ?? $booking['ip_address'])),
    ];

    $actionMap = [
        'approve' => [
            'booking_status' => 'confirmed',
            'approval_status' => 'approved',
            'payment_status' => $booking['payment_status'] === 'paid' ? 'paid' : $booking['payment_status'],
            'approved_by' => (current_user()['email'] ?? 'admin'),
            'approved_at' => date('Y-m-d H:i:s'),
        ],
        'reject' => [
            'booking_status' => 'cancelled',
            'approval_status' => 'rejected',
            'rejected_by' => (current_user()['email'] ?? 'admin'),
            'rejected_at' => date('Y-m-d H:i:s'),
        ],
        'contacted' => [
            'booking_status' => 'contacted',
            'approval_status' => 'pending',
            'contacted_at' => date('Y-m-d H:i:s'),
        ],
        'connected' => [
            'booking_status' => 'contacted',
            'approval_status' => 'pending',
            'contacted_at' => date('Y-m-d H:i:s'),
        ],
        'mark_paid' => [
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
            'approval_status' => 'approved',
            'approved_by' => (current_user()['email'] ?? 'admin'),
            'approved_at' => date('Y-m-d H:i:s'),
        ],
        'mark_pending' => [
            'booking_status' => 'pending_review',
            'approval_status' => 'pending',
        ],
    ];

    if (isset($actionMap[$action])) {
        $update = array_merge($update, $actionMap[$action]);
    }

    $oldStatus = $booking['booking_status'];
    $newStatus = $update['booking_status'];

    if (update_booking($id, $update)) {
        $booking = find_booking_by_id($id) ?: $booking;
        insert_booking_audit_log([
            'booking_id' => $id,
            'actor_email' => current_user()['email'] ?? 'admin',
            'actor_role' => current_user()['role'] ?? 'admin',
            'action_type' => 'booking_' . $action,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'details' => $update['admin_note'] ?? '',
        ]);
        insert_booking_notification([
            'booking_id' => $id,
            'audience' => 'admin',
            'channel' => 'in_app',
            'action_type' => 'booking_' . $action,
            'title' => 'Booking updated',
            'body' => $booking['booking_ref'] . ' was updated to ' . $newStatus . '.',
            'status' => 'unread',
        ]);
        flash_set('success', 'Booking updated successfully.');
    } else {
        flash_set('danger', 'Unable to update booking.');
    }

    redirect(app_path('admin/booking-view.php?id=' . urlencode($id)));
}

$pageTitle = 'Booking ' . $booking['booking_ref'] . ' | ' . $site['brand'];
require_once __DIR__ . '/../includes/header.php';

$customerName = $booking['customer_name'] ?: 'Customer';
$packageName = $booking['package_name'] ?: '—';
$statusClass = booking_status_badge_class($booking['booking_status']);
$paymentClass = payment_status_badge_class($booking['payment_status']);
?>
<section class="arc-section mt-0">
  <div class="container">
    <div class="surface p-4 p-lg-5 rounded-5 reveal mb-4">
      <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-start">
        <div class="flex-grow-1">
          <div class="section-kicker">Booking details</div>
          <h1 class="section-title mb-2"><?= e($booking['booking_ref']) ?></h1>
          <p class="section-lead mb-3"><?= e($customerName) ?> • <?= e($packageName) ?></p>
          <div class="d-flex flex-wrap gap-2">
            <span class="badge rounded-pill bg-<?= e($statusClass) ?>"><?= e($booking['booking_status']) ?></span>
            <span class="badge rounded-pill bg-<?= e($paymentClass) ?>"><?= e($booking['payment_status']) ?></span>
            <span class="badge rounded-pill bg-light text-dark border"><?= e($booking['currency']) ?> <?= e(number_format((float)$booking['amount'], 2)) ?></span>
          </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <a href="<?= e(app_path('admin/bookings.php')) ?>" class="btn btn-outline-dark px-4">Back to list</a>
          <form method="post" action="<?= e(app_path('admin/booking-delete.php')) ?>" onsubmit="return confirm('Delete this booking?');">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= e((string)$booking['id']) ?>">
            <button class="btn btn-danger px-4" type="submit">Delete</button>
          </form>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-4">
        <div class="surface p-4 rounded-5 reveal h-100">
          <h3 class="h4 fw-bold mb-3">Customer</h3>
          <div class="detail-list">
            <div class="detail-row"><span>Name</span><span><?= e($booking['customer_name']) ?></span></div>
            <div class="detail-row"><span>Email</span><span><?= e($booking['customer_email']) ?></span></div>
            <div class="detail-row"><span>Phone</span><span><?= e($booking['customer_phone']) ?></span></div>
            <div class="detail-row"><span>Booked by</span><span><?= e($booking['booked_by']) ?> (<?= e($booking['booked_role']) ?>)</span></div>
            <div class="detail-row"><span>IP</span><span><?= e($booking['ip_address']) ?></span></div>
            <div class="detail-row"><span>User agent</span><span><?= e($booking['user_agent']) ?></span></div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="surface p-4 rounded-5 reveal h-100">
          <h3 class="h4 fw-bold mb-3">Trip</h3>
          <div class="detail-list">
            <div class="detail-row"><span>Type</span><span><?= e($booking['booking_type']) ?></span></div>
            <div class="detail-row"><span>Package</span><span><?= e($booking['package_name']) ?></span></div>
            <div class="detail-row"><span>Package ID</span><span><?= e($booking['package_id'] ?: '—') ?></span></div>
            <div class="detail-row"><span>Country</span><span><?= e($booking['country'] ?: '—') ?></span></div>
            <div class="detail-row"><span>From</span><span><?= e($booking['departure_from'] ?: '—') ?></span></div>
            <div class="detail-row"><span>Destination</span><span><?= e($booking['destination'] ?: '—') ?></span></div>
            <div class="detail-row"><span>Travel</span><span><?= e($booking['travel_date']) ?> at <?= e($booking['travel_time']) ?></span></div>
            <div class="detail-row"><span>Leave</span><span><?= e($booking['leave_date']) ?> at <?= e($booking['leave_time']) ?></span></div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="surface p-4 rounded-5 reveal h-100">
          <h3 class="h4 fw-bold mb-3">Payment & status</h3>
          <div class="detail-list">
            <div class="detail-row"><span>Amount</span><span><?= e($booking['currency']) ?> <?= e(number_format((float)$booking['amount'], 2)) ?></span></div>
            <div class="detail-row"><span>Method</span><span><?= e($booking['payment_method']) ?></span></div>
            <div class="detail-row"><span>Provider</span><span><?= e($booking['payment_provider'] ?: '—') ?></span></div>
            <div class="detail-row"><span>Gateway ref</span><span><?= e($booking['gateway_reference'] ?: '—') ?></span></div>
            <div class="detail-row"><span>Payment ref</span><span><?= e($booking['payment_reference'] ?: '—') ?></span></div>
            <div class="detail-row"><span>Created</span><span><?= e($booking['created_at']) ?></span></div>
          </div>
        </div>
      </div>
    </div>

    <div class="surface p-4 p-lg-5 rounded-5 reveal mt-4">
      <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
          <div class="section-kicker">Admin actions</div>
          <h3 class="h4 fw-bold mb-1">Update the booking state</h3>
          <p class="section-lead mb-0">Use one of the quick actions first, then fine-tune the details below.</p>
        </div>
        <div class="action-strip">
          <form method="post" action="<?= e(app_path('admin/booking-action.php')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= e((string)$booking['id']) ?>">
            <input type="hidden" name="action" value="approve">
            <button class="btn btn-success" type="submit">Approve</button>
          </form>
          <form method="post" action="<?= e(app_path('admin/booking-action.php')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= e((string)$booking['id']) ?>">
            <input type="hidden" name="action" value="contacted">
            <button class="btn btn-info text-dark" type="submit">Mark connected</button>
          </form>
          <form method="post" action="<?= e(app_path('admin/booking-action.php')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= e((string)$booking['id']) ?>">
            <input type="hidden" name="action" value="reject">
            <button class="btn btn-danger" type="submit">Reject</button>
          </form>
          <form method="post" action="<?= e(app_path('admin/booking-action.php')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= e((string)$booking['id']) ?>">
            <input type="hidden" name="action" value="mark_paid">
            <button class="btn btn-gold" type="submit">Mark paid</button>
          </form>
        </div>
      </div>

      <h3 class="h4 fw-bold mb-3">Edit booking</h3>
      <form method="post" class="row g-3" action="<?= e(app_path('admin/booking-view.php')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= e((string)$booking['id']) ?>">
        <input type="hidden" name="action" value="save">

        <div class="col-md-4"><div class="field-card"><label class="form-label">Booking status</label><select name="booking_status" class="form-select"><?php foreach (['pending_review','awaiting_payment','confirmed','contacted','completed','cancelled'] as $opt): ?><option value="<?= e($opt) ?>" <?= $booking['booking_status'] === $opt ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $opt))) ?></option><?php endforeach; ?></select></div></div>
        <div class="col-md-4"><div class="field-card"><label class="form-label">Approval status</label><select name="approval_status" class="form-select"><?php foreach (['pending','approved','rejected'] as $opt): ?><option value="<?= e($opt) ?>" <?= $booking['approval_status'] === $opt ? 'selected' : '' ?>><?= e(ucfirst($opt)) ?></option><?php endforeach; ?></select></div></div>
        <div class="col-md-4"><div class="field-card"><label class="form-label">Payment status</label><select name="payment_status" class="form-select"><?php foreach (['unpaid','pending_verification','paid','refunded','failed'] as $opt): ?><option value="<?= e($opt) ?>" <?= $booking['payment_status'] === $opt ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $opt))) ?></option><?php endforeach; ?></select></div></div>

        <div class="col-md-4"><div class="field-card"><label class="form-label">Payment method</label><select name="payment_method" class="form-select"><?php foreach (['cash','bkash','nagad','rocket','card','bank','paypal'] as $opt): ?><option value="<?= e($opt) ?>" <?= $booking['payment_method'] === $opt ? 'selected' : '' ?>><?= e(ucfirst($opt)) ?></option><?php endforeach; ?></select></div></div>
        <div class="col-md-4"><div class="field-card"><label class="form-label">Payment provider</label><input class="form-control" name="payment_provider" value="<?= e($booking['payment_provider']) ?>"></div></div>
        <div class="col-md-4"><div class="field-card"><label class="form-label">Gateway reference</label><input class="form-control" name="gateway_reference" value="<?= e($booking['gateway_reference']) ?>"></div></div>

        <div class="col-md-4"><div class="field-card"><label class="form-label">Booking type</label><input class="form-control" name="booking_type" value="<?= e($booking['booking_type']) ?>"></div></div>
        <div class="col-md-8"><div class="field-card"><label class="form-label">Package / ticket name</label><input class="form-control" name="package_name" value="<?= e($booking['package_name']) ?>"></div></div>
        <div class="col-md-4"><div class="field-card"><label class="form-label">Package ID</label><input class="form-control" name="package_id" value="<?= e($booking['package_id']) ?>"></div></div>
        <div class="col-md-4"><div class="field-card"><label class="form-label">Country / route</label><input class="form-control" name="country" value="<?= e($booking['country']) ?>"></div></div>
        <div class="col-md-4"><div class="field-card"><label class="form-label">Amount</label><input class="form-control" name="amount" value="<?= e((string)$booking['amount']) ?>"></div></div>

        <div class="col-md-4"><div class="field-card"><label class="form-label">Currency</label><input class="form-control" name="currency" value="<?= e($booking['currency']) ?>"></div></div>
        <div class="col-md-6"><div class="field-card"><label class="form-label">Departure from</label><input class="form-control" name="departure_from" value="<?= e($booking['departure_from']) ?>"></div></div>
        <div class="col-md-6"><div class="field-card"><label class="form-label">Destination</label><input class="form-control" name="destination" value="<?= e($booking['destination']) ?>"></div></div>

        <div class="col-md-3"><div class="field-card"><label class="form-label">Travel date</label><input type="date" class="form-control" name="travel_date" value="<?= e($booking['travel_date']) ?>"></div></div>
        <div class="col-md-3"><div class="field-card"><label class="form-label">Travel time</label><input type="time" class="form-control" name="travel_time" value="<?= e($booking['travel_time']) ?>"></div></div>
        <div class="col-md-3"><div class="field-card"><label class="form-label">Leave date</label><input type="date" class="form-control" name="leave_date" value="<?= e($booking['leave_date']) ?>"></div></div>
        <div class="col-md-3"><div class="field-card"><label class="form-label">Leave time</label><input type="time" class="form-control" name="leave_time" value="<?= e($booking['leave_time']) ?>"></div></div>

        <div class="col-md-4"><div class="field-card"><label class="form-label">Guests / tickets</label><input type="number" min="1" class="form-control" name="guests" value="<?= e((string)$booking['guests']) ?>"></div></div>
        <div class="col-md-4"><div class="field-card"><label class="form-label">Payment reference</label><input class="form-control" name="payment_reference" value="<?= e($booking['payment_reference']) ?>"></div></div>
        <div class="col-md-4"><div class="field-card"><label class="form-label">Booked by</label><input class="form-control" name="booked_by" value="<?= e($booking['booked_by']) ?>"></div></div>

        <div class="col-md-4"><div class="field-card"><label class="form-label">Booked role</label><input class="form-control" name="booked_role" value="<?= e($booking['booked_role']) ?>"></div></div>
        <div class="col-md-4"><div class="field-card"><label class="form-label">Channel</label><input class="form-control" name="booking_channel" value="<?= e($booking['booking_channel']) ?>"></div></div>
        <div class="col-md-4"><div class="field-card"><label class="form-label">IP address</label><input class="form-control" name="ip_address" value="<?= e($booking['ip_address']) ?>"></div></div>

        <div class="col-12"><div class="field-card"><label class="form-label">Admin note</label><textarea class="form-control" name="admin_note" rows="3"><?= e($booking['admin_note']) ?></textarea></div></div>
        <div class="col-12"><div class="field-card"><label class="form-label">Customer message</label><textarea class="form-control" name="message" rows="4"><?= e($booking['message']) ?></textarea></div></div>

        <div class="col-12">
          <button class="btn btn-gold px-4" type="submit">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
