<?php
/**
 * Use a separate handler for ajax calls so they can be accessed from frontend also.
 *
 * @package exactmetrics-page-insights
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class ExactMetrics_Page_Insights_Ajax
 */
class ExactMetrics_Page_Insights_Ajax {

	/**
	 * ExactMetrics_Page_Insights_Ajax constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_exactmetrics_pageinsights_refresh_report', array( $this, 'refresh_reports_data' ) );
		add_action( 'wp_ajax_exactmetrics_pageinsights_check_background_progress', array(
			$this,
			'check_background_progress'
		) );

		add_action( 'wp_ajax_exactmetrics_pageinsights_meta_report', array( $this, 'get_reports_data' ) );
	}

	/**
	 * Refresh the reports data, similar to the core plugin.
	 */
	public function refresh_reports_data() {
		check_ajax_referer( 'mi-admin-nonce', 'security' );

		// Get variables.
		$start     = ! empty( $_REQUEST['start'] ) ? sanitize_text_field($_REQUEST['start']) : '';
		$end       = ! empty( $_REQUEST['end'] ) ? sanitize_text_field($_REQUEST['end']) : '';
		$name      = ! empty( $_REQUEST['report'] ) ? sanitize_text_field($_REQUEST['report']) : '';
		$isnetwork = ! empty( $_REQUEST['isnetwork'] ) ? filter_var($_REQUEST['isnetwork'], FILTER_VALIDATE_BOOLEAN) : false;
		$json      = ! empty( $_REQUEST['json'] ) ? sanitize_text_field($_REQUEST['json']) : false;

		if ( ! empty( $_REQUEST['isnetwork'] ) && filter_var($_REQUEST['isnetwork'], FILTER_VALIDATE_BOOLEAN) ) {
			define( 'WP_NETWORK_ADMIN', true );
		}

		// Only for Pro users, require a license key to be entered first so we can link to things.
		if ( exactmetrics_is_pro_version() ) {
			if ( ! ExactMetrics()->license->is_site_licensed() && ! ExactMetrics()->license->is_network_licensed() ) {
				wp_send_json_error( array( 'message' => __( 'You can\'t view ExactMetrics reports because you are not licensed.', 'exactmetrics-page-insights' ) ) );
			} else if ( ExactMetrics()->license->is_site_licensed() && ! ExactMetrics()->license->site_license_has_error() ) {
				// Good to go: site licensed.
			} else if ( ExactMetrics()->license->is_network_licensed() && ! ExactMetrics()->license->network_license_has_error() ) {
				// Good to go: network licensed.
			} else {
				wp_send_json_error( array( 'message' => __( 'You can\'t view ExactMetrics reports due to license key errors.', 'exactmetrics-page-insights' ) ) );
			}
		}

		// We do not have a current auth.
		$site_auth = ExactMetrics()->auth->get_viewname();
		$ms_auth   = is_multisite() && ExactMetrics()->auth->get_network_viewname();
		if ( ! $site_auth && ! $ms_auth ) {
			wp_send_json_error( array( 'message' => __( 'You must authenticate with ExactMetrics before you can view reports.', 'exactmetrics-page-insights' ) ) );
		}

		if ( empty( $name ) ) {
			wp_send_json_error( array( 'message' => __( 'Unknown report. Try refreshing and retrying. Contact support if this issue persists.', 'exactmetrics-page-insights' ) ) );
		}

		$report = new ExactMetrics_Report_Page_Insights();

		if ( empty( $report ) ) {
			wp_send_json_error( array( 'message' => __( 'Unknown report. Try refreshing and retrying. Contact support if this issue persists.', 'exactmetrics-page-insights' ) ) );
		}

		$args = array(
			'start' => $start,
			'end'   => $end,
		);
		if ( $isnetwork ) {
			$args['network'] = true;
		}

		$data = $report->get_data( $args );

		if ( $json ) {
			$data = $report->prepare_report_data( $data );

			if ( ! empty( $data['success'] ) && ! empty( $data['data'] ) ) {
				wp_send_json_success( $data['data'] );
			} else if ( isset( $data['success'] ) && false === $data['success'] && ! empty( $data['error'] ) ) {
				wp_send_json_error(
					array(
						'message' => $data['error'],
						'footer'  => isset( $data['data']['footer'] ) ? $data['data']['footer'] : '',
					)
				);
			}
		}

		if ( ! empty( $data['success'] ) ) {

			if ( ! empty( $data['more'] ) ) {
				$data = '<p>';
				$data .= __( 'It looks like your site has a large number of pages, please wait while we process the data for you, this might take a couple minutes.', 'exactmetrics-page-insights' );
				$data .= '</p>';
				wp_send_json_success( array( 'more' => $data ) );

			} else {
				$data = $report->get_report_html( $data['data'] );
				wp_send_json_success( array( 'html' => $data ) );
			}
		} else {
			wp_send_json_error(
				array(
					'message' => $data['error'],
					'data'    => $data['data'],
				)
			);
		}
	}

