<?php

require_once('../../LinkIDIdMappingClient.php');
require_once('../ExampleConfig.php');

$client = new LinkIDIdMappingClient($linkIDHost, $linkIDWSUsername, $linkIDWSPassword);
$userId = $client->getUserId("profile.email.address", "wim.vandenhaute@gmail.com");

print("<h2>IdMapping test</h2>");
print("UserID: " . $userId);

?>
