<?php
/**
 * The file contain all plugin functions.
 *
 * @package YITH WooCommerce Delivery Date\Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'yith_get_worksday' ) ) {
	/**
	 * Return the days of the week , localized or not
	 *
	 * @param bool $localized If true return the days localized.
	 *
	 * @return array
	 * @author YITH <plugins@yithemes.com>
	 * @since 1.0.0
	 */
	function yith_get_worksday( $localized = true ) {
		if ( $localized ) {
			$days = array(
				'sun' => __( 'Sunday', 'yith-woocommerce-delivery-date' ),
				'mon' => __( 'Monday', 'yith-woocommerce-delivery-date' ),
				'tue' => __( 'Tuesday', 'yith-woocommerce-delivery-date' ),
				'wed' => __( 'Wednesday', 'yith-woocommerce-delivery-date' ),
				'thu' => __( 'Thursday', 'yith-woocommerce-delivery-date' ),
				'fri' => __( 'Friday', 'yith-woocommerce-delivery-date' ),
				'sat' => __( 'Saturday', 'yith-woocommerce-delivery-date' ),
			);
		} else {
			$days = array(
				'sun' => 'Sunday',
				'mon' => 'Monday',
				'tue' => 'Tuesday',
				'wed' => 'Wednesday',
				'thu' => 'Thursday',
				'fri' => 'Friday',
				'sat' => 'Saturday',
			);
		}

		return $days;
	}
}

