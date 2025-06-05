<?php
$soapUrl = "http://www.scantech.ltd:8090/AndroidInterface.asmx/GetDataOfVehicle"; // asmx URL of WSDL
$soapUser = "a";  //  username
$soapPassword = "password"; // password

// xml post structure

$xml_post_string = '
                     
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <GetDataOfVehicle xmlns="http://tempuri.org/">
      <UserName>' . $soapUser . '</UserName>
    </GetDataOfVehicle>
  </soap:Body>
</soap:Envelope>';   // data from the form, e.g. some ID number

$headers = array(
    "Content-type: application/x-www-form-urlencoded;charset=\"utf-8\"",
    "Accept: text/xml",
    "Cache-Control: no-cache",
    "Pragma: no-cache",
    "SOAPAction: http://www.scantech.ltd:8090/AndroidInterface.asmx/GetDataOfVehicle",
    "Content-length: " . strlen($xml_post_string),
); //SOAPAction: your op URL

$url = $soapUrl;

// PHP cURL  for https connection with auth
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// converting
$response = curl_exec($ch);
curl_close($ch);
