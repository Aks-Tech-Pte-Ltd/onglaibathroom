<?php
/**
 * Plain Template Email Delivery Date
 *
 * @package YITH WooCommerce Delivery Date\Templates\Emails\Plain
 * @since   1.0.0
 * @author  YITH <plugins@yithemes.com>
 */

echo '= ' . $email_heading . " \n\n"; // phpcs:ignore WordPress.Security.EscapeOutput

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo nl2br( get_option( 'ywcdd_mail_content' ) ); // phpcs:ignore WordPress.Security.EscapeOutput

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
