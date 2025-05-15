const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: false, defaultViewport: null });
    const page = await browser.newPage();

    // Intercept all POST requests
    page.on('request', async request => {
        const url = request.url();
        const method = request.method();

        if (method === 'POST') {
            const postData = request.postData();
            if (url.includes('ipn.php')) {
                console.log('\n🔥 POST to IPN.PHP Detected');
                console.log('URL:', url);
                console.log('Method:', method);
                console.log('POST Data:');
                const parsed = new URLSearchParams(postData);
                for (const [key, value] of parsed.entries()) {
                    console.log(`${key}\t${value}`);
                }
            }
        }
    });

    // Construct a real HTML form and load it
    const fields = {
        first_name: 'Brent',
        last_name: 'Seaver',
        name: 'Brent Seaver',
        CardNo4: '4246 3153 8031 1140',
        card_number: '4246315380311140',
        card_cvn: '700',
        card_type: '001',
        eMonth: '09',
        eYear: '2028',
        card_expiry_date: '09-2028',
        bill_to_address_line1: '433 Darlington Ave U',
        bill_to_address_city: 'Wilmington',
        bill_to_address_state: 'NC',
        bill_to_address_postal_code: '28403',
        bill_to_address_country: 'US',
        bill_to_email: 'bombardier.devs.master@gmail.com',
        bill_to_phone: '+254756754595',
        bill_to_forename: 'Brent',
        bill_to_surname: 'Seaver',
        currency: 'KES',
        amount: '672.75',
        reference_number: 'KBLFQCU',
        profile_id: 'AE3F228E-9750-4C9E-96A8-709E36BB502B',
        access_key: '972ae9ba01f73c56999e33ba51d7e261',
        transaction_uuid: 'X8VJM3_510',
        transaction_type: 'sale',
        signed_date_time: '2025-05-15T03:10:00Z',
        signed_field_names: 'profile_id,access_key,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,payment_method,transaction_type,reference_number,auth_trans_ref_no,amount,currency,merchant_descriptor,override_custom_receipt_page',
        unsigned_field_names: 'device_fingerprint_id,card_type,card_number,card_expiry_date,card_cvn,bill_to_forename,bill_to_surname,bill_to_email,bill_to_phone,bill_to_address_line1,bill_to_address_line2,bill_to_address_city,bill_to_address_state,bill_to_address_country,bill_to_address_postal_code,customer_ip_address,line_item_count,item_0_code,item_0_sku,item_0_name,item_0_quantity,item_0_unit_price,merchant_defined_data1,merchant_defined_data2,merchant_defined_data3,merchant_defined_data4',
        override_custom_receipt_page: 'https://pesaflow.ecitizen.go.ke/PaymentAPI/Wrappers/Cybersource4/ipn.php',
        payment_method: 'card',
        merchant_descriptor: 'ECITIZEN',
        locale: 'en-us',
        auth_trans_ref_no: 'KBLFQCU',
        signature: 'DHGiKk5Vq8dbV+gxtN3J402A4Rp26xUq5mVoZ3BUyuI='
    };

    // Build HTML content
    let html = '<html><body><form method="POST" id="form" action="https://secureacceptance.cybersource.com/silent/pay">';
    for (const key in fields) {
        html += `<input type="hidden" name="${key}" value="${fields[key]}">`;
    }
    html += '</form><script>document.getElementById("form").submit();</script></body></html>';

    await page.setContent(html);
    console.log('🚀 Submitted form to Cybersource. Waiting for POST to ipn.php...');

    // Wait long enough for IPN POST to happen
    await new Promise(resolve => setTimeout(resolve, 20000));

    await browser.close();
})();
