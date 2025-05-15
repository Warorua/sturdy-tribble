<?php
require 'vendor/autoload.php';

use HeadlessChromium\BrowserFactory;

$browserFactory = new BrowserFactory();
$browser = $browserFactory->createBrowser();
$page = $browser->createPage();

$page->navigate('file://' . __DIR__ . '/cyber_form.html')->waitForNavigation();
$html = $page->getHtml();
$browser->close();

function extract_input_fields_with_values($html)
{
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($html);
    libxml_clear_errors();

    $xpath = new DOMXPath($doc);
    $fields = [];

    $inputs = $xpath->query('//form[@id="moodleform4"]//input[@name]');
    foreach ($inputs as $input) {
        if ($input instanceof DOMElement) {
            $name = $input->getAttribute('name');
            $value = $input->getAttribute('value');
            if (trim($value) !== '') {
                $fields[$name] = $value;
            }
        }
    }

    return $fields;
}

function post_to_cybersource($url, $fields)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function extract_post_to_ipn($html)
{
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($html);
    libxml_clear_errors();

    $xpath = new DOMXPath($doc);
    $fields = [];

    $inputs = $xpath->query('//form//input[@type="hidden"]');
    foreach ($inputs as $input) {
        if ($input instanceof DOMElement) {
            $name = $input->getAttribute('name');
            $value = $input->getAttribute('value');
            $fields[$name] = $value;
        }
    }

    return $fields;
}

$fields = extract_input_fields_with_values($html);

// Fill in dynamic test values
$fields['card_number'] = '4246315380311140';
$fields['card_cvn'] = '700';
$fields['card_expiry_date'] = '09-2028';
$fields['bill_to_address_country'] = 'US';
$fields['bill_to_address_state'] = 'NC';
$fields['bill_to_address_city'] = 'Wilmington';
$fields['bill_to_address_line1'] = '433 Darlington Ave U';
$fields['bill_to_address_postal_code'] = '28403';
$fields['first_name'] = 'Brent';
$fields['last_name'] = 'Seaver';
$fields['eMonth'] = '09';
$fields['eYear'] = '2028';

// Step 1: Send request to Cybersource silently
$cybersource_response = post_to_cybersource('https://secureacceptance.cybersource.com/silent/pay', $fields);

// Step 2: Capture resulting auto-submitting form response (browser-simulated)
$ipn_data = extract_post_to_ipn($cybersource_response);

?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cybersource POST Replay Result</title>
</head>
<body>
    <h2>📦 POST Data Sent to override_custom_receipt_page</h2>
    <pre><?php print_r($ipn_data); ?></pre>
</body>
</html>
