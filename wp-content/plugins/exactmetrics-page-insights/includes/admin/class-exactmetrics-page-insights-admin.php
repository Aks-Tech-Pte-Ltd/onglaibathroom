<?php
/**
 * Load the individual Posts reports in the admin.
 *
 * @package exactmetrics-page-insights
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ExactMetrics_Page_Insights_Admin
 */
final class ExactMetrics_Page_Insights_Admin {

	/**
	 * The post types for which the column is loaded.
	 *
	 * @var array
	 */
	public $post_types;

	/**
	 * ExactMetrics_Page_Insights_Admin constructor.
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'add_admin_column_for_public_post_types' ) );

		add_action( 'manage_posts_custom_column', array( $this, 'custom_column_content' ), 10, 2 );

		add_action( 'manage_pages_custom_column', array( $this, 'custom_column_content' ), 10, 2 );

		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'load_metabox_scripts' ) );

		add_action( 'admin_footer', array( $this, 'add_reports_markup' ) );

		// Clear the cache when the profile is updated.
		add_action( 'update_option_exactmetrics_site_profile', array( $this, 'clear_cache' ) );
		add_action( 'update_site_option_exactmetrics_network_profile', array( $this, 'clear_network_cache' ) );

		add_action( 'exactmetrics_after_exclude_metabox', array(
			$this,
			'print_page_insights_metabox_html'
		), 10, 2 );
	}

	/**
	 * Grab only the public post types and add the insights column to their manage screen.
	 */
	public function add_admin_column_for_public_post_types() {

		$post_types = $this->get_post_types();

		foreach ( $post_types as $post_type ) {
			add_filter( 'manage_' . $post_type . '_posts_columns', array( $this, 'add_posts_table_column' ), 150 );
		}
	}


	/**
	 * Grab the post types for which we will add the column.
	 *
	 * @return array
	 */
	public function get_post_types() {

		if ( ! isset( $this->post_types ) ) {
			$post_types_args = array(
				'public' => true,
			);
			$post_types      = get_post_types( $post_types_args );

			// Allow plugins to exclude post types from having the column added.
			$this->post_types = apply_filters( 'exactmetrics_posts_post_types_admin_column', $post_types );
		}

		return $this->post_types;

	}

	/**
	 * Add custom column to manage posts/pages table.
	 *
	 * @param array $columns The current columns.
	 *
	 * @return array
	 */
	public function add_posts_table_column( $columns ) {

		if ( ! isset( $columns['exactmetrics_reports'] ) ) {
			if ( current_user_can( 'exactmetrics_view_dashboard' ) || apply_filters( 'exactmetrics_pageinsights_author_can_view', false ) ) {
				$columns['exactmetrics_reports'] = esc_html__( 'Insights', 'exactmetrics-pageinsights' );
			}
		}

		return $columns;

	}

	/**
	 * Load the reports icon in the custom column.
	 *
	 * @param string $column The column key.
	 * @param int $post_id The current post id.
	 */
	public function custom_column_content( $column, $post_id ) {

		// Only use output for the ExactMetrics column.
		if ( 'exactmetrics_reports' === $column ) {
			$author_can_view = current_user_can( 'edit_post', $post_id ) && apply_filters( 'exactmetrics_pageinsights_author_can_view', false );
			// Don't show the insights button if the current user can't access the data.
			if ( current_user_can( 'exactmetrics_view_dashboard' ) || $author_can_view ) {
				$post_title = get_the_title( $post_id );
				echo '<button class="exactmetrics-reports-loader" type="button" data-post_id="' . esc_attr( $post_id ) . '" data-title="' . esc_attr( $post_title ) . '">';
				// Translators: %s is the post/page title.
				echo '<span class="screen-reader-text">' . sprintf( esc_html__( 'View Reports for “%s“', 'exactmetrics-pageinsights' ), esc_html( $post_title ) ) . '</span>';
				echo '</button>';
			}
		}
	}

