<?php
require_once __DIR__ . '/../includes/functions.php';
ensure_storage();
if (!is_admin()) {
    flash_set('danger', 'Admin access required.');
    redirect(app_path('login.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf(app_path('admin/bookings.php'));
    $id = trim((string)($_POST['id'] ?? ''));
    if ($id !== '') {
        delete_booking($id);
        flash_set('success', 'Booking deleted.');
    }
}

redirect(app_path('admin/bookings.php'));
