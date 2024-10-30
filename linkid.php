<?php

/*
Plugin Name: linkID
Plugin URI: https://service.linkid.be
Description: Quick and secure login and payments using your smartphone.
Version: 0.1.2
Author: linkID NV
Author URI: https://service.linkid.be
License: BSD 3-Clause license (see LICENSE.txt in the root folder of the plugin)
Text Domain: link-linkid
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Link_WP_LinkID' ) ) {

	define( 'LINK_WP_LINKID_NAME', plugin_basename( __FILE__ ) );
	define( 'LINK_WP_LINKID_VERSION', '0.1' );
	define( 'LINK_WP_LINKID_PLUGIN_URL', plugins_url( '/', __FILE__ ) );
	define( 'LINK_WP_LINKID_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	define( 'LINK_WP_LINKID_PLUGIN_BASENAME_DIR', plugin_basename( LINK_WP_LINKID_PLUGIN_DIR ) );
	define( 'LINK_WP_LINKID_LIB_DIR', plugin_dir_path( __FILE__ ) . "lib/linkid/" );
	define( 'LINK_WP_LINKID_TEMPLATE_PATH', plugin_dir_path( __FILE__ ) . "templates/" );

	require_once( LINK_WP_LINKID_LIB_DIR . 'linkid-sdk-php/LinkIDAuthnSession.php' );

	class Link_WP_LinkID {


		protected $name = 'linkID';
		protected $version = '0.1';

		//
		// The linkID settings variable
		//
		const LINKID_SETTINGS_VARIABLE = 'link_linkid_settings';

		//
		// LinkID session variables
		//
		const SESSION_LINKID_AUTHN_SESSION = "link.linkID.AuthnSession";
		const SESSION_LINKID_SAML_UTIL = "link.linkID.SamlUtil";

		//
		// Payment poke settings
		//
		const UPDATE_PAYMENT_POKE_SLUG = "linkid-payment-state-update";
		const UPDATE_PAYMENT_POKE_QUERY_VAR = "linkid-payment-state-update";

		//
		// Payment method id
		//
		const PAYMENT_METHOD_ID = "linkid";


		/**
		 * Construct the plugin object
		 */
		public function __construct() {

			add_action( 'plugins_loaded', array( $this, 'load_linkid_textdomain' ) );

			//
			// For some reason there could be no session yet
			//
			if ( ! session_id() ) {
				add_action( 'init', 'session_start' );
			}

			if ( is_admin() ) {
				require_once( LINK_WP_LINKID_PLUGIN_DIR . 'linkid-admin.php' );
				new Link_WP_LinkID_Admin( $this );
			}

			if ( Link_WP_LinkID::get_option_value( self::SETTINGS_LOGIN_ACTIVE ) ) {
				require_once( LINK_WP_LINKID_PLUGIN_DIR . 'linkid-login.php' );
				new Link_WP_LinkID_Login( $this );
			}

			if ( self::is_woocommerce_active() ) {
				//
				// Init the payment gateway
				//
				add_action( 'plugins_loaded', array( $this, 'init_linkid_gateway' ), 0 );

				//
				// Create payment poke rewrite rules and catch the rewrites
				//
				add_filter( 'query_vars', array(
					$this,
					'add_payment_poke_query_var'
				) );  //Make payment poke query var known to wordpress
				add_action( 'init', array(
					$this,
					'add_payment_poke_endpoint'
				), 0 ); //Rewrite payment poke slug into query var
				add_action( 'parse_request', array(
					$this,
					'handle_payment_poke'
				), 0 ); // Handle the payment poke if query var is present
			}


		}

		/**
		 * Checks whether or not permalinks are enabled for the current Wordpress site
		 *
		 * @return bool true if permalinks are enabled for Wordpress Site
		 */
		public static function is_permalinks_enabled() {

			$permalink_option_value = get_option( 'permalink_structure' );

			$permalinks_enabled = ( $permalink_option_value && ! empty( $permalink_option_value ) );

			return $permalinks_enabled;
		}

		// ==================================================
		// PLUGIN ACTIVATION AND DEACTIVATION
		// ==================================================

		/**
		 * Activate the plugin
		 */
		public function activate() {
			//Set default plugin settings
			if ( ! get_option( self::LINKID_SETTINGS_VARIABLE ) ) {
				$defaultSettings = $this->get_default_plugin_settings();
				add_option( self::LINKID_SETTINGS_VARIABLE, $defaultSettings );
			}

			require_once( LINK_WP_LINKID_PLUGIN_DIR . 'linkid-payments.php' );
			Link_WP_LinkID_Payments::activate();

			//Add gateway for Woocommerce to find
			if ( class_exists( 'WC_Payment_Gateway', false ) ) {
				$this->add_payment_poke_endpoint();
				flush_rewrite_rules( true );
			}
		}


		/**
		 * Deactivate the plugin
		 */
		public function deactivate() {
			require_once( LINK_WP_LINKID_PLUGIN_DIR . 'linkid-payments.php' );
			Link_WP_LinkID_Payments::deactivate();
		}

		//=====================================
		// PAYMENT POKE SECTION
		//=====================================

		function add_payment_poke_query_var( $vars ) {
			$vars[] = self::UPDATE_PAYMENT_POKE_QUERY_VAR;

			return $vars;
		}

		function add_payment_poke_endpoint() {
			add_rewrite_rule(
				'^' . self::UPDATE_PAYMENT_POKE_SLUG . '\?orderRef=([1-9a-zA-Z\-]+)$',
				'index.php?' . self::UPDATE_PAYMENT_POKE_QUERY_VAR . '=$matches[1]',
				'top'
			);
		}

		function handle_payment_poke() {
			if ( self::is_woocommerce_active() ) {
				global $wp;

				if ( ! empty( $_GET["orderRef"] ) ) {
					$wp->query_vars["orderRef"] = $_GET["orderRef"];
				}

				// REST API request
				if ( $wp->request === self::UPDATE_PAYMENT_POKE_QUERY_VAR && ! empty( $wp->query_vars["orderRef"] ) ) {
					require_once( LINK_WP_LINKID_PLUGIN_DIR . 'linkid-payment-gateway.php' );
					Link_WP_LinkID_Payment_Gateway::handle_payment_poke( $wp->query_vars["orderRef"] );
					exit;
				}
			}
		}

		// ==================================================
		// PAYMENT GATEWAY INITIALIZATION
		// ==================================================

		/**
		 * Initialize the linkID gateway
		 */
		public function init_linkid_gateway() {
			if ( class_exists( 'WC_Payment_Gateway', false ) ) {
				require_once( LINK_WP_LINKID_PLUGIN_DIR . 'linkid-payment-gateway.php' );
				if ( $_REQUEST && array_key_exists( "action", $_REQUEST ) && $_REQUEST["action"] === "link_linkid_payment_poll" ) {
					new Link_WP_LinkID_Payment_Gateway(); //Load manually if linkID ajax request otherwise it wont get loaded on ajax request
				} else {
					add_filter( 'woocommerce_payment_gateways', array(
						$this,
						'add_linkid_payment_gateway'
					) ); //Otherwise let wp decide
				}
			}
		}

		/**
		 * Add the linkID Gateway to WooCommerce
		 **/
		public function add_linkid_payment_gateway( $methods ) {
			$methods[] = 'Link_WP_LinkID_Payment_Gateway';

			return $methods;
		}

		// ==================================================
		// HELPER METHODS PLUGIN SETTINGS
		// ==================================================

		//
		// Possible settings
		//
		const SETTINGS_APPLICATION_NAME = "application_name";
		const SETTINGS_APPLICATION_USERNAME = "application_username";
		const SETTINGS_APPLICATION_PASSWORD = "application_password";
		const SETTINGS_LINKID_PROFILE = "linkid_profile";
		const SETTINGS_LINKID_HOSTNAME = "linkid_hostname";
		const SETTINGS_LOGIN_ACTIVE = "login_active";
		const SETTINGS_LOGIN_MESSAGE = "login_message";
		const SETTINGS_LOGIN_SUCCESS_MESSAGE = "login_success_message";

		/**
		 * Returns the value for a specific key in the linkID settings variable
		 *
		 * @param $key string the key for which the value is needed
		 *
		 * @return string|null the string value for a given key in the linkID settings. null otherwise
		 */
		public static function get_option_value( $key ) {
			$options = get_option( self::LINKID_SETTINGS_VARIABLE );
			if ( $options && $options != null && isset( $options[ $key ] ) ) {
				return $options[ $key ];
			}

			return null;
		}


		public static function get_default_plugin_settings() {
			return array(
				self::SETTINGS_LINKID_PROFILE       => "",
				self::SETTINGS_APPLICATION_NAME     => "",
				self::SETTINGS_APPLICATION_USERNAME => "",
				self::SETTINGS_APPLICATION_PASSWORD => "",
				self::SETTINGS_LINKID_HOSTNAME      => "",
				self::SETTINGS_LOGIN_ACTIVE         => "0"
			);
		}

		// ==================================================
		// HELPER METHODS LINKID
		// ==================================================

		/**
		 * Creates a linkID authentication client using the linkID settings provided by the user
		 *
		 * @return LinkIDAuthClient an authentication client instance
		 */
		public static function create_linkid_auth_client() {
			require_once( LINK_WP_LINKID_LIB_DIR . "/linkid-sdk-php/LinkIDAuthClient.php" );

			return new LinkIDAuthClient(
				self::get_option_value( self::SETTINGS_LINKID_HOSTNAME ),
				self::get_option_value( self::SETTINGS_APPLICATION_USERNAME ),
				self::get_option_value( self::SETTINGS_APPLICATION_PASSWORD ),
				array()
			);
		}

		/**
		 * Creates a linkID payment client using the linkID settings provided by the user
		 *
		 * @return LinkIDPaymentClient an payment client instance
		 */
		public static function create_linkid_payment_client() {
			require_once( LINK_WP_LINKID_LIB_DIR . "/linkid-sdk-php/LinkIDPaymentClient.php" );

			return new LinkIDPaymentClient(
				self::get_option_value( self::SETTINGS_LINKID_HOSTNAME ),
				self::get_option_value( self::SETTINGS_APPLICATION_USERNAME ),
				self::get_option_value( self::SETTINGS_APPLICATION_PASSWORD ),
				array()
			);
		}

		/**
		 * Creates a linkID wallet client using the linkID settings provided by the user
		 *
		 * @return LinkIDWalletClient a wallet client instance
		 */
		public static function create_linkid_wallet_client() {
			require_once( LINK_WP_LINKID_LIB_DIR . "/linkid-sdk-php/LinkIDWalletClient.php" );

			return new LinkIDWalletClient(
				self::get_option_value( self::SETTINGS_LINKID_HOSTNAME ),
				self::get_option_value( self::SETTINGS_APPLICATION_USERNAME ),
				self::get_option_value( self::SETTINGS_APPLICATION_PASSWORD ),
				array()
			);
		}

		/**
		 * Generates a linkID authentication session with optional payment context.
		 * Store the authentication session inside the php session to be able to poll later on.
		 *
		 * Removes any existing authentication sessions from the php session
		 *
		 * @param $paymentContext LinkIDPaymentContext optional payment context
		 *
		 * @return LinkIDAuthnSession the linkID authentication session
		 */
		public static function start_authn_request( $paymentContext = null ) {

			require_once( LINK_WP_LINKID_LIB_DIR . 'linkid-sdk-php/LinkIDSaml2.php' );
			require_once( LINK_WP_LINKID_LIB_DIR . 'linkid-sdk-php/LinkIDLoginConfig.php' );

			//First cleanup any existing authn session
			self::cleanup_authn_session();

			$client = self::create_linkid_auth_client();

			$linkIDSAMLUtil = new LinkIDSaml2();

			$authnRequest = $linkIDSAMLUtil->generateAuthnRequest(

				self::get_option_value( self::SETTINGS_APPLICATION_NAME ), //appName
				new LinkIDLoginConfig( self::get_option_value( self::SETTINGS_LINKID_HOSTNAME ) ), //loginConfig
				self::get_option_value( self::SETTINGS_LINKID_HOSTNAME ), //loginPage
				null, //clientAuthnMessage
				null, //clientFinishedMessage
				array( self::get_option_value( self::SETTINGS_LINKID_PROFILE ) ), //identityProfiles
				null, //attributeSuggestions
				$paymentContext, //paymentContext
				null //callback
			);

			$linkIDAuthnSession = $client->start( $authnRequest, get_locale() );

			$_SESSION[ self::SESSION_LINKID_SAML_UTIL ]     = $linkIDSAMLUtil;
			$_SESSION[ self::SESSION_LINKID_AUTHN_SESSION ] = $linkIDAuthnSession;

			return $linkIDAuthnSession;
		}

		/**
		 * Polls the linkID authentication session stored inside the php session.
		 *
		 * @return LinkIDPollResponse the linkID poll response containing the state
		 *                            of the authentication session
		 */
		public static function poll_authn_session() {

			require_once( LINK_WP_LINKID_LIB_DIR . 'linkid-sdk-php/LinkIDSaml2.php' );

			$linkIDAuthnSession = $_SESSION[ self::SESSION_LINKID_AUTHN_SESSION ];
			$saml2Util          = $_SESSION[ self::SESSION_LINKID_SAML_UTIL ];

			$client = self::create_linkid_auth_client();

			return $client->poll( $saml2Util, $linkIDAuthnSession->sessionId );
		}

		public static function cleanup_authn_session() {
			$_SESSION[ self::SESSION_LINKID_AUTHN_SESSION ] = null;
			$_SESSION[ self::SESSION_LINKID_SAML_UTIL ]     = null;
		}

		// ==================================================
		// HELPER METHODS GENERAL
		// ==================================================

		public static function is_woocommerce_active() {
			$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
			foreach ( $active_plugins as $active_plugin ) {

				if ( isset( $active_plugin ) && strpos( $active_plugin, 'woocommerce.php' ) !== false ) {
					return true;
				}
			}

			return false;
		}

		public static function get_wc_permalink( $key ) {
			if ( function_exists( 'wc_get_page_id' ) ) {
				return get_permalink( wc_get_page_id( $key ) );
			} else {
				return get_permalink( woocommerce_get_page_id( $key ) );
			}
		}

		function load_linkid_textdomain() {
			$loaded = load_plugin_textdomain( 'link-linkid', false, LINK_WP_LINKID_PLUGIN_BASENAME_DIR . '/lang' );
		}

	}
}

if ( class_exists( 'Link_WP_LinkID' ) ) {

	$wp_plugin_template = new Link_WP_LinkID();

	register_activation_hook( __FILE__, array( $wp_plugin_template, 'activate' ) );
	register_deactivation_hook( __FILE__, array( $wp_plugin_template, 'deactivate' ) );
}