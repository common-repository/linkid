<?php

require_once('LinkIDWSSoapClient.php');
require_once('LinkIDCurrency.php');

/*
 * linkID Wallet WS client
 *
 * @author Wim Vandenhaute
 */

class LinkIDWalletClient
{

    private $client;

    /**
     * Constructor
     *
     * @param $linkIDHost string the linkID host ( https://<linkIDHost>/linkid-ws-username
     * @param $username string the WS-Security username
     * @param $password string the WS-Security password
     * @param array $options [optional]
     */
    public function __construct($linkIDHost, $username, $password, array $options = array())
    {

        $wsdlLocation = "https://" . $linkIDHost . "/linkid-ws-username/wallet?wsdl";

        $this->client = new LinkIDWSSoapClient($wsdlLocation, $options);
        $this->client->__setUsernameToken($username, $password, 'PasswordDigest');
    }

    /**
     * @param $userId string the linkID user ID
     * @param $walletOrganizationId string the linkID wallet organization ID
     * @param $amount double optional start balance
     * @param $currency LinkIDCurrency optional start balance currency
     * @param $walletCoin string optional wallet coin
     *
     * @throws Exception something went wrong enrolling
     *
     * @return string the ID of the linkID wallet that was created
     */
    public function enroll($userId, $walletOrganizationId, $amount, $currency, $walletCoin)
    {

        $requestParams = array(
            'userId' => $userId,
            'walletOrganizationId' => $walletOrganizationId,
            'amount' => $amount,
            'currency' => $currency,
            'walletCoin' => $walletCoin
        );

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->client->enroll($requestParams);

        if (isset($response->error) && null != $response->error) {
            throw new Exception('Error: ' . $response->error->errorCode);
        }

        return $response->success->walletId;
    }

    /**
     * @param $userId string the linkID user ID
     * @param $walletId string the linkID wallet ID
     * @param $amount double amount to add
     * @param $currency LinkIDCurrency currency of amount to add
     * @param $walletCoin string optional wallet coin
     *
     * @throws Exception something went wrong
     */
    public function addCredit($userId, $walletId, $amount, $currency, $walletCoin)
    {

        $requestParams = array(
            'userId' => $userId,
            'walletId' => $walletId,
            'amount' => $amount,
            'currency' => $currency,
            'walletCoin' => $walletCoin
        );

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->client->addCredit($requestParams);

        if (isset($response->error) && null != $response->error) {
            throw new Exception('Error: ' . $response->error->errorCode);
        }
    }

    /**
     * @param $userId string the linkID user ID
     * @param $walletId string the linkID wallet ID
     * @param $amount double amount to remove
     * @param $currency LinkIDCurrency currency of amount to remove
     * @param $walletCoin string optional wallet coin
     *
     * @throws Exception something went wrong
     */
    public function removeCredit($userId, $walletId, $amount, $currency, $walletCoin)
    {

        $requestParams = array(
            'userId' => $userId,
            'walletId' => $walletId,
            'amount' => $amount,
            'currency' => $currency,
            'walletCoin' => $walletCoin
        );

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->client->removeCredit($requestParams);

        if (isset($response->error) && null != $response->error) {
            throw new Exception('Error: ' . $response->error->errorCode);
        }
    }

    /**
     * @param $userId string the linkID user ID
     * @param $walletId string the linkID wallet ID
     *
     * @throws Exception something went wrong
     */
    public function remove($userId, $walletId)
    {

        $requestParams = array(
            'userId' => $userId,
            'walletId' => $walletId
        );

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->client->remove($requestParams);

        if (isset($response->error) && null != $response->error) {
            throw new Exception('Error: ' . $response->error->errorCode);
        }
    }

    /**
     * @param $userId string the linkID user ID
     * @param $walletId string the linkID wallet ID
     * @param $walletTransactionId string the linkID wallet transaction to commit
     *
     * @throws Exception something went wrong
     */
    public function commit($userId, $walletId, $walletTransactionId)
    {

        $requestParams = array(
            'userId' => $userId,
            'walletId' => $walletId,
            'walletTransactionId' => $walletTransactionId
        );

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->client->commit($requestParams);

        if (isset($response->error) && null != $response->error) {
            throw new Exception('Error: ' . $response->error->errorCode);
        }
    }

    /**
     * @param $userId string the linkID user ID
     * @param $walletId string the linkID wallet ID
     * @param $walletTransactionId string the linkID wallet transaction to release
     *
     * @throws Exception something went wrong
     */
    public function release($userId, $walletId, $walletTransactionId)
    {

        $requestParams = array(
            'userId' => $userId,
            'walletId' => $walletId,
            'walletTransactionId' => $walletTransactionId
        );

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->client->release($requestParams);

        if (isset($response->error) && null != $response->error) {
            throw new Exception('Error: ' . $response->error->errorCode);
        }
    }
}