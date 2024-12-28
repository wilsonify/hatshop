<?php
session_start();

if (empty($_GET['step'])) {
    require_once 'include/config.php';
    require_once BUSINESS_DIR . 'datacash_request.php';

    $request = new DataCashRequest(DATACASH_URL);
    $request->MakeXmlPre(DATACASH_CLIENT, DATACASH_PASSWORD,
                         8880000 + random_int(0, 10000), 49.99, 'GBP',
                         'pre', '3528000000000007', '11/08');

    $request_xml = $request->GetRequest();
    $_SESSION['pre_request'] = $request_xml;

    $response_xml = $request->GetResponse();
    $_SESSION['pre_response'] = $response_xml;

    $xml = simplexml_load_string($response_xml);
    $request->MakeXmlFulfill(DATACASH_CLIENT, DATACASH_PASSWORD,
                             'fulfill', $xml->merchantreference,
                             $xml->datacash_reference);

    $response_xml = $request->GetResponse();
    $_SESSION['fulfill_response'] = $response_xml;
} else {
    header('Content-type: text/xml');

    switch ($_GET['step']) {
        case 1:
            print $_SESSION['pre_request'];
            break;
        case 2:
            print $_SESSION['pre_response'];
            break;
        case 3:
            print $_SESSION['fulfill_response'];
            break;
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DataCash Test</title>
    <style>
        /* Container for the steps */
        .steps-container {
            display: flex;
            width: 100%;
            height: 100vh;
        }

        /* Individual step container */
        .step {
            flex: 1;
            height: 100%;
            border: 1px solid #ddd; /* optional border for styling */
            overflow: hidden;
        }

        /* Add iframe styling to ensure proper scaling */
        .step iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
</head>
<body>
    <div class="steps-container">
        <div class="step">
            <iframe src="test_datacash.php?step=1" title="Step 1 Content"></iframe>
        </div>
        <div class="step">
            <iframe src="test_datacash.php?step=2" title="Step 2 Content"></iframe>
        </div>
        <div class="step">
            <iframe src="test_datacash.php?step=3" title="Step 3 Content"></iframe>
        </div>
    </div>
</body>
</html>
