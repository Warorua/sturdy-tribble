<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Cybersource Card Analysis</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/country-state-picker@1.3.0/dist/country-state-picker.min.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <style>
    .card-input { font-size: 1.2rem; padding: 0.75rem; border: 1px solid #ced4da; border-radius: 0.5rem; }
    .section-label { font-weight: 600; font-size: 1.1rem; margin-bottom: 0.5rem; }
    .highlight-box { background: #f8f9fa; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; }
  </style>
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="card shadow p-4">
    <h4 class="mb-4">Cybersource Secure Checkout</h4>
    <form id="cyberForm">
      <div class="row g-3">
        <div class="col-md-12 section-label">💳 Card Information</div>
        <div class="col-md-8"><input type="text" class="form-control card-input" id="CardNo4" placeholder="Card Number (spaced)" value="4246 3153 8031 1140" /></div>
        <div class="col-md-4"><input type="text" class="form-control card-input" id="card_cvn" placeholder="CVV" value="700" /></div>
        <div class="col-md-6"><input type="text" class="form-control card-input" id="eMonth" placeholder="Expiry Month" value="09" /></div>
        <div class="col-md-6"><input type="text" class="form-control card-input" id="eYear" placeholder="Expiry Year" value="2028" /></div>
        <div class="col-md-6"><input type="text" class="form-control card-input" id="card_type" placeholder="Card Type Code (001 = Visa)" value="001" /></div>

        <div class="col-md-12 section-label">📦 Billing Information</div>
        <div class="col-md-6"><input type="text" class="form-control" id="first_name" placeholder="First Name" value="Brent" /></div>
        <div class="col-md-6"><input type="text" class="form-control" id="last_name" placeholder="Last Name" value="Seaver" /></div>
        <div class="col-md-12"><input type="text" class="form-control" id="bill_to_address_line1" placeholder="Street Address" value="433 Darlington Ave U" /></div>
        <div class="col-md-4"><input type="text" class="form-control" id="bill_to_address_city" placeholder="City" value="Wilmington" /></div>
        <div class="col-md-4"><select id="bill_to_address_state" class="form-select"></select></div>
        <div class="col-md-4"><input type="text" class="form-control" id="bill_to_address_postal_code" placeholder="Postal Code" value="28403" /></div>
        <div class="col-md-12"><select id="bill_to_address_country" class="form-select"></select></div>

        <div class="col-md-12 text-center">
          <button type="submit" class="btn btn-success mt-3 w-50">Analyze Card</button>
        </div>
      </div>
    </form>
  </div>

  <div id="loadingSpinner" class="text-center mt-5" style="display:none;">
    <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
    <p class="mt-2 text-muted">Processing transaction securely...</p>
  </div>

  <div id="responseArea" class="mt-4" style="display:none;"></div>
</div>

<script>
  countryStatePicker.load({countrySelector: '#bill_to_address_country', stateSelector: '#bill_to_address_state'});

  $('#cyberForm').on('submit', function(e) {
    e.preventDefault();
    $('#responseArea').hide().html('');
    $('#loadingSpinner').show();

    let data = {
      first_name: $('#first_name').val(),
      last_name: $('#last_name').val(),
      name: $('#first_name').val() + ' ' + $('#last_name').val(),
      CardNo4: $('#CardNo4').val(),
      card_number: $('#CardNo4').val().replace(/\s+/g, ''),
      card_cvn: $('#card_cvn').val(),
      card_type: $('#card_type').val(),
      eMonth: $('#eMonth').val(),
      eYear: $('#eYear').val(),
      card_expiry_date: $('#eMonth').val() + '-' + $('#eYear').val(),
      bill_to_address_line1: $('#bill_to_address_line1').val(),
      bill_to_address_city: $('#bill_to_address_city').val(),
      bill_to_address_state: $('#bill_to_address_state').val(),
      bill_to_address_postal_code: $('#bill_to_address_postal_code').val(),
      bill_to_address_country: $('#bill_to_address_country').val()
    };

    $.post('proxy.php', data, function(response) {
      $('#loadingSpinner').hide();
      $('#responseArea').show();

      if (!response.success) {
        const preview = `<pre class="small bg-light p-3 border rounded">${response.raw_response}</pre>`;
        $('#responseArea').html(`<div class="alert alert-danger"><strong>❌ Error:</strong> ${response.error}<br><strong>Status:</strong> ${response.http_code || 'N/A'}<br><strong>JSON Error:</strong> ${response.json_error || 'N/A'}<hr>${preview}</div>`);
        return;
      }

      const api = response.cybersource_interpretation;
      const bin = response.bin_info;
      let badge = 'secondary', emoji = '⚠️';
      if (api.decision === 'ACCEPT') { badge = 'success'; emoji = '✅'; }
      else if (["REJECT", "DECLINE"].includes(api.decision)) { badge = 'danger'; emoji = '❌'; }

      let html = `<div class="highlight-box">
        <h5>${emoji} Decision: <span class="badge bg-${badge}">${api.decision}</span> <small class="text-muted">(Code: ${api.reason_code})</small></h5>
        <p>${api.reason_code_message || ''}</p>
        <p><strong>CVV:</strong> ${api.cv_code || 'N/A'} - ${api.cv_message || ''}</p>
        <p><strong>AVS:</strong> ${api.avs_code || 'N/A'} - ${api.avs_message || ''}</p>
        <p><strong>PARes:</strong> ${api.pares_status || 'N/A'} - ${api.pares_status_message || ''}</p>
        <p><strong>ECI:</strong> ${api.eci || 'N/A'} - ${api.eci_message || ''}</p>
        <p><strong>CAVV:</strong> ${api.cavv_result_code || 'N/A'} - ${api.cavv_result_message || ''}</p>
        <p><strong>3DS Reason:</strong> ${api['3ds_reason_code'] || 'N/A'} - ${api['3ds_reason_message'] || ''}</p>
        <p><strong>Issuer Risk:</strong> ${api['issuer_insights_code'] || 'N/A'} - ${api['issuer_insights_message'] || ''}</p>
        <p><strong>Auth Response:</strong> ${api.auth_response || 'N/A'} - ${api.auth_response_message || ''}</p>
      </div>`;

      if (bin && bin.bin) {
        html += `<div class="highlight-box">
          <h6 class="text-primary">💳 BIN Metadata</h6>
          <p><strong>BIN:</strong> ${bin.bin}</p>
          <p><strong>Country:</strong> ${bin.country}</p>
          <p><strong>Vendor:</strong> ${bin.vendor}</p>
          <p><strong>Type:</strong> ${bin.type}</p>
          <p><strong>Level:</strong> ${bin.level}</p>
          <p><strong>Bank:</strong> ${bin.bank}</p>
        </div>`;
      }

      $('#responseArea').html(html);
    }, 'json').fail(function(xhr, status) {
      $('#loadingSpinner').hide();
      $('#responseArea').html(`<div class="alert alert-danger">❌ AJAX error: ${status}</div>`);
    });
  });
</script>
</body>
</html>
