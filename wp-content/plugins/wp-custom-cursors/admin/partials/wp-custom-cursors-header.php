<?php

/**
 * @link       https://codecanyon.net/user/web_trendy
 * @since      2.1.0
 * @package    Wp_custom_cursors
 * @subpackage Wp_custom_cursors/includes
 * @author     Web_Trendy <webtrendyio@gmail.com>
 */
?>

<!-- Header -->
<div class="wt-header bg-white rounded d-flex align-items-center p-2">
	<div class="wt-logo mr-3">
		<img src="<?php echo esc_url( plugins_url( '../img/thumbnail.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('WP Custom Cursors', 'wp-custom-cusors');?>" title="<?php echo esc_html__('WP Custom Cursors', 'wp-custom-cusors');?>" />
	</div>
	<div class="wt-header-title ms-3">
		<h2 class="lead d-inline-block"><?php echo esc_html__( 'WP Custom Cursors ', 'wp-custom-cusors' ); ?> </h2>  <span class="badge rounded-pill bg-warning text-dark"><?php echo WP_CUSTOM_CURSORS_VERSION; ?></span>
		<p class="mb-0">
			<a href="<?php echo esc_url('https://codecanyon.net/user/web_trendy/portfolio')?>" class="text-muted text-decoration-none wt-link" target="_blank" title="<?php echo esc_html__('Web_Trendy Portfolio', 'wp-custom-cusors'); ?>"><i class="ri-links-fill"></i> <?php echo esc_html__( 'View Our Portfolio', 'wp-custom-cusors' );?>
			</a>
		</p>
	</div>
</div>
<!-- End Header -->