<?php
include './includes/functions.php';

$key = $encKey;
$message = 'Hello';

$encrypted = encryptMessage($message, $key);
$decrypted = decryptMessage($encrypted, $key);

echo "Encrypted: $encrypted<br/>";
echo "Decrypted: $decrypted<br/>";
