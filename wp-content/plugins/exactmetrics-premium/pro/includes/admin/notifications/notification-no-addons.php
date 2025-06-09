<?php


class ExactMetrics_Notification_No_Addons extends ExactMetrics_Notification_Event {
	public $notification_id = 'exactmetrics_notification_no_addons';
	public $notification_interval = 7;
	public $notification_type = array( 'plus', 'pro' );
	public $notification_category = 'insight';
	public $notification_priority = 2;

	public function prepare_notification_data( $notification ) {

		$addons = exactmetrics_get_addons();
		if ( ! $addons || ! is_array( $addons['licensed'] ) ) {
			return false;
		}
		$licensed_addons = $addons['licensed'];

		$has_active_addons = false;

		foreach ( $licensed_addons as $addon ) {
			$slug            = 'exactmetrics-' . $addon->slug;
			$plugin_basename = exactmetrics_get_plugin_basename_from_slug( $slug );

			if ( is_multisite() && is_network_admin() ) {
				$active = is_plugin_active_for_network( $plugin_basename );
			} else {
				$active = is_plugin_active( $plugin_basename );
			}

			if ( $active ) {
				$has_active_addons = true;
				break;
			}
		}

		if ( ! $has_active_addons ) {
			$notification['title']   = __( "Remember to activate addons", "exactmetrics-premium" );
			$notification['content'] = __( "Get the most of your subscription - enable some addons to help with site performance, reporting, and more", 'exactmetrics-premium' );
			$notification['btns']    = array(
				'view_addons' => array(
					'url'  => $this->get_view_url( null, 'exactmetrics_settings', 'addons' ),
					'text' => __( 'View Addons', 'exactmetrics-premium' ),
				)
			);

			return $notification;
		}

		return false;
	}
}

new ExactMetrics_Notification_No_Addons();
