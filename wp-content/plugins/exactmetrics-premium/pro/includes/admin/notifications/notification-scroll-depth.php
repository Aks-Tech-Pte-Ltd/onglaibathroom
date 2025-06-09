<?php

/**
 * Add notification when scroll depth option is not enabled
 * Recurrence: 40 Days
 *
 * @since 7.12.3
 */
final class ExactMetrics_Notification_Scroll_Depth extends ExactMetrics_Notification_Event {

	public $notification_id       = 'exactmetrics_notification_scroll_depth';
	public $notification_interval = 40; // in days
	public $notification_type     = array( 'basic', 'master', 'plus', 'pro' );
	public $notification_category = 'insight';
	public $notification_priority = 2;

	/**
	 * Build Notification
	 *
	 * @return array $notification notification is ready to add
	 *
	 * @since 7.12.3
	 */
	public function prepare_notification_data( $notification ) {
		$data                 = array();
		$report               = $this->get_report( 'publisher', $this->report_start_from, $this->report_end_to );
		$data['scroll_depth'] = isset( $report['data']['scroll']['average'] ) ? $report['data']['scroll']['average'] : 0;
		$is_em                = function_exists( 'ExactMetrics' );

		if ( ! empty( $data ) && ( $data['scroll_depth'] > 0 && $data['scroll_depth'] < 60 ) ) {
			// Translators: scroll depth notification title
			$notification['title'] = sprintf( __( 'Your Website Scroll Depth is %s', 'exactmetrics-premium' ), $data['scroll_depth'] . '%' );
			// Translators: scroll depth notification content
			if ( ! $is_em ) {
				$notification['content'] = sprintf( __( 'Tracking user scroll activity helps you understand how engaged your users are with your content, and lets you optimize your pages for a better experience. %1$sIn this article%2$s, you can see how to use scroll tracking in WordPress with google analytics.', 'exactmetrics-premium' ), '<a href="' . $this->build_external_link( 'https://www.exactmetrics.com/how-to-use-scroll-tracking-in-wordpress-with-google-analytics/' ) . '" target="_blank">', '</a>' );
			} else {
				$notification['content'] = esc_html__( 'Tracking user scroll activity helps you understand how engaged your users are with your content, and lets you optimize your pages for a better experience.', 'exactmetrics-premium' );
			}

			$notification['btns'] = array(
				'view_report' => array(
					'url'  => $this->get_view_url( 'exactmetrics-report-scroll-depth', 'exactmetrics_reports', 'publishers' ),
					'text' => __( 'View Report', 'exactmetrics-premium' ),
				),
			);

			if ( ! $is_em ) {
				$notification['btns']['learn_more'] = array(
					'url'         => $this->build_external_link( 'https://www.exactmetrics.com/how-to-use-scroll-tracking-in-wordpress-with-google-analytics/' ),
					'text'        => __( 'Learn More', 'exactmetrics-premium' ),
					'is_external' => true,
				);
			}

			return $notification;
		}

		return false;
	}

}

// initialize the class
new ExactMetrics_Notification_Scroll_Depth();