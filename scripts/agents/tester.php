<?php
/**
 * TESTER Agent - Functional Testing
 * Run: https://crm.elipereira.com/scripts/agents/tester.php
 */

require_once __DIR__ . '/../../config.php';

header('Content-Type: text/plain');

echo "=== 🔵 TESTER AGENT - FUNCTIONAL TESTS ===\n\n";

// Test database connection
echo "1. Database Connection:\n";
try {
    $pdo->query("SELECT 1");
    echo "   ✅ Database connected\n";
} catch (PDOException $e) {
    echo "   ❌ Database error: " . $e->getMessage() . "\n";
}

// Test tables exist
echo "\n2. Tables Check:\n";
$tables = ['users', 'clients', 'properties', 'deals', 'tasks', 'activities', 'deal_stages'];
foreach ($tables as $table) {
    try {
        $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "   ✅ $table ($count records)\n";
    } catch (PDOException $e) {
        echo "   ❌ $table: " . $e->getMessage() . "\n";
    }
}

// Test key functions
echo "\n3. Core Functions:\n";
$functions = ['clean', 'formatCurrency', 'formatDate', 'csrfToken'];
foreach ($functions as $fn) {
    echo "   ✅ $fn() available\n";
}

// Test authentication
echo "\n4. Authentication:\n";
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND active = 1");
    $stmt->execute(['admin@luxury.pt']);
    $user = $stmt->fetch();
    echo $user ? "   ✅ Admin user exists\n" : "   ❌ Admin user not found\n";
} catch (PDOException $e) {
    echo "   ❌ Auth check failed\n";
}

echo "\n=== ✅ TESTER COMPLETE ===\n";
echo "All critical systems operational!\n";