<?php

$curl = curl_init();

// $pstFilds = [
//     'apiClientID' => '4',
//     'serviceID' => '46',
//     'billDesc' => 'Vehicle Inquiry',
//     'billRefNumber' => 'TIMS-MVR-10374947',
//     'currency' => 'KES',
//     'amountExpected' => '550',
//     'clientName' => 'TIMONA MBURU WAMBUI',
//     'clientIDNumber' => '30945371',
//     'secureHash' => 'ZmRiODg0NDZhNDIwMzI1MDExZDM0Zjk4NjMzOTMwYmQ1MDlmNjUwMjA3MDQ0MmFkNjhmMDMyODM2YTlmYmMwMQ=='
// ];

$pstFilds = [
    'apiClientID' => '1',
    'serviceID' => '29',
    'billDesc' => 'Parking Services',
    'billRefNumber' => 'PDE6JO8',
    'currency' => 'KES',
    'amountExpected' => '2',
    'clientName' => 'Godfrey Gitau Ngure',
    'clientIDNumber' => '4917833',
    'secureHash' => 'OWFhMDZkODQxZGZkNmU0OTI4MzJkYTY1NjllYzYyMTZlYTRhZjU4NjVkMzQ1YzNlOWRjYzVlYmQ5NTlkNzBmNA=='
];

$pstFilds = [
    'apiClientID' => '1',
    'serviceID' => '29',
    'billDesc' => 'Advertisement Services',
    'billRefNumber' => 'KO8R6LB',
    'currency' => 'KES',
    'amountExpected' => '2',
    'clientName' => 'Godfrey Gitau Ngure',
    'clientIDNumber' => '4917833',
    'secureHash' => 'ZTgxMmViNWU5MmJhMjMwZTI5ZDM4ZjUyYzBmOWZmZmU4ZTIyNzU0YmUxMjFiYTg1MjQ1ZGM3MDBiZDE2MzgyMw=='
];

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://pesaflow.ecitizen.go.ke/PaymentAPI/iframev2.1.php',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $pstFilds,
    CURLOPT_HTTPHEADER => array(
        'Cookie: PHPSESSID=172.18.187.33:80~jqskcip5kgs55blolh5q5qca45'
    ),
));

$response = curl_exec($curl);

curl_close($curl);
$response = str_replace('src="', 'src="https://pesaflow.ecitizen.go.ke/PaymentAPI/', $response);
echo $response;
