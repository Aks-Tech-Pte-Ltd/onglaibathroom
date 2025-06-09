<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Manage class that add integration with WC FedEx plugin
 *
 * @package YITH WooCommerce Delivery Date\Classes\Integrations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Delivery_Date_WC_FedEx_PrintLabel' ) ) {
	/**
	 * Class YITH_Delivery_Date_WC_FedEx_PrintLabel
	 */
	class YITH_Delivery_Date_WC_FedEx_PrintLabel {
		/**
		 * YITH_Delivery_Date_WC_FedEx_PrintLabel constructor.
		 */
		public function __construct() {
			add_filter( 'ywcdd_get_shipping_method_option', array( $this, 'get_fedex_option_name' ), 10, 2 );
			add_filter( 'woocommerce_settings_api_form_fields_wf_fedex_woocommerce_shipping', array( $this, 'add_custom_fields' ), 5, 1 );
			add_filter( 'ywcdd_disable_delivery_date_for_shipping_method', array( $this, 'hide_duplicate_rows' ), 10, 3 );

		}

		/**
		 * Return the fedex option name
		 *
		 * @param array  $shipping_option The shipping options.
		 * @param string $option_name The option name.
		 *
		 * @return array
		 * @since 1.0.0
		 * @author YITH <plugins@yithemes.com>
		 */
		public function get_fedex_option_name( $shipping_option, $option_name ) {

			if ( empty( $shipping_option ) ) {
				$delimiter = ':';

				if ( version_compare( WC()->version, '2.6.0', '>=' ) ) {
					$delimiter = '_';
				}

				$option = explode( $delimiter, $option_name );

				if ( ! empty( $option[0] ) && strpos( $option[0], 'wf_fedex_woocommerce_shipping' ) !== false ) {
					$shipping_option = get_option( 'woocommerce_wf_fedex_woocommerce_shipping_settings' );
				}
			}

			return $shipping_option;
		}

		/**
		 * Hide the unnecessary options
		 *
		 * @param bool   $hide The old value.
		 * @param string $key The key.
		 * @param string $shipping_id The shipping id.
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		public function hide_duplicate_rows( $hide, $key, $shipping_id ) {

			if ( 'wf_fedex_woocommerce_shipping' === $shipping_id ) {
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

			$options                              = YITH_Delivery_Date_Processing_Method()->get_formatted_processing_method();
			$form_fields['select_process_method'] = array(

				'title'   => __( 'Processing Method', 'yith-woocommerce-delivery-date' ),
				'type'    => 'select',
				'default' => '',
				'class'   => 'ywcdd_processing_method wc-enhanced-select fedex_general_tab',
				'options' => $options,
			);

			$form_fields['set_method_as_mandatory'] = array(

				'title'       => __( 'Set as required', 'yith-woocommerce-delivery-date' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'class'       => 'ywcdd_set_mandatory fedex_general_tab',
				'description' => __( 'If enabled, customers must select a date for the delivery', 'yith-woocommerce-delivery-date' ),
			);

			return $form_fields;
		}


	}
}

new YITH_Delivery_Date_WC_FedEx_PrintLabel();
