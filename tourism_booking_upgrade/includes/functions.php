<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!file_exists(__DIR__ . '/travel-data.php')) {
    throw new RuntimeException('travel-data.php not found.');
}

require_once __DIR__ . '/travel-data.php';

function site_root(): string
{
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    if (preg_match('~/(admin|includes|assets|database)$~', $dir)) {
        $dir = dirname($dir);
    }
    return $dir === '/' ? '' : $dir;
}

function app_path(string $file = ''): string
{
    $root = site_root();
    $file = ltrim($file, '/');

    if ($root === '') {
        return $file === '' ? '' : '/' . $file;
    }

    return $file === '' ? $root : $root . '/' . $file;
}

function asset(string $path): string
{
    return app_path($path);
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function load_json(string $file, array $default = []): array
{
    if (!file_exists($file)) {
        return $default;
    }
    $json = file_get_contents($file);
    $data = json_decode((string)$json, true);
    return is_array($data) ? $data : $default;
}

function save_json(string $file, array $data): bool
{
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    return file_put_contents(
        $file,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        LOCK_EX
    ) !== false;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool
{
    return isset($_SESSION['user']) && (($_SESSION['user']['role'] ?? 'user') === 'admin');
}

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function safe_redirect_target(string $target, string $fallback = 'index.php'): string
{
    $target = trim($target);
    if ($target === '') {
        return app_path($fallback);
    }

    if (preg_match('~^[a-z][a-z0-9+.-]*://~i', $target) || str_starts_with($target, '//')) {
        return app_path($fallback);
    }

    if ($target[0] !== '/') {
        return app_path($target);
    }

    $root = site_root();
    if ($root === '' || $target === $root || str_starts_with($target, $root . '/')) {
        return $target;
    }

    return app_path($fallback);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $token = (string)($_POST['csrf_token'] ?? '');
    return $token !== '' && hash_equals((string)($_SESSION['csrf_token'] ?? ''), $token);
}

function require_csrf(string $redirectTo = ''): void
{
    if (csrf_valid()) {
        return;
    }
    flash_set('danger', 'Security check failed. Please try again.');
    redirect($redirectTo !== '' ? $redirectTo : app_path('index.php'));
}

function user_store_path(): string
{
    return __DIR__ . '/../data/users.json';
}

function package_store_path(): string
{
    return __DIR__ . '/../data/packages.json';
}

function booking_store_path(): string
{
    return __DIR__ . '/../data/bookings.json';
}

function schema_path(): string
{
    return __DIR__ . '/../database/schema.sql';
}

function db_config(): array
{
    return [
        'host' => getenv('AURELIA_DB_HOST') ?: 'localhost',
        'port' => getenv('AURELIA_DB_PORT') ?: '3306',
        'name' => getenv('AURELIA_DB_NAME') ?: 'u100779598_faimdatbase',
        'user' => getenv('AURELIA_DB_USER') ?: 'u100779598_faimdata',
        'pass' => getenv('AURELIA_DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ];
}

function db_identifier(string $name): string
{
    if (!preg_match('/^[A-Za-z0-9_]+$/', $name)) {
        throw new RuntimeException('Invalid database identifier.');
    }
    return '`' . $name . '`';
}

function db_connect(array $config, bool $withDatabase = true): PDO
{
    $dsn = 'mysql:host=' . $config['host'] . ';port=' . $config['port'];
    if ($withDatabase) {
        $dsn .= ';dbname=' . $config['name'];
    }
    $dsn .= ';charset=' . $config['charset'];

    try {
        return new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (Throwable $e) {
        throw new RuntimeException(
            'Database connection failed for ' . $config['name'] .
                '. Start Apache/MySQL in XAMPP, then open /setup.php. ' . $e->getMessage(),
            0,
            $e
        );
    }
}

function schema_statements(string $sql): array
{
    $sql = preg_replace('/^\s*--.*$/m', '', $sql);
    $parts = array_map('trim', explode(';', (string)$sql));
    return array_values(array_filter($parts, fn($part) => $part !== ''));
}

function run_schema(PDO $pdo): void
{
    $schema = file_exists(schema_path()) ? file_get_contents(schema_path()) : '';
    foreach (schema_statements((string)$schema) as $statement) {
        $upper = strtoupper(ltrim($statement));
        if (str_starts_with($upper, 'CREATE DATABASE') || str_starts_with($upper, 'USE ')) {
            continue;
        }
        $pdo->exec($statement);
    }
}

function database_table_count(PDO $pdo, string $table): int
{
    $stmt = $pdo->query('SELECT COUNT(*) FROM ' . db_identifier($table));
    return (int)$stmt->fetchColumn();
}

function database_counts(?PDO $pdo = null): array
{
    $pdo = $pdo ?: db();
    return [
        'users' => database_table_count($pdo, 'users'),
        'packages' => database_table_count($pdo, 'packages'),
        'package_features' => database_table_count($pdo, 'package_features'),
        'bookings' => database_table_count($pdo, 'bookings'),
        'payment_transactions' => database_table_count($pdo, 'payment_transactions'),
        'booking_notifications' => database_table_count($pdo, 'booking_notifications'),
        'booking_audit_logs' => database_table_count($pdo, 'booking_audit_logs'),
    ];
}

function database_install(bool $fresh = false): array
{
    ensure_storage();
    $config = db_config();
    $server = db_connect($config, false);
    $server->exec(
        'CREATE DATABASE IF NOT EXISTS ' . db_identifier($config['name']) .
            ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
    );

    $pdo = db_connect($config, true);
    $GLOBALS['aurelia_pdo'] = $pdo;

    if ($fresh) {
        foreach (
            [
                'SET FOREIGN_KEY_CHECKS = 0',
                'DROP TABLE IF EXISTS booking_audit_logs',
                'DROP TABLE IF EXISTS booking_notifications',
                'DROP TABLE IF EXISTS payment_transactions',
                'DROP TABLE IF EXISTS package_features',
                'DROP TABLE IF EXISTS bookings',
                'DROP TABLE IF EXISTS packages',
                'DROP TABLE IF EXISTS users',
                'SET FOREIGN_KEY_CHECKS = 1',
            ] as $sql
        ) {
            $pdo->exec($sql);
        }
    }

    run_schema($pdo);
    $seeded = seed_database_from_json($pdo);

    return [
        'database' => $config['name'],
        'seeded' => $seeded,
        'counts' => database_counts($pdo),
    ];
}

function ensure_database(): void
{
    static $ready = false;
    if ($ready) {
        return;
    }
    database_install(false);
    $ready = true;
}

function db(): PDO
{
    if (isset($GLOBALS['aurelia_pdo']) && $GLOBALS['aurelia_pdo'] instanceof PDO) {
        return $GLOBALS['aurelia_pdo'];
    }
    ensure_database();

    if (!isset($GLOBALS['aurelia_pdo']) || !($GLOBALS['aurelia_pdo'] instanceof PDO)) {
        throw new RuntimeException('Database connection unavailable.');
    }

    return $GLOBALS['aurelia_pdo'];
}

function seed_database_from_json(PDO $pdo): array
{
    $seeded = [
        'users' => 0,
        'packages' => 0,
        'bookings' => 0,
    ];

    if (database_table_count($pdo, 'users') === 0) {
        $seeded['users'] = import_users($pdo, load_json(user_store_path(), []));
    }
    if (database_table_count($pdo, 'packages') === 0) {
        $seeded['packages'] = import_packages($pdo, load_json(package_store_path(), []));
    }
    if (database_table_count($pdo, 'bookings') === 0) {
        $seeded['bookings'] = import_bookings($pdo, load_json(booking_store_path(), []));
    }

    return $seeded;
}

function import_users(PDO $pdo, array $users): int
{
    $stmt = $pdo->prepare('
        INSERT INTO users (name, email, password, role)
        VALUES (:name, :email, :password, :role)
        ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            password = VALUES(password),
            role = VALUES(role),
            updated_at = CURRENT_TIMESTAMP
    ');

    $count = 0;
    foreach ($users as $user) {
        $email = strtolower(trim((string)($user['email'] ?? '')));
        $password = (string)($user['password'] ?? '');
        if ($email === '' || $password === '') {
            continue;
        }
        $stmt->execute([
            ':name' => trim((string)($user['name'] ?? 'User')),
            ':email' => $email,
            ':password' => $password,
            ':role' => (($user['role'] ?? 'user') === 'admin') ? 'admin' : 'user',
        ]);
        $count++;
    }
    return $count;
}

function import_packages(PDO $pdo, array $packages): int
{
    $count = 0;
    foreach ($packages as $package) {
        if (save_package_row($pdo, $package)) {
            $count++;
        }
    }
    return $count;
}

function import_bookings(PDO $pdo, array $bookings): int
{
    $count = 0;
    foreach ($bookings as $booking) {
        $booking = normalize_booking_row($booking);
        if ($booking['id'] === '') {
            $booking['id'] = generate_booking_id();
        }
        if (save_booking_row($pdo, $booking, true)) {
            $count++;
        }
    }
    return $count;
}

function read_users(): array
{
    $stmt = db()->query('SELECT name, email, password, role FROM users ORDER BY id ASC');
    return $stmt->fetchAll() ?: [];
}

function write_users(array $users): bool
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $emails = [];
        foreach ($users as $user) {
            $email = strtolower(trim((string)($user['email'] ?? '')));
            if ($email !== '') {
                $emails[] = $email;
            }
        }

        if ($emails) {
            $placeholders = implode(',', array_fill(0, count($emails), '?'));
            $delete = $pdo->prepare('DELETE FROM users WHERE email NOT IN (' . $placeholders . ')');
            $delete->execute($emails);
        } else {
            $pdo->exec('DELETE FROM users');
        }

        import_users($pdo, $users);
        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        $pdo->rollBack();
        return false;
    }
}

function read_packages(): array
{
    $pdo = db();
    $packages = [];
    $stmt = $pdo->query('SELECT id, title, country, price, rating, days, image, description, category FROM packages ORDER BY created_at ASC, title ASC');
    foreach ($stmt->fetchAll() ?: [] as $row) {
        $row['details'] = [];
        $packages[$row['id']] = $row;
    }

    if (!$packages) {
        return [];
    }

    $featureStmt = $pdo->query('SELECT package_id, feature FROM package_features ORDER BY package_id ASC, sort_order ASC, id ASC');
    foreach ($featureStmt->fetchAll() ?: [] as $feature) {
        if (isset($packages[$feature['package_id']])) {
            $packages[$feature['package_id']]['details'][] = $feature['feature'];
        }
    }

    return array_values($packages);
}

function write_packages(array $packages): bool
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $ids = [];
        foreach ($packages as $package) {
            $id = trim((string)($package['id'] ?? ''));
            if ($id === '') {
                $id = uniqid('pkg_', true);
                $package['id'] = $id;
            }
            if (save_package_row($pdo, $package)) {
                $ids[] = $id;
            }
        }

        if ($ids) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $delete = $pdo->prepare('DELETE FROM packages WHERE id NOT IN (' . $placeholders . ')');
            $delete->execute($ids);
        } else {
            $pdo->exec('DELETE FROM package_features');
            $pdo->exec('DELETE FROM packages');
        }

        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        $pdo->rollBack();
        return false;
    }
}

function save_package_row(PDO $pdo, array $package): bool
{
    $id = trim((string)($package['id'] ?? ''));
    $title = trim((string)($package['title'] ?? ''));
    $image = trim((string)($package['image'] ?? ''));
    if ($id === '' || $title === '' || $image === '') {
        return false;
    }

    $stmt = $pdo->prepare('
        INSERT INTO packages (id, title, country, price, rating, days, image, description, category)
        VALUES (:id, :title, :country, :price, :rating, :days, :image, :description, :category)
        ON DUPLICATE KEY UPDATE
            title = VALUES(title),
            country = VALUES(country),
            price = VALUES(price),
            rating = VALUES(rating),
            days = VALUES(days),
            image = VALUES(image),
            description = VALUES(description),
            category = VALUES(category),
            updated_at = CURRENT_TIMESTAMP
    ');
    $stmt->execute([
        ':id' => $id,
        ':title' => $title,
        ':country' => trim((string)($package['country'] ?? '')),
        ':price' => trim((string)($package['price'] ?? '')),
        ':rating' => trim((string)($package['rating'] ?? '5.0')),
        ':days' => trim((string)($package['days'] ?? '')),
        ':image' => $image,
        ':description' => trim((string)($package['description'] ?? '')),
        ':category' => trim((string)($package['category'] ?? 'city')),
    ]);

    $delete = $pdo->prepare('DELETE FROM package_features WHERE package_id = ?');
    $delete->execute([$id]);

    $featureStmt = $pdo->prepare('INSERT INTO package_features (package_id, feature, sort_order) VALUES (?, ?, ?)');
    foreach (array_values($package['details'] ?? []) as $index => $feature) {
        $feature = trim((string)$feature);
        if ($feature !== '') {
            $featureStmt->execute([$id, $feature, $index]);
        }
    }

    return true;
}

function first_non_empty(...$values): string
{
    foreach ($values as $value) {
        if (is_string($value) || is_numeric($value)) {
            $value = (string)$value;
            if (trim($value) !== '') {
                return $value;
            }
        }
    }
    return '';
}

function booking_defaults(): array
{
    return [
        'id' => '',
        'booking_ref' => '',
        'booking_type' => 'package',
        'package_id' => '',
        'package_name' => '',
        'country' => '',
        'departure_from' => '',
        'destination' => '',
        'travel_date' => '',
        'travel_time' => '',
        'leave_date' => '',
        'leave_time' => '',
        'guests' => 1,
        'customer_name' => '',
        'customer_email' => '',
        'customer_phone' => '',
        'payment_method' => 'cash',
        'payment_provider' => '',
        'gateway_reference' => '',
        'payment_reference' => '',
        'idempotency_key' => '',
        'payment_status' => 'unpaid',
        'booking_status' => 'pending_review',
        'approval_status' => 'pending',
        'amount' => 0,
        'currency' => 'USD',
        'admin_note' => '',
        'message' => '',
        'booked_by' => 'guest',
        'booked_role' => 'guest',
        'booking_channel' => 'website',
        'ip_address' => '',
        'user_agent' => '',
        'approved_by' => '',
        'approved_at' => '',
        'rejected_by' => '',
        'rejected_at' => '',
        'contacted_at' => '',
        'last_notified_at' => '',
        'notification_count' => 0,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ];
}

function normalize_booking_row(array $row): array
{
    $mapped = $row;
    $mapped['package_name'] = first_non_empty($mapped['package_name'] ?? '', $mapped['package'] ?? '');
    $mapped['customer_name'] = first_non_empty($mapped['customer_name'] ?? '', $mapped['name'] ?? '');
    $mapped['customer_email'] = first_non_empty($mapped['customer_email'] ?? '', $mapped['email'] ?? '');
    $mapped['customer_phone'] = first_non_empty($mapped['customer_phone'] ?? '', $mapped['phone'] ?? '');
    $mapped['travel_date'] = first_non_empty($mapped['travel_date'] ?? '', $mapped['date'] ?? '');
    $mapped['guests'] = first_non_empty($mapped['guests'] ?? '', $mapped['people'] ?? 1);
    $booking = array_merge(booking_defaults(), $mapped);

    foreach (booking_defaults() as $key => $default) {
        if (!array_key_exists($key, $booking) || $booking[$key] === null) {
            $booking[$key] = $default;
        }
    }

    foreach (
        [
            'id',
            'booking_ref',
            'booking_type',
            'package_id',
            'package_name',
            'country',
            'departure_from',
            'destination',
            'travel_date',
            'travel_time',
            'leave_date',
            'leave_time',
            'customer_name',
            'customer_email',
            'customer_phone',
            'payment_method',
            'payment_provider',
            'gateway_reference',
            'payment_reference',
            'idempotency_key',
            'payment_status',
            'booking_status',
            'approval_status',
            'currency',
            'admin_note',
            'message',
            'booked_by',
            'booked_role',
            'booking_channel',
            'ip_address',
            'user_agent',
            'approved_by',
            'approved_at',
            'rejected_by',
            'rejected_at',
            'contacted_at',
            'last_notified_at',
            'created_at',
            'updated_at'
        ] as $key
    ) {
        $booking[$key] = (string)($booking[$key] ?? '');
    }

    $booking['travel_date'] = app_date_value($booking['travel_date']);
    $booking['leave_date'] = app_date_value($booking['leave_date']);
    $booking['travel_time'] = app_time_value($booking['travel_time']);
    $booking['leave_time'] = app_time_value($booking['leave_time']);
    $booking['guests'] = max(1, (int)($booking['guests'] ?? 1));
    $booking['amount'] = (float)($booking['amount'] ?? 0);
    $booking['notification_count'] = max(0, (int)($booking['notification_count'] ?? 0));

    if ($booking['booking_ref'] === '') {
        $booking['booking_ref'] = 'BK-' . strtoupper(substr(md5($booking['id'] . microtime(true)), 0, 8));
    }

    return $booking;
}

function generate_booking_id(): string
{
    return uniqid('bk_', true);
}

function generate_booking_ref(): string
{
    return 'BK-' . strtoupper(bin2hex(random_bytes(4)));
}

function parse_amount($value): float
{
    if (is_numeric($value)) {
        return (float)$value;
    }
    $clean = preg_replace('/[^0-9.]/', '', (string)$value);
    return $clean === '' ? 0.0 : (float)$clean;
}

function app_date_value($value): string
{
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }
    $time = strtotime($value);
    return $time ? date('Y-m-d', $time) : '';
}

function app_time_value($value): string
{
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }
    if (preg_match('/^\d{2}:\d{2}/', $value)) {
        return substr($value, 0, 5);
    }
    $time = strtotime($value);
    return $time ? date('H:i', $time) : '';
}

