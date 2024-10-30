<?php

/*
 * LinkID Report's date filter
 *
 * @author Wim Vandenhaute
 */

class LinkIDReportWalletFilter
{
    public $walletId;
    public $userId;

    function __construct($walletId, $userId)
    {
        $this->walletId = $walletId;
        $this->userId = $userId;
    }

}