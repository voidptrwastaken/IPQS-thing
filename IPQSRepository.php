<?php

namespace IPQS\Repository;

class IPQSRepository
{
    private string $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function loadEmailsFromCSV(string $inputFile): array
    {
        $emails = [];
        $row = 1;
        if (($handle = fopen('csv/' . $inputFile, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000)) !== FALSE) {
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
     * Checks if an email is considered as valid according to our rules
     */
    private function isEmailValid(array $result): bool
    {
        return $result["spam_trap_score"] != "high" ||
            $result["frequent_complainer"] === false ||
            $result["recent_abuse"] === false ||
            $result["honeypot"] === false ||
            $result["deliverability"] != "low" ||
            $result["overall_score"] >= 2;
    }

    /**
     * Saves checked email to a CSV file
     */
    private function saveToFile(string $outputFile, array $content): void
    {
        $f = fopen('csv/' . $outputFile, 'a');
        fputcsv($f, $content);
        fclose($f);
    }

    /**
     * Will harass www.ipqualityscore.com with all of the lovely emails
     * you previously fetched from Emarsys (unless it backfires and they throw bricks at you because your consumed your 5000 emails/month balance
     * or tried to call their API more than 200 times within a day,
     * which will most surely result in you crying in a corner while questioning your whole existence.)
     * (aaaaaahhh, cyberbullying 🥰🥰🥰)
     */
    public function handle(array $emails, string $outputFile): void
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

        echo "Fetched " . count($emails) . " email addresses" . PHP_EOL;

        // Loop through provided emails array
        foreach ($emails as $index => $email) {

            // Create the request URL and encode the email address.
            $url = sprintf(
                'https://www.ipqualityscore.com/api/json/email/%s/%s?%s',
                $this->key,
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
                var_dump($json);
                return;
            }

            // Cry if it gets hit in the head by www.ipqualityscore.com
            if ($result['success'] === false) {
                echo "www.ipqualityscore.com responded with: \n\"" . $result["message"] . "\"" . PHP_EOL;
                return;
            }

            $this->saveToFile($outputFile, [$email, $this->isEmailValid($result), date('Y-m-d')]);

            echo "Processed " . $index + 1 . " out of " . count($emails) . " emails" . PHP_EOL;
        }

        curl_close($curl);
    }
}
