<?php

/**
 * Add notification when pro version activated & forms tracking option is disabled.
 * Recurrence: 20 Days
 *
 * @since 7.12.3
 */
final class ExactMetrics_Notification_To_Enable_Custom_Dimensions extends ExactMetrics_Notification_Event {

	public $notification_id = 'exactmetrics_notification_to_enable_custom_dimensions';
	public $notification_interval = 20; // in days
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

		$dimensions_addon_active = class_exists( 'ExactMetrics_Dimensions' );

		if ( ! $dimensions_addon_active ) {

			$notification['title'] = __( 'Enable Custom Dimensions', 'exactmetrics-premium' );
			// Translators: form submission notification content
			$notification['content'] = __( 'Enable Custom Dimensions to track logged in users, determine when is your best time to post, measure if your SEO strategy is working, and find your most popular author.', 'exactmetrics-premium' );

			$notification['btns'] = array(
				"activate_addon" => array(
					'url'  => $this->get_view_url( 'exactmetrics-addon-dimensions', 'exactmetrics_settings', 'addons' ),
					'text' => __( 'Activate Addon', 'exactmetrics-premium' ),
				),
			);

			return $notification;
		}

		return false;
	}

}

// initialize the class
new ExactMetrics_Notification_To_Enable_Custom_Dimensions();
