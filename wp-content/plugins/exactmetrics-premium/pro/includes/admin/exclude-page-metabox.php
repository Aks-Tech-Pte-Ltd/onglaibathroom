<?php

/**
 * Metabox Pro class.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'ExactMetrics_MetaBox_ExcludePage_Pro' ) ) {
	class ExactMetrics_MetaBox_ExcludePage_Pro {

		public function __construct() {
			add_action( 'save_post', [ $this, 'save_skip_field' ] );
		}

		public function save_skip_field( $post_id ) {
			if ( ! isset( $_POST['exactmetrics_metabox_nonce'] ) || ! wp_verify_nonce( $_POST['exactmetrics_metabox_nonce'], 'exactmetrics_metabox' ) ) { // phpcs:ignore
				return;
			}

			$skipped = intval( isset( $_POST['_mi_skip_tracking'] ) ? $_POST['_mi_skip_tracking'] : 0 );

			update_post_meta( $post_id, '_mi_skip_tracking', $skipped );
		}
	}

	new ExactMetrics_MetaBox_ExcludePage_Pro();
}
