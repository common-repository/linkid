<?php

require_once('LinkIDWSSoapClient.php');
require_once('LinkIDPaymentContext.php');
require_once('LinkIDCallback.php');
require_once('LinkIDLTQRSession.php');
require_once('LinkIDLTQRClientSession.php');
require_once('LinkIDLTQRInfo.php');
require_once('LinkIDLTQRPollingConfiguration.php');

/*
 * linkID LTQR WS client
 *
 * @author Wim Vandenhaute
 */

class LinkIDLTQRClient
{

    private $client;

    /**
     * Constructor
     *
     * @param $linkIDHost string the linkID host ( https://<linkIDHost>/linkid-ws-username
     * @param $username string the WS-Security username
     * @param $password string the WS-Security password
     * @param array $options [optional]
     *
     */
    public function __construct($linkIDHost, $username, $password, array $options = array())
    {

        $wsdlLocation = "https://" . $linkIDHost . "/linkid-ws-username/ltqr40?wsdl";

        $this->client = new LinkIDWSSoapClient($wsdlLocation);
        $this->client->__setUsernameToken($username, $password, 'PasswordDigest');

    }

    /**
     * Push a long term QR session to linkID.
     *
     * @param $authenticationMessage [optional] authentication message to be shown in the pin view in the mobile app. If there is a payment, this will be ignored.
     * @param $finishedMessage [optional] finished message on the final view in the mobile app.
     * @param null $paymentContext LinkIDPaymentContext Optional payment context
     * @param bool|false $oneTimeUse
     * @param null $expiryDate Optional expiry date of the long term session.
     * @param null $expiryDuration Optional expiry duration of the long term session. Expressed in number of seconds starting from the creation.
     *                       Do not mix this attribute with expiryDate. If so, expiryDate will be preferred.
     * @param null $callback Optional callback config
     * @param null $identityProfiles Optional identity profiles
     * @param null $sessionExpiryOverride optional session expiry (seconds)
     * @param null $theme optional theme, if not specified default application theme will be chosen
     *
     * @param null $mobileLandingSuccess
     * @param null $mobileLandingError
     * @param null $mobileLandingCancel
     * @param null $pollingConfiguration LinkIDLTQRPollingConfiguration optional polling configuration
     * @param bool|false $waitForUnlock Marks the LTQR to wait for an explicit unlock call. This only makes sense for single-use LTQR codes. Unlock the LTQR with the change operation with unlock=true
     *
     * Success object containing the QR in PNG format, the content of the QR code and a type 4 UUID session ID of the created long term session. This
     * session ID will be used in the notifications to the Service Provider.
     * @return LinkIDLTQRSession the created linkID LTQR session
     * @throws Exception
     * @internal param bool $false Long $oneTimeUse Long term QR session can only be used once
     */
    public function push($authenticationMessage, $finishedMessage, $paymentContext = null,
                         $oneTimeUse = false, $expiryDate = null, $expiryDuration = null,
                         $callback = null, $identityProfiles = null, $sessionExpiryOverride = null, $theme = null,
                         $mobileLandingSuccess = null, $mobileLandingError = null, $mobileLandingCancel = null,
                         $pollingConfiguration = null, $waitForUnlock = false)
    {

        $requestParams = new stdClass;

        $requestParams->authenticationMessage = $authenticationMessage;
        $requestParams->finishedMessage = $finishedMessage;

        if (null != $paymentContext) {
            $requestParams->paymentContext = new stdClass;
            $requestParams->paymentContext->amount = $paymentContext->amount->amount;
            if (null != $paymentContext->amount && null != $paymentContext->amount->currency) {
                $requestParams->paymentContext->currency = linkIDCurrencyToString($paymentContext->currency);
            }
            if (null != $paymentContext->amount && null != $paymentContext->amount->walletCoin) {
                $requestParams->paymentContext->walletCoin = linkIDCurrencyToString($paymentContext->walletCoin);
            }
            $requestParams->paymentContext->description = $paymentContext->description;
            $requestParams->paymentContext->orderReference = $paymentContext->orderReference;
            $requestParams->paymentContext->paymentProfile = $paymentContext->profile;
            $requestParams->paymentContext->validationTime = $paymentContext->validationTime;
            $requestParams->paymentContext->allowPartial = $paymentContext->allowPartial;
            $requestParams->paymentContext->onlyWallets = $paymentContext->onlyWallets;
            $requestParams->paymentContext->mandate = null != $paymentContext->mandate;
            if (null != $paymentContext->mandate) {
                $requestParams->paymentContext->mandateDescription = $paymentContext->mandate->description;
                $requestParams->paymentContext->mandateReference = $paymentContext->mandate->reference;
            }
        }

        if (null != $callback) {
            $requestParams->callback = new stdClass;
            $requestParams->callback->location = $callback->location;
            $requestParams->callback->appSessionId = $callback->appSessionId;
            $requestParams->callback->inApp = $callback->inApp;
        }

        if (null != $identityProfiles) {
            $requestParams->identityProfiles = array();
            foreach ($identityProfiles as $identityProfile) {
                $requestParams->identityProfiles[] = $identityProfile;
            }
        }

        if (null != $pollingConfiguration) {
            $requestParams->pollingConfiguration = new stdClass;
            $requestParams->pollingConfiguration->pollAttempts = $pollingConfiguration->pollAttempts;
            $requestParams->pollingConfiguration->pollInterval = $pollingConfiguration->pollInterval;
            $requestParams->pollingConfiguration->paymentPollAttempts = $pollingConfiguration->paymentPollAttempts;
            $requestParams->pollingConfiguration->paymentPollInterval = $pollingConfiguration->paymentPollInterval;
        }

        $requestParams->oneTimeUse = $oneTimeUse;
        if (null != $expiryDate) {
            /** @noinspection PhpUndefinedMethodInspection */
            $requestParams->expiryDate = $expiryDate->format(DateTime::ATOM);
        }
        if (null != $expiryDuration) {
            $requestParams->expiryDuration = $expiryDuration;
        }

        if (null != $sessionExpiryOverride) {
            $requestParams->sessionExpiryOverride = $sessionExpiryOverride;
        }
        if (null != $theme) {
            $requestParams->theme = $theme;
        }

        if (null != $mobileLandingSuccess) {
            $requestParams->mobileLandingSuccess = $mobileLandingSuccess;
        }
        if (null != $mobileLandingError) {
            $requestParams->mobileLandingError = $mobileLandingError;
        }
        if (null != $mobileLandingCancel) {
            $requestParams->mobileLandingCancel = $mobileLandingCancel;
        }

        $requestParams->waitForUnlock = $waitForUnlock;

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->client->push($requestParams);

        if (isset($response->error) && null != $response->error) {
            throw new Exception('Error: ' . $response->error->errorCode);
        }

        $qrCodeImage = base64_decode($response->success->encodedQR);

        return new LinkIDLTQRSession($qrCodeImage, $response->success->qrContent, $response->success->ltqrReference,
            isset($response->success->paymentOrderReference) ? $response->success->paymentOrderReference : null);
    }

