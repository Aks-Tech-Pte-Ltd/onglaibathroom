<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Manage class that add integration with Flexible shipping
 *
 * @package YITH WooCommerce Delivery Date\Classes\Integrations
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Delivery_Date_WC_Flexible_Shipping' ) ) {
	/**
	 * Class YITH_Delivery_Date_WC_Flexible_Shipping
	 */
	class YITH_Delivery_Date_WC_Flexible_Shipping {
		/**
		 * YITH_Delivery_Date_WC_Flexible_Shipping constructor.
		 */
		public function __construct() {
			add_filter( 'ywcdd_get_shipping_method_option', array( $this, 'get_flexible_shipping_option_name' ), 10, 2 );
			add_action( 'admin_enqueue_scripts', array( $this, 'hide_custom_fields' ), 99 );
			add_filter( 'woocommerce_settings_api_form_fields_flexible_shipping_info', array( $this, 'add_custom_fields' ), 99, 1 );
		}

		/**
		 * Return the flexible shipping option name
		 *
		 * @param array  $shipping_option The shipping options.
		 * @param string $option_name The option name.
		 *
		 * @return array
		 * @since 1.0.0
		 * @author YITH <plugins@yithemes.com>
		 */
		public function get_flexible_shipping_option_name( $shipping_option, $option_name ) {
			if ( false !== strpos( $option_name, 'flexible_shipping' ) ) {
				$shipping_option = get_option( 'woocommerce_' . $option_name . '_settings' );

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

			$options                              = YITH_Delivery_Date_Processing_Method()->get_formatted_processing_method();
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

		/**
		 * Hide the unnecessary options
		 *
		 * @since 1.0.0
		 */
		public function hide_custom_fields() {

			if ( ! empty( $_GET['instance_id'] ) && ( ! empty( $_GET['tab'] ) && 'shipping' === $_GET['tab'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				$script = "jQuery(document).ready(function($){
        		        var flexible_shipping_table = $(document).find('table.flexible_shipping_method_rules');

        		        if( flexible_shipping_table.length ){

        		            var processing_option = $(document).find('select.ywcdd_processing_method'),
        		                set_mandatory = $(document).find('.ywcdd_set_mandatory' );

        		                if( processing_option.length ){

        		                    processing_option.closest('tr').remove();
        		                }

        		                if( set_mandatory.length){
        		                    set_mandatory.closest('tr').remove();
        		                }
        		        }

        		    });";
				wp_add_inline_script( 'woocommerce_admin', $script );
			}
		}


	}
}

new YITH_Delivery_Date_WC_Flexible_Shipping();
