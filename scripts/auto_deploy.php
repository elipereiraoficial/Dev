<?php
/**
 * AUTO DEPLOY - Pull from GitHub (Hardened)
 * Run: https://crm.elipereira.com/scripts/auto_deploy.php?secret=...
 * Note: Requires Git to be installed on server and DEPLOY_SECRET configured.
 */

require_once __DIR__ . '/../../config.php';

header('Content-Type: text/plain');
header('Cache-Control: no-cache');

echo "=== AUTO DEPLOY ===\n\n";

// Security check - use DEPLOY_SECRET from config or environment
$secret = $_GET['secret'] ?? '';
if (defined('DEPLOY_SECRET') && DEPLOY_SECRET) {
    if ($secret !== DEPLOY_SECRET) {
        echo "Access denied. Invalid secret provided.\n";
        exit;
    }
} else {
    // If no secret configured, deny access to prevent accidental public deploys
    echo "Access denied. Deploy secret not configured on server.\n";
    exit;
}

// Check if git is available (use exec with escapeshellcmd)
$gitBinary = 'git';
$git_output = [];
$git_return = 1;
exec(escapeshellcmd($gitBinary) . ' --version 2>&1', $git_output, $git_return);
if ($git_return !== 0) {
    echo "Git not available on server\n";
    echo "Please deploy manually via Hostinger Git panel\n";
    exit;
}

echo "Git version: " . trim(implode("\n", $git_output)) . "\n\n";

// Resolve and validate web root
$webRoot = realpath(__DIR__ . '/../../');
if ($webRoot === false || !is_dir($webRoot)) {
    echo "Web root not found\n";
    exit;
}

// Check if it's a git repo
if (!is_dir($webRoot . '/.git')) {
    echo "Not a git repository\n";
    exit;
}

// Run git commands safely
exec('cd ' . escapeshellarg($webRoot) . ' && git branch --show-current 2>&1', $branch_out);
exec('cd ' . escapeshellarg($webRoot) . ' && git log -1 --oneline 2>&1', $commit_out);
echo "Current branch: " . trim(implode('\n', $branch_out)) . "\n";
echo "Last commit: " . trim(implode('\n', $commit_out)) . "\n\n";

// Fetch latest from remote
echo "1. Fetching from GitHub...\n";
exec('cd ' . escapeshellarg($webRoot) . ' && git fetch origin 2>&1', $fetch_out, $fetch_ret);
echo implode('\n', $fetch_out) . "\n";

// Check if there are updates
exec('cd ' . escapeshellarg($webRoot) . ' && git status --porcelain 2>&1', $status_out);
$status = trim(implode('\n', $status_out));

if ($status === '') {
    echo "Already up to date!\n";
    exec('cd ' . escapeshellarg($webRoot) . ' && git log -1 --format="%Y-%m-%d %H:%M" 2>&1', $last_out);
    echo "Last deployed: " . trim(implode('\n', $last_out)) . "\n";
    exit;
}

echo "2. New commits available. Pulling...\n";
exec('cd ' . escapeshellarg($webRoot) . ' && git pull origin master 2>&1', $pull_out, $pull_ret);
echo implode('\n', $pull_out) . "\n";

// Verify
exec('cd ' . escapeshellarg($webRoot) . ' && git log -1 --oneline 2>&1', $new_out);
$newCommit = trim(implode('\n', $new_out));
echo "\nDeployed to: $newCommit\n";

// Clear PHP OPcache if available
if (function_exists('opcache_get_status')) {
    @opcache_reset();
    echo "OPcache cleared\n";
}

echo "\n=== DEPLOY COMPLETE ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