    /**
     * @param $ltqrReference String LTQR reference, mandatory
     * @param $authenticationMessage String authentication message to be shown in the pin view in the mobile app. If there is a payment, this will be ignored.
     * @param $finishedMessage String finished message on the final view in the mobile app.
     * @param LinkIDPaymentContext $paymentContext Optional payment context
     * @param null $expiryDate Optional expiry date of the long term session.
     * @param null $expiryDuration Optional expiry duration of the long term session. Expressed in number of seconds starting from the creation.
     *                              Do not mix this attribute with expiryDate. If so, expiryDate will be preferred.
     * @param LinkIDCallback $callback Optional callback config
     * @param $identityProfiles String[] identity profiles
     * @param $sessionExpiryOverride int session expiry (seconds)
     * @param $theme string theme, if not specified default application theme will be chosen
     * @param $resetUsed bool Optional flag for single use LTQR codes to let them be used again one time. If multi use this flag does nothing.
     * @param null $pollingConfiguration LinkIDLTQRPollingConfiguration optional polling configuration
     * @param bool|false $waitForUnlock Marks the LTQR to wait for an explicit unlock call. This only makes sense for single-use LTQR codes. Unlock the LTQR with the change operation with unlock=true
     * @param bool|false $unlock Unlocks the LTQR. When the first linkID user has finished for this LTQR, it will go back to locked if waitForUnlock=true.
     * @return LinkIDLTQRSession
     * @throws Exception
     */
    public function change($ltqrReference, $authenticationMessage, $finishedMessage, $paymentContext = null,
                           $expiryDate = null, $expiryDuration = null, $callback = null, $identityProfiles = null,
                           $sessionExpiryOverride = null, $theme = null, $resetUsed = false,
                           $pollingConfiguration = null, $waitForUnlock = false, $unlock = false)
    {
        $requestParams = new stdClass;
        $requestParams->ltqrReference = $ltqrReference;

        $requestParams->authenticationMessage = $authenticationMessage;
        $requestParams->finishedMessage = $finishedMessage;

        if (null != $paymentContext) {
            $requestParams->paymentContext = new stdClass;
            $requestParams->paymentContext->amount = $paymentContext->amount->amount;
            if (null != $paymentContext->amount && null != $paymentContext->amount->currency) {
                $requestParams->paymentContext->currency = linkIDCurrencyToString($paymentContext->currency);
            }
            if (null != $paymentContext->amount && null != $paymentContext->amount->walletCoin) {
                $requestParams->paymentContext->walletCoin = linkIDCurrencyToString($paymentContext->walletCoin);
            }
            $requestParams->paymentContext->description = $paymentContext->description;
            $requestParams->paymentContext->orderReference = $paymentContext->orderReference;
            $requestParams->paymentContext->paymentProfile = $paymentContext->profile;
            $requestParams->paymentContext->validationTime = $paymentContext->validationTime;
            $requestParams->paymentContext->allowPartial = $paymentContext->allowPartial;
            $requestParams->paymentContext->onlyWallets = $paymentContext->onlyWallets;
            $requestParams->paymentContext->mandate = null != $paymentContext->mandate;
            if (null != $paymentContext->mandate) {
                $requestParams->paymentContext->mandateDescription = $paymentContext->mandate->description;
                $requestParams->paymentContext->mandateReference = $paymentContext->mandate->reference;
            }
        }

        if (null != $callback) {
            $requestParams->callback = new stdClass;
            $requestParams->callback->location = $callback->location;
            $requestParams->callback->appSessionId = $callback->appSessionId;
            $requestParams->callback->inApp = $callback->inApp;
        }

        if (null != $identityProfiles) {
            $requestParams->identityProfiles = array();
            foreach ($identityProfiles as $identityProfile) {
                $requestParams->identityProfiles[] = $identityProfile;
            }
        }

        if (null != $pollingConfiguration) {
            $requestParams->pollingConfiguration = new stdClass;
            $requestParams->pollingConfiguration->pollAttempts = $pollingConfiguration->pollAttempts;
            $requestParams->pollingConfiguration->pollInterval = $pollingConfiguration->pollInterval;
            $requestParams->pollingConfiguration->paymentPollAttempts = $pollingConfiguration->paymentPollAttempts;
            $requestParams->pollingConfiguration->paymentPollInterval = $pollingConfiguration->paymentPollInterval;
        }

        if (null != $expiryDate) {
            /** @noinspection PhpUndefinedMethodInspection */
            $requestParams->expiryDate = $expiryDate->format(DateTime::ATOM);
        }
        if (null != $expiryDuration) {
            $requestParams->expiryDuration = $expiryDuration;
        }

        if (null != $sessionExpiryOverride) {
            $requestParams->sessionExpiryOverride = $sessionExpiryOverride;
        }
        if (null != $theme) {
            $requestParams->theme = $theme;
        }

        $requestParams->resetUsed = $resetUsed;
        $requestParams->waitForUnlock = $waitForUnlock;
        $requestParams->unlock = $unlock;

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->client->change($requestParams);

        if (isset($response->error) && null != $response->error) {
            throw new Exception('Error: ' . $response->error->errorCode);
        }

        $qrCodeImage = base64_decode($response->success->encodedQR);

        return new LinkIDLTQRSession($qrCodeImage, $response->success->qrContent, $response->success->ltqrReference,
            isset($response->success->paymentOrderReference) ? $response->success->paymentOrderReference : null);

    }

