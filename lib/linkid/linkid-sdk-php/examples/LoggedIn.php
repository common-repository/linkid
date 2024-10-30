<?php

require_once('../LinkIDAuthnContext.php');
require_once('ExampleConfig.php');

date_default_timezone_set('UTC'); // needed for parsing dates

if (!isset($_SESSION)) {
    session_start();
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>linkID Mobile Demo</title>
</head>

<body>

<?php

$authnContext = $_SESSION[$authnContextParam];

print("<h2>User: " . $authnContext->userId . "</h2>");

print ("<a href=\"logout.php\">Logout</a>");

print("<h3>Attributes</h3>");
print("<pre>");
print_r($authnContext->attributes);
print("</pre>");


print("<h3>Payment response</h3>");
print("<pre>");
print_r($authnContext->paymentResponse);
print("</pre>");


?>


</body>
</html>
