<?php

namespace IPQS\Repository;

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    echo "Since backslashes are a thing (among a lot of other annoying stuff) and that I'm WAY too lazy to add support for Windows specific stuff, the program will now stop here :]" . PHP_EOL;
    echo "(you may use WSL if you desire to stay on your Windows client)" . PHP_EOL;
    exit();
}

require_once(__DIR__ . '/IPQSRepository.php');

use Exception;

//This is where the program starts
$apiKey = (isset($argv[1])) ? $argv[1] : null;
$inputFile = (isset($argv[2])) ? $argv[2] : null;
$outputFile = (isset($argv[3])) ? $argv[3] : "export-" . date('Y-m-d-H:i:s') . ".csv";

if (!$apiKey || !$inputFile) {
    echo "Please provide an API key and an input file." . PHP_EOL;
    exit;
}

$ipqs = new IPQSRepository($apiKey);

// I feel like I've seen this somewhere...
try {
    $emails = $ipqs->loadEmailsFromCSV($inputFile);
    $ipqs->handle($emails, $outputFile);
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}
