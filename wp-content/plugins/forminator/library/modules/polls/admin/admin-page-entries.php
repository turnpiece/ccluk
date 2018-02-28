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
	 * @var onject
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
	public function init() {
		if ( isset( $_REQUEST['form_id'] ) ) {
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

		$nonce = $_POST['forminatorEntryNonce'];
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
			if ( in_array( $slug, $this->visible_fields ) ) {
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
		printf( __( "Showing %s of %s fields", Forminator::DOMAIN ), $this->checked_fields, $this->total_fields );
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
			(function( $, doc ) {
				"use strict";
				jQuery('document').ready(function(){
					google.charts.load('current', {packages: ['corechart', 'bar']});
					google.charts.setOnLoadCallback(drawPollResults_<?php echo $this->model->id; ?>);
					function drawPollResults_<?php echo $this->model->id; ?>() {
						var data = google.visualization.arrayToDataTable([
							['<?php _e( 'Question', Forminator::DOMAIN ) ?>', '<?php _e( 'Results', Forminator::DOMAIN ) ?>', {role: 'style'}, {role: 'annotation'}],
							<?php
							$fields = $this->model->getFields();
							if ( !is_null( $fields ) ) {
								foreach ( $fields as $field ) {
									$label = $field->__get( 'main_label' );
									if ( !$label ) {
										$label = $field->__get( 'field_label' );
										if ( !$label ) {
											$label = addslashes( $field->title );
										}
									}

									if ( empty( $chart_colors ) ) {
										$chart_colors = $default_chart_colors;
									}
									$color      = array_shift( $chart_colors );
									$slug       = isset( $field->slug ) ? $field->slug : sanitize_title( $label );
									$entries    = Forminator_Form_Entry_Model::count_entries_by_form_and_field( $this->model->id, $slug );
									$style      = 'color: ' . $color;
									$annotation = Forminator_Form_Entry_Model::count_entries_by_form_and_field( $this->model->id, $slug ) . ' vote(s)';

									echo "['$label', $entries, '$style', '$annotation'],";
								}

							}
							?>
						]);

						<?php if ( $chart_design != 'pie' ) { ?>

							var options = {
								annotations: {
									textStyle: {
										fontName: '"Roboto", Arial, sans-serif',
										fontSize: 13,
										bold: false,
										color: '#333'
									}
								},
								backgroundColor: 'transparent',
								fontSize: 13,
								fontName: '"Roboto", Arial, sans-serif',
								hAxis: {
									format: 'decimal',
									baselineColor: '#4D4D4D',
									gridlines: {
										color: '#E9E9E9'
									},
									textStyle: {
										color: '#4D4D4D',
										fontName: '"Roboto", Arial, sans-serif',
										fontSize: 13,
										bold: false,
										italic: false
									},
									minValue: 0
								},
								vAxis: {
									baselineColor: '#4D4D4D',
									gridlines: {
										color: '#E9E9E9'
									},
									textStyle: {
										color: '#4D4D4D',
										fontName: '"Roboto", Arial, sans-serif',
										fontSize: 13,
										bold: false,
										italic: false
									},
									minValue: 0
								},
								tooltip: {
									isHtml: true,
									trigger: 'none'
								},
								legend: {
									position: "none"
								}
							};

						<?php } else { ?>

							var options = {
								colors: <?php echo wp_json_encode( $default_chart_colors )?>,
								backgroundColor: 'transparent',
								fontSize: 13,
								fontName: '"Roboto", Arial, sans-serif',
								tooltip: {
									isHtml: false,
									trigger: 'focus',
									text: 'both',
									textStyle:{
										fontName: '"Roboto", Arial, sans-serif'
									}
								}
							};

						<?php } ?>

						<?php if ( $chart_design == 'pie' ) {	?>
						var chart = new google.visualization.PieChart(document.getElementById('forminator-chart-poll'));
						<?php } else { ?>
						var chart = new google.visualization.BarChart(document.getElementById('forminator-chart-poll'));
						<?php } ?>

						chart.draw(data, options);
					}
				});
			}( jQuery, document ));
			</script>
		<?php
	}
}