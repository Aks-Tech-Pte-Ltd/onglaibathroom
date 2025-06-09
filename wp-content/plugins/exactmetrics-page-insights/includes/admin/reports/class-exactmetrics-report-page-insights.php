<?php
/**
 * Handles loading the Page Insights report data from the relay and the data life-cycle.
 *
 * @package exactmetrics-page-insights
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ExactMetrics_Page_Insights_Reports
 */
final class ExactMetrics_Report_Page_Insights extends ExactMetrics_Report {

	/**
	 * The report class.
	 *
	 * @var string
	 */
	public $class = 'ExactMetrics_Report_Page_Insights';

	/**
	 * The report unique identifier.
	 *
	 * @var string
	 */
	public $name = 'pageinsights';

	/**
	 * The report version.
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * The license level needed to access the report.
	 *
	 * @var string
	 */
	public $level = 'plus';

	/**
	 * The path for which we are fetching the report.
	 *
	 * @var string
	 */
	public $path;

	/**
	 * Post ID if found.
	 *
	 * @var int|null
	 */
	private $post_id = null;

	/**
	 * Primary class constructor.
	 *
	 * @access public
	 * @since 6.0.0
	 */
	public function __construct() {
		$this->title = __( 'Page Insights', 'exactmetrics-pageinsights' );
		parent::__construct();
	}

	/**
	 * Custom get_data to handle caching specific to this report.
	 *
	 * @param array $args The arguments for grabbing the data from the Relay.
	 *
	 * @return array
	 */
	public function get_data( $args = array() ) {

		if ( ! ExactMetrics()->license->license_can( $this->level ) ) {
			return array(
				'success' => true,
				'upgrade' => true,
				'data'    => array(),
			);
		}

		$site_auth = ExactMetrics()->auth->get_viewname();
		$ms_auth   = is_multisite() && ExactMetrics()->auth->get_network_viewname();

		if ( empty( $site_auth ) && empty( $ms_auth ) ) {
			return array(
				'success' => false,
				'error'   => __( 'You must authenticate with ExactMetrics to use reports.', 'exactmetrics-pageinsights' ),
				'data'    => array(),
			);
		}

		if ( empty( $_REQUEST['post_id'] ) ) {
			return array(
				'success' => false,
				// Translators: %s is the name of the post type.
				'error'   => esc_html__( 'Missing post id parameter.', 'exactmetrics-pageinsights' ),
				'data'    => array(),
			);
		}

		// Check if a post id is sent or a path.
		if ( is_numeric( $_REQUEST['post_id'] ) ) {
			$post_id       = absint( $_REQUEST['post_id'] );
			$this->post_id = $post_id;

			self::get_path_by_post_id( $post_id );
			$author_can_view = current_user_can( 'edit_post', $post_id ) && apply_filters( 'exactmetrics_pageinsights_author_can_view', false );
			if ( ! $author_can_view && ! current_user_can( 'exactmetrics_view_dashboard' ) ) {
				return array(
					'success' => false,
					// Translators: %s is the name of the post type.
					'error'   => sprintf( esc_html__( 'You are not allowed to view reports for this %s.', 'exactmetrics-pageinsights' ), get_post_type( $post_id ) ),
					'data'    => array(),
				);
			}
		} else {
			self::set_path( sanitize_text_field( $_REQUEST['post_id'] ) );
			// Only allow editors and admins to view all reports.
			if ( ! current_user_can( 'edit_others_posts' ) ) {
				return array(
					'success' => false,
					// Translators: %s is the name of the post type.
					'error'   => esc_html__( 'You are not allowed to view reports for this page.', 'exactmetrics-pageinsights' ),
					'data'    => array(),
				);
			}
		}

		$error = apply_filters( 'exactmetrics_reports_abstract_get_data_pre_cache', false, $args, $this->name );
		if ( $error ) {
			return apply_filters( 'exactmetrics_reports_handle_error_message', array(
				'success' => false,
				'error'   => $error,
				'data'    => array(),
			) );
		}

		// Check if the data exists in the cache.
		$check_cache = ExactMetrics_Page_Insights_Cache::get_instance()->get( $this->get_path() );

		// If there is data return now to prevent additional requests.
		if ( $check_cache ) {
			return array(
				'success' => true,
				'data'    => $check_cache,
			);
		}

		// If the data was recently checked, prevent additional calls to the Relay as the data there is also cached.
		if ( ! ExactMetrics_Page_Insights_Background::should_fetch() ) {
			return array(
				'success' => true,
				'data'    => array(
					'page_path' => $this->get_path(),
				),
			);
		}

		$api_options = array();
		if ( ! $site_auth && $ms_auth ) {
			$api_options['network'] = true;
		}

		$api = new ExactMetrics_API_Request( 'analytics/reports/' . $this->name . '/single', $api_options, 'GET' );

		$additional_data = $this->additional_data();

		if ( ! empty( $additional_data ) ) {
			$api->set_additional_data( $additional_data );
		}

		$report_data = $api->request();

		if ( is_wp_error( $report_data ) ) {
			return array(
				'success' => false,
				'error'   => $report_data->get_error_message(),
				'data'    => array(),
			);
		} else {

			// Data pulled successfully.
			if ( ! empty( $report_data['data']['needs_more'] ) ) {
				// They have more than 1000 pages so we need to do multiple calls.
				// Let's display a message to inform this will take longer and start the fetch process.
				ExactMetrics_Page_Insights_Background::start_fetch( 1 );

				return array(
					'success' => true,
					'more'    => true,
				);
			} else {
				// Save the data to the cache.
				ExactMetrics_Page_Insights_Cache::get_instance()->set( $report_data['data']['page_path'], $report_data['data'] );
				// Initiate the full data pulling.
				ExactMetrics_Page_Insights_Background::start_fetch();
			}

			// Get fresh data from the cache.
			$cache_data = ExactMetrics_Page_Insights_Cache::get_instance()->get( $this->get_path() );

			// Return the page-specific data.
			return array(
				'success' => true,
				'data'    => $cache_data ? $cache_data : $report_data['data'],
			);
		}
	}

