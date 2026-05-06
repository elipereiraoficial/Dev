<?php
// Luxury Real Estate CRM - Configuration
// MySQL Connection (Hostinger)

// Load .env file if exists
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
        putenv(trim($key) . '=' . trim($value));
    }
}

// Database credentials - read from environment where possible.
// IMPORTANT: move real credentials to environment variables or a non-tracked .env file.
// For production (Hostinger), set these as PHP environment variables in the panel.
$isProduction = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'elipereira.com') !== false;

if ($isProduction) {
    // Production: use environment variables or panel settings
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_PORT', getenv('DB_PORT') ?: '3306');
    define('DB_NAME', getenv('DB_NAME') ?: 'u415107443_luxury_crm');
    define('DB_USER', getenv('DB_USER') ?: 'u415107443');
    define('DB_PASS', getenv('DB_PASS') ?: '');
} else {
    // Development: use .env file
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_PORT', getenv('DB_PORT') ?: '3306');
    define('DB_NAME', getenv('DB_NAME') ?: 'luxury_crm');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', isset($_ENV['DB_PASS']) ? $_ENV['DB_PASS'] : '');
}

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

// Deploy secret (do NOT commit real secrets to repo - use env variables)
define('DEPLOY_SECRET', getenv('DEPLOY_SECRET') ?: null);

// Timezone
date_default_timezone_set('Europe/Lisbon');

// Error reporting - disable in production
error_reporting(0);
ini_set('display_errors', '0');

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
// X-XSS-Protection is deprecated in modern browsers but kept for legacy support
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
// Content Security Policy: avoid 'unsafe-inline' where possible. If inline scripts/styles are required,
// consider using nonces or hashes in a later iteration.
header("Content-Security-Policy: default-src 'self' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com https://fonts.googleapis.com https://fonts.gstatic.com;");

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
