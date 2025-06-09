<?php

/**
 * Add notification when pro version is activated and custom dimension for author tracking is not setup
 * Recurrence: 30 Days
 *
 * @since 7.12.3
 */
final class ExactMetrics_Notification_To_Enable_Author_Tracking extends ExactMetrics_Notification_Event {

	public $notification_id       = 'exactmetrics_notification_to_enable_author_tracking';
	public $notification_interval = 30; // in days
	public $notification_type     = array( 'master', 'pro' );
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
		$custom_dimensions          = exactmetrics_get_option( 'custom_dimensions', array() );
		$is_author_tracking_enabled = false;
		$is_em                      = function_exists( 'ExactMetrics' );

		if ( is_array( $custom_dimensions ) && ! empty( $custom_dimensions ) ) {
			foreach ( $custom_dimensions as $custom_dimension ) {
				if ( isset( $custom_dimension['type'] ) && 'author' === $custom_dimension['type'] ) {
					$is_author_tracking_enabled = true;
					break;
				}
			}
		}

		if ( false === $is_author_tracking_enabled || ( is_array( $custom_dimensions ) && empty( $custom_dimensions ) ) ) {

			$notification['title'] = __( 'Author Tracking is Not Enabled', 'exactmetrics-premium' );
			// Translators: author tracking notification content
			if ( ! $is_em ) {
				$notification['content'] = sprintf( __( 'Author tracking in Google Analytics gives you valuable insights into the performance of your blog authors. With author tracking, you can discover the most popular author of your blog, sort page views for articles by author, see which author’s posts keep visitors on your site by analyzing the bounce rate, get instant WordPress author stats for optimization. <br><br>%1$sCheck this article%2$s to set up author tracking.', 'exactmetrics-premium' ), '<a href="' . $this->build_external_link( 'https://www.exactmetrics.com/the-beginners-guide-to-custom-dimensions-in-google-analytics/#:~:text=How%20to%20Track%20Author%20Using%20ExactMetrics&text=ExactMetrics%20automatically%20tracks%20different%20authors,most%20traffic%20on%20your%20site.' ) . '" target="_blank">', '</a>' );
			} else {
				$notification['content'] = esc_html__( 'Author tracking in Google Analytics gives you valuable insights into the performance of your blog authors. With author tracking, you can discover the most popular author of your blog, sort page views for articles by author, see which author’s posts keep visitors on your site by analyzing the bounce rate, get instant WordPress author stats for optimization.', 'exactmetrics-premium' );
			}

			if ( ! $is_em ) {
				$notification['btns'] = array(
					'learn_more' => array(
						'url'         => $this->build_external_link( 'https://www.exactmetrics.com/the-beginners-guide-to-custom-dimensions-in-google-analytics/#:~:text=How%20to%20Track%20Author%20Using%20ExactMetrics&text=ExactMetrics%20automatically%20tracks%20different%20authors,most%20traffic%20on%20your%20site.' ),
						'text'        => __( 'Learn More', 'exactmetrics-premium' ),
						'is_external' => true,
					),
				);
			}

			return $notification;
		}

		return false;
	}

}

// initialize the class
new ExactMetrics_Notification_To_Enable_Author_Tracking();