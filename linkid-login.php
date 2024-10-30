<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once( LINK_WP_LINKID_LIB_DIR . 'linkid-sdk-php/LinkIDAttributeClient.php' );
require_once( LINK_WP_LINKID_PLUGIN_DIR . 'linkid-pollresult.php' );

if ( ! class_exists( 'Link_WP_LinkID_Login' ) ) {
	class Link_WP_LinkID_Login {

		//
		// The user meta key for linkID user ID
		//
		const WP_USER_META_VALUE_LINKID_USER_ID = "link_linkid_user_id";

		/**
		 * Link_WC_LinkID_Login constructor.
		 *
		 */
		public function __construct() {
			$this->init_hooks();
		}

		private function init_hooks() {
			// ==================================================
			// LOGIN BLOCK RENDERING HOOKS
			// ==================================================

			add_action( 'login_form', array( $this, 'render_login_block_wp' ) );
			add_action( 'edit_user_profile', array( $this, 'render_merge_block_wc_my_account' ), 300 );
			add_action( 'show_user_profile', array( $this, 'render_merge_block_wc_my_account' ), 300 );

			if ( Link_WP_LinkID::is_woocommerce_active() ) {
				if ( has_action( 'woocommerce_login_form_end' ) ) {
					add_action( 'woocommerce_login_form_start', array( $this, 'render_login_block_wc_login' ) );
				} else {
					add_action( 'woocommerce_after_template_part', array( $this, 'render_login_block_wc_login_old' ) );
				}
				add_action( 'woocommerce_before_my_account', array( $this, 'render_merge_block_wc_my_account' ) );
			}

			// ==================================================
			// LOGIN BLOCK AJAX ENDPOINT HOOKS
			// ==================================================

			add_action( 'wp_ajax_link_linkid_login_init', array( $this, 'login_init' ) );
			add_action( 'wp_ajax_nopriv_link_linkid_login_init', array( $this, 'login_init' ) );
			add_action( 'wp_ajax_link_linkid_login_poll', array( $this, 'login_poll' ) );
			add_action( 'wp_ajax_nopriv_link_linkid_login_poll', array( $this, 'login_poll' ) );
			add_action( 'wp_ajax_link_linkid_unlink', array( $this, 'login_unlink' ) );
			add_action( 'wp_ajax_nopriv_link_linkid_unlink', array( $this, 'login_unlink' ) );
		}

		public static function can_register() {

			return ( Link_WP_LinkID::is_woocommerce_active()
			         && ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes'
			              || get_option( 'woocommerce_enable_signup_and_login_from_checkout' ) == 'yes' ) )
			       || get_option( 'users_can_register' );

		}

		// ==================================================
		// LOGIN BLOCK RENDERING
		// ==================================================
		function render_merge_block_wc_my_account() {
			$current_user   = wp_get_current_user();
			$linkid_user_id = get_user_meta( $current_user->ID, self::WP_USER_META_VALUE_LINKID_USER_ID, true );

			if ( empty( $linkid_user_id ) ) {

				$this->render_login_block( null, true, true );

			} else {

				wp_register_script( 'link_linkid_unlink_js', LINK_WP_LINKID_PLUGIN_URL . 'js/linkid-unlink-accounts.js', array( 'jquery' ) );
				wp_enqueue_script( 'link_linkid_unlink_js' );

				$jsVarSubstitution = array(
					'ajaxUrl'             => admin_url( 'admin-ajax.php' ),
					'defaultErrorMessage' => __( "Something went wrong fetching the QR code. Please try again later.", 'link-linkid' ),
					'loadingImgUrl'       => LINK_WP_LINKID_PLUGIN_URL . "img/loading.gif"
				);
				wp_localize_script( 'link_linkid_unlink_js', 'Link_LinkID_Login', $jsVarSubstitution );

				wp_register_style( 'link_linkid_login_css', LINK_WP_LINKID_PLUGIN_URL . 'css/linkid-block.css' );
				wp_enqueue_style( 'link_linkid_login_css' );

				$template_path = LINK_WP_LINKID_PLUGIN_DIR . "templates/linkid-merged-block.php";
				include( $template_path );
			}

		}

		function render_login_block_wp() {
			$this->render_login_block();
		}

		function render_login_block_wc_login_old( $template_name ) {

			if ( in_array( $template_name, array( "myaccount/form-login.php", "checkout/form-login.php" ) ) ) {
				$this->render_login_block_wc_login();
			}
		}

		function render_login_block_wc_login() {
			global $post;

			if ( is_shop() ) {
				$page_id = Link_WP_LinkID::get_wc_permalink( 'shop' );
			} else {
				$page_id = $post->ID;
			}

			$this->render_login_block( get_permalink( $page_id ), false, true );
		}

		//Callback function
		/**
		 * Echos a linkID login block with options specified in parameter list.
		 *
		 * @param string $redirectUrl the url to redirect to after succesful login or merge
		 * @param $isWC bool whether or not the login is initiated on a woocommerce page
		 * @param bool $isMerge whether or not we're merging linkID accounts
		 */
		function render_login_block( $redirectUrl = null, $isMerge = false, $isWC = false ) {

			//
			// Don't render the login block if user is already logged in and there is no merge going on
			//
			if ( ! $isMerge && is_user_logged_in() ) {
				return;
			}

			wp_register_script( 'link_linkid_login_js', LINK_WP_LINKID_PLUGIN_URL . 'js/linkid-login.js', array( 'jquery' ) );
			wp_enqueue_script( 'link_linkid_login_js' );

			$js_var_substitution = array(
				'ajaxUrl'             => admin_url( 'admin-ajax.php' ),
				'loadingImgUrl'       => LINK_WP_LINKID_PLUGIN_URL . "img/loading.gif",
				'redirectUrl'         => $redirectUrl,
				'isMerge'             => $isMerge,
				'tryAgainButton'      => __( "Try again", 'link-linkid' ),
				'isWC'                => $isWC,
				'defaultErrorMessage' => __( "Something went wrong. Please try again later.", 'link-linkid' ),
				'expiredMessage'      => __( "QR code expired. Please try again.", 'link-linkid' ),
				'successMessage'      => $isMerge ? __( "Successfully merged account with linkID. Please wait for the page to reload", 'link-linkid' )
					: __( "Successfully logged in. Please wait while you are being redirected.", 'link-linkid' ),
				'startButtonText'     => $isMerge ? __( "Start Merge", 'link-linkid' ) : __( "Start Login", 'link-linkid' )
			);
			wp_localize_script( 'link_linkid_login_js', 'Link_LinkID_Login', $js_var_substitution );

			wp_register_style( 'link_linkid_login_css', LINK_WP_LINKID_PLUGIN_URL . 'css/linkid-block.css' );
			wp_enqueue_style( 'link_linkid_login_css' );

			$template_path = LINK_WP_LINKID_PLUGIN_DIR . "templates/login-block.php";

			include( $template_path );
		}


		// ==================================================
		// LOGIN BLOCK AJAX ENDPOINTS
		// ==================================================
		/**
		 * AJAX endpoint for initializing a linkID authentication session.
		 * Returns a json_encoded PollResponse object.
		 *
		 * @throws Exception something went wrong
		 */
		function login_init() {

			$current_user_id = get_current_user_id();

			if ( $current_user_id ) {

				$is_merge = false;
				if ( isset( $_REQUEST["isMerge"] ) && $current_user_id !== null ) {
					if ( $_REQUEST["isMerge"] === 1 || $_REQUEST["isMerge"] === '1' ) {
						$is_merge = true;
					}
				}

				$linkid_user_id = get_user_meta( $current_user_id, self::WP_USER_META_VALUE_LINKID_USER_ID, true );

				if ( $is_merge && trim( $linkid_user_id ) ) {

					// generate the response
					$response = json_encode( Link_WP_LinkID_PollResult::createFromError(
						__( "Unable to start merging. Logged in user already linked to linkID account", "link-linkid" ),
						false ) );

					// response output
					header( "Content-Type: application/json" );
					echo $response;

					die();

				} else if ( ! $is_merge && trim( $linkid_user_id ) ) {

					// generate the response
					$response = json_encode( Link_WP_LinkID_PollResult::createFromError(
						__( "Unable to start login. User already logged in. Please wait while you are being redirected.", "link-linkid" ),
						false, false, true, $this->get_redirect_url( wp_get_current_user() ) ) );

					// response output
					header( "Content-Type: application/json" );
					echo $response;

					die();

				}
			}

			$linkIDAuthnSession = Link_WP_LinkID::start_authn_request();

			// generate the response
			$response = json_encode( Link_WP_LinkID_PollResult::createFromAuthnSession( $linkIDAuthnSession ) );

			// response output
			header( "Content-Type: application/json" );
			echo $response;

			die();
		}

		/**
		 * AJAX endpoint for polling a linkID authentication session.
		 * Returns a json_encoded PollResponse object.
		 *
		 * @throws Exception something went wrong
		 */
		function login_poll() {

			$linkIDPollResponse = Link_WP_LinkID::poll_authn_session();

			if ( $linkIDPollResponse->authenticationState === LinkIDPollResponse::AUTH_STATE_AUTHENTICATED ) {

				//
				// Check whether the login session is a merge session.
				//
				$current_user_id = get_current_user_id();

				$is_merge = false;
				if ( isset( $_REQUEST["isMerge"] ) && $current_user_id !== null ) {
					if ( $_REQUEST["isMerge"] === 1 || $_REQUEST["isMerge"] === '1' ) {
						$is_merge = true;
					}
				}

				$is_woocommerce = false;
				if ( $is_merge && Link_WP_LinkID::is_woocommerce_active() && isset( $_REQUEST["isWC"] ) ) {
					if ( $_REQUEST["isWC"] === 1 || $_REQUEST["isWC"] === '1' ) {
						$is_woocommerce = true;
					}
				}


				//
				// Check if user exists for given linkID user ID
				//
				/* @var $authnContext LinkIDAuthnContext */
				$authnContext = $linkIDPollResponse->authenticationContext;

				$linkIDUserData = $this->get_user_data( $authnContext->attributes );


				$query        = array(
					'meta_key'    => self::WP_USER_META_VALUE_LINKID_USER_ID,
					'meta_value'  => $authnContext->userId,
					'number'      => 1,
					'count_total' => false
				);
				$linkid_users = get_users( $query );
				/** @var WP_User $linkid_user */
				$linkid_user = reset( $linkid_users );


				if ( $is_merge ) {
					//LOGIN IS MERGE

					//
					// Cannot merge if linkID user id has already been used
					//
					if ( $linkid_user ) {

						$response = json_encode( Link_WP_LinkID_PollResult::createFromError(
							__( "Cannot merge accounts. linkID account already linked to user", "link-linkid" ),
							false ) );

						header( "Content-Type: application/json" );
						echo $response;

						die();
					}

					$linkid_user_id = get_user_meta( $current_user_id, self::WP_USER_META_VALUE_LINKID_USER_ID, true );

					//
					// Cannot merge if linkID user already has a linkID user ID
					//
					if ( $linkid_user_id ) {

						// generate the response
						$response = json_encode( Link_WP_LinkID_PollResult::createFromError(
							__( "Unable to merge accounts. Logged in user already linked to another linkID account",
								"link-linkid" ),
							false ) );

						// response output
						header( "Content-Type: application/json" );
						echo $response;

						die();
					}


					//
					// Send merge email to user if user has email set.
					//
					/* @var $current_user WP_User */
					global $current_user;
					if ( $current_user->user_email ) {
						$blog_name       = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
						$my_account_link = $is_woocommerce ? Link_WP_LinkID::get_wc_permalink( "myaccount" ) : admin_url( "profile.php" );

						$subject = sprintf( '[%s] %s', $blog_name, __( 'Account merged with linkID', 'link-linkid' ) );
						$message = sprintf( __( 'Dear user,' .
						                        '<p>Your %s account has been merged with linkID. This means you can now ' .
						                        'log in on %s using linkID.</p>' .
						                        '<p>If you did not request an account merge, or ' .
						                        'if you wish to undo this action, you can go to the <a href="%s">My account</a> ' .
						                        'page on %s and click the "%s" button.</p>', 'link-linkid' ),
							$blog_name, $blog_name, $my_account_link,
							$blog_name, __( "Unlink linkID", "link-linkid" ) );

						add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );

						wp_mail( $current_user->user_email, $subject, $message );

						if ( has_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) ) ) {
							remove_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );
						}

					}

					$this->update_user_info( $linkIDUserData, $current_user_id );

					// linkID user ID has not been used yet and authenticated user has no linkID user ID yet -> execute merge
					update_user_meta( $current_user_id, self::WP_USER_META_VALUE_LINKID_USER_ID, $authnContext->userId );

					$response = json_encode( Link_WP_LinkID_PollResult::createResultFromSuccess(
						__( "Accounts have been merged successfully. Please wait for the page to reload",
							"link-linkid" ) ) );

					header( "Content-Type: application/json" );
					echo $response;

					die();

				} else {
					//LOGIN IS NO MERGE


					if ( ! $linkid_user ) {

						//
						// Check if users can actually register
						//
						if ( isset( $linkIDUserData["error"] ) && $linkIDUserData["error_message"] ) {
							$response = json_encode( Link_WP_LinkID_PollResult::createFromError(
								__( "Cannot create an account:", 'link-linkid' ) . $linkIDUserData["error"] ) );
							header( "Content-Type: application/json" );
							echo $response;
							die();
						}


						if ( username_exists( $linkIDUserData["email"] ) || email_exists( $linkIDUserData["email"] ) ) {


							$response = json_encode( Link_WP_LinkID_PollResult::createFromError(
								sprintf( __( "Cannot create an account on this website using linkID. The email address %s, associated with your linkID account, already exists for this website. Please log in to your account on this website using your username and password and merge your accounts manually afterwards.",
									'link-linkid' ), $linkIDUserData["email"] ), false ) );
							header( "Content-Type: application/json" );
							echo $response;
							die();
						}

						if ( ! Link_WP_LinkID_Login::can_register() ) {
							$response = json_encode( Link_WP_LinkID_PollResult::createFromError(
								__( "Cannot log in: new user registration not allowed.", "link-linkid" ) ) );
							header( "Content-Type: application/json" );
							echo $response;

							die();
						}

						// Generate the password and create the user
						$password = wp_generate_password( 12, false );
						$user_id  = wp_create_user(
							$linkIDUserData["email"] /* set email as username*/,
							$password,
							$linkIDUserData["email"] );

						if ( ! empty( $user_id->errors ) ) {
							$response = json_encode( Link_WP_LinkID_PollResult::createFromError(
								__( "Something went wrong trying to create account for this website. Please try again later",
									'link-linkid' ), false ) );
							header( "Content-Type: application/json" );
							echo $response;
							die();
						}

						// Set the nickname
						wp_update_user(
							array(
								'ID'         => $user_id,
								'nickname'   => $linkIDUserData["email"],
								'first_name' => isset( $linkIDUserData["first_name"] ) ? $linkIDUserData["first_name"] : '',
								'last_name'  => isset( $linkIDUserData["last_name"] ) ? $linkIDUserData["last_name"] : ''
							)
						);

						// Set the role -- depends whether woocommerce is installed or not

						$linkid_user = new WP_User( $user_id );

						$linkid_user->set_role( Link_WP_LinkID::is_woocommerce_active() ? 'customer' : get_option( 'default_role' ) );

						update_user_meta( $user_id, self::WP_USER_META_VALUE_LINKID_USER_ID, $authnContext->userId );

						// Email the user
						// wp_mail( $email_address, 'Welcome! ', 'Your Password: ' . $password );
					}

					if ( ! is_wp_error( $linkid_user ) ) {

						$this->update_user_info( $linkIDUserData, $linkid_user->ID );

						wp_clear_auth_cookie();
						wp_set_current_user( $linkid_user->ID );
						wp_set_auth_cookie( $linkid_user->ID );

						$response = json_encode( Link_WP_LinkID_PollResult::createFromSuccessWithRedirect( $this->get_redirect_url( $linkid_user ) ) );

						header( "Content-Type: application/json" );
						echo $response;

						die();
					}
				}
			}

			$response = json_encode( Link_WP_LinkID_PollResult::createFromPollResponse( $linkIDPollResponse ) );

			header( "Content-Type: application/json" );
			echo $response;

			die();
		}


		function update_user_info( $linkIDUserData, $user_id ) {

			if ( Link_WP_LinkID::is_woocommerce_active() ) {
				foreach ( $linkIDUserData as $key => $value ) {
					update_user_meta( $user_id, 'billing_' . $key, $value );
					update_user_meta( $user_id, 'shipping_' . $key, $value );
				}
			}

			$userData = array(
				'ID' => $user_id
			);
			if ( isset( $linkIDUserData['email'] ) && ! email_exists( $linkIDUserData["email"] ) ) {
				$userData['user_email'] = $linkIDUserData['email'];
			}
			if ( isset( $linkIDUserData["first_name"] ) ) {
				$userData['first_name'] = $linkIDUserData['first_name'];
			}
			if ( isset( $linkIDUserData["last_name"] ) ) {
				$userData['last_name'] = $linkIDUserData['last_name'];
			}

			wp_update_user( $userData );

		}

		function set_html_content_type() {
			return "text/html; charset=UTF-8";
		}

		/**
		 * AJAX endpoint for unlinking a linkID account from the currently logged in user.
		 * Returns a json_encoded object.
		 *
		 * @throws Exception something went wrong
		 */
		function login_unlink() {

			$current_user_id = get_current_user_id();

			if ( $current_user_id ) {

				$linkid_user_id = get_user_meta( $current_user_id, self::WP_USER_META_VALUE_LINKID_USER_ID, true );

				if ( ! trim( $linkid_user_id ) ) {

					// generate the response
					$response = json_encode(
						array(
							'status'  => "ERROR",
							'message' => __( "Cannot unlink accounts. Logged in user not linked to any linkID account", "link-linkid" )
						)
					);;

					// response output
					header( "Content-Type: application/json" );
					echo $response;

					die();
				}

				//User is logged in. and has an associated linkID user ID.
				delete_user_meta( $current_user_id, self::WP_USER_META_VALUE_LINKID_USER_ID );

				// generate the response
				$response = json_encode(
					array(
						'status'  => "SUCCESS",
						'message' => __( "Accounts successfully unlinked. Please wait while page reloads.", "link-linkid" )
					)
				);;

				// response output
				header( "Content-Type: application/json" );
				echo $response;

				die();

			} else {

				// generate the response
				$response = json_encode(
					array(
						'status'  => "ERROR",
						'message' => __( "Cannot unlink accounts. Currently no logged in user.", "link-linkid" )
					)
				);;

				// response output
				header( "Content-Type: application/json" );
				echo $response;

				die();

			}

		}

		// ==================================================
		// HELPER METHODS
		// ==================================================

		//
		// LinkID user profile attribute list
		//
		const ATTRIBUTE_GIVEN_NAME = "profile.givenName";
		const ATTRIBUTE_FAMILY_NAME = "profile.familyName";
		const ATTRIBUTE_EMAIL = "profile.email";
		const ATTRIBUTE_EMAIL_ADDRESS = "profile.email.address";
		const ATTRIBUTE_EMAIL_CONFIRMED = "profile.email.confirmed";
		const ATTRIBUTE_DOB = "profile.dob";
		const ATTRIBUTE_MOBILE = "profile.mobile";
		const ATTRIBUTE_ADDRESS = "profile.address";
		const ATTRIBUTE_ADDRESS_STREET = "profile.address.street";
		const ATTRIBUTE_ADDRESS_STREETNUMBER = "profile.address.streetNumber";
		const ATTRIBUTE_ADDRESS_STREETBUS = "profile.address.streetBus";
		const ATTRIBUTE_ADDRESS_CITY = "profile.address.city";
		const ATTRIBUTE_ADDRESS_POSTAL_CODE = "profile.address.postalCode";
		const ATTRIBUTE_ADDRESS_COUNTRY = "profile.address.country";

		/**
		 *
		 * Fetches and sanitizes the user data from linkID attribute map.
		 *
		 * @param $attributes array() the linkID attributes
		 *
		 * @return array containing the user data if everything is correct or array containing error and error_message
		 *          if incorrect data is supplied.
		 */
		private function get_user_data( $attributes ) {

			$linkIDUserData = array();

			$linkIDUserData["first_name"] = sanitize_text_field( $attributes[ self::ATTRIBUTE_GIVEN_NAME ][0]->value );
			$linkIDUserData["last_name"]  = sanitize_text_field( $attributes[ self::ATTRIBUTE_FAMILY_NAME ][0]->value );
			$linkIDUserData["phone"]      = sanitize_text_field( $attributes[ self::ATTRIBUTE_MOBILE ][0]->value );

			$linkIDEmailAttributes   = $attributes[ self::ATTRIBUTE_EMAIL ][0]->value;
			$linkIDUserData["email"] = is_email( $this->find_by_attribute_name( $linkIDEmailAttributes, self::ATTRIBUTE_EMAIL_ADDRESS ) );

			if ( ! $linkIDUserData["email"] ) {
				return array(
					"error"         => true,
					"error_message" => __( "Invalid email address in linkID account." )
				);
			}

			$addressAttributes           = $attributes[ self::ATTRIBUTE_ADDRESS ][0]->value;
			$linkIDUserData["address_1"] = sanitize_text_field( $this->find_by_attribute_name( $addressAttributes, self::ATTRIBUTE_ADDRESS_STREET ) . " " . $this->find_by_attribute_name( $addressAttributes, self::ATTRIBUTE_ADDRESS_STREETNUMBER ) );
			$linkIDUserData["address_2"] = sanitize_text_field( $this->find_by_attribute_name( $addressAttributes, self::ATTRIBUTE_ADDRESS_STREETBUS ) );
			$linkIDUserData["city"]      = sanitize_text_field( $this->find_by_attribute_name( $addressAttributes, self::ATTRIBUTE_ADDRESS_CITY ) );
			$linkIDUserData["postcode"]  = sanitize_text_field( $this->find_by_attribute_name( $addressAttributes, self::ATTRIBUTE_ADDRESS_POSTAL_CODE ) );
			$linkIDUserData["country"]   = sanitize_text_field( $this->find_by_attribute_name( $addressAttributes, self::ATTRIBUTE_ADDRESS_COUNTRY ) );

			return $linkIDUserData;
		}

		private function find_by_attribute_name( $attributes, $attributeName ) {
			for ( $i = 0; $i < count( $attributes ); ++ $i ) {
				if ( strcmp( $attributes[ $i ]->name, $attributeName ) === 0 ) {
					return $attributes[ $i ]->value;
				}
			}
		}

		private function get_redirect_url( $user ) {

			$redirect = '';

			if ( isset( $user->roles ) && in_array( 'administrator', $user->roles ) ) {
				$redirect = admin_url();
			} else if ( Link_WP_LinkID::is_woocommerce_active() ) {
				$redirect = Link_WP_LinkID::get_wc_permalink( 'myaccount' );
				if ( ! $redirect ) {
					return home_url();
				}
			} else {
				$redirect = home_url();
			}

			return $redirect;
		}
	}
}