function db_date_value($value): ?string
{
    $value = app_date_value($value);
    return $value === '' ? null : $value;
}

function db_time_value($value): ?string
{
    $value = app_time_value($value);
    return $value === '' ? null : $value . ':00';
}

function db_datetime_value($value): string
{
    $value = trim((string)$value);
    $time = $value !== '' ? strtotime($value) : false;
    return date('Y-m-d H:i:s', $time ?: time());
}

function nullable_string($value): ?string
{
    $value = trim((string)$value);
    return $value === '' ? null : $value;
}

function nullable_datetime_value($value): ?string
{
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }
    $time = strtotime($value);
    return $time ? date('Y-m-d H:i:s', $time) : null;
}

function insert_booking(array $data): string
{
    $booking = array_merge(booking_defaults(), $data);
    $booking['id'] = first_non_empty($booking['id'] ?? '', generate_booking_id());
    $booking['booking_ref'] = first_non_empty($booking['booking_ref'] ?? '', generate_booking_ref());
    $booking['idempotency_key'] = first_non_empty($booking['idempotency_key'] ?? '', bin2hex(random_bytes(12)));
    $booking = normalize_booking_row($booking);
    $booking['created_at'] = db_datetime_value($booking['created_at'] ?? '');
    $booking['updated_at'] = date('Y-m-d H:i:s');

    save_booking_row(db(), $booking, false);
    return (string)$booking['id'];
}

