<?php
/**
 * Plugin Name:       ExactMetrics - Google Optimize Addon
 * Plugin URI:        https://www.exactmetrics.com
 * Description:       Integrates Google Optimize with ExactMetrics
 * Author:            ExactMetrics Team
 * Author URI:        https://www.exactmetrics.com
 * Version:           1.2.2
 * Requires at least: 4.8.0
 * Requires PHP:      5.5
 * Text Domain:       exactmetrics-google-optimize
 * Domain Path:       languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 *
 * @since 1.0.0
 *
 * @package ExactMetrics_Google_Optimize
 * @author  Chris Christoff
 */
class ExactMetrics_Google_Optimize {
	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = '1.2.2';

	/**
	 * The name of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_name = 'ExactMetrics Google Optimize';

	/**
	 * Unique plugin slug identifier.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_slug = 'exactmetrics-google-optimize';

	/**
	 * Plugin file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $file;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->file = __FILE__;

		if ( ! $this->check_compatibility() ) {
			return;
		}

		// Load the plugin textdomain.
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

		// Load the updater
		add_action( 'exactmetrics_updater', array( $this, 'updater' ) );

		// Load the plugin.
		add_action( 'exactmetrics_load_plugins', array( $this, 'init' ), 99 );

		if ( ! defined( 'EXACTMETRICS_PRO_VERSION' ) ) {
			// Make sure plugin is listed in Auto-update Disabled view
			add_filter( 'auto_update_plugin', array( $this, 'disable_auto_update' ), 10, 2 );

			// Display call-to-action to get Pro in order to enable auto-update
			add_filter( 'plugin_auto_update_setting_html', array( $this, 'modify_autoupdater_setting_html' ), 11, 2 );
		}
	}

	/**
	 * Check compatibility with PHP and WP, and display notices if necessary
	 *
	 * @return bool
	 * @since 1.0.3
	 */
	private function check_compatibility() {
		if ( defined( 'EXACTMETRICS_FORCE_ACTIVATION' ) && EXACTMETRICS_FORCE_ACTIVATION ) {
			return true;
		}

		require_once plugin_dir_path( __FILE__ ) . 'includes/compatibility-check.php';
		$compatibility = ExactMetrics_Google_Optimize_Compatibility_Check::get_instance();
		$compatibility->maybe_display_notice();

		return $compatibility->is_php_compatible() && $compatibility->is_wp_compatible();
	}

	/**
	 * Loads the plugin textdomain for translation.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( $this->plugin_slug, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Loads the plugin into WordPress.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		if ( ! defined( 'EXACTMETRICS_PRO_VERSION' ) ) {
			// admin notice, MI not installed
			add_action( 'admin_notices', array( self::$instance, 'requires_exactmetrics' ) );

			return;
		}

		if ( ! defined( 'EXACTMETRICS_GOOGLE_OPTIMIZE_VERSION' ) ) {
			define( 'EXACTMETRICS_GOOGLE_OPTIMIZE_VERSION', $this->version );
		}

		// Load frontend components.
		$this->require_frontend();

		// Load admin files
		$this->require_admin();
	}

	/**
	 * Initializes the addon updater.
	 *
	 * @param string $key The user license key.
	 *
	 * @since 1.0.0
	 *
	 */
	function updater( $key ) {
		$args = array(
			'plugin_name' => $this->plugin_name,
			'plugin_slug' => $this->plugin_slug,
			'plugin_path' => plugin_basename( __FILE__ ),
			'plugin_url'  => trailingslashit( WP_PLUGIN_URL ) . $this->plugin_slug,
			'remote_url'  => 'https://www.exactmetrics.com/',
			'version'     => $this->version,
			'key'         => $key
		);

		$updater = new ExactMetrics_Updater( $args );
	}

	/**
	 * Display ExactMetrics Pro CTA on Plugins -> autoupdater setting column
	 *
	 * @param string $html
	 * @param string $plugin_file
	 *
	 * @return string
	 */
	public function modify_autoupdater_setting_html( $html, $plugin_file ) {
		if ( plugin_basename( __FILE__ ) === $plugin_file &&
			 // If main plugin (free) happens to be enabled and already takes care of this, then bail
			 ! apply_filters( "exactmetrics_is_autoupdate_setting_html_filtered_${plugin_file}", false )
		) {
			$html = sprintf(
				'<a href="%s">%s</a>',
				'https://www.exactmetrics.com/docs/go-lite-pro/?utm_source=liteplugin&utm_medium=plugins-autoupdate&utm_campaign=upgrade-to-autoupdate&utm_content=exactmetrics-google-optimize',
				__( 'Enable the ExactMetrics PRO plugin to manage auto-updates', 'exactmetrics-google-optimize' )
			);
		}

		return $html;
	}

	/**
	 * Disable auto-update.
	 *
	 * @param $update
	 * @param $item
	 *
	 * @return bool
	 */
	public function disable_auto_update( $update, $item ) {
		// If this is multisite and is not on the main site, return early.
		if ( is_multisite() && ! is_main_site() ) {
			return $update;
		}

		if ( isset( $item->id ) && plugin_basename( __FILE__ ) === $item->id ) {
			return false;
		}

		return $update;
	}

	/**
	 * Loads all frontend files into scope.
	 *
	 * @since 1.0.0
	 */
	public function require_frontend() {
		require plugin_dir_path( __FILE__ ) . 'includes/frontend/tracking.php';
	}

	/**
	 * Loads all admin files
	 *
	 * @access admin
	 * @return    void
	 * @since 3.0.0
	 *
	 */
	public function require_admin() {

		if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
			require plugin_dir_path( __FILE__ ) . 'includes/admin/ajax.php';
		}

	}

	/**
	 * Output a nag notice if the user does not have MI installed
	 *
	 * @access public
	 * @return    void
	 * @since 1.0.0
	 *
	 */
	public function requires_exactmetrics() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'Please install ExactMetrics Pro to use the ExactMetrics Google Optimize addon', 'exactmetrics-google-optimize' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Output a nag notice if the user does not have MI version installed
	 *
	 * @access public
	 * @return    void
	 * @since 1.0.0
	 *
	 */
	public function requires_exactmetrics_version() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'Please install or update ExactMetrics Pro version 7.4 or newer to use the ExactMetrics Google Optimize addon', 'exactmetrics-google-optimize' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @return object The ExactMetrics_Google_Optimize object.
	 * @since 1.0.0
	 *
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof ExactMetrics_Google_Optimize ) ) {
			self::$instance = new ExactMetrics_Google_Optimize();
		}

		return self::$instance;
	}
}

// Load the main plugin class.
$exactmetrics_google_optimize = ExactMetrics_Google_Optimize::get_instance();
