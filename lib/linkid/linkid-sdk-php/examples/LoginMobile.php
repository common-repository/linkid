<?php

require_once('../LinkIDLoginConfig.php');

date_default_timezone_set('UTC'); // needed for DateTime

// set device context
setLinkIDAuthnMessage("PHP Authn Message");
setLinkIDFinishedMessage("PHP Finished Message");

// set identity profiles
setLinkIDIdentityProfiles(array("linkid_basic", "linkid_payment"));

// set attribute suggestions
$attributeSuggestions = array("profile.familyName" => "Test", "profile.givenName" => "Mister", "profile.dob" => new DateTime());
setLinkIDAttributeSuggestions($attributeSuggestions);

// set payment context
//$paymentContext = new LinkIDPaymentContext(500, "PHP Payment description");
$paymentContext = new LinkIDPaymentContext(500, LinkIDCurrency::EUR, "PHP Payment description", null, null, 5, LinkIDPaymentAddBrowser::NOT_ALLOWED);
setLinkIDPaymentContext($paymentContext);

// set callback config
$callback = new LinkIDCallback("https://www.linkid.be");
setLinkIDCallback($callback);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>linkID Mobile Demo</title>

    <script type="text/javascript" id="linkid-login-script"
            src="http://192.168.5.14:8080/linkid-static/js/linkid-min.js"></script>

    <style type="text/css">
        .linkid-login {
            cursor: pointer;
        }
    </style>
</head>

<body>

<h1>linkID Mobile Demo</h1>

<div class="qr-demo">
    <div>
        <iframe id="linkid" style="display: none;"></iframe>
    </div>
    <div>
        <a class="linkid-login" data-login-href="./LinkIDLogin.php" data-protocol="HAWS"
           data-mobile-minimal="linkid" data-completion-href="./LoggedIn.php">
            Start
        </a>
    </div>
</div>

</body>
</html>
