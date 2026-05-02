<?php
require_once 'config.php';

echo "=== Deals Status ===\n";
$deals = $pdo->query("SELECT d.id, d.title, d.status, d.actual_close, d.stage_id, s.name as stage_name, s.is_won FROM deals d LEFT JOIN deal_stages s ON d.stage_id = s.id")->fetchAll();
foreach ($deals as $d) {
    echo "ID: {$d['id']} | Status: {$d['status']} | Stage: {$d['stage_name']} | Actual Close: " . ($d['actual_close'] ?? 'NULL') . "\n";
}

echo "\n=== Won Deals This Month ===\n";
$won = $pdo->query("SELECT COUNT(*) FROM deals WHERE status = 'won' AND EXTRACT(MONTH FROM actual_close) = EXTRACT(MONTH FROM CURRENT_DATE) AND EXTRACT(YEAR FROM actual_close) = EXTRACT(YEAR FROM CURRENT_DATE)")->fetchColumn();
echo "Count: " . $won . "\n";

echo "\n=== All Deals with Closed Stages ===\n";
$closed = $pdo->query("SELECT d.id, d.title, d.status, s.name, s.is_won, s.is_closed FROM deals d JOIN deal_stages s ON d.stage_id = s.id WHERE s.is_closed = true")->fetchAll();
foreach ($closed as $d) {
    echo "ID: {$d['id']} | Status: {$d['status']} | Stage: {$d['name']} | is_won: {$d['is_won']}\n";
}