<?php
/**
 * Database Diagnostic Script - TEMPORARY
 * Run: https://crm.elipereira.com/db_diagnostic.php
 * DELETE AFTER USE!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Luxury CRM - Database Diagnostic ===\n\n";

// Check environment variables directly
echo "[1] Environment Variables (getenv):\n";
echo "DB_HOST: " . (getenv('DB_HOST') ?: '(not set - will use fallback)') . "\n";
echo "DB_PORT: " . (getenv('DB_PORT') ?: '(not set - will use fallback)') . "\n";
echo "DB_NAME: " . (getenv('DB_NAME') ?: '(not set - will use fallback)') . "\n";
echo "DB_USER: " . (getenv('DB_USER') ?: '(not set - will use fallback)') . "\n";
echo "DB_PASS: " . (getenv('DB_PASS') ?: '(not set - will use fallback)') . "\n\n";

// Load .env if exists
echo "[2] Checking .env file...\n";
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    echo "[FOUND] .env exists\n";
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        echo "  $key = " . (strpos($key, 'PASS') !== false ? '(set)' : $value) . "\n";
    }
} else {
    echo "[NOT FOUND] .env does not exist\n";
}
echo "\n";

// Detect production mode
$isProduction = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'elipereira.com') !== false;
echo "[3] Production Mode: " . ($isProduction ? "YES" : "NO") . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'unknown') . "\n\n";

// Determine what config.php would use
echo "[4] Values config.php would use:\n";
$dbHost = getenv('DB_HOST') ?: ($isProduction ? 'localhost' : 'localhost');
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_NAME') ?: ($isProduction ? 'u415107443_luxury_crm' : 'luxury_crm');
$dbUser = getenv('DB_USER') ?: ($isProduction ? 'u415107443' : 'root');
$dbPass = getenv('DB_PASS') ?: '';
echo "DB_HOST (fallback): $dbHost\n";
echo "DB_PORT (fallback): $dbPort\n";
echo "DB_NAME (fallback): $dbName\n";
echo "DB_USER (fallback): $dbUser\n";
echo "DB_PASS (fallback): " . (empty($dbPass) ? '(empty)' : '(set)') . "\n\n";

// Try connection
echo "[5] Testing PDO Connection...\n";
try {
    $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
    echo "DSN: $dsn\n";
    echo "User: $dbUser\n";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "[OK] Connected successfully!\n\n";

    // List tables
    echo "[6] Tables in database:\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "  - $table\n";
    }

} catch (PDOException $e) {
    echo "[ERROR] Connection failed: " . $e->getMessage() . "\n";
}

echo "\n=== Diagnostic Complete ===\n";
echo "\n⚠️  DELETE THIS FILE AFTER USE! ⚠️\n";