<?php

require_once('LinkIDPaymentState.php');
require_once('LinkIDPaymentMethodType.php');
require_once('LinkIDCurrency.php');

class LinkIDPaymentTransaction
{

    public $paymentMethodType;
    public $paymentMethod;
    public $paymentState;
    public $creationDate;
    public $authorizationDate;
    public $capturedDate;
    public $docdataReference;
    public $amount;
    public $currency;

    function __construct($paymentMethodType, $paymentMethod, $paymentState, $creationDate, $authorizationDate, $capturedDate, $docdataReference, $amount, $currency)
    {
        $this->paymentMethodType = $paymentMethodType;
        $this->paymentMethod = $paymentMethod;
        $this->paymentState = $paymentState;
        $this->creationDate = $creationDate;
        $this->authorizationDate = $authorizationDate;
        $this->capturedDate = $capturedDate;
        $this->docdataReference = $docdataReference;
        $this->amount = $amount;
        $this->currency = $currency;
    }

}

function parseLinkIDPaymentTransaction($xmlPaymentTransaction)
{
    return new LinkIDPaymentTransaction(
        isset($xmlPaymentTransaction->paymentMethodType) ? parseLinkIDPaymentMethodType($xmlPaymentTransaction->paymentMethodType) : null,
        isset($xmlPaymentTransaction->paymentMethod) ? $xmlPaymentTransaction->paymentMethod : null,
        isset($xmlPaymentTransaction->paymentState) ? parseLinkIDPaymentState($xmlPaymentTransaction->paymentState) : null,
        isset($xmlPaymentTransaction->creationDate) ? $xmlPaymentTransaction->creationDate : null,
        isset($xmlPaymentTransaction->authorizationDate) ? $xmlPaymentTransaction->authorizationDate : null,
        isset($xmlPaymentTransaction->capturedDate) ? $xmlPaymentTransaction->capturedDate : null,
        isset($xmlPaymentTransaction->docdataReference) ? $xmlPaymentTransaction->docdataReference : null,
        isset($xmlPaymentTransaction->amount) ? $xmlPaymentTransaction->amount : null,
        isset($xmlPaymentTransaction->currency) ? parseLinkIDCurrency($xmlPaymentTransaction->currency) : null);
}