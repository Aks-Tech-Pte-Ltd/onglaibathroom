<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Check list day, custom field to show a list with day and time
 *
 * @package YITH WooCommerce Delivery Date\Templates\Meta Boxes\Types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $post;
extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract

$days          = yith_get_worksday();
$days_selected = get_post_meta( $post->ID, $id, true );
$select_all    = get_post_meta( $post->ID, '_ywcdd_all_day', true );

$select_all = empty( $select_all ) ? 'no' : $select_all;

if ( empty( $days_selected ) ) {

	$days_selected = array();

	foreach ( $days as $key => $day ) {
		$new_opt = array(
			'day'       => $key,
			'timelimit' => '',
			'enabled'   => 'no',
		);

		$days_selected[] = $new_opt;
	}
}
?>
<div class="<?php echo esc_attr( $id ); ?>-container">
	<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
	<ul class="ywcdd_list_day">
		<li>
			<div class="ywcdd_item ywcdd_check">
				<input type="checkbox" class="ywcdd_all_day" <?php checked( 'yes', $select_all ); ?> />
				<input type="hidden" value="<?php echo esc_attr( $select_all ); ?>" name="ywcdd_all_day" class="ywcdd_value_en"/>
			</div>
			<div class="ywcdd_item ywcdd_day">
				<?php esc_html_e( 'All Days', 'yith-woocommerce-delivery-date' ); ?>
			</div>

		</li>
		<?php foreach ( $days_selected as $key => $day_selected ) : ?>
			<li>
				<div class="ywcdd_item ywcdd_check">
					<input type="checkbox" class="ywcdd_enable_day" <?php checked( $day_selected['enabled'], 'yes' ); ?>/>
					<input type="hidden" value="<?php echo esc_attr( $day_selected['enabled'] ); ?>" name="ywcdd_enable_day[<?php echo esc_attr( $day_selected['day'] ); ?>]" class="ywcdd_value_en"/>
				</div>
				<div class="ywcdd_item ywcdd_day">
					<?php echo esc_html( $days[ $day_selected['day'] ] ); ?>
				</div>
				<div class="ywcdd_item ywcdd_timelimit">
					<input type="text" class="yith_timepicker" name="ywcdd_timelimit[<?php echo esc_attr( $day_selected['day'] ); ?>]" value="<?php echo esc_attr( $day_selected['timelimit'] ); ?>" placeholder="<?php esc_attr_e( 'Time Limit', 'yith-woocommerce-delivery-date' ); ?>"/>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
	<span class="desc inline"><?php echo esc_html( $desc ); ?></span>
</div>
