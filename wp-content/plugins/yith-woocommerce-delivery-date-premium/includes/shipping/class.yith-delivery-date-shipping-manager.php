<?php // phpcs:ignore WordPress.Files.FileName
/**
 * The class that manage checkout feature
 *
 * @package YITH WooCommerce Delivery Date\Classes\
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'YITH_Delivery_Date_Shipping_Manager' ) ) {
	/**
	 * Class YITH_Delivery_Date_Shipping_Manager
	 */
	class YITH_Delivery_Date_Shipping_Manager {
		/**
		 * The instance of the class
		 *
		 * @var YITH_Delivery_Date_Shipping_Manager
		 */
		protected static $_instance; // phpcs:ignore
		/**
		 * The current shipping method
		 *
		 * @var string
		 */
		protected $shipping_method;

		/**
		 * YITH_Delivery_Date_Shipping_Manager constructor.
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'set_shipping_method' ), 99 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ), 25 );
			add_action( 'wp_ajax_update_datepicker', array( $this, 'update_datepicker' ) );
			add_action( 'wp_ajax_nopriv_update_datepicker', array( $this, 'update_datepicker' ) );
			add_action( 'wp_ajax_update_timeslot', array( $this, 'update_timeslot_ajax' ) );
			add_action( 'wp_ajax_nopriv_update_timeslot', array( $this, 'update_timeslot_ajax' ) );
			add_action( 'wp_ajax_update_carrier_list', array( $this, 'update_carrier_list_by_shipping_method' ) );
			add_action(
				'wp_ajax_nopriv_update_carrier_list',
				array(
					$this,
					'update_carrier_list_by_shipping_method',
				)
			);

			add_action(
				'woocommerce_after_checkout_validation',
				array(
					$this,
					'validate_checkout_width_delivery_date',
				),
				10
			);

			if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {

				add_action( 'woocommerce_checkout_create_order', array( $this, 'add_delivery_date_info_order_meta' ) );
				add_action( 'woocommerce_checkout_shipping', array( $this, 'print_delivery_from' ), 20 );
			} else {
				add_action(
					'woocommerce_checkout_update_order_meta',
					array(
						$this,
						'add_delivery_date_info_order_meta',
					),
					10,
					1
				);
				add_action( 'woocommerce_after_order_notes', array( $this, 'print_delivery_from' ), 20 );
			}

			add_action(
				'woocommerce_order_details_after_order_table',
				array(
					$this,
					'show_delivery_order_details_after_order_table',
				)
			);

			add_action( 'woocommerce_order_status_changed', array( $this, 'manage_order_event' ), 20, 3 );

			// add delivery date information into woocommerce email.
			add_action( 'woocommerce_email_order_meta', array( $this, 'print_delivery_date_into_email' ), 10, 4 );

			$enable_gdpr_option = get_option( 'ywcdd_user_privacy', 'no' );

			if ( 'yes' === $enable_gdpr_option ) {

				add_action( 'woocommerce_checkout_terms_and_conditions', array( $this, 'show_checkbox' ), 15 );
				add_action( 'woocommerce_checkout_create_order', array( $this, 'register_customer_choose' ), 20 );
			}

		}

		/**
		 * Create or return the instance of the class
		 *
		 * @return YITH_Delivery_Date_Shipping_Manager
		 * @since  1.0.0
		 * @author YITH <plugins@yithemes.com>
		 */
		public static function get_instance() {

			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Get the shipping method and add custom form fields filter
		 *
		 * @since  1.0.0
		 */
		public function set_shipping_method() {

			WC()->shipping->load_shipping_methods();
			$this->shipping_method = wp_list_pluck( WC()->shipping()->get_shipping_methods(), 'id' );

			if ( ! empty( $this->shipping_method ) ) {

				foreach ( $this->shipping_method as $key => $shipping_id ) {
					/**
					 * APPLY_FILTERS: ywcdd_disable_delivery_date_for_shipping_method
					 *
					 * This filter allow to disable or not the delivery settings inside the WC shipping method
					 *
					 * @param bool   $disable True or false.
					 * @param string $key The shipping key.
					 * @param int    $shipping_id The shipping id.
					 *
					 * @return bool
					 */
					if ( apply_filters( 'ywcdd_disable_delivery_date_for_shipping_method', false, $key, $shipping_id ) ) {
						continue;
					}
					add_filter(
						'woocommerce_settings_api_form_fields_' . $shipping_id,
						array(
							$this,
							'add_custom_fields',
						),
						99
					);
					/**
					 * Added compatibility with wc 2.6
					 */
					add_filter(
						'woocommerce_shipping_instance_form_fields_' . $shipping_id,
						array(
							$this,
							'add_custom_fields',
						),
						99
					);

				}
			}
		}

		/**
		 * Include the script and style
		 *
		 * @since  1.0.0
		 */
		public function enqueue_frontend_scripts() {

			if ( is_checkout() ) {
				wp_enqueue_script(
					'ywcdd_frontend',
					YITH_DELIVERY_DATE_ASSETS_URL . 'js/' . yit_load_js_file( 'yith_deliverydate_checkout.js' ),
					array(
						'jquery',
						'jquery-ui-datepicker',
						'select2',
					),
					YITH_DELIVERY_DATE_VERSION,
					true
				);
				/**
				 * APPLY_FILTERS: ywcdd_change_year_suffix
				 *
				 * This filter allow to change the delivery suffix in the datepicker.
				 *
				 * @param string $suffix The suffix.
				 *
				 * @return string
				 */
				/**
				 * APPLY_FILTERS: ywcdd_number_of_months
				 *
				 * This filter allow to change the amount of months to show in the datepicker.
				 *
				 * @param string $set_default Yes or not.
				 *
				 * @return string
				 */
				/**
				 * APPLY_FILTERS: ywcdd_set_first_available_date
				 *
				 * This filter allow to set as default the first available date in the datepicker.
				 *
				 * @param int $mounts_to_show The mounts to show.
				 *
				 * @return int
				 */
				/**
				 * APPLY_FILTERS: ywcdd_update_checkout
				 *
				 * This filter allow to update the datepicker after an update checkout action.
				 *
				 * @param string $update Yes or not.
				 *
				 * @return string
				 */
				$params = array(
					'ajax_url'                => admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
					'actions'                 => array(
						'update_datepicker'   => 'update_datepicker',
						'update_timeslot'     => 'update_timeslot',
						'update_carrier_list' => 'update_carrier_list',
					),
					'timeformat'              => 'H:i',
					'dateformat'              => get_option( 'yith_delivery_date_format', 'yy-mm-dd' ),
					'yearSuffix'              => apply_filters( 'ywcdd_change_year_suffix', '' ),
					'numberOfMonths'          => apply_filters( 'ywcdd_number_of_months', wp_is_mobile() ? 1 : 2 ),
					'is_mobile'               => wp_is_mobile(),
					'open_datepicker'         => ywcdd_get_delivery_mode(),
					'show_the_min_date'       => apply_filters( 'ywcdd_set_first_available_date', 'yes' ),
					'trigger_update_checkout' => apply_filters( 'ywcdd_update_checkout', 'yes' ),
					/* translators: %s is a date */
					'msgUnavailableDate'      => __( 'Error: the date %s isn\'t available.', 'yith-woocommerce-delivery-date' ),
					'msgInvalidDate'          => __( 'Enter a valid date.', 'yith-woocommerce-delivery-date' ),

				);

				wp_localize_script( 'ywcdd_frontend', 'ywcdd_params', $params );
				wp_enqueue_style( 'ywcdd_style', YITH_DELIVERY_DATE_ASSETS_URL . 'css/yith_delivery_date_frontend.css', array(), YITH_DELIVERY_DATE_VERSION );
			}
		}

		/**
		 * Add custom form fields in shipping method
		 *
		 * @param array $form_fields The form fields.
		 *
		 * @return array
		 * @since  1.0.0
		 *
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

		/**
		 * Show the delivery field in checkout
		 *
		 * @since 1.0.0
		 */
		public function print_delivery_from() {

			if ( ! is_null( WC()->cart ) && WC()->cart->needs_shipping() ) {
				$chosen_methods  = WC()->session->get( 'chosen_shipping_methods' );
				$chosen_shipping = ! empty( $chosen_methods[0] ) ? $chosen_methods[0] : '';
				/**
				 * APPLY_FILTERS: ywcdd_load_always_delivery_date
				 *
				 * This filter allow to load the delivery date template in the checkout.
				 *
				 * @param bool   $load True or false.
				 * @param string $chosen_shipping The selected shipping method.
				 *
				 * @return bool
				 */
				if ( apply_filters( 'ywcdd_load_always_delivery_date', true, $chosen_shipping ) ) {

					$this->load_delivery_template( $chosen_shipping );
				}
			}

		}

		/**
		 * Load the right template
		 *
		 * @param string $shipping_method The current shipping method.
		 * @param bool   $html Print or return the html.
		 *
		 * @return void|string
		 * @since 1.0.0
		 */
		public function load_delivery_template( $shipping_method, $html = false ) {

			$shipping_settings = $this->get_woocommerce_shipping_option( $shipping_method );

			$processing_method = isset( $shipping_settings['select_process_method'] ) ? $shipping_settings['select_process_method'] : '';
			$is_mandatory      = isset( $shipping_settings['set_method_as_mandatory'] ) ? $shipping_settings['set_method_as_mandatory'] : 'no';

			if ( $html ) {
				return wc_get_template_html(
					'woocommerce/checkout/delivery-date-content.php',
					array(
						'shipping_id'       => $shipping_method,
						'is_mandatory'      => $is_mandatory,
						'processing_method' => $processing_method,
					),
					YITH_DELIVERY_DATE_TEMPLATE_PATH,
					YITH_DELIVERY_DATE_TEMPLATE_PATH
				);
			} else {
				wc_get_template(
					'woocommerce/checkout/delivery-date-content.php',
					array(
						'shipping_id'       => $shipping_method,
						'is_mandatory'      => $is_mandatory,
						'processing_method' => $processing_method,
					),
					YITH_DELIVERY_DATE_TEMPLATE_PATH,
					YITH_DELIVERY_DATE_TEMPLATE_PATH
				);
			}
		}

		/**
		 * Get the woocommerce shipping options by shipping name
		 *
		 * @param string $shipping_option The shipping option name.
		 *
		 * @return array
		 */
		public function get_woocommerce_shipping_option( $shipping_option ) {

			if ( is_array( $shipping_option ) ) {
				return array();
			}

			if ( version_compare( WC()->version, '2.6.0', '>=' ) ) {
				$shipping_option = str_replace( ':', '_', $shipping_option );
			}

			$shipping_settings = get_option( 'woocommerce_' . $shipping_option . '_settings' );

			/**
			 * APPLY_FILTERS: ywcdd_get_shipping_method_option
			 *
			 * This filter allow to change the shipping method configuration.
			 *
			 * @param array  $shipping_settings The settings options.
			 * @param string $shipping_option The selected shipping method.
			 *
			 * @return array
			 */
			return apply_filters( 'ywcdd_get_shipping_method_option', $shipping_settings, $shipping_option );
		}

		/**
		 * Upload the carrier list in checkout page by current shipping method
		 *
		 * @since 1.0.0
		 */
		public function update_carrier_list_by_shipping_method() {

			$shipping_id = isset( $_POST['ywcdd_shipping_id'] ) ? wp_unslash( $_POST['ywcdd_shipping_id'] ) : ''; // phpcs:ignore

			$shipping_settings = $this->get_woocommerce_shipping_option( $shipping_id );

			$processing_method   = ! empty( $shipping_settings['select_process_method'] ) ? $shipping_settings['select_process_method'] : '';
			$template            = '';
			$current_proc_method = isset( $_POST['ywcdd_process_method'] ) ? wp_unslash( $_POST['ywcdd_process_method'] ) : ''; // phpcs:ignore
			/**
			 * APPLY_FILTERS: ywcdd_force_change_template
			 *
			 * This filter force or not the loading of the delivery date template.
			 *
			 * @param bool $force True or not.
			 *
			 * @return bool
			 */
			$change_template = apply_filters( 'ywcdd_force_change_template', $current_proc_method !== $processing_method );
			if ( ! empty( $processing_method ) && $change_template ) {

				$shipping_id = isset( $_POST['ywcdd_shipping_id'] ) ? wp_unslash( $_POST['ywcdd_shipping_id'] ) : ''; // phpcs:ignore

				$template = $this->load_delivery_template( $shipping_id, true );
			}

			wp_send_json(
				array(
					'template'             => $template,
					'update_delivery_form' => $change_template,
				)
			);

		}

		/**
		 * Find the right min days for process an order
		 *
		 * @param int $base_day The days needed to product to be "read" for shipping.
		 * @param int $process_shipping_method_id The processing method id.
		 *
		 * @return int
		 * @since  1.0.0
		 *
		 */
		public function find_day_for_shipping( $base_day, $process_shipping_method_id ) {

			$new_base_days = array();
			$cart          = WC()->cart;

			if ( isset( $cart ) && ! $cart->is_empty() ) {

				foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
					/**
					 * The product
					 *
					 * @var WC_Product $_product
					 */
					$_product = $values['data'];
					$quantity = $values['quantity'];

					$new_base_days[] = YITH_Delivery_Date_Product_Frontend()->get_custom_base_day_for_product( $_product, $quantity );

				}
			}

			$max_day = count( $new_base_days ) > 0 ? max( $new_base_days ) : 0;

			return max( $max_day, $base_day );
		}

		/**
		 * Get all available dates for delivery
		 *
		 * @param int  $processing_id The processing method id.
		 * @param int  $carrier_id The carrier id.
		 * @param bool $format Check if format or not.
		 *
		 * @return array
		 * @since  1.0.0
		 *
		 */
		public function get_available_date_range( $processing_id, $carrier_id, $format = true ) {

			add_filter( 'ywcdd_get_processing_working_day', array( $this, 'find_day_for_shipping' ), 10, 2 );
			$shipping_date   = YITH_Delivery_Date_Manager()->get_first_shipping_date( $processing_id );
			$delivery_date   = YITH_Delivery_Date_Manager()->get_first_delivery_date( $carrier_id, array( 'shipping_date' => $shipping_date ) );
			$all_select_days = YITH_Delivery_Date_Manager()->get_all_delivery_dates( $carrier_id, array( 'from_date' => $delivery_date ) );

			remove_filter( 'ywcdd_get_processing_working_day', array( $this, 'find_day_for_shipping' ), 10 );

			return $all_select_days;
		}

		/**
		 * Return all available days
		 *
		 * @param array $date_range The date range.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		public function get_format_available_date_range( $date_range ) {

			$available_days = array();

			foreach ( $date_range as $date ) {
				$available_days[] = $date;
			}

			return $available_days;
		}

		/**
		 * Set all available delivery date
		 *
		 * @since  1.0.0
		 */
		public function update_datepicker() {

			if ( isset( $_POST['ywcdd_carrier_id'] ) ) { // phpcs:ignore

				$carrier_id = intval( $_POST['ywcdd_carrier_id'] ); // phpcs:ignore
				$process_id = intval( $_POST['ywcdd_process_id'] ); // phpcs:ignore

				$all_select_days = $this->get_available_date_range( $process_id, $carrier_id );

				$timeslot_result = array();
				$text            = '';

				if ( count( $all_select_days ) > 0 ) {

					$format_min_date = $all_select_days[0];
					$text            = sprintf( '%s <strong>%s</strong> <a href="" class="ywcdd_edit_date">%s</a>', __( 'Your order will be delivered on', 'yith-woocommerce-delivery-date' ), $format_min_date, __( 'Edit date', 'yith-woocommerce-delivery-date' ) );
					$timeslot_result = $this->update_timeslot( $carrier_id, $all_select_days[0] );
					$result          = array_merge(
						array(
							'available_days' => $all_select_days,
							'message'        => $text,
						),
						$timeslot_result
					);

				}

				wp_send_json( $result );

			}
		}

		/**
		 * Get all available timeslot for specific date and carrier
		 *
		 * @param int $carrier_id The carrier id.
		 * @param int $date_selected The date selected.
		 *
		 * @return array
		 */
		public function update_timeslot( $carrier_id, $date_selected ) {

			$available_timeslot = YITH_Delivery_Date_Manager()->get_available_time_slots( $carrier_id, $date_selected );

			$json_slot = $this->format_timeslot( $available_timeslot );

			return array( 'available_timeslot' => $json_slot );
		}

		/**
		 * Update the time slot in ajax
		 *
		 * @since 1.0.0
		 */
		public function update_timeslot_ajax() {

			if ( isset( $_POST['ywcdd_carrier_id'] ) ) { // phpcs:ignore

				$carrier_id    = wp_unslash( $_POST['ywcdd_carrier_id'] ); // phpcs:ignore
				$date_selected = wp_unslash( $_POST['ywcdd_date_selected'] ); // phpcs:ignore
				$results       = $this->update_timeslot( $carrier_id, $date_selected );

				wp_send_json( $results );
			}
		}


		/**
		 * Format the time slots
		 *
		 * @param array $available_timeslot The time slot.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		public function format_timeslot( $available_timeslot ) {

			$format_slot = array();

			if ( ! empty( $available_timeslot ) ) {
				foreach ( $available_timeslot as $slot_id => $slot ) {

					$timefrom       = ywcdd_display_timeslot( strtotime( $slot['timefrom'] ) );
					$timeto         = ywcdd_display_timeslot( strtotime( $slot['timeto'] ) );
					$fee            = $slot['fee'];
					$fee_name       = ! empty( $slot['fee_name'] ) ? $slot['fee_name'] : __( 'Fee', 'yith-woocommerce-delivery-date' );
					$timeslotformat = sprintf( '%s: %s - %s: %s', _x( 'From', 'from time', 'yith-woocommerce-delivery-date' ), $timefrom, _x( 'To', 'to time', 'yith-woocommerce-delivery-date' ), $timeto );
					$is_taxable     = false;
					if ( '' !== $fee ) {

						$suffix = '';

						$is_taxable = 'yes' === get_option( 'ywcdd_fee_is_taxable', 'no' );
						/**
						 * APPLY_FILTERS: ywcdd_time_slot_fee_taxable
						 *
						 * This filter allow to set the fee as taxable or not.
						 *
						 * @param bool $is_taxable True or not.
						 *
						 * @return bool
						 */
						$is_taxable = apply_filters( 'ywcdd_time_slot_fee_taxable', $is_taxable );
						if ( $is_taxable ) {
							$suffix = WC()->countries->ex_tax_or_vat();

							$timeslotformat .= sprintf( ' - %s %s ', $fee_name, wc_price( $fee ) . ' ' . $suffix );

						} else {
							$timeslotformat .= sprintf( ' ( %s %s )', $fee_name, wc_price( $fee ) . $suffix );
						}
					}

					/**
					 * APPLY_FILTERS: yith_delivery_date_time_slot_format
					 *
					 * This filter allow to change the date format of time slots.
					 *
					 * @param string $timeslotformat The formatted time slots.
					 * @param string $timefrom The time slot  from.
					 * @param string $timeto The time slot  to.
					 * @param bool   $is_taxable True or false.
					 * @param float  $fee The fee value.
					 * @param string $fee_name The fee name.
					 * @param array  $slot The whole slot configuration.
					 *
					 * @return string
					 */
					$timeslotformat          = apply_filters( 'yith_delivery_date_time_slot_format', $timeslotformat, $timefrom, $timeto, $is_taxable, $fee, $fee_name, $slot );
					$format_slot[ $slot_id ] = $timeslotformat;
				}
			}

			return $format_slot;
		}

		/**
		 * Validate checkout with delivery information
		 *
		 * @since  1.0.0
		 */
		public function validate_checkout_width_delivery_date() {

			if ( isset( $_POST['ywcdd_carrier'] ) && isset( $_POST['ywcdd_datepicker'] ) ) { // phpcs:ignore

				$carrier_id         = intval( wp_unslash( $_POST['ywcdd_carrier'] ) ); // phpcs:ignore
				$date_selected      = wp_unslash( $_POST['ywcdd_delivery_date'] ); // phpcs:ignore
				$is_mandatory       = wp_unslash( $_POST['ywcdd_is_mandatory'] ); // phpcs:ignore
				$time_slot_av       = wp_unslash( $_POST['ywcdd_timeslot_av'] ); // phpcs:ignore
				$time_slot_selected = ! empty( $_POST['ywcdd_timeslot'] ) ? wp_unslash( $_POST['ywcdd_timeslot'] ) : ''; // phpcs:ignore

				if ( ! empty( $date_selected ) ) {
					$now                = strtotime( date( 'Y-m-d', current_time( 'timestamp' ) ) ); // phpcs:ignore
					$date_selected_time = strtotime( $date_selected );

					if ( $date_selected_time < $now ) {
						wc_add_notice( __( 'An error occurred during the checkout,  please try again', 'yith-woocommerce-delivery-date' ), 'error' );
					}
				}

				if ( 'yes' === $is_mandatory ) {

					if ( - 1 !== $carrier_id && '' === $carrier_id ) {

						$error = sprintf( '<strong>%s</strong> %s', __( 'Carrier', 'yith-woocommerce-delivery-date' ), __( 'is a required field.', 'yith-woocommerce-delivery-date' ) );
						wc_add_notice( $error, 'error' );
					}
					if ( is_numeric( $carrier_id ) && '' === $date_selected ) {
						$error = sprintf( '<strong>%s</strong> %s', __( 'Delivery Date', 'yith-woocommerce-delivery-date' ), __( 'is a required field.', 'yith-woocommerce-delivery-date' ) );
						wc_add_notice( $error, 'error' );

					}

					if ( 'yes' === $time_slot_av && '' === $time_slot_selected ) {
						$error = sprintf( '<strong>%s</strong> %s', __( 'Time Slot', 'yith-woocommerce-delivery-date' ), __( 'is a required field.', 'yith-woocommerce-delivery-date' ) );
						wc_add_notice( $error, 'error' );
					}

					if ( ! empty( $time_slot_selected ) ) {
						$slot_selected = YITH_Delivery_Date_Carrier()->get_time_slot_by_id( $carrier_id, $time_slot_selected );
						$is_lock       = YITH_Delivery_Date_Manager()->check_if_time_slot_is_lockout( $slot_selected, $carrier_id, strtotime( $date_selected ) );

						if ( $is_lock ) {

							$error = sprintf( __( '<strong>Time slot:</strong> the time slot select is no longer available, choose another slot', 'yith-woocommerce-delivery-date' ) );
							wc_add_notice( $error, 'error' );
						}
					}
				}
			} else {
				if ( ! is_null( WC()->cart ) && WC()->cart->needs_shipping() ) {
					$shipping_method = isset( $_POST['shipping_method'] ) ? current( wp_unslash( $_POST['shipping_method'] ) ) : array(); // phpcs:ignore

					$option = $this->get_woocommerce_shipping_option( $shipping_method );
					/**
					 * APPLY_FILTERS: ywcdd_checkout_validation
					 *
					 * This filter allow force the checkout validation.
					 *
					 * @param bool $force_validation True or false.
					 *
					 * @return string
					 */
					if ( is_array( $shipping_method ) || ! empty( $option['select_process_method'] ) && apply_filters( 'ywcdd_checkout_validation', true ) ) {

						wc_add_notice( __( 'An error occurred during the checkout,  please try again', 'yith-woocommerce-delivery-date' ), 'error' );
					}
				}
			}
			/**
			 * DO_ACTION: ywcdd_after_checkout_validation
			 *
			 * Triggered after validate the delivery date info in the checkout.
			 */
			do_action( 'ywcdd_after_checkout_validation' );
		}

		/**
		 * Save the delivery details in the order meta
		 *
		 * @param WC_Order $order The order.
		 *
		 * @since 1.0.0
		 */
		public function add_delivery_date_info_order_meta( $order ) {

			if ( isset( $_POST['ywcdd_datepicker'] ) ) { // phpcs:ignore

				if ( ! $order instanceof WC_Order ) {

					$order = wc_get_order( $order );
				}

				$carrier_id    = isset( $_POST['ywcdd_carrier'] ) ? wp_unslash( $_POST['ywcdd_carrier'] ) : - 1; // phpcs:ignore
				$delivery_date = isset( $_POST['ywcdd_delivery_date'] ) ? wp_unslash( $_POST['ywcdd_delivery_date'] ) : ''; // phpcs:ignore
				$slot_id       = isset( $_POST['ywcdd_timeslot'] ) ? wp_unslash( $_POST['ywcdd_timeslot'] ) : ''; // phpcs:ignore
				$proc_method   = isset( $_POST['ywcdd_process_method'] ) ? wp_unslash( $_POST['ywcdd_process_method'] ) : - 1; // phpcs:ignore
				$time_from     = '';
				$time_to       = '';

				$last_shipping_date = YITH_Delivery_Date_Manager()->get_last_shipping_date( $delivery_date, $proc_method, $carrier_id );
				$last_shipping_date = date( 'Y-m-d', $last_shipping_date ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions

				$timeslot = YITH_Delivery_Date_Carrier()->get_time_slot_by_id( $carrier_id, $slot_id );

				if ( ! empty( $slot_id ) ) {
					if ( $timeslot ) {

						$time_from = date( 'H:i', strtotime( $timeslot['timefrom'] ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
						$time_to   = date( 'H:i', strtotime( $timeslot['timeto'] ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions

					} else {
						throw new Exception( __( 'An error occurred during the checkout,  please try again', 'yith-woocommerce-delivery-date' ) );
					}
				}

				$delivery_date = strtotime( $delivery_date );

				$delivery_date = date( 'Y-m-d', $delivery_date ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions

				$order_meta = array(
					'ywcdd_order_delivery_date'     => $delivery_date,
					'ywcdd_order_shipping_date'     => $last_shipping_date,
					'ywcdd_order_slot_from'         => $time_from,
					'ywcdd_order_slot_to'           => $time_to,
					'ywcdd_order_carrier_id'        => $carrier_id,
					'ywcdd_order_processing_method' => $proc_method,
					'ywcdd_order_carrier'           => get_the_title( $carrier_id ),
				);

				foreach ( $order_meta as $key => $meta ) {

					$order->update_meta_data( $key, $meta );
				}
				if ( 'woocommerce_checkout_update_order_meta' === current_filter() ) {

					$order->save();
				}
			}
		}

		/**
		 * Show the delivery details in order or email
		 *
		 * @param WC_Order $order The order.
		 * @param bool     $show_shipping Check if show also the shipping details.
		 *
		 * @throws Exception The exception.
		 * @since 1.0.0
		 */
		public function show_delivery_order_details( $order, $show_shipping = false ) {

			$carrier_label = $order->get_meta( 'ywcdd_order_carrier' );
			$shipping_date = $order->get_meta( 'ywcdd_order_shipping_date' );
			$delivery_date = $order->get_meta( 'ywcdd_order_delivery_date' );
			$time_from     = $order->get_meta( 'ywcdd_order_slot_from' );
			$time_to       = $order->get_meta( 'ywcdd_order_slot_to' );
			$carrier_id    = $order->get_meta( 'ywcdd_order_carrier_id' );
			$processing_id = $order->get_meta( 'ywcdd_order_processing_method' );

			if ( ! empty( $delivery_date ) ) {
				$delivery_date = wc_format_datetime( new WC_DateTime( $delivery_date, new DateTimeZone( 'UTC' ) ) );
				$shipping_date = wc_format_datetime( new WC_DateTime( $shipping_date, new DateTimeZone( 'UTC' ) ) );
				/**
				 * APPLY_FILTERS: ywcdd_delivery_section_title
				 *
				 * This filter allow change the delivery section title in order review and emails.
				 *
				 * @param string $section_title The title.
				 * @param int    $carrier_id The carrier id.
				 * @param int    $processing_id The processing id.
				 *
				 * @return string
				 */
				echo sprintf( '<h2>%s</h2>', apply_filters( 'ywcdd_delivery_section_title', __( 'Delivery Details', 'yith-woocommerce-delivery-date' ), $carrier_id, $processing_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput
				/**
				 * APPLY_FILTERS: ywcdd_delivery_section_title
				 *
				 * This filter allow change the delivery section title in order review and emails.
				 *
				 * @param string $section_title The title.
				 * @param int    $carrier_id The carrier id.
				 * @param int    $processing_id The processing id.
				 *
				 * @return string
				 */
				/**
				 * APPLY_FILTERS: ywcdd_change_carrier_label
				 *
				 * This filter allow change the carrier title in order review and emails.
				 *
				 * @param string $title The title.
				 *
				 * @return string
				 */
				/**
				 * APPLY_FILTERS: ywcdd_change_delivery_date_label
				 *
				 * This filter allow change the delivery date title in order review and emails.
				 *
				 * @param string $title The title.
				 *
				 * @return string
				 */
				/**
				 * APPLY_FILTERS: ywcdd_change_timeslot_label
				 *
				 * This filter allow change the time slot section title in order review and emails.
				 *
				 * @param string $title The title.
				 *
				 * @return string
				 */
				$fields = array(
					'carrier'       => array(
						'label' => apply_filters( 'ywcdd_change_carrier_label', __( 'Carrier', 'yith-woocommerce-delivery-date' ) ),
						'value' => $carrier_label,
					),
					'shipping_date' => array(
						'label' => apply_filters( 'ywcdd_change_shipping_date_label', _x( 'Shipping Date', '[Part of]: Shipping Date within 20th March 2019', 'yith-woocommerce-delivery-date' ) ),
						'value' => sprintf( '%s %s', _x( 'within', '[Part of]: Shipping Date within 20th March 2019', 'yith-woocommerce-delivery-date' ), $shipping_date ),
					),
					'delivery_date' => array(
						'label' => apply_filters( 'ywcdd_change_delivery_date_label', __( 'Delivery Date', 'yith-woocommerce-delivery-date' ) ),
						'value' => $delivery_date,
					),
					'timeslot'      => array(
						'label' => apply_filters( 'ywcdd_change_timeslot_label', __( 'Time Slot', 'yith-woocommerce-delivery-date' ) ),
						'value' => ( empty( $time_from ) || empty( $time_to ) ) ? '' : sprintf( '%s - %s', ywcdd_display_timeslot( $time_from ), ywcdd_display_timeslot( $time_to ) ),
					),
				);

				/**
				 * APPLY_FILTERS: yith_delivery_date_email_fields
				 *
				 * This filter allow add or remove delivery fields to show in order review and in emails.
				 *
				 * @param array $fields The fields.
				 *
				 * @return array
				 */
				$fields = apply_filters( 'yith_delivery_date_email_fields', $fields, $show_shipping, $carrier_id, $processing_id, $order );

				echo '<ul class="order_details bacs_details">' . PHP_EOL;
				foreach ( $fields as $field_key => $field ) {
					if ( ! empty( $field['value'] ) ) {

						if ( 'shipping_date' !== $field_key ) {
							echo '<li class="' . esc_attr( $field_key ) . '">' . esc_attr( $field['label'] ) . ': <strong>' . wptexturize( $field['value'] ) . '</strong></li>' . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput
						} elseif ( 'shipping_date' === $field_key && apply_filters( 'ywcdd_show_date_shipping_details', $show_shipping ) ) {
							echo '<li class="' . esc_attr( $field_key ) . '">' . esc_attr( $field['label'] ) . ': <strong>' . wptexturize( $field['value'] ) . '</strong></li>' . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput
						}
					}
				}
				echo '</ul>' . PHP_EOL;
			}

		}

		/**
		 * Add delivery details in review order
		 *
		 * @param WC_Order $order The order.
		 *
		 * @throws Exception The exception.
		 * @since  1.0.0
		 *
		 */
		public function show_delivery_order_details_after_order_table( $order ) {

			$this->show_delivery_order_details( $order );
		}

		/**
		 * Add delivery details in woocommerce email
		 *
		 * @param WC_Order $order The order.
		 * @param bool     $sent_to_admin Sent to admin.
		 * @param bool     $plain_text Is plain text.
		 * @param WC_Email $email The email.
		 *
		 * @throws Exception The exception.
		 * @since  1.0.0
		 */
		public function print_delivery_date_into_email( $order, $sent_to_admin, $plain_text = false, $email = false ) {

			$this->show_delivery_order_details( $order, $sent_to_admin );
		}


		/**
		 * Add/remove even to calendar
		 *
		 * @param int    $order_id The order id.
		 * @param string $old_status The old order status.
		 * @param string $new_status The new order status.
		 *
		 * @since  1.0.0
		 *
		 */
		public function manage_order_event( $order_id, $old_status, $new_status ) {

			$order         = wc_get_order( $order_id );
			$delivery_date = $order->get_meta( 'ywcdd_order_delivery_date' );
			$shipping_date = $order->get_meta( 'ywcdd_order_shipping_date' );
			$carrier_id    = $order->get_meta( 'ywcdd_order_carrier_id' );
			$proc_id       = $order->get_meta( 'ywcdd_order_processing_method' );
			/**
			 * APPLY_FILTERS: yith_delivery_date_order_has_child
			 *
			 * Check if the order has a child or not.
			 *
			 * @param bool $has_child True or false
			 * @param int  $order_id The order id.
			 *
			 * @return bool
			 */
			$has_child = apply_filters( 'yith_delivery_date_order_has_child', false, $order_id );

			if ( ! empty( $delivery_date ) && ! $has_child ) {

				$add_event_order_status = get_option( 'ywcdd_add_event_into_calendar' );

				if ( in_array( $new_status, $add_event_order_status, true ) || 'on-hold' === $new_status ) {
					/**Add new shipping event and add new delivery event into calendar*/
					YITH_Delivery_Date_Calendar()->add_calendar_event( $proc_id, '', 'shipping_to_carrier', $shipping_date, '', $order_id );

					YITH_Delivery_Date_Calendar()->add_calendar_event( $carrier_id, '', 'delivery_day', $delivery_date, $delivery_date, $order_id );
				} else {

					YITH_Delivery_Date_Calendar()->delete_event_by_order_id( $order_id );

				}
			}
		}

		/**
		 * Add the "accept to receive the shipped email" checkbox for customer in checkout
		 *
		 */
		public function show_checkbox() {

			wc_get_template( 'send-mail-checkbox.php', array(), '', YITH_DELIVERY_DATE_TEMPLATE_PATH . 'woocommerce/checkout/' );
		}

		/**
		 * Save the customer choose
		 *
		 * @param WC_Order $order The order.
		 *
		 */
		public function register_customer_choose( $order ) {

			if ( ! isset( $_REQUEST['ywcdd_send_email'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				$order->update_meta_data( '_ywcdd_not_send', 'yes' );
			}
		}


	}
}
if ( ! function_exists( 'YITH_Delivery_Date_Shipping_Manager' ) ) {
	/**
	 * Return the instance
	 *
	 * @return YITH_Delivery_Date_Shipping_Manager
	 * @since 1.0.0
	 */
	function YITH_Delivery_Date_Shipping_Manager() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
		$option = get_option( 'ywcdd_processing_type', 'checkout' );
		if ( 'checkout' === $option ) {
			return YITH_Delivery_Date_Shipping_Manager::get_instance();
		}
	}
}

YITH_Delivery_Date_Shipping_Manager();
