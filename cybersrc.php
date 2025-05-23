<?php
// Enable error reporting (optional, for debugging)
// error_reporting(E_ALL); ini_set('display_errors', 1);

// 1. Capture the POST data from Cybersource
$data = $_POST;  // In the SOP model, the payment form is posted to Cybersource, which then POSTs results here.

// 2. Define lookup tables for interpretations of codes

// Reason Code interpretations (all official Cybersource reason codes)
$reasonCodeMap = [
    100 => "Successful transaction.",
    101 => "Missing required fields in the request.",
    102 => "One or more fields contains invalid data.",
    104 => "Duplicate request (Order reference already used recently).",
    110 => "Partial amount approved (partial authorization).",
    150 => "General system failure (server error at Cybersource).",
    151 => "Server timeout at Cybersource.",
    152 => "Service timeout at Cybersource.",
    154 => "Bad MAC or signature validation error.",
    200 => "Soft decline: AVS check failed (address mismatch).",
    201 => "Declined: Issuing bank questioned the request – call issuer.",
    202 => "Declined: Expired card or expiration date mismatch.",
    203 => "Declined: General card decline (no specific reason).",
    204 => "Declined: Insufficient funds.",
    205 => "Declined: Stolen or lost card.",
    207 => "Declined: Issuer unavailable.",
    208 => "Declined: Inactive card or not allowed for this transaction.",
    209 => "Declined: CVN did not match.",
    210 => "Declined: Credit limit reached.",
    211 => "Declined: Invalid CVN code.",
    220 => "Declined: Generic decline – bank account issue.",
    221 => "Declined: Cardholder on negative file (blacklist).",
    222 => "Declined: Customer’s bank account is frozen.",
    230 => "Soft decline: CVN check failed (CVV mismatch).",
    231 => "Declined: Invalid card number.",
    232 => "Declined: Card type is not accepted by processor.",
    233 => "Declined: General decline by processor.",
    234 => "Declined: Merchant configuration error – contact support.",
    235 => "Declined: Capture amount exceeds original auth amount.",
    236 => "Declined: Processor failure – try again later.",
    237 => "Declined: Authorization already reversed (voided).",
    238 => "Declined: Transaction already settled.",
    239 => "Declined: Transaction amount mismatch.",
    240 => "Declined: Invalid card type or number/card type mismatch.",
    241 => "Declined: Invalid reference request ID.",
    242 => "Declined: No corresponding authorization found.",
    243 => "Declined: Transaction already settled or reversed.",
    246 => "Declined: Capture/credit not voidable (already submitted).",
    247 => "Declined: Credit requested for a voided capture.",
    248 => "Declined: Boleto transaction was declined by processor.",
    250 => "Error: Timeout at payment processor.",
    251 => "Declined: Pinless debit usage limit exceeded.",
    254 => "Declined: Stand-alone credits not allowed for account.",
    268 => "Error: Unable to confirm transaction (contact processor).",
    459 => "Declined: Multiple address matches (address verification).",
    460 => "Declined: Address match not found.",
    461 => "Declined: Unsupported character set in address.",
    474 => "Declined: PIN entry required (PIN data missing for PIN debit).",
    475 => "Payer Authentication required (card enrolled in 3-D Secure).",
    476 => "Payer Authentication failed or unavailable.",
    478 => "Strong Customer Authentication required – use 3-D Secure.",
    480 => "Held for review by Decision Manager (fraud review).",
    481 => "Rejected by Decision Manager (fraud rules triggered).",
    490 => "Declined: Acquirer is not accepting transactions (aggregator).",
    491 => "Declined: Acquirer rejected this transaction.",
    493 => "Declined: Acquirer is rejecting this transaction.",
    494 => "Declined: Internal risk safeguard rejection.",
    520 => "Soft decline: Declined by Smart Authorization settings.",
    700 => "Declined: Customer on Denied Parties List (DPL).",
    701 => "Declined: Restricted country (billing/shipping).",
    702 => "Declined: Email domain country is restricted.",
    703 => "Declined: IP/hostname country is restricted."
];

// Note: Some codes above (e.g., 474) might not occur in typical card-not-present flows but are included for completeness.

