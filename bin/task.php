<?php
if (PHP_SAPI !== "cli") exit(1);

if ($argc < 2) {

    fwrite(STDERR, "Usage: php bin/task.php <logfile> [--top=10] [--csv-dir=./out] [--only-200] [--all-status]\n");
    exit(1);
}

$log = $argv[1];
$top = 10;
$csv = "./out";
$only200 = true;

if (!is_file($log)) {
    fwrite(STDERR, "File not found\n");
    exit(1);
}

if (!is_dir($csv))

    mkdir($csv, 0777, true);

$h = fopen($log, "r");

if (!$h) {

    fwrite(STDERR, "Cannot open file\n");
    exit(1);
}

while (!feof($h)) {
    $line = fgets($h);
    if ($line === false) break;
    // parsing
}
fclose($h);
