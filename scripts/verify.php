<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: text/plain');

echo "=== DATA SUMMARY ===\n\n";
echo "Users: " . $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() . "\n";
echo "Properties: " . $pdo->query("SELECT COUNT(*) FROM properties")->fetchColumn() . "\n";
echo "Clients: " . $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn() . "\n";
echo "Deals: " . $pdo->query("SELECT COUNT(*) FROM deals")->fetchColumn() . "\n";
echo "Tasks: " . $pdo->query("SELECT COUNT(*) FROM tasks")->fetchColumn() . "\n";
echo "Activities: " . $pdo->query("SELECT COUNT(*) FROM activities")->fetchColumn() . "\n";

echo "\n=== SAMPLE DATA ===\n";
echo "\nUsers:\n";
$users = $pdo->query("SELECT id, name, email, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) echo "- {$u['name']} ({$u['email']}) - {$u['role']}\n";

echo "\nProperties:\n";
$props = $pdo->query("SELECT title, city, price, status FROM properties")->fetchAll(PDO::FETCH_ASSOC);
foreach ($props as $p) echo "- {$p['title']} - €{$p['price']} ({$p['status']})\n";

echo "\nDeals:\n";
$deals = $pdo->query("SELECT d.reference, d.title, d.value, d.status, s.name as stage FROM deals d LEFT JOIN deal_stages s ON d.stage_id = s.id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($deals as $d) echo "- {$d['reference']}: {$d['title']} - €{$d['value']} ({$d['status']})\n";