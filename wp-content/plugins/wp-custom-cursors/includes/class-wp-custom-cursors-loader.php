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
 * @package    Wp_custom_cursors
 * @subpackage Wp_custom_cursors/includes
 * @author     Web_Trendy <webtrendyio@gmail.com>
 */
class Wp_custom_cursors_Loader {

	/**
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $actions  
	 */
	protected $actions;

	/**
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $filters 
	 */
	protected $filters;

	/**
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->actions = array();
		$this->filters = array();

	}

	/**
	 *
	 * @since    1.0.0
	 * @param    string               $hook 
	 * @param    object               $component 
	 * @param    string               $callback 
	 * @param    int                  $priority 
	 * @param    int                  $accepted_args 
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 *
	 * @since    1.0.0
	 * @param    string               $hook  
	 * @param    object               $component  
	 * @param    string               $callback  
	 * @param    int                  $priority
	 * @param    int                  $accepted_args
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array                $hooks 
	 * @param    string               $hook  
	 * @param    object               $component 
	 * @param    string               $callback  
	 * @param    int                  $priority  
	 * @param    int                  $accepted_args 
	 * @return   array 
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {

		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		);

		return $hooks;

	}

	/**
	 *
	 * @since    1.0.0
	 */
	public function run() {

		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

	}

}
