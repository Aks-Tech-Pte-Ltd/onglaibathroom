<?php // phpcs:ignore WordPress.Files.FileName
/**
 * The class that manage the Product table on product page
 *
 * @package YITH WooCommerce Delivery Date\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'YITH_Delivery_Date_Product_Table_Frontend' ) ) {
	/**
	 * Class YITH_Delivery_Date_Product_Table_Frontend
	 */
	class YITH_Delivery_Date_Product_Table_Frontend {
		/**
		 * The instance of the class
		 *
		 * @var YITH_Delivery_Date_Product_Table_Frontend
		 */
		protected static $_instance; // phpcs:ignore

		/**
		 * YITH_Delivery_Date_Product_Table_Frontend constructor.
		 */
		public function __construct() {

			add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'show_product_table' ), 35 );
			add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'change_add_to_cart_link' ), 10, 3 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 20 );
			add_filter( 'woocommerce_add_cart_item', array( $this, 'add_cart_item' ), 20, 2 );
			add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 20, 4 );
			add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'set_delivery_item_meta_from_session' ), 20, 2 );
			add_filter( 'woocommerce_get_item_data', array( $this, 'add_item_data' ), 20, 2 );
			add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'add_custom_item_meta_checkout' ), 20, 3 );
			add_filter(
				'woocommerce_order_item_display_meta_key',
				array(
					$this,
					'change_order_item_display_meta_key',
				),
				20,
				2
			);
			add_filter(
				'woocommerce_order_item_display_meta_value',
				array(
					$this,
					'change_order_item_display_meta_value',
				),
				20,
				2
			);
			add_action( 'woocommerce_order_status_changed', array( $this, 'manage_order_event' ), 20, 3 );
			add_action( 'woocommerce_email_before_order_table', array( $this, 'check_if_show_hide_item_meta' ), 20, 3 );

			add_filter( 'woocommerce_available_variation', array( $this, 'add_variation_data' ), 15, 3 );

			add_filter( 'yith_delivery_date_show_quantity_table', array( $this, 'exclude_product_type' ), 10, 2 );
		}

		/**
		 * Show the quantity table in the product page
		 *
		 * @author YITH <plugins@yithemes.com>
		 * @since 2.1
		 */
		public function show_product_table() {
			global $product;
			/**
			 * APPLY_FILTERS: yith_delivery_date_show_quantity_table
			 *
			 * This filter allow to show or no the quantity table in the product.
			 *
			 * @param bool $force_show True or false.
			 *
			 * @return bool
			 */
			if ( $product && ! $product->is_downloadable() && ! $product->is_virtual() && apply_filters( 'yith_delivery_date_show_quantity_table', true, $product ) ) {

				$this->found_product_table( $product );
			}
		}

		/**
		 * Find the right table for a product
		 *
		 * @param WC_Product $product The product.
		 *
		 * @since 2.1
		 */
		public function found_product_table( $product ) {

			$table_id = $this->get_product_table_id( $product );

			wc_get_template(
				'woocommerce/single-product/quantity-table.php',
				array(
					'quantity_table_id' => $table_id,
					'product_id'        => $product->get_id(),
				),
				YITH_DELIVERY_DATE_TEMPLATE_PATH,
				YITH_DELIVERY_DATE_TEMPLATE_PATH
			);

		}

		/**
		 * Get the current table_id
		 *
		 * @param WC_Product $product the product.
		 *
		 * @return int|bool
		 */
		public function get_product_table_id( $product ) {

			$product_id = $product->get_id();
			$tables     = YITH_Delivery_Product_Quantity_Table()->get_tables_by_product_ids( array( $product_id ) );

			if ( 0 === count( $tables ) ) {

				if ( $product->is_type( 'variation' ) ) {

					$product_id = $product->get_parent_id();

					$tables = YITH_Delivery_Product_Quantity_Table()->get_tables_by_product_ids( array( $product_id ) );
				}
				if ( 0 === count( $tables ) ) {
					$category_ids = wc_get_product_cat_ids( $product_id );

					$tables = YITH_Delivery_Product_Quantity_Table()->get_tables_by_product_category_ids( $category_ids );
				}
			}

			$table_id = ! empty( $tables ) ? current( $tables ) : false;

			return $table_id;
		}

		/**
		 * Enqueue frontend scripts
		 *
		 * @since 2.1
		 */
		public function enqueue_scripts() {

			wp_register_script( 'ywcdd_quantity_table', YITH_DELIVERY_DATE_ASSETS_URL . 'js/' . yit_load_js_file( 'yith_delivery_date_quantity_table_frontend.js' ), array( 'jquery' ), YITH_DELIVERY_DATE_VERSION, true );
			wp_register_style( 'ywcdd_quantity_table', YITH_DELIVERY_DATE_ASSETS_URL . 'css/yith_delivery_date_qty_table_frontend.css', array(), YITH_DELIVERY_DATE_VERSION );

			if ( is_product() ) {

				wp_enqueue_style( 'ywcdd_quantity_table' );
				wp_enqueue_script( 'ywcdd_quantity_table' );
			}
		}

		/**
		 * Create or return the instance
		 *
		 * @return YITH_Delivery_Date_Product_Table_Frontend
		 * @since 2.1.0
		 */
		public static function get_instance() {

			if ( is_null( self::$_instance ) ) {

				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Create a different cart item also for the same product for the same quantity
		 *
		 * @param array $cart_item_data The cart item.
		 * @param int   $product_id The product id.
		 * @param int   $variation_id The variation id.
		 * @param int   $quantity The quantity.
		 *
		 * @return array
		 * @since 2.1
		 */
		public function add_cart_item_data( $cart_item_data, $product_id, $variation_id, $quantity ) {
			if ( ! empty( $_REQUEST['ywcdd_date_selected'] ) && ! empty( $_REQUEST['ywcdd_new_price'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$cart_item_data['ywcdd_item_added_on'] = date( 'Y-m-d H:i:s' ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
			}

			return $cart_item_data;
		}

		/**
		 * Add the plugin meta and set the new product price
		 *
		 * @param array  $cart_item The cart item.
		 * @param string $key The cart item key.
		 *
		 * @return  array
		 * @since 2.1
		 */
		public function add_cart_item( $cart_item, $key ) {

			if ( ! empty( $_REQUEST['ywcdd_date_selected'] ) && ! empty( $_REQUEST['ywcdd_new_price'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				$product = $cart_item['data'];

				$new_price            = wp_unslash( $_REQUEST['ywcdd_new_price'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$date_selected        = wp_unslash( $_REQUEST['ywcdd_date_selected'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$last_shipping_date   = wp_unslash( $_REQUEST['ywcdd_last_shipping_date'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
				$processing_method_id = wp_unslash( $_REQUEST['ywcdd_processing_method_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
				$carrier_id           = wp_unslash( $_REQUEST['ywcdd_carrier_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated

				$cart_item['ywcdd_date_selected']         = $date_selected;
				$cart_item['ywcdd_new_price']             = $new_price;
				$cart_item['_ywcdd_last_shipping_date']   = $last_shipping_date;
				$cart_item['_ywcdd_processing_method_id'] = $processing_method_id;
				$cart_item['_ywcdd_carrier_id']           = $carrier_id;
				$product->set_price( $new_price );
			}

			return $cart_item;
		}

		/**
		 * Set the product price from session
		 *
		 * @param array $cart_item The cart item.
		 * @param array $values The values.
		 *
		 * @return array
		 * @since 2.1
		 */
		public function set_delivery_item_meta_from_session( $cart_item, $values ) {

			$product = $cart_item['data'];

			if ( isset( $values['ywcdd_new_price'] ) ) {
				$product->set_price( $values['ywcdd_new_price'] );
			}

			return $cart_item;
		}

		/**
		 * Show the delivery item date in the product item
		 *
		 * @param array $item_data The item data.
		 * @param array $cart_item The cart item.
		 *
		 * @return array
		 * @since 2.1
		 */
		public function add_item_data( $item_data, $cart_item ) {

			if ( ! empty( $cart_item['ywcdd_date_selected'] ) ) {

				if ( empty( $item_data ) ) {
					$item_data = array();
				}

				$item_data[] = array(
					'key'   => __( 'Delivery Date', 'yith-woocommerce-delivery-date' ),
					'value' => wc_format_datetime( new WC_DateTime( $cart_item['ywcdd_date_selected'], new DateTimeZone( 'UTC' ) ) ),
				);

			}

			return $item_data;
		}

		/**
		 * Add plugin meta data in the checkout page
		 *
		 * @param WC_Order_Item_Product $item_product The order product item.
		 * @param string                $key The cart item key.
		 * @param array                 $cart_item th cart item.
		 *
		 * @return WC_Order_Item_Product
		 * @throws Exception Exception if add meta data fail.
		 * @since 2.1.0
		 *
		 */
		public function add_custom_item_meta_checkout( $item_product, $key, $cart_item ) {

			if ( ! empty( $cart_item['ywcdd_date_selected'] ) ) {

				try {
					$date = date( 'Y-m-d', strtotime( $cart_item['ywcdd_date_selected'] ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
					$item_product->add_meta_data( 'ywcdd_product_delivery_date', $date );

				} catch ( Exception $e ) {
					error_log( $e->getMessage() ); // phpcs:ignore
				};
			}

			if ( ! empty( $cart_item['_ywcdd_last_shipping_date'] ) ) {

				$item_product->add_meta_data( '_ywcdd_last_shipping_date', $cart_item['_ywcdd_last_shipping_date'] );
			}
			if ( ! empty( $cart_item['_ywcdd_carrier_id'] ) ) {

				$item_product->add_meta_data( '_ywcdd_carrier_id', $cart_item['_ywcdd_carrier_id'] );
			}

			if ( ! empty( $cart_item['_ywcdd_processing_method_id'] ) ) {

				$item_product->add_meta_data( '_ywcdd_processing_method_id', $cart_item['_ywcdd_processing_method_id'] );
			}

			return $item_product;
		}

		/**
		 * Show the formatted value for the custom plugin meta
		 *
		 * @param string       $display_key The display key.
		 * @param WC_Meta_Data $meta The meta data.
		 *
		 * @return string
		 * @since 2.1.0
		 */
		public function change_order_item_display_meta_key( $display_key, $meta ) {

			if ( 'ywcdd_product_delivery_date' === $display_key ) {
				$display_key = __( 'Delivery Date', 'yith-woocommerce-delivery-date' );
			}

			if ( '_ywcdd_last_shipping_date' === $display_key ) {
				$display_key = __( 'Last shipping date', 'yith-woocommerce-delivery-date' );
			}

			if ( '_ywcdd_processing_method_id' === $display_key ) {
				$display_key = __( 'Processing Method ID', 'yith-woocommerce-delivery-date' );
			}

			if ( '_ywcdd_carrier_id' === $display_key ) {
				$display_key = __( 'Carrier ID', 'yith-woocommerce-delivery-date' );
			}

			return $display_key;
		}

		/**
		 * Show the formatted value for the custom plugin meta
		 *
		 * @param string       $display_value The date to show.
		 * @param WC_Meta_Data $meta The meta data.
		 *
		 * @return string
		 * @since 2.1
		 */
		public function change_order_item_display_meta_value( $display_value, $meta ) {

			if ( 'ywcdd_product_delivery_date' === $meta->key && ! is_admin() ) {

				$display_value = wc_format_datetime( new WC_DateTime( $display_value, new DateTimeZone( 'UTC' ) ) );
			}

			return $display_value;
		}

		/**
		 * Add the delivery and shipping events into calendar
		 *
		 * @param int    $order_id The order id.
		 * @param string $old_status The old order status.
		 * @param string $new_status The new order status.
		 *
		 * @since 2.1
		 */
		public function manage_order_event( $order_id, $old_status, $new_status ) {

			$order = wc_get_order( $order_id );
			/**
			 * APPLY_FILTERS: yith_delivery_date_order_has_child
			 *
			 * Check if the order has a child or not.
			 *
			 * @param bool $has_child True or false
			 * @param int  $order_id The order id.
			 *
			 * @return bool
			 */
			$has_child = apply_filters( 'yith_delivery_date_order_has_child', false, $order_id );

			if ( ! $has_child ) {

				$order_items = $order->get_items();

				foreach ( $order_items as $key => $order_item ) {

					$delivery_date        = $order_item->get_meta( 'ywcdd_product_delivery_date' );
					$last_shipping_date   = $order_item->get_meta( '_ywcdd_last_shipping_date' );
					$processing_method_id = $order_item->get_meta( '_ywcdd_processing_method_id' );
					$carrier_id           = $order_item->get_meta( '_ywcdd_carrier_id' );

					if ( ! empty( $delivery_date ) ) {

						$add_event_order_status = get_option(
							'ywcdd_add_event_into_calendar',
							array(
								'processing',
								'completed',
							)
						);

						if ( in_array( $new_status, $add_event_order_status, true ) ) {
							/** Add new shipping event and add new delivery event into calendar */
							YITH_Delivery_Date_Calendar()->add_calendar_event( $processing_method_id, '', 'shipping_to_carrier', $last_shipping_date, '', $order_id, true );

							YITH_Delivery_Date_Calendar()->add_calendar_event( $carrier_id, '', 'delivery_day', $delivery_date, $delivery_date, $order_id, true );
						} else {

							YITH_Delivery_Date_Calendar()->delete_event_by_order_id( $order_id );

						}
					}
				}
			}
		}

		/**
		 * Add filter to show the hidden information, into admin email
		 *
		 * @param WC_Order $order The order.
		 * @param bool     $sent_to_admin Check if sent to admin.
		 * @param string   $plain_text  Plain text.
		 *
		 * @since 2.1
		 */
		public function check_if_show_hide_item_meta( $order, $sent_to_admin, $plain_text ) {

			if ( $sent_to_admin ) {

				add_filter(
					'woocommerce_order_item_get_formatted_meta_data',
					array(
						$this,
						'include_hidden_meta_on_admin_emails',
					),
					20,
					2
				);
			} else {
				remove_filter(
					'woocommerce_order_item_get_formatted_meta_data',
					array(
						$this,
						'include_hidden_meta_on_admin_emails',
					),
					20
				);
			}
		}

		/**
		 * Show in the admin email the hidden meta
		 *
		 * @param array    $formatted_meta The formatted meta array.
		 * @param WC_Order $order The order.
		 *
		 * @return array
		 * @since 2.1
		 */
		public function include_hidden_meta_on_admin_emails( $formatted_meta, $order ) {

			$meta_data = $order->get_meta_data();

			foreach ( $meta_data as $meta ) {

				if ( '_ywcdd_last_shipping_date' === $meta->key ) {

					$meta->key   = rawurldecode( (string) $meta->key );
					$meta->value = rawurldecode( (string) $meta->value );

					$meta_value                  = wc_format_datetime( new WC_DateTime( $meta->value, new DateTimeZone( 'UTC' ) ) );
					$display_value               = wp_kses_post( $meta_value );
					$formatted_meta[ $meta->id ] = (object) array(
						'key'           => $meta->key,
						'value'         => $meta->value,
						'display_key'   => __( 'Shipping within', 'yith-woocommerce-delivery-date' ),
						'display_value' => $display_value,

					);
				}

				if ( '_ywcdd_carrier_id' === $meta->key ) {

					$meta->key   = rawurldecode( (string) $meta->key );
					$meta->value = rawurldecode( (string) $meta->value );

					$meta_value                  = get_the_title( $meta->value );
					$display_value               = wp_kses_post( $meta_value );
					$formatted_meta[ $meta->id ] = (object) array(
						'key'           => $meta->key,
						'value'         => $meta->value,
						'display_key'   => __( 'Carrier', 'yith-woocommerce-delivery-date' ),
						'display_value' => $display_value,

					);
				}
			}

			return $formatted_meta;
		}

		/**
		 * Find the quantity table for variation product
		 *
		 * @param array                $variation_data Variation data array.
		 * @param WC_Product_Variable  $variable_product The variable product.
		 * @param WC_Product_Variation $variation_product The variation product.
		 *
		 * @return array
		 * @since 2.1
		 */
		public function add_variation_data( $variation_data, $variable_product, $variation_product ) {

			ob_start();
			$this->found_product_table( $variation_product );
			$info = ob_get_contents();
			ob_end_clean();

			$variation_data['ywcdd_variation_table'] = $info;

			return $variation_data;
		}

		/**
		 * Exclude some product type from plugin
		 *
		 * @param bool       $show Show or not the table for a product.
		 * @param WC_Product $product The product.
		 *
		 * @return bool
		 * @since 2.1
		 */
		public function exclude_product_type( $show, $product ) {

			$check_type = array( 'booking', 'gift-card', 'ywf_deposit' );

			if ( in_array( $product->get_type(), $check_type, true ) ) {
				$show = false;
			}

			return $show;
		}

		/**
		 * If a product need to a date, change the add to cart link in loop
		 *
		 * @param string     $link The add to cart link.
		 * @param WC_Product $product The product.
		 * @param array      $args Extra args.
		 *
		 * @return string
		 */
		public function change_add_to_cart_link( $link, $product, $args ) {

			if ( $product && ! $product->is_downloadable() && ! $product->is_virtual() && $this->get_product_table_id( $product ) ) {

				if ( $product->is_type( array( 'simple', 'variation' ) ) ) {

					$class  = isset( $args['class'] ) ? $args['class'] : 'button';
					$class  = str_replace( array( 'add_to_cart_button', 'ajax_add_to_cart' ), array( '', '' ), $class );
					$class .= ' ywcdd_choose_date_button';
					$link   = sprintf(
						'<a href="%s" class="%s" %s>%s</a>',
						esc_url( $product->get_permalink() ),
						esc_attr( $class ),
						isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
						esc_html( __( 'Choose a date', 'yith-woocommerce-delivery-date' ) )
					);
				}
			}

			return $link;
		}
	}
}

if ( ! function_exists( 'YITH_Delivery_Date_Product_Table_Frontend' ) ) {
	/**
	 * Return the instance of the class
	 *
	 * @since 2.0.0
	 * @return YITH_Delivery_Date_Product_Table_Frontend
	 */
	function YITH_Delivery_Date_Product_Table_Frontend() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
		$option = get_option( 'ywcdd_processing_type', 'checkout' );
		if ( 'product' === $option ) {
			return YITH_Delivery_Date_Product_Table_Frontend::get_instance();
		}
	}
}
YITH_Delivery_Date_Product_Table_Frontend();
