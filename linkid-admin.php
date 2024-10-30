<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Link_WP_LinkID_Admin' ) ) {

	class Link_WP_LinkID_Admin {

		//
		// Page constant
		//
		const LINKID_SETTINGS_PAGE_ID = 'link_linkid_settings_page';

		//
		// Section constants
		//
		const GENERAL_SETTINGS_SECTION_ID = 'link_linkid_general_settings_section';
		const LOGIN_SECTION_ID = 'link_linkid_login_section';
		const PAYMENT_SECTION_ID = 'link_linkid_payment_section';

		//
		// Input types
		//
		const INPUT_TYPE_TEXT_INPUT = "text_input";
		const INPUT_TYPE_PASSWORD = "password";
		const INPUT_TYPE_CHECKBOX = "checkbox";

		/**
		 * LinkID_Admin constructor.
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'linkid_menu' ) );
			add_action( 'admin_init', array( $this, 'options_init' ) );
			add_action( 'admin_notices', array( $this, 'linkid_admin_notices' ) );
			add_filter( "plugin_action_links_" . LINK_WP_LINKID_NAME, array( $this, 'plugin_add_settings_link' ) );
		}

		function linkid_menu() {
			add_options_page(
				'linkID Options',
				'linkID',
				'manage_options',
				self::LINKID_SETTINGS_PAGE_ID,
				array( $this, 'render_options_page' )
			);
		}

		function render_options_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You don\'t have sufficient permissions to access this page.' ) );
			}

			wp_register_script( 'link_linkid_admin_settings_js', LINK_WP_LINKID_PLUGIN_URL . 'js/admin-settings.js', array( 'jquery' ) );
			$js_var_substitution = array(
				'requiredText' => __( 'Required', 'link-linkid' )
			);
			wp_localize_script( 'link_linkid_admin_settings_js', 'Link_LinkID_Settings', $js_var_substitution );
			wp_enqueue_script( 'link_linkid_admin_settings_js' );

			wp_register_style( 'link_linkid_admin_settings_css', LINK_WP_LINKID_PLUGIN_URL . 'css/admin-settings.css' );


			wp_enqueue_style( 'link_linkid_admin_settings_css' );


			?>
			<div class="wrap">
				<h2>
					<?php printf( __( "linkID Settings", "link-linkid" ) ) ?>
				</h2>

				<form id="linkid-settings-form" method="post" action="options.php">
					<?php settings_fields( 'link_linkid_settings_page' ); ?>
					<?php do_settings_sections( 'link_linkid_settings_page' ); ?>
					<?php submit_button(); ?>
				</form>
			</div>
			<?php
		}

		function options_init() {
			$this->init_general_settings();
			$this->init_login_settings();
			if ( Link_WP_LinkID::is_woocommerce_active() ) {
				$this->init_payment_settings();
			}
		}

		// ============================================
		// GENERAL INFO SETTINGS
		// ============================================

		private function init_general_settings() {
			register_setting(
				self::LINKID_SETTINGS_PAGE_ID, //Needs to be the page itself, otherwise the variables do not get white-listed on post...
				Link_WP_LinkID::LINKID_SETTINGS_VARIABLE,
				array( $this, 'validate_settings' )
			);

			//
			// Initialize the section
			//
			add_settings_section(
				self::GENERAL_SETTINGS_SECTION_ID,
				__( "General Settings", "link-linkid" ),
				array( $this, 'render_general_settings_info' ),
				self::LINKID_SETTINGS_PAGE_ID );

			//
			// Add fields
			//
			$this->add_field(
				Link_WP_LinkID::SETTINGS_APPLICATION_NAME,
				__( "linkID Application Name", "link-linkid" ),
				__( "The name of your linkID application.", "link-linkid" ),
				self::INPUT_TYPE_TEXT_INPUT,
				self::GENERAL_SETTINGS_SECTION_ID
			);
			$this->add_field(
				Link_WP_LinkID::SETTINGS_LINKID_HOSTNAME,
				__( "linkID Hostname", "link-linkid" ),
				__( "The url of the linkID server with which the store will communicate. Use service.linkid.be "
				    . "for production and demo.linkid.be for testing.", "link-linkid" ),
				self::INPUT_TYPE_TEXT_INPUT,
				self::GENERAL_SETTINGS_SECTION_ID
			);
			$this->add_field(
				Link_WP_LinkID::SETTINGS_APPLICATION_USERNAME,
				__( "linkID Application Username", "link-linkid" ),
				__( "The username of your linkID application.", "link-linkid" ),
				self::INPUT_TYPE_TEXT_INPUT,
				self::GENERAL_SETTINGS_SECTION_ID
			);
			$this->add_field(
				Link_WP_LinkID::SETTINGS_APPLICATION_PASSWORD,
				__( "linkID Application Password", "link-linkid" ),
				__( "The password of your linkID application.", "link-linkid" ),
				self::INPUT_TYPE_TEXT_INPUT,
				self::GENERAL_SETTINGS_SECTION_ID
			);
			$this->add_field(
				Link_WP_LinkID::SETTINGS_LINKID_PROFILE,
				__( "linkID Profile", "link-linkid" ),
				__( "The linkID profile used during login and/or payment. The profile may ask for e-mail, First Name, "
				    . "Family Name, Street Name, Street Number, Street Bus, City, Postal/Zip Code and Country from the user.",
					"link-linkid" ),
				self::INPUT_TYPE_TEXT_INPUT,
				self::GENERAL_SETTINGS_SECTION_ID
			);


		}

		function render_general_settings_info() {
			?>
			<p>
				<?php _e( "The general linkID settings used for both linkID login and payment.", "link-linkid" ) ?>
			</p>
			<?php
		}

		// ============================================
		// LOGIN SETTINGS
		// ============================================

		private function init_login_settings() {
			//
			// Initialize the section
			//
			add_settings_section(
				self::LOGIN_SECTION_ID,
				__( 'Login Settings', 'link-linkid' ),
				array( $this, 'render_login_settings_info' ),
				self::LINKID_SETTINGS_PAGE_ID );

			//
			// Add fields
			//
			$this->add_field(
				Link_WP_LinkID::SETTINGS_LOGIN_ACTIVE,
				__( 'Login Active', 'link-linkid' ),
				sprintf( __( 'Allow the linkID plugin to add an extra button to the login forms on this website. ' .
				             'This button allows users to log in and register (if registrations are' .
				             'enabled in the <a href="%s"> general settings</a>) with linkID.', 'link-linkid' ),
					admin_url( "options-general.php" ) ),
				self::INPUT_TYPE_CHECKBOX,
				self::LOGIN_SECTION_ID
			);
		}

		function render_login_settings_info() {
			?>
			<p>
				<?php _e( "The linkID settings which are used during login.", "link-linkid" ) ?>
			</p>
			<?php
		}

		// ============================================
		// PAYMENT SETTINGS -- EMPTY, SEE PAYMENT GATEWAY FOR SETTINGS
		// ============================================

		function init_payment_settings() {
			//
			// Initialize the section
			//
			add_settings_section(
				self::PAYMENT_SECTION_ID,
				__( 'Payment Settings', 'link-linkid' ),
				array( $this, 'render_payment_settings_info' ),
				self::LINKID_SETTINGS_PAGE_ID );

		}

		function render_payment_settings_info() {
			?>
			<p>
				<?php
				if ( has_filter( 'woocommerce_get_sections_checkout' ) ) {
					$linkid_settings_section_link = esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=link_wp_linkid_payment_gateway' ) );
				} else {
					$linkid_settings_section_link = esc_url( admin_url( 'admin.php?page=woocommerce_settings&tab=payment_gateways#gateway-linkid' ) );
				}
				printf( __( "Please edit the settings for linkID payments inside the <a href=\"%s\">woocommerce settings</a>", "link-linkid" ),
					$linkid_settings_section_link );
				?>
			</p>
			<?php
		}

		// ============================================
		// Validation
		// ============================================
		function validate_settings( $input ) {
			return $input; //Do nothing for now
		}

		// ============================================
		// INPUT FIELD TYPE RENDERING
		// ============================================

		function render_checkbox( $args ) {
			$optionValue = Link_WP_LinkID::get_option_value( $args['key'] );
			$checked     = $optionValue === null ? '' : checked( 1, $optionValue, false );

			?>
			<input type="checkbox" value="1"
			       name="<?php echo "link_linkid_settings[" . $args['key'] . "]" ?>" <?php echo $checked ?>>
			<p class="description"><?php echo $args["description"] ?></p>
			<?php
		}

		function render_text_input( $args ) {
			$optionValue = Link_WP_LinkID::get_option_value( $args['key'] );
			$required    = $args["required"] ? "required" : "";
			?>
			<input type="text" <?php echo $required ?> class="regular-text"
			       name="<?php echo "link_linkid_settings[" . $args['key'] . "]" ?>"
			       value="<?php echo esc_attr( $optionValue ); ?>">
			<p class="description"><?php echo $args["description"] ?></p>
			<?php
		}

		function render_password( $args ) {
			$required    = $args["required"] ? "required" : "";
			$optionValue = Link_WP_LinkID::get_option_value( $args['key'] );
			$inputValue  = $optionValue === null ? '' : 'topsecret';

			?>
			<input type="password" <?php echo $required ?> class="regular-text"
			       name="<?php echo "link_linkid_settings[" . $args['key'] . "]" ?>"
			       value="<?php echo $inputValue ?>">
			<p class="description"><?php echo $args["description"] ?></p>
			<?php
		}


		// ============================================
		// ADMIN NOTICES
		// ============================================

		/**
		 *
		 * Check whether plugin settings are correct. Show notices on admin pages if not.
		 */
		function linkid_admin_notices() {

			if ( current_user_can( 'manage_options' ) ) {

				$generalSettingsIncomplete = (
					! Link_WP_LinkID::get_option_value( Link_WP_LinkID::SETTINGS_LINKID_HOSTNAME ) ||
					! Link_WP_LinkID::get_option_value( Link_WP_LinkID::SETTINGS_APPLICATION_NAME ) ||
					! Link_WP_LinkID::get_option_value( Link_WP_LinkID::SETTINGS_APPLICATION_USERNAME ) ||
					! Link_WP_LinkID::get_option_value( Link_WP_LinkID::SETTINGS_APPLICATION_PASSWORD ) ||
					! Link_WP_LinkID::get_option_value( Link_WP_LinkID::SETTINGS_LINKID_PROFILE ) ||
					Link_WP_LinkID::get_option_value( Link_WP_LinkID::SETTINGS_LOGIN_ACTIVE ) && (
						Link_WP_LinkID::get_option_value( Link_WP_LinkID::SETTINGS_LOGIN_ACTIVE ) == null ) );

				if ( $generalSettingsIncomplete ) {

					?>
					<div class="update-nag">
						<p>
							<?php
							printf( __( 'LinkID Settings incomplete. Please update settings <a href="%s">here</a>', 'link-linkid' ), esc_url( self::get_settings_url() ) );
							?>
						</p>
					</div>
					<?php

				} else {

					$invalidSettings = false;

					try {
						Link_WP_LinkID::start_authn_request();
					} catch ( Exception $e ) {
						$invalidSettings = true;
					}

					if ( $invalidSettings ) {

						?>
						<div class="update-nag">
							<p>
								<?php
								printf( __( 'LinkID Settings invalid. Cannot start login session using the provided settings. Please change the settings <a href="%s">here</a>', 'link-linkid' ),
									esc_url( self::get_settings_url() ) );
								?>
							</p>
						</div>
						<?php

					}
				}

				$linkid_wc_options       = get_option( "woocommerce_linkid_settings" );
				$linkid_payments_enabled = $linkid_wc_options && $linkid_wc_options != null && isset( $linkid_wc_options["enabled"] ) && $linkid_wc_options["enabled"] !== 'no';

				if ( $linkid_payments_enabled && ! Link_WP_LinkID::is_permalinks_enabled() ) {
					?>
					<div class="update-nag">
						<p>
							<b>
								<?php
								printf( __( 'Permalinks should be enabled in order for the linkID payment provider to work. Please enable permalinks <a href="%s">here</a>', 'link-linkid' ),
									esc_url( admin_url( "options-permalink.php" ) ) );
								?>
							</b>
						</p>
					</div>
					<?php
				}

				$demo_settings =
					Link_WP_LinkID::get_option_value( Link_WP_LinkID::SETTINGS_LINKID_HOSTNAME ) !== "service.linkid.be";

				if ( $linkid_payments_enabled && $demo_settings ) {
					?>
					<div class="update-nag">
						<?php printf( __( "You are currently using linkID demo settings. To change to production settings, click <a href=\"%s\">here</a>.", "link-linkid" ), esc_url( self::get_settings_url() ) );
						?>
					</div>
					<?php
				}

			}
		}

		// ============================================
		// HELPER METHODS
		// ============================================

		/**
		 *
		 * Adds a settings link on the plugin page next to activate and deactivate buttons
		 * to make it easier for user to find and edit linkID settings
		 *
		 * @param $links array the original array of links for
		 *
		 * @return mixed
		 */
		public
		function plugin_add_settings_link(
			$links
		) {
			$settings_link_html = '<a href="' . esc_url( self::get_settings_url() ) . '">' . __( 'Settings', 'link-linkid' ) . '</a>';
			array_unshift( $links, $settings_link_html );

			return $links;
		}

		/**
		 *
		 * Adds a field to a given section
		 *
		 * @param $key string the key by which the field can be recognized
		 * @param $title string the title/label of the field (will be shown to user)
		 * @param $description string the description of the field (shown to user)
		 * @param $type string the type of html input you wish to show to user (see constants at top for possibilities)
		 * @param $section string to which section the field should be added
		 * @param $required bool whether or not the field should be marked as required
		 */
		private
		function add_field(
			$key, $title, $description, $type, $section, $required = true
		) {
			add_settings_field(
				$key,
				$title,
				array( $this, 'render_' . $type ),
				self::LINKID_SETTINGS_PAGE_ID,
				$section,
				array(
					'key'         => $key,
					'description' => $description,
					'required'    => $required
				)
			);

		}

		/**
		 * Returns the fully qualified linkID settings page URL.
		 *
		 * @return string the fully qualified linkID settings page URL
		 */
		private
		static function get_settings_url() {
			return admin_url( 'options-general.php?page=' . self::LINKID_SETTINGS_PAGE_ID );
		}


	}
}