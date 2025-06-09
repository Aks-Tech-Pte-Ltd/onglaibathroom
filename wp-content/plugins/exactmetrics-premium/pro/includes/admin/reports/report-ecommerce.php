<?php
/**
 * eCommerce Report
 *
 * Ensures all of the reports have a uniform class with helper functions.
 *
 * @since 6.0.0
 *
 * @package ExactMetrics
 * @subpackage Reports
 * @author  Chris Christoff
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ExactMetrics_Report_eCommerce extends ExactMetrics_Report {

	public $title;
	public $class = 'ExactMetrics_Report_eCommerce';
	public $name = 'ecommerce';
	public $version = '1.0.0';
	public $level = 'pro';

	/**
	 * Primary class constructor.
	 *
	 * @access public
	 * @since 6.0.0
	 */
	public function __construct() {
		$this->title = __( 'eCommerce', 'exactmetrics-premium' );
		parent::__construct();
	}

	public function requirements( $error = false, $args = array(), $name = '' ) {
		if ( ! empty( $error ) || $name !== $this->name ) {
			return $error;
		}

		if ( ! class_exists( 'ExactMetrics_eCommerce' ) ) {
			add_filter( 'exactmetrics_reports_handle_error_message', array( $this, 'add_error_addon_link' ) );

			// Translators: %s will be the action (install/activate) which will be filled depending on the addon state.
			$text = __( 'Please %s the ExactMetrics eCommerce addon to view custom dimensions reports.', 'exactmetrics-premium' );

			if ( exactmetrics_can_install_plugins() ) {
				return $text;
			} else {
				return sprintf( $text, __( 'install', 'exactmetrics-premium' ) );
			}
		}

		return $error;
	}

	/**
	 * Prepare report-specific data for output.
	 *
	 * @param array $data The data from the report before it gets sent to the frontend.
	 *
	 * @return mixed
	 */
	public function prepare_report_data( $data ) {
		// Add GA links.
		if ( ! empty( $data['data'] ) ) {
			$data['data']['galinks'] = array(
				'products'    => $this->get_ga_report_url(
					'ecomm-product',
					$data['data'],
					'_r.explorerCard..selmet=["itemInfoRevenue","productViews"]&_r.explorerCard..sortKey=itemInfoRevenue'
				),
				'conversions' => $this->get_ga_report_url(
					'lifecycle-traffic-acquisition',
					$data['data'],
					'_r.explorerCard..columnFilters={"conversionEvent":"purchase"}&_r.explorerCard..selmet=["combinedRevenue","activeUsers"]&_r.explorerCard..sortKey=combinedRevenue'
				),
				'days'        => $this->get_ga_report_url( '', $data['data'] ),
				'sessions'    => $this->get_ga_report_url( '', $data['data'] ),
			);
		}

		return $data;
	}

	/**
	 * Add link to ecommerce settings to the footer of the disabled enhanced ecommerce notice.
	 *
	 * @param array $data The data being sent back to the Ajax call.
	 *
	 * @return array
	 */
	public function add_ecommerce_settings_link( $data ) {
		$ecommerce_link         = add_query_arg( array( 'page' => 'exactmetrics_settings' ), admin_url( 'admin.php' ) );
		$ecommerce_link         .= '#/ecommerce';
		$data['data']['footer'] = $ecommerce_link;

		return $data;
	}
}
