<?php

/*
 * LinkID LTQR Session
 *
 * @author Wim Vandenhaute
 */

class LinkIDLTQRClientSession
{
    public $qrCodeImage;
    public $qrCodeURL;
    public $ltqrReference;
    public $clientSessionId;
    public $userId;
    public $created;

    public $paymentOrderReference;
    public $paymentState;

    const STARTED = 0; // payment is being processed
    const PAYED = 1; // completed
    const FAILED = 2; // payment has failed

    /**
     * Constructor
     */
    public function __construct($qrCodeImage, $qrCodeURL, $ltqrReference, $clientSessionId, $userId, $created,
                                $paymentOrderReference, $paymentState)
    {

        $this->qrCodeImage = $qrCodeImage;
        $this->qrCodeURL = $qrCodeURL;
        $this->ltqrReference = $ltqrReference;
        $this->clientSessionId = $clientSessionId;
        $this->userId = $userId;
        $this->created = $created;

        $this->paymentOrderReference = $paymentOrderReference;
        $this->paymentState = parseLinkIDLTQRPaymentState($paymentState);
    }
}

function parseLinkIDLTQRPaymentState($paymentState)
{

    if ($paymentState == "STARTED") return LinkIDLTQRClientSession::STARTED;
    if ($paymentState == "AUTHORIZED") return LinkIDLTQRClientSession::PAYED;
    if ($paymentState == "FAILED") return LinkIDLTQRClientSession::FAILED;

    throw new Exception("Unexpected payment state: " . $paymentState);
}