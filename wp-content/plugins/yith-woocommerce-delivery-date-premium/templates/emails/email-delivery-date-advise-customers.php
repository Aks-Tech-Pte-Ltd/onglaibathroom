<?php
/**
 * HTML Template Email Delivery Date
 *
 * @package YITH WooCommerce Delivery Date\Templates\Emails
 * @since   1.0.0
 * @author  YITH <plugins@yithemes.com>
 */

do_action( 'woocommerce_email_header', $email_heading, $email );

echo nl2br( get_option( 'ywcdd_mail_content' ) ); // phpcs:ignore WordPress.Security.EscapeOutput

do_action( 'woocommerce_email_footer', $email );
