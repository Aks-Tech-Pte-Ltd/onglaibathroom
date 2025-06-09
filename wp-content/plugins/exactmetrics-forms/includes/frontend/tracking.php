<?php
add_filter( 'exactmetrics_tracking_after_gtag', 'exactmetrics_forms_print_dual_tracking_js', 11, 1 );

function exactmetrics_forms_print_dual_tracking_js() {
	if ( ! function_exists( 'exactmetrics_get_v4_id_to_output' ) ||
		 ! exactmetrics_get_v4_id_to_output()
	) {
		return;
	}

	$attr_string = function_exists( 'exactmetrics_get_frontend_analytics_script_atts' )
		? exactmetrics_get_frontend_analytics_script_atts()
		: ' type="text/javascript" data-cfasync="false"';
	?>
	<script<?php echo $attr_string; // phpcs:ignore ?>>
		ExactMetricsDualTracker.trackers['form:impression'] = function (parameters) {
			__gtagDataLayer('event', 'form_impression', {
				form_id: parameters.event_label,
				content_type: 'form',
				non_interaction: true,
				send_to: parameters.send_to,
			});
		};

		ExactMetricsDualTracker.trackers['form:conversion'] = function (parameters) {
			__gtagDataLayer('event', 'generate_lead', {
				form_id: parameters.event_label,
				send_to: parameters.send_to,
			});
		};
	</script>
	<?php
}

function validate_exactmetrics_forms_custom_conversion( $value ) {
	$value = (string) $value;
	return $value === "true" || $value === "false" ? $value : "false";
}

