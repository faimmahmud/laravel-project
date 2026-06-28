-- Aurelia Travel MySQL/MariaDB schema
-- Default local database: tourism_FM

CREATE DATABASE IF NOT EXISTS `tourism_FM`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `tourism_FM`;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS packages (
    id VARCHAR(64) NOT NULL,
    title VARCHAR(190) NOT NULL,
    country VARCHAR(120) NOT NULL DEFAULT '',
    price VARCHAR(50) NOT NULL DEFAULT '',
    rating VARCHAR(20) NOT NULL DEFAULT '5.0',
    days VARCHAR(50) NOT NULL DEFAULT '',
    image TEXT NOT NULL,
    description TEXT NULL,
    category VARCHAR(60) NOT NULL DEFAULT 'city',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_packages_category (category),
    KEY idx_packages_country (country)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS package_features (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    package_id VARCHAR(64) NOT NULL,
    feature VARCHAR(190) NOT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_package_features_package (package_id),
    CONSTRAINT fk_package_features_package
        FOREIGN KEY (package_id) REFERENCES packages(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bookings (
    id VARCHAR(64) NOT NULL,
    booking_ref VARCHAR(40) NOT NULL,
    booking_type VARCHAR(30) NOT NULL DEFAULT 'package',
    package_id VARCHAR(64) NULL,
    package_name VARCHAR(190) NOT NULL,
    country VARCHAR(120) NOT NULL DEFAULT '',
    departure_from VARCHAR(120) NOT NULL DEFAULT '',
    destination VARCHAR(120) NOT NULL DEFAULT '',
    travel_date DATE NULL,
    travel_time TIME NULL,
    leave_date DATE NULL,
    leave_time TIME NULL,
    guests INT UNSIGNED NOT NULL DEFAULT 1,
    customer_name VARCHAR(150) NOT NULL,
    customer_email VARCHAR(190) NOT NULL,
    customer_phone VARCHAR(60) NOT NULL,
    payment_method VARCHAR(40) NOT NULL DEFAULT 'cash',
    payment_provider VARCHAR(40) NOT NULL DEFAULT '',
    gateway_reference VARCHAR(190) NOT NULL DEFAULT '',
    payment_reference VARCHAR(190) NOT NULL DEFAULT '',
    idempotency_key VARCHAR(80) NOT NULL DEFAULT '',
    payment_status VARCHAR(40) NOT NULL DEFAULT 'unpaid',
    booking_status VARCHAR(40) NOT NULL DEFAULT 'pending_review',
    approval_status VARCHAR(40) NOT NULL DEFAULT 'pending',
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    currency CHAR(3) NOT NULL DEFAULT 'USD',
    admin_note TEXT NULL,
    message TEXT NULL,
    booked_by VARCHAR(190) NOT NULL DEFAULT 'guest',
    booked_role VARCHAR(40) NOT NULL DEFAULT 'guest',
    booking_channel VARCHAR(40) NOT NULL DEFAULT 'website',
    ip_address VARCHAR(45) NOT NULL DEFAULT '',
    user_agent VARCHAR(255) NOT NULL DEFAULT '',
    approved_by VARCHAR(190) NOT NULL DEFAULT '',
    approved_at DATETIME NULL,
    rejected_by VARCHAR(190) NOT NULL DEFAULT '',
    rejected_at DATETIME NULL,
    contacted_at DATETIME NULL,
    last_notified_at DATETIME NULL,
    notification_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_bookings_ref (booking_ref),
    KEY idx_bookings_created_at (created_at),
    KEY idx_bookings_status (booking_status),
    KEY idx_bookings_payment (payment_status),
    KEY idx_bookings_type (booking_type),
    KEY idx_bookings_customer_email (customer_email),
    KEY idx_bookings_payment_provider (payment_provider),
    KEY idx_bookings_approval (approval_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_transactions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    booking_id VARCHAR(64) NOT NULL,
    provider VARCHAR(40) NOT NULL DEFAULT '',
    payment_method VARCHAR(40) NOT NULL DEFAULT '',
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    currency CHAR(3) NOT NULL DEFAULT 'USD',
    status VARCHAR(40) NOT NULL DEFAULT 'pending',
    gateway_reference VARCHAR(190) NOT NULL DEFAULT '',
    idempotency_key VARCHAR(80) NOT NULL DEFAULT '',
    payload_json JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_payment_booking (booking_id),
    KEY idx_payment_status (status),
    KEY idx_payment_provider (provider),
    CONSTRAINT fk_payment_booking
        FOREIGN KEY (booking_id) REFERENCES bookings(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS booking_notifications (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    booking_id VARCHAR(64) NOT NULL,
    audience ENUM('admin', 'customer') NOT NULL DEFAULT 'admin',
    channel ENUM('in_app', 'email', 'sms') NOT NULL DEFAULT 'in_app',
    action_type VARCHAR(50) NOT NULL DEFAULT 'booking_created',
    title VARCHAR(190) NOT NULL,
    body TEXT NOT NULL,
    status ENUM('unread', 'read') NOT NULL DEFAULT 'unread',
    read_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_notification_booking (booking_id),
    KEY idx_notification_audience_status (audience, status),
    CONSTRAINT fk_notification_booking
        FOREIGN KEY (booking_id) REFERENCES bookings(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS booking_audit_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    booking_id VARCHAR(64) NOT NULL,
    actor_email VARCHAR(190) NOT NULL DEFAULT '',
    actor_role VARCHAR(40) NOT NULL DEFAULT 'system',
    action_type VARCHAR(50) NOT NULL DEFAULT '',
    old_status VARCHAR(40) NOT NULL DEFAULT '',
    new_status VARCHAR(40) NOT NULL DEFAULT '',
    details TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_audit_booking (booking_id),
    KEY idx_audit_action (action_type),
    CONSTRAINT fk_audit_booking
        FOREIGN KEY (booking_id) REFERENCES bookings(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
