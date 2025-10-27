<?php

if (PHP_SAPI !== 'cli') exit(1);

// check arguments
if ($argc < 2) {
    fwrite(STDERR, "Usage: php bin/task2.php <logfile> [--csv-dir=./out]\n");
    exit(1);
}


$log = $argv[1];

// CSV output
$csvDir = './out';
for ($i = 2; $i < $argc; $i++) {
    if (strpos($argv[$i], '--csv-dir=') === 0) {
        $csvDir = substr($argv[$i], 10);
    }
}

// Precondition
if (!is_file($log)) {
    fwrite(STDERR, "File not found\n");
    exit(1);
}
if (!is_dir($csvDir)) {
    mkdir($csvDir, 0777, true);
}
