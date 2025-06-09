<?php
/**
 * Show the delivery field in checkout page
 *
 * @package YITH WooCommerce Delivery Date\Templates\WooComemrce\Checkout
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="ywcdd_select_delivery_date_content">
	<input type="hidden" name="ywcdd_fields" value="1" />
	<?php
	if ( ! empty( $processing_method ) ) :

		wc_get_template(
			'woocommerce/checkout/delivery-date-select-date.php',
			array(
				'processing_method' => $processing_method,
				'is_mandatory'      => $is_mandatory,
			),
			YITH_DELIVERY_DATE_TEMPLATE_PATH,
			YITH_DELIVERY_DATE_TEMPLATE_PATH
		);
		?>
	<?php endif; ?>
</div>
