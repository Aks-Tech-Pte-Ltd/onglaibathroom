<?php // phpcs:ignore WordPress.Files.FileName
/**
 * The class that manage the plugin widget
 *
 * @package YITH WooCommerce Delivery Date\Classes\Widgets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Delivery_Dynamic_Messages_Widget' ) ) {
	/**
	 * Class YITH_Delivery_Dynamic_Messages_Widget
	 */
	class YITH_Delivery_Dynamic_Messages_Widget extends WP_Widget {
		/**
		 * YITH_Delivery_Dynamic_Messages_Widget constructor.
		 */
		public function __construct() {
			parent::__construct(
				'ywcdd_dynamic_messages',
				__( 'YITH Delivery Dynamic Messages', 'yith-woocommerce-delivery-date' ),
				array( 'description' => __( 'Show dynamic messages about delivery. Please note - this widget works only on the single product sidebar', 'yith-woocommerce-delivery-date' ) )
			);
		}

		/**
		 * Show the widget in sidebars
		 *
		 * @author YITH <plugins@yithemes.com>
		 * @since 1.0.0
		 * @param array $args The args.
		 * @param array $instance The instance.
		 *
		 * @throws Exception The exception.
		 */
		public function widget( $args, $instance ) {

			$title    = $instance['title'];
			$title    = apply_filters( 'widget_title', $title, $instance, $this->id_base );
			$template = '';
			global $product;

			if ( is_product() && ! is_null( $product ) ) {
				ob_start();
				YITH_Delivery_Date_Product_Frontend()->get_template_info( $product );
				$template = ob_get_contents();
				ob_end_clean();
				echo $args['before_widget']; //phpcs:ignore WordPress.Security.EscapeOutput
				echo $args['before_title']; //phpcs:ignore WordPress.Security.EscapeOutput
				echo $title; //phpcs:ignore WordPress.Security.EscapeOutput
				echo $args['after_title']; //phpcs:ignore WordPress.Security.EscapeOutput
				echo $template;//phpcs:ignore WordPress.Security.EscapeOutput
				echo $args['after_widget'];//phpcs:ignore WordPress.Security.EscapeOutput
			}
		}

		/**
		 * Show the widget configuration form
		 *
		 * @since 1.0.0
		 * @param array $instance The instance.
		 */
		public function form( $instance ) {

			$title = isset( $instance['title'] ) ? $instance['title'] : '';
			?>
			<div id="ywcca_widget_content">
				<p class="title_shortcode">
					<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'yith-woocommerce-delivery-date' ); ?></label>
					<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" placeholder="<?php esc_attr_e( 'Insert a title', 'yith-woocommerce-delivery-date' ); ?>" value="<?php echo esc_attr( $title ); ?>">
				</p>
			</div>
			<?php
		}

		/**
		 * Update the widget
		 *
		 * @since 1.0.0
		 * @param array $new_instance The new instance.
		 * @param array $old_instance The old instance.
		 *
		 * @return array
		 */
		public function update( $new_instance, $old_instance ) {

			$instance = array(
				'title' => isset( $new_instance['title'] ) ? $new_instance['title'] : '',
			);

			return $instance;
		}
	}
}
