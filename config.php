<?php
// Luxury Real Estate CRM - Configuration
// MySQL Connection (Hostinger)

// Database credentials
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'luxury_crm');
define('DB_USER', getenv('DB_USER') ?: 'luxury_user');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Application Settings
define('APP_NAME', 'Luxury Estate CRM');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'https://crm.elipereira.com');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);

// Security
define('SESSION_NAME', 'luxury_crm_session');
define('CSRF_TOKEN_NAME', 'csrf_token');

// Timezone
date_default_timezone_set('Europe/Lisbon');

// Error reporting - disable in production
error_reporting(0);
ini_set('display_errors', '0');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Database connection (MySQL)
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed. Please check config.php settings. Error: " . $e->getMessage());
}