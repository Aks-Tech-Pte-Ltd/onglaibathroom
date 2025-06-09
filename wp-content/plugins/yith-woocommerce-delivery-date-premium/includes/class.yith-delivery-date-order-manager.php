<?php // phpcs:ignore WordPress.Files.FileName

/**
 * This class add delivery date in the order meta
 *
 * @package YITH WooCommerce Delivery Date\Classes
 */

use Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableDataStore;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Delivery_Date_Order_Manager' ) ) {

	/**
	 * Class YITH_Delivery_Date_Order_Manager
	 */
	class YITH_Delivery_Date_Order_Manager {

		/**
		 * The unique instance of the class
		 *
		 * @var YITH_Delivery_Date_Order_Manager
		 */
		protected static $_instance; // phpcs:ignore

		/**
		 * YITH_Delivery_Date_Order_Manager constructor.
		 */
		public function __construct() {
			add_action( 'add_meta_boxes', array( $this, 'add_order_delivery_date_meta_boxes' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'include_scripts' ) );
			add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_order_meta' ), 99 );

			add_filter( 'ywcdd_send_email', array( $this, 'can_send_email' ), 10, 2 );

			if ( is_admin() ) {
				$has_hpos = get_option( 'woocommerce_custom_orders_table_enabled', 'no' );
				if ( 'yes' === $has_hpos ) {
					add_action(
						'woocommerce_order_list_table_restrict_manage_orders',
						array(
							$this,
							'restrict_manage_orders',
						)
					);
					add_filter(
						'woocommerce_order_list_table_prepare_items_query_args',
						array(
							$this,
							'add_order_query_args',
						)
					);
					add_filter(
						'manage_woocommerce_page_wc-orders_custom_column',
						array(
							$this,
							'custom_columns',
						),
						20,
						2
					);
					add_filter( 'manage_woocommerce_page_wc-orders_columns', array( $this, 'edit_columns' ) );
					add_filter(
						'woocommerce_shop_order_list_table_sortable_columns',
						array(
							$this,
							'edit_sortable_columns',
						)
					);
				} else {
					add_filter( 'manage_edit-shop_order_columns', array( $this, 'edit_columns' ) );
					add_filter( 'manage_edit-shop_order_sortable_columns', array( $this, 'edit_sortable_columns' ) );
					add_action( 'manage_shop_order_posts_custom_column', array( $this, 'custom_columns' ) );
					add_filter( 'request', array( $this, 'request_query' ), 25 );
					add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_posts' ), 15 );
				}
			}

			add_action( 'wp_ajax_update_order_details', array( $this, 'update_order_details' ) );

		}

		/**
		 * Create or return instance
		 *
		 * @return YITH_Delivery_Date_Order_Manager
		 * @since 1.0.0
		 * @author YITH <plugins@yithemes.com>
		 */
		public static function get_instance() {

			if ( is_null( self::$_instance ) ) {

				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Add meta box in the order post type
		 *
		 * @since 1.0.0
		 */
		public function add_order_delivery_date_meta_boxes( $post_type ) {
			if ( in_array( $post_type, array( wc_get_page_screen_id( 'shop-order' ), 'shop-order' ), true ) ) {
				add_meta_box(
					'yith-wc-order-delivery-date-metabox',
					__( 'Delivery Details', 'yith-woocommerce-delivery-date' ),
					array(
						$this,
						'order_delivery_date_meta_box_content',
					),
					$post_type,
					'side',
					'core'
				);
			}
		}

		/**
		 * Include the file for add the order meta box
		 *
		 * @since 1.0.0
		 */
		public function order_delivery_date_meta_box_content() {

			wc_get_template( 'meta-boxes/order-delivery-details-meta-box.php', array(), YITH_DELIVERY_DATE_TEMPLATE_PATH, YITH_DELIVERY_DATE_TEMPLATE_PATH );
		}

		/**
		 * Save the delivery info in the order meta
		 *
		 * @param int $post_id The order id.
		 *
		 * @since 1.0.0
		 *
		 */
		public function save_order_meta( $post_id ) {

			$post_type = get_post_type( $post_id );

			if ( 'shop_order' === $post_type ) {

				$order = wc_get_order( $post_id );

				if ( isset( $_POST['ywcdd_order_shipped'] ) ) { // phpcs:ignore
					$shipping_date = $order->get_meta( 'ywcdd_order_shipping_date' );

					if ( ! empty( $shipping_date ) ) {
						$shipped = isset( $_POST['ywcdd_order_shipped'] ) ? 'yes' : 'no'; // phpcs:ignore

						$order->update_meta_data( 'ywcdd_order_shipped', $shipped );
						$order->save();

						$email_is_sent = $order->get_meta( '_ywcdd_email_sent' );
						/**
						 * APPLY_FILTERS: ywcdd_send_email
						 *
						 * The filter allow to disable the email to send.
						 *
						 * @param bool $enable True or false.
						 * @param WC_Order $order The order.
						 *
						 * @return string
						 */
						if ( 'yes' === $shipped && empty( $email_is_sent ) && apply_filters( 'ywcdd_send_email', true, $order ) ) {

							WC()->mailer();
							/**
							 * DO_ACTION: yith_advise_user_delivery_email_notification
							 *
							 * This action is triggered to send the email notification.
							 *
							 * @param WC_Order $order the order.
							 */
							do_action( 'yith_advise_user_delivery_email_notification', $order );
						}
						/**
						 * DO_ACTION: yith_delivery_date_suborders_shipped
						 *
						 * This action is triggered to send the email notification in suborders.
						 *
						 * @param int $post_id The post id.
						 * @param string $shipped Yes or not.
						 */
						do_action( 'yith_delivery_date_suborders_shipped', $post_id, $shipped );
					}
				}

				if ( isset( $_POST['ywcdd_edit_processing_method'] ) ) { // phpcs:ignore

					$processing_id  = $_POST['ywcdd_edit_processing_method']; // phpcs:ignore
					$carrier_id     = $_POST['ywcdd_edit_carrier']; // phpcs:ignore
					$shipping_date  = $_POST['ywcdd_edit_processing_date']; // phpcs:ignore
					$delivery_date  = $_POST['ywcdd_edit_delivery_date']; // phpcs:ignore
					$time_from      = $_POST['ywcdd_edit_time_from']; // phpcs:ignore
					$time_to        = $_POST['ywcdd_edit_time_to']; // phpcs:ignore
					$meta_to_update = array(
						'ywcdd_order_processing_method' => $processing_id,
						'ywcdd_order_carrier_id'        => $carrier_id,
						'ywcdd_order_shipping_date'     => $shipping_date,
						'ywcdd_order_delivery_date'     => $delivery_date,
						'ywcdd_order_slot_from'         => $time_from,
						'ywcdd_order_slot_to'           => $time_to,
						'ywcdd_order_carrier'           => get_the_title( $carrier_id ),
					);

					foreach ( $meta_to_update as $key => $value ) {
						$order->update_meta_data( $key, $value );
					}
					$order->save();
				}
			}
		}

		/**
		 * Add custom columns in the order post table
		 *
		 * @param array $columns Order table columns.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		public function edit_columns( $columns ) {

			$columns['shipping_date'] = __( 'Shipping date', 'yith-woocommerce-delivery-date' );
			$columns['delivery_date'] = __( 'Delivery date', 'yith-woocommerce-delivery-date' );

			/**
			 * APPLY_FILTERS: ywcdd_get_order_edit_columns
			 *
			 * The filter allow to edit the order column for the shipping date and delivery date.
			 *
			 * @param array $columns The columns.
			 *
			 * @return array
			 */
			return apply_filters( 'ywcdd_get_order_edit_columns', $columns );
		}

		/**
		 * Add custom sortable columns in the order table
		 *
		 * @param array $sortable_columns The sortable columns.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		public function edit_sortable_columns( $sortable_columns ) {

			$sortable_columns['shipping_date'] = 'ywcdd_order_shipping_date';
			$sortable_columns['delivery_date'] = 'ywcdd_order_delivery_date';

			/**
			 * APPLY_FILTERS: ywcdd_get_order_edit_sortable_columns
			 *
			 * The filter allow to edit the order sortable column for the shipping date and delivery date.
			 *
			 * @param array $sortable_columns The columns.
			 *
			 * @return array
			 */
			return apply_filters( 'ywcdd_get_order_edit_sortable_columns', $sortable_columns );
		}

		/**
		 * Show the content of custom columns
		 *
		 * @param string $column_name The column name.
		 * @param WC_Order $order The order.
		 *
		 * @since 1.0.0
		 */
		public function custom_columns( $column_name, $order = false ) {
			global $post;
			if ( ! $order ) {
				$order_id  = $post->ID;
				$order_obj = wc_get_order( $order_id );
			} else {
				$order_obj = $order;
			}

			if ( 'shipping_date' === $column_name ) {

				$ship_date = $this->get_more_near_date_details( $order_obj );

				$value = __( 'No shipping date', 'yith-woocommerce-delivery-date' );

				if ( ! empty( $ship_date ) ) {
					/**
					 * APPLY_FILTERS: ywcdd_custom_order_column_date_format
					 *
					 * The filter allow to change the date format to show in order.
					 *
					 * @param string $date_format The format.
					 *
					 * @return string
					 */
					$date_format = apply_filters( 'ywcdd_custom_order_column_date_format', 'Y/m/d' );
					$value       = gmdate( $date_format, strtotime( $ship_date ) );

				}

				echo $value; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
			}

			if ( 'delivery_date' === $column_name ) {
				$ship_date = $this->get_more_near_date_details( $order_obj, 'delivery' );

				$value = __( 'No delivery date', 'yith-woocommerce-delivery-date' );

				if ( ! empty( $ship_date ) ) {
					/**
					 * APPLY_FILTERS: ywcdd_custom_order_column_date_format
					 *
					 * The filter allow to change the date format to show in order.
					 *
					 * @param string $date_format The format.
					 *
					 * @return string
					 */
					$date_format = apply_filters( 'ywcdd_custom_order_column_date_format', 'Y/m/d' );
					$value       = date( $date_format, strtotime( $ship_date ) ); // phpcs:ignore

					$time_from = $order_obj->get_meta( 'ywcdd_order_slot_from' );
					$time_to   = $order_obj->get_meta( 'ywcdd_order_slot_to' );
					if ( ! empty( $time_from ) && ! empty( $time_to ) ) {

						$value = sprintf( '%s<br/><small>%s - %s</small>', $value, $time_from, $time_to );
					}
				}

				echo $value; //phpcs:ignore WordPress.Security.EscapeOutput
			}

		}

		/**
		 * Return the near date from order items
		 *
		 * @param WC_Order $order The order.
		 * @param string $return How return.
		 *
		 * @return string
		 * @since 2.0.0
		 */
		public function get_more_near_date_details( $order, $return = 'shipping' ) {

			$shipping_date = $order->get_meta( 'ywcdd_order_shipping_date' );
			$delivery_date = $order->get_meta( 'ywcdd_order_delivery_date' );

			if ( empty( $shipping_date ) ) {

				$today = current_time( 'Y-m-d' );
				$today = new WC_DateTime( $today, new DateTimeZone( 'UTC' ) );
				$min   = false;
				foreach ( $order->get_items() as $order_item ) {

					$processing_date = $order_item->get_meta( '_ywcdd_last_shipping_date' );

					if ( ! empty( $processing_date ) ) {
						$item_date = new WC_DateTime( $processing_date, new DateTimeZone( 'UTC' ) );

						if ( $item_date >= $today ) {

							$current_diff = $item_date->diff( $today )->days;

							if ( ! $min || $min > $current_diff ) {
								$min           = $current_diff;
								$shipping_date = $item_date->date( 'Y/m/d' );
								$delivery_date = $order_item->get_meta( 'ywcdd_product_delivery_date' );
							}
						}
					}
				}
			}

			return 'shipping' === $return ? $shipping_date : $delivery_date;
		}

		/**
		 * Add style and script in the order metabox
		 *
		 * @since 1.0.0
		 */
		public function include_scripts() {

			$current_screen = get_current_screen();
			if ( in_array( $current_screen->id, array( wc_get_page_screen_id( 'shop-order' ), 'shop-order' ), true ) ) {

				wp_enqueue_script( 'ywcdd_timepicker', YITH_DELIVERY_DATE_ASSETS_URL . 'js/timepicker/' . yit_load_js_file( 'jquery.timepicker.js' ), array( 'jquery' ), YITH_DELIVERY_DATE_VERSION, true );
				wp_enqueue_style( 'ywcdd_timepicker', YITH_DELIVERY_DATE_ASSETS_URL . 'css/timepicker/jquery.timepicker.css', array(), YITH_DELIVERY_DATE_VERSION );

				wp_enqueue_style( 'delivery_date_order_metabox', YITH_DELIVERY_DATE_ASSETS_URL . 'css/yith_order_metaboxes.css', array(), YITH_DELIVERY_DATE_VERSION );

				$args = array(
					'ajax_url'                   => admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
					'actions'                    => array(
						'update_order_details' => 'update_order_details',
					),
					'update_order_details_nonce' => wp_create_nonce( 'update-order-details' ),
					'timeformat'                 => 'H:i',
					'timestep'                   => get_option( 'ywcdd_timeslot_step', 30 ),
				);
				wp_register_script(
					'delivery_date_order_metabox',
					YITH_DELIVERY_DATE_ASSETS_URL . 'js/' . yit_load_js_file( 'yith_deliverydate_order_metaboxes.js' ),
					array(
						'jquery',
						'jquery-ui-datepicker',
					),
					YITH_DELIVERY_DATE_VERSION,
					true
				);

				wp_localize_script( 'delivery_date_order_metabox', 'ywcdd_order_args', $args );
				wp_enqueue_script( 'delivery_date_order_metabox' );
			}
		}

		/**
		 * Check if is possible send the shipped email
		 *
		 * @param bool $send_email Can send email value.
		 * @param WC_Order $order The order.
		 *
		 * @return bool
		 */
		public function can_send_email( $send_email, $order ) {

			if ( 'yes' === get_option( 'ywcdd_user_privacy', 'no' ) ) {

				$not_send_email = $order->get_meta( '_ywcdd_not_send' );

				if ( 'yes' === $not_send_email ) {

					$send_email = false;
				}
			}

			return $send_email;
		}


		/**
		 * Add query vars to sort the order by shipping or delivery date
		 *
		 * @param array $query_vars The query vars.
		 *
		 * @return array
		 */
		public function request_query( $query_vars ) {
			global $typenow;
			if ( 'shop_order' === $typenow ) {

				if ( isset( $query_vars['orderby'] ) ) {
					$orderby        = $query_vars['orderby'];
					$custom_orderby = array( 'ywcdd_order_shipping_date', 'ywcdd_order_delivery_date' );

					if ( in_array( $orderby, $custom_orderby, true ) ) {
						$query_vars = array_merge(
							$query_vars,
							array(
								'meta_key' => $orderby, // phpcs:ignore WordPress.DB.SlowDBQuery
							)
						);
					}
				}

				$meta_key = false;
				if ( ! empty( $_GET['ywcdd_order_shipping_date'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					$meta_key = 'ywcdd_order_shipping_date';

				} elseif ( ! empty( $_GET['ywcdd_order_delivery_date'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

					$meta_key = 'ywcdd_order_delivery_date';
				}

				if ( $meta_key && isset( $_GET[ $meta_key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					$meta_value = wp_unslash( $_GET[ $meta_key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					$month      = substr( $meta_value, 0, 4 );
					$year       = substr( $meta_value, 4 );

					$first_date = $month . '-' . $year . '-01';
					$last_date  = gmdate( 'Y-m-t', strtotime( $first_date ) );

					if ( ! isset( $query_vars['meta_query'] ) ) {
						$query_vars['meta_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery
					}

					$query_vars['meta_query'][] = array(
						'key'     => $meta_key,
						'value'   => array( $first_date, $last_date ),
						'compare' => 'BETWEEN',
						'type'    => 'DATE',
					);

				}
			}

			return $query_vars;
		}

		/**
		 * Filter the order by delivery and shipping date
		 *
		 * @param object $query_args The array.
		 *
		 * @return object
		 */
		public function add_order_query_args( $query_args ) {

			if ( isset( $_GET['ywcdd_order_shipping_date'] ) ) {
				$year_month = sanitize_text_field( wp_unslash( $_GET['ywcdd_order_shipping_date'] ) );
				if ( empty( $year_month ) || ! preg_match( '/^[0-9]{6}$/', $year_month ) ) {
					return $query_args;
				}
				$year  = (int) substr( $year_month, 0, 4 );
				$month = (int) substr( $year_month, 4, 2 );
				if ( $month < 0 || $month > 12 ) {
					return $query_args;
				}
				$first_date        = "$year-$month-01";
				$last_day_of_month = date_create( "$year-$month" )->format( 'Y-m-t' );
				if ( ! isset( $query_args['meta_query'] ) ) {
					$query_args['meta_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery
				}

				$query_args['meta_query'][] = array(
					'key'     => 'ywcdd_order_shipping_date',
					'value'   => array( $first_date, $last_day_of_month ),
					'compare' => 'BETWEEN',
					'type'    => 'DATE',
				);
			}

			if ( isset( $_GET['ywcdd_order_delivery_date'] ) ) {
				$year_month = sanitize_text_field( wp_unslash( $_GET['ywcdd_order_delivery_date'] ) );
				if ( empty( $year_month ) || ! preg_match( '/^[0-9]{6}$/', $year_month ) ) {
					return $query_args;
				}
				$year  = (int) substr( $year_month, 0, 4 );
				$month = (int) substr( $year_month, 4, 2 );

				if ( $month < 0 || $month > 12 ) {
					return $query_args;
				}

				$first_date        = $month . '-' . $year . '-01';
				$last_day_of_month = date_create( "$year-$month" )->format( 'Y-m-t' );
				if ( ! isset( $query_args['meta_query'] ) ) {
					$query_args['meta_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery
				}

				$query_args['meta_query'][] = array(
					'key'     => 'ywcdd_order_delivery_date',
					'value'   => array( $first_date, $last_day_of_month ),
					'compare' => 'BETWEEN',
					'type'    => 'DATE',
				);
			}

			$custom_orderby = array( 'ywcdd_order_shipping_date', 'ywcdd_order_delivery_date' );

			if ( isset( $_GET['orderby'] ) ) {
				$orderby = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );

				if ( in_array( $orderby, $custom_orderby, true ) ) {

					if ( in_array( $orderby, $custom_orderby, true ) ) {
						$query_args = array_merge(
							$query_args,
							array(
								'meta_key' => $orderby, // phpcs:ignore WordPress.DB.SlowDBQuery
							)
						);
					}
				}
			}

			return $query_args;
		}

		/**
		 * Check if is possible show the filters
		 *
		 * @since 2.0.0
		 */
		public function restrict_manage_posts() {
			global $typenow;
			if ( in_array( $typenow, wc_get_order_types( 'order-meta-boxes' ), true ) ) {
				$this->render_filters( $typenow, 'ywcdd_order_shipping_date' );
				$this->render_filters( $typenow, 'ywcdd_order_delivery_date' );

			}
		}

		/**
		 * Manage the custom dropdown filters
		 *
		 * @param string $order_type The order type.
		 *
		 * @return void
		 */
		public function restrict_manage_orders( $order_type ) {

			if ( 'shop_order' === $order_type ) {
				$this->render_order_filter( 'ywcdd_order_shipping_date' );
				$this->render_order_filter( 'ywcdd_order_delivery_date' );
			}
		}

		/**
		 * Render the dropdown filter
		 *
		 * @param string $type The date type to show.
		 *
		 * @return void
		 */
		public function render_order_filter( $type ) {
			global $wpdb;

			$orders_table      = esc_sql( $wpdb->prefix . 'wc_orders' );
			$orders_meta_table = esc_sql( $wpdb->prefix . 'wc_orders_meta' );

			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$order_dates = $wpdb->get_results(
				$wpdb->prepare(
					"
				SELECT DISTINCT YEAR( date_created_gmt ) AS year,
								MONTH( date_created_gmt ) AS month

				FROM $orders_table JOIN  $orders_meta_table ON $orders_table.id = $orders_meta_table.order_id

				WHERE $orders_table.status NOT IN (
					'trash'
				) AND meta_key = %s AND meta_value != ''

				ORDER BY year DESC, month DESC;
			",
					$type
				)
			);

			$this->show_dropdown_date_filter( $type, $order_dates );
		}

		/**
		 * Show the order  filters
		 *
		 * @param string $post_type The post type (shop-order).
		 * @param string $type The filter type.
		 *
		 * @since 2.0.0
		 */
		public function render_filters( $post_type, $type ) {
			global $wpdb;
			$extra_checks = "AND post_status != 'auto-draft'";
			if ( ! isset( $_GET['post_status'] ) || 'trash' !== $_GET['post_status'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$extra_checks .= " AND post_status != 'trash'";
			} elseif ( isset( $_GET['post_status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$extra_checks = $wpdb->prepare( ' AND post_status = %s', wp_unslash( $_GET['post_status'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			}
			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$order_dates = $wpdb->get_results(  // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"
			SELECT DISTINCT YEAR( meta_value ) AS year, MONTH( meta_value ) AS month
			FROM $wpdb->postmeta JOIN $wpdb->posts ON $wpdb->postmeta.post_id = $wpdb->posts.ID
			WHERE post_type = %s AND meta_key = %s AND meta_value !=''
			{$extra_checks}
			ORDER BY meta_value DESC
		",
					$post_type,
					$type
				)
			);
			$this->show_dropdown_date_filter( $type, $order_dates );
		}

		/**
		 * Show the dropdown filter
		 *
		 * @param string $type The filter type.
		 * @param object $dates The dates.
		 *
		 * @return void
		 */
		public function show_dropdown_date_filter( $type, $dates ) {
			global $wp_locale;
			$m              = isset( $_GET[ $type ] ) ? (int) $_GET[ $type ] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$general_option = 'ywcdd_order_shipping_date' === $type ? __( 'All shipping dates', 'yith-woocommerce-delivery-date' ) : __( 'All delivery dates', 'yith-woocommerce-delivery-date' );
			echo '<select name="' . esc_attr( $type ) . '" id="filter-by-' . esc_attr( $type ) . '">';
			echo '<option ' . selected( $m, 0, false ) . ' value="0">' . esc_html( $general_option ) . '</option>';

			foreach ( $dates as $date ) {
				$month           = zeroise( $date->month, 2 );
				$month_year_text = sprintf(
				/* translators: 1: Month name, 2: 4-digit year. */
					esc_html_x( '%1$s %2$d', 'order dates dropdown', 'yith-woocommerce-delivery-date' ),
					$wp_locale->get_month( $month ),
					$date->year
				);

				printf(
					'<option %1$s value="%2$s">%3$s</option>\n',
					selected( $m, $date->year . $month, false ),
					esc_attr( $date->year . $month ),
					esc_html( $month_year_text )
				);
			}

			echo '</select>';
		}

		/**
		 * Update order details
		 *
		 * @since 1.0.0
		 */
		public function update_order_details() {

			check_ajax_referer( 'update-order-details', 'security' );

			$order_id = ! empty( $_REQUEST['order_id'] ) ? wp_unslash( $_REQUEST['order_id'] ) : false; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( $order_id ) {

				$processing_method_id = ! empty( $_REQUEST['processing_method_id'] ) ? wp_unslash( $_REQUEST['processing_method_id'] ) : false;  // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$carrier_id           = ! empty( $_REQUEST['carrier_id'] ) ? wp_unslash( $_REQUEST['carrier_id'] ) : false;  // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$processing_date      = ! empty( $_REQUEST['processing_date'] ) ? wp_unslash( $_REQUEST['processing_date'] ) : false;  // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$delivery_date        = ! empty( $_REQUEST['delivery_date'] ) ? wp_unslash( $_REQUEST['delivery_date'] ) : false;  // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$timefrom             = ! empty( $_REQUEST['time_from'] ) ? wp_unslash( $_REQUEST['time_from'] ) : false;  // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$timeto               = ! empty( $_REQUEST['time_to'] ) ? wp_unslash( $_REQUEST['time_to'] ) : false;  // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				if ( $processing_method_id && $carrier_id && $processing_date && $delivery_date && $timefrom && $timeto ) {

					$order                  = wc_get_order( $order_id );
					$add_event_order_status = get_option( 'ywcdd_add_event_into_calendar' );
					YITH_Delivery_Date_Calendar()->delete_event_by_order_id( $order_id );

					if ( in_array( $order->get_status(), $add_event_order_status, true ) ) {
						/** Add new shipping event and add new delivery event into calendar */
						YITH_Delivery_Date_Calendar()->add_calendar_event( $processing_method_id, '', 'shipping_to_carrier', $processing_date, '', $order_id );

						YITH_Delivery_Date_Calendar()->add_calendar_event( $carrier_id, '', 'delivery_day', $delivery_date, $delivery_date, $order_id );
					}

					$order_meta = array(
						'ywcdd_order_delivery_date'     => $delivery_date,
						'ywcdd_order_shipping_date'     => $processing_date,
						'ywcdd_order_slot_from'         => $timefrom,
						'ywcdd_order_slot_to'           => $timeto,
						'ywcdd_order_carrier_id'        => $carrier_id,
						'ywcdd_order_processing_method' => $processing_method_id,
						'ywcdd_order_carrier'           => get_the_title( $carrier_id ),

					);

					foreach ( $order_meta as $key => $meta ) {

						$order->update_meta_data( $key, $meta );
					}
					$order->save();
					$add_event_order_status = get_option( 'ywcdd_add_event_into_calendar' );
					YITH_Delivery_Date_Calendar()->delete_event_by_order_id( $order_id );

					if ( in_array( $order->get_status(), $add_event_order_status, true ) ) {
						// add new shipping event and add new delivery event into calendar.
						YITH_Delivery_Date_Calendar()->add_calendar_event( $processing_method_id, '', 'shipping_to_carrier', $processing_date, '', $order_id );
						YITH_Delivery_Date_Calendar()->add_calendar_event( $carrier_id, '', 'delivery_day', $delivery_date, $delivery_date, $order_id );
					}
				}
			}
		}
	}
}

if ( ! function_exists( 'YITH_Delivery_Date_Order_Manager' ) ) {

	/**
	 * Return the instance of the class
	 *
	 * @since 1.0.0
	 */
	function YITH_Delivery_Date_Order_Manager() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
		return YITH_Delivery_Date_Order_Manager::get_instance();
	}
}

YITH_Delivery_Date_Order_Manager();
