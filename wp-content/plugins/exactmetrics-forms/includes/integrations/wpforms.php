<?php
/**
 * WPForms specific integrations.
 *
 * @package exactmetrics_forms
 */

/**
 * Class ExactMetrics_Forms_Integration_WPForms
 */
class ExactMetrics_Forms_Integration_WPForms {

	/**
	 * The UTM Tags used in this class.
	 *
	 * @var array
	 */
	public $utm_tags = array(
		'utm_source',
		'utm_medium',
		'utm_campaign',
		'utm_term',
		'utm_content',
	);

	/**
	 * ExactMetrics_Forms_Integration_WPForms constructor.
	 */
	public function __construct() {

		if ( ! function_exists( 'wpforms' ) || ( function_exists( 'wpforms' ) && ! wpforms()->pro ) ) {

			// Only init if WPForms is installed & active.
			return;
		}

		// When the form is submitted store the UTM data if it's available.
		add_action( 'wpforms_process_complete', array( $this, 'maybe_capture_utm' ), 15, 4 );
		// Add support for Ajax submit.
		add_action( 'wpforms_display_submit_before', array( $this, 'fields_for_ajax_submit' ) );
		// Display the UTM data in the entry, under Notes.
		add_action( 'wpforms_entry_details_content', array( $this, 'display_utm_data' ), 15, 3 );

		// Use the UTM fields as Smart Tags.
		add_filter( 'wpforms_smart_tags', array( $this, 'add_smart_tags' ) );
		// Process UTM Smart tags and replace them with the actual value.
		add_filter( 'wpforms_process_smart_tags', array( $this, 'process_smart_tags' ), 15, 4 );
		// Add UTM fields as options in the entries filters.
		add_filter( 'wpforms_entries_list_form_filters_search_fields', array( $this, 'add_to_filters' ) );
		// Use the UTM fields to actually filter the data.
		add_filter( 'wpforms_entry_handler_get_entries_args', array( $this, 'use_utm_data_in_filter' ), 15, 2 );

		// Add UTM data to the export options.
		add_filter( 'wpforms_pro_admin_entries_export_additional_info_fields', array(
			$this,
			'add_utm_to_export_options'
		) );
		// Add UTM data in the export data.
		add_filter( 'wpforms_pro_admin_entries_export_ajax_get_additional_info_value', array(
			$this,
			'add_utm_to_export_data'
		), 10, 3 );
	}

	/**
	 * Attempt to capture UTM parameters on WPForms submission to display in WPForms entry.
	 *
	 * @param array $fields The fields saved in the entry.
	 * @param array $entry The original $_POST object.
	 * @param array $form_data The form data array.
	 * @param int $entry_id The saved entry id.
	 */
	public function maybe_capture_utm( $fields, $entry, $form_data, $entry_id ) {

		// Attempt to save the data as custom meta to display in entry view.
		if ( ! empty( $entry_id ) && ! empty( $form_data['id'] ) ) {
			if ( ! empty( $_REQUEST['utm_source'] ) ) {
				wpforms()->entry_meta->add( array(
					'entry_id' => absint( $entry_id ),
					'form_id'  => absint( $form_data['id'] ),
					'user_id'  => get_current_user_id(),
					'type'     => 'exactmetrics_utm_source',
					'data'     => sanitize_text_field( wp_unslash( $_REQUEST['utm_source'] ) ),
				), 'entry_meta' );
			}

			if ( ! empty( $_REQUEST['utm_medium'] ) ) {
				wpforms()->entry_meta->add( array(
					'entry_id' => absint( $entry_id ),
					'form_id'  => absint( $form_data['id'] ),
					'user_id'  => get_current_user_id(),
					'type'     => 'exactmetrics_utm_medium',
					'data'     => sanitize_text_field( wp_unslash( $_REQUEST['utm_medium'] ) ),
				), 'entry_meta' );
			}

			if ( ! empty( $_REQUEST['utm_campaign'] ) ) {
				wpforms()->entry_meta->add( array(
					'entry_id' => absint( $entry_id ),
					'form_id'  => absint( $form_data['id'] ),
					'user_id'  => get_current_user_id(),
					'type'     => 'exactmetrics_utm_campaign',
					'data'     => sanitize_text_field( wp_unslash( $_REQUEST['utm_campaign'] ) ),
				), 'entry_meta' );
			}

			if ( ! empty( $_REQUEST['utm_term'] ) ) {
				wpforms()->entry_meta->add( array(
					'entry_id' => absint( $entry_id ),
					'form_id'  => absint( $form_data['id'] ),
					'user_id'  => get_current_user_id(),
					'type'     => 'exactmetrics_utm_term',
					'data'     => sanitize_text_field( wp_unslash( $_REQUEST['utm_term'] ) ),
				), 'entry_meta' );
			}

			if ( ! empty( $_REQUEST['utm_content'] ) ) {
				wpforms()->entry_meta->add( array(
					'entry_id' => absint( $entry_id ),
					'form_id'  => absint( $form_data['id'] ),
					'user_id'  => get_current_user_id(),
					'type'     => 'exactmetrics_utm_content',
					'data'     => sanitize_text_field( wp_unslash( $_REQUEST['utm_content'] ) ),
				), 'entry_meta' );
			}
		}

	}