	/**
	 * The report-specific output.
	 *
	 * @param array $data The report data from cache or from Relay.
	 *
	 * @return string
	 */
	public function get_report_html( $data = array() ) {

		check_ajax_referer( 'mi-admin-nonce', 'security' );

		ob_start();

		$interval = '30days';
		if ( isset( $_REQUEST['interval'] ) ) {
			$interval = sanitize_text_field( wp_unslash( $_REQUEST['interval'] ) );
		}
		?>
		<div
			class="exactmetrics-pageinsights-report-content exactmetrics-pageinsights-interval-<?php echo esc_attr( $interval ); ?>">
			<?php

			$report_data = array();
			if ( isset( $data[ $interval ] ) ) {
				$report_data = $data[ $interval ];
			}
			$report_data = wp_parse_args( $report_data, self::get_default_metrics_value() );
			$labels      = self::get_metrics_labels();

			foreach ( $report_data as $metric_name => $metric_value ) {
				$label        = isset( $labels[ $metric_name ] ) ? $labels[ $metric_name ] : $metric_name;
				$metric_value = self::prepare_metric( $metric_value, $metric_name );
				?>
				<div
					class="exactmetrics-pageinsights-report-box exactmetrics-pageinsights-report-<?php echo esc_attr( $metric_name ); ?>">
					<span class="exactmetrics-pageinsights-report-metric"><?php echo esc_html( $label ); ?></span>
					<span
						class="exactmetrics-pageinsights-report-value"><?php echo esc_html( $metric_value ); ?></span>
				</div>
				<?php
			}

			?>
		</div>
		<?php

		return ob_get_clean();

	}

	public function prepare_report_raw_data( $data = array() ) {

		check_ajax_referer( 'mi-admin-nonce', 'security' );

		$output = [];

		$interval = '30days';
		if ( isset( $_REQUEST['interval'] ) ) {
			$interval = sanitize_text_field( wp_unslash( $_REQUEST['interval'] ) );
		}

		$report_data = array();
		if ( isset( $data[ $interval ] ) ) {
			$report_data = $data[ $interval ];
		}
		$report_data = wp_parse_args( $report_data, self::get_default_metrics_value() );
		$labels      = self::get_metrics_labels();

		foreach ( $report_data as $metric_name => $metric_value ) {
			$label        = isset( $labels[ $metric_name ] ) ? $labels[ $metric_name ] : $metric_name;
			$metric_value = self::prepare_metric( $metric_value, $metric_name );

			$output[ $metric_name ] = [
				'label' => $label,
				'value' => $metric_value,
			];
		}

		return $output;

	}

