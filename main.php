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

if (!isset($argv[1])) {
    echo "Please provide an API key." . PHP_EOL;
    exit;
}

if (!isset($argv[2])) {
    echo "Please provide an input file." . PHP_EOL;
    exit;
}

$filename = (isset($argv[3])) ? $argv[3] : "export-" . date('Y-m-d-H:i:s') . ".csv";
$ipqs = new IPQSRepository($argv[1], $argv[2], $filename);

// I feel like I've seen this somewhere...
try {
    $ipqs->handle();
}
catch (Exception $e)
{
    echo $e->getMessage() . PHP_EOL;
}

