<?php
// Complete Fix for Luxury CRM - Hostinger
// Run this once to fix everything

$host = 'localhost';
$db   = 'u415107443_luxury_crm';
$user = 'u415107443_luxury_user';
$pass = 'Cadu5540!!';

echo "<h1>🔧 Luxury CRM - Fixing Database</h1>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>✅ Database Connected!</h2>";
    
    // Fix 1: Create admin user
    echo "<h3>1. Creating Admin User...</h3>";
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Check if exists
    $stmt = $pdo->query("SELECT id FROM users WHERE email = 'admin@luxury.pt'");
    if ($stmt->fetch()) {
        echo "Admin already exists. Updating password...<br>";
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = 'admin@luxury.pt'");
        $stmt->execute([$hash]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, active) VALUES (?, ?, ?, 'admin', 1)");
        $stmt->execute(['Administrador', 'admin@luxury.pt', $hash]);
    }
    echo "✅ Admin user fixed!<br>";
    
    // Fix 2: Ensure deal_stages exist
    echo "<h3>2. Checking Deal Stages...</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM deal_stages");
    $cnt = $stmt->fetch()['cnt'];
    if ($cnt == 0) {
        $stages = [
            ['Novo Lead', 1, '#64748b', 0, 0],
            ['Contacto Inicial', 2, '#3b82f6', 0, 0],
            ['Visita Agendada', 3, '#8b5cf6', 0, 0],
            ['Em Negociação', 4, '#f59e0b', 0, 0],
            ['Proposta Submetida', 5, '#f97316', 0, 0],
            ['Contrato', 6, '#ec4899', 0, 0],
            ['Fechado Ganho', 7, '#10b981', 1, 1],
            ['Fechado Perdido', 8, '#ef4444', 1, 0]
        ];
        $stmt = $pdo->prepare("INSERT INTO deal_stages (name, stage_order, color, is_closed, is_won) VALUES (?, ?, ?, ?, ?)");
        foreach ($stages as $s) {
            $stmt->execute($s);
        }
        echo "✅ Deal stages created!<br>";
    } else {
        echo "✅ Deal stages already exist!<br>";
    }
    
    echo "<h2>🎉 All Fixed!</h2>";
    echo "<hr>";
    echo "<h3>📋 Login Credentials:</h3>";
    echo "<p><strong>URL:</strong> <a href='https://crm.elipereira.com'>https://crm.elipereira.com</a></p>";
    echo "<p><strong>Email:</strong> admin@luxury.pt</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "<hr>";
    echo "<p><em>You can now delete this file (fix_complete.php) for security.</em></p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color:red'>❌ Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}