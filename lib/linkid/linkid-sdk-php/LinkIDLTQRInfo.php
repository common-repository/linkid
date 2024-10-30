<?php

/*
 * LinkID LTQR Session
 *
 * @author Wim Vandenhaute
 */

class LinkIDLTQRInfo
{

    public $ltqrReference;
    public $sessionId;
    public $created;
    //
    public $qrCodeImage;
    public $qrCodeURL;
    //
    public $authenticationMessage;
    public $finishedMessage;
    //
    public $oneTimeUse;
    //
    public $expiryDate;
    public $expiryDuration;
    //
    public $paymentContext;
    public $callback;
    public $identityProfiles;
    //
    public $sessionExpiryOverride;
    public $theme;
    //
    public $mobileLandingSuccess;
    public $mobileLandingError;
    public $mobileLandingCancel;
    //
    public $pollingConfiguration;
    //
    public $waitForUnlock;
    public $locked;

    function __construct($ltqrReference, $sessionId, $created, $qrCodeImage, $qrCodeURL, $authenticationMessage, $finishedMessage, $oneTimeUse, $expiryDate, $expiryDuration, $paymentContext, $callback, $identityProfiles, $sessionExpiryOverride, $theme, $mobileLandingSuccess, $mobileLandingError, $mobileLandingCancel, $pollingConfiguration, $waitForUnlock, $locked)
    {
        $this->ltqrReference = $ltqrReference;
        $this->sessionId = $sessionId;
        $this->created = $created;
        $this->qrCodeImage = $qrCodeImage;
        $this->qrCodeURL = $qrCodeURL;
        $this->authenticationMessage = $authenticationMessage;
        $this->finishedMessage = $finishedMessage;
        $this->oneTimeUse = $oneTimeUse;
        $this->expiryDate = $expiryDate;
        $this->expiryDuration = $expiryDuration;
        $this->paymentContext = $paymentContext;
        $this->callback = $callback;
        $this->identityProfiles = $identityProfiles;
        $this->sessionExpiryOverride = $sessionExpiryOverride;
        $this->theme = $theme;
        $this->mobileLandingSuccess = $mobileLandingSuccess;
        $this->mobileLandingError = $mobileLandingError;
        $this->mobileLandingCancel = $mobileLandingCancel;
        $this->pollingConfiguration = $pollingConfiguration;
        $this->waitForUnlock = $waitForUnlock;
        $this->locked = $locked;
    }


}