<?php // phpcs:ignore WordPress.Files.FileName
/**
 * The class that manage the product quantity table post type
 *
 * @package YITH WooCommerce Delivery Date\Classes\Post Type
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Delivery_Product_Quantity_Table' ) ) {
	/**
	 * Class YITH_Delivery_Product_Quantity_Table
	 */
	class YITH_Delivery_Product_Quantity_Table {

		/**
		 * The instance of the class
		 *
		 * @var YITH_Delivery_Product_Quantity_Table
		 */
		protected static $_instance; // phpcs:ignore
		/**
		 * Post type name
		 *
		 * @var string
		 */
		protected $post_type_name;
		/**
		 * The capability name
		 *
		 * @var string
		 */
		protected $capability_name;

		/**
		 * YITH_Delivery_Product_Quantity_Table constructor.
		 */
		public function __construct() {
			$this->post_type_name  = 'yith_product_table';
			$this->capability_name = 'delivery_date_product_table';
			add_action( 'init', array( $this, 'register_post_type' ), 16 );
			add_action( 'admin_init', array( $this, 'add_capabilities' ) );
			add_action( 'admin_init', array( $this, 'add_meta_boxes' ) );
			add_filter( 'yit_fw_metaboxes_type_args', array( $this, 'add_custom_type_metaboxes' ) );
			add_action( 'save_post', array( $this, 'save_post_meta' ) );
			add_action( 'edit_form_top', array( $this, 'add_return_to_list_button' ) );
			add_action( 'admin_action_duplicate_quantity_table', array( $this, 'duplicate_quantity_table' ), 20 );
		}

		/**
		 * Create or return the instance
		 *
		 * @return YITH_Delivery_Product_Quantity_Table
		 * @since 2.1.0
		 * @author YITH <plugins@yithemes.com>
		 */
		public static function get_instance() {

			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}


		/**
		 * Get post_type capabilities
		 *
		 * @return array
		 * @since 2.1.0
		 */
		public function get_capability() {

			$caps = array(
				'edit_post'              => "edit_{$this->capability_name}",
				'read_post'              => "read_{$this->capability_name}",
				'delete_post'            => "delete_{$this->capability_name}",
				'edit_posts'             => "edit_{$this->capability_name}s",
				'edit_others_posts'      => "edit_others_{$this->capability_name}s",
				'publish_posts'          => "publish_{$this->capability_name}s",
				'read_private_posts'     => "read_private_{$this->capability_name}s",
				'read'                   => 'read',
				'delete_posts'           => "delete_{$this->capability_name}s",
				'delete_private_posts'   => "delete_private_{$this->capability_name}s",
				'delete_published_posts' => "delete_published_{$this->capability_name}s",
				'delete_others_posts'    => "delete_others_{$this->capability_name}s",
				'edit_private_posts'     => "edit_private_{$this->capability_name}s",
				'edit_published_posts'   => "edit_published_{$this->capability_name}s",
				'create_posts'           => "edit_{$this->capability_name}s",
				'manage_posts'           => "manage_{$this->capability_name}s",
			);
			/**
			 * APPLY_FILTERS: yith_delivery_date_quantity_table_capabilities
			 *
			 * This filter allow to change the capabilites of Quantity table CPT.
			 *
			 * @param array $caps the capabilites.
			 *
			 * @return array
			 */
			return apply_filters( 'yith_delivery_date_quantity_table_capabilities', $caps );
		}

		/**
		 * Get the taxonomy label
		 *
		 * @param string $arg The string to return. Default empty. If is empty return all taxonomy labels.
		 *
		 * @return array taxonomy label
		 *
		 * @since  2.1.0
		 */
		public function get_taxonomy_label( $arg = '' ) {
			/**
			 * APPLY_FILTERS: yith_delivery_date_product_table_taxonomy_label
			 *
			 * This filter allow to change the label taxonomy of quantity table CPT.
			 *
			 * @param array $taxonomy_labels the labels.
			 *
			 * @return array
			 */
			$label = apply_filters(
				'yith_delivery_date_product_table_taxonomy_label',
				array(
					'name'               => _x( 'Quantity Table', 'post type general name', 'yith-woocommerce-delivery-date' ),
					'singular_name'      => _x( 'Quantity Table', 'post type singular name', 'yith-woocommerce-delivery-date' ),
					'menu_name'          => __( 'Quantity Table', 'yith-woocommerce-delivery-date' ),
					'parent_item_colon'  => __( 'Parent Item:', 'yith-woocommerce-delivery-date' ),
					'all_items'          => __( 'All Quantity Tables', 'yith-woocommerce-delivery-date' ),
					'view_item'          => __( 'View Quantity Table', 'yith-woocommerce-delivery-date' ),
					'add_new_item'       => __( 'Add Quantity Table', 'yith-woocommerce-delivery-date' ),
					'add_new'            => __( 'Add Quantity Table', 'yith-woocommerce-delivery-date' ),
					'edit_item'          => __( 'Edit Quantity Table', 'yith-woocommerce-delivery-date' ),
					'update_item'        => __( 'Update Quantity Table', 'yith-woocommerce-delivery-date' ),
					'search_items'       => __( 'Search Quantity Table', 'yith-woocommerce-delivery-date' ),
					'not_found'          => __( 'No Quantity Table found', 'yith-woocommerce-delivery-date' ),
					'not_found_in_trash' => __( 'No Quantity Table found in Trash', 'yith-woocommerce-delivery-date' ),
				)
			);

			return ! empty( $arg ) ? $label[ $arg ] : $label;
		}

		/**
		 * Add capabilities
		 *
		 * @since 2.1.0
		 */
		public function add_capabilities() {

			$caps = $this->get_capability();

			// gets the admin and shop_manager roles.
			$admin        = get_role( 'administrator' );
			$shop_manager = get_role( 'shop_manager' );

			foreach ( $caps as $key => $cap ) {

				$admin->add_cap( $cap );
				$shop_manager->add_cap( $cap );
			}

		}

		/**
		 * Register the post type
		 *
		 * @since 2.0.0
		 */
		public function register_post_type() {
			/**
			 * APPLY_FILTERS: yith_delivery_date_product_quantity_table_post_type
			 *
			 * This filter allow to change the main params of quantity table CPT.
			 *
			 * @param array $params the params.
			 *
			 * @return array
			 */
			$args = apply_filters(
				'yith_delivery_date_product_quantity_table_post_type',
				array(
					'label'               => $this->get_taxonomy_label( 'name' ),
					'description'         => '',
					'labels'              => $this->get_taxonomy_label(),
					'supports'            => array( 'title' ),
					'hierarchical'        => false,
					'public'              => false,
					'show_ui'             => true,
					'show_in_menu'        => false,
					'menu_position'       => 57,
					'show_in_nav_menus'   => false,
					'show_in_admin_bar'   => false,
					'can_export'          => false,
					'has_archive'         => false,
					'exclude_from_search' => true,
					'publicly_queryable'  => false,
					'capability_type'     => $this->capability_name,
					'capabilities'        => $this->get_capability(),
				)
			);

			register_post_type( $this->post_type_name, $args );
		}

		/**
		 * Add meta boxes for post type carrier
		 *
		 * @since 1.0.0
		 */
		public function add_meta_boxes() {

			$post_id = isset( $_GET['post'] ) ? wp_unslash( $_GET['post'] ) : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			if ( ( $post_id && 'yith_product_table' === get_post_type( $post_id ) ) || ( isset( $_GET['post_type'] ) && 'yith_product_table' === wp_unslash( $_GET['post_type'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				$metaboxes = array(
					'yit-delivery-table-metaboxes' => 'delivery-table-meta-boxes-options.php',
				);

				if ( ! function_exists( 'YIT_Metabox' ) ) {
					require_once YITH_DELIVERY_DATE_DIR . 'plugin-fw/yit-plugin.php';
				}

				foreach ( $metaboxes as $key => $metabox ) {
					$args = require_once YITH_DELIVERY_DATE_TEMPLATE_PATH . '/meta-boxes/' . $metabox;
					$box  = YIT_Metabox( $key );
					$box->init( $args );
				}
			}
		}

		/**
		 * Show custom metabox type
		 *
		 * @param array $args The field args.
		 *
		 * @return array
		 * @since 2.1.0
		 */
		public function add_custom_type_metaboxes( $args ) {
			global $post;

			if ( isset( $post ) && 'yith_product_table' === $post->post_type ) {

				$custom_types = array( 'quantity-table' );
				if ( in_array( $args['type'], $custom_types, true ) ) {
					$args['basename'] = YITH_DELIVERY_DATE_DIR;
					$args['path']     = 'meta-boxes/types/';
				}
			}

			return $args;
		}

		/**
		 * Save the post meta
		 *
		 * @param int $post_id The post id.
		 *
		 */
		public function save_post_meta( $post_id ) {

			if ( get_post_type( $post_id ) === $this->post_type_name ) {

				$qty_table       = isset( $_POST['yit_metaboxes']['ywcdd_qty_product_table'] ) ? wp_unslash( $_POST['yit_metaboxes']['ywcdd_qty_product_table'] ) : array(); // phpcs:ignore
				$enable_table    = isset( $_POST['yit_metaboxes']['ywcdd_enable_quantity_rule_table'] ) ? wp_unslash( $_POST['yit_metaboxes']['ywcdd_enable_quantity_rule_table'] ) : 'no'; // phpcs:ignore
				$how_apply_table = isset( $_POST['yit_metaboxes']['ywcdd_table_how_set_table'] ) ? wp_unslash( $_POST['yit_metaboxes']['ywcdd_table_how_set_table'] ) : 'product'; // phpcs:ignore
				$product         = isset( $_POST['yit_metaboxes']['ywcdd_table_select_product'] ) ? wp_unslash( $_POST['yit_metaboxes']['ywcdd_table_select_product'] ) : ''; // phpcs:ignore
				$category        = isset( $_POST['yit_metaboxes']['ywcdd_table_select_product_cat'] ) ? wp_unslash( $_POST['yit_metaboxes']['ywcdd_table_select_product_cat'] ) : ''; // phpcs:ignore
				$carrier         = isset( $_POST['yit_metaboxes']['ywcdd_table_select_carrier'] ) ? wp_unslash( $_POST['yit_metaboxes']['ywcdd_table_select_carrier'] ) : ''; // phpcs:ignore
				$need_days       = isset( $_POST['yit_metaboxes']['ywcdd_table_need_days'] ) ? wp_unslash( $_POST['yit_metaboxes']['ywcdd_table_need_days'] ) : 0; // phpcs:ignore
				update_post_meta( $post_id, 'ywcdd_qty_product_table', $qty_table );
				update_post_meta( $post_id, 'ywcdd_enable_quantity_rule_table', $enable_table );
				update_post_meta( $post_id, 'ywcdd_table_how_set_table', $how_apply_table );
				update_post_meta( $post_id, 'ywcdd_table_select_product', $product );
				update_post_meta( $post_id, 'ywcdd_table_select_product_cat', $category );
				update_post_meta( $post_id, 'ywcdd_table_select_carrier', $carrier );
				update_post_meta( $post_id, 'ywcdd_table_need_days', $need_days );
			}
		}

		/**
		 * Return the quantity tables
		 *
		 * @param array $args The argument.
		 *
		 * @return  array
		 * @since 2.0.0
		 */
		public function get_quantity_tables( $args = array() ) {

			$default_args = array(
				'post_type'      => $this->post_type_name,
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
			);

			$default_args = wp_parse_args( $args, $default_args );

			$posts = get_posts( $default_args );

			return $posts;
		}

		/**
		 * Return only enabled tables
		 *
		 * @param array $args The args.
		 *
		 * @return array
		 * @since 2.1.0
		 */
		public function get_enabled_quantity_tables( $args = array() ) {

			$default_args = array(
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery
					array(
						'key'     => 'ywcdd_enable_quantity_rule_table',
						'value'   => 'yes',
						'compare' => '=',
					),
				),
			);

			$default_args = wp_parse_args( $args, $default_args );

			$posts = $this->get_quantity_tables( $default_args );

			return $posts;
		}

		/**
		 * Get all tables by product id
		 *
		 * @param array $product_ids The product ids.
		 *
		 * @return array
		 * @since 2.0.0
		 */
		public function get_tables_by_product_ids( $product_ids = array() ) {

			$default_args = array(
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery
					'relation' => 'AND',
					array(
						'key'     => 'ywcdd_enable_quantity_rule_table',
						'value'   => 'yes',
						'compare' => '=',
					),
					array(
						'key'     => 'ywcdd_table_how_set_table',
						'value'   => 'product',
						'compare' => '=',
					),
				),
				'fields'     => 'ids',
			);

			$posts = $this->get_quantity_tables( $default_args );

			foreach ( $posts as $i => $post_id ) {
				$post_product_ids = get_post_meta( $post_id, 'ywcdd_table_select_product', true );
				$find             = array();
				if ( is_array( $post_product_ids ) ) {
					$find = array_intersect( $product_ids, $post_product_ids );
				}

				if ( 0 === count( $find ) ) {
					unset( $posts[ $i ] );
				}
			}

			return $posts;
		}

		/**
		 * Get the product table by product category ids
		 *
		 * @param array $category_ids The cateogry ids.
		 *
		 * @return array
		 * @since 2.0.0
		 */
		public function get_tables_by_product_category_ids( $category_ids = array() ) {

			$default_args = array(
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery
					'relation' => 'AND',
					array(
						'key'     => 'ywcdd_enable_quantity_rule_table',
						'value'   => 'yes',
						'compare' => '=',
					),
					array(
						'key'     => 'ywcdd_table_how_set_table',
						'value'   => 'product_cat',
						'compare' => '=',
					),
				),
				'fields'     => 'ids',
			);

			$posts = $this->get_quantity_tables( $default_args );

			foreach ( $posts as $i => $post_id ) {
				$post_product_cat_ids = get_post_meta( $post_id, 'ywcdd_table_select_product_cat', true );

				$find = array_intersect( $category_ids, $post_product_cat_ids );

				if ( 0 === count( $find ) ) {
					unset( $posts[ $i ] );
				}
			}

			return $posts;
		}

		/**
		 * Add a button in single post for back to list
		 *
		 * @since 1.0.0
		 */
		public function add_return_to_list_button() {

			global $post;

			if ( isset( $post ) && $this->post_type_name === $post->post_type ) {
				$admin_url = admin_url( 'admin.php' );
				$params    = array(
					'page' => 'yith_delivery_date_panel',
					'tab'  => 'delivery-table',
				);
				/**
				 * APPLY_FILTERS: ywcdd_quantity_tables_back_link
				 *
				 * This filter allow to change come back link for show the quantity cpt table.
				 *
				 * @param string $url The url.
				 *
				 * @return string
				 */
				$list_url = apply_filters( 'ywcdd_quantity_tables_back_link', esc_url( add_query_arg( $params, $admin_url ) ) );
				/* translators: %$1s is the page url %2$s is the paga title */
				$button = sprintf(
					'<a href="%1$s" title="%2$s" class="ywcdd_back_to">%2$s</a>',
					$list_url,
					__( 'Back to Quantity Tables', 'yith-woocommerce-delivery-date' )
				);
				echo $button; // phpcs:ignore WordPress.Security.EscapeOutput
			}
		}

		/**
		 * Duplicate a quantity table
		 *
		 * @since 2.1.30
		 */
		public function duplicate_quantity_table() {
			if ( empty( $_REQUEST['post'] ) ) {
				wp_die( esc_html__( 'No quantity table to duplicate has been supplied!', 'yith-woocommerce-delivery-date' ) );
			}

			$table_id = isset( $_REQUEST['post'] ) ? absint( $_REQUEST['post'] ) : '';

			check_admin_referer( 'woocommerce-duplicate-quantity-table_' . $table_id );
			$metas      = get_post_custom( $table_id );
			$post_title = get_the_title( $table_id ) . ' ' . esc_html__( '(Copy)', 'yith-woocommerce-delivery-date' );
			$post_args  = array(
				'post_title'  => $post_title,
				'post_type'   => get_post_type( $table_id ),
				'post_status' => 'publish',
			);
			$new_post   = wp_insert_post( $post_args );

			foreach ( $metas as $key => $value ) {
				if ( '_edit_lock' !== $key ) {

					update_post_meta( $new_post, $key, maybe_unserialize( $value[0] ) );
				}
			}

			$admin_post_url = admin_url( 'post.php' );
			$url_args       = array(
				'action' => 'edit',
				'post'   => $new_post,
			);

			$admin_post_url = esc_url_raw( add_query_arg( $url_args, $admin_post_url ) );
			wp_safe_redirect( $admin_post_url );
			exit;
		}

	}
}

if ( ! function_exists( 'YITH_Delivery_Product_Quantity_Table' ) ) {
	/**
	 * Return the instance of the class
	 *
	 * @since 2.0.0
	 * @return YITH_Delivery_Product_Quantity_Table
	 */
	function YITH_Delivery_Product_Quantity_Table() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName

		return YITH_Delivery_Product_Quantity_Table::get_instance();
	}
}

YITH_Delivery_Product_Quantity_Table();
