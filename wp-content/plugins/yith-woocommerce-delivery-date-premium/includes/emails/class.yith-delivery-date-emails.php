<?php // phpcs:ignore WordPress.Files.FileName

/**
 * Manage class that add custom email in WooCommerce Emails
 *
 * @package YITH WooCommerce Delivery Date\Classes\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'YITH_Delivery_Date_Emails' ) ) {
	/**
	 * Class YITH_Delivery_Date_Emails
	 */
	class YITH_Delivery_Date_Emails {
		/**
		 * The unique instance of the class
		 *
		 * @var YITH_Delivery_Date_Emails
		 */
		protected static $_instance; // phpcs:ignore

		/**
		 * YITH_Delivery_Date_Emails constructor.
		 */
		public function __construct() {

			// customer email.
			add_filter( 'woocommerce_email_classes', array( $this, 'add_woocommerce_emails' ) );
			add_filter( 'woocommerce_resend_order_emails_available', array( $this, 'add_resend_order_emails' ) );
		}
		/**
		 * Create or return the instance of the class
		 *
		 * @author YITH <plugins@yithemes.com>
		 * @since 1.0.0
		 * @return YITH_Delivery_Date_Emails
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {

				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Add custom email in WC_Emails
		 *
		 * @since 1.0.0
		 * @param array $emails The WooCommerce emails.
		 * @return array
		 */
		public function add_woocommerce_emails( $emails ) {

			$emails['YITH_Delivery_Date_Advise_Customer_Email'] = include YITH_DELIVERY_DATE_INC . 'emails/class.yith-delivery-date-advise-customer-email.php';
			return $emails;
		}

		/**
		 * Add custom email in WooCommerce emails
		 *
		 * @since 1.0.0
		 * @param array $emails The WooCommerce emails.
		 * @return array
		 */
		public function add_resend_order_emails( $emails ) {

			$emails[] = 'yith_advise_user_delivery_email';
			return $emails;
		}
	}
}

if ( ! function_exists( 'YITH_Delivery_Date_Emails' ) ) {
	/**
	 * Return the instance of the class
	 *
	 * @since 1.0.0
	 * @return YITH_Delivery_Date_Emails
	 */
	function YITH_Delivery_Date_Emails() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
		return YITH_Delivery_Date_Emails::get_instance();
	}
}

YITH_Delivery_Date_Emails();
