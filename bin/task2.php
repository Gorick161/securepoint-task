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

// open file
$h = fopen($log, 'r');
if (!$h) {
    fwrite(STDERR, "Cannot open file\n");
    exit(1);
}

// for mapping serial to MAC addresses
$serialToMacs = [];

// !!!
while (!feof($h)) {
    $line = fgets($h);
    if ($line === false) break;

    // check status code
    if (!preg_match('/"\s(\d{3})\s/', $line, $mStatus)) continue;
    $status = (int)$mStatus[1];

    // only successful requests
    if ($status !== 200) continue;

    // serialnumber extraction
    if (!preg_match('/\bserial=([A-Fa-f0-9]+)\b/', $line, $mSerial)) continue;
    $serial = $mSerial[1];

    // specs extraction base64
    if (!preg_match('/\bspecs=([A-Za-z0-9+\/=]+)/', $line, $mSpecs)) continue;
    $b64 = $mSpecs[1];

    // base64-decoding strict
    $raw = base64_decode($b64, true);
    if ($raw === false) continue;

    // gzip-decode
    $json = @gzdecode($raw);
    if ($json === false || $json === null) continue;

    // JSON-decoding
    $specs = json_decode($json, true);
    if (!is_array($specs)) continue;

    // MAC extraction
    if (!isset($specs['mac'])) continue;
    $mac = strtolower(trim((string)$specs['mac']));
    if ($mac === '') continue;

    // Uniqueness-Set per serial
    if (!isset($serialToMacs[$serial])) $serialToMacs[$serial] = [];
    // Set-Insert
    $serialToMacs[$serial][$mac] = true; 
}

fclose($h);

// collect violations
$violations = [];
foreach ($serialToMacs as $s => $macSet) {
    $count = count($macSet);
    if ($count > 1) {
        $violations[$s] = $count;
    }
}

// sort by number of devices, descending
arsort($violations);

// top 10
$topList = array_slice($violations, 0, 10, true);