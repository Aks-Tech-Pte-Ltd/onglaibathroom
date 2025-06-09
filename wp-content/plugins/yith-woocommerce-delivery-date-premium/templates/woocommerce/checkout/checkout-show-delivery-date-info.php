<?php
/**
 * Show the delivery info in the order review
 *
 * @package YITH WooCommerce Delivery Date\Templates\WooComemrce\Checkout
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<span class="ywcdd_message">
<?php esc_html_e( 'Your order will be shipped to carrier on', 'yith-woocommerce-delivery-date' ); ?>
	<strong><?php echo esc_html( $shipping_date ); ?></strong>
</span>
<span class="ywcdd_message">
<?php esc_html_e( 'You should receive the package on', 'yith-woocommerce-delivery-date' ); ?>
	<strong><?php echo esc_html( $delivery_date ); ?></strong>
</span>
