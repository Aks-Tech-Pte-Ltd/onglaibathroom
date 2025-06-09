<?php // phpcs:ignore WordPress.Files.FileName
/**
 * This class manage the GDPR
 *
 * @package YITH WooCommerce Delivery Date\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Delivery_Date_Privacy' ) && class_exists( 'YITH_Privacy_Plugin_Abstract' ) ) {
	/**
	 * Class YITH_Delivery_Date_Privacy
	 */
	class YITH_Delivery_Date_Privacy extends YITH_Privacy_Plugin_Abstract {
		/**
		 * YITH_Delivery_Date_Privacy constructor.
		 */
		public function __construct() {

			$plugin_info = get_plugin_data( YITH_DELIVERY_DATE_FILE );

			$name = $plugin_info['Name'];

			parent::__construct( $name );

		}

		/**
		 * Gets the message of the privacy to display.
		 * To be overloaded by the implementor.
		 *
		 * @param string $section The section.
		 *
		 * @return string
		 */
		public function get_privacy_message( $section ) {

			$message = '';
			switch ( $section ) {
				case 'collect_and_store':
					$message  = '<p>' . __( 'We collect information about you during the registration and checkout process on our store.', 'yith-woocommerce-delivery-date' ) . '</p>';
					$message .= '<p>' . __( 'While you visit our site, we’ll track:', 'yith-woocommerce-delivery-date' ) . '</p>';
					$message .= '<ul>';
					$message .= '<li>' . __( 'Delivery information: we will use these data to save delivery information that allows customers to select a delivery date during the checkout process.', 'yith-woocommerce-delivery-date' ) . '</li>';
					$message .= '<li>' . __( 'The information required, carrier identifier, date and time slot are stored in the Order, so this field is subject to WooCommerce policy.', 'yith-woocommerce-delivery-date' ) . '</li>';
					$message .= '<li>' . __( 'The plugin can also send an email when the order has been shipped by the admin.', 'yith-woocmmerce-delivery-date' ) . '</li>';
					$message .= '</ul>';
					break;

				case 'has_access':
					$message  = '<p>' . __( 'Members of our team have access to the information you provide us. For example, both Administrators and Shop Managers can access:', 'yith-woocommerce-delivery-date' ) . '</p>';
					$message .= '<ul>';
					$message .= '<li>' . __( 'All delivery information', 'yith-woocommerce-delivery-date' ) . '</li>';
					$message .= '</ul>';
					$message .= '<p>' . __( 'Our team members have access to this information to help fulfill orders, process orders and support you.', 'yith-woocommerce-delivery-date' ) . '</p>';
					break;

			}

			return $message;
		}
	}
}
