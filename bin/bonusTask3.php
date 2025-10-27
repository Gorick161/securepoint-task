<?php

if (PHP_SAPI !== 'cli') exit(1);

if ($argc < 2) {
    fwrite(STDERR, "Usage: php bin/task3.php <logfile> [--csv-dir=./out] [--top=20] [--fields=architecture,machine,cpu,mem_total,disk_root,disk_data]\n");
    exit(1);
}

$log = $argv[1];

// CSV output / defaults
$csvDir = './out';
$top = 20;
$fields = ['architecture', 'machine', 'cpu', 'mem_total', 'disk_root', 'disk_data'];

// optional cli options
for ($i = 2; $i < $argc; $i++) {
    if (strpos($argv[$i], '--csv-dir=') === 0) {
        $csvDir = substr($argv[$i], 10);
    } elseif (strpos($argv[$i], '--top=') === 0) {
        $top = max(1, (int)substr($argv[$i], 6));
    } elseif (strpos($argv[$i], '--fields=') === 0) {
        $list = substr($argv[$i], 9);
        $parts = array_filter(array_map('trim', explode(',', $list)));
        if ($parts) $fields = $parts;
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

// signature => count
$counts = [];

// normalizer
$norm = function ($v) {
    if (is_bool($v)) return $v ? 'true' : 'false';
    if (is_numeric($v)) return (string)$v;
    if (is_null($v)) return '';
    return strtolower(trim((string)$v));
};

// main loop
while (!feof($h)) {
    $line = fgets($h);
    if ($line === false) break;

    // check status code
    if (!preg_match('/"\s(\d{3})\s/', $line, $mStatus)) continue;
    $status = (int)$mStatus[1];
    if ($status !== 200) continue;

    // specs extraction (base64 token)
    if (!preg_match('/\bspecs=([A-Za-z0-9+\/=]+)/', $line, $mSpecs)) continue;

    // base64 → gzip → json
    $raw = base64_decode($mSpecs[1], true);
    if ($raw === false) continue;
    $json = @gzdecode($raw); // suppress warnings for malformed specs
    if ($json === false || $json === null) continue;

    $specs = json_decode($json, true);
    if (!is_array($specs)) continue;

    // build canonical signature from selected fields
    $parts = [];
    foreach ($fields as $key) {
        $val = array_key_exists($key, $specs) ? $specs[$key] : '';
        $parts[] = $key . '=' . $norm($val);
    }
    $signature = implode('|', $parts);

    // count
    if (!isset($counts[$signature])) $counts[$signature] = 0;
    $counts[$signature]++;
}
fclose($h);

// no results
if (!$counts) {
    echo "Task 3: No classes found.\n";
    exit(0);
}

// sort & top-N
arsort($counts);
$topList = array_slice($counts, 0, $top, true);

// console output
echo "Task 3: Top {$top} hardware classes (by occurrences)\n";
foreach ($topList as $sig => $cnt) {
    echo $cnt . "\t" . $sig . "\n";
}

// write csv
$csv = $csvDir . "/task3_hardware_classes.csv";
$fp = fopen($csv, 'w');

// header: count + fields
$header = array_merge(['count'], $fields);
fputcsv($fp, $header);

// rows: split signature back into field values
foreach ($topList as $sig => $cnt) {
    $row = [$cnt];
    $kv = explode('|', $sig);
    foreach ($kv as $elem) {
        $pos = strpos($elem, '=');
        $row[] = ($pos === false) ? '' : substr($elem, $pos + 1);
    }
    fputcsv($fp, $row);
}
fclose($fp);

echo "CSV: " . $csvDir . PHP_EOL;
