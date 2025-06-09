<?php

/**
 * Add notification when pro version activated & there is no container id in settings
 * Recurrence: 30 Days
 *
 * @since 7.12.3
 */
final class ExactMetrics_Notification_To_Setup_Google_Optimize extends ExactMetrics_Notification_Event {

	public $notification_id = 'exactmetrics_notification_to_setup_google_optimize';
	public $notification_interval = 30; // in days
	public $notification_type = array( 'master', 'pro' );
	public $notification_category = 'insight';
	public $notification_priority = 3;

	/**
	 * Build Notification
	 *
	 * @return array $notification notification is ready to add
	 *
	 * @since 7.12.3
	 */
	public function prepare_notification_data( $notification ) {
		$goptimize_container = exactmetrics_get_option( 'goptimize_container', '' );

		if ( empty( $goptimize_container ) ) {
			$settings_url          = is_network_admin() ? $this->get_view_url( 'exactmetrics-settings-block-google-optimize', 'exactmetrics_network', 'conversions' ) : $this->get_view_url( 'exactmetrics-settings-block-google-optimize', 'exactmetrics_settings', 'conversions' );
			$notification['title'] = __( 'Set Up Google Optimize', 'exactmetrics-premium' );
			// Translators: setup google optimize notification content
			$notification['content'] = sprintf( __( 'Want to conduct A/B tests on your WordPress site? You can use Google Optimize to conduct experiments to see what works best on your site. With ExactMetrics, you can easily connect Google Optimize with Google Analytics. Read %sour article%s for step-by-step information on how set up google optimize with ExactMetrics.', 'exactmetrics-premium' ), '<a href="' . $this->build_external_link( 'https://www.exactmetrics.com/docs/how-to-set-up-google-optimize/' ) . '">', '</a>', '<a href="' . $settings_url . '">', '</a>' );
			$notification['btns']    = array(
				"read_more"             => array(
					'url'         => $this->build_external_link( 'https://www.exactmetrics.com/docs/how-to-set-up-google-optimize/' ),
					'text'        => __( 'Read More', 'exactmetrics-premium' ),
					'is_external' => true,
				),
				"setup_google_optimize" => array(
					'url'  => $settings_url,
					'text' => __( 'Set Up Google Optimize', 'exactmetrics-premium' )
				),
			);

			return $notification;
		}

		return false;
	}

}

// initialize the class
new ExactMetrics_Notification_To_Setup_Google_Optimize();
