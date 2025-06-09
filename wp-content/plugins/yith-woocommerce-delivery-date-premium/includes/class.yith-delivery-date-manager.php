<?php // phpcs:ignore WordPress.Files.FileName
/**
 * The class that manage the delivery dates
 *
 * @package YITH WooCommerce Delivery Date\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Delivery_Date_Manager' ) ) {
	/**
	 * Class YITH_Delivery_Date_Manager
	 */
	class YITH_Delivery_Date_Manager {

		/**
		 * The instance of the class
		 *
		 * @var YITH_Delivery_Date_Manager
		 */
		protected static $_instance; // phpcs:ignore

		/**
		 * YITH_Delivery_Date_Manager constructor.
		 */
		public function __construct() {
			add_action( 'woocommerce_checkout_update_order_review', array( $this, 'set_timeslot_session' ) );
			add_action( 'woocommerce_cart_calculate_fees', array( $this, 'add_timeslot_fee' ), 10 );
		}

		/**
		 * Create or return the instance of the class
		 *
		 * @return YITH_Delivery_Date_Manager
		 * @since 2.0.0
		 * @author YITH <plugins@yithemes.com>
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}


		/**
		 * Return the first shipping date for a processing method
		 *
		 * @param int   $id The processing method id.
		 * @param array $args The extra args.
		 *
		 * @return int
		 * @since 2.0
		 *
		 */
		public function get_first_shipping_date( $id, $args = array() ) {

			$default_args = array(
				'start_date'      => current_time( 'Y-m-d H:i:s' ),
				'min_working_day' => YITH_Delivery_Date_Processing_Method()->get_min_working_day( $id ),
				'work_days'       => YITH_Delivery_Date_Processing_Method()->get_work_days( $id ),
				'today'           => current_time( 'Y-m-d H:i:s' ),
			);

			$args                = wp_parse_args( $args, $default_args );
			$first_shipping_date = false;

			$start_timestamp   = strtotime( $args['start_date'] );
			$today             = strtotime( $args['today'] );
			$all_days          = array_keys( yith_get_worksday( false ) );
			$wday              = strtolower( date( 'D', $start_timestamp ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
			$i                 = array_search( $wday, $all_days, true );
			$days_for_shipping = 0;
			$min_workdays      = $args['min_working_day'];

			$max_execution = 1000;
			if ( count( $args['work_days'] ) > 0 ) {
				do {

					$current_work_day = isset( $args['work_days'][ $wday ] ) ? $args['work_days'][ $wday ] : false;
					if ( $current_work_day && 'yes' === $current_work_day['enabled'] ) {
						$timestamp = strtotime( "{$days_for_shipping} days", $start_timestamp );

						$is_holiday = YITH_Delivery_Date_Calendar()->is_holiday( $id, $timestamp );

						if ( ! $is_holiday ) {

							if ( 0 === $days_for_shipping && $today === $timestamp ) {

								if ( ! empty( $current_work_day['timelimit'] ) ) {
									$timestamp_limit = strtotime( current_time( 'Y-m-d' ) . " {$current_work_day['timelimit']}" );
									if ( current_time( 'timestamp' ) < $timestamp_limit ) { // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp
										$first_shipping_date = $today;
										$min_workdays --;
									}
								} else {
									$first_shipping_date = $today;
									$min_workdays --;
								}
							} else {
								$first_shipping_date = $timestamp;
								$min_workdays --;
							}
						}
					}
					$days_for_shipping ++;
					$i    = ( $i + 1 ) % 7; // phpcs:ignore
					$wday = $all_days[ $i ];

				} while ( $min_workdays >= 0 && $max_execution > 0 );
			}

			/**
			 * APPLY_FILTERS: ywcdd_first_shipping_date
			 *
			 * This filter allow to change the first shipping date.
			 *
			 * @param int $first_shipping_date The date in timestamp.
			 *
			 * @return int
			 */
			return apply_filters( 'ywcdd_first_shipping_date', $first_shipping_date );
		}

		/**
		 * Return the first delivery date for a carrier
		 *
		 * @param int   $id Carrier id.
		 * @param array $args Extra args.
		 *
		 * @return int
		 * @since 2.0
		 *
		 */
		public function get_first_delivery_date( $id, $args = array() ) {
			$default_args = array(
				'shipping_date'   => current_time( 'Y-m-d H:i:s' ),
				'min_working_day' => YITH_Delivery_Date_Carrier()->get_min_working_day( $id ),
				'work_days'       => YITH_Delivery_Date_Carrier()->get_work_days( $id ),
				'max_range'       => YITH_Delivery_Date_Carrier()->get_max_range( $id ),
				'today'           => current_time( 'Y-m-d H:i:s' ),
			);

			$args                = wp_parse_args( $args, $default_args );
			$first_delivery_date = false;
			$start_timestamp     = is_string( $args['shipping_date'] ) ? strtotime( $args['shipping_date'] ) : $args['shipping_date'];
			$today               = strtotime( $args['today'] );
			$wday                = strtolower( date( 'D', $start_timestamp ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
			$all_day             = array_keys( yith_get_worksday( false ) );
			$i                   = array_search( $wday, $all_day, true );
			$min_workdays        = $args['min_working_day'];
			$end_by              = strtotime( ( $args['max_range'] + 1 ) . ' days', $start_timestamp );
			/**
			 * APPLY_FILTERS: ywcdd_days_for_delivery
			 *
			 * This filter allow to change the amout of day to delivery.
			 *
			 * @param int $days_for_delivery The days for delivery.
			 *
			 * @return int
			 */
			$days_for_delivery = apply_filters( 'ywcdd_days_for_delivery', ceil( ( $start_timestamp - $today ) / DAY_IN_SECONDS ), $args, $id );

			$has_time_slot = YITH_Delivery_Date_Carrier()->get_enabled_time_slots( $id );
			$has_time_slot = ! ( empty( $has_time_slot ) );
			do {
				$timestamp = strtotime( "{$days_for_delivery} days", $today );

				if ( isset( $args['work_days'][ $wday ] ) || ( $start_timestamp === $timestamp && 0 < $min_workdays ) ) {

					$is_holiday = YITH_Delivery_Date_Calendar()->is_holiday( $id, $timestamp );

					$day_have_slots = $this->get_available_time_slots( $id, $timestamp );

					if ( ! $is_holiday && ( ! $has_time_slot || count( $day_have_slots ) > 0 ) ) {

						if ( $min_workdays <= 0 ) {

							$first_delivery_date = $timestamp;

						}
						$min_workdays --;
					}
				}

				$days_for_delivery ++;
				$i    = ( $i + 1 ) % 7; // phpcs:ignore
				$wday = $all_day[ $i ];

			} while ( ! $first_delivery_date && ( $min_workdays >= 0 || $timestamp < $end_by ) );

			$first_delivery_date = $first_delivery_date ? $first_delivery_date : $end_by;

			/**
			 * APPLY_FILTERS: ywcdd_first_delivery_date
			 *
			 * This filter allow to change the first delivery date.
			 *
			 * @param int $first_delivery_date The first delivery date in timestamp.
			 *
			 * @return int
			 */
			return apply_filters( 'ywcdd_first_delivery_date', $first_delivery_date );
		}

		/**
		 * Return all delivery date ( array of timestamp )
		 *
		 * @param int   $id Carrier id.
		 * @param array $args Extra args.
		 *
		 * @return array
		 * @since 2.0.0
		 *
		 */
		public function get_all_delivery_dates( $id, $args = array() ) {
			$default_args = array(
				'from_date' => current_time( 'Y-m-d H:i:s' ),
				'work_days' => YITH_Delivery_Date_Carrier()->get_work_days( $id ),
				'max_range' => YITH_Delivery_Date_Carrier()->get_max_range( $id ),
			);

			$args             = wp_parse_args( $args, $default_args );
			$from_date        = is_string( $args['from_date'] ) ? strtotime( $args['from_date'] ) : $args['from_date'];
			$start_time_stamp = $from_date + DAY_IN_SECONDS;
			$delivery_dates   = array( $from_date );

			$has_time_slot = YITH_Delivery_Date_Carrier()->get_enabled_time_slots( $id );
			$has_time_slot = ! ( empty( $has_time_slot ) );
			$count_days    = 1;
			while ( $count_days < $args['max_range'] ) {

				$day1           = strtolower( date( 'D', $start_time_stamp ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
				$is_work_day    = in_array( $day1, $args['work_days'], true );
				$is_holiday     = YITH_Delivery_Date_Calendar()->is_holiday( $id, $start_time_stamp );
				$day_have_slots = $has_time_slot ? $this->get_available_time_slots( $id, $start_time_stamp ) : array();

				if ( $is_work_day && ! $is_holiday && ( ! $has_time_slot || count( $day_have_slots ) > 0 ) ) {

					$delivery_dates[] = $start_time_stamp;
					$count_days ++;
				}
				$start_time_stamp += DAY_IN_SECONDS;

			}
			/**
			 * APPLY_FILTERS: ywcdd_add_custom_delivery_dates
			 *
			 * This filter allow to add/remove delivery dates. This array will be show in checkout page.
			 *
			 * @param array $delivery_dates The  delivery dates array in timestamp.
			 * @param int   $id The carrier id.
			 *
			 * @return array
			 */
			$delivery_dates = apply_filters( 'ywcdd_add_custom_delivery_dates', array_unique( $delivery_dates ), $id );
			asort( $delivery_dates );

			return $delivery_dates;

		}

		/**
		 * Get the last useful shipping date
		 *
		 * @param int|string $date The delivery date.
		 * @param int        $processing_id The processing id.
		 * @param int        $carrier_id The carrier id.
		 * @param array      $args Extra args.
		 *
		 * @return int
		 */
		public function get_last_shipping_date( $date, $processing_id, $carrier_id, $args = array() ) {

			if ( ! is_numeric( $date ) ) {
				$date = strtotime( $date );
			}

			$default_args = array(
				'processing_min_working_day' => YITH_Delivery_Date_Processing_Method()->get_min_working_day( $processing_id ),
				'carrier_min_working_day'    => YITH_Delivery_Date_Carrier()->get_min_working_day( $carrier_id ),
			);

			$args = wp_parse_args( $args, $default_args );

			extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract

			$processing_work_days = YITH_Delivery_Date_Processing_Method()->get_work_days( $processing_id );
			$carrier_work_days    = YITH_Delivery_Date_Carrier()->get_work_days( $carrier_id );
			$has_time_slot        = YITH_Delivery_Date_Carrier()->get_enabled_time_slots( $carrier_id );
			$has_time_slot        = ! ( empty( $has_time_slot ) );
			$day_for_delivery     = 0;
			$end_by               = strtotime( current_time( 'Y-m-d H:i:s' ) );
			$last_shipping_date   = false;
			$wday                 = strtolower( date( 'D', $date ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
			$all_days             = array_keys( yith_get_worksday( false ) );
			$i                    = array_search( $wday, $all_days, true );

			do {
				$timestamp = strtotime( "{$day_for_delivery} days", $date );
				if ( $carrier_min_working_day > 0 ) {

					$is_working_day = isset( $carrier_work_days[ $wday ] );
					$is_holiday     = YITH_Delivery_Date_Calendar()->is_holiday( $carrier_id, $timestamp );
					$day_have_slots = $this->get_available_time_slots( $carrier_id, $timestamp );

					if ( $is_working_day && ! $is_holiday && ( ! $has_time_slot || count( $day_have_slots ) > 0 ) ) {
						$carrier_min_working_day --;
					}
				} elseif ( isset( $processing_work_days[ $wday ] ) && ! YITH_Delivery_Date_Calendar()->is_holiday( $processing_id, $timestamp ) ) {
					$last_shipping_date = $timestamp;
				}
				$i = ( 0 === $i ) ? 6 : $i - 1;
				$day_for_delivery --;
				$wday = $all_days[ $i ];

			} while ( ! $last_shipping_date && $timestamp > $end_by );

			$last_shipping_date = $last_shipping_date ? $last_shipping_date : strtotime( current_time( 'Y-m-d' ) );
			/**
			 * APPLY_FILTERS: ywcdd_get_last_shipping_date
			 *
			 * This filter allow to change the latest shipping date.
			 *
			 * @param int   $last_shipping_date The lastes shipping date in timestamp.
			 * @param int   $delivery_date The delivery date in timestamp.
			 * @param int   $processing_id The processing id.
			 * @param int   $carrier_id The carrier id.
			 * @param array $args The args.
			 *
			 * @return array
			 */
			$last_shipping_date = apply_filters( 'ywcdd_get_last_shipping_date', $last_shipping_date, $date, $processing_id, $carrier_id, $args );

			return $last_shipping_date;
		}

		/**
		 * Return all dates between two dates
		 *
		 * @param int $from Date from.
		 * @param int $to Date to.
		 *
		 * @return array
		 * @since 2.0.0
		 */
		public function get_date_range( $from, $to ) {

			$range = array();
			while ( $from <= $to ) {
				$range[] = $from;
				$from    += DAY_IN_SECONDS;
			}

			return $range;
		}

		/**
		 * Get available slots for a date
		 *
		 * @param int        $carrier_id The carrier id.
		 * @param int|string $date The delivery date.
		 * @param bool       $check Extra args.
		 *
		 * @return array
		 * @since 2.0.0
		 */
		public function get_available_time_slots( $carrier_id, $date, $check = false ) {

			$all_slots       = YITH_Delivery_Date_Carrier()->get_enabled_time_slots( $carrier_id );
			$available_slots = array();

			if ( count( $all_slots ) > 0 ) {

				$available_slots = $all_slots;

				if ( ! is_numeric( $date ) ) {
					$date = strtotime( $date );
				}

				$a   = HOUR_IN_SECONDS;
				$now = current_time( 'Y-m-d H:i:s' );
				/**
				 * APPLY_FILTERS: ywcdd_cut_off_time
				 *
				 * This filter allow to set a cut off time in the slot.
				 *
				 * @param int $cut_off The cut off time
				 *
				 * @return int
				 */
				$cut_off_time = apply_filters( 'ywcdd_cut_off_time', 0, $carrier_id );
				$now_time     = strtotime( $now ) + $cut_off_time;
				$wday         = strtolower( date( 'D', $date ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions

				foreach ( $all_slots as $slot_id => $slot ) {

					$time_from = strtotime( $slot['timefrom'], $date );
					$time_to   = strtotime( $slot['timeto'], $date );

					$check_time = $time_to < $now_time;
					/**
					 * APPLY_FILTERS: ywcdd_is_invalid_time_slot
					 *
					 * This filter allow force is a time slot is valid or not.
					 *
					 * @param bool   $is_valid True or false.
					 * @param array  $available_slots All slots.
					 * @param string $slot_id The slot id.
					 * @param int    $time_to The time to.
					 * @param int    $time_from The time from
					 * @param int    $now_time Now.
					 * @param int    $date The date to process.
					 * @param int    $carrier_id The carrier id.
					 *
					 * @return bool
					 */
					$check_time = $check_time || apply_filters( 'ywcdd_is_invalid_time_slot', false, $available_slots, $slot_id, $time_to, $time_from, $now_time, $date, $carrier_id );

					$day_selected = ! empty( $slot['day_selected'] ) ? $slot['day_selected'] : array();

					$check_override_day = ( isset( $slot['override_days'] ) && yith_plugin_fw_is_true( $slot['override_days'] ) && count( $day_selected ) > 0 && ! in_array( $wday, $slot['day_selected'], true ) );

					$check_lockout_order = $this->check_if_time_slot_is_lockout( $slot, $carrier_id, $date );

					if ( $check_time || $check_override_day || $check_lockout_order ) {

						unset( $available_slots[ $slot_id ] );
					}
				}
			}

			return $available_slots;
		}


		/**
		 * Check if a time slot is lockout
		 *
		 * @param array $slot The slot.
		 * @param int   $carrier_id Carrier id.
		 * @param int   $date_selected Date selected.
		 *
		 * @return bool
		 */
		public function check_if_time_slot_is_lockout( $slot, $carrier_id, $date_selected ) {

			$is_lockout = false;

			if ( ( ! empty( $slot['max_order'] ) && $slot['max_order'] > 0 ) ) {

				$result = $this->count_timeslot_order_used( $date_selected, $carrier_id );
				$key    = $slot['timefrom'] . '-' . $slot['timeto'];
				$key    = strtolower( $key );
				if ( isset( $result[ $carrier_id ][ $key ] ) && ( $slot['max_order'] - $result[ $carrier_id ][ $key ] ) <= 0 ) {

					$is_lockout = true;
				}
			}

			return $is_lockout;
		}


		/**
		 * Count the amount time slot used in the orders for a specific date
		 *
		 * @param int $date_selected The date to check.
		 * @param int $carrier_id The carrier id.
		 *
		 * @return array
		 * @since 2.0.0
		 */
		public function count_timeslot_order_used( $date_selected, $carrier_id ) {

			global $wpdb;

			$calendar_table_name = $wpdb->prefix . 'ywcdd_calendar';
			/**
			 * APPLY_FILTERS: ywcdd_order_status
			 *
			 * This filter allow add or remove order status used to count the time slot.
			 *
			 * @param array $order_status The order status.
			 *
			 * @return array
			 */
			$order_status = apply_filters(
				'ywcdd_order_status',
				array(
					'wc-pending',
					'wc-processing',
					'wc-on-hold',
					'wc-completed',
				)
			);

			$date_selected = date( 'Y-m-d', $date_selected ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
			$query         = $wpdb->prepare( "SELECT order_id FROM {$calendar_table_name} WHERE post_id = %s AND event_start = '%s'", $carrier_id, $date_selected ); // phpcs:ignore
			$order_ids     = $wpdb->get_col( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared

			$results = array();
			foreach ( $order_ids as $order_id ) {

				$order = wc_get_order( $order_id );
				if ( $order instanceof WC_Order ) {
					$timefrom = strtolower( $order->get_meta( 'ywcdd_order_slot_from' ) );
					$timeto   = strtolower( $order->get_meta( 'ywcdd_order_slot_to' ) );
					$carrier  = $order->get_meta( 'ywcdd_order_carrier_id' );
					$skip     = ( intval( $carrier_id ) !== intval( $carrier ) ) || intval( $carrier_id ) === intval( $carrier ) && ( empty( $timefrom ) || empty( $timeto ) );

					if ( ! $skip ) {

						if ( ! is_numeric( $timefrom ) && ! is_numeric( $timeto ) ) {
							$key = $timefrom . '-' . $timeto;
						} else {
							$key = date( 'H:i', $timefrom ) . '-' . date( 'H:i', $timeto ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
						}
						if ( ! isset( $results[ $carrier_id ][ $key ] ) ) {
							$results[ $carrier_id ][ $key ] = 1;
						} else {
							$results[ $carrier_id ][ $key ] = $results[ $carrier_id ][ $key ] + 1;
						}
					}
				}
			}

			return $results;
		}

		/**
		 * Save the time slot selected in the session
		 *
		 * @param array $post_data The post data.
		 *
		 * @since 2.0.0
		 */
		public function set_timeslot_session( $post_data ) {

			$args = wp_parse_args( $post_data );

			WC()->session->__unset( 'ywcdd_fee' );
			WC()->session->__unset( 'ywcdd_fee_name' );
			if ( isset( $args['ywcdd_timeslot_av'] ) && 'yes' === $args['ywcdd_timeslot_av'] ) {

				$timeslot_id   = isset( $args['ywcdd_timeslot'] ) ? $args['ywcdd_timeslot'] : '';
				$carrier_id    = isset( $args['ywcdd_carrier'] ) ? $args['ywcdd_carrier'] : - 1;
				$date_selected = isset( $args['ywcdd_delivery_date'] ) ? $args['ywcdd_delivery_date'] : current_time( 'Y-m-d' );

				if ( ! empty( $timeslot_id ) ) {
					$all_slots     = $this->get_available_time_slots( $carrier_id, $date_selected );
					$selected_slot = ( isset( $all_slots[ $timeslot_id ] ) ) ? $all_slots[ $timeslot_id ] : false;

					if ( $selected_slot ) {

						$fee      = wc_format_decimal( $selected_slot['fee'] );
						$fee_name = ! empty( $selected_slot['fee_name'] ) ? $selected_slot['fee_name'] : get_option( 'ywcdd_fee_label', 'Time Slot Fee' );
					} else {
						$fee      = 0;
						$fee_name = '';
					}

					if ( $fee > 0 ) {
						WC()->session->set( 'ywcdd_fee', $fee );
						WC()->session->set( 'ywcdd_fee_name', $fee_name );
					}
				}
			}
		}

		/**
		 * Add timeslot fee
		 *
		 * @since 1.0.0
		 */
		public function add_timeslot_fee() {

			if ( WC()->session->get( 'ywcdd_fee' ) ) {

				$time_slot_fee = WC()->session->get( 'ywcdd_fee_name' );
				/**
				 * APPLY_FILTERS: ywcdd_time_slot_fee_text
				 *
				 * The filter allow to change the fee name.
				 *
				 * @param string $time_slot_fee the time slot fee name.
				 *
				 * @return string
				 */
				$time_slot_fee = apply_filters( 'ywcdd_time_slot_fee_text', $time_slot_fee );
				$is_taxable    = 'yes' === get_option( 'ywcdd_fee_is_taxable', 'no' );
				/**
				 * APPLY_FILTERS: ywcdd_time_slot_fee_taxable
				 *
				 * The filter allow to force if a fee is taxable or not..
				 *
				 * @param bool $is_taxable True or false.
				 *
				 * @return bool
				 */
				$is_taxable = apply_filters( 'ywcdd_time_slot_fee_taxable', $is_taxable );
				$tax_class  = '';

				if ( $is_taxable ) {

					$tax_class = get_option( 'ywcdd_fee_tax_class', '' );
					/**
					 * APPLY_FILTERS: ywcdd_fee_tax_class
					 *
					 * The filter allow to change the tax class.
					 *
					 * @param string $tax_class The tax class.
					 *
					 * @return string
					 */
					$tax_class = apply_filters( 'ywcdd_fee_tax_class', $tax_class );
				}

				WC()->cart->add_fee( $time_slot_fee, WC()->session->get( 'ywcdd_fee' ), $is_taxable, $tax_class );
			}
		}
	}

}

/**
 * Return the instance
 *
 * @return YITH_Delivery_Date_Manager
 * @since 2.0.0
 */
function YITH_Delivery_Date_Manager() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
	return YITH_Delivery_Date_Manager::get_instance();
}

YITH_Delivery_Date_Manager();
