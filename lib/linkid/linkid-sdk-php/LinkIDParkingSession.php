<?php

require_once('LinkIDPaymentState.php');

/*
 * LinkID Parking session Order
 *
 * @author Wim Vandenhaute
 */

class LinkIDParkingSession
{

    public $date;
    public $barCode;
    public $parking;
    public $userId;
    public $turnover;
    public $validated;
    public $paymentOrderReference;
    public $paymentState;

    function __construct($date, $barCode, $parking, $userId, $turnover, $validated, $paymentOrderReference, $paymentState)
    {
        $this->date = $date;
        $this->barCode = $barCode;
        $this->parking = $parking;
        $this->userId = $userId;
        $this->turnover = $turnover;
        $this->validated = $validated;
        $this->paymentOrderReference = $paymentOrderReference;
        $this->paymentState = $paymentState;
    }


}

function parseLinkIDParkingSession($xmlParkingSession)
{
    return new LinkIDParkingSession(
        isset($xmlParkingSession->date) ? $xmlParkingSession->date : null,
        isset($xmlParkingSession->barCode) ? $xmlParkingSession->barCode : null,
        isset($xmlParkingSession->parking) ? $xmlParkingSession->parking : null,
        isset($xmlParkingSession->userId) ? $xmlParkingSession->userId : null,
        isset($xmlParkingSession->turnover) ? $xmlParkingSession->turnover : null,
        isset($xmlParkingSession->validated) ? $xmlParkingSession->validated : null,
        isset($xmlParkingSession->paymentOrderReference) ? $xmlParkingSession->paymentOrderReference : null,
        isset($xmlParkingSession->paymentState) ? parseLinkIDPaymentState($xmlParkingSession->paymentState) : null);

}