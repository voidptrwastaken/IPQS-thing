<?php

/**
 *Fetches emails from specified CSV file and returns them as an array.
 */
function loadEmailsFromCSV(string $fileName): array
{
    $emails = [];
    $row = 1;
    if (($handle = fopen('csv/' . $fileName, "r")) !== FALSE) {
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

/**
 *Checks if an email is considered as valid
 */
function isEmailValid(array $result): bool
{
    return $result["spam_trap_score"] != "high" ||
        $result["frequent_complainer"] === false ||
        $result["recent_abuse"] === false ||
        $result["honeypot"] === false ||
        $result["deliverability"] != "low" ||
        $result["overall_score"] >= 2;
}

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    echo "Since backslashes are a thing (among a lot of other annoying stuff) and that I'm WAY too lazy to add support for Windows specific stuff, the program will now stop here :]" . PHP_EOL;
    echo "(you may use WSL if you desire to stay on your Windows client)" . PHP_EOL;
    exit();
}

/**
 * Core of the program. Will harass www.ipqualityscore.com with all of the lovely emails that you have previously fetched (unless they throw bricks at you because your consumed your 5000 emails/month balance or tried more than 200 emails within a day, probably resulting in you crying in a corner 🥰🥰🥰) 
 */
function handle(array $emails, string $key, string $filename): void
{
    $timeout = 1;

    // Create parameters array.
    $parameters = array(
        'timeout' => $timeout,
        'fast' => 'false',
        'abuse_strictness' => 0
    );

    $curl = curl_init();

    // Format our parameters.
    $formatted_parameters = http_build_query($parameters);

    echo "Fetched " . count($emails) . " email adresses" . PHP_EOL;

    // Loop through provided emails array
    foreach ($emails as $index => $email) {

        // Create the request URL and encode the email address.
        $url = sprintf(
            'https://www.ipqualityscore.com/api/json/email/%s/%s?%s',
            $key,
            urlencode($email),
            $formatted_parameters
        );


        // Set cURL options
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);

        $json = curl_exec($curl);

        // Decode the result into an array.
        $result = json_decode($json, true);

        // Cry if it receives garbage response
        if (!isset($result['success'])) {
            echo "Invalid response" . PHP_EOL;
            return;
        }

        // Cry if it gets hit in the head by www.ipqualityscore.com
        if ($result['success'] === false) {
            echo "www.ipqualityscore.com responded with: \n\"" . $result["message"] . "\"" . PHP_EOL;
            return;
        }

        saveToFile($filename, [$email, isEmailValid($result), date('Y-m-d')]);

        echo "Processed " . $index + 1 . " out of " . count($emails) . " emails" . PHP_EOL;
    }

    curl_close($curl);
}

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
$emails = loadEmailsFromCSV($argv[2]);
handle($emails, $argv[1], $filename);
