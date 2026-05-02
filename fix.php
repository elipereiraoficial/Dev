<?php
// Complete Diagnostic & Fix for Luxury CRM
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$db   = 'u415107443_luxury_crm';
$user = 'u415107443_luxury_user';
$pass = 'Cadu5540!!';

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Luxury CRM - Diagnostic</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #d4af37; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>
<div class='container'>
<h1>🔧 Luxury CRM - Diagnostic & Fix</h1>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div class='success'>✅ Database Connected Successfully!</div>";
    
    // Check users table
    echo "<h2>📊 Users in Database</h2>";
    $stmt = $pdo->query("SELECT id, name, email, role, active FROM users");
    $users = $stmt->fetchAll();
    
    if (count($users) > 0) {
        echo "<table><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Active</th></tr>";
        foreach ($users as $u) {
            echo "<tr><td>{$u['id']}</td><td>{$u['name']}</td><td>{$u['email']}</td><td>{$u['role']}</td><td>{$u['active']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>❌ No users found!</div>";
    }
    
    // Fix: Create or update admin
    echo "<h2>🔐 Fixing Admin User</h2>";
    
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Delete existing admin and create new
    $pdo->exec("DELETE FROM users WHERE email = 'admin@luxury.pt'");
    
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, active) VALUES (?, ?, ?, 'admin', 1)");
    $stmt->execute(['Administrador', 'admin@luxury.pt', $password_hash]);
    
    echo "<div class='success'>✅ Admin user created/updated!</div>";
    
    // Verify the password works
    echo "<h2>🔍 Testing Login</h2>";
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND active = 1");
    $stmt->execute(['admin@luxury.pt']);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<div class='info'>Admin found in database!</div>";
        
        // Test password
        if (password_verify('admin123', $admin['password'])) {
            echo "<div class='success'>✅ Password 'admin123' VERIFIED!</div>";
        } else {
            echo "<div class='error'>❌ Password verification FAILED!</div>";
            echo "<p>Hash in DB: " . substr($admin['password'], 0, 30) . "...</p>";
        }
    } else {
        echo "<div class='error'>❌ Admin NOT found after creation!</div>";
    }
    
    echo "<hr>";
    echo "<h2>🎯 FINAL CREDENTIALS</h2>";
    echo "<div class='success'>";
    echo "<p><strong>URL:</strong> https://crm.elipereira.com</p>";
    echo "<p><strong>Email:</strong> admin@luxury.pt</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "</div>";
    
    // Check deal_stages
    echo "<h2>📋 Deal Stages</h2>";
    $stmt = $pdo->query("SELECT id, name, stage_order FROM deal_stages ORDER BY stage_order");
    $stages = $stmt->fetchAll();
    echo "<p>Total stages: " . count($stages) . "</p>";
    foreach ($stages as $s) {
        echo "- {$s['stage_order']}. {$s['name']}<br>";
    }
    
    // Check deals
    echo "<h2>💼 Deals</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM deals");
    echo "<p>Total deals: " . $stmt->fetch()['cnt'] . "</p>";
    
    // Check properties
    echo "<h2>🏡 Properties</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM properties");
    echo "<p>Total properties: " . $stmt->fetch()['cnt'] . "</p>";
    
    // Check clients
    echo "<h2>👥 Clients</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM clients");
    echo "<p>Total clients: " . $stmt->fetch()['cnt'] . "</p>";
    
    echo "<hr>";
    echo "<h3>🚀 Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Delete this file (fix.php) for security</li>";
    echo "<li>Go to: <a href='https://crm.elipereira.com/login.php'>https://crm.elipereira.com/login.php</a></li>";
    echo "<li>Login with: admin@luxury.pt / admin123</li>";
    echo "</ol>";
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Database Error: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";