<?php
include './includes/functions.php';

header('Content-Type: application/json');

if (!isset($_GET['obj'])) {
    $data = [];
    $data['first_name'] = 'Brent';
    $data['last_name'] = 'Seaver';
    $data['name'] = 'Brent Seaver';
    $data['CardNo4'] = '4246 3153 8031 1140';
    $data['card_number'] = '4246315380311140';
    $data['card_cvn'] = '700';
    $data['card_type'] = '001';
    $data['eMonth'] = '09';
    $data['eYear'] = '2028';
    $data['card_expiry_date'] = '09-2028';
    $data['bill_to_address_line1'] = '433 Darlington Ave U';
    $data['bill_to_address_city'] = 'Wilmington';
    $data['bill_to_address_state'] = 'NC';
    $data['bill_to_address_postal_code'] = '28403';
    $data['bill_to_address_country'] = 'US';
    $data['anchor'] = 'amountExpected=650.00&apiClientID=1&billDesc=OFFICIAL%20SEARCH%20(%22CR12%22)&billRefNumber=X8VJM3&callBackURLOnFail=https%3A%2F%2Fbrs.ecitizen.go.ke%2Fpayments%2FX8VJM3%2Fcallback%2Ffailed&callBackURLOnSuccess=https%3A%2F%2Fbrs.ecitizen.go.ke%2Fpayments%2FX8VJM3%2Fcallback%2Fsuccess&clientEmail=bombardier.devs.master%40gmail.com&clientIDNumber=4917833&clientMSISDN=%2B254756754595&clientName=GODFREY%20GITAU%20NGURE&currency=KES&notificationURL=https%3A%2F%2Fbrs.ecitizen.go.ke%2Fapi%2Fpayments%2Fpesaflow-ipn&secureHash=ODAwNTBjZjQzMWE4NzZmMjNhZDE4M2E1OTJiMzFjNGZmMzU2YTUwN2ZlOTFiMDVkMmEyMmMzOTliMDcwNzkxYQ%3D%3D&serviceID=42&clientType=1';
} else {

    $decrypted = decryptMessage($_GET['obj'], $encKey);

    if ($decrypted === false) {
        $errMsg = "Decryption failed: wrong key or corrupted data : ".base64_encode($_GET['obj']);
        $logger->critical($errMsg);
        die(json_encode(["error" => $errMsg], JSON_PRETTY_PRINT));
    } else {
        //echo "Decrypted message: $decrypted";
        $errMsg = "Decryption success : ".base64_encode($_GET['obj']);
        $logger->info($errMsg);
        $data = json_decode(base64_decode($decrypted, true), true);
        $data['anchor'] = 'amountExpected=650.00&apiClientID=1&billDesc=OFFICIAL%20SEARCH%20(%22CR12%22)&billRefNumber=X8VJM3&callBackURLOnFail=https%3A%2F%2Fbrs.ecitizen.go.ke%2Fpayments%2FX8VJM3%2Fcallback%2Ffailed&callBackURLOnSuccess=https%3A%2F%2Fbrs.ecitizen.go.ke%2Fpayments%2FX8VJM3%2Fcallback%2Fsuccess&clientEmail=bombardier.devs.master%40gmail.com&clientIDNumber=4917833&clientMSISDN=%2B254756754595&clientName=GODFREY%20GITAU%20NGURE&currency=KES&notificationURL=https%3A%2F%2Fbrs.ecitizen.go.ke%2Fapi%2Fpayments%2Fpesaflow-ipn&secureHash=ODAwNTBjZjQzMWE4NzZmMjNhZDE4M2E1OTJiMzFjNGZmMzU2YTUwN2ZlOTFiMDVkMmEyMmMzOTliMDcwNzkxYQ%3D%3D&serviceID=42&clientType=1';
    }
}

function pullData($data)
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
        CURLOPT_POSTFIELDS => $data['anchor'],
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function extractFields($html, $data)
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

    foreach ($data as $id => $row) {
        $fields[$id] = $row;
    }
    // Add required hardcoded fields
    // $fields['first_name'] = 'Brent';
    // $fields['last_name'] = 'Seaver';
    // $fields['name'] = 'Brent Seaver';
    // $fields['CardNo4'] = '4246 3153 8031 1140';
    // $fields['card_number'] = '4246315380311140';
    // $fields['card_cvn'] = '700';
    // $fields['card_type'] = '001';
    // $fields['eMonth'] = '09';
    // $fields['eYear'] = '2028';
    // $fields['card_expiry_date'] = '09-2028';
    // $fields['bill_to_address_line1'] = '433 Darlington Ave U';
    // $fields['bill_to_address_city'] = 'Wilmington';
    // $fields['bill_to_address_state'] = 'NC';
    // $fields['bill_to_address_postal_code'] = '28403';
    // $fields['bill_to_address_country'] = 'US';

    return $fields;
}

$html = pullData($data);
$fields = extractFields($html, $data);
echo json_encode($fields, JSON_PRETTY_PRINT);
