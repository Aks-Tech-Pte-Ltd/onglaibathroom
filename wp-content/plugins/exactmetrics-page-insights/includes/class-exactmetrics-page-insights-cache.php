<?php
/**
 * Handle the custom caching for the Page Insights report.
 *
 * @package exactmetrics-page-insights
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ExactMetrics_Page_Insights_Cache
 */
class ExactMetrics_Page_Insights_Cache {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var ExactMetrics_Page_Insights_Cache
	 */
	public static $instance;

	/**
	 * The db interface.
	 *
	 * @var wpdb
	 */
	private $db;

	/**
	 * The name of the table where the data is stored.
	 *
	 * @var string
	 */
	private $table;

	/**
	 * Final cache result after making sum of all similar page data.
	 *
	 * @var array
	 */
	private $final_data;

	/**
	 * ExactMetrics_Page_Insights_Cache constructor.
	 */
	private function __construct() {

		global $wpdb;
		$this->db    = $wpdb;
		$this->table = $this->db->prefix . 'exactmetrics_pageinsights_cache';

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @return ExactMetrics_Page_Insights_Cache
	 * @since 1.0.0
	 *
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof ExactMetrics_Page_Insights_Cache ) ) {
			self::$instance = new ExactMetrics_Page_Insights_Cache();
		}

		return self::$instance;
	}

	/**
	 * Save data to the cache.
	 *
	 * @param string $path The page path for which the data is stored.
	 * @param array $data The actual data which will be stored.
	 * @param int $expiration When should this data expire.
	 *
	 * @return int|bool False if the data was not inserted.
	 */
	public function set( $path, $data, $expiration = 0 ) {

		if ( 0 === $expiration ) {
			// By default, set the expiration for next day in the website's timezone.
			$expiration = strtotime( ' Tomorrow 1am ' ) - ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
		}

		if ( empty( $path ) || empty( $data ) ) {
			return false;
		}

		$data = wp_json_encode( $data );

		return $this->db->insert( $this->table, array(
			'path'   => $path,
			'value'  => $data,
			'expiry' => date( 'Y-m-d H:i:s', $expiration ),
		) );

	}

	/**
	 * Grab the data from the cache.
	 *
	 * @param string $path The path of the page for the data is loaded.
	 *
	 * @return array|bool The stored data or false if not valid/expired.
	 */
	public function get( $path ) {
		$query = $this->db->prepare(
			"SELECT `value`, `expiry` FROM  $this->table WHERE `path` = %s OR `path` LIKE %s",
			$path,
			$path . '?%'
		);

		$query_results = $this->db->get_results( $query );

		// If data not found then return.
		if ( ! $query_results ) {
			return false;
		}

		return $this->process_cache_result( $query_results );
	}

	/**
	 * Empty the cache table.
	 */
	public function clear_cache() {

		update_option( 'exactmetrics_pageinsights_next_fetch', 0 );
		$this->db->query( "TRUNCATE table $this->table" );

	}

	/**
	 * Destroy the current instance.
	 */
	public static function destroy() {

		self::$instance = null;
	}

	/**
	 * Process page path cache result.
	 *
	 * @param array $query_results Result from query.
	 *
	 * @return array
	 */
	private function process_cache_result( $query_results ) {
		$this->final_data = array(
			'page_paths' => array(),
			'yesterday'  => array(
				'totalusers' => 0,
				'pageviews'  => 0,
				'timeonpage' => '0s',
				'entrances'  => 0,
			),
			'30days'     => array(
				'totalusers' => 0,
				'pageviews'  => 0,
				'timeonpage' => '0s',
				'entrances'  => 0,
			),
		);

		foreach ( $query_results as $result ) {
			if ( empty( $result->expiry ) || empty( $result->value ) ) {
				return false;
			}

			if ( strtotime( $result->expiry ) < time() ) {
				// The content expired.
				// Empty the table so we can replace it with fresh data.
				$this->clear_cache();

				return false;
			}

			$this->sum_query_result( json_decode( $result->value, true ) );
		}

		return $this->final_data;
	}

	/**
	 * Make sum of all results one by one.
	 *
	 * @param array $final_data Final result to return.
	 * @param array $value Json decoded value from each row.
	 */
	private function sum_query_result( $value ) {
		$this->final_data['page_paths'][] = $value['page_path'];

		$this->final_data['yesterday']['totalusers'] += isset( $value['yesterday']['totalusers'] ) ? $value['yesterday']['totalusers'] : 0;
		$this->final_data['yesterday']['pageviews']  += isset( $value['yesterday']['pageviews'] ) ? $value['yesterday']['pageviews'] : 0;
		$this->final_data['yesterday']['entrances']  += isset( $value['yesterday']['entrances'] ) ? $value['yesterday']['entrances'] : 0;
		$this->final_data['yesterday']['timeonpage'] = $this->sum_timeonpage_seconds(
			$this->final_data['yesterday']['timeonpage'],
			isset( $value['yesterday']['timeonpage'] ) ? $value['yesterday']['timeonpage'] : '0s'
		);

		$this->final_data['30days']['totalusers'] += isset( $value['30days']['totalusers'] ) ? $value['30days']['totalusers'] : 0;
		$this->final_data['30days']['pageviews']  += isset( $value['30days']['pageviews'] ) ? $value['30days']['pageviews'] : 0;
		$this->final_data['30days']['entrances']  += isset( $value['30days']['entrances'] ) ? $value['30days']['entrances'] : 0;
		$this->final_data['30days']['timeonpage'] = $this->sum_timeonpage_seconds(
			$this->final_data['30days']['timeonpage'],
			isset( $value['30days']['timeonpage'] ) ? $value['30days']['timeonpage'] : '0s'
		);
	}

	/**
	 * Sum two value of second string.
	 *
	 * @param string $stored_value Value one.
	 * @param string $page_value Value two.
	 *
	 * @return string
	 */
	private function sum_timeonpage_seconds( $stored_value, $page_value ) {
		$stored_seconds = absint( rtrim( $stored_value, 's' ) );
		$page_seconds   = absint( rtrim( $page_value, 's' ) );

		return strval( $stored_seconds + $page_seconds ) . 's';
	}
}
