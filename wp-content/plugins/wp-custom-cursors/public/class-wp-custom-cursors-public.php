<?php

/**
 *
 * @link       https://codecanyon.net/user/web_trendy
 * @since      1.0.0
 *
 * @package    Wp_custom_cursors
 * @subpackage Wp_custom_cursors/admin
 */

/**
 *
 * @package    Wp_custom_cursors
 * @subpackage Wp_custom_cursors/admin
 * @author     Web_Trendy <webtrendyio@gmail.com>
 */

class Wp_custom_cursors_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style($this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp_custom_cursors_main_style.css', array(), $this->version, 'all');

	}

	/**
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		global $wpdb;
		$tablename = $wpdb->prefix . "custom_cursors";

		$sql = "SELECT * FROM $tablename";
		$cursors = $wpdb->get_results( $sql, ARRAY_A );

		$reformed_cursors = [];

		foreach($cursors as $cursor) {
			$cursor['hover_cursors'] = stripcslashes($cursor['hover_cursors']);
			array_push($reformed_cursors, $cursor);
		}

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp_custom_cursors_main_script.js', array(), $this->version, true );

		wp_localize_script( $this->plugin_name, 'cursors', $reformed_cursors );
	}

}
