<?php
/**
 * Install the custom DB tables.
 *
 * @package exactmetrics-page-insights
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ExactMetrics_Page_Insights_Install
 */
class ExactMetrics_Page_Insights_Install {

	/**
	 * Install and handle multisite.
	 *
	 * @param bool $network_wide Whether to enable the plugin for all sites in the network.
	 */
	public static function handle_install( $network_wide = false ) {

		if ( is_multisite() && $network_wide ) {
			// Loop through network sites and install the tables. Keep compatibility for WP versions older than 4.6.
			if ( function_exists( 'get_sites' ) && class_exists( 'WP_Site_Query' ) ) {

				$sites = get_sites();

				foreach ( $sites as $site ) {

					switch_to_blog( $site->blog_id );
					self::install();
					restore_current_blog();
				}
			} else {
				$sites = wp_get_sites( array( 'limit' => 0 ) );

				foreach ( $sites as $site ) {

					switch_to_blog( $site['blog_id'] );
					self::install();
					restore_current_blog();
				}
			}
		} else {
			self::install();
		}
	}

	/**
	 * Install the DB.
	 */
	public static function install() {

		// Check if the install is not already running.
		if ( 'yes' === get_transient( 'exactmetrics_page_insights_install' ) ) {
			return;
		}

		// Set a transient to prevent running this multiple times before it's ready.
		set_transient( 'exactmetrics_page_insights_install', 'yes', MINUTE_IN_SECONDS * 10 );

		self::create_tables();

		delete_transient( 'exactmetrics_page_insights_install' );

	}

	/**
	 * Create tables for new blogs if the plugin is network activated.
	 *
	 * @param int $blog_id Site ID.
	 * @param int $user_id User ID.
	 * @param string $domain Site domain.
	 * @param string $path Site path.
	 * @param int $network_id Network ID. Only relevant on multi-network installations.
	 * @param array $meta Meta data. Used to set initial site options.
	 */
	public static function install_new_blog( $blog_id, $user_id, $domain, $path, $network_id, $meta ) {
		if ( is_plugin_active_for_network( plugin_basename( ExactMetrics_Page_Insights::get_instance()->file ) ) ) {

			switch_to_blog( $blog_id );
			self::install();
			restore_current_blog();

		}
	}

	/**
	 * Create the custom tables needed
	 */
	public static function create_tables() {

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( self::get_schema() );
	}

	/**
	 * Get Table schema.
	 *
	 * @return string
	 */
	public static function get_schema() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$tables = "
CREATE TABLE {$wpdb->prefix}exactmetrics_pageinsights_cache (
  request_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  path VARCHAR(2048) NOT NULL,
  value longtext NOT NULL,
  expiry datetime NOT NULL,
  created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (request_id),
  UNIQUE KEY request_id (request_id)
) $collate;";

		return $tables;

	}

	/**
	 * Handler for the uninstall function.
	 */
	public static function handle_uninstall() {

		if ( is_multisite() ) {
			if ( function_exists( 'get_sites' ) && class_exists( 'WP_Site_Query' ) ) {

				$sites = get_sites();

				foreach ( $sites as $site ) {

					switch_to_blog( $site->blog_id );
					self::uninstall();
					restore_current_blog();
				}
			} else {
				$sites = wp_get_sites( array( 'limit' => 0 ) );

				foreach ( $sites as $site ) {

					switch_to_blog( $site['blog_id'] );
					self::uninstall();
					restore_current_blog();
				}
			}
		} else {
			self::uninstall();
		}

	}

	/**
	 * Trigger for uninstall.
	 */
	public static function uninstall() {
		global $wpdb;

		// Delete the cache table.
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'exactmetrics_pageinsights_cache' );

	}

}
