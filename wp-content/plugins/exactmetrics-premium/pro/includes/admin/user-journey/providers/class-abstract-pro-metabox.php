<?php
/**
 * This file contains class that will be extended by other
 * providers metabox classes.
 *
 * @since 8.7.0
 *
 * @package ExactMetrics
 * @subpackage ExactMetrics_User_Journey
 */

abstract class ExactMetrics_User_Journey_Pro_Metabox {

	/**
	 * URL to assets folder.
	 *
	 * @since 8.7.0
	 *
	 * @var string
	 */
	public $assets_url = EXACTMETRICS_PLUGIN_URL . 'pro/includes/admin/user-journey/assets/';

	/**
	 * Get Currently loaded provider admin url.
	 *
	 * @return string
	 * @since 8.7.0
	 *
	 */
	abstract protected function get_provider_admin_url();

	/**
	 * Metabox Title.
	 *
	 * @return void
	 * @since 8.7.0
	 *
	 */
	protected function metabox_title() {
		return '';
	}

	/**
	 * Contains HTML to display inside the metabox
	 *
	 * @return void
	 * @since 8.5.0
	 *
	 */
	public function metabox_html() {
		?>
		<!-- User Journey metabox -->
		<?php $this->metabox_title(); ?>
		<div id="exactmetrics-user-journey-pro-metabox-container">
			<div class="exactmetrics-pro-uj-backdrop-pic"
				 style="background-image: url( '<?php echo esc_url( $this->assets_url ); ?>img/user-journey-backdrop.png' )"></div>
			<div id="exactmetrics-pro-entry-user-journey" class="postbox">
				<div class="exactmetrics-pro-uj-container desktop">
					<div class="exactmetrics-pro-uj-modal-content">
						<div class="exactmetrics-pro-modal-left">
							<h4><?php esc_html_e( 'Activate User Journey Addon', 'exactmetrics' ); ?></h4>
							<p><?php esc_html_e( 'Easily see which steps each customer takes before making a purchase on your store.', 'exactmetrics' ); ?></p>
							<a id="exactmetrics-activate-user-journey"
							   data-admin-url="<?php echo esc_url( $this->get_provider_admin_url() ); ?>" href="#"
							   title="" class="exactmetrics-uj-button">
								<?php esc_html_e( 'Activate', 'exactmetrics' ); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Check if an array is a valid array and not empty.
	 * This will also check if a key exists inside an array
	 * if the param is set to true.
	 *
	 * @param array $array Array to check.
	 * @param string $key Array key to check.
	 * @param boolean $check_key Wether to check the key or not.
	 *
	 * @return boolean
	 * @since 8.7.0
	 *
	 */
	public static function is_valid_array( $array, $key, $check_key = false ) {
		if ( is_array( $array ) ) {
			if ( ! empty( $array ) ) {
				if ( $check_key ) {
					if ( array_key_exists( $key, $array ) ) {
						return true;
					} else {
						return false;
					}
				}

				return true;
			} else {
				return false;
			}
		}

		return false;
	}
}
