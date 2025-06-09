<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Manage class that add integration with WC Advanced Shipping
 *
 * @package YITH WooCommerce Delivery Date\Classes\Integrations
 */

if ( ! class_exists( 'YITH_Delivery_Date_WC_Advanced_Shipping' ) ) {
	/**
	 * Class YITH_Delivery_Date_WC_Advanced_Shipping
	 */
	class YITH_Delivery_Date_WC_Advanced_Shipping {
		/**
		 * YITH_Delivery_Date_WC_Advanced_Shipping constructor.
		 */
		public function __construct() {
			add_filter( 'ywcdd_get_shipping_method_option', array( $this, 'get_table_rate_option_name' ), 10, 2 );
			add_filter( 'woocommerce_settings_api_form_fields_advanced_shipping', array( $this, 'add_custom_fields' ), 99, 1 );

		}

		/**
		 * Return the table rate option name
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
				$shipping_option = get_option( 'woocommerce_advanced_shipping_settings', array() );
			}

			return $shipping_option;
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
				'class'   => 'ywcdd_processing_method wc-enhanced-select',
				'options' => $options,
			);

			$form_fields['set_method_as_mandatory'] = array(
				'title'       => __( 'Set as required', 'yith-woocommerce-delivery-date' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'class'       => 'ywcdd_set_mandatory',
				'description' => __( 'If enabled, customers must select a date for the delivery', 'yith-woocommerce-delivery-date' ),
			);

			return $form_fields;
		}


	}
}

new YITH_Delivery_Date_WC_Advanced_Shipping();
