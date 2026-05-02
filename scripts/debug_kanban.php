<?php
/**
 * Debug Kanban Drag & Drop
 * Run: https://crm.elipereira.com/scripts/debug_kanban.php
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: text/plain');

echo "=== DEBUGGING KANBAN DRAG DROP ===\n\n";

// Test the AJAX endpoint directly
echo "1. Testing AJAX Endpoint:\n";

$_POST['ajax_update_stage'] = '1';
$_POST['deal_id'] = '1';
$_POST['stage_id'] = '2';

ob_start();
include __DIR__ . '/../deals.php';
$output = ob_get_clean();

echo "Output: $output\n";

echo "\n2. Checking Deal Stage Update:\n";
$deal = $pdo->query("SELECT id, stage_id, status FROM deals WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
echo "Deal 1: Stage {$deal['stage_id']}, Status {$deal['status']}\n";

echo "\n3. Checking deal_stages table:\n";
$stages = $pdo->query("SELECT id, name, is_closed, is_won FROM deal_stages")->fetchAll(PDO::FETCH_ASSOC);
foreach ($stages as $s) {
    echo "- Stage {$s['id']}: {$s['name']} (closed: {$s['is_closed']}, won: {$s['is_won']})\n";
}

echo "\n4. Testing with actual POST:\n";
// Simulate actual update
$deal_id = 1;
$stage_id = 3;

$stageStmt = $pdo->prepare("SELECT * FROM deal_stages WHERE id = ?");
$stageStmt->execute([$stage_id]);
$stage = $stageStmt->fetch();

$status = 'open';
if (!empty($stage['is_closed'])) {
    $status = !empty($stage['is_won']) ? 'won' : 'lost';
}

$stmt = $pdo->prepare("UPDATE deals SET stage_id = ?, status = ?, updated_at = NOW() WHERE id = ?");
$stmt->execute([$stage_id, $status, $deal_id]);

echo "Updated Deal $deal_id to Stage $stage_id ($status)\n";

$deal = $pdo->query("SELECT id, stage_id, status FROM deals WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
echo "Result: Stage {$deal['stage_id']}, Status {$deal['status']}\n";

echo "\n=== DONE ===\n";