function save_booking_row(PDO $pdo, array $booking, bool $upsert = true): bool
{
    $booking = normalize_booking_row($booking);
    if (
        $booking['id'] === '' ||
        $booking['package_name'] === '' ||
        $booking['customer_name'] === '' ||
        $booking['customer_email'] === '' ||
        $booking['customer_phone'] === ''
    ) {
        return false;
    }

    $sql = '
        INSERT INTO bookings (
            id, booking_ref, booking_type, package_id, package_name, country, departure_from, destination,
            travel_date, travel_time, leave_date, leave_time, guests, customer_name, customer_email, customer_phone,
            payment_method, payment_provider, gateway_reference, payment_reference, idempotency_key,
            payment_status, booking_status, approval_status, amount, currency, admin_note, message,
            booked_by, booked_role, booking_channel, ip_address, user_agent,
            approved_by, approved_at, rejected_by, rejected_at, contacted_at, last_notified_at, notification_count,
            created_at, updated_at
        ) VALUES (
            :id, :booking_ref, :booking_type, :package_id, :package_name, :country, :departure_from, :destination,
            :travel_date, :travel_time, :leave_date, :leave_time, :guests, :customer_name, :customer_email, :customer_phone,
            :payment_method, :payment_provider, :gateway_reference, :payment_reference, :idempotency_key,
            :payment_status, :booking_status, :approval_status, :amount, :currency, :admin_note, :message,
            :booked_by, :booked_role, :booking_channel, :ip_address, :user_agent,
            :approved_by, :approved_at, :rejected_by, :rejected_at, :contacted_at, :last_notified_at, :notification_count,
            :created_at, :updated_at
        )';

    if ($upsert) {
        $sql .= ' ON DUPLICATE KEY UPDATE
            booking_ref = VALUES(booking_ref),
            booking_type = VALUES(booking_type),
            package_id = VALUES(package_id),
            package_name = VALUES(package_name),
            country = VALUES(country),
            departure_from = VALUES(departure_from),
            destination = VALUES(destination),
            travel_date = VALUES(travel_date),
            travel_time = VALUES(travel_time),
            leave_date = VALUES(leave_date),
            leave_time = VALUES(leave_time),
            guests = VALUES(guests),
            customer_name = VALUES(customer_name),
            customer_email = VALUES(customer_email),
            customer_phone = VALUES(customer_phone),
            payment_method = VALUES(payment_method),
            payment_provider = VALUES(payment_provider),
            gateway_reference = VALUES(gateway_reference),
            payment_reference = VALUES(payment_reference),
            idempotency_key = VALUES(idempotency_key),
            payment_status = VALUES(payment_status),
            booking_status = VALUES(booking_status),
            approval_status = VALUES(approval_status),
            amount = VALUES(amount),
            currency = VALUES(currency),
            admin_note = VALUES(admin_note),
            message = VALUES(message),
            booked_by = VALUES(booked_by),
            booked_role = VALUES(booked_role),
            booking_channel = VALUES(booking_channel),
            ip_address = VALUES(ip_address),
            user_agent = VALUES(user_agent),
            approved_by = VALUES(approved_by),
            approved_at = VALUES(approved_at),
            rejected_by = VALUES(rejected_by),
            rejected_at = VALUES(rejected_at),
            contacted_at = VALUES(contacted_at),
            last_notified_at = VALUES(last_notified_at),
            notification_count = VALUES(notification_count),
            created_at = VALUES(created_at),
            updated_at = VALUES(updated_at)';
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $booking['id'],
        ':booking_ref' => $booking['booking_ref'],
        ':booking_type' => $booking['booking_type'],
        ':package_id' => nullable_string($booking['package_id']),
        ':package_name' => $booking['package_name'],
        ':country' => $booking['country'],
        ':departure_from' => $booking['departure_from'],
        ':destination' => $booking['destination'],
        ':travel_date' => db_date_value($booking['travel_date']),
        ':travel_time' => db_time_value($booking['travel_time']),
        ':leave_date' => db_date_value($booking['leave_date']),
        ':leave_time' => db_time_value($booking['leave_time']),
        ':guests' => max(1, (int)$booking['guests']),
        ':customer_name' => $booking['customer_name'],
        ':customer_email' => $booking['customer_email'],
        ':customer_phone' => $booking['customer_phone'],
        ':payment_method' => $booking['payment_method'],
        ':payment_provider' => $booking['payment_provider'],
        ':gateway_reference' => $booking['gateway_reference'],
        ':payment_reference' => $booking['payment_reference'],
        ':idempotency_key' => $booking['idempotency_key'],
        ':payment_status' => $booking['payment_status'],
        ':booking_status' => $booking['booking_status'],
        ':approval_status' => $booking['approval_status'],
        ':amount' => (float)$booking['amount'],
        ':currency' => strtoupper(substr($booking['currency'] ?: 'USD', 0, 3)),
        ':admin_note' => $booking['admin_note'],
        ':message' => $booking['message'],
        ':booked_by' => $booking['booked_by'],
        ':booked_role' => $booking['booked_role'],
        ':booking_channel' => $booking['booking_channel'],
        ':ip_address' => $booking['ip_address'],
        ':user_agent' => substr($booking['user_agent'], 0, 255),
        ':approved_by' => $booking['approved_by'],
        ':approved_at' => nullable_datetime_value($booking['approved_at']),
        ':rejected_by' => $booking['rejected_by'],
        ':rejected_at' => nullable_datetime_value($booking['rejected_at']),
        ':contacted_at' => nullable_datetime_value($booking['contacted_at']),
        ':last_notified_at' => nullable_datetime_value($booking['last_notified_at']),
        ':notification_count' => max(0, (int)$booking['notification_count']),
        ':created_at' => db_datetime_value($booking['created_at']),
        ':updated_at' => db_datetime_value($booking['updated_at']),
    ]);

    return true;
}

