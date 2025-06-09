<?php
/**
 * Plugin Name:       ExactMetrics - Page Insights Addon
 * Plugin URI:        https://www.exactmetrics.com
 * Description:       Adds individual page insights directly in the WordPress admin.
 * Author:            ExactMetrics Team
 * Author URI:        https://www.exactmetrics.com
 * Version:           1.2.4
 * Requires at least: 4.8.0
 * Requires PHP:      5.5
 * Text Domain:       exactmetrics-page-insights
 * Domain Path:       languages
 *
 * @package exactmetrics-page-insights
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ExactMetrics_Posts
 */
class ExactMetrics_Page_Insights {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var ExactMetrics_Page_Insights
	 */
	public static $instance;

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = '1.2.4';

	/**
	 * The name of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_name = 'ExactMetrics Page Insights';

	/**
	 * Unique plugin slug identifier.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_slug = 'exactmetrics-page-insights';

	/**
	 * Plugin file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $file;

	/**
	 * ExactMetrics_Posts constructor.
	 */
	private function __construct() {
		$this->file = __FILE__;

		if ( ! $this->check_compatibility() ) {
			return;
		}

		// Load the plugin textdomain.
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

		// Load the updater.
		add_action( 'exactmetrics_updater', array( $this, 'updater' ) );

		// Load the plugin.
		add_action( 'exactmetrics_load_plugins', array( $this, 'init' ), 99 );

		if ( ! defined( 'EXACTMETRICS_PRO_VERSION' ) ) {
			// Make sure plugin is listed in Auto-update Disabled view
			add_filter( 'auto_update_plugin', array( $this, 'disable_auto_update' ), 10, 2 );

			// Display call-to-action to get Pro in order to enable auto-update
			add_filter( 'plugin_auto_update_setting_html', array( $this, 'modify_autoupdater_setting_html' ), 11, 2 );
		}

		$this->install_hooks();
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @return ExactMetrics_Page_Insights The ExactMetrics_Posts object.
	 * @since 1.0.0
	 *
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof ExactMetrics_Page_Insights ) ) {
			self::$instance = new ExactMetrics_Page_Insights();
		}

		return self::$instance;
	}

	/**
	 * Check compatibility with PHP and WP, and display notices if necessary
	 *
	 * @return bool
	 * @since 1.0.4
	 */
	private function check_compatibility() {
		if ( defined( 'EXACTMETRICS_FORCE_ACTIVATION' ) && EXACTMETRICS_FORCE_ACTIVATION ) {
			return true;
		}

		require_once plugin_dir_path( __FILE__ ) . 'includes/class-exactmetrics-page-insights-compatibility-check.php';
		$compatibility = ExactMetrics_Page_Insights_Compatibility_Check::get_instance();
		$compatibility->maybe_display_notice();

		return $compatibility->is_php_compatible() && $compatibility->is_wp_compatible();
	}

	/**
	 * Loads the plugin textdomain for translation.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( $this->plugin_slug, false, dirname( plugin_basename( $this->file ) ) . '/languages/' );
	}

	/**
	 * Loads the plugin into WordPress.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		if ( ! defined( 'EXACTMETRICS_PRO_VERSION' ) ) {
			// admin notice, MI not installed.
			add_action( 'admin_notices', array( self::$instance, 'requires_exactmetrics' ) );

			return;
		}

		if ( version_compare( EXACTMETRICS_VERSION, '7.5.1', '<' ) ) {
			// ExactMetrics version not supported.
			add_action( 'admin_notices', array( self::$instance, 'requires_exactmetrics_version' ) );

			return;
		}

		if ( ! defined( 'EXACTMETRICS_PAGE_INSIGHTS_ADDON_PLUGIN_URL' ) ) {
			define( 'EXACTMETRICS_PAGE_INSIGHTS_ADDON_PLUGIN_URL', plugin_dir_url( $this->file ) );
		}

		// Load admin only components.
		if ( is_admin() ) {
			$this->require_admin();
		}

		$this->require_frontend();
	}

	/**
	 * Loads all admin related files into scope.
	 *
	 * @since 1.0.0
	 */
	public function require_admin() {
		// The caching class.
		require_once plugin_dir_path( $this->file ) . 'includes/class-exactmetrics-page-insights-cache.php';
		// Load the background data fetcher.
		require_once plugin_dir_path( $this->file ) . 'includes/class-exactmetrics-page-insights-background.php';

		// Load the admin interface.
		require_once plugin_dir_path( $this->file ) . 'includes/admin/class-exactmetrics-page-insights-admin.php';
		new ExactMetrics_Page_Insights_Admin();

		// Load the report.
		require_once plugin_dir_path( $this->file ) . 'includes/admin/reports/class-exactmetrics-report-page-insights.php';

	}

	/**
	 * Non-admin includes.
	 */
	public function require_frontend() {

		require_once plugin_dir_path( $this->file ) . 'includes/class-exactmetrics-page-insights-ajax.php';
		new ExactMetrics_Page_Insights_Ajax();
	}

	/**
	 * Initializes the addon updater.
	 *
	 * @param string $key The user license key.
	 *
	 * @since 1.0.0
	 *
	 */
	public function updater( $key ) {
		$args = array(
			'plugin_name' => $this->plugin_name,
			'plugin_slug' => $this->plugin_slug,
			'plugin_path' => plugin_basename( __FILE__ ),
			'plugin_url'  => trailingslashit( WP_PLUGIN_URL ) . $this->plugin_slug,
			'remote_url'  => 'https://www.exactmetrics.com/',
			'version'     => $this->version,
			'key'         => $key,
		);

		new ExactMetrics_Updater( $args );
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
				'https://www.exactmetrics.com/docs/go-lite-pro/?utm_source=liteplugin&utm_medium=plugins-autoupdate&utm_campaign=upgrade-to-autoupdate&utm_content=exactmetrics-page-insights',
				__( 'Enable the ExactMetrics PRO plugin to manage auto-updates', 'exactmetrics-page-insights' )
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
	 * Add the install hooks.
	 */
	public function install_hooks() {

		require_once plugin_dir_path( $this->file ) . 'includes/class-exactmetrics-page-insights-install.php';

		register_activation_hook( $this->file, array(
			'ExactMetrics_Page_Insights_Install',
			'handle_install',
		) );

		register_uninstall_hook( $this->file, array(
			'ExactMetrics_Page_Insights_Install',
			'handle_uninstall',
		) );

		// Add tables to new blogs.
		add_action( 'wpmu_new_blog', array( 'ExactMetrics_Page_Insights_Install', 'install_new_blog' ), 10, 6 );
	}

	/**
	 * Output a nag notice if the user does not have MI installed
	 */
	public function requires_exactmetrics() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'Please install ExactMetrics Pro to use the ExactMetrics Page Insights addon', 'exactmetrics-page-insights' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Output a nag notice if the user does not have MI version installed
	 */
	public function requires_exactmetrics_version() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'Please install or update ExactMetrics Pro with version 7.5.1 or newer to use the ExactMetrics Page Insights addon', 'exactmetrics-page-insights' ); ?></p>
		</div>
		<?php
	}
}

$exactmetrics_page_insights = ExactMetrics_Page_Insights::get_instance();
