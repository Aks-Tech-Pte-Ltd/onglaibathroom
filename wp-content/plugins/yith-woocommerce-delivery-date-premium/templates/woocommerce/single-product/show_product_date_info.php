<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Show the date info in the Product page
 *
 * @package YITH WooCommerce Delivery Date\Templates\WooComemrce\Single Product
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $product;

if ( ! $product || ! $product instanceof WC_Product ) {
	return;
}

?>

<div id="ywcdd_info_single_product" class="<?php echo esc_attr( $product->get_type() ); ?>">
	<?php if ( ! empty( $last_shipping_date ) ) : ?>
		<div id="ywcdd_info_shipping_date">
			<span class="ywcdd_shipping_icon"></span>
			<span class="ywcdd_shipping_message">
				<?php echo $last_shipping_date; //phpcs:ignore WordPress.Security.EscapeOutput ?>
			</span>
		</div>
	<?php endif; ?>
	<?php if ( ! empty( $delivery_date ) ) : ?>
		<div id="ywcdd_info_first_delivery_date">
			<span class="ywcdd_delivery_icon"></span>
			<span class="ywcdd_delivery_message">
				<?php echo $delivery_date; //phpcs:ignore WordPress.Security.EscapeOutput ?>
			</span>
		</div>
	<?php endif; ?>
</div>
