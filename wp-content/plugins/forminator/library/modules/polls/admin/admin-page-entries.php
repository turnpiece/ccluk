<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Poll_View_Page
 *
 * @since 1.0
 */
class Forminator_Poll_View_Page extends Forminator_Admin_Page {

	/**
	 * Current model
	 *
	 * @var Forminator_Poll_Form_Model
	 */
	protected $model = false;

	/**
	 * Current form id
	 *
	 * @var int
	 */
	protected $form_id = 0;

	/**
	 * Fields
	 *
	 * @var array
	 */
	protected $fields = array();

	/**
	 * Visible Fields
	 *
	 * @var array
	 */
	protected $visible_fields = array();

	/**
	 * Number of checked fields
	 *
	 * @var int
	 */
	protected $checked_fields = 0;

	/**
	 * Number of total fields
	 *
	 * @var int
	 */
	protected $total_fields = 0;

	/**
	 * Initialise variables
	 *
	 * @since 1.0
	 */
	public function before_render() {
		if ( isset( $_REQUEST['form_id'] ) ) { // WPCS: CSRF OK
			$this->form_id  = sanitize_text_field( $_REQUEST['form_id'] );
			$this->model 	= Forminator_Poll_Form_Model::model()->load( $this->form_id );
			if ( is_object( $this->model ) ) {
				$this->fields = $this->model->getFields();
				if ( is_null( $this->fields ) ) {
					$this->fields = array();
				}
				add_action( 'admin_footer', array( $this, 'render_pie_chart' ), 100 );
			} else {
				$this->model = false;
			}
			$this->total_fields 	= count( $this->fields ) + 1;
			$this->checked_fields 	= $this->total_fields;
			$this->process_request();
		}
	}

	/**
	 * Process request
	 *
	 * @since 1.0
	 */
	public function process_request() {
		if ( ! isset( $_POST['forminatorEntryNonce'] ) ) {
			return;
		}

		$nonce = $_POST['forminatorEntryNonce']; // WPCS: CSRF OK
		if ( ! wp_verify_nonce( $nonce, 'forminatorPollEntries' ) ) {
			return;
		}

		if ( isset( $_POST['field'] ) ) {
			$this->visible_fields = $_POST['field'];
			$this->checked_fields = count( $this->visible_fields );
		}
	}

	/**
	 * Register content boxes
	 *
	 * @since 1.0
	 */
    public function register_content_boxes() {
        $this->add_box(
			'custom-form/entries/popup/exports-list',
			__( 'Your Exports', Forminator::DOMAIN ),
			'entries-popup-exports-list',
			null,
			null,
			null
        );

        $this->add_box(
			'custom-form/entries/popup/schedule-export',
			__( 'Edit Schedule Export', Forminator::DOMAIN ),
			'entries-popup-schedule-export',
			null,
			null,
			null
		);
	}

	/**
	 * Get fields
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_fields() {
		return $this->fields;
	}

	/**
	 * Visible fields
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_visible_fields() {
		return $this->visible_fields;
	}

	/**
	 * Checked field option
	 *
	 * @since 1.0
	 * @param string $slug - the field slug
	 *
	 * @return string
	 */
	public function checked_field( $slug ) {
		if ( !empty( $this->visible_fields ) && is_array( $this->visible_fields ) ) {
			if ( in_array( $slug, $this->visible_fields, true ) ) {
				return checked( $slug, $slug );
			} else {
				return '';
			}
		}

		return checked( $slug, $slug );
	}

	/**
	 * Get model name
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_model_name() {
		if ( $this->model ) {
			return isset ( $this->model->settings['poll-title'] ) ? $this->model->settings['poll-title'] : $this->model->name;
		}

		return '';
	}

	/**
	 * Get poll question
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_poll_question() {
		if ( $this->model && isset ( $this->model->settings['poll-question'] ) ) {
			return $this->model->settings['poll-question'];
		}

		return '';
	}

	/**
	 * Get poll description
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_poll_description() {
		if ( $this->model && isset ( $this->model->settings['poll-description'] ) ) {
			return $this->model->settings['poll-description'];
		}

		return '';
	}

	/**
	 * Results chart design
	 *
	 * @since 1.0
	 * @return string
	 */
	private function get_chart_design() {
		if ( $this->model && isset ( $this->model->settings['results-style'] ) ) {
			return $this->model->settings['results-style'];
		}

		return 'bar';
	}

	/**
	 * Fields header
	 *
	 * @since 1.0
	 * @return string
	 */
	public function fields_header() {
		echo esc_html( sprintf( __( 'Showing %$1s of %$2s fields', Forminator::DOMAIN ), $this->checked_fields, $this->total_fields ) );
	}

