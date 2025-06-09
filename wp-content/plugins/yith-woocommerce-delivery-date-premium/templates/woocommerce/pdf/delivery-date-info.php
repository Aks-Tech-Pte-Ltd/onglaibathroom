<?php
/**
 * Show the delivery field in checkout page
 *
 * @package YITH WooCommerce Delivery Date\Templates\WooComemrce\Pdf
 *
 * @var WC_Order $order The order.
 */

$delivery_info = '';
$carrier_label = $order->get_meta( 'ywcdd_order_carrier' );
$shipping_date = $order->get_meta( 'ywcdd_order_shipping_date' );
$delivery_date = $order->get_meta( 'ywcdd_order_delivery_date' );
$time_from     = $order->get_meta( 'ywcdd_order_slot_from' );
$time_to       = $order->get_meta( 'ywcdd_order_slot_to' );

if ( ! empty( $delivery_date ) ) :?>

    <div class="ywcdd-delivery-info-content">
        <h4><?php esc_html_e( 'Delivery Notes', 'yith-woocommerce-delivery-date' ); ?></h4>
		<?php if ( ! empty( $carrier_label ) ) : ?>
            <div class="ywcdd-delivery-carrier-info">
                <span class="ywcdd-carrier-label"
                      style="margin-right: 5px;"><strong><?php esc_html_e( 'Carrier', 'yith-woocommerce-delivery-date' ); ?>:</strong></span>
                <span class="ywcdd-carrier-name"><?php echo esc_html( $carrier_label ); ?></span>
            </div>
		<?php endif; ?>
        <div class="ywcdd-delivery-shipping-info">
            <span class="ywcdd-shipping-label"
                  style="margin-right: 5px;"><strong><?php echo esc_html_x( 'Shipping Date', '[Part of]: Shipping Date within 20th March 2019', 'yith-woocommerce-delivery-date' ); ?>:</strong></span>
            <span class="ywcdd-shipping-value">
			<?php echo sprintf( '%s %s', _x( 'within', '[Part of]: Shipping Date within 20th March 2019', 'yith-woocommerce-delivery-date' ), ywcdd_get_date_by_format( $shipping_date ) ); //phpcs:ignore WordPress.Security.EscapeOutput
			?>
			</span>
        </div>
        <div class="ywcdd-delivery-date-info">
            <span class="ywcdd-date-label"
                  style="margin-right: 5px;"><strong><?php esc_html_e( 'Delivery Date', 'yith-woocommerce-delivery-date' ); ?>:</strong></span>
			<?php
			$timeslot_info = ( empty( $time_from ) || empty( $time_to ) ) ? '' : sprintf( '%s - %s', ywcdd_display_timeslot( $time_from ), ywcdd_display_timeslot( $time_to ) );
			$delivery_date = ywcdd_get_date_by_format( $delivery_date );
			$date_info     = empty( $timeslot_info ) ? $delivery_date : sprintf( '%s, %s', $delivery_date, $timeslot_info );
			?>
            <span class="ywcdd-date-value"><?php echo esc_html( $date_info ); ?></span>
        </div>
    </div>
<?php
endif;
