<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ExactMetrics_Admin_Custom_Dimensions {
	/**
	 * @var array Contains all the custom dimensions by type, with parameters 'title', 'active' and 'enabled'.
	 */
	public $custom_dimensions;

	/**
	 * @var int The amount of custom dimensions currently active and enabled (used in the custom dimensions view).
	 */
	public $custom_dimensions_usage;

	/**
	 * @var int The maximum amount of custom dimensions that could be active (used in the custom dimensions view).
	 */
	public $custom_dimensions_limit;

	/**
	 * @var array
	 */
	public $active_custom_dimensions_types;

	/**
	 * @var array Contains the active custom dimensions as they were saved to the database.
	 */
	public $active_custom_dimensions = array();

	/**
	 * @var array The seo dimension types
	 */
	public $seo_dimension_types = array( 'focus_keyword', 'seo_score' );

	public function __construct() {
		$this->set_active_custom_dimensions();
		$this->set_rendering_properties();
	}

	/**
	 * Fetches the active custom dimensions and assigns them to the active_custom_dimensions property
	 */
	public function set_active_custom_dimensions() {
		$this->active_custom_dimensions = exactmetrics_get_option( 'custom_dimensions', array() );
	}

	/**
	 * The current supported custom dimensions types (Key name is the matching name for the functions). The metric
	 * is a setting for this specific custom dimension. The metric is used to fetch data with this custom dimension.
	 *
	 * @return array
	 */
	public function custom_dimensions() {
		return apply_filters( 'exactmetrics_available_custom_dimensions',
			array(
				'logged_in'              => array(
					'title'   => __( 'Logged in', 'exactmetrics-dimensions' ),
					'label'   => __( 'Number of logged-in sessions', 'exactmetrics-dimensions' ),
					'enabled' => true,
					'metric'  => 'sessions',
				),
				'user_id'                => array(
					'title'   => __( 'User ID', 'exactmetrics-dimensions' ),
					'label'   => __( 'Top logged-in users by sessions', 'exactmetrics-dimensions' ),
					'enabled' => true,
					'metric'  => 'sessions',
				),
				'post_type'              => array(
					'title'   => __( 'Post type', 'exactmetrics-dimensions' ),
					'label'   => __( 'Most popular post types', 'exactmetrics-dimensions' ),
					'enabled' => true,
					'metric'  => 'pageviews',
				),
				'author'                 => array(
					'title'   => __( 'Author', 'exactmetrics-dimensions' ),
					'label'   => __( 'Most popular authors', 'exactmetrics-dimensions' ),
					'enabled' => true,
					'metric'  => 'pageviews',
				),
				'category'               => array(
					'title'   => __( 'Category', 'exactmetrics-dimensions' ),
					'label'   => __( 'Most popular categories', 'exactmetrics-dimensions' ),
					'enabled' => true,
					'metric'  => 'pageviews',
				),
				'published_at'           => array(
					'title'   => __( 'Published at', 'exactmetrics-dimensions' ),
					'label'   => __( 'Best publication time', 'exactmetrics-dimensions' ),
					'enabled' => true,
					'metric'  => 'pageviews',
				),
				'tags'                   => array(
					'title'   => __( 'Tags', 'exactmetrics-dimensions' ),
					'label'   => __( 'Most popular tags', 'exactmetrics-dimensions' ),
					'enabled' => true,
					'metric'  => 'pageviews',
				),
				'seo_score'              => array(
					'title'   => __( 'SEO Score', 'exactmetrics-dimensions' ),
					'label'   => __( 'Best SEO score', 'exactmetrics-dimensions' ),
					'enabled' => exactmetrics_is_wp_seo_active(),
					'metric'  => 'pageviews',
				),
				'focus_keyword'          => array(
					'title'   => __( 'Focus Keyword', 'exactmetrics-dimensions' ),
					'label'   => __( 'Most popular focus keywords', 'exactmetrics-dimensions' ),
					'enabled' => exactmetrics_is_wp_seo_active(),
					'metric'  => 'pageviews',
				),
				'aioseo_truseo_score'    => array(
					'title'   => __( 'TruSeo Score', 'exactmetrics-dimensions' ),
					'label'   => __( 'TruSEO Score (AIOSEO)', 'exactmetrics-dimensions' ),
					'enabled' => function_exists( 'exactmetrics_is_aioseo_active' ) && exactmetrics_is_aioseo_active(),
					'metric'  => 'pageviews',
				),
				'aioseo_focus_keyphrase' => array(
					'title'   => __( 'Focus Keyphrase', 'exactmetrics-dimensions' ),
					'label'   => __( 'Focus Keyphrase (AIOSEO)', 'exactmetrics-dimensions' ),
					'enabled' => function_exists( 'exactmetrics_is_aioseo_active' ) && exactmetrics_is_aioseo_active(),
					'metric'  => 'pageviews',
				),
			)
		);
	}

	/**
	 * Checks if the given dimensions all have a unique ID
	 *
	 * @param array $dimensions Dimensions to check.
	 *
	 * @return bool Whether or not the dimension IDs are unique.
	 */
	public function dimension_ids_are_unique( $dimensions ) {
		$dimension_ids = wp_list_pluck( $dimensions, 'id' );

		return $dimension_ids === array_unique( $dimension_ids );
	}

	/**
	 * Checks if the given dimensions all have a unique type
	 *
	 * @param array $dimensions Dimensions to check.
	 *
	 * @return bool Whether or not the dimension types are unique.
	 */
	public function dimension_types_are_unique( $dimensions ) {
		$dimension_ids = wp_list_pluck( $dimensions, 'type' );

		return $dimension_ids === array_unique( $dimension_ids );
	}

	/**
	 * @return bool Checks if there are any active seo dimensions
	 */
	public function seo_dimensions_active() {
		$active_seo_dimension_types = array_intersect( $this->seo_dimension_types, $this->active_custom_dimensions_types() );

		return ! empty( $active_seo_dimension_types );
	}

	/**
	 * Prepares a couple of properties to be used in the custom dimensions view
	 */
	public function set_rendering_properties() {
		$this->custom_dimensions              = $this->custom_dimensions();
		$this->active_custom_dimensions_types = $this->active_custom_dimensions_types();
		$this->custom_dimensions_usage        = count( $this->active_enabled_custom_dimensions() );
		$this->custom_dimensions_limit        = count( $this->enabled_custom_dimensions() );
	}

	/**
	 * Returns an array with custom dimensions that are both active and enabled.
	 *
	 * @return array
	 */
	private function active_enabled_custom_dimensions() {
		$active_enabled_custom_dimensions = array();

		foreach ( $this->enabled_custom_dimensions() as $key => $custom_dimension ) {
			if ( in_array( $key, $this->active_custom_dimensions_types ) ) {
				$active_enabled_custom_dimensions[ $key ] = $custom_dimension;
			}
		}

		return $active_enabled_custom_dimensions;
	}

	/**
	 * Returns an array with all enabled custom dimensions, both active and inactive.
	 *
	 * @return array
	 */
	private function enabled_custom_dimensions() {
		$enabled_custom_dimensions = array();

		foreach ( $this->custom_dimensions as $key => $custom_dimension ) {
			if ( $custom_dimension['enabled'] ) {
				$enabled_custom_dimensions[ $key ] = $custom_dimension;
			}
		}

		return $enabled_custom_dimensions;
	}

	/**
	 * Maps the types of the active custom dimensions to a separate array to be analyzed in $this->custom_dimensions()
	 *
	 * @return array
	 */
	private function active_custom_dimensions_types() {
		$active_custom_dimensions_types = array();

		foreach ( $this->active_custom_dimensions as $active_custom_dimension ) {
			$active_custom_dimensions_types[] = $active_custom_dimension['type'];
		}

		return $active_custom_dimensions_types;
	}
}
