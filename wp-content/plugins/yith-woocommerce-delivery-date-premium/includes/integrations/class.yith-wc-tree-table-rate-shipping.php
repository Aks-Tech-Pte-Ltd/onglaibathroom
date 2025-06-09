<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Manage class that add integration with Tree Table Rate
 *
 * @package YITH WooCommerce Delivery Date\Classes\Integrations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'YITH_Delivery_Date_Tree_Table_Rate_Shipping' ) ) {
	/**
	 * Class YITH_Delivery_Date_Tree_Table_Rate_Shipping
	 */
	class YITH_Delivery_Date_Tree_Table_Rate_Shipping {
		/**
		 * YITH_Delivery_Date_Tree_Table_Rate_Shipping constructor.
		 */
		public function __construct() {

			add_filter( 'ywcdd_get_shipping_method_option', array( $this, 'get_table_rate_option_name' ), 10, 2 );
		}


		/**
		 * Return the tree table rate option name
		 *
		 * @param array  $shipping_option The shipping options.
		 * @param string $option_name The option name.
		 *
		 * @return array
		 * @since 1.0.0
		 * @author YITH <plugins@yithemes.com>
		 */
		public function get_table_rate_option_name( $shipping_option, $option_name ) {

			if ( empty( $shipping_option ) ) {

				if ( version_compare( WC()->version, '2.6.0', '>=' ) ) {
					$delimiter = '_';
				} else {
					$delimiter = ':';
				}

				$option = explode( $delimiter, $option_name );

				$option_name = isset( $option[3] ) ? 'tree_table_rate' . $delimiter . $option[3] : $option_name;

				$shipping_option = get_option( 'woocommerce_' . $option_name . '_settings' );
			}

			return $shipping_option;
		}
	}
}

new YITH_Delivery_Date_Tree_Table_Rate_Shipping();
