<?php
// Luxury Real Estate CRM - Configuration
// MySQL Connection (Hostinger)

// Database credentials
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'u415107443_luxury_crm');
define('DB_USER', 'u415107443_luxury_user');
define('DB_PASS', 'Cadu5540!!');

// Application Settings
define('APP_NAME', 'Luxury Estate CRM');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'https://crm.elipereira.com');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);

// Security
define('SESSION_NAME', 'luxury_crm_session');
define('CSRF_TOKEN_NAME', 'csrf_token');
define('LOGIN_ATTEMPTS_MAX', 5);
define('LOGIN_ATTEMPTS_WINDOW', 900);

// Timezone
date_default_timezone_set('Europe/Lisbon');

// Error reporting - disable in production
error_reporting(0);
ini_set('display_errors', '0');

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com https://fonts.googleapis.com https://fonts.gstatic.com 'unsafe-inline';");

// Session Security Configuration
if (session_status() === PHP_SESSION_NONE) {
    // Set cookie parameters BEFORE starting session
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    session_name(SESSION_NAME);
    session_start();
    
    // Regenerate session ID periodically
    if (!isset($_SESSION['_init'])) {
        session_regenerate_id(true);
        $_SESSION['_init'] = true;
        $_SESSION['_created'] = time();
    } elseif (time() - $_SESSION['_created'] > 300) {
        session_regenerate_id(true);
        $_SESSION['_created'] = time();
    }
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
    error_log("Database connection failed: " . $e->getMessage());
    die("System temporarily unavailable. Please try again later.");
}