<?php
header('Content-Type: application/json');

// Input validation with fallback BIN
$bin = (isset($_GET['bin']) && preg_match('/^\d{6,}$/', $_GET['bin'])) ? $_GET['bin'] : '519911';

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://bins.su/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => array(
        'action' => 'searchbins',
        'bank' => '',
        'bins' => $bin,
        'country' => ''
    ),
));

$response = curl_exec($curl);
curl_close($curl);

function scrapeFirstBinFromHtml($html) {
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $rows = $xpath->query('//div[@id="result"]/table/tr');

    foreach ($rows as $index => $row) {
        if (!($row instanceof DOMElement)) continue;
        if ($index === 0) continue; // Skip the header row

        $cells = [];
        foreach ($row->childNodes as $cell) {
            if ($cell instanceof DOMElement && $cell->tagName === 'td') {
                $cells[] = trim($cell->textContent);
            }
        }

        if (count($cells) === 6) {
            return [
                'bin'     => $cells[0],
                'country' => $cells[1],
                'vendor'  => $cells[2],
                'type'    => $cells[3],
                'level'   => $cells[4],
                'bank'    => $cells[5]
            ];
        }
    }

    return null;
}

$firstBin = scrapeFirstBinFromHtml($response);

echo json_encode($firstBin ?: ['error' => 'No BIN data found.'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
