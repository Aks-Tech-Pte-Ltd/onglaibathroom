<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Manage class that add integration with YITH Product Shipping
 *
 * @package YITH WooCommerce Delivery Date\Classes\Integrations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Delivery_Date_YITH_Product_Shipping' ) ) {
	/**
	 * Class YITH_Delivery_Date_YITH_Product_Shipping
	 */
	class YITH_Delivery_Date_YITH_Product_Shipping {
		/**
		 * YITH_Delivery_Date_YITH_Product_Shipping constructor.
		 */
		public function __construct() {

			add_filter( 'woocommerce_settings_api_form_fields_yith_wc_product_shipping_method', array( $this, 'add_custom_fields' ), 5, 1 );
			add_filter( 'ywcdd_disable_delivery_date_for_shipping_method', array( $this, 'hide_duplicate_rows' ), 10, 3 );
		}


		/**
		 * Hide the unnecessary options
		 *
		 * @param bool   $hide The old value.
		 * @param string $key The key.
		 * @param string $shipping_id The shipping id.
		 *
		 * @return bool
		 * @author YITH <plugins@yithemes.com>
		 * @since 1.0.0
		 */
		public function hide_duplicate_rows( $hide, $key, $shipping_id ) {

			if ( 'yith_wc_product_shipping_method' === $shipping_id ) {
				$hide = true;
			}

			return $hide;
		}

		/**
		 * Add the custom fields
		 *
		 * @param array $form_fields The default form fields.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		public function add_custom_fields( $form_fields ) {

			$options = YITH_Delivery_Date_Processing_Method()->get_formatted_processing_method();

			$form_fields['select_process_method'] = array(

				'title'   => __( 'Processing Method', 'yith-woocommerce-delivery-date' ),
				'type'    => 'select',
				'default' => '',
				'class'   => 'ywcdd_processing_method wc-enhanced-select ',
				'options' => $options,

			);

			$form_fields['set_method_as_mandatory'] = array(

				'title'       => __( 'Set as required', 'yith-woocommerce-delivery-date' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'class'       => 'ywcdd_set_mandatory ',
				'description' => __( 'If enabled, customers must select a date for the delivery', 'yith-woocommerce-delivery-date' ),

			);

			return $form_fields;
		}


	}
}

new YITH_Delivery_Date_YITH_Product_Shipping();
