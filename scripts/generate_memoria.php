<?php
/**
 * Generate a short MEMORIA.md summary automatically.
 * This script is intended to be run inside CI (GitHub Actions) before deploy.
 */

chdir(__DIR__ . '/..');
require_once __DIR__ . '/../config.php';

$lastCommit = trim(shell_exec('git log -1 --pretty=format:"%h - %s (%ci)"'));
$filesChanged = trim(shell_exec('git diff --name-only HEAD~1 HEAD || true'));
$time = date('Y-m-d H:i:s');

$summary = "## Auto-generated deployment summary\n\n";
$summary .= "- Time: {$time}\n";
$summary .= "- Last commit: {$lastCommit}\n";
$summary .= "- Files changed:\n";
foreach (explode("\n", $filesChanged) as $f) {
    if (trim($f) === '') continue;
    $summary .= "  - {$f}\n";
}

$memoriaPath = __DIR__ . '/../MEMORIA.md';
$current = file_exists($memoriaPath) ? file_get_contents($memoriaPath) : '';

// Insert or update an AUTO-GENERATED section at the top of MEMORIA.md
$markerStart = "<!-- AUTO-GENERATED-START -->";
$markerEnd = "<!-- AUTO-GENERATED-END -->";

$autoBlock = "{$markerStart}\n{$summary}\n{$markerEnd}\n\n";

if (strpos($current, $markerStart) !== false) {
    $new = preg_replace("/{$markerStart}.*?{$markerEnd}/s", $autoBlock, $current);
} else {
    $new = $autoBlock . $current;
}

file_put_contents($memoriaPath, $new);
echo "MEMORIA.md updated\n";
