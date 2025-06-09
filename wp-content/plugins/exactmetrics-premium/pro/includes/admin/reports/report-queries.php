<?php
/**
 * Queries Report
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

final class ExactMetrics_Report_Queries extends ExactMetrics_Report {

	public $title;
	public $class = 'ExactMetrics_Report_Queries';
	public $name = 'queries';
	public $version = '1.0.0';
	public $level = 'plus';

	/**
	 * Primary class constructor.
	 *
	 * @access public
	 * @since 6.0.0
	 */
	public function __construct() {
		$this->title = __( 'Search Console', 'exactmetrics-premium' );
		parent::__construct();
	}


	/**
	 * Prepare report-specific data for output.
	 *
	 * @param array $data The data from the report before it gets sent to the frontend.
	 *
	 * @return mixed
	 */
	public function prepare_report_data( $data ) {
		return $data;
	}

}
