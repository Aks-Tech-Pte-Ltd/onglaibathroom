<?php
/**
 * This file show admin notice
 *
 * @package YITH WooCommerce Delivery Date\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="notice notice-warning" style="padding-right: 38px;position: relative;">
<p><?php echo wp_kses_post( $message ); ?></p>
<?php if ( ! empty( $url ) ) : ?>
<a class="notice-dismiss" href="<?php echo esc_url( $url ); ?>"  style="text-decoration: none;"></a>
<?php endif; ?>
</div>
