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

//optional cli options
for ($i = 2; $i < $argc; $i++) {

    if (strpos($argv[$i], "--top=") === 0) $top = (int)substr($argv[$i], 6);
    elseif (strpos($argv[$i], "--csv-dir=") === 0) $csv = substr($argv[$i], 10);
    elseif ($argv[$i] === "--only-200") $only200 = true;
    elseif ($argv[$i] === "--all-status") $only200 = false;
}

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
    if (!preg_match('/"\s(\d{3})\s/', $line, $mStatus)) continue;
    $status = (int)$mStatus[1];
    if ($only200 && $status !== 200) continue;
}

fclose($h);
