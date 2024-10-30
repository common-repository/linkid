<?php

class LinkIDWalletTransaction
{

    public $walletId;
    public $creationDate;
    public $transactionId;
    public $amount;
    public $currency;
    public $walletCoin;

    function __construct($walletId, $creationDate, $transactionId, $amount, $currency, $walletCoin)
    {
        $this->walletId = $walletId;
        $this->creationDate = $creationDate;
        $this->transactionId = $transactionId;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->walletCoin = $walletCoin;
    }

}

function parseLinkIDWalletTransaction($xmlWalletTransaction)
{
    return new LinkIDWalletTransaction(
        isset($xmlWalletTransaction->walletId) ? $xmlWalletTransaction->walletId : null,
        isset($xmlWalletTransaction->creationDate) ? $xmlWalletTransaction->creationDate : null,
        isset($xmlWalletTransaction->transactionId) ? $xmlWalletTransaction->transactionId : null,
        isset($xmlWalletTransaction->amount) ? $xmlWalletTransaction->amount : null,
        isset($xmlWalletTransaction->currency) ? parseLinkIDCurrency($xmlWalletTransaction->currency) : null,
        isset($xmlWalletTransaction->walletCoin) ? $xmlWalletTransaction->walletCoin : null);
}