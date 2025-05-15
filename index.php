<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Cybersource Card Analysis</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>

  <style>
    body {
      background: #f5f9fc;
      font-family: 'Inter', sans-serif;
    }
    .form-section {
      margin-bottom: 2rem;
      padding: 1.5rem;
      border: 1px solid #ccc;
      border-radius: 10px;
      background: #fff;
    }
    #card-wrapper {
      margin-bottom: 2rem;
    }
    #resultsBox {
      display: none;
    }
  </style>
</head>
<body>
<div class="container py-5">
  <h3 class="mb-4 text-center">Cybersource Card Analysis</h3>

  <div class="form-section">
    <h5>Card Details</h5>
    <div id="card-wrapper"></div>
    <form id="cyberForm">
      <div class="row g-3">
        <div class="col-md-6">
          <input type="text" class="form-control" name="first_name" id="first_name" placeholder="First Name" required>
        </div>
        <div class="col-md-6">
          <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Last Name" required>
        </div>
        <div class="col-md-12">
          <input type="text" class="form-control" name="card_number" id="card_number" placeholder="Card Number" required>
        </div>
        <div class="col-md-4">
          <input type="text" class="form-control" name="card_cvn" placeholder="CVV" required>
        </div>
        <div class="col-md-4">
          <input type="text" class="form-control" name="eMonth" id="eMonth" placeholder="MM" required>
        </div>
        <div class="col-md-4">
          <input type="text" class="form-control" name="eYear" id="eYear" placeholder="YYYY" required>
        </div>

        <!-- Hidden fields auto-filled by JS -->
        <input type="hidden" name="name" id="full_name">
        <input type="hidden" name="CardNo4" id="CardNo4">
        <input type="hidden" name="card_expiry_date" id="card_expiry_date">
        <input type="hidden" name="card_type" id="card_type">

      </div>
  </div>

  <div class="form-section">
    <h5>Billing Address</h5>
    <div class="row g-3">
      <div class="col-md-12">
        <input type="text" class="form-control" name="bill_to_address_line1" placeholder="Street Address" required>
      </div>
      <div class="col-md-4">
        <input type="text" class="form-control" name="bill_to_address_city" placeholder="City" required>
      </div>
      <div class="col-md-4">
        <select class="form-control select2" name="bill_to_address_state" id="state" required></select>
      </div>
      <div class="col-md-4">
        <input type="text" class="form-control" name="bill_to_address_postal_code" placeholder="Postal Code" required>
      </div>
      <div class="col-md-6">
        <select class="form-control select2" name="bill_to_address_country" id="country" required></select>
      </div>
      <div class="col-md-12 text-center">
        <button type="submit" class="btn btn-primary mt-3">Analyze Card</button>
      </div>
    </div>
    </form>
  </div>

  <div id="resultsBox" class="form-section">
    <h5>Analysis Results</h5>
    <pre id="results" class="bg-light p-3 rounded"></pre>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/card@2.5.6/lib/js/card.min.js"></script>
<script>
  $(document).ready(function () {
    $('#country').select2({
      placeholder: 'Select Country',
      ajax: {
        url: 'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/countries.json',
        dataType: 'json',
        processResults: data => ({ results: data.map(c => ({ id: c.iso2, text: c.name })) })
      }
    });

    $('#country').on('change', function () {
      const countryCode = $(this).val();
      $('#state').empty();
      $.getJSON(`https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/states.json`, function (data) {
        const states = data.filter(s => s.country_code === countryCode);
        const options = states.map(s => `<option value="${s.name}">${s.name}</option>`);
        $('#state').html(options);
      });
    });

    new Card({
      form: '#cyberForm',
      container: '#card-wrapper',
      formSelectors: {
        numberInput: 'input[name="card_number"]',
        expiryInput: 'input[name="eMonth"], input[name="eYear"]',
        cvcInput: 'input[name="card_cvn"]',
        nameInput: 'input[name="first_name"]'
      },
      formatting: true
    });

    $('#cyberForm').on('submit', function (e) {
      e.preventDefault();
      $('#full_name').val($('#first_name').val() + ' ' + $('#last_name').val());
      $('#CardNo4').val($('#card_number').val().replace(/(\d{4})(?=\d)/g, "$1 "));
      $('#card_expiry_date').val($('#eMonth').val() + '-' + $('#eYear').val());

      const formData = $(this).serialize();
      $.post('proxy.php', formData, function (res) {
        if (res.success) {
          $('#results').html(JSON.stringify(res, null, 2));
          $('#resultsBox').show();
        } else {
          $('#results').html('Error: ' + res.error);
          $('#resultsBox').show();
        }
      }, 'json');
    });
  });
</script>
</body>
</html>
