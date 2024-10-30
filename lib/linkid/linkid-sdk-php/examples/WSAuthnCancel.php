<?php

require_once('ExampleConfig.php');
require_once('../LinkIDAuthClient.php');
require_once('../LinkIDSaml2.php');
require_once('../LinkIDLoginConfig.php');

if (!isset($_REQUEST['sessionId'])) {
    return;
}
$sessionId = $_REQUEST['sessionId'];

$client = new LinkIDAuthClient($linkIDHost, $linkIDWSUsername, $linkIDWSPassword);
$client->cancel($sessionId);

print("Canceled");
