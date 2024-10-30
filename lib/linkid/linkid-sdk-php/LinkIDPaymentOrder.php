<?php

require_once('LinkIDCurrency.php');
require_once('LinkIDPaymentState.php');
require_once('LinkIDPaymentTransaction.php');
require_once('LinkIDWalletTransaction.php');

/*
 * LinkID Payment Order
 *
 * @author Wim Vandenhaute
 */

class LinkIDPaymentOrder
{

    public $date;
    public $amount;
    public $currency;
    public $walletCoin;
    public $description;
    public $paymentState;
    public $amountPayed;
    public $authorized;
    public $captured;
    public $orderReference;
    public $userId;
    public $email;
    public $givenName;
    public $familyName;
    public $transactions;
    public $walletTransactions;

    function __construct($date, $amount, $currency, $walletCoin, $description, $paymentState, $amountPayed, $authorized, $captured, $orderReference, $userId, $email, $givenName, $familyName, $transactions, $walletTransactions)
    {
        $this->date = $date;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->walletCoin = $walletCoin;
        $this->description = $description;
        $this->paymentState = $paymentState;
        $this->amountPayed = $amountPayed;
        $this->authorized = $authorized;
        $this->captured = $captured;
        $this->orderReference = $orderReference;
        $this->userId = $userId;
        $this->email = $email;
        $this->givenName = $givenName;
        $this->familyName = $familyName;
        $this->transactions = $transactions;
        $this->walletTransactions = $walletTransactions;
    }

}

function parseLinkIDPaymentOrder($xmlPaymentOrder)
{
    $paymentTransactions = array();
    $walletTransactions = array();

    // payment transactions
    if (isset($xmlPaymentOrder->transactions)) {
        $xmlPaymentTransactions = $xmlPaymentOrder->transactions;
        if (is_array($xmlPaymentTransactions)) {
            foreach ($xmlPaymentTransactions as $xmlPaymentTransaction) {
                $paymentTransactions[] = parseLinkIDPaymentTransaction($xmlPaymentTransaction);
            }
        } else {
            $paymentTransactions[] = parseLinkIDPaymentTransaction($xmlPaymentTransactions);
        }
    }

    // wallet transactions
    if (isset($xmlPaymentOrder->walletTransactions)) {
        $xmlWalletTransactions = $xmlPaymentOrder->walletTransactions;
        if (is_array($xmlWalletTransactions)) {
            foreach ($xmlWalletTransactions as $xmlWalletTransaction) {
                $walletTransactions[] = parseLinkIDWalletTransaction($xmlWalletTransaction);
            }
        } else {
            $walletTransactions[] = parseLinkIDWalletTransaction($xmlWalletTransactions);
        }
    }

    return new LinkIDPaymentOrder(
        isset($xmlPaymentOrder->date) ? $xmlPaymentOrder->date : null,
        isset($xmlPaymentOrder->amount) ? $xmlPaymentOrder->amount : null,
        isset($xmlPaymentOrder->currency) ? parseLinkIDCurrency($xmlPaymentOrder->currency) : null,
        isset($xmlPaymentOrder->walletCoin) ? $xmlPaymentOrder->walletCoin : null,
        isset($xmlPaymentOrder->description) ? $xmlPaymentOrder->description : null,
        isset($xmlPaymentOrder->paymentState) ? parseLinkIDPaymentState($xmlPaymentOrder->paymentState) : null,
        isset($xmlPaymentOrder->amountPayed) ? $xmlPaymentOrder->amountPayed : null,
        isset($xmlPaymentOrder->authorized) ? $xmlPaymentOrder->authorized : null,
        isset($xmlPaymentOrder->captured) ? $xmlPaymentOrder->captured : null,
        isset($xmlPaymentOrder->orderReference) ? $xmlPaymentOrder->orderReference : null,
        isset($xmlPaymentOrder->userId) ? $xmlPaymentOrder->userId : null,
        isset($xmlPaymentOrder->email) ? $xmlPaymentOrder->email : null,
        isset($xmlPaymentOrder->givenName) ? $xmlPaymentOrder->givenName : null,
        isset($xmlPaymentOrder->familyName) ? $xmlPaymentOrder->familyName : null,
        $paymentTransactions, $walletTransactions);
}