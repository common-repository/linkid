<?php

require_once('LinkIDCurrency.php');

class LinkIDPaymentAmount
{

    public $amount;
    public $currency;
    public $walletCoin;

    public function __construct($amount, $currency, $walletCoin)
    {
        $this->amount = $amount;
        $this->currency = $currency;
        $this->walletCoin = $walletCoin;
    }


}