	/**
	 * Add the page-specific path to the request.
	 *
	 * @return array|WP_Error
	 */
	public function additional_data() {

		// This was checked in the get_data function above.
		if ( empty( $_REQUEST['post_id'] ) || ! is_numeric( $_REQUEST['post_id'] ) ) {
			return array(
				'path' => ''
			);
		}

		return array(
			'path' => self::get_path_by_post_id( absint($_REQUEST['post_id']) ),
		);

	}

	/**
	 * Get a consistent path from a post id.
	 *
	 * @param int $post_id The post id for which to grab the path.
	 *
	 * @return string
	 */
	public function get_path_by_post_id( $post_id ) {

		$this->post_id = $post_id;
		if ( ! isset( $this->path ) ) {
			$permalink       = get_permalink( $post_id );
			$permalink_parts = wp_parse_url( $permalink );
			$path            = $permalink_parts['path'];
			if ( ! empty( $permalink_parts['query'] ) ) {
				$path .= '?' . $permalink_parts['query'];
			}
			$this->set_path( $path );
		}

		return $this->get_path();

	}

	/**
	 * Path getter.
	 *
	 * @return string
	 */
	public function get_path() {
		return apply_filters( 'exactmetrics_pageinsights_path', $this->path, $this->post_id );
	}

	/**
	 * Sanitize and set the path when a non-numerical value is used.
	 *
	 * @param string $path The path passed from frontend.
	 *
	 * @return string
	 */
	public function set_path( $path ) {
		$this->path = $path;

		return $this->path;
	}

	/**
	 * Get the metrics with their default values.
	 *
	 * @return array
	 */
	public static function get_default_metrics_value() {

		$defaults = array(
			'entrances'    => 0,
			'pageviews'    => 0,
			'timeonpage'   => 0,
			'exits'        => 0,
		);

		return apply_filters( 'exactmetrics_pageinsights_report_metrics_default', $defaults );

	}

	/**
	 * Get metrics labels.
	 *
	 * @return array
	 */
	public static function get_metrics_labels() {

		$labels = array(
			'entrances'    => esc_html__( 'Entrances', 'exactmetrics-pageinsights' ),
			'pageviews'    => esc_html__( 'Page Views', 'exactmetrics-pageinsights' ),
			'timeonpage'   => esc_html__( 'Time on Page', 'exactmetrics-pageinsights' ),
			'exits'        => esc_html__( 'Exits', 'exactmetrics-pageinsights' ),
		);

		return apply_filters( 'exactmetrics_pageinsights_report_metrics_labels', $labels );

	}

	/**
	 * Some metrics need to be formatted before output.
	 *
	 * @param string|int $value The metric value.
	 * @param string $name The name of the metric.
	 *
	 * @return string
	 */
	public static function prepare_metric( $value, $name ) {

		switch ( $name ) {
			case 'bouncerate':
				$value = number_format( $value, 2 ) . '%';
				break;
			case 'timeonpage':
				$value = empty( $value ) ? 0 . 's' : $value;
				break;
			case 'pageloadtime':
				$value = empty( $value ) ? 0 : $value;
				$value .= 's';
				break;
		}

		$value = apply_filters( 'exactmetrics_pageinsights_prepare_metric', $value, $name );

		return $value;
	}

	/**
	 * Preapare the report data for display.
	 *
	 * @param $data
	 *
	 * @return mixed|void
	 */
	public function prepare_report_data( $data ) {

		if ( ! empty( $data['data'] ) ) {
			if ( isset( $data['data']['yesterday'] ) ) {
				foreach ( $data['data']['yesterday'] as $key => $value ) {
					$data['data']['yesterday'][ $key ] = self::prepare_metric( $value, $key );
				}
			}
			if ( isset( $data['data']['30days'] ) ) {
				foreach ( $data['data']['30days'] as $key => $value ) {
					$data['data']['30days'][ $key ] = self::prepare_metric( $value, $key );
				}
			}
		}

		return $data;
	}
}
