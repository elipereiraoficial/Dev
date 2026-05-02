<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: text/plain');

$tables = ['users', 'properties', 'clients', 'deals', 'tasks', 'activities', 'deal_stages'];

foreach ($tables as $table) {
    echo "=== $table ===\n";
    $cols = $pdo->query("SHOW COLUMNS FROM $table")->fetchAll(PDO::FETCH_COLUMN);
    echo implode(', ', $cols) . "\n\n";
}