	/**
	 * Display UTM data, if available, below the notes section for the entry.
	 *
	 * @param object $entry The entry object.
	 * @param array $form_data Form data for current entry.
	 * @param WPForms_Entries_Single $instance The instance of the entries view class.
	 */
	public function display_utm_data( $entry, $form_data, $instance ) {

		$utm_keys = array(
			'exactmetrics_utm_source',
			'exactmetrics_utm_medium',
			'exactmetrics_utm_campaign',
			'exactmetrics_utm_term',
			'exactmetrics_utm_content',
		);

		$utm_data = array();

		foreach ( $utm_keys as $utm_key ) {
			$utm_single_data = wpforms()->entry_meta->get_meta(
				array(
					'entry_id' => $entry->entry_id,
					'type'     => $utm_key,
				)
			);
			if ( empty( $utm_single_data ) || empty( $utm_single_data[0] ) || ! isset( $utm_single_data[0]->data ) ) {
				// If no UTM data is present, continue.
				continue;
			}
			$utm_data_key              = str_replace( 'exactmetrics_', '', $utm_key );
			$utm_data[ $utm_data_key ] = $utm_single_data[0]->data;
		}

		if ( empty( $utm_data ) ) {
			// If no UTM data is present, don't show the box.
			return;
		}

		?>
		<div id="wpforms-entry-notes" class="postbox">

			<h2 class="hndle">
				<span><?php esc_html_e( 'UTM Data by ExactMetrics', 'exactmetrics-forms' ); ?></span>
			</h2>

			<div class="inside">
				<?php
				echo '<div class="wpforms-entry-notes-list">';
				foreach ( $utm_data as $utm => $value ) {
					echo '<div class="wpforms-entry-notes-single"><strong>' . esc_html( $this->get_utm_label( $utm ) ) . ' (' . esc_attr( $utm ) . '):</strong> ' . esc_html( $value ) . '</div>';
				}

				echo '</div>';
				?>
			</div>

		</div>
		<?php
	}

	/**
	 * Get a label to display for each utm key.
	 *
	 * @param string $utm_key The utm key to get the label for.
	 *
	 * @return string The translated utm label.
	 */
	public function get_utm_label( $utm_key ) {

		$labels = array(
			'utm_source'   => __( 'Campaign Source', 'exactmetrics-forms' ),
			'utm_medium'   => __( 'Campaign Medium', 'exactmetrics-forms' ),
			'utm_campaign' => __( 'Campaign Name', 'exactmetrics-forms' ),
			'utm_term'     => __( 'Campaign Term', 'exactmetrics-forms' ),
			'utm_content'  => __( 'Campaign Content', 'exactmetrics-forms' ),
		);

		if ( ! empty( $labels[ $utm_key ] ) ) {
			return $labels[ $utm_key ];
		}

		return '';
	}

	/**
	 * Add UTM tags to the list of Smart Tags available.
	 *
	 * @param array $tags The Smart Tags used by WPForms.
	 *
	 * @return array
	 */
	public function add_smart_tags( $tags ) {

		foreach ( $this->utm_tags as $tag ) {
			if ( ! isset( $tags[ $tag ] ) ) {
				$tags[ $tag ] = $this->get_utm_label( $tag );
			}
		}

		return $tags;
	}

	/**
	 * Process the content passed to replace the tag with stored utm value, if available.
	 *
	 * @param string $content The content to process.
	 * @param array $form_data Current form data.
	 * @param array|string $fields Form fields.
	 * @param int|string $entry_id Entry id.
	 *
	 * @return string
	 */
	public function process_smart_tags( $content, $form_data, $fields = '', $entry_id = '' ) {

		$tag_value = '';

		// If the entry id is not set return early.
		if ( empty( $entry_id ) ) {
			return $content;
		}

		preg_match_all( '/\\{(.+?)\\}/', $content, $tags );

		if ( ! empty( $tags[1] ) ) {

			foreach ( $tags[1] as $key => $tag ) {

				// Check if the tag is one of ours.
				if ( in_array( $tag, $this->utm_tags, true ) ) {
					$utm_single_data = wpforms()->entry_meta->get_meta(
						array(
							'entry_id' => $entry_id,
							'type'     => 'exactmetrics_' . $tag,
						)
					);

					if ( ! empty( $utm_single_data ) && ! empty( $utm_single_data[0] ) && isset( $utm_single_data[0]->data ) ) {
						// If data is present set it so it's used.
						$tag_value = $utm_single_data[0]->data;
					}

					// Replace the tag with the data or empty if not set.
					$content = str_replace( '{' . $tag . '}', $tag_value, $content );
				}
			}
		}

		return $content;

	}

