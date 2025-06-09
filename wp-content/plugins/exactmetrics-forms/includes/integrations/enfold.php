<?php
defined( 'ABSPATH' ) or exit;

/**
 * Integration class for Enfold theme
 */
class ExactMetrics_Forms_Integration_Enfold {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_filter( 'avf_contact_form_submit_button_attr', [ $this, 'register_submit_onclick' ] );
	}

	/**
	 * Add submit button onclick attribute.
	 *
	 * @param string $att Previous button attributes.
	 *
	 * @return string
	 */
	public function register_submit_onclick( $att = '' ) {
		$att .= ' onclick = "exactmetrics_forms_record_conversion({target: this.form})" ';

		return $att;
	}

}

( new ExactMetrics_Forms_Integration_Enfold() )->register_hooks();
