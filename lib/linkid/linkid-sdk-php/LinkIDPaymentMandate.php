<?php

class LinkIDPaymentMandate
{

    public $description;
    public $reference;

    function __construct($description, $reference)
    {
        $this->description = $description;
        $this->reference = $reference;
    }


}