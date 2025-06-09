<?php
	/**
	 * @link       https://codecanyon.net/user/web_trendy
	 * @since      2.1.0
	 * @package    Wp_custom_cursors
	 * @subpackage Wp_custom_cursors/includes
	 * @author     Web_Trendy <webtrendyio@gmail.com>
	 */
 
	// Save the cursor
	if( isset( $_POST['submit'] ) && check_admin_referer( 'wpcc_add_new_cursor', 'wpcc_add_new_nonce' ) ) {
		global $wpdb;
		$tablename = $wpdb->prefix . "custom_cursors";

		$cursor_type = sanitize_text_field( $_POST['cursor_type'] );
		$cursor_shape = sanitize_text_field( $_POST['cursor_shape'] );
		$cursor_image = sanitize_text_field( $_POST['cursor_image'] );
		$cursor_text = sanitize_text_field( $_POST['cursor_text'] );
		$click_point = (isset($_POST['click_point']))? sanitize_text_field( $_POST['click_point'] ) : '50,50' ;
		$default_cursor = (isset($_POST['default_cursor']))? sanitize_text_field( $_POST['default_cursor'] ) : 0 ;
		$hover_cursors = sanitize_text_field( $_POST['hover_cursors'] );
		$activate_on = sanitize_text_field( $_POST['activate_on'] );
		$selector_type = sanitize_text_field( $_POST['selector_type'] );
		if ($_POST['selector_data'] == "") {
			$_POST['selector_data'] = "body";
		}
		$selector_data = sanitize_text_field( $_POST['selector_data'] );
		$color = sanitize_text_field( $_POST['color'] );
		if ($_POST['width'] == 0) {
			$_POST['width'] = 30; 
		}
		$width = sanitize_text_field( $_POST['width'] );
		$blending_mode = sanitize_text_field( $_POST['blending_mode'] );
		if (!isset($_POST['hide_tablet'])) {
			$_POST['hide_tablet'] = 'off';
		}
		$hide_tablet = sanitize_text_field( $_POST['hide_tablet'] );
		if (!isset($_POST['hide_mobile'])) {
			$_POST['hide_mobile'] = 'off';
		}
		$hide_mobile = sanitize_text_field( $_POST['hide_mobile'] );
		
		$success = $wpdb->insert($tablename, array("cursor_type" => $cursor_type, "cursor_shape" => $cursor_shape, "cursor_image" => $cursor_image, "cursor_text" => $cursor_text, "click_point" => $click_point, "default_cursor" => $default_cursor, "color" => $color, "width" => $width, "blending_mode" => $blending_mode, "hide_tablet" => $hide_tablet, "hide_mobile" => $hide_mobile, "hover_cursors" => $hover_cursors, "activate_on" => $activate_on, "selector_type" => $selector_type, "selector_data" => $selector_data), array("%s", "%d", "%s", "%s", "%s", "%s", "%s", "%d", "%s", "%s", "%s", "%s", "%d", "%s", "%s"));
		
		if($success) {
			echo '<script type="text/javascript">window.location = "admin.php?page=wp_custom_cursors";</script>';
	    } 
	    else {
			echo '<div class="container">
					<div class="row">
						<div class="col">
							<div class="alert alert-warning" role="alert">
							  '.esc_html__( 'An error happened!', 'wp-custom-cusors' ).'
							</div>
						</div>
					</div>
				 </div>';
		}
	}

	// Update the cursor
	if( isset( $_POST['update'] ) && check_admin_referer( 'wpcc_add_new_cursor', 'wpcc_add_new_nonce' ) ) {
		global $wpdb;
		$tablename = $wpdb->prefix . "custom_cursors";

		$cursor_type = sanitize_text_field( $_POST['cursor_type'] );
		$cursor_shape = sanitize_text_field( $_POST['cursor_shape'] );
		$cursor_image = sanitize_text_field( $_POST['cursor_image'] );
		$cursor_text = sanitize_text_field( $_POST['cursor_text'] );
		$click_point = sanitize_text_field( $_POST['click_point']  );
		$default_cursor = (isset($_POST['default_cursor']))? sanitize_text_field( $_POST['default_cursor'] ) : 0 ;
		$hover_cursors = sanitize_text_field( $_POST['hover_cursors'] );
		$activate_on = sanitize_text_field( $_POST['activate_on'] );
		$selector_type = sanitize_text_field( $_POST['selector_type'] );
		if ($_POST['selector_data'] == "") {
			$_POST['selector_data'] = "body";
		}
		$selector_data = sanitize_text_field( $_POST['selector_data'] );
		$color = sanitize_text_field( $_POST['color'] );
		if ($_POST['width'] == 0) {
			$_POST['width'] = 30; 
		}
		$width = sanitize_text_field( $_POST['width'] );
		$blending_mode = sanitize_text_field( $_POST['blending_mode'] );
		if (!isset($_POST['hide_tablet'])) {
			$_POST['hide_tablet'] = 'off';
		}
		$hide_tablet = sanitize_text_field( $_POST['hide_tablet'] );
		if (!isset($_POST['hide_mobile'])) {
			$_POST['hide_mobile'] = 'off';
		}
		$hide_mobile = sanitize_text_field( $_POST['hide_mobile'] );
		$row_id = sanitize_text_field( $_POST['update_id'] );

		$success = $wpdb->update($tablename, array("cursor_type" => $cursor_type, "cursor_shape" => $cursor_shape, "cursor_image" => $cursor_image, "cursor_text" => $cursor_text, "click_point" => $click_point, "default_cursor" => $default_cursor, "color" => $color, "width" => $width, "blending_mode" => $blending_mode, "hide_tablet" => $hide_tablet, "hide_mobile" => $hide_mobile, "hover_cursors" => $hover_cursors, "activate_on" => $activate_on, "selector_type" => $selector_type, "selector_data" => $selector_data), array("cursor_id" => $row_id), array("%s", "%d", "%s", "%s", "%s", "%s", "%s", "%d", "%s", "%s", "%s", "%s", "%d", "%s", "%s"));

		
		if($success) {
			echo '<script type="text/javascript">window.location = "admin.php?page=wp_custom_cursors";</script>';
	    } 
	    else {
			echo '<div class="container">
					<div class="row">
						<div class="col">
							<div class="alert alert-warning" role="alert">
							  '.esc_html__( 'An error happened!', 'wp-custom-cusors' ).'
							</div>
						</div>
					</div>
				 </div>';
		}
	}
	
