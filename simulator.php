<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://pesaflow.ecitizen.go.ke/PaymentAPI/iframev2.1.php',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => array('apiClientID' => '4','serviceID' => '46','billDesc' => 'Vehicle Inquiry','billRefNumber' => 'TIMS-MVR-10374947','currency' => 'KES','amountExpected' => '550','clientName' => 'TIMONA MBURU WAMBUI','clientIDNumber' => '30945371','secureHash' => 'ZmRiODg0NDZhNDIwMzI1MDExZDM0Zjk4NjMzOTMwYmQ1MDlmNjUwMjA3MDQ0MmFkNjhmMDMyODM2YTlmYmMwMQ=='),
  CURLOPT_HTTPHEADER => array(
    'Cookie: PHPSESSID=172.18.187.33:80~jqskcip5kgs55blolh5q5qca45'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
$response = str_replace('src="','src="https://pesaflow.ecitizen.go.ke/PaymentAPI/',$response);
echo $response;
