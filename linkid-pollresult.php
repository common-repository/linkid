<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Link_WP_LinkID_PollResult' ) ) {
	class Link_WP_LinkID_PollResult {

		public $linkIDState;
		public $qrCodeImageEncoded;
		public $errorMessage;
		public $successMessage;
		public $ltqrReference;
		public $redirectUrl;
		public $shouldRefresh;
		public $shouldRedirect;
		public $responseMessage;
		public $canRetry;

		public static function createFromPollResponse( LinkIDPollResponse $pollResponse, $shouldRefresh = true, $responseMessage = null ) {
			$obj                  = new Link_WP_LinkID_PollResult();
			$obj->linkIDState     = self::parseAuthnState( $pollResponse->authenticationState );
			$obj->shouldRefresh   = $shouldRefresh;
			$obj->responseMessage = $responseMessage;
			if ( isset( $pollResponse->paymentMenuURL ) ) {
				$obj->redirectUrl = $pollResponse->paymentMenuURL;
			}

			return $obj;
		}

		public static function createFromError(
			$errorMessage, $shouldRefresh = false, $canRetry = false,
			$shouldRedirect = false, $redirectUrl = null
		) {
			$obj                 = new Link_WP_LinkID_PollResult();
			$obj->linkIDState    = "ERROR";
			$obj->errorMessage   = $errorMessage;
			$obj->shouldRefresh  = $shouldRefresh;
			$obj->canRetry       = $canRetry;
			$obj->shouldRedirect = $shouldRedirect;
			$obj->redirectUrl    = $redirectUrl;

			return $obj;
		}

		public static function createFromAuthnSession( LinkIDAuthnSession $authnSession ) {
			$obj                     = new Link_WP_LinkID_PollResult();
			$obj->linkIDState        = "AUTH_STATE_STARTED";
			$obj->qrCodeImageEncoded = $authnSession->qrCodeImageEncoded;

			return $obj;
		}

		public static function createFromLTQRSession( LinkIDLTQRSession $ltqrSession ) {
			$obj                     = new Link_WP_LinkID_PollResult();
			$obj->linkIDState        = "AUTH_STATE_STARTED";
			$obj->qrCodeImageEncoded = base64_encode( $ltqrSession->qrCodeImage );
			$obj->ltqrReference      = $ltqrSession->ltqrReference;

			return $obj;
		}

		public static function createPendingPollResponse() {
			$obj              = new Link_WP_LinkID_PollResult();
			$obj->linkIDState = "AUTH_STATE_RETRIEVED";

			return $obj;
		}

		public static function createResultFromSuccessfulPayment() {
			$obj              = new Link_WP_LinkID_PollResult();
			$obj->linkIDState = "AUTH_STATE_AUTHENTICATED";

			return $obj;
		}


		public static function createResultFromSuccess( $successMessage ) {
			$obj                 = new Link_WP_LinkID_PollResult();
			$obj->linkIDState    = "AUTH_STATE_AUTHENTICATED";
			$obj->successMessage = $successMessage;
			$obj->shouldRefresh  = true;

			return $obj;
		}

		public static function createFromPendingPayment() {
			$obj              = new Link_WP_LinkID_PollResult();
			$obj->linkIDState = "AUTH_STATE_STARTED";

			return $obj;
		}

		public static function createFromSuccessWithRedirect( $redirectUrl ) {

			$obj              = new Link_WP_LinkID_PollResult();
			$obj->linkIDState = "AUTH_STATE_AUTHENTICATED";
			$obj->redirectUrl = $redirectUrl;

			return $obj;

		}

		public static function parseAuthnState( $authenticationState ) {

			$returnValue = "UNDEFINED";

			switch ( $authenticationState ) {
				case 0:
					$returnValue = "AUTH_STATE_STARTED";
					break;
				case 1:
					$returnValue = "AUTH_STATE_RETRIEVED";
					break;
				case 2:
					$returnValue = "AUTH_STATE_AUTHENTICATED";
					break;
				case 3:
					$returnValue = "AUTH_STATE_EXPIRED";
					break;
				case 5:
					$returnValue = "AUTH_STATE_PAYMENT_ADD";
					break;
				case 4:
					$returnValue = "ERROR";
					break;
			}

			return $returnValue;

		}
	}
}
