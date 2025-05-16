<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Cybersource Card Analysis</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/card@2.5.4/dist/card.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/card@2.5.4/dist/card.min.js"></script>
    <style>
        body {
            background: #f8f9fa;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        .form-control::placeholder {
            font-size: 0.9rem;
            color: #aaa;
        }

        .form-control {
            border-radius: 0.5rem;
        }

        .card-wrapper {
            margin: 20px auto;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="card shadow-lg p-4">
            <h4 class="mb-4 text-primary">💳 Cybersource Card Analysis</h4>
            <form id="cyberForm" action="javascript:void(0);" method="post" autocomplete="off">
                <div class="section-title">👤 Personal Info</div>
                <div class="row g-3">
                    <div class="col-md-6"><input type="text" class="form-control" name="first_name" id="first_name" placeholder="First Name" value="Brenter" /></div>
                    <div class="col-md-6"><input type="text" class="form-control" name="last_name" id="last_name" placeholder="Last Name" value="Seaver" /></div>
                    <div class="col-md-12"><input type="text" class="form-control" name="name" id="name" placeholder="Full Name" value="Brenter Seaver" readonly /></div>
                </div>
                <div class="section-title">💳 Card Details</div>
                <div class="card-wrapper visually-hidden"></div>
                <div class="row g-3">
                    <div class="col-md-6"><input type="hidden" class="form-control" name="CardNo4" id="CardNo4" placeholder="Formatted Card Number" value="" readonly /></div>
                    <div class="col-md-6"><input type="text" class="form-control" name="card_number" id="card_number" placeholder="Raw Card Number" value="4246315380311140" /></div>
                    <div class="col-md-4"><input type="text" class="form-control" name="card_cvn" id="card_cvn" placeholder="CVN" value="700" /></div>
                    <div class="col-md-2"><input type="text" class="form-control" name="eMonth" id="eMonth" placeholder="Exp. Month" value="09" /></div>
                    <div class="col-md-2"><input type="text" class="form-control" name="eYear" id="eYear" placeholder="Exp. Year" value="2028" /></div>
                    <div class="col-md-12"><input type="hidden" class="form-control" name="card_expiry_date" id="card_expiry_date" placeholder="Card Expiry Date" value="" readonly /></div>
                </div>
                <div class="section-title">🏠 Billing Info</div>
                <div class="row g-3">
                    <div class="col-md-12"><input type="text" class="form-control" name="bill_to_address_line1" placeholder="Address" value="433 Darlington Ave U" /></div>
                    <div class="col-md-6">
                        <select class="form-select" name="bill_to_address_country" id="countrySelect">
                            <option value="">Select Country</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <select class="form-select" name="bill_to_address_state" id="stateSelect">
                            <option value="">Select State/Province</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <input type="text" class="form-control" name="bill_to_address_postal_code" placeholder="Postal Code" value="28403" />
                    </div>

                    <div class="col-md-4">
                        <select class="form-select" name="bill_to_address_city" id="citySelect">
                            <option value="">Select City</option>
                        </select>
                    </div>

                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-lg btn-primary mt-3 px-5">Analyze Card</button>
                    </div>
                </div>
            </form>
        </div>
        <div id="loadingSpinner" class="text-center mt-5" style="display:none;">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Processing transaction securely...</p>
        </div>
        <div id="responseArea" class="mt-4" style="display:none;"></div>
    </div>
    <script>
        $('#first_name, #last_name').on('input', function() {
            $('#name').val($('#first_name').val() + ' ' + $('#last_name').val());
        });
        $('#card_number').on('input', function() {
            let raw = $(this).val().replace(/\D/g, '');
            $('#CardNo4').val(raw.replace(/(\d{4})(?=\d)/g, '$1 '));
            $(this).val(raw);
        });
        $('#eMonth, #eYear').on('input', function() {
            $('#card_expiry_date').val($('#eMonth').val() + '-' + $('#eYear').val());
        });
        new Card({
            form: '#cyberForm',
            container: '.card-wrapper',
            formSelectors: {
                numberInput: 'input[name="CardNo4"]',
                expiryInput: 'input[name="card_expiry_date"]',
                cvcInput: 'input[name="card_cvn"]',
                nameInput: 'input[name="name"]'
            },
            width: 300,
            formatting: true
        });
        $('#cyberForm').on('submit', function(e) {
            console.log("Form submitted via JS");
            e.preventDefault();
            $('#responseArea').hide().html('');
            $('#loadingSpinner').show();
            const formData = $(this).serializeArray();
            let data = {};
            formData.forEach(field => data[field.name] = field.value);
            console.log("POSTing to proxy.php", data);
            $.post('proxy.php', data, function(response) {
                $('#loadingSpinner').hide();
                $('#responseArea').show();
                console.log("Response received:", response);
                if (!response.success) {
                    const isHtml = response.raw_response && response.raw_response.startsWith('<');
                    const preview = isHtml ?
                        `<iframe style="width:100%;height:400px;border:1px solid #ccc" srcdoc="${response.raw_response.replace(/"/g, '&quot;')}"></iframe>` :
                        `<pre class="small bg-light p-3 border rounded">${response.raw_response}</pre>`;
                    $('#responseArea').html(`
<div class="alert alert-danger">
  <strong>❌ Error:</strong> ${response.error}<br>
  <strong>Status:</strong> ${response.http_code || 'N/A'}<br>
  <strong>JSON Error:</strong> ${response.json_error || 'N/A'}
  <hr>${preview}
</div>`);
                    return;
                }
                const api = response.cybersource_interpretation;
                const bin = response.bin_info;
                let badge = 'secondary',
                    emoji = '⚠️';
                if (api.decision === 'ACCEPT') {
                    badge = 'success';
                    emoji = '✅';
                } else if (["REJECT", "DECLINE"].includes(api.decision)) {
                    badge = 'danger';
                    emoji = '❌';
                }
                let html = `<div class="card shadow p-4 mb-4">
<h5>${emoji} Decision: <span class="badge bg-${badge}">${api.decision}</span> <small class="text-muted">(Reason Code: ${api.reason_code})</small></h5>
<p>${api.reason_code_message || ''}</p>
<strong>Primary Message: ${api.primary_message || ''}</strong>
<hr>
<h6>🔒 CVV</h6><p><strong>${api.cv_code}</strong>: ${api.cv_message || 'N/A'}</p>
<h6>🏠 AVS</h6><p><strong>${api.avs_code}</strong>: ${api.avs_message || 'N/A'}</p>
<h6>🛡️ 3D Secure</h6>
<p><strong>PARes:</strong> ${api.pares_status || 'N/A'} - ${api.pares_status_message || ''}</p>
<p><strong>ECI:</strong> ${api.eci || 'N/A'} - ${api.eci_message || ''}</p>
<p><strong>CAVV:</strong> ${api.cavv_result_code || 'N/A'} - ${api.cavv_result_message || ''}</p>
<p><strong>3DS Reason:</strong> ${api['3ds_reason_code'] || 'N/A'} - ${api['3ds_reason_message'] || ''}</p>
<p><strong>Issuer Risk:</strong> ${api['issuer_insights_code'] || 'N/A'} - ${api['issuer_insights_message'] || ''}</p>
<p><strong>Auth Response:</strong> ${api.auth_response || 'N/A'} - ${api.auth_response_message || ''}</p>
</div>`;
                if (bin && bin.bin) {
                    html += `<div class="card shadow p-4">
<h6 class="text-primary">💳 BIN Metadata</h6>
<ul class="list-group">
  <li class="list-group-item"><strong>BIN:</strong> ${bin.bin}</li>
  <li class="list-group-item"><strong>Country:</strong> ${bin.country}</li>
  <li class="list-group-item"><strong>Vendor:</strong> ${bin.vendor}</li>
  <li class="list-group-item"><strong>Type:</strong> ${bin.type}</li>
  <li class="list-group-item"><strong>Level:</strong> ${bin.level}</li>
  <li class="list-group-item"><strong>Bank:</strong> ${bin.bank}</li>
</ul>
</div>`;
                }
                $('#responseArea').html(html);
            }, 'json').fail(function(xhr, status) {
                $('#loadingSpinner').hide();
                $('#responseArea').html(`<div class="alert alert-danger">❌ AJAX error: ${status}</div>`);
                console.error("AJAX failed:", status, xhr.responseText);
            });
            return false;
        });
    </script>
    <script>
        fetch('scripts/countries_states_cities.json')
            .then(res => res.json())
            .then(data => {
                const countries = data;

                countries.forEach(c => {
                    $('#countrySelect').append(`<option value="${c.iso2}">${c.name}</option>`);
                });

                $('#countrySelect').on('change', function() {
                    const selectedCountry = countries.find(c => c.iso2 === $(this).val());
                    $('#stateSelect').html('<option value="">Select State/Province</option>');
                    $('#citySelect').html('<option value="">Select City</option>');

                    if (!selectedCountry || !selectedCountry.states) return;

                    selectedCountry.states.forEach(s => {
                        $('#stateSelect').append(`<option value="${s.state_code}">${s.name}</option>`);
                    });

                    $('#stateSelect').off('change').on('change', function() {
                        const selectedState = selectedCountry.states.find(s => s.state_code === $(this).val());
                        $('#citySelect').html('<option value="">Select City</option>');
                        if (!selectedState || !selectedState.cities) return;
                        selectedState.cities.forEach(city => {
                            $('#citySelect').append(`<option value="${city.name}">${city.name}</option>`);
                        });
                    });
                });
            })
            .catch(err => {
                console.error("Error loading geo JSON:", err);
            });
    </script>


</body>

</html>