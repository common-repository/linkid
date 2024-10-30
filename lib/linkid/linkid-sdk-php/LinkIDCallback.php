<?php

/*
 * LinkID Callback configuration
 *
 * @author Wim Vandenhaute
 */
class LinkIDCallback
{
    public $location;
    public $appSessionId;
    public $inApp;

    /**
     * Constructor
     */
    public function __construct($location, $appSessionId = null, $inApp = true)
    {

        $this->location = $location;
        $this->appSessionId = $appSessionId;
        $this->inApp = $inApp;
    }

}

function parseLinkIDCallback($xmlCallback)
{
    return new LinkIDPaymentContext(
        isset($xmlCallback->location) ? $xmlCallback->location : null,
        isset($xmlCallback->appSessionId) ? $xmlCallback->appSessionId : null,
        isset($xmlCallback->inApp) ? $xmlCallback->inApp : true
    );
}