<?php

// Target URL to forward to
$target = $_GET['target'] ?? '';

// Only allow POST relays
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($target)) {
    http_response_code(400);
    echo json_encode(['error' => 'POST with ?target=https://target.domain/ipn.php required']);
    exit;
}

// Get raw payload
$body = file_get_contents('php://input');

// Forward headers to mimic the original request
$headers = [
    'Content-Type: application/json',
    'Accept-Encoding: gzip',
    'X-Forwarded-Proto: https',
    'X-Real-IP: 197.248.11.129',
    'X-Real-Port: 45247',
    'X-Forwarded-For: 197.248.11.129',
    'X-Forwarded-Port: 443',
    'X-Port: 443',
    'X-LSCACHE: 1',
    'User-Agent: hackney/1.20.1',
    'Host: ' . parse_url($target, PHP_URL_HOST),
    'Content-Length: ' . strlen($body)
];

// Send to target
$ch = curl_init($target);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);
$info = curl_getinfo($ch);
$error = curl_error($ch);
curl_close($ch);

// Output what happened
header('Content-Type: application/json');
echo json_encode([
    'http_code' => $info['http_code'],
    'response' => $response,
    'error' => $error
]);
