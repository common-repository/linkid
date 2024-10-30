<?php

require_once('../../LinkIDMandateClient.php');
require_once('../ExampleConfig.php');

date_default_timezone_set('UTC'); // needed for parsing dates

$mandateReference = "2876527e-850e-4615-975c-94a29ff48fb8";
$paymentContext = new LinkIDPaymentContext(new LinkIDPaymentAmount(500, LinkIDCurrency::EUR, null), "Mandate Test");
$language = "nl";

$client = new LinkIDMandateClient($linkIDHost, $linkIDWSUsername, $linkIDWSPassword);
$orderReference = $client->pay($mandateReference, $paymentContext, $language);

print("<h2>Mandate</h2>");
print("<li>Orderreference: " . $orderReference . "</ul>");

?>
