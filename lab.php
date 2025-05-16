<?php
   $data = [];
   // $data['first_name'] = 'Brenter';
   // $data['last_name'] = 'Seaver';
   // $data['name'] = 'Brenter Seaver';
   // $data['CardNo4'] = '4246 3153 8031 1140';
   // $data['card_number'] = '4246315380311140';
   // $data['card_cvn'] = '700';
   // $data['card_type'] = '001';
   // $data['eMonth'] = '09';
   // $data['eYear'] = '2028';
   // $data['card_expiry_date'] = '09-2028';
   // $data['bill_to_address_line1'] = '433 Darlington Ave U';
   // $data['bill_to_address_city'] = 'Wilmington';
   // $data['bill_to_address_state'] = 'NC';
   // $data['bill_to_address_postal_code'] = '28403';
   // $data['bill_to_address_country'] = 'US';

   $data['first_name'] = 'Alex';
   $data['last_name'] = 'Warorua';
   $data['name'] = $data['first_name'].' '.$data['last_name'];
   $data['CardNo4'] = '4251 9973 6729 7160';
   $data['card_number'] = str_replace(' ', '', $data['CardNo4']);
   $data['card_cvn'] = '953';
   $data['card_type'] = '001';
   $data['eMonth'] = '10';
   $data['eYear'] = '2028';
   $data['card_expiry_date'] = $data['eMonth'].'-'.$data['eYear'];
   $data['bill_to_address_line1'] = 'Nairobi';
   $data['bill_to_address_city'] = 'Nairobi';
   $data['bill_to_address_state'] = 'Nairobi';
   $data['bill_to_address_postal_code'] = '00100';
   $data['bill_to_address_country'] = 'KE';
   $data['anchor'] = 'amountExpected=650.00&apiClientID=1&billDesc=OFFICIAL%20SEARCH%20(%22CR12%22)&billRefNumber=X8VJM3&callBackURLOnFail=https%3A%2F%2Fbrs.ecitizen.go.ke%2Fpayments%2FX8VJM3%2Fcallback%2Ffailed&callBackURLOnSuccess=https%3A%2F%2Fbrs.ecitizen.go.ke%2Fpayments%2FX8VJM3%2Fcallback%2Fsuccess&clientEmail=bombardier.devs.master%40gmail.com&clientIDNumber=4917833&clientMSISDN=%2B254756754595&clientName=GODFREY%20GITAU%20NGURE&currency=KES&notificationURL=https%3A%2F%2Fbrs.ecitizen.go.ke%2Fapi%2Fpayments%2Fpesaflow-ipn&secureHash=ODAwNTBjZjQzMWE4NzZmMjNhZDE4M2E1OTJiMzFjNGZmMzU2YTUwN2ZlOTFiMDVkMmEyMmMzOTliMDcwNzkxYQ%3D%3D&serviceID=42&clientType=1';

echo base64_encode(json_encode($data));