	/**
	 * Check if we should load scripts and templates in current screen.
	 *
	 * @return bool
	 */
	public function should_load_in_current_screen() {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();

		// Check if we need to load scripts on current page.
		if ( ! isset( $screen->post_type ) || ! isset( $screen->base ) || ! in_array( $screen->post_type, $this->get_post_types(), true ) || 'edit' !== $screen->base ) {
			return false;
		}

		return true;
	}

	/**
	 * Load the posts reports scripts.
	 *
	 * @param string $hook The current admin page hook.
	 *
	 * @return bool
	 */
	public function load_scripts( $hook ) {

		if ( ! $this->should_load_in_current_screen() ) {
			return false;
		}

		wp_enqueue_style( 'exactmetrics_page_insights_styles', EXACTMETRICS_PAGE_INSIGHTS_ADDON_PLUGIN_URL . 'assets/css/page-insights-reports.css', array(), exactmetrics_get_asset_version() );

		wp_enqueue_script( 'exactmetrics_page_insights_script', EXACTMETRICS_PAGE_INSIGHTS_ADDON_PLUGIN_URL . 'assets/js/page-insights-reports.js', array( 'jquery' ), exactmetrics_get_asset_version(), true );

		wp_localize_script( 'exactmetrics_page_insights_script', 'exactmetrics_page_insights_admin', array(
			'admin_nonce'   => wp_create_nonce( 'mi-admin-nonce' ),
			'isnetwork'     => is_network_admin(),
			'timezone'      => date( 'e' ),
			'error_text'    => esc_html__( 'Error', 'exactmetrics-page-insights' ),
			'error_default' => esc_html__( 'There was an issue loading the report data. Please try again.', 'exactmetrics-page-insights' ),
		) );

	}

	public function load_metabox_scripts() {
		wp_enqueue_script( 'exactmetrics-admin-metabox-script', EXACTMETRICS_PAGE_INSIGHTS_ADDON_PLUGIN_URL . 'assets/js/metaboxes.js', array( 'jquery' ), exactmetrics_get_asset_version(), true );

		wp_localize_script( 'exactmetrics-admin-metabox-script', 'exactmetrics_page_insights_admin', array(
			'admin_nonce'   => wp_create_nonce( 'mi-admin-nonce' ),
			'isnetwork'     => is_network_admin(),
			'timezone'      => date( 'e' ),
			'error_text'    => esc_html__( 'Error', 'exactmetrics-page-insights' ),
			'error_default' => esc_html__( 'There was an issue loading the report data. Please try again.', 'exactmetrics-page-insights' ),
			'loading_txt'   => esc_html__( 'Loading Page Insights...', 'exactmetrics-page-insights' ),
		) );
	}

	/**
	 * Add the reports overlay markup to the admin footer.
	 *
	 * @return bool
	 */
	public function add_reports_markup() {

		if ( ! $this->should_load_in_current_screen() ) {
			return false;
		}

		?>
		<div class="exactmetrics-reports-overlay">
			<div class="exactmetrics-reports-overlay-inner">
				<div class="exactmetrics-reports-overlay-header">
					<div class="exactmetrics-reports-overlay-logo">
						<img
							src="<?php echo esc_url( EXACTMETRICS_PAGE_INSIGHTS_ADDON_PLUGIN_URL ); ?>/assets/img/ExactMetrics-Logo.png"
							srcset="<?php echo esc_url( EXACTMETRICS_PAGE_INSIGHTS_ADDON_PLUGIN_URL ); ?>/assets/img/ExactMetrics-Logo@2x.png 2x"
							alt="ExactMetrics"/>
					</div>
					<h2 class="exactmetrics-reports-overlay-title"><?php esc_html_e( 'Page Insights for:', 'exactmetrics-page-insights' ); ?>
						<span class="exactmetrics-reports-overlay-title-text"></span></h2>
					<button type="button" class="exactmetrics-close-overlay">
						<span class="dashicons dashicons-no-alt"></span>
						<span
							class="screen-reader-text"><?php esc_html_e( 'Close reports overlay', 'exactmetrics-page-insights' ); ?></span>
					</button>
				</div>
				<div class="exactmetrics-reports-overlay-controls">
					<select id="exactmetrics-report-interval">
						<option
							value="30days"><?php esc_html_e( 'Last 30 Days', 'exactmetrics-page-insights' ); ?></option>
						<option
							value="yesterday"><?php esc_html_e( 'Yesterday', 'exactmetrics-page-insights' ); ?></option>
					</select>
				</div>
				<div class="exactmetrics-reports-overlay-content">
					<div class="exactmetrics-reports-overlay-loading"></div>
				</div>
			</div>
		</div>
		<script id="exactmetrics-pageinsights-error-template" type="text/html">
			<div class="exactmetrics-pageinsights-error">
				<div class="mi-pageinsights-icon mi-pageinsights-error mi-pageinsights-animate-error-icon"
					 style="display: flex;">
					<span class="mi-pageinsights-x-mark"><span class="mi-pageinsights-x-mark-line-left"></span><span
							class="mi-pageinsights-x-mark-line-right"></span></span>
				</div>
				<h2 class="exactmetrics-pageinsights-error-title"></h2>
				<div class="exactmetrics-pageinsights-error-content"></div>
				<div class="exactmetrics-pageinsights-error-footer"></div>
			</div>
		</script>
		<?php

	}

