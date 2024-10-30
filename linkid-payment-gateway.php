<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Link_WP_LinkID_Payment_Gateway' ) ) {

	require_once( LINK_WP_LINKID_PLUGIN_DIR . 'linkid-payments.php' );

	class Link_WP_LinkID_Payment_Gateway extends WC_Payment_Gateway {

		const PAYMENT_METHOD_NAME = "linkID";

		const RESULT_STRING_SUCCESS = "success";
		const RESULT_STRING_CANCEL = "cancel";
		const RESULT_STRING_PENDING = "pending";
		const RESULT_STRING_ERROR = "error";

		/* @var $payment_provider Link_WP_LinkID_Payments */
		private $payment_provider;

		/**
		 * LinkID Gateway constructor.
		 */
		public function __construct() {
			$this->payment_provider = new Link_WP_LinkID_Payments();

			$this->id           = Link_WP_LinkID::PAYMENT_METHOD_ID;
			$this->method_title = self::PAYMENT_METHOD_NAME;

			if ( $this->get_option( "payment_method_icon" ) === "white-background" ) {
				$this->icon = LINK_WP_LINKID_PLUGIN_URL . "img/linkid-logo-small-white.jpg";
			} else if ( $this->get_option( "payment_method_icon" ) === "green-background" ) {
				$this->icon = LINK_WP_LINKID_PLUGIN_URL . "img/linkID-logo-green-background-small.png";
			}


			$this->has_fields = false;

			// Load the settings.
			$this->init_form_fields();
			if ( method_exists( $this, 'init_settings' ) ) {
				$this->init_settings();
			}

			$this->title       = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );
			$this->enabled     = $this->get_option( 'enabled' ) && $this->settings['enabled'] == 'yes' ? 'yes' : 'no';

			if ( has_action( 'woocommerce_update_options_payment_gateways' ) ) {
				add_action( 'woocommerce_update_options_payment_gateways', array(
					$this,
					'process_admin_options'
				) ); //Old woocommerce
			} else {
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id,
					array( $this, 'process_admin_options' ) );
			}

			add_action( 'woocommerce_receipt_' . Link_WP_LinkID::PAYMENT_METHOD_ID,
				array( $this, 'render_checkout_page' ) );

			add_action( 'woocommerce_receipt_linkid', array( &$this, 'render_checkout_page' ) );


			// Payment action ajax hooks
			// poll
			add_action( 'wp_ajax_link_linkid_payment_poll', array( $this, 'payment_poll' ) );
			add_action( 'wp_ajax_nopriv_link_linkid_payment_poll', array( $this, 'payment_poll' ) );

		}

		/**
		 * Overrides the get_option method in newer versions of WooCommerce. Takes into account settings from older
		 * versions of Woocommerce.
		 *
		 * @param string $key
		 * @param mixed $empty_value
		 *
		 * @return mixed The value specified for the option or a default value for the option
		 */
		function get_option( $key, $empty_value = null ) {
			if ( method_exists( get_parent_class(), 'get_option' ) ) {
				return parent::get_option( $key, $empty_value );
			} else {
				return $this->settings[ $key ];
			}
		}

		function init_form_fields() {
			$this->form_fields = array(
				'enabled'             => array(
					'title'    => __( 'Enable/Disable', 'woocommerce' ),
					'type'     => 'checkbox',
					'label'    => __( 'Enable LinkID Payment Gateway', 'link-linkid' ),
					'default'  => 'yes',
					'desc_tip' => true
				),
				'title'               => array(
					'title'       => __( 'Title', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Title of the linkID Payment Gateway during checkout', 'link-linkid' ),
					'default'     => __( 'linkID', 'link-linkid' ),
					'desc_tip'    => true
				),
				'description'         => array(
					'title'       => __( 'Description', 'woocommerce' ),
					'type'        => 'textarea',
					'description' => __( 'This is the description which the user sees during checkout.', 'link-linkid' ),
					'default'     => __( "Quick and secure payments using your smartphone.", 'link-linkid' ),
					'desc_tip'    => true
				),
				'payment_method_icon' => array(
					'title'       => __( 'Payment Method Icon', 'link-linkid' ),
					'type'        => 'select',
					'description' => __( 'Show linkID logo on checkout page.', 'link-linkid' ),
					'default'     => 'light',
					'desc_tip'    => true,
					'options'     => array(
						'none'             => __( "Don't show logo", "link-linkid" ),
						'green-background' => __( 'Logo green background', 'link-linkid' ),
						'white-background' => __( 'Logo white background', 'link-linkid' ),
					)
				)
			);
		}

		//=======================================
		// HANDLE PAYMENT POKES
		//=======================================
		/**
		 * Handle incoming payment poke. Maybe from linkID, maybe from a malignant source.
		 *
		 * @param $order_reference string the order reference (coming from outside world -- sanitize)
		 */
		public static function handle_payment_poke( $order_reference ) {

			if ( function_exists( 'wc_clean' ) ) {
				$order_reference = wc_clean( $order_reference );
			}

			$linkid_payment_client = Link_WP_LinkID::create_linkid_payment_client();

			$linkid_payment_status = null;
			try {
				$linkid_payment_status = $linkid_payment_client->getStatus( $order_reference );
			} catch ( Exception $e ) {
				error_log( "Could not fetch payment status for order reference {$order_reference}" );
				wp_die( "Could not fetch payment status for order reference {$order_reference}" );
			}

			if ( $linkid_payment_status && $linkid_payment_status != null ) {

				$payment_provider = new Link_WP_LinkID_Payments();
				$payment          = $payment_provider->getPaymentForOrderReference( $order_reference );

				if ( ! $payment ) {
					error_log( "Could not find payment in dB for order reference {$order_reference}" );
					wp_die( "Could not find payment in dB for order reference {$order_reference}" );
				}

				$order = new WC_Order( $payment->order_id );

				$order_just_paid = false;
				if ( $payment->state != LinkIDPaymentState::PAYED
				     && $linkid_payment_status->paymentState === LinkIDPaymentState::PAYED
				) {

					$order_just_paid = true;

					//Autocommit wallet transactions first
					/** @var LinkIDPaymentDetails $payment_details */
					$payment_details = $linkid_payment_status->paymentDetails;
					if ( ! empty( $payment_details->walletTransactions ) ) {

						$wallet_client = Link_WP_LinkID::create_linkid_wallet_client();

						/**  @var LinkIDWalletTransaction $wallet_transaction */
						foreach ( $payment_details->walletTransactions as $wallet_transaction ) {

							try {
								$wallet_client->commit(
									$linkid_payment_status->userId,
									$wallet_transaction->walletId,
									$wallet_transaction->transactionId
								);

								$order->add_order_note(
									sprintf(
										__( "Commited wallet transaction %s of %s %s", 'link-linkid' ),
										$wallet_transaction->transactionId,
										$wallet_transaction->amount / 100.0,
										self::linkid_currency_to_string( $wallet_transaction->currency )
									)
								);

							} catch ( Exception $e ) {
								error_log( "Could not commit wallet transaction {$wallet_transaction->transactionId} for order reference {$order_reference}" );
								wp_die( "Could not commit wallet transaction {$wallet_transaction->transactionId} for order reference {$order_reference}" );
							}
						}
					}
				}

				$order_payment_just_failed = false;
				if ( $payment->state != LinkIDPaymentState::FAILED &&
				     $linkid_payment_status->paymentState === LinkIDPaymentState::FAILED
				) {
					$order_payment_just_failed = true;
				}

				$updated_data = array(
					Link_WP_LinkID_Payments::STATE => $linkid_payment_status->paymentState
				);
				$payment_provider->updatePayment( $payment, $updated_data );

				$order->add_order_note(
					sprintf(
						__( "Payment status update for order reference %s. Status changed to %s", 'link-linkid' ),
						$order_reference,
						self::payment_state_to_string( $linkid_payment_status->paymentState )
					)
				);

				//
				// Only if everything went well should we reduce stock en set payment complete
				//
				if ( $order_just_paid ) {
					$order->payment_complete();
				}

				//
				// Only if everything went well, we should set error notice for failed payments
				//
				if ( $order_payment_just_failed ) {
					$order->update_status( 'failed', sprintf(
						__( "Payment failed notification came in for payment session with order reference %s", 'link-linkid' ),
						$order_reference
					) );
				}
			}
		}

		private static function get_supported_currencies() {
			return array( 'EUR' );
		}

		//=======================================
		// CHECKOUT PAGE METHODS
		//=======================================

		/**
		 *
		 * If linkID is chosen as payment method. This method is called when rendering the page.
		 *
		 * @param $order_id
		 */
		function render_checkout_page( $order_id ) {

			$order = new WC_Order( $order_id );

			// Cancel all possible running payment sessions and check if order has already been paid
			$payment_state = $this->get_payment_state_for_order( $order_id, true );
			if ( $payment_state === LinkIDPaymentState::PAYED || ! $this->needs_payment( $order ) ) {
				//redirect
				wp_safe_redirect( $this->get_return_url( $order ) );

				return;
			}

			$show_error_text = false;
			if ( ! empty( $_REQUEST["status"] ) ) {

				$result_string = $_REQUEST["status"];

				if ( ! empty( $result_string ) ) {

					if ( $result_string === self::RESULT_STRING_SUCCESS ) {
						//redirect
						wp_safe_redirect( $this->get_return_url( $order ) );

						return;

					} else if ( $result_string === self::RESULT_STRING_PENDING ) {

						//redirect
						wp_safe_redirect( $this->get_return_url( $order ) );

						return;

					} else if ( $result_string === self::RESULT_STRING_ERROR ) {
						$show_error_text = true;
						$error_text      = sprintf( __( 'Something went wrong during payment. Please try again. If the problem'
						                                . ' persists, please contact us.', 'link-linkid' ) );
					} else if ( $result_string === self::RESULT_STRING_CANCEL ) {
						$show_error_text = true;
						$error_text      = sprintf( __( 'The previous payment has been canceled. Please try again or cancel the payment and go back to cart.', 'link-linkid' ) );
					}

				}
			}


			//$linkIDAuthnSession used by payment block template

			$linkIDAuthnSession  = null;
			$payment_initialized = false;
			try {
				$linkIDAuthnSession  = $this->create_payment_session_for_order( $order );
				$payment_initialized = true;
			} catch ( Exception $e ) {
				error_log( "Could not initialize payment for order {$order_id}!" );
				error_log( $e );
			}

			//
			// Load the linkID payment template, style and scripts
			//

			if ( $payment_initialized ) {

				$cancel_order_url = $order->get_cancel_order_url();

				wp_register_script( 'link_linkid_payment_js', LINK_WP_LINKID_PLUGIN_URL . 'js/linkid-payment.js', array( 'jquery' ) );
				wp_enqueue_script( 'link_linkid_payment_js' );
				wp_localize_script( 'link_linkid_payment_js', 'Link_LinkID_Payment',
					array(
						'ajaxUrl'                       => admin_url( 'admin-ajax.php' ),
						'loadingImgUrl'                 => LINK_WP_LINKID_PLUGIN_URL . "img/loading.gif",
						'paymentAddRedirectMessage'     => __( "Please wait while you are being redirected to the payment provider.", 'link-linkid' ),
						'paymentSuccessRedirectMessage' => __( "Payment succeeded, please wait while you are being redirected.", 'link-linkid' ),
						'somethingWentWrongMessage'     => __( "Something went wrong. Please try again later.", 'link-linkid' ),
						'retryPaymentButtonText'        => __( "Retry Payment", 'link-linkid' )
					)
				);
			}

			wp_register_style( 'link_linkid_login_css', LINK_WP_LINKID_PLUGIN_URL . 'css/linkid-block.css' );
			wp_enqueue_style( 'link_linkid_login_css' );

			$template_path = LINK_WP_LINKID_PLUGIN_DIR . "templates/payment-block.php";
			include( $template_path );

		}

		/**
		 * Verifies all payment sessions for an order. If one of them has been payed, the called is informed that
		 * the order has been payed already
		 *
		 * @param $order_id int the id of the order for which to cancel the payments
		 * @param $cancel_existing_payments bool whether or not to cancel the existing payment instances
		 *
		 * @return LinkIDPaymentState|-1 the linkID payment state of the order. -1 if order has no linkID payments yet/all are canceled.
		 */
		private function get_payment_state_for_order( $order_id, $cancel_existing_payments = false ) {

			$order = new WC_Order( $order_id );

			$payments = $this->payment_provider->getPayments( $order_id );

			$client              = Link_WP_LinkID::create_linkid_auth_client();
			$linkIDPaymentClient = Link_WP_LinkID::create_linkid_payment_client();

			$payment_status = - 1;

			foreach ( $payments as $payment ) {

				if ( ! $payment->payment_session_canceled ) {

					if ( $cancel_existing_payments ) {
						try {

							$client->cancel( $payment->authn_session_id );
							$this->payment_provider->setPaymentCanceled( $payment->payment_order_reference );

							$order->add_order_note(
								sprintf(
									__( "Canceled payment session with payment order reference %s.", 'link-linkid' ),
									$payment->payment_order_reference
								)
							);


						} catch ( Exception $e ) {

							error_log( "Could not cancel payment session with ID {$payment->authn_session_id}." );

						}
					}


					$linkIDPaymentStatus = $linkIDPaymentClient->getStatus( $payment->payment_order_reference );

					if ( $linkIDPaymentStatus->paymentState === LinkIDPaymentState::PAYED ) {

						$payment_status = LinkIDPaymentState::PAYED;

					}
				}
			}

			return $payment_status;
		}

		/**
		 * Creates a linkID payment session for a given order.
		 *
		 * @param $order WC_Order the order for which to create a linkID payment session
		 *
		 * @return LinkIDAuthnSession LinkIDAuthnSession the payment session
		 *
		 * @throws Exception something went wrong initializing the payment session
		 */
		private function create_payment_session_for_order( $order ) {

			$orderReference      = null;
			$foundOrderReference = false;
			do {

				$orderReference = $this->gen_uuid();
				$payment        = $this->payment_provider->getPaymentForOrderReference( $orderReference );

				if ( ! ( $payment && $payment->order_id ) ) {
					$foundOrderReference = true;
				}

			} while ( ! $foundOrderReference );

			$paymentDescription = sprintf( __( "Payment for order on %s", 'link-linkid' ), get_bloginfo( 'name' ) );
			$orderMessage       = trim( $paymentDescription ) ? $paymentDescription . ": " : "";
			$orderMessage .= sprintf( __( "Order %s", "link-linkid" ), $order->id );

			$paymentContext = new LinkIDPaymentContext(
				new LinkIDPaymentAmount(
					$order->order_total * 100, //Amount
					$this->get_order_currency( $order ), //Currency
					null
				),
				$orderMessage
			);

			$paymentContext->allowPartial   = false;
			$paymentContext->onlyWallets    = false;
			$paymentContext->orderReference = $orderReference;

			$paymentContext->paymentAddBrowser = LinkIDPaymentAddBrowser::REDIRECT;

			$base_return_page = $this->get_payment_page_url( $order );

			$paymentContext->paymentMenu = new LinkIDPaymentMenu(
				str_replace( '&', '&amp;', $this->get_return_url( $order ) ),
				$this->create_order_received_payment_status_url( $base_return_page, self::RESULT_STRING_CANCEL ),
				str_replace( '&', '&amp;', $this->get_return_url( $order ) ),
				$this->create_order_received_payment_status_url( $base_return_page, self::RESULT_STRING_ERROR )
			);


			$linkIDAuthnSession = Link_WP_LinkID::start_authn_request( $paymentContext );

			$order->add_order_note(
				sprintf(
					__( "Created payment session for order with payment order reference %s.", 'link-linkid' ),
					$orderReference
				)
			);

			$this->payment_provider->createPayment( $order->id, $orderReference, $linkIDAuthnSession->sessionId );

			return $linkIDAuthnSession;
		}

		/**
		 * AJAX endpoint for polling a linkID authentication session.
		 * Returns a json_encoded PollResponse object.
		 *
		 * @throws Exception something went wrong
		 */
		function payment_poll() {

			$linkIDPollResponse = Link_WP_LinkID::poll_authn_session();

			if ( $linkIDPollResponse->authenticationState === LinkIDPollResponse::AUTH_STATE_FAILED ) {

				$response = json_encode( Link_WP_LinkID_PollResult::createFromError(
					__( "Payment failed. Click \"Retry Payment\".",
						"link-linkid" ), false, true ) );

				header( "Content-Type: application/json" );
				echo $response;

				die();

			} else if ( $linkIDPollResponse->authenticationState === LinkIDPollResponse::AUTH_STATE_EXPIRED ) {

				$response = json_encode( Link_WP_LinkID_PollResult::createFromError(
					__( "Payment expired. Click \"Retry Payment\".",
						"link-linkid" ), false, true ) );

				header( "Content-Type: application/json" );
				echo $response;

				die();

			} else if ( isset( $linkIDPollResponse->paymentState ) && $linkIDPollResponse->paymentState != null ) {

				/** @var LinkIDAuthnContext $authnContext */
				$authnContext = $linkIDPollResponse->authenticationContext;

				/** @var LinkIDPaymentResponse $paymentResponse */
				$paymentResponse = $authnContext->paymentResponse;

				$orderReference = $paymentResponse->orderReference;

				$response = null;

				if ( $linkIDPollResponse->paymentState === LinkIDPaymentState::PAYED ) {

					$payment  = $this->payment_provider->getPaymentForOrderReference( $orderReference );
					$order    = new WC_Order( $payment->order_id );
					$response = json_encode( Link_WP_LinkID_PollResult::createFromSuccessWithRedirect(
						$this->get_return_url( $order ) ) );

				} else if ( $linkIDPollResponse->paymentState === LinkIDPaymentState::FAILED ) {

					$response = json_encode( Link_WP_LinkID_PollResult::createFromError(
						__( "Payment failed. Click \"Retry Payment\".", "link-linkid" ), false, true ) );

				} else if ( $linkIDPollResponse->paymentState === LinkIDPaymentState::WAITING_FOR_UPDATE ) {

					$response = json_encode( Link_WP_LinkID_PollResult::createPendingPollResponse() );

				}


				header( "Content-Type: application/json" );
				echo $response;

				die();

			}


			$response = json_encode( Link_WP_LinkID_PollResult::createFromPollResponse( $linkIDPollResponse ) );

			//Make sure that user keeps polling if authnstate.AUTHENTICATED but not paymentstate.PAYED
			if ( $linkIDPollResponse->authenticationState === LinkIDPollResponse::AUTH_STATE_AUTHENTICATED ) {
				$response = json_encode( Link_WP_LinkID_PollResult::createPendingPollResponse() );
			}

			header( "Content-Type: application/json" );
			echo $response;

			die();
		}

		public function gen_uuid() {
			return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				// 32 bits for "time_low"
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

				// 16 bits for "time_mid"
				mt_rand( 0, 0xffff ),

				// 16 bits for "time_hi_and_version",
				// four most significant bits holds version number 4
				mt_rand( 0, 0x0fff ) | 0x4000,

				// 16 bits, 8 bits for "clk_seq_hi_res",
				// 8 bits for "clk_seq_low",
				// two most significant bits holds zero and one for variant DCE1.1
				mt_rand( 0, 0x3fff ) | 0x8000,

				// 48 bits for "node"
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
			);
		}

		function process_payment( $order_id ) {

			$order = new WC_Order( $order_id );

			return array( 'result' => 'success', 'redirect' => $this->get_payment_page_url( $order ) );
		}

		public function is_available() {
			return parent::is_available() && $this->is_valid_for_use();
		}

		/**
		 * Check if this gateway is enabled and available in the user's country
		 *
		 * @return bool
		 */
		public function is_valid_for_use() {

			$has_order      = false;
			$order_currency = get_woocommerce_currency();
			$order_id       = absint( get_query_var( 'order-pay' ) );

			// Gets order total from "pay for order" page.
			if ( 0 < $order_id ) {
				$order          = new WC_Order( $order_id );
				$has_order      = true;
				$order_currency = $this->get_order_currency( $order );
			}

			return Link_WP_LinkID::is_permalinks_enabled()
			       && in_array( get_woocommerce_currency(), self::get_supported_currencies() ) //In case we're in the checkout page
			       && ( ! $has_order || ( $has_order && in_array( $order_currency, self::get_supported_currencies() ) ) );// In case we're in the "pay for order" page.
		}

		/**
		 * Creates an order received url with additional payment status query param
		 *
		 * @param $base_url string the woocommerce order object
		 * @param $status_string string the payment status string
		 *
		 * @return string the order received url with additional payment status query param
		 */
		private function create_order_received_payment_status_url( $base_url, $status_string ) {

			return str_replace( '&', '&amp;', add_query_arg( array( 'status' => $status_string ), $base_url ) );
		}

		private static function payment_state_to_string( $linkIDPaymentStatus ) {

			$payment_state_string = __( "Initialized", "link-linkid" );

			switch ( $linkIDPaymentStatus ) {
				case LinkIDPaymentState::PAYED:
					$payment_state_string = __( "Payed", "link-linkid" );
					break;
				case LinkIDPaymentState::FAILED:
					$payment_state_string = __( "Failed", "link-linkid" );
					break;
				case LinkIDPaymentState::REFUND_STARTED:
					$payment_state_string = __( "Refund Started", "link-linkid" );
					break;
				case LinkIDPaymentState::REFUNDED:
					$payment_state_string = __( "Refunded", "link-linkid" );
					break;
				case LinkIDPaymentState::STARTED:
					$payment_state_string = __( "Started", "link-linkid" );
					break;
				case LinkIDPaymentState::WAITING_FOR_UPDATE:
					$payment_state_string = __( "Waiting for update", "link-linkid" );
					break;
			}

			return $payment_state_string;
		}

		private static function linkid_currency_to_string( $linkid_currency ) {

			$currency_string = __( "(Unknown currency)", "link-linkid" );

			switch ( $linkid_currency ) {
				case LinkIDCurrency::EUR:
					$currency_string = __( "euro", "link-linkid" );
					break;
			}

			return $currency_string;

		}

		/**
		 * Rewrite of needs_payment method on WC_Order (in later versions) to be able to access it on
		 * older woocommerce versions
		 *
		 * @param $order WC_Order the order for which to check whether it needs payment
		 *
		 * @return true if order needs payment, false otherwise
		 */
		private function needs_payment( $order ) {
			if ( method_exists( $order, 'needs_payment' ) ) {
				$needs_payment = $order->needs_payment();
			} else {
				if ( in_array( $order->status, array( 'pending', 'failed' ) ) && $order->get_total() > 0 ) {
					$needs_payment = true;
				} else {
					$needs_payment = false;
				}
			}

			return $needs_payment;
		}

		/**
		 * Generates and returns the linkID payment page for the order.
		 *
		 * @param $order WC_Order the woocommerce order
		 *
		 * @return string the payment page url
		 */
		private function get_payment_page_url( $order ) {
			//
			// Check if the woocommerce version supports argyments for this checkout method
			//
			$url_on_checkout     = $order->get_checkout_payment_url( true );
			$url_not_on_checkout = $order->get_checkout_payment_url( false );

			if ( $url_on_checkout === $url_not_on_checkout ) {
				// The older woocommerce version does not support arguments yet. Let's create our own URL
				$redirect_url = add_query_arg( 'order', $order->id, add_query_arg( 'key', $order->order_key, get_permalink( woocommerce_get_page_id( 'pay' ) ) ) );
			} else {
				// The older woocommerce version does support arguments yet. Let's create our own URL
				$redirect_url = $url_on_checkout;
			}

			return $redirect_url;
		}


		private function get_order_currency( $order ) {
			if ( method_exists( $order, 'get_order_currency' ) ) {
				return $order->get_order_currency();
			}

			//In older versions of woocommerce, the currency is not stored inside the order itself
			return get_woocommerce_currency();
		}
	}
}