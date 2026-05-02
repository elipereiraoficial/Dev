<?php
// Luxury Real Estate CRM - Configuration
// Supabase PostgreSQL Connection

define('DB_HOST', 'db.wxliavsgxnstfonxlrnd.supabase.co');
define('DB_PORT', '5432');
define('DB_NAME', 'postgres');
define('DB_USER', 'postgres');
define('DB_PASS', 'Cadu554076!!');

// Application Settings
define('APP_NAME', 'Luxury Estate CRM');
define('APP_VERSION', '1.0.0');
define('BASE_URL', '');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);

// Security
define('SESSION_NAME', 'luxury_crm_session');
define('CSRF_TOKEN_NAME', 'csrf_token');

// Timezone
date_default_timezone_set('Europe/Lisbon');

// Error reporting - set to 0 in production
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Database connection (PostgreSQL)
try {
    $pdo = new PDO(
        "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME,
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