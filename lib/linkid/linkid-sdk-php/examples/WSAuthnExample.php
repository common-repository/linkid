<?php

require_once('ExampleConfig.php');
require_once('../LinkIDAuthClient.php');
require_once('../LinkIDSaml2.php');
require_once('../LinkIDLoginConfig.php');

date_default_timezone_set('UTC'); // needed for DateTime
if (!isset($_SESSION)) {
    session_start();
}

$loginPage = "http://localhost/~wvdhaute/linkid-sdk-php/examples/LinkIDLogin.php";

$client = new LinkIDAuthClient($linkIDHost, $linkIDWSUsername, $linkIDWSPassword);

if (!isset($_SESSION["linkIDSession"])) {

    // start new linkID authentication session
    $saml2Util = new LinkIDSaml2();

    $loginConfig = new LinkIDLoginConfig($linkIDHost);
    $clientAuthnMessage = "PHP Authn Message";
    $clientFinishedMessage = "PHP Finished Message";
    $attributeSuggestions = array("profile.familyName" => "Test", "profile.givenName" => "Mister", "profile.dob" => new DateTime());
    $identityProfiles = array("linkid_basic");

    $paymentContext = new LinkIDPaymentContext(new LinkIDPaymentAmount(1, null, "urn:linkid:wallet:coin:coffee"), "PHP Payment description");
//    $paymentContext = new LinkIDPaymentContext(new LinkIDPaymentAmount(500, LinkIDCurrency::EUR, null), "PHP Payment description");
//    $paymentContext = null;

    $callback = new LinkIDCallback("https://www.google.be");
//    $callback = null;

    $sessionExpiryOverride = 60;
    $theme = "ugent";

    $authnRequest = $saml2Util->generateAuthnRequest($linkIDAppName, $loginConfig, $loginPage, $clientAuthnMessage, $clientFinishedMessage, $identityProfiles, $attributeSuggestions, $paymentContext, $callback, $sessionExpiryOverride, $theme);

    $linkIDAuthnSession = $client->start($authnRequest, "en");

    // store on session
    $_SESSION["linkIDSession"] = $linkIDAuthnSession;
    $_SESSION["linkIDSaml2Util"] = $saml2Util;

    // show QR code
    $qrCodeImage = imagecreatefromstring($linkIDAuthnSession->qrCodeImage);
    if ($qrCodeImage != false) {
        header('Content-Type: image/png');
        imagepng($qrCodeImage);
        imagedestroy($qrCodeImage);
    } else {
        echo "Not a valid QR code";
    }

} else {

    // poll existing linkID authentication session
    $saml2Util = $_SESSION["linkIDSaml2Util"];
    $linkIDAuthnSession = $_SESSION["linkIDSession"];

    $linkIDPollResponse = $client->poll($saml2Util, $linkIDAuthnSession->sessionId);

    // output linkID poll response
    if (null != $linkIDPollResponse->authenticationContext) {

        print("<h2>User: " . $linkIDPollResponse->authenticationContext->userId . "</h2>");

        print ("<a href=\"wslogout.php\">Restart</a>");

        print("<h3>Attributes</h3>");
        print("<pre>");
        print_r($linkIDPollResponse->authenticationContext->attributes);
        print("</pre>");


        print("<h3>Payment response</h3>");
        print("<pre>");
        print_r($linkIDPollResponse->authenticationContext->paymentResponse);
        print("</pre>");

    } else {

        print ("<a href=\"wslogout.php\">Restart</a>");
        print("<pre>");
        print("AuthenticationState: " . $linkIDPollResponse->authenticationState . "\n");
        print("PaymentState: " . $linkIDPollResponse->paymentState . "\n");
        print("PaymentMenuURL: " . $linkIDPollResponse->paymentMenuURL . "\n");
        print("</pre>");

    }

}

?>