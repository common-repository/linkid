<?php

require_once('LinkIDPaymentTransaction.php');
require_once('LinkIDWalletTransaction.php');

class LinkIDPaymentDetails
{

    public $transactions;
    public $walletTransactions;

    /**
     * Constructor
     */
    public function __construct($transactions, $walletTransactions)
    {

        $this->transactions = $transactions;
        $this->walletTransactions = $walletTransactions;
    }

}