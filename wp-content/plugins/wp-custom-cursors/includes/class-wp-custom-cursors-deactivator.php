<?php

/**
 *
 * @link       https://codecanyon.net/user/web_trendy
 * @since      1.0.0
 *
 * @package    Wp_custom_cursors
 * @subpackage Wp_custom_cursors/includes
 */

/**
 *
 * @since      1.0.0
 * @package    Wp_custom_cursors
 * @subpackage Wp_custom_cursors/includes
 * @author     Web_Trendy <webtrendyio@gmail.com>
 */

class Wp_custom_cursors_Deactivator {

	/**
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		global $wpdb;
	    $tablename = $wpdb->prefix . "custom_cursors";
	    $sql = "DROP TABLE IF EXISTS $tablename";
	    $wpdb->query($sql);
	}

}
