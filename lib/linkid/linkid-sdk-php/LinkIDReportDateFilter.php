<?php

/*
 * LinkID Report's date filter
 *
 * @author Wim Vandenhaute
 */

class LinkIDReportDateFilter
{
    public $startDate;
    public $endDate;

    function __construct($startDate, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }


}