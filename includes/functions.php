<?php
require_once 'vendor/autoload.php';

use Faker\Factory;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;

function generateE164Phone($countryCode = 'KE')
{
   $faker = Factory::create(strtolower($countryCode)); // e.g., en_KE
   $phoneUtil = PhoneNumberUtil::getInstance();

   // Generate a local-looking number
   $rawNumber = $faker->phoneNumber;

   try {
      $numberProto = $phoneUtil->parse($rawNumber, $countryCode);
      return $phoneUtil->format($numberProto, PhoneNumberFormat::E164);
   } catch (\libphonenumber\NumberParseException $e) {
      return null;
   }
}


function getCardTypeCode($cardNumber)
{
   $cardNumber = preg_replace('/\D/', '', $cardNumber); // Remove non-digits

   $cardTypes = [
      'visa' => ['/^4[0-9]{12}(?:[0-9]{3})?$/', '001'],
      'mastercard' => ['/^5[1-5][0-9]{14}|^2[2-7][0-9]{14}$/', '002'],
      'amex' => ['/^3[47][0-9]{13}$/', '003'],
      'dankort' => ['/^5019[0-9]{12}$/', '034'],
      'hipercard' => ['/^606282|^3841(?:[0|4|6]{1})0/', '050'],
      'dinersclub' => ['/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/', '005'],
      'discover' => ['/^6(?:011|5[0-9]{2})[0-9]{12}$/', '004'],
      'jcb' => ['/^(?:2131|1800|35\d{3})\d{11}$/', '007'],
      'maestro' => ['/^(5[06-9]|6[37])[0-9]{10,17}$/', '042'],
      'unionpay' => ['/^62[0-9]{14,17}$/', '062'],
      'visaelectron' => ['/^(4026|417500|4508|4844|4913|4917)\d+$/', '001'],
      'elo' => ['/^((((636368)|(438935)|(504175)|(451416)|(636297))\d{10})|((5067)|(4576)|(4011))\d{12})$/', '054'],
      'aura' => ['/^5078\d{2}\d{10}$/', '051'],
      'uatp' => ['/^1[0-9]{14}$/', '040'],
   ];

   foreach ($cardTypes as $type => [$pattern, $code]) {
      if (preg_match($pattern, $cardNumber)) {
         return $code;
      }
   }

   return null; // Unknown card type
}

function loadDbIpLite($csvPath)
{
   $ipRanges = [];
   $handle = fopen($csvPath, 'r');
   while (($row = fgetcsv($handle)) !== false) {
      [$startIp, $endIp, $country] = $row;
      $ipRanges[$country][] = [
         'start' => ip2long($startIp),
         'end' => ip2long($endIp)
      ];
   }
   fclose($handle);
   return $ipRanges;
}

function getRandomIpForCountry($countryCode, $ipRanges)
{
   if (!isset($ipRanges[$countryCode])) return null;

   $range = $ipRanges[$countryCode][array_rand($ipRanges[$countryCode])];
   return long2ip(mt_rand($range['start'], $range['end']));
}

function generateRandomId()
{
   $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
   $length = 27;
   $result = '';

   for ($i = 0; $i < $length; $i++) {
      $result .= $chars[random_int(0, strlen($chars) - 1)];
   }

   return $result;
}

function reorderArray(array $input, array $order)
{
   $result = [];

   foreach ($order as $key) {
      if (array_key_exists($key, $input)) {
         $result[$key] = $input[$key];
      }
   }

   // Optional: add any leftover keys not in the order list
   foreach ($input as $key => $value) {
      if (!array_key_exists($key, $result)) {
         $result[$key] = $value;
      }
   }

   return $result;
}

function formatStateCode($stateCode) {
    // Check if the state code is numeric
    if (is_numeric($stateCode)) {
        // Convert to integer and format with leading zeros
        return str_pad((int)$stateCode, 3, '0', STR_PAD_LEFT);
    }
    // Return the state code as-is if it's not numeric
    return $stateCode;
}

function formatCardNumberByTypeCode($cardNumber) {
   $cardNumber = preg_replace('/\D/', '', $cardNumber); // Clean
   $typeCode = getCardTypeCode($cardNumber);

   switch ($typeCode) {
       case '003': // Amex → 4-6-5
           return substr($cardNumber, 0, 4) . ' ' .
                  substr($cardNumber, 4, 6) . ' ' .
                  substr($cardNumber, 10, 5);

       case '005': // Diners Club → 4-6-4
           return substr($cardNumber, 0, 4) . ' ' .
                  substr($cardNumber, 4, 6) . ' ' .
                  substr($cardNumber, 10, 4);

       case '042': // Maestro → often 4-4-4-4 but can be longer
       case '001': // Visa / Visa Electron
       case '002': // Mastercard
       case '004': // Discover
       case '007': // JCB
       case '054': // Elo
       case '050': // Hipercard
       case '034': // Dankort
           return trim(chunk_split($cardNumber, 4, ' ')); // Default 4-4-4-4...

       default: // Unknown format
           return $cardNumber;
   }
}

function encryptMessage($plaintext, $key) {
   $method = 'AES-256-CBC';
   $key = hash('sha256', $key, true); // Ensure 256-bit key
   $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));

   $ciphertext = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv);
   return base64_encode($iv . $ciphertext);
}

function decryptMessage($ciphertextBase64, $key) {
   $method = 'AES-256-CBC';
   $key = hash('sha256', $key, true);

   $ciphertextRaw = base64_decode($ciphertextBase64);
   $ivLength = openssl_cipher_iv_length($method);
   $iv = substr($ciphertextRaw, 0, $ivLength);
   $ciphertext = substr($ciphertextRaw, $ivLength);

   return openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
}


$ipRanges = loadDbIpLite('scripts/dbip-country-lite-2025-05.csv');

$targetOrder = [
   "first_name",
   "last_name",
   "CardNo4",
   "card_cvn",
   "eMonth",
   "eYear",
   "name",
   "card_number",
   "card_type",
   "card_expiry_date",
   "bill_to_forename",
   "bill_to_surname",
   "device_fingerprint_id",
   "bill_to_address_line2",
   "bill_to_phone",
   "bill_to_email",
   "customer_ip_address",
   "bill_to_address_country",
   "bill_to_address_state",
   "bill_to_address_city",
   "bill_to_address_line1",
   "bill_to_address_postal_code",
   "anchor"
];

$encKey = "ViNWU5MmJhMjMwZTI5ZDM4ZjUyYzBmOWZmZmU4ZTIyNzU0YmUxMjFiYTg1MjQ1ZGM3MDBi";