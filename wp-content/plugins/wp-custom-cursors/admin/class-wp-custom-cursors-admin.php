<?php

/**
 *
 * @link       https://codecanyon.net/user/web_trendy
 * @since      1.0.0
 *
 * @package    Wp_custom_cursors
 * @subpackage Wp_custom_cursors/admin
 */
 
/**
 *
 * @package    Wp_custom_cursors
 * @subpackage Wp_custom_cursors/admin
 * @author     Web_Trendy <webtrendyio@gmail.com>
 */
class Wp_custom_cursors_Admin {

	/**
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		$screen = get_current_screen();
		$base = $screen->base;

		$pages = ['wp_custom_cursors', 'wpcc_add_new'];

		foreach ($pages as $page) {
			$pos = strripos($base, $page);
			if (!($pos === false)) {
			    wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-custom-cursors-admin.css', array(), $this->version, 'all' );
	            wp_enqueue_style( 'bootstrapcss', plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css', array(), $this->version, 'all' );
	            
	            wp_enqueue_style( 'remixicon', plugin_dir_url( __FILE__ ) . 'fonts/remixicon.css', array(), $this->version, 'all' );
	            
	            wp_enqueue_style( 'spectrum', plugin_dir_url( __FILE__ ) . 'css/spectrum.min.css', array(), $this->version, 'all' );

	            wp_enqueue_style( 'select2', plugin_dir_url( __FILE__ ) . 'css/select2.min.css', array(), $this->version, 'all' );
			} 
		}

	}

	/**
	 *
	 * @since    1.1.0
	 */
	public function enqueue_scripts() {
		$image_path = [plugins_url( 'img', __FILE__ )];

		$screen = get_current_screen();
		$base = $screen->base;

		$pages = ['wpcc_add_new'];

		foreach ($pages as $page) {
			$pos = strripos($base, $page);
			if (!($pos === false)) {
			    wp_enqueue_script( 'bootstrapjs', plugin_dir_url( __FILE__ ) . 'js/bootstrap.min.js', array(), $this->version, 'all' );
			    
			    wp_enqueue_script( 'interactjs', plugin_dir_url( __FILE__ ) . 'js/interact.min.js', array(), $this->version, 'all' );
			    
			    wp_enqueue_script( 'spectrum', plugin_dir_url( __FILE__ ) . 'js/spectrum.min.js', array(), $this->version, 'all' );

			    wp_enqueue_script( 'formtowizard', plugin_dir_url( __FILE__ ) . 'js/jquery.formtowizard.js', array(), $this->version, 'all' );

	            wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-custom-cursors-admin.js', array(), $this->version, 'all' );
	            wp_localize_script( $this->plugin_name, 'wpcc_image_path', $image_path );
	            wp_enqueue_media();
			} 
		}

	}

