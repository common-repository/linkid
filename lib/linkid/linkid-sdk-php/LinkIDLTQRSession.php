<?php

/*
 * LinkID LTQR Session
 *
 * @author Wim Vandenhaute
 */

class LinkIDLTQRSession
{

    public $qrCodeImage;
    public $qrCodeURL;
    public $ltqrReference;

    public $paymentOrderReference;

    /**
     * Constructor
     */
    public function __construct($qrCodeImage, $qrCodeURL, $ltqrReference, $paymentOrderReference)
    {

        $this->qrCodeImage = $qrCodeImage;
        $this->qrCodeURL = $qrCodeURL;
        $this->ltqrReference = $ltqrReference;

        $this->paymentOrderReference = $paymentOrderReference;
    }
}