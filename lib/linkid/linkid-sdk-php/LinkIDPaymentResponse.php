<?php

require_once('LinkIDPaymentState.php');

class LinkIDPaymentResponse
{

    public $orderReference;
    public $paymentState;
    public $mandateReference;

    public $paymentMenuURL;

    public $docdataReference;

    /**
     * Constructor
     */
    public function __construct($orderReference, $paymentState, $mandateReference = null, $docdataReference = null, $paymentMenuURL = null)
    {

        $this->orderReference = $orderReference;
        $this->paymentState = parseLinkIDPaymentState($paymentState);
        $this->mandateReference = $mandateReference;
        $this->docdataReference = $docdataReference;
        $this->paymentMenuURL = $paymentMenuURL;
    }
}
