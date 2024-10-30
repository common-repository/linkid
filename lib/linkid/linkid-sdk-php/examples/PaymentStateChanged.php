<?php

require_once('ExampleConfig.php');
require_once('../LinkIDPaymentClient.php');

// get order reference
if (!isset($_REQUEST['orderRef'])) {
    print("<p>Expecting orderRef query param...</p>");
    return;
}
$orderReference = $_REQUEST['orderRef'];

$client = new LinkIDPaymentClient($linkIDHost, $linkIDWSUsername, $linkIDWSPassword);
$linkIDPaymentStatus = $client->getStatus($orderReference);

print("<h2>LinkID Payment Status</h2>");
print("<pre>");
print_r($linkIDPaymentStatus);
print("</pre>");
