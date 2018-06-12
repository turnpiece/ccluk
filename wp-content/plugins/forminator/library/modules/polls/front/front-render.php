<?php

/**
 * Front render class for custom forms
 *
 * @since 1.0
 */
class Forminator_Poll_Front extends Forminator_Render_Form {

	/**
	 * Class instance
	 *
	 * @var Forminator_Render_Form|null
	 */
	private static $instance = null;

	/**
	 * Scripts of graph results
	 *
	 * @var array
	 */
	private static $graph_result_scripts = array();

	/**
	 * @var array
	 */
	private static $forms_properties = array();

	/**
	 * Default Combination of Chart Colors
	 *
	 * @var array
	 */
	public static $default_chart_colors = array( '#F4B414', '#1ABC9C', '#17A8E3', '#18485D', '#D30606' );

	/**
	 * Return class instance
	 *
	 * @since 1.0
	 * @return Forminator_Poll_Front
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize method
	 *
	 * @since 1.0
	 */
	public function init() {
		add_shortcode( 'forminator_poll', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Render shortcode
	 *
	 * @since 1.0
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	public function render_shortcode( $atts = array() ) {
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', 1 );
		}
		//use already created instance if already available
		$view = self::get_instance();
		if ( ! isset( $atts['id'] ) ) {
			return $view->message_required();
		}

		ob_start();

		$view->display( $atts['id'], false );

		return ob_get_clean();
	}


	/**
	 * Display form method
	 *
	 * @since 1.0
	 *
	 * @param $id
	 */
	public function display( $id, $ajax = false, $data = false ) {
		if ( $data && ! empty( $data ) ) {
			// New form, we have to update the form id
			$has_id = filter_var( $id, FILTER_VALIDATE_BOOLEAN );

			if( ! $has_id && isset($data['settings']['formID']) ) {
				$id = $data['settings']['formID'];
			}

			$this->model = Forminator_Poll_Form_Model::model()->load_preview( $id, $data );
		} else {
			$this->model = Forminator_Poll_Form_Model::model()->load( $id );
		}

		if ( is_object( $this->model ) ) {
			$this->generate_render_id( $id );

			$is_same_form   = false;
			$is_same_render = false;
			if ( isset( $_REQUEST['form_id'] ) && (int) $_REQUEST['form_id'] === (int) $this->model->id ) { // WPCS: CSRF OK
				$is_same_form = true;
			}

			if ( isset( $_REQUEST['render_id'] ) && (int) $_REQUEST['render_id'] === (int) self::$render_ids[ $this->model->id ] ) { // WPCS: CSRF OK
				$is_same_render = true;
			}

			if ( isset( $_REQUEST['saved'] ) && $is_same_form && $is_same_render && $this->show_results() ) { // WPCS: CSRF OK
				$this->track_views = false;
				$this->render_success();
			} elseif ( isset( $_REQUEST['results'] ) && $is_same_form && $is_same_render && $this->show_link() ) { // WPCS: CSRF OK
				$this->track_views = false;
				$this->render_success();
			} elseif ( ! $this->is_admin && ( ! $this->model->current_user_can_vote() && ( $this->show_results() || $this->show_link() ) ) ) { // WPCS: CSRF OK
				$this->track_views = false;
				$this->render_success();
			} else {
				$this->render( $id );
			}

			self::$forms_properties[] = array(
				'id'            => $id,
				'render_id'     => self::$render_ids[ $id ],
				'settings'      => $this->get_form_settings(),
				'chart_design'  => $this->get_chart_design(),
				'chart_options' => self::get_default_chart_options( $this->model ),
			);

			if ( ! $ajax ) {
				forminator_print_front_styles();
				forminator_print_front_scripts();
				add_action( 'wp_footer', array( $this, 'forminator_render_front_scripts' ), 9999 );
				add_action( 'wp_footer', array( $this, 'graph_scripts' ), 100 );
			}

			if ( $ajax ) {
				$this->print_styles();
			} else {
				add_action( 'wp_footer', array( $this, 'print_styles' ), 9999 );
			}
		}
	}

	/**
	 * Return form fields
	 *
	 * @since 1.0
	 * @return array|mixed
	 */
	public function get_fields() {
		if ( is_object( $this->model ) ) {
			return $this->model->getFieldsGrouped();
		} else {
			return $this->message_not_found();
		}
	}

	/**
	 * Poll question
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_poll_question() {
		if ( is_object( $this->model ) && isset( $this->model->settings['poll-question'] ) ) {
			return $this->model->settings['poll-question'];
		} else {
			return '';
		}
	}

	/**
	 * Poll Description
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_poll_description() {
		if ( is_object( $this->model ) && isset( $this->model->settings['poll-description'] ) ) {
			return $this->model->settings['poll-description'];
		} else {
			return '';
		}
	}

	/**
	 * Poll header
	 *
	 * @since 1.0
	 * @return string
	 */
	public function render_form_header() {
		$html = '<div class="forminator-poll-response-message">';
		ob_start();
		do_action( 'forminator_poll_post_message' ); //prints html, so we need to capture this
		if ( isset( $_REQUEST['saved'] ) && ! isset( $_REQUEST['results'] ) ) { // WPCS: CSRF OK
			if ( isset( $_REQUEST['form_id'] ) && $_REQUEST['form_id'] === $this->model->id // WPCS: CSRF OK
			     && isset( $_REQUEST['render_id'] )  // WPCS: CSRF OK
			     && $_REQUEST['render_id'] === self::$render_ids[ $this->model->id ] ) { // WPCS: CSRF OK
				$this->track_views = false;
				?>
                <label class="forminator-label--success"><span><?php esc_html_e( "Your vote has been saved", Forminator::DOMAIN ); ?></span></label>
				<?php
			}
		} else {
			if ( ! $this->is_admin && ! $this->model->current_user_can_vote() ) {
				$this->track_views = false;
				?>
                <label class="forminator-label--info"><span><?php esc_html_e( "You have already voted for this poll", Forminator::DOMAIN ); ?></span></label>
				<?php
			}
		}
		$html .= ob_get_clean();
		$html .= '</div>';

		$question    = $this->get_poll_question();
		$description = $this->get_poll_description();
		if ( ! empty( $question ) ) {
			$html .= sprintf( '<p class="forminator-poll--question">%s</p>', $question );
		}
		if ( ! empty( $description ) ) {
			$html .= sprintf( '<p class="forminator-poll--description">%s</p>', $description );
		}

		return apply_filters( 'forminator_poll_header', $html, $this );
	}

	/**
	 * Poll question
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_submit_button_text() {
		if ( is_object( $this->model ) && isset( $this->model->settings['poll-button-label'] ) && ! empty( $this->model->settings['poll-button-label'] ) ) {
			return $this->model->settings['poll-button-label'];
		} else {
			return __( "Submit", Forminator::DOMAIN );
		}
	}

	/**
	 * Button markup
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_button_markup() {
		// if its on admin then bypass current_user_can_vote.
		if ( is_object( $this->model ) && ( $this->is_admin || $this->model->current_user_can_vote() ) ) {
			$button = $this->get_submit_button_text();
			$html   = '<div class="forminator-poll--actions">';
			$html   .= sprintf( '<button class="forminator-button">%s</button>', $button );
			if ( isset( $_REQUEST['saved'] ) || $this->show_link() ) { // WPCS: CSRF OK
				$url = '';
				if ( isset( $_REQUEST['saved'] ) ) { // WPCS: CSRF OK
					$this->track_views = false;
					$url               = remove_query_arg( array( 'saved', 'form_id', 'render_id' ) );
				}

				// Fallback, disable view results in Preview
				if ( $this->is_admin ) {
					$url = '#';
				} else {
					$url = add_query_arg(
						array(
									'results' => 'true',
									'form_id' => $this->model->id,
									'render_id' => self::$render_ids[ $this->model->id ]
						),
					$url );
				}
				if ( 0 === Forminator_Form_Entry_Model::count_entries($this->model->id) ) {
					$html .= sprintf( '<span class="forminator-button forminator-button-ghost">%s</span>', __( 'No votes yet', Forminator::DOMAIN ) );
				} else {
					$html .= sprintf( '<a href="%s" class="forminator-button forminator-button-ghost">%s</a>', esc_url( $url ), __( 'View results', Forminator::DOMAIN ) );
				}
			}
			$html .= '</div>';

			return apply_filters( 'forminator_render_button_markup', $html, $button );
		} else {
			$html = '<div class="forminator-poll--actions">';
			if ( $this->show_link() ) {
				$url = '';
				if ( isset( $_REQUEST['saved'] ) ) { // WPCS: CSRF OK
					$this->track_views = false;
					$url               = remove_query_arg( array( 'saved', 'form_id', 'render_id' ) );
				}
				// Fallback, disable view results in Preview
				if ( $this->is_admin ) {
					$url = '#';
				} else {
					$url = add_query_arg(
						array(
							'results' => 'true',
							'form_id' => $this->model->id,
							'render_id' => self::$render_ids[ $this->model->id ]
						),
					$url );
				}
				$html .= sprintf( '<a href="%s" class="forminator-button forminator-button-ghost">%s</a>', esc_url( $url ), __( 'View results', Forminator::DOMAIN ) );
			}
			$html .= '</div>';

			return apply_filters( 'forminator_render_button_disabled_markup', $html, $this );
		}
	}

	/**
	 * Return Poll ID required message
	 *
	 * @since 1.0
	 * @return string
	 */
	public function message_required() {
		return __( "Poll ID attribute is required!", Forminator::DOMAIN );
	}

	/**
	 * Return From ID not found message
	 *
	 * @since 1.0
	 * @return string
	 */
	public function message_not_found() {
		return __( "Poll not found!", Forminator::DOMAIN );
	}

	/**
	 * Extra form classes for ajax
	 *
	 * @since 1.0
	 * @return string
	 */
	public function form_extra_classes() {
		$classes = '';

		$ajax_form = $this->is_ajax_submit();
		if ( $ajax_form ) {
			$classes .= ' forminator_ajax';
		}

		if ( is_object( $this->model ) && ! $this->is_admin && ! $this->model->current_user_can_vote() ) {
			$classes .= ' forminator-poll-disabled';
		}

		return apply_filters( 'forminator_polls_form_extra_classes', $classes, $this );
	}

	/**
	 * Return before wrapper markup
	 *
	 * @since 1.0
	 *
	 * @param $wrapper
	 *
	 * @return string
	 */
	public function render_wrapper_before( $wrapper ) {
		$html = '<ul class="forminator-poll--answers">';

		return apply_filters( 'forminator_before_wrapper_markup', $html );
	}

	/**
	 * Return after wrapper markup
	 *
	 * @since 1.0
	 *
	 * @param $wrapper
	 *
	 * @return mixed
	 */
	public function render_wrapper_after( $wrapper ) {
		$html = '</ul>';

		return apply_filters( 'forminator_after_wrapper_markup', $html );
	}

	/**
	 * Return fields markup
	 *
	 * @since 1.0
	 *
	 * @param bool $render
	 *
	 * @return string
	 */
	public function render_fields( $render = true ) {
		$html     = '';
		$wrappers = $this->get_fields();

		if ( ! empty( $wrappers ) ) {
			foreach ( $wrappers as $key => $wrapper ) {

				if ( ! isset( $wrapper['fields'] ) ) {
					return;
				}

				// Render before wrapper markup
				$html .= $this->render_wrapper_before( $wrapper );

				foreach ( $wrapper['fields'] as $k => $field ) {
					if( ! empty( $field['title'] ) ) {
						$uniq_id = uniqid();
						do_action( 'forminator_before_field_render', $field );

						// Render before field markup
						$html .= $this->render_field_before( $field );

						// Render field
						$html .= $this->render_field_radio( $field, $uniq_id );

						do_action( 'forminator_after_field_render', $field );

						// Render after field markup
						$html .= $this->render_field_after( $field );


						$use_extra = Forminator_Field::get_property( 'use_extra', $field, false );
						$use_extra = filter_var( $use_extra, FILTER_VALIDATE_BOOLEAN );
						if ( $use_extra ) {
							// Render before field markup
							$html .= $this->render_field_before( $field );

							$html .= $this->render_extra_field( $field, $uniq_id );
							// Render after field markup
							$html .= $this->render_field_after( $field );
						}
					}
				}

				// Render after wrapper markup
				$html .= $this->render_wrapper_after( $wrapper );
			}
		}

		if ( $render ) {
			echo $html; // phpcs:ignore
		} else {
			return apply_filters( 'forminator_render_fields_markup', $html, $wrappers, $this );
		}

	}

	/**
	 * Return field markup of Radio for poll
	 *
	 * @since 1.0
	 *
	 * @param $field
	 * @param $uniq_id
	 *
	 * @return mixed
	 */
	public function render_field_radio( $field, $uniq_id ) {
		$label = Forminator_Field::get_property( 'title', $field, $this->model->id );
		// Get field object
		$element_id = Forminator_Field::get_property( 'element_id', $field );
		$name       = $this->model->id;

		if ( ! isset( $field['value'] ) ) {
			$field['value'] = sanitize_title( $label );
		}

		// form_id - render_id - element_id
		$input_id = $name . '-' . self::$render_ids[ $this->model->id ] . '-' . $element_id;

		// Print field markup
		$html = $this->radio_field_markup( $field, $input_id, $name );

		$html .= sprintf( '<label class="forminator-radio--design" aria-hidden="true" for="%s"></label><label class="forminator-radio--label" for="%s">%s</label>', $input_id, $input_id, $label );

		return apply_filters( 'forminator_field_markup', $html, $field, $this );
	}

	/**
	 * Radio field markup
	 *
	 * @since 1.0
	 *
	 * @param $field
	 * @param $id
	 * @param $name
	 *
	 * @return mixed
	 */
	public function radio_field_markup( $field, $id, $name ) {

		$required = Forminator_Field::get_property( 'required', $field, false );
		$value    = Forminator_Field::get_property( 'element_id', $field );
		$disabled = '';
		if ( ! $this->is_admin && ! $this->model->current_user_can_vote() ) {
			$disabled = 'disabled="disabled"';
		}

		$html = sprintf( '<input class="forminator-radio--field forminator-radio--input" id="%s" type="radio" data-required="%s" name="%s" value="%s" %s/>', $id, $required, $name, $value, $disabled );

		return apply_filters( 'forminator_field_radio_markup', $html, $id, $name, $required, $value );
	}

	/**
	 * Render extra field
	 *
	 * @since 1.0
	 *
	 * @param $field
	 * @param $uniq_id
	 *
	 * @return mixed
	 */
	public function render_extra_field( $field, $uniq_id ) {
		$extra = Forminator_Field::get_property( 'extra', $field );

		// Get field object
		$element_id = Forminator_Field::get_property( 'element_id', $field );
		$name       = $this->model->id;

		// form_id - render_id - element_id
		$input_id = $name . '-' . self::$render_ids[ $this->model->id ] . '-' . $element_id;


		$html = sprintf( '<input style="display:none" class="forminator-name--field forminator-input" type="text" name="%s" placeholder="%s" id="%s" />', $name . '-extra', $extra, $input_id . '-extra' );

		return apply_filters( 'forminator_field_textfield_extra_markup', $html, $name );
	}

	/**
	 * Return field columns
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return string
	 */
	public function get_cols( $field ) {
		if ( ! isset( $field['cols'] ) ) {
			return '12';
		}

		return $field['cols'];
	}

	/**
	 * Return field before markup
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function render_field_before( $field ) {
		$html = sprintf( '<li class="forminator-answer forminator-radio">' );

		return apply_filters( 'forminator_before_field_markup', $html );
	}

	/**
	 * Return field after markup
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function render_field_after( $field ) {
		$html = sprintf( '</li>' );

		return apply_filters( 'forminator_after_field_markup', $html, $field );
	}

	/**
	 * Return form type
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_form_type() {
		return 'poll';
	}

	/**
	 * Return form settings
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function get_form_settings() {
		return $this->model->settings;
	}

	/**
	 * Return form design
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function get_form_design() {
		$form_settings = $this->get_form_settings();

		if ( ! isset( $form_settings['form-style'] ) ) {
			return 'default';
		}

		return $form_settings['form-style'];
	}

	/**
	 * Results chart design
	 *
	 * @since 1.0
	 * @return string
	 */
	private function get_chart_design() {
		$form_settings = $this->get_form_settings();

		if ( ! isset( $form_settings['results-style'] ) ) {
			return 'bar';
		}

		return $form_settings['results-style'];
	}

	/**
	 * Results chart design
	 *
	 * @since 1.0
	 * @return string
	 */
	private function get_show_results() {
		$form_settings = $this->get_form_settings();

		if ( ! isset( $form_settings['results-behav'] ) ) {
			return 'link_on';
		}

		return $form_settings['results-behav'];
	}

	/**
	 * Ajax submit
	 * Check if the form is ajax submit
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function is_ajax_submit() {
		$form_settings = $this->get_form_settings();

		if ( ! isset( $form_settings['enable-ajax'] ) || empty( $form_settings['enable-ajax'] ) ) {
			return false;
		}

		return filter_var( $form_settings['enable-ajax'], FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Show results after poll submit
	 *
	 * @since 1.0
	 * @return bool
	 */
	private function show_results() {
		$show_results = $this->get_show_results();
		if ( 'show_after' === $show_results ) {
			return true;
		}

		return false;
	}

	/**
	 * Show link after submit
	 *
	 * @since 1.0
	 * @return bool
	 */
	private function show_link() {
		$show_results = $this->get_show_results();
		if ( 'link_on' === $show_results ) {
			return true;
		}

		return false;
	}

	/**
	 * Render success
	 *
	 * @since 1.0
	 * @return string
	 */
	public function render_success( $render = true ) {
		if ( is_object( $this->model ) ) {
			$post_id    = $this->get_post_id();
			$return_url = get_permalink( $post_id );
			$chart_container = 'forminator_chart_poll_' . uniqid() . '_' . $this->model->id;
			ob_start();
			?>
            <form class="forminator-poll forminator-poll-<?php echo esc_attr( $this->model->id ); ?>
            <?php echo $this->get_form_design_class(); // WPCS: XSS ok. ?>
            <?php echo $this->get_fields_type_class(); // WPCS: XSS ok. ?>
            <?php echo $this->form_extra_classes(); // WPCS: XSS ok. ?>" method="GET" action="<?php echo esc_url( $return_url ); ?>"
                  data-forminator-render="<?php echo esc_attr( self::$render_ids[ $this->model->id ] ); ?>">
				<?php echo $this->render_form_header(); // WPCS: XSS ok. ?>
                <div id="<?php echo esc_attr( $chart_container ); ?>" class="forminator-poll--chart" style="width: 100%; height: 300px;"></div>
                <div class="forminator-poll--actions">
                    <button class="forminator-button"><?php esc_html_e( 'Back To poll', Forminator::DOMAIN ); ?></button>
                </div>
            </form>
			<?php

            self::$graph_result_scripts[] = array(
                    'model' => $this->model,
                    'container' => $chart_container,
            );

			$html = ob_get_clean();

			if ( $render ) {
				echo apply_filters( 'forminator_render_form_success_markup', $html, $this->model ); // WPCS: XSS ok.
			} else {
				return apply_filters( 'forminator_render_form_success_markup', $html, $this->model );
			}
		}
	}

	public function graph_scripts() {
        foreach (self::$graph_result_scripts as $graph_script) {
            $this->success_footer_script($graph_script['model'], $graph_script['container']);
        }
    }

	/**
	 * Get Options for google chart
	 *
	 * @param $model
	 *
	 * @return array
	 */
	public static function get_default_chart_options( $model ) {
		$chart_colors     = apply_filters( 'forminator_poll_chart_color', self::$default_chart_colors );
		$chart_design     = 'bar';
		$pie_tooltip_text = 'percentage';
		$form_settings    = $model->settings;
		if ( isset( $form_settings['results-style'] ) ) {
			$chart_design = $form_settings['results-style'];
		}

		if ( isset( $form_settings['show-votes-count'] ) && $form_settings['show-votes-count'] ) {
			if ( 'pie' === $chart_design ) {
				$pie_tooltip_text = 'both';
			}
		}

		if ( 'pie' !== $chart_design ) {
			$chart_options = array(
				'annotations'     => array(
					'textStyle' => array(
						'fontSize' => 13,
						'bold'     => false,
						'color'    => '#333',
					),
				),
				'backgroundColor' => 'transparent',
				'fontSize'        => 13,
				'fontName'        => 'Roboto',
				'hAxis'           => array(
					'format'        => 'decimal',
					'baselineColor' => '#4D4D4D',
					'gridlines'     => array(
						'color' => '#E9E9E9',
					),
					'textStyle'     => array(
						'color'    => '#4D4D4D',
						'fontSize' => 13,
						'bold'     => false,
						'italic'   => false,
					),
					'minValue'      => 0,
				),
				'vAxis'           => array(
					'baselineColor' => '#4D4D4D',
					'gridlines'     => array(
						'color' => '#E9E9E9',
					),
					'textStyle'     => array(
						'color'    => '#4D4D4D',
						'fontSize' => 13,
						'bold'     => false,
						'italic'   => false,
					),
					'minValue'      => 0,
				),
				'tooltip'         => array(
					'isHtml'  => true,
					'trigger' => 'none',
				),
				'legend'          => array(
					'position' => 'none',
				),
			);
		} else {
			$chart_options = array(
				'colors'          => $chart_colors,
				'backgroundColor' => 'transparent',
				'fontSize'        => 13,
				'fontName'        => 'Roboto',
				'tooltip'         => array(
					'isHtml'  => false,
					'trigger' => 'focus',
					'text'    => $pie_tooltip_text,
				),
			);
		}

		return apply_filters( 'forminator_poll_chart_options', $chart_options, $model );


	}

	/**
	 * Success footer scripts
	 *
	 * @since 1.0
	 */
	public function success_footer_script( $model, $container_id ) {
		if ( ! is_object( $model ) ) {
			return '';
		}
		$form_settings = $model->settings;

		$chart_design = 'bar';
		if ( isset( $form_settings['results-style'] ) ) {
			$chart_design = $form_settings['results-style'];
		}

		$number_votes_enabled = false;
		if ( isset( $settings['show-votes-count'] ) && $form_settings['show-votes-count'] ) {
			$number_votes_enabled = true;
		}

		$chart_colors         = apply_filters( 'forminator_poll_chart_color', self::$default_chart_colors );
		$default_chart_colors = $chart_colors;
		?>
        <script type="text/javascript">
			(function ($, doc) {
				"use strict";
				jQuery('document').ready(function () {
					google.charts.load('current', {packages: ['corechart', 'bar']});
					google.charts.setOnLoadCallback(drawPollResults_<?php echo esc_attr( $container_id ); ?>);

					function drawPollResults_<?php echo esc_attr( $container_id ); ?>() {
						var data = google.visualization.arrayToDataTable([
							['<?php esc_html_e( 'Question', Forminator::DOMAIN ); ?>', '<?php esc_html_e( 'Results', Forminator::DOMAIN ); ?>', {role: 'style'}, {role: 'annotation'}],
							<?php
							$fields_array = $model->getFieldsAsArray();
							$map_entries = Forminator_Form_Entry_Model::map_polls_entries( $model->id, $fields_array );
							$fields = $model->getFields();
							if ( ! is_null( $fields ) ) {
								$html = '';
								foreach ( $fields as $field ) {
									$annotation = '';
									$label      = addslashes( $field->title );

									if ( empty( $chart_colors ) ) {
										$chart_colors = $default_chart_colors;
									}
									$color   = array_shift( $chart_colors );
									$slug    = isset( $field->slug ) ? $field->slug : sanitize_title( $label );
									$entries = 0;
									if ( in_array( $slug, array_keys( $map_entries ), true ) ) {
										$entries = $map_entries[ $slug ];
									}
									if ( $number_votes_enabled ) {
										$annotation = $entries . __( ' vote(s)', Forminator::DOMAIN );
									}
									$style = 'color: ' . $color;

									$html .= "['$label', $entries, '$style', '$annotation'],";
								}

								echo substr( $html, 0, - 1 ); // WPCS: XSS ok.
							}
							?>
						]);

						var options = <?php echo wp_json_encode( self::get_default_chart_options( $model ) ); ?>;

						<?php if ( 'pie' === $chart_design ) { ?>
						var chart = new google.visualization.PieChart(document.getElementById('<?php echo esc_attr( $container_id ); ?>'));
						<?php } else { ?>
						var chart = new google.visualization.BarChart(document.getElementById('<?php echo esc_attr( $container_id ); ?>'));
						<?php } ?>

						chart.draw(data, options);
					}
				});
			}(jQuery, document));
        </script>
		<?php
	}

	/**
	 * Return styles template path
	 *
	 * @since 1.0
	 * @return bool|string
	 */
	public function styles_template_path() {
		return realpath( forminator_plugin_dir() . '/assets/js/front/templates/poll-styles.html' );
	}

	/**
	 * Return if view votes setting is enabled
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function has_votes_enabled() {
		$settings = $this->get_form_settings();
		if ( isset( $settings['show-votes-count'] ) && $settings['show-votes-count'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Get Properties styles of each rendered forms
	 *
	 * @return array
	 */
	public function get_styles_properties() {
		$properties = array();
		if ( ! empty( self::$forms_properties ) ) {
			// avoid same custom style printed
			$style_rendered = array();
			foreach ( self::$forms_properties as $form_properties ) {
				if ( ! in_array( $form_properties['id'], $style_rendered, true ) ) {
					$properties[] = $form_properties;
				}
			}
		}

		return $properties;
	}

	/**
	 * Print poll styles
	 *
	 * @since 1.0
	 */
	public function print_styles() {
		$style_properties = $this->get_styles_properties();

		if ( ! empty( $style_properties ) ) {
			foreach ( $style_properties as $style_property ) {
				if ( ! isset( $style_property['settings'] ) || empty( $style_property['settings'] ) ) {
					continue;
				}
				$properties = $style_property['settings'];

				// If we don't have a formID use $model->id
				/** @var array $properties */
				if ( ! isset( $properties['formID'] ) ) {
					if ( ! isset( $style_property ['id'] ) ) {
						continue;
					}
					$properties['formID'] = $style_property['id'];
				}

				ob_start();
				/** @noinspection PhpIncludeInspection */
				include $this->styles_template_path();
				$styles         = ob_get_clean();
				$trimmed_styles = trim( $styles );

				if ( isset( $properties['formID'] ) && strlen( $trimmed_styles ) > 0 ) {
					?>
					<style type="text/css" id="forminator-poll-styles-<?php echo esc_attr( $properties['formID'] ); ?>">
						<?php echo $trimmed_styles;// wpcs XSS ok. unescaped expected for printed css. ?>
                    </style>
					<?php
				}
			}
		}
	}


	/**
	 * Initiate `forminatorFront` front javascript for rendered form(s)
	 *
	 * @since 1.0
	 */
	public function forminator_render_front_scripts() {
		?>
        <script type="text/javascript">
			jQuery(document).ready(function () {
				<?php
				if ( ! empty( self::$forms_properties ) ) {
				foreach ( self::$forms_properties as $form_properties ) {
				?>
				jQuery('#forminator-module-<?php echo esc_attr( $form_properties['id'] ); ?>[data-forminator-render="<?php echo esc_attr( $form_properties['render_id'] ); ?>"]').forminatorFront({
					form_type: '<?php echo $this->get_form_type(); // WPCS: XSS ok. ?>',
					chart_design: '<?php echo $form_properties['chart_design']; // WPCS: XSS ok. ?>',
					chart_options: <?php echo wp_json_encode($form_properties['chart_options']); ?>,
				});
				<?php
				}
				}
				?>
			});
        </script>
		<?php

	}
}