<?php

/**
 * Configuration for the example pages, linkID hostname, appname, username/password, ...
 */

//$linkIDHost = "192.168.5.14:8080";
$linkIDHost = "demo.linkid.be";

$linkIDAppName = "example-mobile";
//$linkIDAppName = "test-shop";

$linkIDLanguage = "en";

// username/password
$linkIDWSUsername = "example-mobile";
//$linkIDWSUsername = "test-shop";
$linkIDWSPassword = "6E6C1CB7-965C-48A0-B2B0-6B65674BE19F";
//$linkIDWSPassword = "5E017416-23B2-47E1-A9E0-43EE3C75A1B0";

/*
 * linkID authentication context session attribute
 *
 * After a successful authentication with linkID this will hold the returned
 * AuthenticationProtocolContext object which contains the linkID user ID,
 * used authentication device(s) and optionally the returned linkID attributes
 * for the application.
 */
$authnContextParam = "linkID.authnContext";

// UserId to be used in the WS examples
$userId = "2b35dbab-2ba2-403b-8c36-a8399c3af3d5";