	/**
	 *
	 * @since    1.0.0 
	 */
	public function wp_custom_cursors_add_admin_menu(  ) {
	    add_menu_page( esc_html__('WP Custom Cursors', 'wp-custom-cusors'), esc_html__('WP Custom Cursors', 'wp-custom-cusors'), 'manage_options', 'wp_custom_cursors', 'wp_custom_cursors_render_options_page', 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMjEuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgdmlld0JveD0iMCAwIDUxMiA1MTIiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDUxMiA1MTI7IiB4bWw6c3BhY2U9InByZXNlcnZlIiB3aWR0aD0iNTEyIiBoZWlnaHQ9IjUxMiI+CjxnPgoJPHBhdGggZmlsbD0iI2EwYTVhYSIgZD0iTTUwMi45LDMwNC44MzRMMTg4LjQ4MSwxNjguNzc5Yy01LjY0LTIuMzg4LTEyLjE3My0xLjE0My0xNi41MDksMy4xOTNzLTUuNTk2LDEwLjg2OS0zLjE5MywxNi41MDlsMTM2LjA1NSwzMTQuNDIyICAgYzIuMzg4LDUuNTUyLDcuODM3LDkuMDk3LDEzLjc5OSw5LjA5N2MwLjQzOSwwLDAuODk0LTAuMDE1LDEuMzYyLTAuMDU5YzYuNDc1LTAuNTg2LDExLjgzNi01LjI4OCwxMy4yNzEtMTEuNjMxbDMxLjU1My0xMzUuNDkxICAgbDEzNS40ODgtMzEuNTUzYzYuMzQzLTEuNDM2LDExLjA0NS02Ljc5NywxMS42MzEtMTMuMjcxUzUwOC44NzcsMzA3LjM5Nyw1MDIuOSwzMDQuODM0eiIvPgoJPHBhdGggZmlsbD0iI2EwYTVhYSIgZD0iTTE5NSwxMjBjOC4yOTEsMCwxNS02LjcwOSwxNS0xNVYxNWMwLTguMjkxLTYuNzA5LTE1LTE1LTE1cy0xNSw2LjcwOS0xNSwxNXY5MEMxODAsMTEzLjI5MSwxODYuNzA5LDEyMCwxOTUsMTIweiIvPgoJPHBhdGggZmlsbD0iI2EwYTVhYSIgZD0iTTEyMC43NjIsMjQ4LjAyN2wtNjMuNjQ3LDYzLjY0N2MtNS44NTksNS44NTktNS44NTksMTUuMzUyLDAsMjEuMjExYzUuODYsNS44NiwxNS4zNTEsNS44NiwyMS4yMTEsMGw2My42NDctNjMuNjQ3ICAgYzUuODU5LTUuODU5LDUuODU5LTE1LjM1MiwwLTIxLjIxMVMxMjYuNjIxLDI0Mi4xNjgsMTIwLjc2MiwyNDguMDI3eiIvPgoJPHBhdGggZmlsbD0iI2EwYTVhYSIgZD0iTTI2OS4yMzgsMTQxLjk3M2w2My42NDctNjMuNjQ3YzUuODU5LTUuODU5LDUuODU5LTE1LjM1MiwwLTIxLjIxMXMtMTUuMzUyLTUuODU5LTIxLjIxMSwwbC02My42NDcsNjMuNjQ3ICAgYy01Ljg1OSw1Ljg1OS01Ljg1OSwxNS4zNTIsMCwyMS4yMTFDMjUzLjg4NywxNDcuODMyLDI2My4zNzksMTQ3LjgzMiwyNjkuMjM4LDE0MS45NzN6Ii8+Cgk8cGF0aCBmaWxsPSIjYTBhNWFhIiBkPSJNNzguMzI1LDU3LjExNGMtNS44NTktNS44NTktMTUuMzUyLTUuODU5LTIxLjIxMSwwcy01Ljg1OSwxNS4zNTIsMCwyMS4yMTFsNjMuNjQ3LDYzLjY0N2M1Ljg2LDUuODYsMTUuMzUxLDUuODYsMjEuMjExLDAgICBjNS44NTktNS44NTksNS44NTktMTUuMzUyLDAtMjEuMjExTDc4LjMyNSw1Ny4xMTR6Ii8+Cgk8cGF0aCBmaWxsPSIjYTBhNWFhIiBkPSJNMTIwLDE5NWMwLTguMjkxLTYuNzA5LTE1LTE1LTE1SDE1Yy04LjI5MSwwLTE1LDYuNzA5LTE1LDE1czYuNzA5LDE1LDE1LDE1aDkwQzExMy4yOTEsMjEwLDEyMCwyMDMuMjkxLDEyMCwxOTV6Ii8+CjwvZz4KCgoKCgoKCgoKCgoKCgoKPC9zdmc+Cg==' );

	    add_submenu_page( 'wp_custom_cursors', esc_html__( 'Add New', 'wp-custom-cusors' ), esc_html__( 'Add New', 'wp-custom-cusors' ), 'manage_options', 'wpcc_add_new', 'add_new_display_func' );


	    function wp_custom_cursors_render_options_page(  ) {
	    	if ( isset( $_POST['submit'] ) && check_admin_referer( 'wpcc_delete_cursor', 'wpcc_delete_cursor_nonce' ) ) {
	    		global $wpdb;
				$tablename = $wpdb->prefix . "custom_cursors";

				$delete_row = sanitize_text_field( $_POST['delete_row'] );

				$sql = $wpdb->prepare("DELETE from $tablename WHERE cursor_id = %d", array($delete_row));
				$deleted = $wpdb->query($sql);
				
				if($deleted) {
					//echo 'Deleted successfully';
			    } 
			    else {
					//echo 'Not deleted';
				}
	    	}
		    ?>
		    <div class="wt-page mt-3 mr-4">
				
				<?php include_once( 'partials/wp-custom-cursors-header.php' ); ?>

				<!-- Body -->
				<div class="wt-body">
					<?php  
						global $wpdb;
						$tablename = $wpdb->prefix . "custom_cursors";
					    $cursors = $wpdb->get_results("SELECT cursor_id, cursor_type, cursor_shape, cursor_image, cursor_text, activate_on, default_cursor, selector_type, selector_data, color, width, blending_mode FROM $tablename");

					    $no = 1;

					    if ($cursors) {
					    ?>
					    	<a href="<?php menu_page_url('wpcc_add_new', true) ?>" class="btn btn-primary d-inline-flex fw-light my-3"><?php echo esc_html__( 'Add new cursor', 'wp-custom-cusors' ); ?> <i class="ri-focus-line ms-1"></i></a>
					
							<table class="table cursors-table text-center">
								<thead class="thead-dark">
									<tr>
										<th scope="col">#</th>
										<th scope="col"><?php echo esc_html__( 'Type', 'wp-custom-cusors' ); ?></th>
										<th scope="col"><?php echo esc_html__( 'Cursor', 'wp-custom-cusors' ); ?></th>
										<th scope="col"><?php echo esc_html__( 'Apply To', 'wp-custom-cusors' ); ?></th>
										<th scope="col"><?php echo esc_html__( 'Color', 'wp-custom-cusors' ); ?></th>
										<th scope="col"><?php echo esc_html__( 'Width', 'wp-custom-cusors' ); ?></th>
										<th scope="col"><?php echo esc_html__( 'Blending Mode', 'wp-custom-cusors' ); ?></th>
										<th scope="col"><?php echo esc_html__( 'Action', 'wp-custom-cusors' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									    foreach ($cursors as $cursor) { ?>
									    	<tr>
												<th scope="row"><?php echo esc_html( $no ); ?></th>
												<td>
													<?php 
														echo esc_html(ucfirst($cursor->cursor_type));
													?>
												</td>
												<td><?php 
													switch ($cursor->cursor_type) {
														case "shape":
													    ?>
													    <img src="<?php echo esc_url( plugins_url( 'img/cursors/'.$cursor->cursor_shape.'.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Cursor Shape '.$cursor->cursor_shape, 'wp-custom-cusors');?>" class="list-shape-image" />
													    <?php
													    break;
														case "image":
													    ?>
														<img src="<?php echo esc_attr($cursor->cursor_image); ?>" alt="<?php echo esc_html__('Cursor Image', 'wp-custom-cusors');?>" class="list-cursor-image" />
												    	<?php
													    break;
														case "text":
													    echo esc_html($cursor->cursor_text);
													    break;
													}
													?>
												</td>
												<td>
													<?php if ($cursor->activate_on == 0) {
													echo esc_html__( 'Body', 'wp-custom-cusors' );
													} 
													else {
														switch ($cursor->selector_type) {
															case 'tag':
																echo "&lt;".esc_html($cursor->selector_data)."&gt;";
																break;
															case 'class':
																echo ".".esc_html($cursor->selector_data);
																break;
															case 'id':
																echo "#".esc_html($cursor->selector_data);
																break;
															case 'attribute':
																echo "[".esc_html($cursor->selector_data)."]";
																break;
															default:
																echo esc_html__( 'No data!', 'wp-custom-cusors' );
																break;
														}
													} ?>
												</td>
												<td>
													<?php echo esc_html($cursor->color); ?>
												</td>
												<td>
													<?php echo esc_html($cursor->width); ?>
												</td>
												<td>
													<?php echo $cursor->blending_mode; ?>
												</td>
												<td>
													<a href="<?php menu_page_url('wpcc_add_new', true); ?>&edit_row=<?php echo intval( $cursor->cursor_id ); ?>" title="<?php echo esc_html__( 'Edit Cursor', 'wp-custom-cusors' ); ?>" class="wpcc-icon"><i class="ri-pencil-line ri-lg"></i></a>
													<form action="" class="d-inline-block" method="post">
														<input type="hidden" name="delete_row" value="<?php echo esc_html($cursor->cursor_id); ?>">
														<button type="submit" name="submit" title="<?php echo esc_html__( 'Delete Cursor', 'wp-custom-cusors' ); ?>" class="wpcc-icon"><i class="ri-close-fill ri-lg"></i></button>
														<?php wp_nonce_field( 'wpcc_delete_cursor', 'wpcc_delete_cursor_nonce' ); ?>
													</form>
												</td>
											</tr>
									    <?php $no++; }
									?>
								</tbody>
							</table>
					    <?php
					    }
					    else {
					    ?>
					    	<div class="container mt-5">
					    		<div class="row justify-content-center">
					    			<div class="col-sm-4 text-center">
					    				<img src="<?php echo esc_url( plugins_url( 'img/icons/no-cursor.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Add you first cursor!', 'wp-custom-cusors'); ?>" class="img-fluid" />
					    				<div class="mt-3 fw-light"><?php echo esc_html__( 'Click ', 'wp-custom-cusors' );?>
					    					<a href="<?php menu_page_url('wpcc_add_new', true) ?>" class="text-decoration-none fw-normal"><?php echo esc_html__( 'here', 'wp-custom-cusors' );?></a>
					    					<?php echo esc_html__( ' to create your first custom cursor.', 'wp-custom-cusors' );?>
					    				</div>
					    			</div>
					    		</div>
					    	</div>
					    <?php
					    }
					?>
				</div>
				<!-- End Body -->
			</div>
		    
		    
		    <?php
		}

		function add_new_display_func() {
			?>
			<div class="wt-page mt-3 mr-4">
				<?php include_once( 'partials/wp-custom-cursors-header.php' ); ?>

				<!-- Body -->
				<div class="wt-body">
					<?php include_once( 'partials/wp-custom-cursors-add-new.php' ); ?>
				</div>
				<!-- End Body -->
			</div>
			<?php
		}
	}

	/**
	 *
	 * @since    2.2.4 
	 */
	public function add_plugin_settings_link($links) {
		$links[] = '<a href="' .
		admin_url( 'options-general.php?page=wp_custom_cursors' ) .
		'">' . esc_html__('Settings') . '</a>';
		return $links;
	}

}
