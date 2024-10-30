<?php

require_once('../../LinkIDReportingClient.php');
require_once('../ExampleConfig.php');

date_default_timezone_set('UTC'); // needed for parsing dates

$client = new LinkIDReportingClient($linkIDHost, $linkIDWSUsername, $linkIDWSPassword);

// Orders lookup on date

$orders = $client->getPaymentReport(new DateTime('2015-06-20'));

print("<h2>Payment orders since 2015-04-01</h2>");
print("<pre>");
print_r($orders);
print("</pre>");

// Orders lookup on orderReferences

$orderReferences = array();
$orderReferences[] = "QR-SHOP-0a0337b4-1fa2-4a89-a93c-e59e94bd41f5";
$orderReferences[] = "QR-SHOP-0f3ce411-548a-4785-a7a5-feb07687f91a";
$orders = $client->getPaymentReport(null, null, $orderReferences, null);

print("<h2>Payment orders filtered with order references list</h2>");
print("<pre>");
print_r($orders);
print("</pre>");

// Orders lookup on mandateReferences

$mandateReferences = array();
$mandateReferences[] = "c4d8edae-22e4-4c55-8486-5ba3bec3d0ad";
$mandateReferences[] = "9ae4d903-b3b5-4a03-9e60-92ac7bdafcd6";
$orders = $client->getPaymentReport(null, null, null, $mandateReferences);

print("<h2>Payment orders filtered with mandate references list</h2>");
print("<pre>");
print_r($orders);
print("</pre>");

// Parking session lookup on date

$sessions = $client->getParkingReport(new DateTime('2014-01-01'));

print("<h2>Parking sessions since 2014-01-01</h2>");
print("<pre>");
print_r($sessions);
print("</pre>");

// Wallet transactions lookup on date

$walletOrganizationId = "urn:linkid:wallet:leaseplan";

$transactions = $client->getWalletReport($walletOrganizationId, new LinkIDReportDateFilter(new DateTime('2014-01-01')), null, null);

print("<h2>Wallet transactions sessions since 2014-01-01</h2>");
print("<pre>");
print_r($transactions);
print("</pre>");

// Wallet transactions lookup on application name

$walletOrganizationId = "urn:linkid:wallet:leaseplan";
$applicationName = "test-shop";

$transactions = $client->getWalletReport($walletOrganizationId, null, new LinkIDReportApplicationFilter($applicationName), null);

print("<h2>Wallet transactions for application " . $applicationName . "</h2>");
print("<pre>");
print_r($transactions);
print("</pre>");

// Wallet transactions lookup on wallet

$walletOrganizationId = "urn:linkid:wallet:leaseplan";
$walletId = "ff52177f-8f80-4640-9e86-558f6b1b24c3";
$userId = "e4269366-ddfb-43dc-838d-01569a8c4c22";

$transactions = $client->getWalletReport($walletOrganizationId, null, null, new LinkIDReportWalletFilter($walletId, $userId));

print("<h2>Wallet transactions for walletId " . $walletId . " and userId " . $userId . "</h2>");
print("<pre>");
print_r($transactions);
print("</pre>");


