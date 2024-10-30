<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Link_WP_LinkID_Payments' ) ) {

	class Link_WP_LinkID_Payments {

		/** @var string $table_name the linkID payments table name */
		private $payments_table_name;

		/**
		 * Current version of the plugin.
		 * @var string
		 */
		const PAYMENTS_TABLE_VERSION = '0.1';
		const PAYMENTS_TABLE_OPTION = "link_linkid_payments_table_version";
		const PAYMENTS_TABLE_NAME = "linkid_payment";

		//
		// Columns
		//
		const PAYMENT_ORDER_REFERENCE = "payment_order_reference";
		const ORDER_ID = "order_id";
		const STATE = "state";
		const CREATION_DATE = "creation_date";
		const LAST_UPDATE_DATE = "last_update_date";
		const PAYMENT_SESSION_CANCELED = "payment_session_canceled";
		const AUTHN_SESSION_ID = "authn_session_id";


		/**
		 * Link_WC_Payments constructor.
		 *
		 */
		public function __construct() {

			global $wpdb;
			$this->payments_table_name = $wpdb->prefix . 'linkid_payment';
		}

		public static function activate() {

			global $wpdb;

			$current_linkid_db_version = get_option( self::PAYMENTS_TABLE_OPTION );

			if ( $current_linkid_db_version !== self::PAYMENTS_TABLE_VERSION ) {

				$table_name = $wpdb->prefix . self::PAYMENTS_TABLE_NAME;

				if ( method_exists( $wpdb, 'get_charset_collate' ) ) {
					$charset_collate = $wpdb->get_charset_collate();
				} else {//Older wordpress versions dont have this method.
					$charset_collate = '';

					if ( ! empty( $wpdb->charset ) ) {
						$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
					}
					if ( ! empty( $wpdb->collate ) ) {
						$charset_collate .= " COLLATE $wpdb->collate";
					}
				}


				$statement = "CREATE TABLE " . $table_name . "( "
				             . self::PAYMENT_ORDER_REFERENCE . " VARCHAR(38) NOT NULL, "
				             . self::ORDER_ID . " INT(9) NOT NULL, "
				             . self::STATE . " INT(11) NOT NULL, "
				             . self::CREATION_DATE . " TIMESTAMP DEFAULT '0000-00-00 00:00:00' NOT NULL, "
				             . self::LAST_UPDATE_DATE . " TIMESTAMP DEFAULT '0000-00-00 00:00:00' NOT NULL, "
				             . self::PAYMENT_SESSION_CANCELED . " BIT NOT NULL, "
				             . self::AUTHN_SESSION_ID . " VARCHAR(38) NOT NULL, "
				             . "PRIMARY KEY  ( " . self::PAYMENT_ORDER_REFERENCE . ")"
				             . " ) " . $charset_collate . ";";

				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				$result = dbDelta( $statement );


				add_option( self::PAYMENTS_TABLE_OPTION, self::PAYMENTS_TABLE_VERSION );
			}

		}

		public static function deactivate() {
			delete_option( self::PAYMENTS_TABLE_OPTION );
		}


		public function getPayments( $order_id ) {
			global $wpdb;

			return $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM " . $this->payments_table_name
				                . " WHERE " . self::ORDER_ID . " = %s"
				                . " ORDER BY " . self::CREATION_DATE,
					$order_id ) );
		}

		public function getPaymentForOrderReference( $payment_order_reference ) {
			global $wpdb;
			$_linkid_payment = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM " . $this->payments_table_name
				                . " WHERE " . self::PAYMENT_ORDER_REFERENCE . " = %s"
				                . " LIMIT 1",
					$payment_order_reference ) );

			return $_linkid_payment;
		}

		public function setPaymentCanceled( $payment_order_reference ) {
			global $wpdb;
			$wpdb->update(
				$this->payments_table_name,
				array(
					self::LAST_UPDATE_DATE         => current_time( 'mysql', true ),
					self::PAYMENT_SESSION_CANCELED => 1
				),
				array(
					self::PAYMENT_ORDER_REFERENCE => $payment_order_reference
				) );
		}

		/**
		 *
		 * Create a linkID payment entry for a given order and order reference
		 *
		 * @param $order_id int the id of the order
		 * @param $order_reference string the order reference
		 * @param $authn_session_id string the id of the linkID authentication correspond to the order reference
		 *
		 * @return int the linkID payment id
		 */
		public function createPayment( $order_id, $order_reference, $authn_session_id ) {
			global $wpdb;
			$result = $wpdb->insert(
				$this->payments_table_name,
				array(
					self::PAYMENT_ORDER_REFERENCE  => $order_reference,
					self::ORDER_ID                 => $order_id,
					self::STATE                    => - 1,
					self::CREATION_DATE            => current_time( 'mysql', true ),
					self::LAST_UPDATE_DATE         => current_time( 'mysql', true ),
					self::PAYMENT_SESSION_CANCELED => 0,
					self::AUTHN_SESSION_ID         => $authn_session_id
				),
				array(
					'%s',
					'%d',
					'%d',
					'%s',
					'%s',
					'%d',
					'%s',
				)
			);

			if ( false === $result ) {
				return new WP_Error( 'db_insert_error', 'Could not insert payment into the database', $wpdb->last_error );
			}

			return (int) $wpdb->insert_id;
		}

		/**
		 * Updates a payment object with given data
		 *
		 * @param $payment object the original payment object
		 * @param $providedData array containing the data to update the payment object with
		 */
		public function updatePayment( $payment, $providedData ) {

			$providedData[ self::LAST_UPDATE_DATE ] = current_time( 'mysql', true );

			global $wpdb;
			$wpdb->update(
				$this->payments_table_name,
				$providedData,
				array(
					self::PAYMENT_ORDER_REFERENCE => $payment->payment_order_reference
				) );

		}

	}
}