function exactmetrics_forms_output_after_script( $options ) {
	if ( function_exists( 'exactmetrics_skip_tracking' ) && exactmetrics_skip_tracking() ) {
		return;
	}

	$track_user = exactmetrics_track_user();
	$v4         = function_exists( 'exactmetrics_get_v4_id_to_output' ) && exactmetrics_get_v4_id_to_output();

	if ( $track_user && $v4 ) {
		$attr_string = function_exists( 'exactmetrics_get_frontend_analytics_script_atts' )
			? exactmetrics_get_frontend_analytics_script_atts()
			: ' type="text/javascript" data-cfasync="false"';

		ob_start();
		echo PHP_EOL;
		?>
		<!-- ExactMetrics Form Tracking -->
		<script<?php echo $attr_string; // phpcs:ignore ?>>
			function exactmetrics_forms_record_impression(event) {
				exactmetrics_add_bloom_forms_ids();
				var exactmetrics_forms = document.getElementsByTagName("form");
				var exactmetrics_forms_i;
				for (exactmetrics_forms_i = 0; exactmetrics_forms_i < exactmetrics_forms.length; exactmetrics_forms_i++) {
					var exactmetrics_form_id = exactmetrics_forms[exactmetrics_forms_i].getAttribute("id");
					var skip_conversion = false;
					/* Check to see if it's contact form 7 if the id isn't set */
					if (!exactmetrics_form_id) {
						exactmetrics_form_id = exactmetrics_forms[exactmetrics_forms_i].parentElement.getAttribute("id");
						if (exactmetrics_form_id && exactmetrics_form_id.lastIndexOf('wpcf7-f', 0) === 0) {
							/* If so, let's grab that and set it to be the form's ID*/
							var tokens = exactmetrics_form_id.split('-').slice(0, 2);
							var result = tokens.join('-');
							exactmetrics_forms[exactmetrics_forms_i].setAttribute("id", result);/* Now we can do just what we did above */
							exactmetrics_form_id = exactmetrics_forms[exactmetrics_forms_i].getAttribute("id");
						} else {
							exactmetrics_form_id = false;
						}
					}

					/* Check if it's Ninja Forms & id isn't set. */
					if (!exactmetrics_form_id && exactmetrics_forms[exactmetrics_forms_i].parentElement.className.indexOf('nf-form-layout') >= 0) {
						exactmetrics_form_id = exactmetrics_forms[exactmetrics_forms_i].parentElement.parentElement.parentElement.getAttribute('id');
						if (exactmetrics_form_id && 0 === exactmetrics_form_id.lastIndexOf('nf-form-', 0)) {
							/* If so, let's grab that and set it to be the form's ID*/
							tokens = exactmetrics_form_id.split('-').slice(0, 3);
							result = tokens.join('-');
							exactmetrics_forms[exactmetrics_forms_i].setAttribute('id', result);
							/* Now we can do just what we did above */
							exactmetrics_form_id = exactmetrics_forms[exactmetrics_forms_i].getAttribute('id');
							skip_conversion = true;
						}
					}

					if (exactmetrics_form_id && exactmetrics_form_id !== 'commentform' && exactmetrics_form_id !== 'adminbar-search') {
						__gtagTracker('event', 'impression', {
							event_category: 'form',
							event_label: exactmetrics_form_id,
							value: 1,
							non_interaction: true
						});

						/* If a WPForms Form, we can use custom tracking */
						if (exactmetrics_form_id && 0 === exactmetrics_form_id.lastIndexOf('wpforms-form-', 0)) {
							continue;
						}

						/* Formiddable Forms, use custom tracking */
						if (exactmetrics_forms_has_class(exactmetrics_forms[exactmetrics_forms_i], 'frm-show-form')) {
							continue;
						}

						/* If a Gravity Form, we can use custom tracking */
						if (exactmetrics_form_id && 0 === exactmetrics_form_id.lastIndexOf('gform_', 0)) {
							continue;
						}

						/* If Ninja forms, we use custom conversion tracking */
						if (skip_conversion) {
							continue;
						}

						var custom_conversion_mi_forms = <?php echo validate_exactmetrics_forms_custom_conversion(apply_filters( "exactmetrics_forms_custom_conversion", "false" )); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
						if (custom_conversion_mi_forms) {
							continue;
						}

						if (window.jQuery) {
							(function (form_id) {
								jQuery(document).ready(function () {
									jQuery('#' + form_id).on('submit', exactmetrics_forms_record_conversion);
								});
							})(exactmetrics_form_id);
						} else {
							var __gaFormsTrackerWindow = window;
							if (__gaFormsTrackerWindow.addEventListener) {
								document.getElementById(exactmetrics_form_id).addEventListener("submit", exactmetrics_forms_record_conversion, false);
							} else {
								if (__gaFormsTrackerWindow.attachEvent) {
									document.getElementById(exactmetrics_form_id).attachEvent("onsubmit", exactmetrics_forms_record_conversion);
								}
							}
						}

					} else {
						continue;
					}
				}
			}

			function exactmetrics_forms_has_class(element, className) {
				return (' ' + element.className + ' ').indexOf(' ' + className + ' ') > -1;
			}

			function exactmetrics_forms_record_conversion(event) {
				var exactmetrics_form_conversion_id = event.target.id;
				var exactmetrics_form_action = event.target.getAttribute("miforms-action");
				if (exactmetrics_form_conversion_id && !exactmetrics_form_action) {
					document.getElementById(exactmetrics_form_conversion_id).setAttribute("miforms-action", "submitted");
					__gtagTracker('event', 'conversion', {
						event_category: 'form',
						event_label: exactmetrics_form_conversion_id,
						value: 1,
					});
				}
			}

			/* Attach the events to all clicks in the document after page and GA has loaded */
			function exactmetrics_forms_load() {
				if (typeof (__gtagTracker) !== 'undefined' && __gtagTracker) {
					var __gtagFormsTrackerWindow = window;
					if (__gtagFormsTrackerWindow.addEventListener) {
						__gtagFormsTrackerWindow.addEventListener("load", exactmetrics_forms_record_impression, false);
					} else {
						if (__gtagFormsTrackerWindow.attachEvent) {
							__gtagFormsTrackerWindow.attachEvent("onload", exactmetrics_forms_record_impression);
						}
					}
				} else {
					setTimeout(exactmetrics_forms_load, 200);
				}
			}

			/* Custom Ninja Forms impression tracking */
			if (window.jQuery) {
				jQuery(document).on('nfFormReady', function (e, layoutView) {
					var label = layoutView.el;
					label = label.substring(1, label.length);
					label = label.split('-').slice(0, 3).join('-');
					__gtagTracker('event', 'impression', {
						event_category: 'form',
						event_label: label,
						value: 1,
						non_interaction: true
					});
				});
			}

			/* Custom Bloom Form tracker */
			function exactmetrics_add_bloom_forms_ids() {
				var bloom_forms = document.querySelectorAll('.et_bloom_form_content form');
				if (bloom_forms.length > 0) {
					for (var i = 0; i < bloom_forms.length; i++) {
						if ('' === bloom_forms[i].id) {
							var form_parent_root = exactmetrics_find_parent_with_class(bloom_forms[i], 'et_bloom_optin');
							if (form_parent_root) {
								var classes = form_parent_root.className.split(' ');
								for (var j = 0; j < classes.length; ++j) {
									if (0 === classes[j].indexOf('et_bloom_optin')) {
										bloom_forms[i].id = classes[j];
									}
								}
							}
						}
					}
				}
			}

			function exactmetrics_find_parent_with_class(element, className) {
				if (element.parentNode && '' !== className) {
					if (element.parentNode.className.indexOf(className) >= 0) {
						return element.parentNode;
					} else {
						return exactmetrics_find_parent_with_class(element.parentNode, className);
					}
				}
				return false;
			}

			exactmetrics_forms_load();
		</script>
		<!-- End ExactMetrics Form Tracking -->
		<?php
		echo PHP_EOL;
		echo ob_get_clean(); // phpcs:ignore
	}

}

add_action( 'wp_head', 'exactmetrics_forms_output_after_script', 15 );