	function check_background_progress() {
		check_ajax_referer( 'mi-admin-nonce', 'security' );

		$pulling_data = get_option( 'exactmetrics_pageinsights_pulling_data', false );
		$done         = true;

		if ( $pulling_data || time() - intval( $pulling_data ) < 10 * MINUTE_IN_SECONDS ) {
			$done = false;
		}

		wp_send_json_success(
			array(
				'done' => $done,
			)
		);

	}

	public function get_reports_data() {
		check_ajax_referer( 'mi-admin-nonce', 'security' );

		// Get variables.
		$start     = ! empty( $_REQUEST['start'] ) ? sanitize_text_field($_REQUEST['start']) : '';
		$end       = ! empty( $_REQUEST['end'] ) ? sanitize_text_field($_REQUEST['end']) : '';
		$name      = ! empty( $_REQUEST['report'] ) ? sanitize_text_field($_REQUEST['report']) : '';
		$isnetwork = ! empty( $_REQUEST['isnetwork'] ) ? filter_var($_REQUEST['isnetwork'], FILTER_VALIDATE_BOOLEAN) : '';

		if ( ! empty( $_REQUEST['isnetwork'] ) && filter_var($_REQUEST['isnetwork'], FILTER_VALIDATE_BOOLEAN) ) {
			define( 'WP_NETWORK_ADMIN', true );
		}

		// Only for Pro users, require a license key to be entered first so we can link to things.
		if ( exactmetrics_is_pro_version() ) {
			if ( ! ExactMetrics()->license->is_site_licensed() && ! ExactMetrics()->license->is_network_licensed() ) {
				wp_send_json_error( array( 'message' => __( 'You can\'t view ExactMetrics reports because you are not licensed.', 'exactmetrics-page-insights' ) ) );
			} else if ( ExactMetrics()->license->is_site_licensed() && ! ExactMetrics()->license->site_license_has_error() ) {
				// Good to go: site licensed.
			} else if ( ExactMetrics()->license->is_network_licensed() && ! ExactMetrics()->license->network_license_has_error() ) {
				// Good to go: network licensed.
			} else {
				wp_send_json_error( array( 'message' => __( 'You can\'t view ExactMetrics reports due to license key errors.', 'exactmetrics-page-insights' ) ) );
			}
		}

		// We do not have a current auth.
		$site_auth = ExactMetrics()->auth->get_viewname();
		$ms_auth   = is_multisite() && ExactMetrics()->auth->get_network_viewname();
		if ( ! $site_auth && ! $ms_auth ) {
			wp_send_json_error( array( 'message' => __( 'You must authenticate with ExactMetrics before you can view reports.', 'exactmetrics-page-insights' ) ) );
		}

		if ( empty( $name ) ) {
			wp_send_json_error( array( 'message' => __( 'Unknown report. Try refreshing and retrying. Contact support if this issue persists.', 'exactmetrics-page-insights' ) ) );
		}

		$report = new ExactMetrics_Report_Page_Insights();

		if ( empty( $report ) ) {
			wp_send_json_error( array( 'message' => __( 'Unknown report. Try refreshing and retrying. Contact support if this issue persists.', 'exactmetrics-page-insights' ) ) );
		}

		$args = array(
			'start' => $start,
			'end'   => $end,
		);
		if ( $isnetwork ) {
			$args['network'] = true;
		}

		$data = $report->get_data( $args );

		$report_data = $report->prepare_report_raw_data( $data['data'] );

		if ( ! empty( $data['success'] ) && ! empty( $report_data ) ) {
			wp_send_json_success( $report_data );
		} else if ( isset( $data['success'] ) && false === $data['success'] && ! empty( $data['error'] ) ) {
			wp_send_json_error(
				array(
					'message' => $data['error'],
					'footer'  => isset( $data['data']['footer'] ) ? $data['data']['footer'] : '',
				)
			);
		}
	}
}