	/**
	 * Add available tags to the filter options.
	 *
	 * @param array $filters The filters available in the dropdown.
	 *
	 * @return array
	 */
	public function add_to_filters( $filters ) {

		foreach ( $this->utm_tags as $utm_tag ) {
			$filters[ $utm_tag ] = $this->get_utm_label( $utm_tag );
		}

		return $filters;
	}

	/**
	 * If a utm parameter is used in the search, filter entries ids by that.
	 *
	 * @param array $args The current entries list args.
	 *
	 * @return array
	 */
	public function use_utm_data_in_filter( $args ) {

		if ( isset( $_GET['search']['field'] ) && in_array( $_GET['search']['field'], $this->utm_tags ) && ! empty( $_GET['search']['term'] ) ) {

			global $wpdb;
			$search_term = esc_sql( sanitize_text_field($_GET['search']['term']) );
			$meta_key    = 'exactmetrics_' . esc_sql( sanitize_text_field($_GET['search']['field']) );
			$where       = array();
			$where[]     = "`type` = '$meta_key' ";

			$value_compare = ! empty( $_GET['search']['comparison'] ) ? esc_sql( sanitize_text_field($_GET['search']['comparison']) ) : '';
			if ( ! empty( $value_compare ) ) {
				switch ( $value_compare ) {
					case '': // Preserving backward compatibility.
					case 'is':
						$where['arg_value'] = "`data` = '" . esc_sql( $search_term ) . "'";
						break;

					case 'is_not':
						$where['arg_value'] = "`data` <> '" . esc_sql( $search_term ) . "'";
						break;

					case 'contains':
						$where['arg_value'] = "`data` LIKE '%" . esc_sql( $search_term ) . "%'";
						break;

					case 'contains_not':
						$where['arg_value'] = "`data` NOT LIKE '%" . esc_sql( $search_term ) . "%'";
						break;
				}
			}

			$where_sql = implode( ' AND ', $where );

			$query = 'SELECT entry_id FROM ' . wpforms()->entry_meta->table_name . ' WHERE ' . $where_sql . ' LIMIT 0,' . PHP_INT_MAX;

			$results        = $wpdb->get_results( $query );
			$search_results = array();
			if ( ! empty( $results ) && is_array( $results ) ) {
				foreach ( $results as $result ) {
					$search_results[] = $result->entry_id;
				}
			}

			$args['entry_id'] = $search_results;
		}

		return $args;
	}

	/**
	 * Add UTM options to the export additional fields.
	 *
	 * @param array $ai_fields The array of export fields.
	 *
	 * @return mixed
	 */
	public function add_utm_to_export_options( $ai_fields ) {

		foreach ( $this->utm_tags as $utm_tag ) {

			if ( ! isset( $ai_fields[ $utm_tag ] ) ) {
				$ai_fields[ $utm_tag ] = self::get_utm_label( $utm_tag );
			}
		}

		return $ai_fields;
	}

	/**
	 * Use the UTM value for the export.
	 *
	 * @param string $value The value to add in the export.
	 * @param string $col_id The name of the field to grab data for.
	 * @param object $entry The entry object.
	 *
	 * @return string
	 */
	public function add_utm_to_export_data( $value, $col_id, $entry ) {

		if ( in_array( $col_id, $this->utm_tags, true ) && ! empty( $entry['entry_id'] ) ) {
			$value = wpforms()->entry_meta->get_meta(
				array(
					'entry_id' => $entry['entry_id'],
					'type'     => 'exactmetrics_' . $col_id,
				)
			);
			if ( is_array( $value ) && isset( $value[0] ) && isset( $value[0]->data ) ) {
				$value = $value[0]->data;
			} else {
				$value = ''; // No data so don't send an array value.
			}
		}

		return $value;
	}

	/**
	 * If the form uses ajax submission output hidden fields for the UTM data fields.
	 *
	 * @param array $formdata The WPForms form data with settings and fields.
	 */
	public function fields_for_ajax_submit( $formdata ) {

		if ( empty( $formdata['settings']['ajax_submit'] ) || ! $formdata['settings']['ajax_submit'] ) {
			return;
		}

		foreach ( $this->utm_tags as $utm_tag ) {
			if ( ! empty ( $_GET[ $utm_tag ] ) ) {
				echo wp_kses('<input type="hidden" name="' . esc_attr($utm_tag) . '" value="' . sanitize_text_field( wp_unslash( $_GET[ $utm_tag ] ) ) . '" />', [
					'input' => array(
						'type' => array(),
						'name' => array(),
						'value' => array(),
					)
				]);
			}
		}

	}
}

new ExactMetrics_Forms_Integration_WPForms();
