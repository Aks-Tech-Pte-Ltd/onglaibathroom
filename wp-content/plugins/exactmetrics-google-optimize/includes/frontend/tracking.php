<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function exactmetrics_goptimize_is_allowed_on_current_page() {
	$is_conditional_container = exactmetrics_get_option( 'goptimize_load_conditionally', false );

	// If the conditional container option is disabled, then the container id will load on all pages,
	// that's why it should return true
	if ( ! $is_conditional_container ) {
		return true;
	}

	$rule_sets = exactmetrics_get_option( 'goptimize_container_conditions', false );

	// If conditional option is enabled, then there should be at least one rule set,
	// if there is no rule set, then the container id will not load on any page.
	if ( empty( $rule_sets ) ) {
		return false;
	}

	$count            = 0;
	$rule_sets_return = array();

	// Logic of each rule set is `or`, so we have to find out only one rule set that returns true by checking each rule set conditions
	foreach ( $rule_sets as $rule ) {

		$rule_count                 = 0;
		$rule_conditions            = $rule['rule_conditions'];
		$rule_sets_return[ $count ] = true;
		$rule_conditions_return     = array();

		// Here all the conditions should return true as the logic of each condition is `and`
		foreach ( $rule_conditions as $condition ) {
			global $post;

			$user      = wp_get_current_user();
			$page_id   = $post->ID;
			$page_slug = $post->post_name;
			$operator  = isset( $condition['operator'] ) ? $condition['operator'] : '';
			$value     = isset( $condition['value'] ) ? $condition['value'] : '';

			if ( is_archive() ) {
				$query_object = get_queried_object();

				if ( $query_object ) {
					$page_slug = $query_object->slug;
				} else {
					$page_slug = '';
				}
			}

			$rule_conditions_return[ $rule_count ] = false;

			switch ( $operator ) {
				case 'logged_in':
					if ( is_user_logged_in() && ( in_array( $value, (array) $user->roles ) || $value === 'all_roles' ) ) {
						$rule_conditions_return[ $rule_count ] = true;
					}
					break;
				case 'not_logged_in':
					if ( ! is_user_logged_in() ) {
						$rule_conditions_return[ $rule_count ] = true;
					}
					break;
				case 'page_is':
					if ( isset( $value['id'] ) && intval( $value['id'] ) === $page_id ) {
						$rule_conditions_return[ $rule_count ] = true;
					}

					if ( isset( $value['id'] ) && intval( $value['id'] ) === - 1 && is_home() ) {
						$rule_conditions_return[ $rule_count ] = true;
					}
					break;
				case 'page_is_not':
					if ( isset( $value['id'] ) && intval( $value['id'] ) != $page_id ) {
						$rule_conditions_return[ $rule_count ] = true;
					}

					if ( isset( $value['id'] ) && intval( $value['id'] ) === - 1 && ! is_home() ) {
						$rule_conditions_return[ $rule_count ] = true;
					}
					break;
				case 'slug_starts_with':
					if ( substr( $page_slug, 0, strlen( $value ) ) === $value && ! is_home() ) {
						$rule_conditions_return[ $rule_count ] = true;
					}
					break;
				case 'slug_ends_with':
					$slug_value_length = strlen( $value );

					if ( substr( $page_slug, - $slug_value_length ) === $value && ! is_home() ) {
						$rule_conditions_return[ $rule_count ] = true;
					}
					break;
				case 'slug_contains':
					if ( strpos( $page_slug, $value ) !== false && ! is_home() ) {
						$rule_conditions_return[ $rule_count ] = true;
					}
					break;
				case 'slug_not_contains':
					if ( strpos( $page_slug, $value ) === false ) {
						$rule_conditions_return[ $rule_count ] = true;
					}
					break;
				default:
					$rule_conditions_return[ $rule_count ] = false;
					break;
			}

			$rule_count ++;
		}

		// If there is any condition does not meet then the current rule will return false
		if ( in_array( false, $rule_conditions_return, true ) ) {
			$rule_sets_return[ $count ] = false;
		}

		$count ++;

	}

	// Let's try to find at least one rule meet that meet condition, then return true
	if ( in_array( true, $rule_sets_return, true ) ) {
		return true;
	}

}

function exactmetrics_goptimize_frontend_tracking_options_before_analytics( $mode ) {
	$pagehide  = exactmetrics_get_option( 'goptimize_pagehide', false );
	$container = exactmetrics_get_option( 'goptimize_container', '' );

	if ( empty( $container ) ) {
		return;
	}

	if ( ! exactmetrics_goptimize_is_allowed_on_current_page() ) {
		return;
	}

	$speed = absint( exactmetrics_get_option( 'goptimize_pagehide_speed', 4000 ) );

	ob_start();
	if ( $pagehide ) { ?>
		<style>.exactmetrics-async-hide {
				opacity: 0 !important
			} </style>
		<script data-cfasync="false">(function (a, s, y, n, c, h, i, d, e) {
				s.className += ' ' + y;
				h.start = 1 * new Date;
				h.end = i = function () {
					s.className = s.className.replace(RegExp(' ?' + y), '')
				};
				(a[n] = a[n] || []).hide = h;
				setTimeout(function () {
					i();
					h.end = null
				}, c);
				h.timeout = c;
			})(window, document.documentElement, 'exactmetrics-async-hide', 'dataLayer',<?php echo absint($speed); ?>,
				{<?php echo "'" . esc_js( $container ) . "'"; ?>:
			true
			})
			;</script>
	<?php } ?>
	<script src="https://www.googleoptimize.com/optimize.js?id=<?php echo esc_js( $container ); ?>"
			onerror="dataLayer.hide.end && dataLayer.hide.end()"></script>
	<?php
	echo ob_get_clean(); // phpcs:ignore
}

add_action( 'exactmetrics_tracking_before', 'exactmetrics_goptimize_frontend_tracking_options_before_analytics' );
remove_action( 'exactmetrics_tracking_before', 'exactmetrics_performance_frontend_tracking_options_before_analytics' );

// Force the option for compatibility mode to true.
add_filter( 'exactmetrics_get_option_gtagtracker_compatibility_mode', '__return_true', 50 );
