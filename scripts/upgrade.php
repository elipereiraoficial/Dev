<?php
/**
 * Database Auto-Upgrade Script
 * Run this URL after any database schema changes
 * https://crm.elipereira.com/scripts/upgrade.php
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: text/plain');

echo "=== DATABASE UPGRADE ===\n\n";

$results = [];

// Execute SQL files in scripts folder
$sqlFiles = glob(__DIR__ . '/*.sql');

foreach ($sqlFiles as $file) {
    $filename = basename($file);
    echo "Processing: $filename\n";
    
    try {
        $sql = file_get_contents($file);
        $pdo->exec($sql);
        echo "  ✅ Success\n";
        $results[$filename] = 'success';
    } catch (PDOException $e) {
        echo "  ❌ Error: " . $e->getMessage() . "\n";
        $results[$filename] = 'error: ' . $e->getMessage();
    }
}

// Also try individual upgrade files if they exist
$upgradeFiles = [
    'audit_trail.sql',
    'two_factor.sql', 
    'marketing_automation.sql',
    'duplicate_fix.sql'
];

foreach ($upgradeFiles as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "\nRunning: $file\n";
        try {
            $sql = file_get_contents($path);
            $pdo->exec($sql);
            echo "  ✅ Success\n";
        } catch (PDOException $e) {
            echo "  ⚠️ Already exists or error: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n=== UPGRADE COMPLETE ===\n";
echo "You can now use all new features!\n";