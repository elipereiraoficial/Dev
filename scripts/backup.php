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


// Use mysqldump safely. Avoid passing password on CLI. Create a temporary my.cnf with strict perms.
$host = DB_HOST;
$port = DB_PORT;
$db = DB_NAME;
$user = DB_USER;
$pass = DB_PASS;

// Check mysqldump exists
exec('which mysqldump 2>/dev/null || where mysqldump 2>&1', $which_out, $which_ret);
if ($which_ret !== 0) {
    // Try running mysqldump directly - may still be available on Windows in PATH
    $mysqldump = 'mysqldump';
} else {
    $mysqldump = trim(implode('\n', $which_out));
}

// Create temp credentials file to avoid exposing password in process list
$tmpCnf = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'luxury_crm_my.cnf';
$cnfContents = "[client]\nuser={$user}\npassword={$pass}\nhost={$host}\nport={$port}\n";
file_put_contents($tmpCnf, $cnfContents);
chmod($tmpCnf, 0600);

$command = escapeshellcmd($mysqldump) . " --defaults-extra-file=" . escapeshellarg($tmpCnf) . " " . escapeshellarg($db) . " > " . escapeshellarg($filepath) . " 2>&1";

exec($command, $output, $return_var);

// Remove temp credentials file
@unlink($tmpCnf);

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