function exactmetrics_form_custom_dual_track_conversion( $form_id, $extra_params = array() ) {
	$user_id = false;
	if ( exactmetrics_get_option( 'userid', false ) && is_user_logged_in() ) {
		$user_id = get_current_user_id();
	}

	if ( function_exists( 'exactmetrics_get_v4_id_to_output' ) &&
		 function_exists( 'exactmetrics_mp_collect_v4' ) &&
		 exactmetrics_get_v4_id_to_output()
	) {
		$params = array(
			'form_id' => $form_id,
		);

		if ( isset( $extra_params['page_location'] ) ) {
			$params['page_location'] = $extra_params['page_location'];
		}

		if ( isset( $extra_params['page_path'] ) ) {
			$params['page_path'] = $extra_params['page_path'];
		}

		$args = array(
			'events' => array(
				array(
					'name'   => 'generate_lead',
					'params' => $params,
				)
			),
		);

		if ( $user_id ) {
			$args['user_id'] = $user_id;
		}

		exactmetrics_mp_collect_v4( $args );
	}
}

// Custom tracking for WPForms
function exactmetrics_forms_custom_wpforms_save( $fields, $entry, $form_id, $form_data ) {
	// Skip tracking if not a trackable user.
	if ( ! function_exists( 'exactmetrics_debug_output' ) ) {
		$do_not_track = ! exactmetrics_track_user( get_current_user_id() );
		if ( $do_not_track ) {
			return;
		}
	}

	if ( isset( $_POST['page_url'] ) ) {
		$page_url = sanitize_text_field( wp_unslash( $_POST['page_url'] ) );

		$extra_params = array(
			'page_location' => $page_url,
			'page_path'     => parse_url( $page_url, PHP_URL_PATH ),
		);
	}

	exactmetrics_form_custom_dual_track_conversion( 'wpforms-form-' . $form_id, $extra_params );
}

add_action( 'wpforms_process_entry_save', 'exactmetrics_forms_custom_wpforms_save', 10, 4 );

// Custom tracking for Ninja Forms
function exactmetrics_forms_custom_ninja_forms_save( $data ) {
	// Skip tracking if not a trackable user.
	if ( ! function_exists( 'exactmetrics_debug_output' ) ) {
		$do_not_track = ! exactmetrics_track_user( get_current_user_id() );
		if ( $do_not_track ) {
			return;
		}
	}

	exactmetrics_form_custom_dual_track_conversion( 'nf-form-' . $data['form_id'] );
}

add_action( 'ninja_forms_after_submission', 'exactmetrics_forms_custom_ninja_forms_save' );

function exactmetrics_forms_custom_gravity_forms_save( $entry, $form ) {
	// Skip tracking if not a trackable user.
	if ( ! function_exists( 'exactmetrics_debug_output' ) ) {
		$do_not_track = ! exactmetrics_track_user( get_current_user_id() );
		if ( $do_not_track ) {
			return;
		}
	}

	exactmetrics_form_custom_dual_track_conversion( 'gform_' . $form['id'] );
}

add_action( 'gform_after_submission', 'exactmetrics_forms_custom_gravity_forms_save', 10, 2 );

function exactmetrics_forms_custom_formidable_forms_save( $entry_id, $form_id ) {
	// Skip tracking if not a trackable user.
	if ( ! function_exists( 'exactmetrics_debug_output' ) ) {
		$do_not_track = ! exactmetrics_track_user( get_current_user_id() );
		if ( $do_not_track ) {
			return;
		}
	}
	$form = FrmForm::getOne( $form_id );

	exactmetrics_form_custom_dual_track_conversion( 'form_' . $form->form_key );
}

add_action( 'frm_after_create_entry', 'exactmetrics_forms_custom_formidable_forms_save', 30, 2 );

/**
 * Add a default id to the Elementor forms for tracking.
 *
 * @param array $instance The current form instance.
 * @param \ElementorPro\Modules\Forms\Widgets\Form $form The form object.
 */
function exactmetrics_add_elementor_form_id( $instance, $form ) {
	// If the form has an ID set exit so it is used.
	if ( ! empty( $instance['form_id'] ) ) {
		return;
	}

	if ( method_exists( $form, 'add_render_attribute' ) && method_exists( $form, 'get_id' ) ) {

		$form_id = 'elementor_post_' . get_the_ID() . '_form_';
		if ( ! empty( $instance['form_name'] ) ) {
			$form_id .= sanitize_title( $instance['form_name'] );
		} else {
			$form_id .= $form->get_id();
		}

		$form->add_render_attribute( 'form', 'id', $form_id );
	}
}

add_action( 'elementor-pro/forms/pre_render', 'exactmetrics_add_elementor_form_id', 15, 2 );

/**
 * Add a unique id to the Enfold contact form element.
 *
 * @param array $form_args
 * @param int $post_id
 *
 * @return array
 */
function exactmetrics_enfold_add_unique_id( $form_args, $post_id ) {

	// If the form has a title, use that to make the id unique.
	$form_id = ! empty( $form_args['heading'] ) ? sanitize_title( wp_strip_all_tags( $form_args['heading'] ) ) : '';

	// If no heading is set, attempt to use the avia form id.
	if ( empty( $form_id ) && class_exists( 'avia_form' ) ) {
		$form_id = avia_form::$form_id;
	}

	$form_args['action'] .= '" id="avia_contact_' . $post_id . '_' . $form_id;

	return $form_args;
}

add_filter( 'avia_contact_form_args', 'exactmetrics_enfold_add_unique_id', 10, 2 );
