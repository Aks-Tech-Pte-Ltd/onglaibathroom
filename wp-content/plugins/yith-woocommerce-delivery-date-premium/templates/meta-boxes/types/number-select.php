<?php
/**
 * Custom field, to show a list of number and select
 *
 * @package YITH WooCommerce Delivery Date\Templates\Meta Boxes\Types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

wp_enqueue_script( 'wc-enhanced-select' );
extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract

$value = get_post_meta( $post->ID, $id, true );

if ( empty( $value ) ) {
	$value = $std;
}

if ( ! empty( $value ) && ! is_array( $value ) ) {
	$value = array(
		array(
			'shipping_zone' => 'all',
			'day'           => $value,
		),
	);
}
$select_options         = yith_delivery_date_get_shipping_zones();
$select_options ['all'] = __( 'All Zones', 'yith-woocommerce-delivery-date' );

?>
<div id="<?php echo esc_attr( $id ); ?>-container" class="yith-plugin-fw-metabox-field-row">
	<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
	<div class="yith_delivery_date_fields_container">
		<div class="ywcdd_field_list">
			<?php
			foreach ( $value as $i => $estimated_day ) :
				$number_args = array(
					'id'                => 'yith_estimated_day_' . $i,
					'type'              => 'number',
					'custom_attributes' => 'min="0"',
					'value'             => $estimated_day['day'],
					'name'              => $name . "[$i][day]",
				);

				$select_args = array(
					'id'      => 'yith_estimated_shipping_zone_' . $i,
					'type'    => 'select',
					'class'   => 'wc-enhanced-select',
					'options' => $select_options,
					'name'    => $name . "[$i][shipping_zone]",
					'value'   => $estimated_day['shipping_zone'],
				);
				?>
				<div class="ywcdd_estimated_day_row">
					<?php
					echo yith_plugin_fw_get_field( $number_args, true );//phpcs:ignore WordPress.Security.EscapeOutput
					echo '<span class="desc">' . esc_html__( 'days for zone', 'yith-woocommerce-delivery-date' ) . '</span>';
					echo yith_plugin_fw_get_field( $select_args, true );//phpcs:ignore WordPress.Security.EscapeOutput
					?>
					<a href="" class="ywcdd_delete_range" title=""><span class="yith-icon icon-trash"></span></a>

				</div>

			<?php endforeach; ?>
		</div>
		<a class="ywcdd_add_new_day" href="">+<?php esc_html_e( 'Add another estimated day for a zone', 'yith-woocommerce-delivery-date' ); ?></a>
		<span class="description"><?php echo esc_html( $desc ); ?></span>
	</div>
	<script type="text/template" id="tmpl-ywcdd-carrier-estimated-days">
		<?php
		$number_args = array(
			'id'                => 'yith_estimated_day_{{{data.index}}}',
			'type'              => 'number',
			'custom_attributes' => 'min="0"',
			'value'             => 3,
			'name'              => $name . '[{{{data.index}}}][day]',
		);

		$select_args = array(
			'id'      => 'yith_estimated_shipping_zone_{{{data.index}}}',
			'type'    => 'select',
			'class'   => 'wc-enhanced-select',
			'options' => $select_options,
			'name'    => $name . '[{{{data.index}}}][shipping_zone]',
			'value'   => 'all',
		);
		?>
		<div class="ywcdd_estimated_day_row">
			<?php
			echo yith_plugin_fw_get_field( $number_args, true );//phpcs:ignore WordPress.Security.EscapeOutput
			echo '<span class="desc">' . esc_html__( 'days for zone', 'yith-woocommerce-delivery-date' ) . '</span>';
			echo yith_plugin_fw_get_field( $select_args, true );//phpcs:ignore WordPress.Security.EscapeOutput
			?>
			<a href="" class="ywcdd_delete_range" title=""><span class="yith-icon icon-trash"></span></a>

		</div>
	</script>
</div>
