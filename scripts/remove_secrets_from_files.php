<?php
// Scans the working tree (excluding .git) and replaces known secret strings with [REDACTED]
// This script is intended to be used by git filter-branch --tree-filter

$secrets = [
    'Cadu5540!!',
    'admin123',
    '123456'
];

$cwd = getcwd();
// iterate files
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cwd, RecursiveDirectoryIterator::SKIP_DOTS));
foreach ($it as $file) {
    $path = $file->getPathname();
    // skip .git
    if (strpos($path, DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR) !== false) continue;
    if ($file->isDir()) continue;
    // Skip binary files by checking for NUL byte
    $contents = @file_get_contents($path);
    if ($contents === false) continue;
    if (strpos($contents, "\0") !== false) continue;

    $new = $contents;
    foreach ($secrets as $s) {
        $new = str_replace($s, '[REDACTED]', $new);
    }

    if ($new !== $contents) {
        // overwrite
        file_put_contents($path, $new);
        echo "Replaced secrets in: $path\n";
    }
}

?>
