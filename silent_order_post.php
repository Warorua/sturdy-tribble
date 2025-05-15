<?php
$access_key = 'YOUR_ACCESS_KEY';
$profile_id = 'YOUR_PROFILE_ID';
$secret_key = 'YOUR_SECRET_KEY';
$transaction_uuid = uniqid('txn_', true);
$reference_number = 'INV123456';
$amount = '672.75';
$currency = 'KES';
$datetime = gmdate("Y-m-d\TH:i:s\Z");

$signed_field_names = [
    'access_key',
    'profile_id',
    'transaction_uuid',
    'signed_field_names',
    'unsigned_field_names',
    'signed_date_time',
    'locale',
    'transaction_type',
    'reference_number',
    'amount',
    'currency'
];
$unsigned_field_names = [
    'card_number',
    'card_expiry_date',
    'card_cvn',
    'card_type'
];

$data = [
    'access_key' => $access_key,
    'profile_id' => $profile_id,
    'transaction_uuid' => $transaction_uuid,
    'signed_field_names' => implode(',', $signed_field_names),
    'unsigned_field_names' => implode(',', $unsigned_field_names),
    'signed_date_time' => $datetime,
    'locale' => 'en-us',
    'transaction_type' => 'sale',
    'reference_number' => $reference_number,
    'amount' => $amount,
    'currency' => $currency
];

function sign($data, $secret_key)
{
    ksort($data);
    $signed_data = [];
    foreach ($data as $key => $value) {
        $signed_data[] = $key . '=' . $value;
    }
    $string_to_sign = implode(',', $signed_data);
    return base64_encode(hash_hmac('sha256', $string_to_sign, base64_decode($secret_key), true));
}

$signature = sign($data, $secret_key);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cybersource Payment</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="card shadow-lg p-4">
      <h3 class="mb-4">Secure Payment</h3>
      <form id="cybersourceForm" method="post" action="https://secureacceptance.cybersource.com/silent/pay">
        <?php foreach ($data as $key => $value): ?>
          <input type="hidden" name="<?= $key ?>" value="<?= $value ?>">
        <?php endforeach; ?>
        <input type="hidden" name="signature" value="<?= $signature ?>">

        <div class="mb-3">
          <label class="form-label">Card Number</label>
          <input type="text" class="form-control" name="card_number" required>
        </div>
        <div class="row mb-3">
          <div class="col">
            <label class="form-label">Expiry (MM-YYYY)</label>
            <input type="text" class="form-control" name="card_expiry_date" required>
          </div>
          <div class="col">
            <label class="form-label">CVV</label>
            <input type="text" class="form-control" name="card_cvn" required>
          </div>
        </div>
        <input type="hidden" name="card_type" value="001"> <!-- Visa -->

        <button type="submit" class="btn btn-primary w-100">Pay KES <?= $amount ?></button>
      </form>
      <div id="response" class="mt-4"></div>
    </div>
  </div>

  <script>
    $('#cybersourceForm').on('submit', function (e) {
      e.preventDefault();
      const $form = $(this);
      const $btn = $form.find('button');
      $btn.prop('disabled', true).text('Processing...');

      $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: $form.serialize(),
        success: function (response) {
          $('#response').html('<div class="alert alert-success">Payment submitted. Awaiting processing...</div>');
        },
        error: function (xhr) {
          $('#response').html('<div class="alert alert-danger">Payment failed. Please try again.</div>');
        },
        complete: function () {
          $btn.prop('disabled', false).text('Pay KES <?= $amount ?>');
        }
      });
    });
  </script>
</body>
</html>
