<?php
/**
 * Handle background data-grabbing from the relay for all pages.
 *
 * @package exactmetrics-page-insights
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ExactMetrics_Page_Insights_Background
 */
class ExactMetrics_Page_Insights_Background {

	/**
	 * ExactMetrics_Page_Insights_Background constructor.
	 */
	public function __construct() {

		add_action( 'wp_ajax_exactmetrics_pageinsights_fetch_data', array( $this, 'fetch_all_data' ) );

	}

	/**
	 * Fetch all the data from Relay and store it in the local cache.
	 */
	public function fetch_all_data() {

		// Don't lock up other requests while processing this because it's meant to run async.
		session_write_close();

		check_ajax_referer( 'exactmetrics_pageinsights_fetch', 'nonce' );

		if ( ! ExactMetrics()->license->license_can( 'plus' ) ) {
			wp_die( 'license' );
		}

		$site_auth = ExactMetrics()->auth->get_viewname();
		$ms_auth   = is_multisite() && ExactMetrics()->auth->get_network_viewname();

		if ( empty( $site_auth ) && empty( $ms_auth ) ) {
			wp_die();
		}

		$api_options = array();
		if ( ! $site_auth && $ms_auth ) {
			$api_options['network'] = true;
		}

		$api = new ExactMetrics_API_Request( 'analytics/reports/pageinsights', $api_options, 'GET' );

		if ( isset( $_POST['page'] ) && sanitize_text_field($_POST['page']) ) {
			$page = intval( $_POST['page'] );
			$api->set_additional_data( array(
				'page' => $page,
			) );
			update_option( 'exactmetrics_pageinsights_pulling_data', time() );
		}

		$report_data = $api->request();

		if ( is_wp_error( $report_data ) ) {
			// Failed pulling report data.
			wp_die();
		} else {
			if ( ! empty( $report_data['data']['pageinsights'] ) && is_array( $report_data['data']['pageinsights'] ) ) {
				foreach ( $report_data['data']['pageinsights'] as $page_data ) {
					if ( ! empty( $page_data['page_path'] ) ) {
						ExactMetrics_Page_Insights_Cache::get_instance()->set( $page_data['page_path'], $page_data );
					}
				}
			}
		}

		if ( ! empty( $report_data['data']['more'] ) && isset( $page ) ) {
			$page ++;
			wp_remote_post( admin_url( 'admin-ajax.php' ), array(
				'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
				'blocking'  => true,
				'body'      => array(
					'action' => 'exactmetrics_pageinsights_fetch_data',
					'nonce'  => wp_create_nonce( 'exactmetrics_pageinsights_fetch' ),
					'page'   => $page,
				),
				'cookies'   => $_COOKIE,
			) );
		} else {
			// Save the time when the next bulk fetch should be triggered.
			$expiration = strtotime( ' Tomorrow 1am ' ) - ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
			update_option( 'exactmetrics_pageinsights_next_fetch', $expiration );

			// Clear the pulling data option so we know all data is loaded.
			delete_option( 'exactmetrics_pageinsights_pulling_data' );
		}

		wp_die(); // No need to output anything as it's meant for a non-blocking request.
	}

	/**
	 * Initialize a fetch in a non-blocking request.
	 *
	 * @param int|bool $page Need more than 1 background call.
	 *
	 * @return bool
	 */
	public static function start_fetch( $page = false ) {

		if ( ! self::should_fetch() ) {
			return false;
		}

		ExactMetrics_Page_Insights_Cache::get_instance()->clear_cache();

		wp_remote_post( admin_url( 'admin-ajax.php' ), array(
			'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
			'blocking'  => false,
			'timeout'   => 0.1,
			'body'      => array(
				'action' => 'exactmetrics_pageinsights_fetch_data',
				'nonce'  => wp_create_nonce( 'exactmetrics_pageinsights_fetch' ),
				'page'   => $page,
			),
			'cookies'   => $_COOKIE,
		) );

		return true;
	}

	/**
	 * If we should fetch all the data again.
	 *
	 * @return bool
	 */
	public static function should_fetch() {

		$next_fetch = get_option( 'exactmetrics_pageinsights_next_fetch' );

		return $next_fetch < time();

	}

}

new ExactMetrics_Page_Insights_Background();
