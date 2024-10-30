<?php

require_once('ExampleConfig.php');
require_once('../LinkIDLTQRClient.php');

// get LTQR reference
if (!isset($_REQUEST['ltqrRef'])) {
    return;
}
$ltqrReference = $_REQUEST['ltqrRef'];

// get client session ID
if (!isset($_REQUEST['clientSessionId'])) {
    return;
}
$clientSessionId = $_REQUEST['clientSessionId'];

// get paymentOrderRef
if (!isset($_REQUEST['paymentOrderRef'])) {
    return;
}
$paymentOrderRef = $_REQUEST['paymentOrderRef'];

$client = new LinkIDLTQRClient($linkIDHost, $linkIDWSUsername, $linkIDWSPassword);
$clientSessions = $client->pull(array($ltqrReference));

print("<h2>LTQR Client Sessions</h2>");

print("<pre>");
print_r($clientSessions);
print("</pre>");
