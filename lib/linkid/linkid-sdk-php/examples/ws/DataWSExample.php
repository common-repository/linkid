<?php

require_once('../../LinkIDDataClient.php');
require_once('../ExampleConfig.php');

//$attributeName = "profile.address";
$attributeName = "profile.givenName";

print("<h2>User: " . $userId . "</h2>");

$client = new LinkIDDataClient($linkIDHost, $linkIDWSUsername, $linkIDWSPassword);

// GET ATTRIBUTES
$attributes = getAttributes($client, $userId, $attributeName);

if (null == $attributes) return;

// SET ATTRIBUTES
print("<h3>set attribute</h3>");

$origAttribute = clone $attributes[$attributeName][0];

//$attributes['profile.address'][0]->value[0]->value .= "- modified";
$attributes[$attributeName][0]->value .= " - modified";
$client->setAttribute($userId, $attributes[$attributeName][0]);

// GET ATTRIBUTES
$attributes = getAttributes($client, $userId, $attributeName);

// DELETE ATTRIBUTES
$client->removeAttribute($userId, $attributes[$attributeName][0]);

print("<h3>remove attribute</h3>");

// GET ATTRIBUTES
$attributes = getAttributes($client, $userId, $attributeName);

// SET ATTRIBUTES
print("<h3>set attribute</h3>");

$client->setAttribute($userId, $origAttribute);

// GET ATTRIBUTES
$attributes = getAttributes($client, $userId, $attributeName);

function getAttributes($client, $userId, $attributeName)
{

    $attributes = $client->getAttributes($userId, $attributeName);

    print("<h3>get attribute</h3>");
    print("<pre>");
    print_r($attributes);
    print("</pre>");

    return $attributes;
}

?>