// AVS result code interpretations
$avsCodeMap = [
    "Y" => "Street address and 5-digit postal code both match (AVS).",
    "X" => "Street address and 9-digit postal code both match (AVS).",
    "W" => "9-digit postal code matches, street address does not.",
    "Z" => "5-digit postal code matches, street address does not.",
    "A" => "Street address matches, postal code does not.",
    "B" => "Street address matches, postal code not verified (Intl).",
    "C" => "Street address and postal code do not match (Intl).",
    "D" => "Street address and postal code both match (Intl).",
    "M" => "Street address and postal code both match (Intl).",  // D and M are equivalent
    "E" => "AVS data invalid or AVS not allowed for card type.",
    "F" => "Cardholder name does not match, but postal code matches (Amex).",
    "G" => "Non-U.S. issuing bank does not support AVS.",
    "H" => "Cardholder name does not match, but address and postal code match (Amex).",
    "I" => "Address not verified (Intl issuer did not verify).",
    "K" => "Name matches, address and postal code do not (Amex, Enhanced AVS).",
    "L" => "Name and postal code match, address does not (Amex, Enhanced AVS).",
    "O" => "Name and address match, postal code does not (Amex, Enhanced AVS).",
    "N" => "No – street address and postal code do not match.",
    "P" => "Postal code matches, street address not verified (Intl).",
    "R" => "System unavailable or timeout – AVS result not obtained.",
    "S" => "AVS not supported by issuer.",
    "T" => "Name does not match, street address matches (Amex).",
    "U" => "Address information unavailable (issuer or system).",
    "V" => "Name, address, and postal code all match (Amex).",
    "W" => "Street address does not match, 9-digit postal code matches.",  // Already listed above
    "X" => "Street address and 9-digit postal code match.",               // Already listed
    "Y" => "Street address and 5-digit postal code match.",              // Already listed
    "Z" => "Street address does not match, 5-digit postal code matches.", // Already listed
    "1" => "AVS not supported by processor or card type.",
    "2" => "Unrecognized AVS response from processor."
];

// CVN/CVV result code interpretations
$cvnCodeMap = [
    "M" => "CVV/CVN matched.",
    "N" => "CVV did not match.",
    "P" => "CVV was not processed.",
    "S" => "CVV is on card but not provided in request.",
    "U" => "Issuer does not support CVV processing.",
    "X" => "Card network does not support CVV.",
    "D" => "Issuer indicates the transaction is suspicious (CVV check passed but flagged).",
    "I" => "CVV failed data validation (invalid format).",
    "1" => "CVV not supported by processor or card type.",
    "2" => "Unrecognized CVV response from processor.",
    "3" => "No CVV result code returned by processor."
];

// Payer Authentication (3-D Secure) PaRes status interpretations
$paresStatusMap = [
    "Y" => "Successful 3-D Secure authentication (cardholder authenticated; liability shift).",
    "A" => "Attempted authentication (issuer not participating or not available; liability shift applies).",
    "U" => "Authentication unavailable (error or timeout; no liability shift).",
    "N" => "Authentication failed or not successful (cardholder not verified; treat as declined).",
    "R" => "Authentication rejected by issuer (frictionless attempt was rejected; do not proceed).",
    "C" => "Challenge required for 3-D Secure (cardholder must authenticate via challenge)."
];

// ECI (Electronic Commerce Indicator) interpretations for 3-D Secure outcomes
$eciMap = [
    "05" => "ECI 05 – Fully authenticated via 3-D Secure (liability shift).",
    "06" => "ECI 06 – Authentication attempted (liability shift).",
    "07" => "ECI 07 – No 3-D Secure authentication or failed (no liability shift).",
    "02" => "ECI 02 – (Mastercard) Fully authenticated (liability shift).",
    "01" => "ECI 01 – (Mastercard) Authentication attempted (liability shift).",
    "00" => "ECI 00 – (Mastercard) No authentication/failed (no liability shift)."
];

// CAVV Result Code interpretations (if provided by auth response)
$cavvResultMap = [
    "0" => "CAVV not validated (issuer did not attempt or not present).",
    "1" => "CAVV failed validation (incorrect or tampered).",
    "2" => "CAVV passed validation (authentication successful).",
    "3" => "CAVV validation could not be performed (issuer unavailable)."
];
// Note: The above CAVV result codes are illustrative. Actual values may vary by card network. Not all integrations will return a cavvResultCode.

// NEW: auth_response interpretation
$authResponseMap = [
    "00" => "Approved or completed successfully.",
    "08" => "Honor with identification.",
    "10" => "Approved for partial amount.",
    "46" => "Closed account.",
    "51" => "Insufficient funds.",
    "54" => "Expired card.",
    "57" => "Transaction not permitted to cardholder.",
    "62" => "Restricted card.",
    "63" => "Security violation.",
    "65" => "Activity limit exceeded.",
    "70" => "Contact card issuer.",
    "82" => "CVV validation failed.",
    "85" => "No reason to decline.",
    "91" => "Issuer unavailable.",
    "96" => "System malfunction.",
];

// NEW: issuer category code map
$insightsCategoryMap = [
    "00" => "Approve (Low risk)",
    "01" => "Decline (High risk or restricted)",
    "02" => "Review (Issuer recommends checking)",
    "03" => "Decline (Stolen/lost/fraudulent)",
    "04" => "Soft decline (Retry with 3DS)",
    "05" => "Restricted card category"
];

