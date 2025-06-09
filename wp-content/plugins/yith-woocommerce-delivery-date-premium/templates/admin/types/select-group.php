<?php
/**
 * This field allow to use the enhanced select with groups
 *
 * @package YITH WooCommerce Delivery Date\Templates\Admin\Types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
wp_enqueue_script( 'wc-enhanced-select' );

extract( $field ); // phpcs:ignore WordPress.PHP.DontExtract
$multiple      = isset( $multiple ) && $multiple;
$multiple_html = ( $multiple ) ? ' multiple' : '';
$name          = $multiple ? $name . '[]' : $name;
if ( $multiple && ! is_array( $value ) ) {
	$value = array();
}

$class = isset( $class ) ? $class : '';
?>
	<select <?php echo $multiple_html; //phpcs:ignore WordPress.Security.EscapeOutput ?>
		id="<?php echo esc_attr( $id ); ?>"
		name="<?php echo esc_attr( $name ); ?>" <?php if ( isset( $std ) ) : ?>
		data-std="<?php echo ( $multiple ) ? implode( ' ,', $std ) : $std; //phpcs:ignore WordPress.Security.EscapeOutput ?>"<?php endif ?>
		class="wc-enhanced-select <?php echo esc_attr( $class ); ?>"
		<?php echo $custom_attributes; //phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php
		if ( isset( $data ) ) {
			echo yith_plugin_fw_html_data_to_string( $data ); //phpcs:ignore WordPress.Security.EscapeOutput
		}
		?>
	>
		<?php foreach ( $groups as $group => $options ) : ?>
			<optgroup label="<?php echo esc_attr( $options['label'] ); ?>">
				<?php foreach ( $options['options'] as $key => $item ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>"
						<?php
						if ( $multiple ) :
							selected( true, in_array( $key, $value ) );  // phpcs:ignore WordPress.PHP.StrictInArray
						else :
							selected( $key, $value );
						endif;
						?>
					><?php echo esc_html( $item ); ?></option>
				<?php endforeach; ?>
			</optgroup>
		<?php endforeach; ?>
	</select>

<?php
/* --------- BUTTONS ----------- */

$button_field = array(
	'type'    => 'buttons',
	'buttons' => array(
		array(
			'name'  => __( 'Select All', 'yith-plugin-fw' ),
			'class' => 'yith-plugin-fw-select-all',
			'data'  => array(
				'select-id' => $field['id'],
			),
		),
		array(
			'name'  => __( 'Deselect All', 'yith-plugin-fw' ),
			'class' => 'yith-plugin-fw-deselect-all',
			'data'  => array(
				'select-id' => $field['id'],
			),
		),
	),
);
yith_plugin_fw_get_field( $button_field, true );
