<?php

/*
 * LinkID LTQR Polling configuration
 *
 * @author Wim Vandenhaute
 */

class LinkIDLTQRPollingConfiguration
{

    public $pollAttempts;
    public $pollInterval;
    //
    public $paymentPollAttempts;
    public $paymentPollInterval;

    /**
     * LinkIDLTQRPollingConfiguration constructor.
     * @param $pollAttempts
     * @param $pollInterval
     * @param $paymentPollAttempts
     * @param $paymentPollInterval
     */
    public function __construct($pollAttempts, $pollInterval, $paymentPollAttempts, $paymentPollInterval)
    {
        $this->pollAttempts = $pollAttempts;
        $this->pollInterval = $pollInterval;
        $this->paymentPollAttempts = $paymentPollAttempts;
        $this->paymentPollInterval = $paymentPollInterval;
    }
}

function parseLinkIDLTQRPollingConfiguration($xmlPollingConfiguration)
{
    return new LinkIDLTQRPollingConfiguration(
        isset($xmlPollingConfiguration->pollAttempts) ? $xmlPollingConfiguration->pollAttempts : -1,
        isset($xmlPollingConfiguration->pollInterval) ? $xmlPollingConfiguration->pollInterval : -1,
        isset($xmlPollingConfiguration->paymentPollAttempts) ? $xmlPollingConfiguration->paymentPollAttempts : -1,
        isset($xmlPollingConfiguration->paymentPollInterval) ? $xmlPollingConfiguration->paymentPollInterval : -1
    );
}