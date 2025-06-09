<?php
/**
 * Show the carrier table
 *
 * @package YITH WooCommerce Delivery Date\Templates\Admin\Tabs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$current_tab = isset( $_GET['tab'] ) ? wp_unslash( $_GET['tab'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
?>
<div id="yith_delivery_date_panel_<?php echo esc_attr( $current_tab ); ?>" class="yith-plugin-fw  yit-admin-panel-container">
	<div class="yit-admin-panel-content-wrap">
		<form id="plugin-fw-wc" method="post">
			<table class="form-table">
				<tbody>
				<tr valign="top" class="yith-plugin-fw-panel-wc-row list-table">
					<td class="forminp forminp-list-table">
						<?php
							$args = array(
								'type'                 => 'list-table',
								'class'                => '',
								'post_type'            => 'yith_carrier',
								'list_table_class'     => 'YITH_Carrier_Table',
								'list_table_class_dir' => YITH_DELIVERY_DATE_INC . 'admin-tables/class.yith-delivery-date-carrier-table.php',
								'title'                => YITH_Delivery_Date_Carrier()->get_taxonomy_label( 'name' ),
								'add_new_button'       => YITH_Delivery_Date_Carrier()->get_taxonomy_label( 'add_new' ),
								'id'                   => 'ywcdd_carrier_list_table',
							);

							echo yith_plugin_fw_get_field( $args, true ); //phpcs:ignore WordPress.Security.EscapeOutput
							?>
					</td>
				</tr>
				</tbody>
			</table>
		</form>
	</div>
</div>

