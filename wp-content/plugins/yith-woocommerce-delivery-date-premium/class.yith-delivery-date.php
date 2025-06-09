<?php // phpcs:ignore WordPress.Files.FileName
/**
 * The main class
 *
 * @package YITH WooCommerce Delivery Date\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'YITH_Delivery_Date' ) ) {
	/**
	 * Class YITH_Delivery_Date
	 */
	class YITH_Delivery_Date {

		/**
		 * Unique instance
		 *
		 * @var YITH_Delivery_Date
		 */
		protected static $_instance; // phpcs:ignore
		/**
		 * This is the instance of the YITH WooCommerce Panel class.
		 *
		 * @var YIT_Plugin_Panel_WooCommerce
		 */
		protected $_panel; // phpcs:ignore
		/**
		 * This is the name of the panel page
		 *
		 * @var string
		 */
		protected $_panel_page = 'yith_delivery_date_panel'; // phpcs:ignore

		/**
		 * YITH_Delivery_Date constructor.
		 */
		public function __construct() {
			/* === Main Classes to Load === */

			/**
			 * APPLY_FILTERS: yith_wcdd_require_class
			 *
			 * Can add or remove class to load.
			 *
			 * @param array $classes The classes to load.
			 *
			 * @return array
			 */
			$require = apply_filters(
				'yith_wcdd_require_class',
				array(
					'common' => array(
						'includes/functions.yith-delivery-date.php',
						'includes/post-type/class.yith-delivery-date-carrier.php',
						'includes/post-type/class.yith-delivery-date-processing-method.php',
						'includes/shipping/class.yith-delivery-date-shipping-manager.php',
						'includes/emails/class.yith-delivery-date-emails.php',
						'includes/class.yith-delivery-date-manager.php',
						'includes/class.yith-delivery-date-product.php',
						'includes/class.yith-delivery-date-frontend-product-table.php',
						'includes/widgets/class.yith-delivery-date-dynamic-messages-widget.php',
						'includes/shortcodes/class.yith-delivery-date-shortcodes.php',
						'includes/post-type/class.yith-delivery-date-product-quantity-table.php',
					),
					'admin'  => array(
						'includes/class.yith-delivery-date-admin.php',
						'includes/class.yith-delivery-date-order-manager.php',
					),
				)
			);

			$this->_require( $require );

			// Load Plugin Framework.
			add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 15 );
			add_action( 'plugins_loaded', array( $this, 'load_privacy_policy' ), 20 );
			// Add action links.
			add_filter( 'plugin_action_links_' . plugin_basename( YITH_DELIVERY_DATE_DIR . '/' . basename( YITH_DELIVERY_DATE_FILE ) ), array(
				$this,
				'action_links'
			) );
			// Add row meta.
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 5 );

			// Add action for register and update plugin.
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );

			// Add YITH DELIVERY DATE menu.
			add_action( 'admin_menu', array( $this, 'add_menu' ), 5 );

			add_action( 'widgets_init', array( $this, 'register_widgets' ) );

			add_action( 'woocommerce_admin_field_holidays', array( $this, 'add_custom_field_path' ), 20, 2 );
			add_action( 'woocommerce_admin_field_calendar', array( $this, 'add_custom_field_path' ), 20, 2 );
			add_action( 'woocommerce_admin_field_toggle-element', array( $this, 'add_custom_field_path' ), 20, 2 );
			add_filter( 'yith_plugin_fw_get_field_template_path', array( $this, 'add_field_path' ), 20, 2 );
		}

		/**
		 * Create or return the instance
		 *
		 * @return YITH_Delivery_Date
		 * @since 1.0.0
		 * @author YITH <plugins@yithemes.com>
		 */
		public static function get_instance() {

			if ( is_null( self::$_instance ) ) {

				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Add the main classes file
		 *
		 * Include the admin and frontend classes
		 *
		 * @param array $main_classes The require classes file path.
		 *
		 * @access protected
		 * @since  1.0
		 *
		 */
		protected function _require( $main_classes ) { // phpcs:ignore
			foreach ( $main_classes as $section => $classes ) {
				foreach ( $classes as $class ) {
					if ( ( 'common' === $section || ( 'frontend' === $section && ! is_admin() ) || ( 'admin' === $section && is_admin() ) ) && file_exists( YITH_DELIVERY_DATE_DIR . $class ) ) {
						require_once YITH_DELIVERY_DATE_DIR . $class;
					}
				}
			}
		}

		/**
		 * Load plugin fw
		 *
		 * @since 1.0.0
		 */
		public function plugin_fw_loader() {

			if ( ! defined( 'YIT_CORE_PLUGIN' ) ) {
				global $plugin_fw_data;
				if ( ! empty( $plugin_fw_data ) ) {
					$plugin_fw_file = array_shift( $plugin_fw_data );
					require_once $plugin_fw_file;
				}
			}
		}

		/**
		 * Action Links.
		 * Add the action links to plugin admin page.
		 *
		 * @param array $links array with plugin links.
		 *
		 * @return array
		 * @since    1.0
		 */
		public function action_links( $links ) {

			$links = yith_add_action_links( $links, $this->_panel_page, true, YITH_DELIVERY_DATE_SLUG );

			return $links;
		}

		/**
		 * Plugin_row_meta.
		 *
		 * Add the action links to plugin admin page.
		 *
		 * @param array $new_row_meta_args The new plugin meta.
		 * @param array $plugin_meta The plugin meta.
		 * @param string $plugin_file The filename of plugin.
		 * @param array $plugin_data The plugin data.
		 * @param string $status The plugin status.
		 * @param string $init_file The filename of plugin.
		 *
		 * @return   array
		 * @since    1.0
		 * @use plugin_row_meta
		 */
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YITH_DELIVERY_DATE_INIT' ) {

			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['slug']       = YITH_DELIVERY_DATE_SLUG;
				$new_row_meta_args['is_premium'] = true;
			}

			return $new_row_meta_args;
		}

		/**
		 * Register plugins for activation tab
		 *
		 * @since    1.0.0
		 */
		public function register_plugin_for_activation() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				require_once YITH_DELIVERY_DATE_DIR . 'plugin-fw/licence/lib/yit-licence.php';
				require_once YITH_DELIVERY_DATE_DIR . 'plugin-fw/licence/lib/yit-plugin-licence.php';
			}
			YIT_Plugin_Licence()->register( YITH_DELIVERY_DATE_INIT, YITH_DELIVERY_DATE_SECRET_KEY, YITH_DELIVERY_DATE_SLUG );
		}

		/**
		 * Register plugins for update tab
		 *
		 * @since    1.0.0
		 */
		public function register_plugin_for_updates() {
			if ( ! class_exists( 'YIT_Upgrade' ) ) {
				require_once YITH_DELIVERY_DATE_DIR . 'plugin-fw/lib/yit-upgrade.php';
			}
			YIT_Upgrade()->register( YITH_DELIVERY_DATE_SLUG, YITH_DELIVERY_DATE_INIT );
		}

		/**
		 * Add YITH Delivery_Date menu under YITH_Plugins
		 *
		 * @since 1.0.0
		 */
		public function add_menu() {

			if ( ! empty( $this->_panel ) ) {
				return;
			}

			/**
			 * APPLY_FILTERS: yith_delivery_date_add_tab
			 *
			 * The filter allow to add/ remove plugin tabs.
			 *
			 * @param array $tabs The plugin tab.
			 *
			 * @return array
			 */
			$admin_tabs = apply_filters(
				'yith_delivery_date_add_tab',
				array(
					'general-settings'         => __( 'Settings', 'yith-woocommerce-delivery-date' ),
					'processing-method'        => __( 'Processing Options', 'yith-woocommerce-delivery-date' ),
					'carrier-settings'         => __( 'Carrier Options', 'yith-woocommerce-delivery-date' ),
					'dynamic-delivery-message' => __( 'Dynamic Delivery Message', 'yith-woocommerce-delivery-date' ),
					'general-calendar'         => __( 'Calendar', 'yith-woocommerce-delivery-date' ),
					'delivery-table'           => __( 'Quantity Tables', 'yith-woocommerce-delivery-date' ),
					'email-settings'           => __( 'Email', 'yith-woocommerce-delivery-date' ),
				)
			);
			/**
			 * APPLY_FILTERS: ywcdd_capability_menu
			 *
			 * The filter allow to change user capability.
			 *
			 * @param string $user_capability The capability.
			 *
			 * @return string
			 */
			$args = array(
				'create_menu_page'   => true,
				'parent_slug'        => '',
				'page_title'         => 'YITH WooCommerce Delivery Date',
				'menu_title'         => 'Delivery Date',
				'plugin_description' => __( 'Advanced delivery options for your customers', 'yith-woocommerce-delivery-date' ),
				'capability'         => apply_filters( 'ywcdd_capability_menu', 'manage_options' ),
				'parent'             => '',
				'class'              => function_exists( 'yith_set_wrapper_class' ) ? yith_set_wrapper_class() : '',
				'parent_page'        => 'yit_plugin_panel',
				'page'               => $this->_panel_page,
				'help_tab'           => array(
					'hc_url'  => '//support.yithemes.com/hc/en-us/categories/360003474678-YITH-WOOCOMMERCE-DELIVERY-DATE',
					'doc_url' => '//docs.yithemes.com/yith-woocommerce-delivery-date/',
				),
				'admin-tabs'         => $admin_tabs,
				'options-path'       => YITH_DELIVERY_DATE_DIR . '/plugin-options',
				'plugin_slug'        => YITH_DELIVERY_DATE_SLUG,
			);

			if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
				require_once YITH_DELIVERY_DATE_DIR . 'plugin-fw/lib/yith-plugin-panel-wc.php';
			}

			$this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );
		}

		/**
		 * Load delivery privacy class
		 *
		 * @since 1.0.20
		 */
		public function load_privacy_policy() {
			require_once YITH_DELIVERY_DATE_INC . 'class.yith-delivery-date-privacy.php';
			new YITH_Delivery_Date_Privacy();
		}

		/**
		 * Register plugin widget
		 *
		 * @since 1.0.0
		 */
		public function register_widgets() {
			register_widget( 'YITH_Delivery_Dynamic_Messages_Widget' );
		}

		/**
		 * Add custom field in framework
		 *
		 * @param array $option The option.
		 *
		 * @since 1.0.0
		 */
		public function add_custom_field_path( $option ) {

			$custom_type = array(
				'holidays',
				'calendar',
				'quantity-range-field',
			);
			extract( $option ); // phpcs:ignore WordPress.PHP.DontExtract

			if ( in_array( $type, $custom_type, true ) ) {
				$template_path = YITH_DELIVERY_DATE_TEMPLATE_PATH . 'admin/types/' . $type . '.php';
			}

			include $template_path;

		}

		/**
		 * Add custom field path
		 *
		 * @param string $template_path The field path.
		 * @param array $field The field.
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function add_field_path( $template_path, $field ) {

			if ( 'select-group' === $field['type'] ) {
				$template_path = YITH_DELIVERY_DATE_TEMPLATE_PATH . 'admin/types/select-group.php';
			} elseif ( 'quantity-range-field' === $field['type'] ) {
				$template_path = YITH_DELIVERY_DATE_TEMPLATE_PATH . 'admin/types/quantity-range-field.php';
			}

			return $template_path;
		}

	}
}
