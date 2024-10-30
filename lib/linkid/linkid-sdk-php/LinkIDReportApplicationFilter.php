<?php

/*
 * LinkID Report's date filter
 *
 * @author Wim Vandenhaute
 */

class LinkIDReportApplicationFilter
{
    public $applicationName;

    function __construct($applicationName)
    {
        $this->applicationName = $applicationName;
    }


}