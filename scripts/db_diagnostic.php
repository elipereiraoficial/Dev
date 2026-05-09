<?php
/**
 * Database Diagnostic Script - TEMPORARY
 * Run: https://crm.elipereira.com/scripts/db_diagnostic.php
 * DELETE AFTER USE!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Luxury CRM - Database Diagnostic ===\n\n";

// Check if config loads
echo "[1] Testing config.php...\n";
require_once __DIR__ . '/../config.php';
echo "[OK] Config loaded\n\n";

// Show detected values
echo "[2] Database Configuration (from config.php):\n";
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_PORT: " . DB_PORT . "\n";
echo "DB_NAME: " . DB_NAME . "\n";
echo "DB_USER: " . DB_USER . "\n";
echo "DB_PASS: " . (empty(DB_PASS) ? "(empty)" : "(set)") . "\n\n";

// Check environment variables
echo "[3] Environment Variables:\n";
echo "DB_HOST env: " . (getenv('DB_HOST') ?: '(not set)') . "\n";
echo "DB_PORT env: " . (getenv('DB_PORT') ?: '(not set)') . "\n";
echo "DB_NAME env: " . (getenv('DB_NAME') ?: '(not set)') . "\n";
echo "DB_USER env: " . (getenv('DB_USER') ?: '(not set)') . "\n";
echo "DB_PASS env: " . (getenv('DB_PASS') ?: '(not set)') . "\n\n";

// Detect production
$isProduction = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'elipereira.com') !== false;
echo "[4] Production Mode: " . ($isProduction ? "YES" : "NO") . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'unknown') . "\n\n";

// Try connection
echo "[5] Testing PDO Connection...\n";
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    echo "DSN: $dsn\n";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
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