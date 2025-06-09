<?php
/**
 * Retrieve posts/pages
 *
 * @access admin
 * @since 3.0.0
 */
function exactmetrics_get_posts() {
	global $wpdb;

	// Run a security check first.
	check_ajax_referer( 'mi-admin-nonce', 'nonce' );

	$args = array(
		's'              => isset( $_POST['keyword'] ) ? wp_unslash( sanitize_text_field($_POST['keyword']) ) : '',
		'post_type'      => isset( $_POST['post_type'] ) ? wp_unslash( sanitize_text_field($_POST['post_type']) ) : 'any',
		'posts_per_page' => isset( $_POST['numberposts'] ) ? wp_unslash( sanitize_text_field($_POST['numberposts']) ) : 10,
	);

	$array    = array();
	$posts    = get_posts( $args );
	$homepage = get_option( 'page_on_front' );

	if ( ! $homepage ) {
		$array[] = array(
			'id'    => - 1,
			'title' => __( 'Homepage', 'exactmetrics-google-optimize' ),
		);
	}

	if ( $posts ) {
		foreach ( $posts as $post ) {
			$array[] = array(
				'id'    => esc_attr( $post->ID ),
				'title' => esc_attr( $post->post_title ),
			);
		}
	}

	wp_send_json_success( $array );
	wp_die();
}

add_action( 'wp_ajax_exactmetrics_get_posts', 'exactmetrics_get_posts' );
