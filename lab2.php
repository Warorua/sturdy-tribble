<?php
include './includes/functions.php';

$key = $encKey;
$message = 'Card number: 4111111111111111';

$encrypted = encryptMessage($message, $key);
$decrypted = decryptMessage($encrypted, $key);

echo "Encrypted: $encrypted\n";
echo "Decrypted: $decrypted\n";
