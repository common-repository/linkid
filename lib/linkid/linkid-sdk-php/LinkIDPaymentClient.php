<?php

require_once('LinkIDWSSoapClient.php');
require_once('LinkIDPaymentState.php');
require_once('LinkIDPaymentStatus.php');

/*
 * linkID Payment WS client
 *
 * @author Wim Vandenhaute
 */

class LinkIDPaymentClient
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

        $wsdlLocation = "https://" . $linkIDHost . "/linkid-ws-username/payment40?wsdl";

        $this->client = new LinkIDWSSoapClient($wsdlLocation);
        $this->client->__setUsernameToken($username, $password, 'PasswordDigest');
    }

    public function getStatus($orderReference)
    {

        $requestParams = array(
            'orderReference' => $orderReference
        );
        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->client->status($requestParams);

        if (null == $response) throw new Exception("Failed to get payment status...");

        if (null == $response->paymentStatus) return LinkIDPaymentState::STARTED;

        $paymentTransactions = array();
        $walletTransactions = array();

        // payment transactions
        if (isset($response->paymentDetails->paymentTransactions)) {
            $xmlPaymentTransactions = $response->paymentDetails->paymentTransactions;
            if (is_array($xmlPaymentTransactions)) {
                foreach ($xmlPaymentTransactions as $xmlPaymentTransaction) {
                    $paymentTransactions[] = parseLinkIDPaymentTransaction($xmlPaymentTransaction);
                }
            } else {
                $paymentTransactions[] = parseLinkIDPaymentTransaction($xmlPaymentTransactions);
            }
        }

        // wallet transactions
        if (isset($response->paymentDetails->walletTransactions)) {
            $xmlWalletTransactions = $response->paymentDetails->walletTransactions;
            if (is_array($xmlWalletTransactions)) {
                foreach ($xmlWalletTransactions as $xmlWalletTransaction) {
                    $walletTransactions[] = parseLinkIDWalletTransaction($xmlWalletTransaction);
                }
            } else {
                $walletTransactions[] = parseLinkIDWalletTransaction($xmlWalletTransactions);
            }
        }

        $paymentDetails = new LinkIDPaymentDetails($paymentTransactions, $walletTransactions);

        return new LinkIDPaymentStatus(
            isset($response->orderReference) ? $response->orderReference : null,
            isset($response->userId) ? $response->userId : null,
            isset($response->paymentStatus) ? parseLinkIDPaymentState($response->paymentStatus) : null,
            isset($response->authorized) ? $response->authorized : false,
            isset($response->captured) ? $response->captured : false,
            isset($response->amountPayed) ? $response->amountPayed : null,
            isset($response->amount) ? $response->amount : null,
            isset($response->currency) ? parseLinkIDCurrency($response->currency) : null,
            isset($response->walletCoin) ? $response->walletCoin : null,
            isset($response->description) ? $response->description : null,
            isset($response->profile) ? $response->profile : null,
            isset($response->created) ? $response->created : null,
            isset($response->mandateReference) ? $response->mandateReference : null,
            $paymentDetails);
    }

}