// NEW: 3DS reason code map
$payerAuthReasonMap = [
    "100" => "Authentication successful",
    "101" => "Authentication failed",
    "102" => "Card not enrolled",
    "200" => "Technical problem during authentication",
    "201" => "Timeout at ACS (Access Control Server)",
    "300" => "Invalid input",
    "400" => "Merchant not 3DS-enabled"
];

$output = [];
$output['decision'] = $data['decision'] ?? null;
$output['reason_code'] = $data['reason_code'] ?? null;
$output['reason_code_message'] = $reasonCodeMap[$data['reason_code']] ?? "Unknown reason code.";
$output['transaction_id'] = $data['transaction_id'] ?? null;
$output['primary_message'] = $data['message'] ?? "No Primary Message Given.";

if (isset($data['requestID'])) {
    $output['request_id'] = $data['requestID'];
}

// Authorization response
if (isset($data['auth_response'])) {
    $authResp = $data['auth_response'];
    $output['auth_response'] = $authResp;
    $output['auth_response_message'] = $authResponseMap[$authResp] ?? "Unknown or custom auth response.";
}

// AVS
if (isset($data['ccAuthReply_avsCode'])) {
    $avs = $data['ccAuthReply_avsCode'];
    $output['avs_code'] = $avs;
    $output['avs_message'] = $avsCodeMap[$avs] ?? "Unknown AVS code.";
} elseif (isset($data['auth_avs_code'])) {
    $avs = $data['auth_avs_code'];
    $output['avs_code'] = $avs;
    $output['avs_message'] = $avsCodeMap[$avs] ?? "Unknown AVS code.";
}

// CVV
if (isset($data['ccAuthReply_cvCode'])) {
    $cv = $data['ccAuthReply_cvCode'];
    $output['cv_code'] = $cv;
    $output['cv_message'] = $cvnCodeMap[$cv] ?? "Unknown CVV code.";
} elseif (isset($data['auth_cv_result'])) {
    $cv = $data['auth_cv_result'];
    $output['cv_code'] = $cv;
    $output['cv_message'] = $cvnCodeMap[$cv] ?? "Unknown CVV code.";
}

// 3DS - PARes
if (isset($data['payerAuthValidateReply_paResStatus'])) {
    $pa = $data['payerAuthValidateReply_paResStatus'];
    $output['pares_status'] = $pa;
    $output['pares_status_message'] = $paresStatusMap[$pa] ?? "Unknown PaRes status.";
} elseif (isset($data['payer_authentication_pares_status'])) {
    $pa = $data['payer_authentication_pares_status'];
    $output['pares_status'] = $pa;
    $output['pares_status_message'] = $paresStatusMap[$pa] ?? "Unknown PaRes status.";
}

// ECI
if (isset($data['payerAuthValidateReply_eci']) || isset($data['ccAuthReply_eci'])) {
    $eci = $data['payerAuthValidateReply_eci'] ?? $data['ccAuthReply_eci'];
    $output['eci'] = $eci;
    $output['eci_message'] = $eciMap[$eci] ?? "Unknown ECI value.";
} elseif (isset($data['payer_authentication_eci'])) {
    $eci = $data['payer_authentication_eci'];
    $output['eci'] = $eci;
    $output['eci_message'] = $eciMap[$eci] ?? "Unknown ECI value.";
}

// CAVV
if (isset($data['payerAuthValidateReply_cavvResultCode'])) {
    $cavvCode = $data['payerAuthValidateReply_cavvResultCode'];
    $output['cavv_result_code'] = $cavvCode;
    $output['cavv_result_message'] = $cavvResultMap[$cavvCode] ?? "Unknown CAVV result code.";
} elseif (isset($data['payer_authentication_cavv'])) {
    $output['cavv_result_code'] = 'Provided';
    $output['cavv_result_message'] = 'CAVV received and passed. (Value masked)';
}

// Issuer insights (risk tier)
if (isset($data['auth_insights_response_category_code'])) {
    $cat = $data['auth_insights_response_category_code'];
    $output['issuer_insights_code'] = $cat;
    $output['issuer_insights_message'] = $insightsCategoryMap[$cat] ?? "Unknown or unclassified issuer insight code.";
}

// 3DS reason explanation
if (isset($data['payer_authentication_reason_code'])) {
    $rc = $data['payer_authentication_reason_code'];
    $output['3ds_reason_code'] = $rc;
    $output['3ds_reason_message'] = $payerAuthReasonMap[$rc] ?? "Unknown 3DS reason code.";
}

// Optional: Digital signature
if (isset($data['decision_publicSignature'])) {
    $output['decision_signature'] = $data['decision_publicSignature'];
}

header('Content-Type: application/json');
echo json_encode($output, JSON_PRETTY_PRINT);
