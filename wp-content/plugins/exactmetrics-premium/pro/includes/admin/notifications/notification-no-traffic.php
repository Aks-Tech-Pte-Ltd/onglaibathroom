<?php

/**
 * Add notification when the site hasn't received any traffic in the last 7 days.
 * Recurrence: 7 Days
 *
 * @since 7.12.3
 */
final class ExactMetrics_Notification_No_Traffic extends ExactMetrics_Notification_Event {
	public $notification_id = 'exactmetrics_notification_no_traffic';
	public $notification_interval = 7;
	public $notification_type = array( 'basic', 'plus', 'pro' );
	public $notification_category = 'alert';
	public $notification_icon = 'warning';
	public $notification_priority = 1;

	public function prepare_notification_data( $notification ) {

		$report = $this->get_report( 'overview', $this->report_start_from, $this->report_end_to );

		$sessions = isset( $report['data']['infobox']['sessions']['value'] ) ? $report['data']['infobox']['sessions']['value'] : 0;

		if ( $sessions > 0 ) {
			return false;
		}

		$notification['title']   = __( "No traffic received in the past 7 days", "exactmetrics-premium" );
		$notification['content'] = __( "Your site hasn't received any traffic in the past 7 days. If this is unexpected, please contact ExactMetrics so we can make sure tracking is setup properly.", 'exactmetrics-premium' );
		$notification['btns']    = array(
			'contact_support' => array(
				'url'         => 'mailto:support@exactmetrics.com',
				'text'        => __( 'Contact Support', 'exactmetrics-premium' ),
				'is_external' => true
			)
		);

		return $notification;
	}
}

// Initialize class
new ExactMetrics_Notification_No_Traffic();
