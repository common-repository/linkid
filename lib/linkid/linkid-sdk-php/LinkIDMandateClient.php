<?php

require_once('LinkIDWSSoapClient.php');
require_once('LinkIDPaymentContext.php');

/*
 * linkID Mandate WS client
 *
 * @author Wim Vandenhaute
 */

class LinkIDMandateClient
{

    private $client;

    /**
     * Constructor
     *
     * @param $linkIDHost string the linkID host ( https://<linkIDHost>/linkid-ws-username
     * @param $username string the WS-Security username
     * @param $password string the WS-Security password
     * @param array $options [optional]
     *
     */
    public function __construct($linkIDHost, $username, $password, array $options = array())
    {

        $wsdlLocation = "https://" . $linkIDHost . "/linkid-ws-username/mandate20?wsdl";

        $this->client = new LinkIDWSSoapClient($wsdlLocation);
        $this->client->__setUsernameToken($username, $password, 'PasswordDigest');

    }

    public function pay($mandateReference, $paymentContext, $language = "en")
    {

        $requestParams = new stdClass;

        $requestParams->paymentContext = new stdClass;
        $requestParams->paymentContext->amount = $paymentContext->amount;
        $requestParams->paymentContext->currency = linkIDCurrencyToString($paymentContext->currency);
        $requestParams->paymentContext->description = $paymentContext->description;
        $requestParams->paymentContext->orderReference = $paymentContext->orderReference;
        $requestParams->paymentContext->paymentProfile = $paymentContext->profile;

        $requestParams->mandateReference = $mandateReference;
        $requestParams->language = $language;

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->client->pay($requestParams);

        if (null != $response->error) {
            throw new Exception('Error: ' . $response->error->errorCode);
        }

        return $response->success->orderReference;
    }
}