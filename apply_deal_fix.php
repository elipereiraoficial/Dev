<?php
require_once 'config.php';

$secret = $_GET['secret'] ?? '';
if ($secret !== 'fix2026') {
    http_response_code(403);
    echo "Access denied";
    exit;
}

header('Content-Type: text/plain');
header('Cache-Control: no-cache');

echo "=== Fix Deal 2025-008 ===\n\n";

try {
    $stmt = $pdo->prepare("SELECT id, reference, title, stage_id, status FROM deals WHERE reference = '2025-008'");
    $stmt->execute();
    $deal = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$deal) {
        echo "❌ Deal 2025-008 not found\n";
        exit;
    }

    echo "Before: ID={$deal['id']}, Stage={$deal['stage_id']}, Status={$deal['status']}\n";

    $update = $pdo->prepare("UPDATE deals SET stage_id = 7, status = 'won', actual_close = NOW() WHERE reference = '2025-008'");
    $update->execute();

    echo "✅ Updated to: Stage=7 (Fechado Ganho), Status=won\n";

    $stmt->execute();
    $deal = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "After: ID={$deal['id']}, Stage={$deal['stage_id']}, Status={$deal['status']}\n";

    echo "\n✅ Fix applied successfully!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}