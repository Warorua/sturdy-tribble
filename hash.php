<?php

// Received notification data (replace with actual data)
$data = [
    'status' => 'settled',
    'secure_hash' => 'MzA5YzdkODFmZTdiM2E2MmU0NDJjNzZkN2IxOTAxMzkxZjUzNTgyNTU0NjE1MDE3Y2FjNDVkYmUyNDE5ZTJjNA==',
    'phone_number' => '254700000000',
    'payment_date' => '2025-05-30 02:16:03+03:00 EAT Africa/Nairobi',
    'payment_channel' => 'MPESA',
    'invoice_number' => 'WQBGPWRR',
    'invoice_amount' => '5.00',
    'currency' => 'KES',
    'client_invoice_ref' => 'RNH3B02',
    'amount_paid' => '5',
];

$secret_key = 'Wfs+Hjd5Mb1GRCrt0A02gl6tmxqnL61y'; // Replace with your actual secret key
$received_hash = $data['secure_hash'];

// Define possible concatenation combinations
$combinations = [
    'Combination 1' => $data['amount_paid'] . $data['client_invoice_ref'] . $data['currency'] . $data['invoice_amount'] . $data['invoice_number'] . $data['payment_channel'] . $data['payment_date'] . $data['phone_number'] . $data['status'],
    'Combination 2' => $data['status'] . $data['phone_number'] . $data['payment_date'] . $data['payment_channel'] . $data['invoice_number'] . $data['invoice_amount'] . $data['currency'] . $data['client_invoice_ref'] . $data['amount_paid'],
    'Combination 3' => $data['invoice_number'] . $data['amount_paid'] . $data['currency'] . $data['status'],
    'Combination 4' => $data['client_invoice_ref'] . $data['status'] . $data['amount_paid'] . $data['phone_number'],
    'Combination 5' => $data['payment_channel'] . $data['invoice_amount'] . $data['invoice_number'] . $data['payment_date'],
    'Combination 6' => '{"status":"settled","secure_hash":"MzA5YzdkODFmZTdiM2E2MmU0NDJjNzZkN2IxOTAxMzkxZjUzNTgyNTU0NjE1MDE3Y2FjNDVkYmUyNDE5ZTJjNA==","phone_number":"254700000000","payment_reference":[{"payment_reference":"TET6QMYYAK","payment_date":"2025-05-29T23:16:03Z","inserted_at":"2025-05-29T20:16:03","currency":"KES","amount":"5"}],"payment_date":"2025-05-30 02:16:03+03:00 EAT Africa/Nairobi","payment_channel":"MPESA","last_payment_amount":"5","invoice_number":"WQBGPWRR","invoice_amount":"5.00","currency":"KES","client_invoice_ref":"RNH3B02","amount_paid":"5"}',
    'Combination 7' => $data['payment_channel'] . $data['invoice_amount'] . $data['invoice_number'] . $data['payment_date'],
    'Combination 8' => $data['payment_channel'] . $data['invoice_amount'] . $data['invoice_number'] . $data['payment_date'],
    'Combination 9' => $data['payment_channel'] . $data['invoice_amount'] . $data['invoice_number'] . $data['payment_date'],
    'Combination 10' => $data['payment_channel'] . $data['invoice_amount'] . $data['invoice_number'] . $data['payment_date'],
    'Combination 11' => $data['payment_channel'] . $data['invoice_amount'] . $data['invoice_number'] . $data['payment_date'],
    'Combination 12' => $data['payment_channel'] . $data['invoice_amount'] . $data['invoice_number'] . $data['payment_date'],
    'Combination 13' => $data['payment_channel'] . $data['invoice_amount'] . $data['invoice_number'] . $data['payment_date'],
    'Combination 14' => $data['payment_channel'] . $data['invoice_amount'] . $data['invoice_number'] . $data['payment_date'],
    'Combination 15' => $data['payment_channel'] . $data['invoice_amount'] . $data['invoice_number'] . $data['payment_date'],
    'Combination 16' => $data['payment_channel'] . $data['invoice_amount'] . $data['invoice_number'] . $data['payment_date'],
    'Combination 17' => $data['payment_channel'] . $data['invoice_amount'] . $data['invoice_number'] . $data['payment_date'],
    // Add other combinations as per gateway documentation or your guesses
];

$found = false;

foreach ($combinations as $label => $string) {
    $calculated_hash = base64_encode(hash_hmac('sha256', $string, $secret_key, true));

    if (hash_equals($calculated_hash, $received_hash)) {
        echo "Verified with {$label}: {$string}\n";
        $found = true;
        break;
    }
}

if (!$found) {
    echo "No matching combination found for provided secure_hash.\n";
}

?>