	/**
	 * When this is called, clears the cache of all the sites in the network.
	 */
	public function clear_network_cache() {

		if ( function_exists( 'get_sites' ) && class_exists( 'WP_Site_Query' ) ) {

			$sites = get_sites();

			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				ExactMetrics_Page_Insights_Cache::get_instance()->clear_cache();
				ExactMetrics_Page_Insights_Cache::destroy(); // This is needed to use the right wpdb instance.
				restore_current_blog();
			}
		} else {
			$sites = wp_get_sites( array( 'limit' => 0 ) );

			foreach ( $sites as $site ) {

				switch_to_blog( $site['blog_id'] );
				ExactMetrics_Page_Insights_Cache::get_instance()->clear_cache();
				ExactMetrics_Page_Insights_Cache::destroy(); // This is needed to use the right wpdb instance.
				restore_current_blog();
			}
		}

	}

	/**
	 * Clear the cache for the current site.
	 */
	public function clear_cache() {
		ExactMetrics_Page_Insights_Cache::get_instance()->clear_cache();
	}

	private function show_pending_message( $post ) {
		if ( ! $post ) {
			return false;
		}

		$post_status = get_post_status( $post );
		$hours       = ( current_time( 'U' ) - get_post_timestamp( $post ) ) / ( 60 * 60 );

		return 'draft' === $post_status || 24 > $hours;
	}

	public function print_page_insights_metabox_html( $skipped, $post ) {
		if ( $skipped ) {
			return;
		}
		$show_pending_message = $this->show_pending_message( $post );
		?>
		<div class="exactmetrics-metabox pro" id="exactmetrics-metabox-page-insights"
			 <?php if ( $show_pending_message ){ ?>data-skip-requests="1"<?php } ?>>
			<a class="button" href="#" id="exactmetrics_show_page_insights">
				<?php esc_html_e( 'Show Page Insights', 'exactmetrics-page-insights' ); ?>
			</a>

			<div id="exactmetrics-page-insights-content">
				<div class="exactmetrics-page-insights__tabs">
					<a href="#" class="exactmetrics-page-insights__tabs-tab active"
					   data-tab="exactmetrics-last-30-days-content" data-interval="30days">
						<?php esc_html_e( 'Last 30 days', 'exactmetrics-page-insights' ); ?>
					</a>
					<a href="#" class="exactmetrics-page-insights__tabs-tab"
					   data-tab="exactmetrics-yesterday-content" data-interval="yesterday">
						<?php esc_html_e( 'Yesterday', 'exactmetrics-page-insights' ); ?>
					</a>
				</div>
				<div class="exactmetrics-page-insights-tabs-content">
					<div class="exactmetrics-page-insights-tabs-content__tab active"
						 id="exactmetrics-last-30-days-content">
						<div class="exactmetrics-page-insights-tabs-content__tab-items">
							<div class="exactmetrics-page-insights-tabs-content__tab-item">
								<div class="exactmetrics-page-insights-tabs-content__tab-item__result">
									<span data-exactmetrics-metric="timeonpage">---</span>
								</div>
								<div class="exactmetrics-page-insights-tabs-content__tab-item__title">
									<?php esc_html_e( 'Time on Page', 'exactmetrics-page-insights' ); ?>
								</div>
							</div>
							<div class="exactmetrics-page-insights-tabs-content__tab-item">
								<div class="exactmetrics-page-insights-tabs-content__tab-item__result">
									<span data-exactmetrics-metric="entrances">---</span>
								</div>
								<div class="exactmetrics-page-insights-tabs-content__tab-item__title">
									<?php esc_html_e( 'Entrances', 'exactmetrics-page-insights' ); ?>
								</div>
							</div>
							<div class="exactmetrics-page-insights-tabs-content__tab-item">
								<div class="exactmetrics-page-insights-tabs-content__tab-item__result">
									<span data-exactmetrics-metric="pageviews">---</span>
								</div>
								<div class="exactmetrics-page-insights-tabs-content__tab-item__title">
									<?php esc_html_e( 'Page Views', 'exactmetrics-page-insights' ); ?>
								</div>
							</div>
							<div class="exactmetrics-page-insights-tabs-content__tab-item">
								<div class="exactmetrics-page-insights-tabs-content__tab-item__result">
									<span data-exactmetrics-metric="exits">---</span>
								</div>
								<div class="exactmetrics-page-insights-tabs-content__tab-item__title">
									<?php esc_html_e( 'Exits', 'exactmetrics-page-insights' ); ?>
								</div>
							</div>

						</div>
					</div>
					<div class="exactmetrics-page-insights-tabs-content__tab" id="exactmetrics-yesterday-content">
						<div class="exactmetrics-page-insights-tabs-content__tab-items">
							<div class="exactmetrics-page-insights-tabs-content__tab-item">
								<div class="exactmetrics-page-insights-tabs-content__tab-item__result">
									<span data-exactmetrics-metric="timeonpage">---</span>
								</div>
								<div class="exactmetrics-page-insights-tabs-content__tab-item__title">
									<?php esc_html_e( 'Time on Page', 'exactmetrics-page-insights' ); ?>
								</div>
							</div>
							<div class="exactmetrics-page-insights-tabs-content__tab-item">
								<div class="exactmetrics-page-insights-tabs-content__tab-item__result">
									<span data-exactmetrics-metric="entrances">---</span>
								</div>
								<div class="exactmetrics-page-insights-tabs-content__tab-item__title">
									<?php esc_html_e( 'Entrances', 'exactmetrics-page-insights' ); ?>
								</div>
							</div>
							<div class="exactmetrics-page-insights-tabs-content__tab-item">
								<div class="exactmetrics-page-insights-tabs-content__tab-item__result">
									<span data-exactmetrics-metric="pageviews">---</span>
								</div>
								<div class="exactmetrics-page-insights-tabs-content__tab-item__title">
									<?php esc_html_e( 'Page Views', 'exactmetrics-page-insights' ); ?>
								</div>
							</div>
							<div class="exactmetrics-page-insights-tabs-content__tab-item">
								<div class="exactmetrics-page-insights-tabs-content__tab-item__result">
									<span data-exactmetrics-metric="exits">---</span>
								</div>
								<div class="exactmetrics-page-insights-tabs-content__tab-item__title">
									<?php esc_html_e( 'Exits', 'exactmetrics-page-insights' ); ?>
								</div>
							</div>

						</div>
					</div>
				</div>
				<?php if ( $show_pending_message ) { ?>
					<div class="exactmetrics-insights-draft">
						<?php esc_html_e( 'Oops! There are currently no page insights available for this page. Please wait ensure the post is published, if has been just published, please wait 24 hours while data is collected.', 'exactmetrics-page-insights' ); ?>
					</div>
				<?php } ?>

				<a class="button" href="#" id="exactmetrics_hide_page_insights">
					<?php esc_html_e( 'Hide Page Insights', 'exactmetrics-page-insights' ); ?>
				</a>
			</div>

		</div>
		<?php
	}

}
