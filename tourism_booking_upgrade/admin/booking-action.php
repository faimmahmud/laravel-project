<?php
require_once __DIR__ . '/../includes/functions.php';
ensure_storage();
if (!is_admin()) {
    flash_set('danger', 'Admin access required.');
    redirect(app_path('login.php'));
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
    flash_set('danger', 'Security check failed.');
    redirect(app_path('admin/bookings.php'));
}

$id = trim((string)($_POST['id'] ?? ''));
$action = trim((string)($_POST['action'] ?? ''));
$booking = $id !== '' ? find_booking_by_id($id) : null;
if (!$booking) {
    flash_set('danger', 'Booking not found.');
    redirect(app_path('admin/bookings.php'));
}

$actor = current_user() ?: ['email' => 'admin@system.local', 'role' => 'admin'];
$action = strtolower($action);
if ($action === 'connected') {
    $action = 'contacted';
}

$oldStatus = $booking['booking_status'];
$update = [];
$title = 'Booking updated';
$body = $booking['booking_ref'] . ' was updated.';

switch ($action) {
    case 'approve':
        $update = [
            'booking_status' => 'confirmed',
            'approval_status' => 'approved',
            'approved_by' => $actor['email'],
            'approved_at' => date('Y-m-d H:i:s'),
        ];
        $title = 'Booking approved';
        $body = $booking['booking_ref'] . ' is now confirmed.';
        break;
    case 'reject':
        $update = [
            'booking_status' => 'cancelled',
            'approval_status' => 'rejected',
            'rejected_by' => $actor['email'],
            'rejected_at' => date('Y-m-d H:i:s'),
        ];
        $title = 'Booking rejected';
        $body = $booking['booking_ref'] . ' was rejected.';
        break;
    case 'contacted':
        $update = [
            'booking_status' => 'contacted',
            'approval_status' => 'pending',
            'contacted_at' => date('Y-m-d H:i:s'),
        ];
        $title = 'Customer contacted';
        $body = $booking['booking_ref'] . ' was marked as contacted.';
        break;
    case 'mark_paid':
        $update = [
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
            'approval_status' => 'approved',
            'approved_by' => $actor['email'],
            'approved_at' => date('Y-m-d H:i:s'),
        ];
        $title = 'Payment marked paid';
        $body = $booking['booking_ref'] . ' payment is now marked paid.';
        break;
    default:
        flash_set('danger', 'Unknown booking action.');
        redirect(app_path('admin/booking-view.php?id=' . urlencode($id)));
}

if (update_booking($id, $update)) {
    $booking = find_booking_by_id($id) ?: $booking;
    insert_booking_audit_log([
        'booking_id' => $id,
        'actor_email' => $actor['email'] ?? '',
        'actor_role' => $actor['role'] ?? 'admin',
        'action_type' => $action,
        'old_status' => $oldStatus,
        'new_status' => $update['booking_status'] ?? $oldStatus,
        'details' => $title . ' | ' . ($booking['admin_note'] ?? ''),
    ]);
    insert_booking_notification([
        'booking_id' => $id,
        'audience' => 'admin',
        'channel' => 'in_app',
        'action_type' => $action,
        'title' => $title,
        'body' => $body,
        'status' => 'unread',
    ]);
    flash_set('success', 'Booking updated successfully.');
} else {
    flash_set('danger', 'Unable to update booking.');
}

redirect(app_path('admin/booking-view.php?id=' . urlencode($id)));
