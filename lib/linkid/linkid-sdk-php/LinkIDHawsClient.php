<?php

require_once('LinkIDWSSoapClient.php');

/*
 * linkID HAWS WS client
 *
 * @author Wim Vandenhaute
 */

class LinkIDHawsClient
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

        $wsdlLocation = "https://" . $linkIDHost . "/linkid-ws-username/haws?wsdl";

        $this->client = new LinkIDWSSoapClient($wsdlLocation);
        $this->client->__setUsernameToken($username, $password, 'PasswordDigest');

    }

    public function push($authnRequest, $language)
    {

        $requestParams = array(
            'any' => $authnRequest,
            'language' => $language
        );
        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->client->pushV2($requestParams);

        if (null != $response->error) {
            throw new Exception('Error: ' . $response->error->error . " - " . $response->error->info);
        }

        return $response->sessionId;
    }

    public function pull($sessionId)
    {

        $requestParams = array(
            'sessionId' => $sessionId
        );
        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->client->pull($requestParams);

        if (null != $response->error) {
            throw new Exception('Error: ' . $response->error->error . " - " . $response->error->info);
        }

        return $response->success->any;
    }
}
