<?php
die('This file is not meant to be accessed directly.');

$url = "https://jkuepos.jkuat.ac.ke/api/payment/paynotification";

$payload = [
    "status" => "settled",
    "secure_hash" => "NTk4NGE3NTIxNjk4OTg2MjhmMWZmMzU4NmU4NDBmYmVlYWVlYTMxN2E1MWMwYzg4MTU3YTBmN2Q0NGQ3ZjUyMA==",
    "phone_number" => "254792140424",
    "payment_reference" => [
        [
            "payment_reference" => "TF61PE7SBL",
            "payment_date" => "2025-06-06T07:26:48Z",
            "inserted_at" => "2025-06-06T04:26:49",
            "currency" => "KES",
            "amount" => "1500"
        ]
    ],
    "payment_date" => "2025-06-06 10:26:48+03:00 EAT Africa/Nairobi",
    "payment_channel" => "MPESA",
    "last_payment_amount" => "1500",
    "invoice_number" => "ZXVBXGRD",
    "invoice_amount" => "1500.00",
    "currency" => "KES",
    "client_invoice_ref" => "JKUATFEE17075",
    "amount_paid" => "1500"
];

$jsonData = json_encode($payload);

$headers = [
    "Host: jkuepos.jkuat.ac.ke",
    "Accept-Encoding: gzip",
    "X-Forwarded-Proto: https",
    "X-Real-IP: 197.248.11.129",
    "X-Real-Port: 39505",
    "X-Forwarded-For: 197.248.11.129",
    "X-Forwarded-Port: 443",
    "X-Port: 443",
    "X-LSCACHE: 1",
    "Content-Length: " . strlen($jsonData),
    "User-Agent: hackney/1.20.1",
    "Content-Type: application/json"
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);


$response = curl_exec($ch);
$info = curl_getinfo($ch);
$error = curl_error($ch);
curl_close($ch);

// Optional output for debugging
echo "Response:\n" . $response . "\n";
echo "Info:\n" . print_r($info, true) . "\n";
echo "Error:\n" . $error . "\n";
