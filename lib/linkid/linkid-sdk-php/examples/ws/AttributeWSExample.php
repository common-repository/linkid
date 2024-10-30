<?php

require_once('../../LinkIDAttributeClient.php');
require_once('../ExampleConfig.php');

$client = new LinkIDAttributeClient($linkIDHost, $linkIDWSUsername, $linkIDWSPassword);
$attributes = $client->getAttributes($userId);
//$attributes = $client->getAttributes($userId,"profile.address");

print("<h2>Attribute for " . $userId . "</h2>");
print("<pre>");
print_r($attributes);
print("</pre>");

?>