function booking_from_db_row(array $row): array
{
    $row['travel_date'] = app_date_value($row['travel_date'] ?? '');
    $row['leave_date'] = app_date_value($row['leave_date'] ?? '');
    $row['travel_time'] = app_time_value($row['travel_time'] ?? '');
    $row['leave_time'] = app_time_value($row['leave_time'] ?? '');
    return normalize_booking_row($row);
}

function read_bookings(): array
{
    $stmt = db()->query('SELECT * FROM bookings ORDER BY created_at DESC, id DESC');
    $rows = $stmt->fetchAll() ?: [];
    return array_map('booking_from_db_row', $rows);
}

function write_bookings(array $bookings): bool
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $ids = [];
        foreach ($bookings as $booking) {
            $booking = normalize_booking_row($booking);
            if ($booking['id'] === '') {
                $booking['id'] = generate_booking_id();
            }
            if (save_booking_row($pdo, $booking, true)) {
                $ids[] = $booking['id'];
            }
        }

        if ($ids) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $delete = $pdo->prepare('DELETE FROM bookings WHERE id NOT IN (' . $placeholders . ')');
            $delete->execute($ids);
        } else {
            $pdo->exec('DELETE FROM bookings');
        }

        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        $pdo->rollBack();
        return false;
    }
}

