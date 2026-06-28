<?php
require_once __DIR__ . '/../includes/functions.php';
ensure_storage();
if (!is_admin()) {
    flash_set('danger', 'Admin access required.');
    redirect(app_path('login.php'));
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf(app_path('admin/index.php'));
    $id = $_POST['id'] ?? '';
    $packages = read_packages();
    $packages = array_values(array_filter($packages, fn($pkg) => ($pkg['id'] ?? '') !== $id));
    write_packages($packages);
    flash_set('success', 'Package deleted.');
}
redirect(app_path('admin/index.php'));
