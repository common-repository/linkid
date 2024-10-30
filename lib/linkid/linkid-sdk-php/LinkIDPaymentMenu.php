<?php

class LinkIDPaymentMenu
{

    public $menuResultSuccess;
    public $menuResultCancel;
    public $menuResultPending;
    public $menuResultError;

    function __construct($menuResultSuccess, $menuResultCancel, $menuResultPending, $menuResultError)
    {
        $this->menuResultSuccess = $menuResultSuccess;
        $this->menuResultCancel = $menuResultCancel;
        $this->menuResultPending = $menuResultPending;
        $this->menuResultError = $menuResultError;
    }


}