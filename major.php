<?php
// die("This file is not meant to be accessed directly.");
// This file is part of MMKA (Mobile Money Kenya Application).
// MMKA is a web application that allows users to interact with the eCitizen payment system.
// It is designed to fetch invoice details and send notifications via MPESA.
//
// MMKA is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version
class Database
{


    private $server = "mysql:host=srv1140.hstgr.io;dbname=u854855859_redHat";
    private $username = "u854855859_redHat";
    private $password = "ccu*4HhD4^Cm";
    private $options  = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT => true,  // Use persistent connections
    );

    protected $conn;

    public function open()
    {
        try {
            $this->conn = new PDO($this->server, $this->username, $this->password, $this->options);
            return $this->conn;
        } catch (PDOException $e) {
            echo "There is some problem in connection: " . $e->getMessage();
        }
    }

    public function close()
    {
        $this->conn = null;
    }
}

//$dbFile = 'nationPersons.db';
$start = date('Y-m-d H:i:s');
//*


$pdo = new Database();

$conn = $pdo->open();
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

        // if(!$invoice_html) {
        //     echo json_encode(['status' => 'error', 'message' => 'Failed to fetch invoice 2']);
        //     exit;
        // }

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
        } elseif ($invoice_html !== false && preg_match('/<tkash\s+([^>]+)>/i', $invoice_html, $match)) {
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
        }elseif ($invoice_html !== false && preg_match('/<iframe-v3\s+([^>]+)>/i', $invoice_html, $match)) {
            $attributes = [];
            preg_match_all('/(\w+)="([^"]*)"/', $match[1], $pairs, PREG_SET_ORDER);
            foreach ($pairs as $pair) {
                $attributes[$pair[1]] = $pair[2];
            }

            $now = new DateTime("now", new DateTimeZone("Africa/Nairobi"));
            $date_iso = $now->format(DateTime::ATOM);
            $date_custom = $now->format("Y-m-d H:i:sP T e");
            $jsonObj_1 = $attributes[':invoice'] ?? '';
            $obj_1 = json_decode(html_entity_decode($jsonObj_1), true);
            if(isset($obj_1['status'])){
   echo json_encode([
                'status' => 'success',
                'data' => [
                    'amount' => $obj_1['amount_net'] ?? '',
                    'bill_ref' => $obj_1['client_invoice_ref'] ?? '',
                    'invoice_no' => $obj_1['invoice_number'] ?? '',
                    'notification_url' => $obj_1['service']['metadata']['metadata']['webhook_url'] ?? '',
                    'msisdn' => $obj_1['msisdn'] ?? '+254700000000',
                    'date_iso' => $date_iso,
                    'date_custom' => $date_custom
                ]
            ]);
            exit;
            }else{
echo json_encode(['status' => 'error', 'message' => 'iFrame 3 Loaded. No Object. ::: <br/> '.json_encode($obj_1).'<br/>::::<br/>'.$jsonObj_1.'<br/>::::<br/>'.json_encode($attributes)]);
                exit;
            }


         
        }else {

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://pesaflow.ecitizen.go.ke/PaymentAPI/getStatus.php?billRefNumber=' . $code,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HTTPHEADER => array(
                    'Cookie: PHPSESSID=172.18.187.33:80~5jr4e98f92oan1930o269rpa70'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $oldInvoice = json_decode($response, true);
            if ($oldInvoice && isset($oldInvoice['status']) && $oldInvoice['billRefNumber'] === $code) {
                // Process the successful invoice details
                $html = '<ul class="list-group list-group-flush">';
                $html .= '<li class="list-group-item list-group-item-success"><strong>---|OLD-SYSTEM|---</strong></li>';
                $html .= '<li class="list-group-item list-group-item-success"><strong>Status:</strong> ' . htmlspecialchars($oldInvoice['status'] ?? '') . '</li>';
                $html .= '<li class="list-group-item list-group-item-success"><strong>Reference:</strong> ' . htmlspecialchars($oldInvoice['reference'] ?? '') . '</li>';
                $html .= '<li class="list-group-item list-group-item-success"><strong>Service ID:</strong> ' . htmlspecialchars($oldInvoice['serviceID'] ?? '') . '</li>';
                $html .= '<li class="list-group-item list-group-item-success"><strong>Amount:</strong> ' . htmlspecialchars($oldInvoice['amount'] ?? '') . '</li>';
                $html .= '<li class="list-group-item list-group-item-success"><strong>Bill Ref Number:</strong> ' . htmlspecialchars($oldInvoice['billRefNumber'] ?? '') . '</li>';
                $html .= '<li class="list-group-item list-group-item-success"><strong>Service:</strong> ' . htmlspecialchars($oldInvoice['service'] ?? '') . '</li>';
                $html .= '<li class="list-group-item list-group-item-success"><strong>Account Number:</strong> ' . htmlspecialchars($oldInvoice['account_number'] ?? '') . '</li>';
                $html .= '</ul>';

                echo json_encode([
                    'status' => 'error',
                    'message' => $html
                ]);
                exit;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to extract invoice details. Neither new nor old system returned valid data.']);
                exit;
            }
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
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // Max seconds to wait for connection
curl_setopt($ch, CURLOPT_TIMEOUT, 20);        // Max seconds to allow cURL to execute

        $response = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);


        if ($http_status === 200 || $http_status === 201 && $response) {
            $dataToInsert = array(
                "invoice_no" => $invoice_no,
                "amount" => $amount,
                'client' => $client,
                'ref' => $notification_url,
                'route' => $notification_url,
                'extdoc' => $bill_ref
                // Add more columns and values as needed
            );
            $tableName = 'bypass';
            // Call the insert method
            $stmt = $conn->prepare('INSERT INTO bypass (invoice_no, amount, client, ref, route, extdoc) VALUES (:invoice_no, :amount, :client, :ref, :route, :extdoc)');
            $stmt->execute($dataToInsert);
            $dt1 = "Data inserted successfully recorded.";
        } else {
            $dt1 = "Insertion Not Done!";
        }

        if ($http_status === 200) {
            echo json_encode([
                'status' => $http_status === 200 ? 'success' : 'error',
                'message' => $response . '<br/>' . $dt1
            ]);
        } elseif ($http_status === 201) {
            echo json_encode([
                'status' => $http_status === 201 ? 'success' : 'error',
                'message' => $response . '<br/>' . $dt1
            ]);
        } else {
            echo json_encode([
                'status' => $http_status === 200 ? 'success' : 'error',
                'message' => $response . '<br/>' . $dt1 . '<br/> Status Code: ' . $http_status . '<br/><br/>' . json_encode($payload, JSON_PRETTY_PRINT)
            ]);
        }
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



        <form id="notifyForm" class="mt-4">
            <h5 class="mb-3">Form A - Details</h5>
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
                        <label for="exampleInputtext1" class="form-label">Use Client</label>
                        <select class="form-select" name="client" aria-label="Default select example">
                            <?php
                            $stmt = $conn->prepare('SELECT * FROM clients ORDER BY name ASC');
                            $stmt->execute();
                            $clients = $stmt->fetchAll();
                            foreach ($clients as $rows) {
                                if ($rows['id'] == '2') {
                                    $attr = 'selected';
                                } else {
                                    $attr = '';
                                }
                                echo '<option value="' . $rows['name'] . '" ' . $attr . '>' . $rows['name'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>


            <button type="submit" class="btn btn-success">Submit Notification</button>
        </form>
        <div class="mt-3">
            <div id="result"></div>
        </div>
    </div>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Loader modal instance
        const loaderModal = new bootstrap.Modal(document.getElementById('loaderModal'), {
            backdrop: 'static',
            keyboard: false
        });

        function showLoader() {
            loaderModal.show();
        }

        function hideLoader() {
            loaderModal.hide();
        }

        $(function() {
            $('#fetchForm').on('submit', function(e) {
                showLoader();
            });
            $('#notifyForm').on('submit', function(e) {
                showLoader();
            });

        });
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

        // Hide loader on AJAX complete
        $(document).ajaxStop(function() {
            hideLoader();
        });
    </script>


</body>


</html>