function find_booking_by_id(string $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM bookings WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ? booking_from_db_row($row) : null;
}

function find_booking_by_ref(string $ref): ?array
{
    $stmt = db()->prepare('SELECT * FROM bookings WHERE booking_ref = ? LIMIT 1');
    $stmt->execute([$ref]);
    $row = $stmt->fetch();
    return $row ? booking_from_db_row($row) : null;
}

function update_booking(string $id, array $data): bool
{
    $existing = find_booking_by_id($id);
    if (!$existing) {
        return false;
    }

    $booking = normalize_booking_row(array_merge($existing, $data, ['id' => $id]));
    $booking['created_at'] = $existing['created_at'];
    $booking['updated_at'] = date('Y-m-d H:i:s');

    $stmt = db()->prepare('
        UPDATE bookings SET
            booking_type = :booking_type,
            package_id = :package_id,
            package_name = :package_name,
            country = :country,
            departure_from = :departure_from,
            destination = :destination,
            travel_date = :travel_date,
            travel_time = :travel_time,
            leave_date = :leave_date,
            leave_time = :leave_time,
            guests = :guests,
            payment_method = :payment_method,
            payment_provider = :payment_provider,
            gateway_reference = :gateway_reference,
            payment_reference = :payment_reference,
            idempotency_key = :idempotency_key,
            payment_status = :payment_status,
            booking_status = :booking_status,
            approval_status = :approval_status,
            amount = :amount,
            currency = :currency,
            admin_note = :admin_note,
            message = :message,
            booked_by = :booked_by,
            booked_role = :booked_role,
            booking_channel = :booking_channel,
            ip_address = :ip_address,
            user_agent = :user_agent,
            approved_by = :approved_by,
            approved_at = :approved_at,
            rejected_by = :rejected_by,
            rejected_at = :rejected_at,
            contacted_at = :contacted_at,
            last_notified_at = :last_notified_at,
            notification_count = :notification_count,
            updated_at = :updated_at
        WHERE id = :id
    ');

    return $stmt->execute([
        ':booking_type' => $booking['booking_type'],
        ':package_id' => nullable_string($booking['package_id']),
        ':package_name' => $booking['package_name'],
        ':country' => $booking['country'],
        ':departure_from' => $booking['departure_from'],
        ':destination' => $booking['destination'],
        ':travel_date' => db_date_value($booking['travel_date']),
        ':travel_time' => db_time_value($booking['travel_time']),
        ':leave_date' => db_date_value($booking['leave_date']),
        ':leave_time' => db_time_value($booking['leave_time']),
        ':guests' => max(1, (int)$booking['guests']),
        ':payment_method' => $booking['payment_method'],
        ':payment_provider' => $booking['payment_provider'],
        ':gateway_reference' => $booking['gateway_reference'],
        ':payment_reference' => $booking['payment_reference'],
        ':idempotency_key' => $booking['idempotency_key'],
        ':payment_status' => $booking['payment_status'],
        ':booking_status' => $booking['booking_status'],
        ':approval_status' => $booking['approval_status'],
        ':amount' => (float)$booking['amount'],
        ':currency' => strtoupper(substr($booking['currency'] ?: 'USD', 0, 3)),
        ':admin_note' => $booking['admin_note'],
        ':message' => $booking['message'],
        ':booked_by' => $booking['booked_by'],
        ':booked_role' => $booking['booked_role'],
        ':booking_channel' => $booking['booking_channel'],
        ':ip_address' => $booking['ip_address'],
        ':user_agent' => substr($booking['user_agent'], 0, 255),
        ':approved_by' => $booking['approved_by'],
        ':approved_at' => nullable_datetime_value($booking['approved_at']),
        ':rejected_by' => $booking['rejected_by'],
        ':rejected_at' => nullable_datetime_value($booking['rejected_at']),
        ':contacted_at' => nullable_datetime_value($booking['contacted_at']),
        ':last_notified_at' => nullable_datetime_value($booking['last_notified_at']),
        ':notification_count' => max(0, (int)$booking['notification_count']),
        ':updated_at' => $booking['updated_at'],
        ':id' => $id,
    ]);
}

function delete_booking(string $id): bool
{
    $stmt = db()->prepare('DELETE FROM bookings WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->rowCount() > 0;
}

function booking_stats(): array
{
    $bookings = read_bookings();
    $stats = [
        'total' => 0,
        'pending_review' => 0,
        'awaiting_payment' => 0,
        'confirmed' => 0,
        'contacted' => 0,
        'cancelled' => 0,
        'completed' => 0,
        'unpaid' => 0,
        'revenue' => 0.0,
    ];

    foreach ($bookings as $b) {
        $stats['total']++;
        $stats['revenue'] += (float)($b['amount'] ?? 0);
        $status = strtolower((string)($b['booking_status'] ?? ''));
        $payment = strtolower((string)($b['payment_status'] ?? ''));

        if (in_array($status, ['pending', 'pending_review'], true)) {
            $stats['pending_review']++;
        }
        if ($status === 'awaiting_payment') {
            $stats['awaiting_payment']++;
        }
        if ($status === 'confirmed') {
            $stats['confirmed']++;
        }
        if ($status === 'contacted') {
            $stats['contacted']++;
        }
        if ($status === 'cancelled') {
            $stats['cancelled']++;
        }
        if ($status === 'completed') {
            $stats['completed']++;
        }
        if (in_array($payment, ['pending', 'unpaid', 'pending_verification'], true)) {
            $stats['unpaid']++;
        }
    }

    return $stats;
}

function booking_status_badge_class(string $status): string
{
    return match (strtolower($status)) {
        'pending', 'pending_review', 'awaiting_payment' => 'warning text-dark',
        'contacted' => 'info text-dark',
        'confirmed' => 'success',
        'completed' => 'primary',
        'cancelled', 'rejected' => 'danger',
        'failed' => 'danger',
        'paid' => 'success',
        'refunded' => 'secondary',
        'unpaid', 'pending_verification' => 'secondary',
        default => 'secondary',
    };
}

function payment_status_badge_class(string $status): string
{
    return match (strtolower($status)) {
        'paid' => 'success',
        'unpaid', 'pending', 'pending_verification' => 'warning text-dark',
        'failed' => 'danger',
        'refunded' => 'secondary',
        default => 'secondary',
    };
}

function insert_payment_transaction(array $data): int
{
    $stmt = db()->prepare('
        INSERT INTO payment_transactions
            (booking_id, provider, payment_method, amount, currency, status, gateway_reference, idempotency_key, payload_json)
        VALUES
            (:booking_id, :provider, :payment_method, :amount, :currency, :status, :gateway_reference, :idempotency_key, :payload_json)
    ');
    $stmt->execute([
        ':booking_id' => (string)($data['booking_id'] ?? ''),
        ':provider' => trim((string)($data['provider'] ?? '')),
        ':payment_method' => trim((string)($data['payment_method'] ?? '')),
        ':amount' => (float)($data['amount'] ?? 0),
        ':currency' => strtoupper(substr(trim((string)($data['currency'] ?? 'USD')), 0, 3)),
        ':status' => trim((string)($data['status'] ?? 'pending')),
        ':gateway_reference' => trim((string)($data['gateway_reference'] ?? '')),
        ':idempotency_key' => trim((string)($data['idempotency_key'] ?? '')),
        ':payload_json' => isset($data['payload_json']) ? json_encode($data['payload_json'], JSON_UNESCAPED_SLASHES) : null,
    ]);
    return (int)db()->lastInsertId();
}

function insert_booking_notification(array $data): int
{
    $stmt = db()->prepare('
        INSERT INTO booking_notifications
            (booking_id, audience, channel, action_type, title, body, status, read_at)
        VALUES
            (:booking_id, :audience, :channel, :action_type, :title, :body, :status, :read_at)
    ');
    $stmt->execute([
        ':booking_id' => (string)($data['booking_id'] ?? ''),
        ':audience' => in_array(($data['audience'] ?? 'admin'), ['admin', 'customer'], true) ? $data['audience'] : 'admin',
        ':channel' => in_array(($data['channel'] ?? 'in_app'), ['in_app', 'email', 'sms'], true) ? $data['channel'] : 'in_app',
        ':action_type' => trim((string)($data['action_type'] ?? 'booking_created')),
        ':title' => trim((string)($data['title'] ?? 'Booking update')),
        ':body' => trim((string)($data['body'] ?? '')),
        ':status' => in_array(($data['status'] ?? 'unread'), ['unread', 'read'], true) ? $data['status'] : 'unread',
        ':read_at' => nullable_datetime_value($data['read_at'] ?? ''),
    ]);
    return (int)db()->lastInsertId();
}

function insert_booking_audit_log(array $data): int
{
    $stmt = db()->prepare('
        INSERT INTO booking_audit_logs
            (booking_id, actor_email, actor_role, action_type, old_status, new_status, details)
        VALUES
            (:booking_id, :actor_email, :actor_role, :action_type, :old_status, :new_status, :details)
    ');
    $stmt->execute([
        ':booking_id' => (string)($data['booking_id'] ?? ''),
        ':actor_email' => trim((string)($data['actor_email'] ?? 'system')),
        ':actor_role' => trim((string)($data['actor_role'] ?? 'system')),
        ':action_type' => trim((string)($data['action_type'] ?? '')),
        ':old_status' => trim((string)($data['old_status'] ?? '')),
        ':new_status' => trim((string)($data['new_status'] ?? '')),
        ':details' => trim((string)($data['details'] ?? '')),
    ]);
    return (int)db()->lastInsertId();
}

function read_booking_notifications(int $limit = 10, string $audience = 'admin'): array
{
    $limit = max(1, min(100, $limit));
    $sql = '
        SELECT n.*, b.booking_ref, b.customer_name, b.package_name, b.booking_status, b.payment_status
        FROM booking_notifications n
        LEFT JOIN bookings b ON b.id = n.booking_id
        WHERE n.audience = :audience
        ORDER BY n.created_at DESC, n.id DESC
        LIMIT ' . $limit;
    $stmt = db()->prepare($sql);
    $stmt->execute([':audience' => in_array($audience, ['admin', 'customer'], true) ? $audience : 'admin']);
    return $stmt->fetchAll() ?: [];
}

function unread_booking_notification_count(string $audience = 'admin'): int
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM booking_notifications WHERE audience = ? AND status = "unread"');
    $stmt->execute([in_array($audience, ['admin', 'customer'], true) ? $audience : 'admin']);
    return (int)$stmt->fetchColumn();
}

function handle_image_upload(string $field, string $existing = ''): string
{
    if (!isset($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return $existing;
    }

    $tmp = $_FILES[$field]['tmp_name'];
    if (!is_uploaded_file($tmp) || (int)($_FILES[$field]['size'] ?? 0) > 8 * 1024 * 1024) {
        return $existing;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string)$finfo->file($tmp);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    if (!isset($allowed[$mime])) {
        return $existing;
    }

    $uploadDir = __DIR__ . '/../uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $newName = uniqid('img_', true) . '.' . $allowed[$mime];
    $target = $uploadDir . '/' . $newName;
    if (move_uploaded_file($tmp, $target)) {
        return app_path('uploads/' . $newName);
    }

    return $existing;
}

function ensure_storage(): void
{
    foreach ([__DIR__ . '/../data', __DIR__ . '/../uploads'] as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }
}