if ( ! function_exists( 'yith_get_month' ) ) {
	/**
	 *
	 * Return a month if exist
	 *
	 * @param string $abbr The month.
	 *
	 * @return string|bool
	 * @since 1.0.0
	 */
	function yith_get_month( $abbr ) {

		$abbr   = strtolower( $abbr );
		$months = array(
			'jan'  => _x( 'January', 'month', 'yith-woocommerce-delivery-date' ),
			'feb'  => _x( 'February', 'month', 'yith-woocommerce-delivery-date' ),
			'mar'  => _x( 'March', 'month', 'yith-woocommerce-delivery-date' ),
			'apr'  => _x( 'April', 'month', 'yith-woocommerce-delivery-date' ),
			'may'  => _x( 'May', 'month', 'yith-woocommerce-delivery-date' ),
			'jun'  => _x( 'June', 'month', 'yith-woocommerce-delivery-date' ),
			'jul'  => _x( 'July', 'month', 'yith-woocommerce-delivery-date' ),
			'aug'  => _x( 'August ', 'month', 'yith-woocommerce-delivery-date' ),
			'sep'  => _x( 'September ', 'month', 'yith-woocommerce-delivery-date' ),
			'sept' => _x( 'September ', 'month', 'yith-woocommerce-delivery-date' ),
			'oct'  => _x( 'October ', 'month', 'yith-woocommerce-delivery-date' ),
			'nov'  => _x( 'November ', 'month', 'yith-woocommerce-delivery-date' ),
			'dec'  => _x( 'December ', 'month', 'yith-woocommerce-delivery-date' ),
		);

		return isset( $months[ $abbr ] ) ? $months[ $abbr ] : false;
	}
}
if ( ! function_exists( 'ywcdd_search_product_category' ) ) {

	/**
	 * Search product by category
	 *
	 * @since 1.0.0
	 */
	function ywcdd_search_product_category() {
		global $wpdb;
		check_ajax_referer( 'search-products', 'security' );

		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			die( - 1 );
		}

		$term = isset( $_GET['term'] ) ? wc_clean( stripslashes( wp_unslash( $_GET['term'] ) ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		$term = '%' . $term . '%';

		$query_cat = $wpdb->prepare(
			"SELECT {$wpdb->terms}.term_id,{$wpdb->terms}.name, {$wpdb->terms}.slug
                                   FROM {$wpdb->terms} INNER JOIN {$wpdb->term_taxonomy} ON {$wpdb->terms}.term_id = {$wpdb->term_taxonomy}.term_id
                                   WHERE {$wpdb->term_taxonomy}.taxonomy IN (%s) AND {$wpdb->terms}.name LIKE %s",
			'product_cat',
			$term
		);

		$product_categories = $wpdb->get_results( $query_cat );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared

		$to_json = array();

		foreach ( $product_categories as $product_category ) {

			$to_json[ $product_category->term_id ] = '#' . $product_category->term_id . '-' . $product_category->name;
		}

		wp_send_json( $to_json );
	}
}

if ( ! function_exists( 'ywcdd_get_date_by_format' ) ) {

	/**
	 * Format the date
	 *
	 * @param string|int $date The date.
	 * @param string $format The format.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function ywcdd_get_date_by_format( $date, $format = '' ) {

		$format = empty( $format ) ? get_option( 'date_format' ) : $format;

		if ( is_string( $date ) ) {
			$time = strtotime( $date );
		} else {
			$time = $date;
		}
		$new_date = date_i18n( $format, $time );

		return $new_date;

	}
}

if ( ! function_exists( 'ywcdd_get_date_mysql' ) ) {
	/**
	 * Return the date in mysql format
	 *
	 * @param int $date the date.
	 *
	 * @return false|int|string
	 * @since 1.0.0
	 */
	function ywcdd_get_date_mysql( $date ) {

		return mysql2date( __( 'Y/m/d' ), $date, false );
	}
}

add_action( 'wp_ajax_ywcdd_search_product_category', 'ywcdd_search_product_category' );

if ( ! function_exists( 'ywcdd_get_delivery_mode' ) ) {
	/**
	 * Get delivery mode
	 *
	 * @return string
	 * @since 1.0.5
	 */
	function ywcdd_get_delivery_mode() {
		$option = get_option( 'ywcdd_delivery_mode', 'no' );
		/**
		 * APPLY_FILTERS: ywcdd_open_datepicker
		 *
		 * The filter allow to force open the datepicker in checkout.
		 *
		 * @param string       $option Yes or no.
		 *
		 * @return string
		 */
		return apply_filters( 'ywcdd_open_datepicker', $option );
	}
}

if ( ! function_exists( 'ywcdd_display_timeslot' ) ) {
	/**
	 * Return the format time slot
	 *
	 * @param int $timeslot The time slot.
	 *
	 * @return false|mixed|string
	 * @since 1.0.0
	 */
	function ywcdd_display_timeslot( $timeslot ) {

		if ( is_numeric( $timeslot ) ) {

			/**
			 * APPLY_FILTERS: ywcdd_timeslot_format
			 *
			 * The filter allow to change the time slot format.
			 *
			 * @param string       $time_format The time format.
			 *
			 * @return string
			 */
			$time_format = apply_filters( 'ywcdd_timeslot_format', get_option( 'time_format' ) );
			$timeslot    = date( $time_format, $timeslot ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
		}

		return $timeslot;
	}
}

if ( ! function_exists( 'ywcdd_get_delivery_info' ) ) {

	/**
	 * Get the delivery info by order
	 *
	 * @param WC_Order $order The order.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	function ywcdd_get_delivery_info( $order ) {

		ob_start();
		wc_get_template( 'woocommerce/pdf/delivery-date-info.php', array( 'order' => $order ), YITH_DELIVERY_DATE_TEMPLATE_PATH, YITH_DELIVERY_DATE_TEMPLATE_PATH );
		$delivery_info = ob_get_contents();
		ob_end_clean();

		return $delivery_info;
	}
}


add_action( 'yith_ywpi_delivery_date_label', 'ywpi_print_delivery_notes', 10 );
/**
 * Show the delivery info in the PDF template ( YITH PDF INVOICE PLUGIN )
 *
 * @param YITH_Document $document The document.
 *
 * @since 1.0.0
 */
function ywpi_print_delivery_notes( $document ) {

	$print_notes = get_option( 'ywpi_show_delivery_info' );

	if ( 'yes' === $print_notes ) {
		$notes = ywcdd_get_delivery_info( $document->order );

		echo $notes; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
	}
}

/**
 * Get all available date format
 *
 * @return array
 * @since 2.0.0
 */
function yith_get_delivery_date_format() {

	$date_formats = array(
		'mm/dd/y'      => 'm/d/y',
		'dd/mm/y'      => 'd/m/y',
		'y/mm/dd'      => 'y/m/d',
		'dd.mm.y'      => 'd.m.y',
		'y.mm.dd'      => 'y.m.d',
		'yy-mm-dd'     => 'Y-m-d',
		'dd-mm-y'      => 'd-m-y',
		'd M, y'       => 'j M, y',
		'd M, yy'      => 'j M, Y',
		'd MM, y'      => 'j F, y',
		'd MM, yy'     => 'j F, Y',
		'DD, d MM, yy' => 'l, j F, Y',
		'D, M d, yy'   => 'D, M j, Y',
		'DD, M d, yy'  => 'l, M j, Y',
		'DD, MM d, yy' => 'l, F j, Y',
		'D, MM d, yy'  => 'D, F j, Y',
	);

	return $date_formats;
}


if ( ! function_exists( 'yith_delivery_date_get_tax_options' ) ) {

	/**
	 * Return all available taxes
	 *
	 * @return array
	 */
	function yith_delivery_date_get_tax_options() {

		$tax_options = array();
		if ( class_exists( 'WC_Tax' ) ) {
			$tax_classes = WC_Tax::get_tax_classes();
			foreach ( $tax_classes as $tax_class ) {
				$tax_options[ $tax_class ] = $tax_class;
			}
		}

		return $tax_options;
	}
}

// UPDATE DB TO VERSION 2.0.0.
if ( ! function_exists( 'ywcdd_update_db_2_0' ) ) {
	/**
	 * Update db to new version
	 *
	 * @since 2.0.0
	 */
	function ywcdd_update_db_2_0() {

		$old_carrier_system = get_option( 'yith_delivery_date_enable_carrier_system', '' );
		$db_version         = get_option( 'ywcdd_db_version', '1.0.0' );

		if ( ! empty( $old_carrier_system ) && 'no' === $old_carrier_system && version_compare( $db_version, '2.0.0', '<' ) ) {

			$args = array(
				'post_type'      => 'yith_proc_method',
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
				'fields'         => 'ids',
			);

			$processing_ids = get_posts( $args );
			$default_id     = array( ywcdd_create_default_carrier() );

			foreach ( $processing_ids as $key => $processing_id ) {
				/**
				 * The processing id
				 *
				 * @var int $processing_id
				 */
				update_post_meta( $processing_id, '_ywcdd_carrier', $default_id );
				update_post_meta( $processing_id, '_ywcdd_type_checkout', 'yes' );
			}

			update_option( 'ywcdd_db_version', '2.0.0' );
		}
	}

	add_action( 'admin_init', 'ywcdd_update_db_2_0', 20 );
}

if ( ! function_exists( 'ywcdd_create_default_carrier' ) ) {
	/**
	 * Create the default carrier if not exist
	 *
	 * @return int
	 * @since 2.0.0
	 */
	function ywcdd_create_default_carrier() {

		$default_carrier_id = get_option( 'ywcdd_default_carrier_id', - 1 );
		$post               = intval( get_post( $default_carrier_id ) );
		if ( - 1 === $default_carrier_id || is_null( $post ) ) {
			$args = array(
				'post_author'  => get_current_user_id(),
				'post_type'    => 'yith_carrier',
				'post_status'  => 'publish',
				'post_title'   => 'Default',
				'post_content' => '',
			);

			$carrier_id = wp_insert_post( $args );

			$num_work_day = get_option( 'yith_delivery_date_range_day' );
			$works_day    = get_option( 'yith_delivery_date_workday' );
			$max_range    = get_option( 'yith_delivery_date_max_range' );
			$time_slots   = get_option( 'yith_delivery_date_time_slot' );

			update_post_meta( $carrier_id, '_ywcdd_dayrange', $num_work_day );
			update_post_meta( $carrier_id, '_ywcdd_workday', $works_day );
			update_post_meta( $carrier_id, '_ywcdd_max_selec_orders', $max_range );
			update_post_meta( $carrier_id, '_ywcdd_addtimeslot', $time_slots );

			$default_carrier_id = $carrier_id;
			update_option( 'ywcdd_default_carrier_id', $carrier_id );

			global $wpdb;

			$args       = array(
				'post_type' => 'yith_carrier',
				'post_id'   => $default_carrier_id,
			);
			$where_args = array(
				'post_type' => 'carrier_default',
				'post_id'   => - 1,
			);

			$wpdb->update( $wpdb->prefix . 'ywcdd_calendar', $args, $where_args );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared
		}

		return $default_carrier_id;
	}
}

if ( ! function_exists( 'ywcdd_get_order_status' ) ) {
	/**
	 * Get the woocommerce order status
	 *
	 * @return array
	 * @since 1.0.0
	 */
	function ywcdd_get_order_status() {
		$order_status     = wc_get_order_statuses();
		$new_order_status = array();
		foreach ( $order_status as $key => $status ) {
			$key                      = 'wc-' === substr( $key, 0, 3 ) ? substr( $key, 3 ) : $key;
			$new_order_status[ $key ] = $status;
		}

		return $new_order_status;
	}
}


// UPDATE DB TO VERSION 2.0.2.
add_action( 'admin_init', 'ywcdd_update_db_2_0_2', 25 );
/**
 * Update the db to version 2.0.2
 *
 * @since 2.0.2
 */
function ywcdd_update_db_2_0_2() {

	$args = array(
		'post_type'      => 'yith_proc_method',
		'post_status'    => 'publish',
		'posts_per_page' => - 1,
		'fields'         => 'ids',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
			array(
				'key'     => '_ywcdd_type_checkout',
				'compare' => 'NOT EXISTS',
			),
		),
	);

	$db_version = get_option( 'ywcdd_db_version', '1.0.0' );

	if ( version_compare( $db_version, '2.0.2', '<' ) ) {

		$posts = get_posts( $args );

		foreach ( $posts as $post_id ) {
			update_post_meta( $post_id, '_ywcdd_type_checkout', 'yes' );
		}

		update_option( 'ywcdd_db_version', '2.0.2' );
	}

}

if ( ! function_exists( 'yith_delivery_date_get_disabled_checkout_option_message' ) ) {
	/**
	 * Add a notice in the options if the checkout option is disabled
	 *
	 * @return string
	 * @since 2.0.0
	 */
	function yith_delivery_date_get_disabled_checkout_option_message() {

		$message = sprintf( '<span class="ywcdd_option_message_disabled">%s</span>', __( 'These options are disabled because the plugin is in "Product quantity table mode"', 'yith-woocommerce-delivery-date' ) );

		return $message;
	}
}

if ( ! function_exists( 'yith_delivery_date_get_shipping_zones' ) ) {
	/**
	 * Get all shipping zones
	 *
	 * @return array
	 * @since 2.0.0
	 */
	function yith_delivery_date_get_shipping_zones() {
		$shipping_zones = WC_Shipping_Zones::get_zones();
		$global_zone    = new WC_Shipping_Zone( 0 );

		$all_zones = array();

		foreach ( $shipping_zones as $zone ) {

			$all_zones[ $zone['zone_id'] ] = $zone['zone_name'];
		}

		$all_zones[ $global_zone->get_id() ] = $global_zone->get_zone_name();

		return $all_zones;
	}
}

if ( ! function_exists( 'yith_delivery_date_get_customer_zone' ) ) {
	/**
	 * Return the zone of current customer
	 *
	 * @return int|string
	 * @since 2.0.0
	 */
	function yith_delivery_date_get_customer_zone() {

		$zone_id = 'all';
		if ( WC()->customer ) {
			$customer_packing = array(
				'destination' => array(
					'country'  => WC()->customer->get_shipping_country(),
					'state'    => WC()->customer->get_shipping_state(),
					'postcode' => WC()->customer->get_shipping_postcode(),
				),
			);

			$zone1 = WC_Shipping_Zones::get_zone_matching_package( $customer_packing );

			$zone_id = intval( $zone1->get_id() );
		}

		return $zone_id;

	}
}

if ( ! function_exists( 'yith_delivery_date_column_is_disabled' ) ) {

	/**
	 * Check if a column is disabled in quantity product table
	 *
	 * @param array $quantity_table The quantity table args.
	 * @param int $column The column to check.
	 *
	 * @return bool
	 * @since 2.0.0
	 */
	function yith_delivery_date_column_is_disabled( $quantity_table, $column = 0 ) {

		$is_disabled = true;

		$days       = wp_list_pluck( $quantity_table, 'days' );
		$column_day = wp_list_pluck( $days, $column );
		foreach ( $column_day as $column ) {

			if ( 'yes' === $column['enabled'] ) {
				return false;
			}
		}

		return $is_disabled;
	}
}
