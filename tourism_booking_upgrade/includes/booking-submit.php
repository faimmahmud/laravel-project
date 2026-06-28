<?php
require_once __DIR__ . '/functions.php';
header('Content-Type: application/json');

ensure_storage();

if (!csrf_valid()) {
    echo json_encode(['success' => false, 'message' => 'Security check failed. Please refresh the page and try again.']);
    exit;
}

$bookingType = trim($_POST['booking_type'] ?? 'package');
$packageId = trim($_POST['package_id'] ?? '');
$packageName = trim($_POST['package_name'] ?? '');
$country = trim($_POST['country'] ?? '');
$departureFrom = trim($_POST['departure_from'] ?? '');
$destination = trim($_POST['destination'] ?? '');
$travelDate = trim($_POST['travel_date'] ?? '');
$travelTime = trim($_POST['travel_time'] ?? '');
$leaveDate = trim($_POST['leave_date'] ?? '');
$leaveTime = trim($_POST['leave_time'] ?? '');
$guests = max(1, (int)($_POST['guests'] ?? 1));
$name = trim($_POST['customer_name'] ?? '');
$email = trim($_POST['customer_email'] ?? '');
$phone = trim($_POST['customer_phone'] ?? '');
$paymentMethod = strtolower(trim($_POST['payment_method'] ?? 'cash'));
$paymentReference = trim($_POST['payment_reference'] ?? '');
$amount = parse_amount($_POST['amount'] ?? 0);
$currency = strtoupper(trim($_POST['currency'] ?? 'USD'));
$message = trim($_POST['message'] ?? '');
$idempotencyKey = trim($_POST['idempotency_key'] ?? '');

if ($packageName === '' && $packageId !== '') {
    foreach (read_packages() as $pkg) {
        if (($pkg['id'] ?? '') === $packageId) {
            $packageName = $pkg['title'] ?? $packageName;
            $country = $country !== '' ? $country : ($pkg['country'] ?? '');
            if ($amount <= 0) {
                $amount = parse_amount($pkg['price'] ?? 0);
            }
            break;
        }
    }
}

if ($bookingType === '' || $packageName === '' || $name === '' || $email === '' || $phone === '' || $travelDate === '' || $leaveDate === '' || $travelTime === '' || $leaveTime === '') {
    echo json_encode(['success' => false, 'message' => 'Please complete the booking form.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

$paymentMethods = ['cash', 'bkash', 'nagad', 'rocket', 'card', 'bank', 'paypal'];
if (!in_array($paymentMethod, $paymentMethods, true)) {
    $paymentMethod = 'cash';
}

$paymentStatusMap = [
    'cash' => 'unpaid',
    'bank' => 'unpaid',
    'bkash' => 'pending_verification',
    'nagad' => 'pending_verification',
    'rocket' => 'pending_verification',
    'card' => 'pending_verification',
    'paypal' => 'pending_verification',
];

$currentUser = current_user();
if (!$currentUser) {
    echo json_encode(['success' => false, 'message' => 'Please log in or register before booking tickets.']);
    exit;
}

$bookingId = insert_booking([
    'booking_type' => $bookingType,
    'package_id' => $packageId,
    'package_name' => $packageName,
    'country' => $country,
    'departure_from' => $departureFrom,
    'destination' => $destination,
    'travel_date' => $travelDate,
    'travel_time' => $travelTime,
    'leave_date' => $leaveDate,
    'leave_time' => $leaveTime,
    'guests' => $guests,
    'customer_name' => $name,
    'customer_email' => $email,
    'customer_phone' => $phone,
    'payment_method' => $paymentMethod,
    'payment_provider' => $paymentMethod,
    'gateway_reference' => $paymentReference,
    'payment_reference' => $paymentReference,
    'idempotency_key' => $idempotencyKey !== '' ? $idempotencyKey : bin2hex(random_bytes(12)),
    'payment_status' => $paymentStatusMap[$paymentMethod] ?? 'unpaid',
    'booking_status' => 'pending_review',
    'approval_status' => 'pending',
    'amount' => $amount,
    'currency' => $currency !== '' ? $currency : 'USD',
    'message' => $message,
    'booked_by' => $currentUser['email'] ?? 'guest',
    'booked_role' => $currentUser['role'] ?? 'guest',
    'booking_channel' => 'website',
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
    'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
    'notification_count' => 1,
]);

insert_booking_notification([
    'booking_id' => $bookingId,
    'audience' => 'admin',
    'channel' => 'in_app',
    'action_type' => 'booking_created',
    'title' => 'New booking request',
    'body' => $name . ' booked ' . $packageName . ' and is waiting for review.',
    'status' => 'unread',
]);

insert_booking_audit_log([
    'booking_id' => $bookingId,
    'actor_email' => $currentUser['email'] ?? 'system',
    'actor_role' => $currentUser['role'] ?? 'user',
    'action_type' => 'booking_created',
    'old_status' => '',
    'new_status' => 'pending_review',
    'details' => $paymentMethod . ' / ' . $paymentStatusMap[$paymentMethod],
]);

insert_payment_transaction([
    'booking_id' => $bookingId,
    'provider' => $paymentMethod,
    'payment_method' => $paymentMethod,
    'amount' => $amount,
    'currency' => $currency,
    'status' => $paymentStatusMap[$paymentMethod] === 'unpaid' ? 'pending' : 'pending_verification',
    'gateway_reference' => $paymentReference,
    'idempotency_key' => $idempotencyKey !== '' ? $idempotencyKey : bin2hex(random_bytes(12)),
    'payload_json' => [
        'customer_email' => $email,
        'booking_type' => $bookingType,
    ],
]);

echo json_encode([
    'success' => true,
    'message' => 'Booking request submitted. Admin will review it and contact you shortly.',
    'booking_id' => $bookingId,
]);
