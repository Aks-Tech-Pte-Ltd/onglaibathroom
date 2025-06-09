<?php
/**
 * Show the delivery field in checkout page
 *
 * @package YITH WooCommerce Delivery Date\Templates\WooComemrce\Checkout
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$class_req = 'yes' === $is_mandatory ? 'validate-required' : '';
$abbr_span = 'yes' === $is_mandatory ? '<abbr class="required" title="required">*</abbr>' : '';

?>

<div class="ywcdd_timeslot_content ywcdd_hide">
    <p class="form-row form-row-wide form-row-slot">
        <label for="ywcdd_timeslot">
			<?php
			/**
			 * APPLY_FILTERS: ywcdd_change_timeslot_label
			 *
			 * Change the time slot label in checkout page.
			 *
			 * @param string $time_slot_label the label.
			 *
			 * @return string
			 */
			echo esc_html( apply_filters( 'ywcdd_change_timeslot_label', __( 'Time Slot', 'yith-woocommerce-delivery-date' ) ) );
			?>
			<?php echo $abbr_span; //phpcs:ignore WordPress.Security.EscapeOutput ?>
        </label>
        <select id="ywcdd_timeslot" name="ywcdd_timeslot">
			<?php if ( 'no' === $is_mandatory ) : ?>
                <option value=""><?php esc_html_e( 'Select time slot', 'yith-woocommerce-delivery-date' ); ?></option>
			<?php endif; ?>
        </select>
        <input type="hidden" name="ywcdd_timeslot_av" class="ywcdd_timeslot_av"/>
    </p>
</div>