	/**
	 * Get fields table
	 *
	 * @since 1.0
	 * @return Forminator_Entries_List_Table
	 */
	public function get_table() {
		return new Forminator_Entries_List_Table( array(
			'model' 			=> $this->model,
			'visible_fields' 	=> $this->visible_fields
		) );
	}

	/**
	 * Get custom votes
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_custom_votes() {
		$custom_votes = array();
		if ( is_object( $this->model ) ) {
			$entries = Forminator_Form_Entry_Model::get_entries( $this->model->id );
			foreach ( $entries as $entry ) {
				$custom_votes[] = $entry->get_meta( 'extra', '' );
			}
			if ( !empty( $custom_votes ) ) {
				$custom_votes = array_unique( $custom_votes );
			}
		}

		return $custom_votes;
	}

	/**
	 * Map Custom Votes
	 *
	 * @since   1.0.5
	 * @example {
	 *  'ELEMENT_ID' => [
	 *      'EXTRA_VALUE' => COUNT
	 *  ],
	 * 'answer-2' => [
	 *      'skip it' => 9
	 *  ]
	 * }
	 *
	 * @return array
	 */
	public function map_custom_votes() {
		$custom_votes = array();
		if ( is_object( $this->model ) ) {
			$fields_with_extra_enabled = array();

			$fields_array = $this->model->getFieldsAsArray();
			// Trigger Update DB if needed.
			Forminator_Form_Entry_Model::map_polls_entries( $this->model->id, $fields_array );

			$fields = $this->model->getFields();

			foreach ( (array) $fields as $field ) {
				if ( filter_var( $field->use_extra, FILTER_VALIDATE_BOOLEAN ) === true ) {
					$fields_with_extra_enabled[] = $field->slug;
				}
			}

			if ( ! empty( $fields_with_extra_enabled ) ) {
				$custom_votes = Forminator_Form_Entry_Model::count_polls_with_extra( $this->model->id, $fields_with_extra_enabled );
			}
		}

		return $custom_votes;
	}

	/**
	 * Get Element Title
	 *
	 * @since 1.0.5
	 *
	 * @param $element_id
	 *
	 * @return mixed
	 */
	public function get_field_title( $element_id ) {
		$fields = $this->model->pluck_fields_array( 'title', 'element_id', $element_id );

		return ( isset( $fields[ $element_id ] ) ? $fields[ $element_id ] : $element_id );
	}

	/**
	 * Render the chart
	 * Generate the google charts js for the chart
	 *
	 * @since 1.0
	 */
	public function render_pie_chart() {
		$chart_colors         = apply_filters( 'forminator_poll_chart_color', array( '#F4B414', '#1ABC9C', '#17A8E3', '#18485D', '#D30606' ) );
		$default_chart_colors = $chart_colors;
		$chart_design         = $this->get_chart_design();
		?>
        <script type="text/javascript">
			(function ($, doc) {
				"use strict";
				jQuery('document').ready(function () {
					google.charts.load('current', {packages: ['corechart', 'bar']});
					google.charts.setOnLoadCallback(drawPollResults_<?php echo esc_attr( $this->model->id ); ?>);

					function drawPollResults_<?php echo esc_attr( $this->model->id ); ?>() {
						var data = google.visualization.arrayToDataTable([
							['<?php esc_html_e( 'Question', Forminator::DOMAIN ); ?>', '<?php esc_html_e( 'Results', Forminator::DOMAIN ); ?>', {role: 'style'}, {role: 'annotation'}],
							<?php
							$fields_array = $this->model->getFieldsAsArray();
							$map_entries = Forminator_Form_Entry_Model::map_polls_entries( $this->model->id, $fields_array );
							$fields = $this->model->getFields();
							if ( ! is_null( $fields ) ) {
								foreach ( $fields as $field ) {
									$label = addslashes( $field->title );

									if ( empty( $chart_colors ) ) {
										$chart_colors = $default_chart_colors;
									}

									$color   = array_shift( $chart_colors );
									$slug    = isset( $field->slug ) ? $field->slug : sanitize_title( $label );
									$entries = 0;
									if ( in_array( $slug, array_keys( $map_entries ), true ) ) {
										$entries = $map_entries[ $slug ];
									}
									$style      = 'color: ' . $color;
									$annotation = $entries . ' vote(s)';

									echo "['$label', $entries, '$style', '$annotation'],"; // WPCS: XSS ok.
								}
							}
							?>
						]);

						var options = <?php echo wp_json_encode( Forminator_Poll_Front::get_default_chart_options( $this->model ) ); ?>;
						<?php if ( 'pie' === $chart_design ) { ?>
						var chart = new google.visualization.PieChart(document.getElementById('forminator-chart-poll'));
						<?php } else { ?>
						var chart = new google.visualization.BarChart(document.getElementById('forminator-chart-poll'));
						<?php } ?>

						chart.draw(data, options);
					}
				});
			}(jQuery, document));
        </script>
		<?php
	}
}