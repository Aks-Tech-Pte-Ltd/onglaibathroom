<?php
/**
 * Media Report
 *
 * Ensures all of the reports have a uniform class with helper functions.
 *
 * @since 8.9.0
 *
 * @package ExactMetrics
 * @subpackage Reports
 * @author Sourov
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ExactMetrics_Report_Media extends ExactMetrics_Report {

	public $class = 'ExactMetrics_Report_Media';
	public $name  = 'media';
	public $level = 'plus';

	/**
	 * Primary class constructor.
	 *
	 * @access public
	 * @since 8.9.0
	 */
	public function __construct() {
		$this->title = __( 'Media', 'exactmetrics-premium' );

		parent::__construct();
	}

	public function requirements( $error = false, $args = array(), $name = '' ) {
		if ( ! empty( $error ) || $name !== $this->name ) {
			return $error;
		}

		if ( ! class_exists( 'ExactMetrics_Media' ) ) {
			add_filter( 'exactmetrics_reports_handle_error_message', array( $this, 'add_error_addon_link' ) );

			// Translators: %s will be the action (install/activate) which will be filled depending on the addon state.
			$text = __( 'Please %s the ExactMetrics Media addon to view media reports.', 'exactmetrics-premium' );

			if ( exactmetrics_can_install_plugins() ) {
				return $text;
			} else {
				return sprintf( $text, __( 'install', 'exactmetrics-premium' ) );
			}
		}

		return $error;
	}

}
