<?php
require_once __DIR__ . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = [];
session_destroy();
session_start();

flash_set('success', 'You have been logged out.');
redirect(app_path('index.php'));
