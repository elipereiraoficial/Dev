<?php
/**
 * DEPLOY Agent - Database Upgrades & Deployment
 * Run: https://crm.elipereira.com/scripts/agents/deploy.php
 */

require_once __DIR__ . '/../../config.php';

header('Content-Type: text/plain');

echo "=== 🟡 DEPLOY AGENT - AUTOMATIC UPGRADES ===\n\n";

echo "Running automatic database upgrades...\n\n";

// Check which tables exist
echo "1. Checking Database Schema:\n";
$existingTables = [];
$tables = ['users', 'clients', 'properties', 'deals', 'tasks', 'activities', 'deal_stages'];

foreach ($tables as $table) {
    try {
        $pdo->query("SELECT 1 FROM $table LIMIT 1");
        $existingTables[] = $table;
        echo "   ✅ $table exists\n";
    } catch (PDOException $e) {
        echo "   ❌ $table missing\n";
    }
}

echo "\n2. Running SQL Upgrades:\n";

// Run each upgrade file
$upgrades = [
    'audit_trail.sql' => 'Audit Log',
    'two_factor.sql' => '2FA',
    'marketing_automation.sql' => 'Marketing',
    'duplicate_fix.sql' => 'Duplicates'
];

foreach ($upgrades as $file => $name) {
    $path = __DIR__ . '/../' . $file;
    if (file_exists($path)) {
        try {
            $sql = file_get_contents($path);
            $pdo->exec($sql);
            echo "   ✅ $name upgrade applied\n";
        } catch (PDOException $e) {
            echo "   ⚠️ $name: " . substr($e->getMessage(), 0, 50) . "...\n";
        }
    }
}

echo "\n3. Data Verification:\n";
$stats = [
    'Users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'Clients' => $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn(),
    'Properties' => $pdo->query("SELECT COUNT(*) FROM properties")->fetchColumn(),
    'Deals' => $pdo->query("SELECT COUNT(*) FROM deals")->fetchColumn(),
];

foreach ($stats as $name => $count) {
    echo "   📊 $name: $count\n";
}

echo "\n=== ✅ DEPLOY COMPLETE ===\n";
echo "Database is up to date!\n";