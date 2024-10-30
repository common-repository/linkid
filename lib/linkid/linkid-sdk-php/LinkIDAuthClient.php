<?php

require_once('LinkIDWSSoapClient.php');
require_once('LinkIDAuthnSession.php');
require_once('LinkIDPollResponse.php');

/*
 * linkID Auth WS client
 *
 * @author Wim Vandenhaute
 */

class LinkIDAuthClient
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

        $wsdlLocation = "https://" . $linkIDHost . "/linkid-ws-username/auth?wsdl";

        $this->client = new LinkIDWSSoapClient($wsdlLocation, $options);
        $this->client->__setUsernameToken($username, $password, 'PasswordDigest');

    }

    /**
     * @param object $authnRequest the SAML v2.0 authentication request
     * @param string $language optional language (default is en)
     * @param null $userAgent optional user agent string, for adding e.g. callback params to the QR code URL, android chrome URL needs to be http://linkidmauthurl/MAUTH/2/zUC8oA/eA==, ...
     * @param bool $forceRegistration
     * @return \LinkIDAuthnSession the linkID authentication session containing the QR code image, URL, sessionId and client authentication context
     * @throws Exception something went wrong...
     */
    public function start($authnRequest, $language = "en", $userAgent = null, $forceRegistration = false)
    {
        $requestParams = array(
            'any' => $authnRequest,
            'language' => $language,
            'userAgent' => $userAgent,
            'forceRegistration' => $forceRegistration
        );
        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->client->start($requestParams);

        if (isset($response->error) && null != $response->error) {
            throw new Exception('Error: ' . $response->error->error . " - " . $response->error->info);
        }

        $qrCodeImage = base64_decode($response->success->encodedQRCode);

        return new LinkIDAuthnSession($response->success->sessionId, $qrCodeImage, $response->success->encodedQRCode, $response->success->qrCodeURL, $response->success->authenticationContext);

    }

    /**
     * @param LinkIDSaml2 $saml2Util
     * @param $sessionId
     * @param string $language
     * @return LinkIDPollResponse
     * @throws Exception
     */
    public function poll($saml2Util, $sessionId, $language = "en")
    {

        $requestParams = array(
            'sessionId' => $sessionId,
            'language' => $language
        );
        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->client->poll($requestParams);

        if (isset($response->error) && null != $response->error) {
            throw new Exception('Error: ' . $response->error->error . " - " . $response->error->info);
        }

        $authenticationContext = null;
        if (isset($response->success->authenticationResponse) && null != $response->success->authenticationResponse->any) {

            $xml = new SimpleXMLElement($response->success->authenticationResponse->any);
            $authenticationContext = $saml2Util->parseXmlAuthnResponse($xml);
        }

        return new LinkIDPollResponse($response->success->authenticationState,
            isset($response->success->paymentState) ? $response->success->paymentState : null,
            isset($response->success->paymentMenuURL) ? $response->success->paymentMenuURL : null,
            $authenticationContext);
    }

    /**
     * Cancel a linkID authentication / payment session
     *
     * @param $sessionId string ID of session to cancel
     *
     * @throws Exception
     */
    public function cancel($sessionId)
    {

        $requestParams = array(
            'sessionId' => $sessionId,
        );
        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->client->cancel($requestParams);

        if (isset($response->error) && null != $response->error) {
            throw new Exception('Error: ' . $response->error->error . " - " . $response->error->info);
        }

    }

}
