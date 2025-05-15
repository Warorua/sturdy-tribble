<?php

require 'vendor/autoload.php';
include './iframev2.1.php';

use HeadlessChromium\BrowserFactory;

function extractFormFields(string $htmlContent): array
{
    if (empty(trim($htmlContent))) {
        die("Error: pullData() returned empty content.\n");
    }

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($htmlContent);
    libxml_clear_errors();

    $xpath = new DOMXPath($doc);
    $inputs = $xpath->query('//form[@id="moodleform4"]//input[@name]');
    $fields = [];

    foreach ($inputs as $input) {
        if ($input instanceof DOMElement) {
            $name = $input->getAttribute('name');
            $value = $input->getAttribute('value');
            $fields[$name] = $value;
        }
    }

    return $fields;
}

function fillMissingFields(array &$fields): void
{
    $fields['first_name'] = 'Brent';
    $fields['last_name'] = 'Seaver';
    $fields['name'] = 'Brent Seaver';
    $fields['CardNo4'] = '4246 3153 8031 1140';
    $fields['card_number'] = '4246315380311140';
    $fields['card_cvn'] = '700';
    $fields['card_type'] = '001';
    $fields['eMonth'] = '09';
    $fields['eYear'] = '2028';
    $fields['card_expiry_date'] = '09-2028';
    $fields['bill_to_address_line1'] = '433 Darlington Ave U';
    $fields['bill_to_address_city'] = 'Wilmington';
    $fields['bill_to_address_state'] = 'NC';
    $fields['bill_to_address_postal_code'] = '28403';
    $fields['bill_to_address_country'] = 'US';
}

function simulateAndPrintAllRequests(array $fields): void
{
    $browserFactory = new BrowserFactory();
    $browser = $browserFactory->createBrowser([
        'headless' => false,
        'noSandbox' => true,
    ]);

    try {
        $page = $browser->createPage();
        $session = $page->getSession();

        $session->sendMessage(new \HeadlessChromium\Communication\Message('Fetch.enable', [
            'patterns' => [['urlPattern' => '*']]
        ]));

        $session->on('Fetch.requestPaused', function ($params) use ($session) {
            $request = $params['request'] ?? [];

            echo "\n--- REQUEST ---\n";
            echo "URL: " . ($request['url'] ?? '[Unknown]') . "\n";
            echo "Method: " . ($request['method'] ?? '[Unknown]') . "\n";

            if (!empty($request['postData'])) {
                echo "POST Data:\n";
                parse_str($request['postData'], $parsed);
                foreach ($parsed as $key => $value) {
                    echo "$key\t$value\n";
                }
            } else {
                echo "[No POST data]\n";
            }

            echo "--- END REQUEST ---\n";

            $session->sendMessage(new \HeadlessChromium\Communication\Message('Fetch.continueRequest', [
                'requestId' => $params['requestId']
            ]));
        });

        $formHtml = '<form id="moodleform4" method="POST" action="https://secureacceptance.cybersource.com/silent/pay">';
        foreach ($fields as $key => $value) {
            $escapedValue = htmlspecialchars($value, ENT_QUOTES);
            $formHtml .= "<input type='hidden' name=\"$key\" value=\"$escapedValue\">";
        }
        $formHtml .= '</form><script>document.forms[0].submit();</script>';

        $htmlFile = __DIR__ . '/tmp/simulated_form.html';
        if (!is_dir(__DIR__ . '/tmp')) {
            mkdir(__DIR__ . '/tmp', 0777, true);
        }
        file_put_contents($htmlFile, $formHtml);

        $page->navigate('file://' . $htmlFile); // Don't waitForNavigation
        sleep(25); // Wait for full flow to complete

    } catch (Exception $e) {
        echo "Simulation error: " . $e->getMessage();
    } finally {
        $browser->close();
    }
}

// MAIN
$html = pullData();
$fields = extractFormFields($html);
fillMissingFields($fields);
simulateAndPrintAllRequests($fields);