    /**
     * Fetch a set of client sessions.
     *
     * @param null $ltqrReferences Optional list of ltqrReferences to fetch. If none are specified, all LTQR sessions and client session are returned.
     * @param null $paymentOrderReferences Optional list of Payment order References to fetch. If none are specified, all are fetched for the LTQR References
     *                               specified above.
     * @param null $clientSessionIds optional list of client session IDs
     *
     * returns list of client sessions
     * @return array
     * @throws Exception
     */
    public function pull($ltqrReferences = null, $paymentOrderReferences = null, $clientSessionIds = null)
    {

        $requestParams = new stdClass;

        if (null != $ltqrReferences) {
            $requestParams->ltqrReferences = array();
            foreach ($ltqrReferences as $ltqrReference) {
                $requestParams->ltqrReferences[] = $ltqrReference;
            }
        }

        if (null != $paymentOrderReferences) {
            $requestParams->paymentOrderReferences = array();
            foreach ($paymentOrderReferences as $paymentOrderReference) {
                $requestParams->paymentOrderReferences[] = $paymentOrderReference;
            }
        }

        if (null != $clientSessionIds) {
            $requestParams->clientSessionIds = array();
            foreach ($clientSessionIds as $clientSessionId) {
                $requestParams->clientSessionIds[] = $clientSessionId;
            }
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->client->pull($requestParams);

        if (isset($response->error) && null != $response->error) {
            throw new Exception('Error: ' . $response->error->errorCode);
        }

        $clientSessions = array();
        foreach ($response->success as $session) {

            $qrCodeImage = base64_decode($session->encodedQR);

            $clientSessions[] = new LinkIDLTQRClientSession($qrCodeImage, $session->qrContent, $session->ltqrReference,
                $session->clientSessionId, $session->userId, $session->created,
                $session->paymentOrderReference, $session->paymentStatus);
        }

        return $clientSessions;
    }

    /**
     * Remove a set of client sessions.
     *
     * @param null $ltqrReferences Optional list of LTQR References to remove. If none are specified, all LTQR sessions and client session are removed.
     * @param null $paymentOrderReferences Optional list of Payment order References to remove. If none are specified, all are removed for the LTQR References specified above.
     * @param null $clientSessionIds optional list of client session IDs to remove
     * @throws Exception
     */
    public function remove($ltqrReferences = null, $paymentOrderReferences = null, $clientSessionIds = null)
    {

        $requestParams = new stdClass;

        if (null != $ltqrReferences) {
            $requestParams->ltqrReferences = array();
            foreach ($ltqrReferences as $ltqrReference) {
                $requestParams->ltqrReferences[] = $ltqrReference;
            }
        }

        if (null != $paymentOrderReferences) {
            $requestParams->paymentOrderReferences = array();
            foreach ($paymentOrderReferences as $paymentOrderReference) {
                $requestParams->paymentOrderReferences[] = $paymentOrderReference;
            }
        }

        if (null != $clientSessionIds) {
            $requestParams->clientSessionIds = array();
            foreach ($clientSessionIds as $clientSessionId) {
                $requestParams->clientSessionIds[] = $clientSessionId;
            }
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->client->remove($requestParams);

        if (isset($response->error) && null != $response->error) {
            throw new Exception('Error: ' . $response->error->errorCode);
        }

        // all good, return
        return;
    }

    /**
     * @param null $ltqrReferences
     * @return array
     * @throws Exception
     */
    public function info($ltqrReferences = null)
    {
        $requestParams = new stdClass;

        if (null != $ltqrReferences) {
            $requestParams->ltqrReferences = array();
            foreach ($ltqrReferences as $ltqrReference) {
                $requestParams->ltqrReferences[] = $ltqrReference;
            }
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->client->info($requestParams);

        if (isset($response->error) && null != $response->error) {
            throw new Exception('Error: ' . $response->error->errorCode);
        }

        $infos = array();
        foreach ($response->success as $ltqrInfo) {

            $qrCodeImage = isset($ltqrInfo->encodedQR) ? base64_decode($ltqrInfo->encodedQR) : null;

            // payment context
            $paymentContext = null;
            if (isset($ltqrInfo->paymentContext)) {
                $paymentContext = parseLinkIDPaymentContext($ltqrInfo->paymentContext);
            }

            // callback
            $callback = null;
            if (isset($ltqrInfo->callback)) {
                $callback = parseLinkIDCallback($ltqrInfo->callback);
            }

            // polling configuration
            $pollingConfiguration = null;
            if (isset($ltqrInfo->pollingConfiguration)) {
                $pollingConfiguration = parseLinkIDLTQRPollingConfiguration($ltqrInfo->pollingConfiguration);
            }

            $infos[] = new LinkIDLTQRInfo(
                isset($ltqrInfo->ltqrReference) ? $ltqrInfo->ltqrReference : null,
                isset($ltqrInfo->sessionId) ? $ltqrInfo->sessionId : null,
                isset($ltqrInfo->created) ? $ltqrInfo->created : null,
                $qrCodeImage,
                isset($ltqrInfo->qrContent) ? $ltqrInfo->qrContent : null,
                isset($ltqrInfo->authenticationMessage) ? $ltqrInfo->authenticationMessage : null,
                isset($ltqrInfo->finishedMessage) ? $ltqrInfo->finishedMessage : null,
                isset($ltqrInfo->oneTimeUse) ? $ltqrInfo->oneTimeUse : false,
                isset($ltqrInfo->expiryDate) ? $ltqrInfo->expiryDate : null,
                isset($ltqrInfo->expiryDuration) ? $ltqrInfo->expiryDuration : null,
                $paymentContext,
                $callback,
                isset($ltqrInfo->identityProfiles) ? $ltqrInfo->identityProfiles : null,
                isset($ltqrInfo->sessionExpiryOverride) ? $ltqrInfo->sessionExpiryOverride : null,
                isset($ltqrInfo->theme) ? $ltqrInfo->theme : null,
                isset($ltqrInfo->mobileLandingSuccess) ? $ltqrInfo->mobileLandingSuccess : null,
                isset($ltqrInfo->mobileLandingError) ? $ltqrInfo->mobileLandingError : null,
                isset($ltqrInfo->mobileLandingCancel) ? $ltqrInfo->mobileLandingCancel : null,
                $pollingConfiguration,
                isset($ltqrInfo->waitForUnlock) ? $ltqrInfo->waitForUnlock : false,
                isset($ltqrInfo->locked) ? $ltqrInfo->locked : false
            );
        }

        return $infos;
    }
}