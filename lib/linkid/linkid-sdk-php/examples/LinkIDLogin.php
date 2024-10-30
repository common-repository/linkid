<?php

require_once('../LinkIDLoginConfig.php');
require_once('ExampleConfig.php');

// location of this page, linkID will post its authentication response back to this location.
$loginPage = "http://localhost/~wvdhaute/linkid-sdk-php/examples/LinkIDLogin.php";

// creates authentication request and handles incoming authentication responses
handleLinkID($authnContextParam, $linkIDHost, $linkIDAppName, $linkIDLanguage, $loginPage, $linkIDWSUsername, $linkIDWSPassword);