<?php // phpcs:ignore WordPress.Files.FileName
/**
 * This class manage the calendar feature
 *
 * @package YITH WooCommerce Delivery Date\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'YITH_Delivery_Date_Calendar' ) ) {

	/**
	 * Class YITH_Delivery_Date_Calendar
	 */
	class YITH_Delivery_Date_Calendar {
		/**
		 * The instance of the class
		 *
		 * @var YITH_Delivery_Date_Calendar
		 */
		protected static $_instance; // phpcs:ignore

		/**
		 * The table name
		 *
		 * @var string
		 */
		protected $table_name;

		/**
		 * YITH_Delivery_Date_Calendar constructor.
		 */
		public function __construct() {
			global $wpdb;

			$this->table_name = $wpdb->prefix . 'ywcdd_calendar';

		}

		/**
		 * The unique access of the class
		 *
		 * @return YITH_Delivery_Date_Calendar
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
		 * Create calendar table if not exist
		 *
		 * @since 1.0.0
		 */
		public function install() {

			$db_version = get_option( 'ywcdd_db_version', '0' );

			if ( version_compare( $db_version, '1.0.0', '<' ) ) {

				$this->create_table();
				update_option( 'ywcdd_db_version', '1.0.0' );
			}
		}

		/**
		 * Create table
		 *
		 * @since 1.0.0
		 */
		public function create_table() {

			$sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
                    ID int(11) NOT NULL AUTO_INCREMENT,
                    post_type varchar(255) NOT NULL,
                    post_id int(11) NOT NULL,
                    event_type varchar(255) NOT NULL,
                    event_name varchar(255) ,
                    event_start DATE NOT NULL ,
                   	event_end DATE NOT NULL ,
                    order_id int(11)  ,
                    PRIMARY KEY ( ID ) ); DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

			if ( ! function_exists( 'dbDelta' ) ) {
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			}

			dbDelta( $sql );
		}

		/**
		 * Return the event types
		 *
		 * @return array
		 * @since 1.0.0
		 */
		public function get_event_type() {
			/**
			 * APPLY_FILTERS: ywcdd_calendar_event_type
			 *
			 * This filter allow to add or remove the calendar events in the calendar.
			 *
			 * @param array $events The calendar events.
			 *
			 * @return array
			 */
			return apply_filters(
				'ywcdd_calendar_event_type',
				array(
					'shipping_to_carrier',
					'delivery_day',
					'holiday',
				)
			);

		}

		/**
		 * Add int the calendar the events
		 *
		 * @param array  $event_for The event types.
		 * @param string $event_name The event name.
		 * @param string $start_event Date start.
		 * @param string $end_event Date end.
		 *
		 * @return array new index
		 */
		public function add_all_holiday_calendar_event( $event_for, $event_name, $start_event, $end_event = '' ) {
			$new_ids = array();
			if ( $event_for ) {

				foreach ( $event_for as $single_for ) {
					$new_ids[] = $this->add_calendar_event( $single_for, $event_name, 'holiday', $start_event, $end_event );
				}
			}

			return $new_ids;
		}

		/**
		 * Add new event into calendar
		 *
		 * @param int    $post_id The post id.
		 * @param string $event_name The event name.
		 * @param string $type_event The event type.
		 * @param string $start_event Date start.
		 * @param string $end_event Date end.
		 * @param int    $order_id The order id.
		 * @param bool   $single_items if true check if event exists.
		 *
		 * @return false|int
		 * @since 1.0.0
		 *
		 */
		public function add_calendar_event( $post_id, $event_name, $type_event, $start_event, $end_event = '', $order_id = - 1, $single_items = false ) {

			if ( in_array( $type_event, $this->get_event_type(), true ) ) {

				$event_exists = ! $single_items ? $this->event_exists( $order_id, $type_event ) : $this->single_event_exists( $order_id, $start_event, $end_event, $type_event, $post_id );

				if ( - 1 === intval( $order_id ) || ! $event_exists ) {

					global $wpdb;

					$start_event = date( 'Y-m-d', strtotime( $start_event ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
					$end_event   = empty( $end_event ) ? $start_event : date( 'Y-m-d', strtotime( $end_event ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
					$args        = array(
						'post_id'     => $post_id,
						'post_type'   => - 1 !== intval( $post_id ) ? get_post_type( $post_id ) : 'carrier_default',
						'event_name'  => $event_name,
						'event_type'  => $type_event,
						'event_start' => $start_event,
						'event_end'   => $end_event,
						'order_id'    => $order_id,
					);

					$wpdb->show_errors();
					$index = $wpdb->insert( $this->table_name, $args ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

					return $index;
				}
			}
		}

		/**
		 * Check if the event is already in the table
		 *
		 * @param int    $order_id The order id.
		 * @param string $event_type The event type.
		 *
		 * @return bool
		 * @since 1.0.3
		 *
		 */
		public function event_exists( $order_id, $event_type ) {

			global $wpdb;

			$query = "SELECT COUNT(ID) AS tot FROM {$this->table_name} WHERE order_id = %d AND event_type LIKE %s ";

			$query = $wpdb->prepare( $query, $order_id, $event_type ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			$result = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared

			return $result >= 1;
		}

		/**
		 * Check if an event exist
		 *
		 * @param int    $order_id The order id.
		 * @param string $from The date from.
		 * @param string $to The date to.
		 * @param string $event_type The event type.
		 * @param string $event_for The event "id".
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		public function single_event_exists( $order_id, $from, $to, $event_type, $event_for ) {

			global $wpdb;

			if ( empty( $to ) ) {
				$to = $from;
			}

			$query = $wpdb->prepare(
				"SELECT * FROM {$this->table_name} WHERE order_id = %d AND event_start = %s AND event_end = %s AND post_id = %s AND event_type LIKE %s LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$order_id,
				$from,
				$to,
				$event_for,
				$event_type
			); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			$result = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared

			return ! empty( $result );
		}

		/**
		 * Get all events
		 *
		 * @return array|object|null
		 * @since 1.0.0
		 */
		public function get_calendar_all_events() {

			global $wpdb;

			$query = "SELECT * FROM {$this->table_name} AS ca ORDER BY ca.post_id";

			return $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,  WordPress.DB.PreparedSQL.NotPrepared
		}

		/**
		 * Check if a day is holiday
		 *
		 * @param int        $post_id The event id.
		 * @param string|int $time The time.
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		public function is_holiday( $post_id, $time ) {
			global $wpdb;

			if ( ! is_numeric( $time ) ) {
				$time = strtotime( $time );
			}

			$date_from = date( 'Y-m-d', $time ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions

			$query = $wpdb->prepare( "SELECT DISTINCT COUNT(ID) as tot  FROM {$this->table_name} as ca WHERE ( %s BETWEEN  ca.event_start AND ca.event_end ) AND ca.event_type LIKE %s AND ca.post_id = %d", $date_from, 'holiday', $post_id ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			$count = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared

			return $count > 0;
		}

		/**
		 * Get the holiday that start from $date_from.
		 *
		 * @param int $post_id The post id.
		 * @param int $date_from The datetime.
		 *
		 * @return array|object|null
		 * @since 1.0.0
		 */
		public function get_calendar_holiday_from( $post_id, $date_from ) {

			global $wpdb;

			$date_from = date( 'Y-m-d', strtotime( $date_from ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
			$query     = $wpdb->prepare( "SELECT  ca.event_start as start, ca.event_end as end FROM {$this->table_name} as ca WHERE ca.event_start <= %s AND ca.event_type LIKE %s AND ca.post_id = %d", $date_from, 'holiday', $post_id ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			return $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared
		}

		/**
		 * Get the holiday within $date_to
		 *
		 * @param int $post_id The post id.
		 * @param int $date_to The time.
		 *
		 * @return array|object|null
		 * @since 1.0.0
		 */
		public function get_calendar_holiday_to( $post_id, $date_to ) {

			global $wpdb;

			$date_to = date( 'Y-m-d', strtotime( $date_to ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
			$query   = $wpdb->prepare( "SELECT ca.event_start as start, ca.event_end as end FROM {$this->table_name} as ca WHERE ca.event_end >= %s AND ca.event_type LIKE %s AND ca.post_id = %d", $date_to, 'holiday', $post_id ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			return $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared
		}

		/**
		 * Get all events for fullcalendar
		 *
		 * @param bool   $is_json Return the result in json ?.
		 * @param string $from Date from.
		 * @param string $to Date to.
		 *
		 * @return array|false|string
		 * @since 1.0.0
		 */
		public function get_calendar_events( $is_json = false, $from = '', $to = '' ) {
			if ( empty( $from ) || empty( $to ) ) {
				$all_holiday = $this->get_calendar_all_events();
			} else {
				$all_holiday = $this->get_events_by_date_range( $from, $to );
			}
			$json_holidays = array();

			foreach ( $all_holiday as $holiday ) {

				$event_type = $holiday->event_type;
				$event      = array();

				if ( isset( $holiday->order_id ) && - 1 !== intval( $holiday->order_id ) ) {
					$order = wc_get_order( $holiday->order_id );

					if ( ! ( $order instanceof WC_Order ) ) {

						$this->delete_event_by_order_id( $holiday->order_id );
						continue;
					}

					$add_event_order_status = get_option( 'ywcdd_add_event_into_calendar' );
					if ( ! in_array( $order->get_status(), $add_event_order_status, true ) ) {
						continue;
					}
				}
				$id = $holiday->post_id;

				$who         = $this->get_event_identifier( $id );
				$color       = get_option( 'ywcdd_' . $event_type . '_color', '#1197C1' );
				$start_event = strtotime( $holiday->event_start );
				$start_event = date( 'Y-m-d', $start_event ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
				$end_event   = '';

				// if end event is set.
				if ( $holiday->event_start !== $holiday->event_end ) {
					$end_event = strtotime( $holiday->event_end );
					$end_event = date( 'Y-m-d', $end_event + DAY_IN_SECONDS ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
				}

				switch ( $event_type ) {

					case 'holiday':
						$title   = sprintf( '<strong>%s %s</strong><br/> %s', __( 'Holiday for', 'yith-woocommerce-delivery-date' ), $who, $holiday->event_name );
						$content = '';
						break;
					case 'shipping_to_carrier':
						$edit_order_link = admin_url( 'post.php' );
						$edit_order_link = esc_url(
							add_query_arg(
								array(
									'action' => 'edit',
									'post'   => $holiday->order_id,
								),
								$edit_order_link
							)
						);
						$edit_order_link = sprintf( '<a href="%s" class="order_link" target="_blank">%s #%s</a>', $edit_order_link, __( 'Order', 'yith-woocommerce-delivery-date' ), $holiday->order_id );
						$title           = sprintf( '<strong>%s</strong><br/>%s', __( 'Shipping day for', 'yith-woocommerce-delivery-date' ), $edit_order_link );

						break;

					case 'delivery_day':
						$edit_order_link = admin_url( 'post.php' );
						$edit_order_link = esc_url(
							add_query_arg(
								array(
									'action' => 'edit',
									'post'   => $holiday->order_id,
								),
								$edit_order_link
							)
						);
						$edit_order_link = sprintf( '<a href="%s" class="order_link" target="_blank">%s #%s</a>', $edit_order_link, __( 'Order', 'yith-woocommerce-delivery-date' ), $holiday->order_id );
						$title           = sprintf( '<strong>%s</strong><br/>%s', __( 'Delivery day for', 'yith-woocommerce-delivery-date' ), $edit_order_link );
						$time_from       = get_post_meta( $holiday->order_id, 'ywcdd_order_slot_from', true );
						$time_to         = get_post_meta( $holiday->order_id, 'ywcdd_order_slot_to', true );

						if ( ! empty( $time_from ) && ! empty( $time_to ) ) {

							if ( ! is_numeric( $time_from ) && ! is_numeric( $time_to ) ) {

								$time_from = strtotime( $time_from );
								$time_to   = strtotime( $time_to );
							}
							$time_from = date( 'H:i:s', $time_from ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
							$time_to   = date( 'H:i:s', $time_to ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions

							$end_event   = $start_event . 'T' . $time_to;
							$start_event = $start_event . 'T' . $time_from;
						}
						break;
					default:
						$title   = '';
						$content = '';
						break;
				}

				$event['id']    = $holiday->ID;
				$event['title'] = $title;
				$event['start'] = $start_event;

				if ( ! empty( $end_event ) ) {
					$event['end'] = $end_event;
				}
				$event['color']      = $color;
				$event['event_type'] = $event_type;
				$json_holidays[]     = $event;

			}

			return $is_json ? wp_json_encode( $json_holidays ) : $json_holidays;
		}

		/**
		 * Return the event identifier : A Carrier name or a process order method name
		 *
		 * @param string|int $holiday_id The post id.
		 *
		 * @return string
		 * @since 1.0.0
		 */
		private function get_event_identifier( $holiday_id ) {

			if ( - 1 === $holiday_id ) {

				return __( 'Carrier Default', 'yith-woocommerce-delivery-date' );
			} elseif ( is_numeric( $holiday_id ) ) {

				return get_the_title( $holiday_id );
			} else {
				return '';
			}
		}

		/**
		 * Delete event by event id
		 *
		 * @param int $event_id The event id.
		 *
		 * @return false|int
		 * @since 1.0.0
		 */
		public function delete_event_by_id( $event_id ) {

			global $wpdb;

			return $wpdb->delete( $this->table_name, array( 'ID' => $event_id ) );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		}

		/**
		 * Delete event by order id
		 *
		 * @param int $order_id The order id.
		 *
		 * @return false|int
		 * @since 1.0.0
		 */
		public function delete_event_by_order_id( $order_id ) {
			global $wpdb;

			return $wpdb->delete( $this->table_name, array( 'order_id' => $order_id ) );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}

		/**
		 * Delete event by date
		 *
		 * @param string $date_from Date from.
		 * @param string $date_to Date to.
		 * @param array  $post_ids the post ids.
		 */
		public function delete_event_by_date( $date_from, $date_to, $post_ids ) {

			global $wpdb;

			$date_from = date( 'Y-m-d', strtotime( $date_from ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
			$date_to   = date( 'Y-m-d', strtotime( $date_to ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
			$query     = "DELETE FROM $this->table_name WHERE event_start = %s AND event_end = %s AND post_id IN ('" . implode( "','", $post_ids ) . "' ) ";

			$query = $wpdb->prepare( $query, $date_from, $date_to );  // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			$wpdb->query( $query );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared

		}

		/**
		 * Get the event between two date
		 *
		 * @param string|int $from Date from.
		 * @param string|int $to Date to.
		 *
		 * @return array
		 */
		public function get_events_by_date_range( $from, $to ) {

			$from = ! is_string( $from ) ? date( 'Y-m-d', $from ) : $from; // phpcs:ignore WordPress.DateTime.RestrictedFunctions
			$to   = ! is_string( $to ) ? date( 'Y-m-d', $to ) : $to; // phpcs:ignore WordPress.DateTime.RestrictedFunctions

			global $wpdb;

			$query = $wpdb->prepare( "SELECT * FROM $this->table_name WHERE (event_end >=%s AND event_start<=%s )", $from, $to ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			$result = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.NotPrepared

			return $result;
		}
	}
}
/**
 * Return the instance
 *
 * @return YITH_Delivery_Date_Calendar
 */
function YITH_Delivery_Date_Calendar() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName

	return YITH_Delivery_Date_Calendar::get_instance();
}
