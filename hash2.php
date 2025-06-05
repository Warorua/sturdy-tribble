<?php

function flattenData($data) {
    $flat = [];
    foreach ($data as $key => $value) {
        if (is_array($value) && isset($value[0]) && is_array($value[0])) {
            foreach ($value[0] as $subKey => $subValue) {
                $flat["payment_reference_" . $subKey] = $subValue;
            }
        } else {
            $flat[$key] = $value;
        }
    }
    return $flat;
}

function combinations(array $elements, int $length): array {
    if ($length === 1) {
        return array_map(fn($e) => [$e], $elements);
    }
    $combs = [];
    foreach ($elements as $i => $first) {
        $rest = array_slice($elements, $i + 1);
        foreach (combinations($rest, $length - 1) as $comb) {
            array_unshift($comb, $first);
            $combs[] = $comb;
        }
    }
    return $combs;
}

function permutations(array $items): array {
    if (count($items) <= 1) return [$items];
    $result = [];
    foreach ($items as $i => $item) {
        $remaining = $items;
        unset($remaining[$i]);
        foreach (permutations(array_values($remaining)) as $perm) {
            array_unshift($perm, $item);
            $result[] = $perm;
        }
    }
    return $result;
}

// Input Data
$data = [
    'status' => 'settled',
    'secure_hash' => '309c7d81fe7b3a62e442c76d7b1901391f53582554615017cac45dbe2419e2c4',
    'phone_number' => '254700000000',
    'payment_reference' => [
        [
            'payment_reference' => 'TET6QMYYAK',
            'payment_date' => '2025-05-29T23:16:03Z',
            'inserted_at' => '2025-05-29T20:16:03',
            'currency' => 'KES',
            'amount' => '5'
        ]
    ],
    'payment_date' => '2025-05-30 02:16:03+03:00 EAT Africa/Nairobi',
    'payment_channel' => 'MPESA',
    'last_payment_amount' => '5',
    'invoice_number' => 'WQBGPWRR',
    'invoice_amount' => '5.00',
    'currency' => 'KES',
    'client_invoice_ref' => 'RNH3B02',
    'amount_paid' => '5'
];

$secret_key = 'Wfs+Hjd5Mb1GRCrt0A02gl6tmxqnL61y';
$target_hash = strtolower($data['secure_hash']);
$flat = flattenData($data);
unset($flat['secure_hash']);

$keys = array_keys($flat);

foreach (range(4, 8) as $len) {
    foreach (combinations($keys, $len) as $subset) {
        foreach (permutations($subset) as $perm) {
            $joined = implode(',', array_map(fn($k) => $flat[$k], $perm));
            $hmac = hash_hmac('sha256', $joined, $secret_key);
            if ($hmac === $target_hash) {
                echo "MATCH FOUND:\nFields: " . implode(',', $perm) . "\nValues: $joined\n";
                exit;
            }
        }
    }
}

echo "No match found.\n";
