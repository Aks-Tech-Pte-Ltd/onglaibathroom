<?php
/**
 * Will show a custom tab in plugin panel
 *
 * @package YITH WooCommerce Delivery Date\Plugin Options
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = array(
	'carrier-settings' => array(
		'carrier_tab' => array(
			'type'   => 'custom_tab',
			'action' => 'ywcdd_show_carrier_tab',
		),
	),

);

return $settings;