?>




<?php
	
	// Form initialization
	$cursor_type_value = 'shape';
	$cursor_shape_value = 1;
	$cursor_image_value = null;
	$cursor_text_value = null;
	$click_point_value = "50,50";
	$default_cursor_value = 0;
	
	$color_value = "#000000";
	$width_value = 30;
	$blending_mode_value = 'normal';
	$hide_tablet_value = 'on';
	$hide_mobile_value = 'on';
	$activate_on_value = 0;
	$selector_type_value = null;
	$selector_data_value = null;
	$hover_cursors_value = null;

    $hover_trigger_link_value = 1;
	$hover_trigger_button_value = 1;
	$hover_trigger_custom_value = 0; 
	$hover_trigger_selector_value = "";
	$hover_cursor_value = 1;
	$hover_cursor_text = "View";
	$hover_cursor_icon = esc_url( plugins_url( '../img/icons/hover-cursor-icon.svg', __FILE__ ) );
	$hover_bg_color_value = "#ff3c38";
	$hover_cursor_width_value = 100;


    // Edit cursor
    if(isset($_GET['edit_row'])) {
    	global $wpdb;
		$tablename = $wpdb->prefix . "custom_cursors";
		$edit_row = $_GET['edit_row'];
		$cursor = $wpdb->get_row("SELECT * from $tablename WHERE cursor_id = $edit_row");

		$cursor_type_value = $cursor->cursor_type;
		$cursor_shape_value = $cursor->cursor_shape;
		$cursor_image_value = $cursor->cursor_image;
		$cursor_text_value = $cursor->cursor_text;
		$click_point_value = $cursor->click_point;
		$default_cursor_value = $cursor->default_cursor;
		$color_value = $cursor->color;
		$width_value = $cursor->width;
		$blending_mode_value = $cursor->blending_mode;
		$hide_tablet_value = $cursor->hide_tablet;
		$hide_mobile_value = $cursor->hide_mobile;
		$hover_cursors_value =$cursor->hover_cursors;
		$activate_on_value = $cursor->activate_on;
		$selector_type_value = $cursor->selector_type;
		$selector_data_value = $cursor->selector_data;

		if ($hover_cursors_value) {
			$hover_cursors_value = stripslashes($hover_cursors_value);
			$hover_cursors_value = json_decode($hover_cursors_value);
			
			$hover_cursor_item = '';
			foreach($hover_cursors_value as $hover_cursor_object) {
				$hover_cursor_value = $hover_cursor_object->cursor;
				$hover_bg_color_value = $hover_cursor_object->bgColor;
				$hover_cursor_width_value = $hover_cursor_object->width;
				$hover_cursor_item .= '<div class="hover-list-item title-normal"><img src="'.plugins_url( '', __FILE__ ).'/../img/cursors/hover-'.$hover_cursor_value.'.svg" class="img" /><div class="bg-color">Background Color: <div style="background-color: '.$hover_bg_color_value.';"></div></div><div class="width">Width: <div class="text-muted">'.$hover_cursor_width_value.' px</div></div><div class="activation">Activates on: <div class="text-muted">'.implode(',', $hover_cursor_object->selector).'</div></div></div>';
			}

			$hover_cursors_value = htmlspecialchars(json_encode($hover_cursors_value));
		}
    }

	?>

	<div class="container mt-3">
		<div class="row">
			<div class="col-md-8">

				<!-- Form -->
				<form action="#" method="post" id="add_new_form" class="bg-custom rounded-custom p-4">
					<!-- Step 1: Select Cursor -->
					<fieldset>
						<legend class="pb-2 mb-0">
							<div class="d-flex">
								<i class="ri-cursor-fill ri-lg"></i>
								<div class="ms-2 lead fw-normal"><?php echo esc_html__( 'Cursor Type', 'wp-custom-cusors' );?></div>
							</div>
							<div class="title-normal text-muted"><?php echo esc_html__('Choose cursor type:', 'wp-custom-cusors'); ?></div>
						</legend>
						<!-- Progress Bar -->
						<div class="progressbar">
							<div class="progress-complete"></div>
						</div>

						<div>
							<!-- Tabs -->
							<ul class="nav nav-pills mb-2" id="cursor_type_tabs" role="tablist">
								<li class="nav-item me-2" role="presentation">
								    <button class="nav-link <?php if($cursor_type_value == 'shape') {echo 'active';} ?>" id="shape-tab" data-bs-toggle="tab" data-bs-target="#shape" type="button" role="tab" aria-controls="shape" aria-selected="<?php if($cursor_type_value == 'shape') {echo 'true';} else {echo 'false';} ?>"><i class="ri-gradienter-line me-1"></i> <?php echo esc_html__('Shape Cursor', 'wp-custom-cusors'); ?></button>
								</li>
								<li class="nav-item me-2" role="presentation">
								    <button class="nav-link <?php if($cursor_type_value == 'image') {echo 'active';} ?>" id="image-tab" data-bs-toggle="tab" data-bs-target="#image" type="button" role="tab" aria-controls="image" aria-selected="<?php if($cursor_type_value == 'image') {echo 'true';} else {echo 'false';} ?>"><i class="ri-landscape-line me-1"></i> <?php echo esc_html__('Image Cursor', 'wp-custom-cusors'); ?></button>
								</li>
								<li class="nav-item" role="presentation">
								    <button class="nav-link <?php if($cursor_type_value == 'text') {echo 'active';} ?>" id="text-tab" data-bs-toggle="tab" data-bs-target="#text" type="button" role="tab" aria-controls="text" aria-selected="<?php if($cursor_type_value == 'text') {echo 'true';} else {echo 'false';} ?>"><i class="ri-font-size me-1"></i> <?php echo esc_html__('Text Cursor', 'wp-custom-cusors'); ?></button>
								</li>
							</ul>
							<div class="tab-content" id="cursor_type_tab_contents">
								<!-- Shape Cursor Tab Content -->
								<div class="tab-pane fade <?php if($cursor_type_value == 'shape') {echo 'show active';} ?>" id="shape" role="tabpanel" aria-labelledby="shape-tab">
									<div>
										<!-- Cursor 1 -->
										<input type="radio" class="btn-check" id="shape-1" autocomplete="off" name="cursor_shape" value="1" <?php checked( $cursor_shape_value, '1' ); ?>>
										<label for="shape-1"><img src="<?php echo esc_url( plugins_url( '../img/cursors/1.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Cursor 1', 'wp-custom-cusors'); ?>" class="shape-img" /></label>
										<!-- End Cursor 1 -->

										<!-- Cursor 2 -->
										<input type="radio" class="btn-check" id="shape-2" autocomplete="off" name="cursor_shape" value="2" <?php checked( $cursor_shape_value, '2' ); ?>>
										<label for="shape-2"><img src="<?php echo esc_url( plugins_url( '../img/cursors/2.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Cursor 2', 'wp-custom-cusors'); ?>" class="shape-img" /></label>
										<!-- End Cursor 2 -->

										<!-- Cursor 3 -->
										<input type="radio" class="btn-check" id="shape-3" autocomplete="off" name="cursor_shape" value="3" <?php checked( $cursor_shape_value, '3' ); ?>>
										<label for="shape-3"><img src="<?php echo esc_url( plugins_url( '../img/cursors/3.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Cursor 3', 'wp-custom-cusors'); ?>" class="shape-img" /></label>
										<!-- End Cursor 3 -->

										<!-- Cursor 4 -->
										<input type="radio" class="btn-check" id="shape-4" autocomplete="off" name="cursor_shape" value="4" <?php checked( $cursor_shape_value, '4' ); ?>>
										<label for="shape-4"><img src="<?php echo esc_url( plugins_url( '../img/cursors/4.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Cursor 4', 'wp-custom-cusors'); ?>" class="shape-img" /></label>
										<!-- End Cursor 4 -->

										<!-- Cursor 5 -->
										<input type="radio" class="btn-check" id="shape-5" autocomplete="off" name="cursor_shape" value="5" <?php checked( $cursor_shape_value, '5' ); ?>>
										<label for="shape-5"><img src="<?php echo esc_url( plugins_url( '../img/cursors/5.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Cursor 5', 'wp-custom-cusors'); ?>" class="shape-img" /></label>
										<!-- End Cursor 5 -->

										<!-- Cursor 6 -->
										<input type="radio" class="btn-check" id="shape-6" autocomplete="off" name="cursor_shape" value="6" <?php checked( $cursor_shape_value, '6' ); ?>>
										<label for="shape-6"><img src="<?php echo esc_url( plugins_url( '../img/cursors/6.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Cursor 6', 'wp-custom-cusors'); ?>" class="shape-img" /></label>
										<!-- End Cursor 6 -->

										<!-- Cursor 7 -->
										<input type="radio" class="btn-check" id="shape-7" autocomplete="off" name="cursor_shape" value="7" <?php checked( $cursor_shape_value, '7' ); ?>>
										<label for="shape-7"><img src="<?php echo esc_url( plugins_url( '../img/cursors/7.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Cursor 7', 'wp-custom-cusors'); ?>" class="shape-img" /></label>
										<!-- End Cursor 7 -->

										<!-- Cursor 8 -->
										<input type="radio" class="btn-check" id="shape-8" autocomplete="off" name="cursor_shape" value="8" <?php checked( $cursor_shape_value, '8' ); ?>>
										<label for="shape-8"><img src="<?php echo esc_url( plugins_url( '../img/cursors/8.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Cursor 8', 'wp-custom-cusors'); ?>" class="shape-img" /></label>
										<!-- End Cursor 8 -->
									</div>
								</div>

								<!-- Image Cursor Tab Content -->
								<div class="tab-pane fade <?php if($cursor_type_value == 'image') {echo 'show active';} ?>" id="image" role="tabpanel" aria-labelledby="image-tab">
									<div class="image-cursor-wrapper">
										<div class="cursor-upload <?php if($cursor_image_value) echo 'visually-hidden'; ?>" id="image_upload_btn">
											<?php echo esc_html__( 'Upload', 'wp-custom-cusors' ); ?>
										</div>

										<div class="new-image position-relative d-inline-block <?php if(!$cursor_image_value) echo 'visually-hidden'; ?>" id="uploaded_image_wrapper">

											<!-- Set The Click Point -->
								    		<div class="click-point visually-hidden" id="click_point"></div>
								    		
											<img src="<?php echo $cursor_image_value ?>" id="uploaded_image" class="shape-img" alt="<?php echo esc_html__('Custom Image Cursor', 'wp-custom-cusors'); ?>" />

											<!-- Delete Button -->
								    		<div id="wpcc_delete_image" class="wpcc_delete_image">
										    	<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
												    <g>
												        <path fill="none" d="M0 0h24v24H0z"/>
												        <path d="M12 10.586l4.95-4.95 1.414 1.414-4.95 4.95 4.95 4.95-1.414 1.414-4.95-4.95-4.95 4.95-1.414-1.414 4.95-4.95-4.95-4.95L7.05 5.636z" fill="white"/>
												    </g>
												</svg>
										    </div>

								    		<!-- Set The Click Point Button -->
								    		<div class="click-point-btn btn btn-success d-flex mt-1 justify-content-center btn-sm" id="click_point_btn"><?php echo esc_html__('Set Click Point', 'wp-custom-cusors'); ?></div>
								    		
										</div>

										<!-- Cursor Type Input -->
										<div class="visually-hidden">
											<input type="text" class="" name="cursor_type" value="<?php echo esc_html($cursor_type_value); ?>" id="cursor_type_input" autocomplete="off">
										</div>

										<!-- Image Cursor URL Input -->
										<input type="hidden" id="image_url_input" name="cursor_image" value="<?php echo esc_html($cursor_image_value); ?>">

										<!-- Click Point Inputs -->
										<input type="hidden" id="click_point_input" name="click_point" value="<?php echo esc_html($click_point_value); ?>">
									</div>
								</div>

								<!-- Text Cursor Tab Content -->
								<div class="tab-pane fade <?php if($cursor_type_value == 'text') {echo 'show active';} ?>" id="text" role="tabpanel" aria-labelledby="text-tab">
									<div class="row">
										<div class="col-md-6">
											<label for="cursor_text_input" class="form-label"><?php echo esc_html__('Text for the cursor', 'wp-custom-cusors'); ?></label>
											<input type="text" name="cursor_text" value="<?php echo esc_html($cursor_text_value); ?>" class="form-control" placeholder="<?php echo esc_html__('Enter Text', 'wp-custom-cusors'); ?>" aria-label="<?php echo esc_html__('Text Cursor', 'wp-custom-cusors'); ?>" id="cursor_text_input">
										</div>
									</div>
								</div>
							</div>
						</div>
					</fieldset>

					<!-- Step 2: Cursor Options -->
					<fieldset>
						<legend class="pb-2 mb-0">
							<div class="d-flex">
								<i class="ri-tools-line ri-lg"></i>
								<div class="ms-2 lead fw-normal"><?php echo esc_html__( 'Cursor Options', 'wp-custom-cusors' );?></div>
							</div>
							<div class="title-normal text-muted"><?php echo esc_html__('Set the options for the cursor:', 'wp-custom-cusors'); ?></div>
						</legend>
						<!-- Progress Bar -->
						<div class="progressbar">
							<div class="progress-complete"></div>
						</div>

						<div>
							<!-- Show Default Cursor -->
							<label class="toggler-wrapper mt-2 style-4"> 
								<span class="placeholder"><?php echo esc_html__( 'Show Default Cursor?', 'wp-custom-cusors' );?></span>
								<input type="checkbox" name="default_cursor" id="default_cursor" value="1" <?php checked( $default_cursor_value, '1' ); ?>>
								<div class="toggler-slider">
									<div class="toggler-knob"></div>
								</div>
							</label>

							<div class="row">
								<div class="col-md-6">
									<!-- Cursor Color -->
									<div class="title-normal mt-3">
										<?php echo esc_html__('Cursor Color:', 'wp-custom-cusors'); ?>
									</div>
									<div class="color_select form-group mt-2">
									    <label class="w-100">
									    	<input type='text' class="form-control basic wp-custom-cursor-color-picker" id="cursor_color" name="color" value="<?php echo esc_html($color_value); ?>">
									    </label>
									</div>
								</div>
								<div class="col-md-6">
									<!-- Cursor Size -->
									<label for="cursor_size_input" class="title-normal mt-3"><?php echo esc_html__( 'Cursor Size:', 'wp-custom-cusors' );?></label>

									<div class="d-flex align-items-center mt-2">
										<input type="range" class="form-range me-2" min="10" max="500" id="cursor_size_range" value="<?php echo esc_html($width_value); ?>">
										<input type="number" min="10" max="500" id="cursor_size_input" class="number-input" name='width' value="<?php echo esc_html($width_value); ?>">
										<span class="ms-2 small"><?php echo esc_html__( 'PX', 'wp-custom-cusors' );?></span>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-6">
									<!-- Blending Mode Select -->
									<div class="form-group blending-selector">
										<label for="blending_mode" class="title-normal mt-3"><?php echo esc_html__('Blending Mode:', 'wp-custom-cusors'); ?></label>
										<select class="form-control mt-2" id="blending_mode" name='blending_mode'>
											<option value="normal" <?php if (isset($blending_mode_value)) {selected( $blending_mode_value, 'normal' );} ?>><?php _e('Normal', 'wp-custom-cusors'); ?></option>
											<option value="multiply" <?php if (isset($blending_mode_value)) {selected( $blending_mode_value, 'multiply' );} ?>><?php _e('Multiply', 'wp-custom-cusors'); ?></option>
											<option value="screen" <?php if (isset($blending_mode_value)) {selected( $blending_mode_value, 'screen' );} ?>><?php _e('Screen', 'wp-custom-cusors'); ?></option>
											<option value="overlay" <?php if (isset($blending_mode_value)) {selected( $blending_mode_value, 'overlay' );} ?>><?php _e('Overlay', 'wp-custom-cusors'); ?></option>
											<option value="darken" <?php if (isset($blending_mode_value)) {selected( $blending_mode_value, 'darken' );} ?>><?php _e('Darken', 'wp-custom-cusors'); ?></option>
											<option value="lighten" <?php if (isset($blending_mode_value)) {selected( $blending_mode_value, 'lighten' );} ?>><?php _e('Lighten', 'wp-custom-cusors'); ?></option>
											<option value="color-dodge" <?php if (isset($blending_mode_value)) {selected( $blending_mode_value, 'color-dodge' );} ?>><?php _e('Color Dodge', 'wp-custom-cusors'); ?></option>
											<option value="color-burn" <?php if (isset($blending_mode_value)) {selected( $blending_mode_value, 'color-burn' );} ?>><?php _e('Color Burn', 'wp-custom-cusors'); ?></option>
											<option value="hard-light" <?php if (isset($blending_mode_value)) {selected( $blending_mode_value, 'hard-light' );} ?>><?php _e('Hard Light', 'wp-custom-cusors'); ?></option>
											<option value="soft-light" <?php if (isset($blending_mode_value)) {selected( $blending_mode_value, 'soft-light' );} ?>><?php _e('Soft Light', 'wp-custom-cusors'); ?></option>
											<option value="difference" <?php if (isset($blending_mode_value)) {selected( $blending_mode_value, 'difference' );} ?>><?php _e('Difference', 'wp-custom-cusors'); ?></option>
											<option value="exclusion" <?php if (isset($blending_mode_value)) {selected( $blending_mode_value, 'exclusion' );} ?>><?php _e('Exclusion', 'wp-custom-cusors'); ?></option>
											<option value="hue" <?php if (isset($blending_mode_value)) {selected( $blending_mode_value, 'hue' );} ?>><?php _e('Hue', 'wp-custom-cusors'); ?></option>
											<option value="saturation" <?php if (isset($blending_mode_value)) {selected( $blending_mode_value, 'saturation' );} ?>><?php _e('Saturation', 'wp-custom-cusors'); ?></option>
											<option value="color" <?php if (isset($blending_mode_value)) {selected( $blending_mode_value, 'color' );} ?>><?php _e('Color', 'wp-custom-cusors'); ?></option>
											<option value="luminosity" <?php if (isset($blending_mode_value)) {selected( $blending_mode_value, 'luminosity' );} ?>><?php _e('Luminosity', 'wp-custom-cusors'); ?></option>
										</select>
									</div>
								</div>
							</div>

							<!-- Hide Custom Cursor On Tablet -->
							<label class="toggler-wrapper mt-3 style-4"> 
								<span class="placeholder"><?php echo esc_html__( 'Hide Custom Cursor On Tablet', 'wp-custom-cusors' );?></span>
								<input type="checkbox" id="hide_tablet" name="hide_tablet" value="on" <?php checked( $hide_tablet_value, 'on' ); ?>>
								<div class="toggler-slider">
									<div class="toggler-knob"></div>
								</div>
							</label>

							<!-- Hide Custom Cursor On Mobile -->
							<label class="toggler-wrapper mt-3 style-4"> 
								<span class="placeholder"><?php echo esc_html__( 'Hide Custom Cursor On Mobile', 'wp-custom-cusors' );?></span>
								<input type="checkbox" id="hide_mobile" name="hide_mobile" value="on" <?php checked( $hide_mobile_value, 'on' ); ?>>
								<div class="toggler-slider">
									<div class="toggler-knob"></div>
								</div>
							</label>
						</div>
					</fieldset>

					<!-- Step 3: Hover Options -->
					<fieldset>
						<legend class="pb-2 mb-0">
							<div class="d-flex">
								<i class="ri-drag-drop-fill ri-lg"></i>
								<div class="ms-2 lead fw-normal"><?php echo esc_html__( 'Hover Options', 'wp-custom-cusors' );?></div>
							</div>
							<div class="title-normal text-muted"><?php echo esc_html__('Add hover cursors:', 'wp-custom-cusors'); ?></div>
						</legend>
						<!-- Progress Bar -->
						<div class="progressbar">
							<div class="progress-complete"></div>
						</div>

						<div>
							<!-- List of Hover Cursors -->
							<div class="hover-cursors-list-wrapper">
								<?php if( isset($hover_cursor_item) ) { echo esc_html($hover_cursor_item); } ?>
							</div>

							<!-- Add Hover Cursor Button -->
							<button type="button" class="btn btn-dark btn-sm disabled" id="create_hover_btn_disabled"><?php echo esc_html__('Create Hover Cursor', 'wp-custom-cusors'); ?><span class="pro"><?php echo esc_html__('PRO', 'wp-custom-cusors'); ?></span></button>

							<!-- Add Hover Cursor Form -->
							<div id="hover_cursor_wrapper" class="hover-form-wrapper rounded-custom mt-3 p-3" style="display: none;">

								<div class="title-normal">
									<?php echo esc_html__( 'Enable On:', 'wp-custom-cusors' );?>
								</div>
								<div class="row">
									<div class="col-md-6">
										<!-- Links -->
										<label class="toggler-wrapper mt-2 style-4"> 
											<span class="placeholder"><?php echo esc_html__( 'Links', 'wp-custom-cusors' );?></span>
											<input type="checkbox" id="hover_trigger_link" value="1" <?php checked( $hover_trigger_link_value, '1' ); ?>>
											<div class="toggler-slider">
												<div class="toggler-knob"></div>
											</div>
										</label>

										<!-- Buttons -->
										<label class="toggler-wrapper mt-3 style-4"> 
											<span class="placeholder"><?php echo esc_html__( 'Buttons', 'wp-custom-cusors' );?></span>
											<input type="checkbox" id="hover_trigger_button" value="1" <?php checked( $hover_trigger_button_value, '1' ); ?>>
											<div class="toggler-slider">
												<div class="toggler-knob"></div>
											</div>
										</label>
									</div>
									<div class="col-md-6">
										<!-- Custom Element -->
										<label class="toggler-wrapper style-4"> 
											<span class="placeholder"><?php echo esc_html__( 'Custom Element', 'wp-custom-cusors' );?></span>
											<input type="checkbox" id="hover_trigger_custom" value="1" <?php checked( $hover_trigger_custom_value, '1' ); ?>>
											<div class="toggler-slider">
												<div class="toggler-knob"></div>
											</div>
										</label>

										<!-- Custom Hover Selector -->
										<div class="input-group mt-2" id="hover_trigger_custom_wrapper">
											<span class="input-group-text" id="custom_hover_selector"><?php echo esc_html__('Selector', 'wp-custom-cusors'); ?></span>
											<input type="text" value="<?php echo esc_html($hover_trigger_selector_value); ?>" class="form-control" placeholder="<?php echo esc_html__('e.g. .btn', 'wp-custom-cusors'); ?>" aria-label="<?php echo esc_html__('Selector', 'wp-custom-cusors'); ?>" aria-describedby="custom_hover_selector" id="hover_trigger_selector">
										</div>
									</div>
								</div>

								<div class="title-normal mt-3 mb-2">
									<?php echo esc_html__( 'Select Hover Type:', 'wp-custom-cusors' );?>
								</div>

								<!-- Cursor 1 -->
								<input type="radio" name="hover_cursor_type" class="btn-check hover-cursor-radio" id="hover_cursor_1" autocomplete="off" value="1" <?php checked( $hover_cursor_value, '1' ); ?>>
								<label for="hover_cursor_1"><img src="<?php echo esc_url( plugins_url( '../img/cursors/hover-1.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Hover Cursor 1', 'wp-custom-cusors'); ?>" class="shape-img" /></label>

								<!-- Cursor 2 -->
								<input type="radio" name="hover_cursor_type" class="btn-check hover-cursor-radio" id="hover_cursor_2" autocomplete="off" value="2" <?php checked( $hover_cursor_value, '2' ); ?>>
								<label for="hover_cursor_2"><img src="<?php echo esc_url( plugins_url( '../img/cursors/hover-2.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Hover Cursor 2', 'wp-custom-cusors'); ?>" class="shape-img" /></label>

								<!-- Cursor 3 -->
								<input type="radio" name="hover_cursor_type" class="btn-check hover-cursor-radio" id="hover_cursor_3" autocomplete="off" value="3" <?php checked( $hover_cursor_value, '3' ); ?>>
								<label for="hover_cursor_3"><img src="<?php echo esc_url( plugins_url( '../img/cursors/hover-3.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Hover Cursor 3', 'wp-custom-cusors'); ?>" class="shape-img" /></label>

								<!-- Cursor 4 -->
								<input type="radio" name="hover_cursor_type" class="btn-check hover-cursor-radio" id="hover_cursor_4" autocomplete="off" value="4" <?php checked( $hover_cursor_value, '4' ); ?>>
								<label for="hover_cursor_4"><img src="<?php echo esc_url( plugins_url( '../img/cursors/hover-4.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Hover Cursor 4', 'wp-custom-cusors'); ?>" class="shape-img" /></label>

								<!-- Cursor 5 -->
								<input type="radio" name="hover_cursor_type" class="btn-check hover-cursor-radio" id="hover_cursor_5" autocomplete="off" value="5" <?php checked( $hover_cursor_value, '5' ); ?>>
								<label for="hover_cursor_5"><img src="<?php echo esc_url( plugins_url( '../img/cursors/hover-5.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Hover Cursor 5', 'wp-custom-cusors'); ?>" class="shape-img" /></label>

								<!-- Cursor 6 -->
								<input type="radio" name="hover_cursor_type" class="btn-check hover-cursor-radio" id="hover_cursor_6" autocomplete="off" value="6" <?php checked( $hover_cursor_value, '6' ); ?>>
								<label for="hover_cursor_6"><img src="<?php echo esc_url( plugins_url( '../img/cursors/hover-6.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Hover Cursor 6', 'wp-custom-cusors'); ?>" class="shape-img" /></label>

								<!-- Cursor 7 -->
								<input type="radio" name="hover_cursor_type" class="btn-check hover-cursor-radio" id="hover_cursor_7" autocomplete="off" value="7" <?php checked( $hover_cursor_value, '7' ); ?>>
								<label for="hover_cursor_7"><img src="<?php echo esc_url( plugins_url( '../img/cursors/hover-7.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Hover Cursor 7', 'wp-custom-cusors'); ?>" class="shape-img" /></label>

								<!-- Cursor 8 -->
								<input type="radio" name="hover_cursor_type" class="btn-check hover-cursor-radio" id="hover_cursor_8" autocomplete="off" value="8" <?php checked( $hover_cursor_value, '8' ); ?>>
								<label for="hover_cursor_8"><img src="<?php echo esc_url( plugins_url( '../img/cursors/hover-8.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Hover Cursor 8', 'wp-custom-cusors'); ?>" class="shape-img" /></label>

								<!-- Cursor 9 -->
								<input type="radio" name="hover_cursor_type" class="btn-check hover-cursor-radio" id="hover_cursor_9" autocomplete="off" value="9" <?php checked( $hover_cursor_value, '9' ); ?>>
								<label for="hover_cursor_9"><img src="<?php echo esc_url( plugins_url( '../img/cursors/hover-9.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Hover Cursor 9', 'wp-custom-cusors'); ?>" class="shape-img" /></label>

								<!-- Cursor 10 -->
								<input type="radio" name="hover_cursor_type" class="btn-check hover-cursor-radio" id="hover_cursor_10" autocomplete="off" value="10" <?php checked( $hover_cursor_value, '10' ); ?>>
								<label for="hover_cursor_10"><img src="<?php echo esc_url( plugins_url( '../img/cursors/hover-10.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Hover Cursor 10', 'wp-custom-cusors'); ?>" class="shape-img" /></label>

								<!-- Cursor 11 -->
								<input type="radio" name="hover_cursor_type" class="btn-check hover-cursor-radio" id="hover_cursor_11" autocomplete="off" value="11" <?php checked( $hover_cursor_value, '11' ); ?>>
								<label for="hover_cursor_11"><img src="<?php echo esc_url( plugins_url( '../img/cursors/hover-11.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Hover Cursor 11', 'wp-custom-cusors'); ?>" class="shape-img" /></label>

								<!-- Cursor 12 -->
								<input type="radio" name="hover_cursor_type" class="btn-check hover-cursor-radio" id="hover_cursor_12" autocomplete="off" value="12" <?php checked( $hover_cursor_value, '12' ); ?>>
								<label for="hover_cursor_12"><img src="<?php echo esc_url( plugins_url( '../img/cursors/hover-12.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Hover Cursor 12', 'wp-custom-cusors'); ?>" class="shape-img" /></label>

								<!-- Cursor 13 -->
								<input type="radio" name="hover_cursor_type" class="btn-check hover-cursor-radio" id="hover_cursor_13" autocomplete="off" value="13" <?php checked( $hover_cursor_value, '13' ); ?>>
								<label for="hover_cursor_13"><img src="<?php echo esc_url( plugins_url( '../img/cursors/hover-13.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Hover Cursor 13', 'wp-custom-cusors'); ?>" class="shape-img" /></label>

								<!-- Cursor 14 -->
								<input type="radio" name="hover_cursor_type" class="btn-check hover-cursor-radio" id="hover_cursor_14" autocomplete="off" value="14" <?php checked( $hover_cursor_value, '14' ); ?>>
								<label for="hover_cursor_14"><img src="<?php echo esc_url( plugins_url( '../img/cursors/hover-14.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Hover Cursor 14', 'wp-custom-cusors'); ?>" class="shape-img" /></label>

								<!-- Cursor 15 -->
								<input type="radio" name="hover_cursor_type" class="btn-check hover-cursor-radio" id="hover_cursor_15" autocomplete="off" value="15" <?php checked( $hover_cursor_value, '15' ); ?>>
								<label for="hover_cursor_15"><img src="<?php echo esc_url( plugins_url( '../img/cursors/hover-15.svg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Hover Cursor 15', 'wp-custom-cusors'); ?>" class="shape-img" /></label>

								<div class="row align-items-end" id="hover_text_icon_wrapper" style="display: none;">
									<div class="col-md-6">
										<!-- Hover Cursor Text -->
										<div class="mt-3">
											<label for="hover_cursor_text" class="form-label"><?php echo esc_html__('Hover Cursor Text', 'wp-custom-cusors'); ?></label>
											<input type="text" class="form-control" value="<?php echo esc_html($hover_cursor_text); ?>" id="hover_cursor_text" placeholder="<?php echo esc_html__('View', 'wp-custom-cusors'); ?>" aria-label="<?php echo esc_html__('View', 'wp-custom-cusors'); ?>">
										</div>
									</div>
									<div class="col-md-6">
										<!-- Hover Cursor Icon -->
										<div class="icon-wrapper" id="hover_cursor_icon_wrapper">
											<div class="label">
												<?php echo esc_html__('Icon', 'wp-custom-cusors'); ?>
											</div>
											<div class="file">
												<img src="<?php echo esc_html($hover_cursor_icon); ?>" id="hover_cursor_icon" alt="<?php echo esc_html__('Hover Cursor Icon', 'wp-custom-cusors'); ?>" />
											</div>
										</div>
										<input type="hidden" id="hover_cursor_icon_url" value="<?php echo esc_html($hover_cursor_icon); ?>">
									</div>
								</div>

								<div class="row">
									<div class="col-md-6">
										<!-- Color -->
										<div class="title-normal mt-3">
											<?php echo esc_html__('Background Color:', 'wp-custom-cusors'); ?>
										</div>
										<div class="color_select form-group mt-2">
										    <label class="w-100">
										    	<input type='text' class="form-control basic wp-custom-cursor-color-picker" id="hover_background_color" value="<?php echo esc_html($hover_bg_color_value); ?>">
										    </label>
										</div>
									</div>
									<div class="col-md-6">
										<!-- Width -->
										<label for="hover_cursor_width_range" class="title-normal mt-3"><?php echo esc_html__( 'Width:', 'wp-custom-cusors' );?></label>

										<div class="d-flex align-items-center mt-2">
											<input type="range" class="form-range me-2" min="10" max="500" id="hover_cursor_width_input" value="<?php echo esc_html($hover_cursor_width_value); ?>">
											<input type="number" min="10" max="500" id="hover_cursor_width_range" class="number-input" value="<?php echo esc_html($hover_cursor_width_value); ?>">
											<span class="ms-2 small"><?php echo esc_html__( 'PX', 'wp-custom-cusors' );?></span>
										</div>
									</div>
								</div>

								<!-- Input to store all hover cursors -->
								<input type="hidden" id="hover_cursors" name="hover_cursors" value="<?php echo esc_html($hover_cursors_value); ?>">

								<!-- Save Cursor Button -->
								<button type="button" class="btn btn-primary mt-3" id="save_hover_btn"><?php echo esc_html__('Save Hover Cursor', 'wp-custom-cusors'); ?></button>

								<!-- Error Message -->
								<div class="mt-3 alert alert-danger d-none align-items-center fade small" role="alert" id="alert_container">
								  	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img" aria-label="Warning:">
								    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
								  	</svg>
								  	<div id="alert_message"></div>
								</div>
							</div>


						</div>
					</fieldset>

					<!-- Step 4: Activation -->
					<fieldset>
						<legend class="pb-2 mb-0">
							<div class="d-flex">
								<i class="ri-check-double-fill ri-lg"></i>
								<div class="ms-2 lead fw-normal"><?php echo esc_html__( 'Activation', 'wp-custom-cusors' );?></div>
							</div>
							<div class="title-normal text-muted"><?php echo esc_html__('Add custom cursor to your page:', 'wp-custom-cusors'); ?></div>
						</legend>
						<!-- Progress Bar -->
						<div class="progressbar">
							<div class="progress-complete"></div>
						</div>

						<div>
							
							
							<div class="btn-group" role="group" aria-label="<?php echo esc_html__('Activate On', 'wp-custom-cusors'); ?>">
								<div id="activate_on_page">
									<input type="radio" class="btn-check" name="activate_on" value="0" id="activate_on_body" autocomplete="off" <?php checked( $activate_on_value, '0' ); ?>>
									<label class="btn btn-outline-dark btn-sm" for="activate_on_body"><i class="ri-window-fill"></i> <?php echo esc_html__('Body', 'wp-custom-cusors'); ?></label>
								</div>

								<div id="activate_on_section">
									<input type="radio" class="btn-check" name="activate_on" value="1" id="activate_on_element" autocomplete="off" <?php checked( $activate_on_value, '1' ); ?>>
									<label class="btn btn-outline-dark btn-sm" for="activate_on_element"><i class="ri-picture-in-picture-line"></i> <?php echo esc_html__('Elements', 'wp-custom-cusors'); ?></label>
								</div>
							</div>
							<!-- End Activate On -->

							<!-- Element Selector Group -->
							<div id="select_element_group" style="display: none;">
								<div class="input-group selector-group mt-3">
									<select class="form-select" name="selector_type" id="selector_type" aria-label="<?php echo esc_html__('Selector Type', 'wp-custom-cusors'); ?>">
										<option value="tag" <?php if ($selector_type_value) {selected( $selector_type_value, 'tag' );}?>><?php echo esc_html__( 'Tag', 'wp-custom-cusors' ); ?></option>
									    <option value="class" <?php if ($selector_type_value) {selected( $selector_type_value, 'class' );}?>><?php echo esc_html__( 'Class', 'wp-custom-cusors' ); ?></option>
									    <option value="id" <?php if ($selector_type_value) {selected( $selector_type_value, 'id' );}?>><?php echo esc_html__( 'ID', 'wp-custom-cusors' ); ?></option>
									    <option value="attribute" <?php if ($selector_type_value) {selected( $selector_type_value, 'attribute' );}?>><?php echo esc_html__( 'Attribute', 'wp-custom-cusors' ); ?></option>
									</select>
									 <input type='text' placeholder="<?php echo esc_html__('Selector', 'wp-custom-cusors'); ?>" class="form-control rounded-right " name='selector_data' id="selector_data" value="<?php echo $selector_data_value; ?>" aria-label="<?php echo esc_html__('Selector Query', 'wp-custom-cusors'); ?>" aria-describedby="selector_type">
								</div>

								<small class="text-muted fw-light"><?php echo esc_html__('All elements selected with above criteria would have the custom cursor.', 'wp-custom-cusors'); ?></small>
								<!-- End Element Selector Group -->
							</div>

							<!-- Submit Button -->
							<div>
							<?php  
								if(isset($_GET['edit_row'])) {
									?>
									<input type="hidden" name="update_id" value="<?php echo intval( $_GET['edit_row'] ); ?>">
									<input type="submit" name="update" class="btn btn-primary mt-4" value="<?php echo esc_html__( 'Update Cursor', 'wp-custom-cusors' ) ?>" />
									<?php
								}

								else {
									?>
									<input type="submit" name="submit" class="btn btn-primary mt-4" value="<?php echo esc_html__( 'Save Cursor', 'wp-custom-cusors' ) ?>" />
									<?php
								}
							?>
							</div>
						</div>
					</fieldset>
					<?php wp_nonce_field( 'wpcc_add_new_cursor', 'wpcc_add_new_nonce' ); ?>
				</form>
			</div>
			<div class="col-md-4">
				<!-- Preview -->
				<div class="position-sticky top">
					<div id="wt-preview" class="<?php if($default_cursor_value == 0) { echo 'no-cursor'; } ?>">
						<div class="preview-inner bg-custom p-4 rounded-custom">
							<div class="font-weight-bold mb-3"><?php _e('Preview:', 'wp-custom-cusors'); ?></div>
							<div class="d-flex align-items-center">
								<button class="btn btn-danger"><?php _e('Button', 'wp-custom-cusors'); ?></button>
								<input type="text" class="form-control mx-2" placeholder="<?php _e('Input', 'wp-custom-cusors'); ?>">
								<a href="javascript:void(0);"><?php _e('Link', 'wp-custom-cusors'); ?></a>
								
							</div>
					    	
					    	<div class="position-relative">
					    		<img src="<?php echo esc_url( plugins_url( '../img/preview-image.jpg', __FILE__ ) ); ?>" alt="<?php echo esc_html__('Test blending mode option on image', 'wp-custom-cusors');?>" class="img-fluid mt-2" />
					    		<small class="credit"><?php _e('Photo Credit: Unsplash', 'wp-custom-cusors'); ?></small>
					    	</div>
				    	</div>
				    </div>
				</div>
				<!-- End Preview -->
			</div>
		</div>
	</div>