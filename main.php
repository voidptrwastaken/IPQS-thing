<?php

function loadEmailsFromCSV(string $fileName): array
{
    $emails = [];
    $row = 1;
    if (($handle = fopen($fileName, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $num = count($data);
            $row++;
            for ($c = 0; $c < $num; $c++) {
                if ($c % 2 != 0 && $row != 2) {
                    array_push($emails, $data[$c]);
                }
            }
        }
        fclose($handle);
    }

    return $emails;
}

function isEmailValid(array $result): bool
{
    return $result["spam_trap_score"] != "high" ||
        $result["frequent_complainer"] === false ||
        $result["recent_abuse"] === false ||
        $result["honeypot"] === false ||
        $result["deliverability"] != "low" ||
        $result["overall_score"] >= 2;
}

function saveToFile(string $filename, array $content): void
{
    $f = fopen($filename, 'a');
    fputcsv($f, $content);
    fclose($f);
}

function handle(array $emails, string $key, string $filename): void
{
    $timeout = 1;

    $parameters = array(
        'timeout' => $timeout,
        'fast' => 'false',
        'abuse_strictness' => 0
    );

    $curl = curl_init();
    $formatted_parameters = http_build_query($parameters);

    foreach ($emails as $index => $email) {
        $url = sprintf(
            'https://www.ipqualityscore.com/api/json/email/%s/%s?%s',
            $key,
            urlencode($email),
            $formatted_parameters
        );

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);

        $json = curl_exec($curl);
        $result = json_decode($json, true);

        if (!isset($result['success'])) {
            echo "Invalid response" . PHP_EOL;
            return;
        }

        if ($result['success'] === false) {
            echo "www.ipqualityscore.com responded with: \n\"" . $result["message"] . "\"" . PHP_EOL;
            return;
        }

        saveToFile($filename, [$email, isEmailValid($result), date('Y-m-d')]);

        echo "Processed " . $index + 1 . " out of " . count($emails) . " emails" . PHP_EOL;
    }

    curl_close($curl);
}

if (!isset($argv[1])) {
    echo "Please provide an API key." . PHP_EOL;
    exit;
}

if (!isset($argv[2])) {
    echo "Please provide an input file." . PHP_EOL;
    exit;
}

$filename = (isset($argv[3])) ? $argv[3] : "export-" . date('Y-m-d-H:i:s') . ".csv";
$emails = loadEmailsFromCSV($argv[2]);
handle($emails, $argv[1], $filename);
