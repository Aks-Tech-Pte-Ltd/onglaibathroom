<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Manage class that add integration with WF FedEx plugin
 *
 * @package YITH WooCommerce Delivery Date\Classes\Integrations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Delivery_Date_WC_FedEx' ) ) {
	/**
	 * Class YITH_Delivery_Date_WC_FedEx
	 */
	class YITH_Delivery_Date_WC_FedEx {
		/**
		 * YITH_Delivery_Date_WC_FedEx constructor.
		 */
		public function __construct() {
			add_filter( 'woocommerce_settings_api_form_fields_wf_fedex_woocommerce_shipping', array( $this, 'add_custom_fields' ) );
			add_filter( 'ywcdd_get_shipping_method_option', array( $this, 'get_fedex_option_name' ), 10, 2 );
			add_action( 'admin_enqueue_scripts', array( $this, 'hide_custom_fields' ), 99 );

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

				if ( ! empty( $option[1] ) && 'fedex' === strtolower( $option[1] ) ) {
					$shipping_option = get_option( 'woocommerce_wf_fedex_woocommerce_shipping_settings' );
				}
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
			$all_processing_method = get_posts(
				array(
					'post_type'   => 'yith_proc_method',
					'post_status' => 'publish',
					'numberposts' => - 1,
				)
			);

			$options = array();

			$options[''] = __( 'Select a processing method', 'yith-woocommerce-delivery-date' );

			foreach ( $all_processing_method as $key => $method ) {
				$options[ $method->ID ] = get_the_title( $method->ID );
			}

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

		/**
		 * Hide the unnecessary options
		 *
		 * @since 1.0.0
		 */
		public function hide_custom_fields() {
			if ( ! empty( $_GET['instance_id'] ) && ( ! empty( $_GET['tab'] ) && 'shipping' === $_GET['tab'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				$script = "jQuery(document).ready(function($){
        		        var fedex_table = $(document).find('table.fedex_services');

        		        if( fedex_table.length ){

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

new YITH_Delivery_Date_WC_FedEx();
