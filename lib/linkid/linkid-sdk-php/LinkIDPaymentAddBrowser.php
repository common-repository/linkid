<?php

abstract class LinkIDPaymentAddBrowser
{

    const NOT_ALLOWED = 0;
    const REDIRECT = 1;

}

function linkIDPaymentAddBrowserToString($paymentAddBrowser) {

    if (0 == $paymentAddBrowser) return "NOT_ALLOWED";
    if (1 == $paymentAddBrowser) return "REDIRECT";

    return "NOT_ALLOWED";
}