<?php

/**
 * Pro Admin features.
 *
 * Adds Pro Reporting features.
 *
 * @since 6.0.0
 *
 * @package ExactMetrics Dimensions
 * @subpackage Reports
 * @author  Chris Christoff
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ExactMetrics_Admin_Pro_Reports {

	/**
	 * Primary class constructor.
	 *
	 * @access public
	 * @since 6.0.0
	 */
	public function __construct() {
		$this->load_reports();
	}

	public function load_reports() {
		$overview_report = new ExactMetrics_Report_Overview();
		ExactMetrics()->reporting->add_report( $overview_report );

		require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/admin/reports/report-publisher.php';
		$publisher_report = new ExactMetrics_Report_Publisher();
		ExactMetrics()->reporting->add_report( $publisher_report );

		require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/admin/reports/report-ecommerce.php';
		$ecommerce_report = new ExactMetrics_Report_eCommerce();
		ExactMetrics()->reporting->add_report( $ecommerce_report );

		require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/admin/reports/report-queries.php';
		$queries_report = new ExactMetrics_Report_Queries();
		ExactMetrics()->reporting->add_report( $queries_report );

		require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/admin/reports/report-dimensions.php';
		$dimensions_report = new ExactMetrics_Report_Dimensions();
		ExactMetrics()->reporting->add_report( $dimensions_report );

		require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/admin/reports/report-forms.php';
		$forms_report = new ExactMetrics_Report_Forms();
		ExactMetrics()->reporting->add_report( $forms_report );

		require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/admin/reports/report-realtime.php';
		$realtime_report = new ExactMetrics_Report_RealTime();
		ExactMetrics()->reporting->add_report( $realtime_report );

		require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/admin/reports/report-year-in-review.php';
		$year_in_review = new ExactMetrics_Report_YearInReview();
		ExactMetrics()->reporting->add_report( $year_in_review );

		require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/admin/reports/report-popularposts.php';
		$popular_posts = new ExactMetrics_Report_PopularPosts();
		ExactMetrics()->reporting->add_report( $popular_posts );

		require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/admin/reports/report-site-speed.php';
		$site_speed = new ExactMetrics_Report_SiteSpeed();
		ExactMetrics()->reporting->add_report( $site_speed );

		require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/admin/reports/report-site-speed-mobile.php';
		$site_speed_mobile = new ExactMetrics_Report_SiteSpeed_Mobile();
		ExactMetrics()->reporting->add_report( $site_speed_mobile );

		require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/admin/reports/report-summaries.php';
		$summaries = new ExactMetrics_Report_Summaries();
		ExactMetrics()->reporting->add_report( $summaries );

        require_once EXACTMETRICS_PLUGIN_DIR . 'pro/includes/admin/reports/report-media.php';
        $media_report = new ExactMetrics_Report_Media();
        ExactMetrics()->reporting->add_report( $media_report );
    }
}
