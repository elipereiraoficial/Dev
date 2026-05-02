<?php
/**
 * Automated Backup Script
 * Run via cron: php /path/to/scripts/backup.php
 * Recommended: Daily at 2 AM
 */

require_once __DIR__ . '/../config.php';

$backup_dir = __DIR__ . '/../backups';
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

$date = date('Y-m-d-H-i-s');
$filename = "luxury_crm_backup_{$date}.sql";
$filepath = $backup_dir . '/' . $filename;

$output = [];
$return_var = 0;

$host = escapeshellarg(DB_HOST);
$port = escapeshellarg(DB_PORT);
$db = escapeshellarg(DB_NAME);
$user = escapeshellarg(DB_USER);
$pass = escapeshellarg(DB_PASS);

$command = "mysqldump --host={$host} --port={$port} --user={$user} --password={$pass} {$db} > {$filepath}";

exec($command, $output, $return_var);

if ($return_var === 0 && file_exists($filepath)) {
    // Compress backup
    $gz_file = $filepath . '.gz';
    $fp = fopen($filepath, 'r');
    $zp = gzopen($gz_file, 'wb');
    while (!feof($fp)) {
        gzwrite($zp, fread($fp, 1024 * 1024));
    }
    fclose($fp);
    gzclose($zp);
    unlink($filepath);
    
    // Keep only last 7 backups
    $backups = glob($backup_dir . '/*.gz');
    if (count($backups) > 7) {
        usort($backups, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        for ($i = 7; $i < count($backups); $i++) {
            unlink($backups[$i]);
        }
    }
    
    error_log("[BACKUP] Success: $gz_file");
    echo "Backup created: $gz_file\n";
} else {
    error_log("[BACKUP] Failed with code: $return_var");
    echo "Backup failed!\n";
}