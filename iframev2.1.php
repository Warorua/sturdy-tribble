<?php

header('Content-Type: application/json');

function pullData()
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://pesaflow.ecitizen.go.ke/PaymentAPI/iframev2.1.php',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => 'amountExpected=650.00&apiClientID=1&billDesc=OFFICIAL%20SEARCH%20(%22CR12%22)&billRefNumber=X8VJM3&callBackURLOnFail=https%3A%2F%2Fbrs.ecitizen.go.ke%2Fpayments%2FX8VJM3%2Fcallback%2Ffailed&callBackURLOnSuccess=https%3A%2F%2Fbrs.ecitizen.go.ke%2Fpayments%2FX8VJM3%2Fcallback%2Fsuccess&clientEmail=bombardier.devs.master%40gmail.com&clientIDNumber=4917833&clientMSISDN=%2B254756754595&clientName=GODFREY%20GITAU%20NGURE&currency=KES&notificationURL=https%3A%2F%2Fbrs.ecitizen.go.ke%2Fapi%2Fpayments%2Fpesaflow-ipn&secureHash=ODAwNTBjZjQzMWE4NzZmMjNhZDE4M2E1OTJiMzFjNGZmMzU2YTUwN2ZlOTFiMDVkMmEyMmMzOTliMDcwNzkxYQ%3D%3D&serviceID=42&clientType=1',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function extractFields($html)
{
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($html);
    libxml_clear_errors();

    $xpath = new DOMXPath($doc);
    $inputs = $xpath->query('//form//input[@name]');
    $fields = [];

    foreach ($inputs as $input) {
        if ($input instanceof DOMElement) {
            $name = $input->getAttribute('name');
            $value = $input->getAttribute('value');
            if (trim($value) !== '') {
                $fields[$name] = $value;
            }
        }
    }

    // Add required hardcoded fields
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

    return $fields;
}

$html = pullData();
$fields = extractFields($html);
echo json_encode($fields, JSON_PRETTY_PRINT);
