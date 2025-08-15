<?php
/**
 * SMM Platform Configuration
 * Compatible with PHP 7.4+
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'u634930929_In0');
define('DB_USER', 'u634930929_In0');
define('DB_PASS', 'Ino1234@');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('APP_NAME', 'SMM Platform');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost');
define('APP_ROOT', dirname(__DIR__));

// Security Configuration
define('SESSION_NAME', 'smm_session');
define('SESSION_LIFETIME', 3600); // 1 hour
define('PASSWORD_COST', 12);
define('CSRF_TOKEN_NAME', 'csrf_token');

// Email Configuration
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_SECURE', 'tls');

// File Upload Configuration
define('UPLOAD_DIR', APP_ROOT . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);

// Maintenance Mode
define('MAINTENANCE_MODE', false);
define('MAINTENANCE_ALLOWED_IPS', ['127.0.0.1', '::1']);

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', APP_ROOT . '/logs/error.log');

// Timezone
date_default_timezone_set('UTC');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Set security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// CSRF Protection
if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
    $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
}

// Maintenance Mode Check
if (MAINTENANCE_MODE && !in_array($_SERVER['REMOTE_ADDR'], MAINTENANCE_ALLOWED_IPS)) {
    http_response_code(503);
    include APP_ROOT . '/maintenance.php';
    exit;
}
