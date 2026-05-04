<?php
/**
 * AUTO DEPLOY - Pull from GitHub
 * Run: https://crm.elipereira.com/scripts/auto_deploy.php
 * 
 * Note: Requires Git to be installed on server
 */

require_once __DIR__ . '/../../config.php';

header('Content-Type: text/plain');
header('Cache-Control: no-cache');

echo "=== 🚀 AUTO DEPLOY ===\n\n";

// Security check - only allow from authorized IPs or with secret key
$secret = $_GET['secret'] ?? '';
$allowedSecret = 'deploy2026'; // Change this to a secure secret

if ($secret !== $allowedSecret) {
    echo "❌ Access denied. Use ?secret=your_secret\n";
    exit;
}

// Check if git is available
$gitCheck = shell_exec('git --version');
if (!$gitCheck) {
    echo "❌ Git not available on server\n";
    echo "Please deploy manually via Hostinger Git panel\n";
    exit;
}

echo "Git version: " . trim($gitCheck) . "\n\n";

// Navigate to web root
$webRoot = __DIR__ . '/../../';

// Check if it's a git repo
if (!is_dir($webRoot . '/.git')) {
    echo "❌ Not a git repository\n";
    exit;
}

echo "Current branch: " . trim(shell_exec('cd ' . $webRoot . ' && git branch --show-current')) . "\n";
echo "Last commit: " . trim(shell_exec('cd ' . $webRoot . ' && git log -1 --oneline')) . "\n\n";

// Fetch latest from remote
echo "1. Fetching from GitHub...\n";
$fetch = shell_exec('cd ' . $webRoot . ' && git fetch origin 2>&1');
echo $fetch . "\n";

// Check if there are updates
$status = shell_exec('cd ' . $webRoot . ' && git status --porcelain');

if (empty(trim($status))) {
    echo "✅ Already up to date!\n";
    echo "Last deployed: " . trim(shell_exec('cd ' . $webRoot . ' && git log -1 --format="%Y-%m-%H %H:%M"')) . "\n";
    exit;
}

echo "2. New commits available. Pulling...\n";
$pull = shell_exec('cd ' . $webRoot . ' && git pull origin master 2>&1');
echo $pull . "\n";

// Verify
$newCommit = trim(shell_exec('cd ' . $webRoot . ' && git log -1 --oneline'));
echo "\n✅ Deployed to: $newCommit\n";

// Clear PHP OPcache if available
if (function_exists('opcache_get_status')) {
    opcache_reset();
    echo "✅ OPcache cleared\n";
}

echo "\n=== ✅ DEPLOY COMPLETE ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";