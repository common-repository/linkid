<?php

require_once('LinkIDWSSoapClient.php');

/*
 * linkID Capture WS client
 *
 * @author Wim Vandenhaute
 */

class LinkIDCaptureClient
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

        $wsdlLocation = "https://" . $linkIDHost . "/linkid-ws-username/capture?wsdl";

        $this->client = new LinkIDWSSoapClient($wsdlLocation);
        $this->client->__setUsernameToken($username, $password, 'PasswordDigest');
    }

    public function capture($orderReference)
    {

        $requestParams = array(
            'orderReference' => $orderReference
        );
        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->client->capture($requestParams);

        if (null == $response) throw new Exception("Failed to get payment status...");

        if (isset($response->error) && null != $response->error) {
            throw new Exception('Error: ' . $response->error->errorCode);
        }
    }

}