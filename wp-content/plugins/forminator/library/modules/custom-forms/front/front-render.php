<?php

/**
 * Front render class for custom forms
 *
 * @since 1.0
 */
class Forminator_CForm_Front extends Forminator_Render_Form {

	/**
	 * Class instance
	 *
	 * @var Forminator_Render_Form|null
	 */
	private static $instance = null;

	/**
	 * @var null
	 */
	private static $paypal = null;

	/**
	 * @var array
	 */
	private static $paypal_forms = array();

	/**
	 * @var string
	 */
	private $inline_rules = '';

	/**
	 * @var string
	 */
	private $inline_messages = '';

	/**
	 * @var array
	 */
	private static $forms_properties = array();


	/**
	 * Initialize method
	 *
	 * @since 1.0
	 */
	public function init() {
		add_shortcode( 'forminator_form', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Return class instance
	 *
	 * @since 1.0
	 * @return Forminator_CForm_Front
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Display form method
	 *
	 * @since 1.0
	 *
	 * @param      $id
	 * @param bool $ajax
	 * @param bool $data
	 */
	public function display( $id, $ajax = false, $data = false ) {

		if ( $data && ! empty( $data ) ) {
			$this->model = Forminator_Custom_Form_Model::model()->load_preview( $id, $data );
		} else {
			$this->model = Forminator_Custom_Form_Model::model()->load( $id );
		}

		if ( is_object( $this->model ) ) {
			$this->generate_render_id( $id );

			if ( $this->model->form_is_visible() ) {

				add_filter( 'forminator_render_form_submit_markup', array( $this, 'render_honeypot_field' ), 10, 4 );

				// Render form
				$this->render( $id );

				self::$forms_properties[] = array(
					'id'                  => $id,
					'render_id'           => self::$render_ids[ $id ],
					'inline_validation'   => $this->has_inline_validation() ? 'true' : 'false',
					'conditions'          => $this->get_conditions(),
					'validation_rules'    => $this->inline_rules,
					'validation_messages' => $this->inline_messages,
					'settings'            => $this->get_form_settings(),
					'pagination'          => $this->get_pagination_properties(),
				);

				// Enqueue form scripts
				$this->enqueue_form_scripts( $ajax );

				if ( $ajax ) {
					$this->print_styles();
				} else {
					add_action( 'wp_footer', array( $this, 'print_styles' ), 9999 );
				}
				if ( $this->is_admin ) {
					add_action( 'forminator_before_form_render', array( $this, 'print_styles' ) );
				}
			}
		}
	}

	/**
	 * Header message to handle error message
	 *
	 * @since 1.0
	 */
	public function render_form_header() {
		//if rendered on Preview, the array is empty and sometimes PHP notices show up
		if ( $this->is_admin && !isset(self::$render_ids[ $this->model->id ]) ) {
			self::$render_ids[ $this->model->id ] = 0;
		}
		$content = '<div class="forminator-cform-response-message">';
		ob_start();
		do_action( 'forminator_cform_post_message', $this->model->id, self::$render_ids[ $this->model->id ] ); //prints html, so we need to capture this
		$content .= ob_get_clean();
		$content .= '</div>';

		return $content;
	}

	/**
	 * Enqueue form scripts
	 *
	 * @since 1.0
	 *
	 * @param $ajax
	 */
	public function enqueue_form_scripts( $ajax ) {
		if ( ! $ajax ) {
			forminator_print_front_styles( FORMINATOR_VERSION );
			forminator_print_front_scripts( FORMINATOR_VERSION );
		}

		// Load reCaptcha scripts
		if ( $this->has_captcha() ) {
			$language = get_option( "forminator_captcha_language", "en" );
			wp_enqueue_script( 'forminator-google-recaptcha',
			                   'https://www.google.com/recaptcha/api.js?hl=' . $language . '&onload=forminator_render_captcha&render=explicit',
			                   array( 'jquery' ),
			                   FORMINATOR_VERSION,
			                   true );
		}

		// load date picker scripts
		if ( $this->has_date() ) {
			wp_enqueue_script( 'jquery-ui-datepicker' );
		}

		// Load selected google font
		if ( $this->has_google_font() ) {
			$font = $this->get_google_font();
			wp_enqueue_style( $font, 'https://fonts.googleapis.com/css?family=' . $font );
		}

		// Load PayPal scripts if required
		if ( ! empty( self::$paypal_forms ) ) {
			add_action( 'wp_footer', array( $this, 'print_paypal_scripts' ), 9999 );
		}

		//Load Front Render Scripts
		//render front script of form front end initialization
		add_action( 'wp_footer', array( $this, 'forminator_render_front_scripts' ), 9999 );
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

		$view->display( $atts['id'] );

		return ob_get_clean();
	}

	/**
	 * Return Form ID required message
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function message_required() {
		return __( "Form ID attribute is required!", Forminator::DOMAIN );
	}

	/**
	 * Return From ID not found message
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function message_not_found() {
		return __( "Form ID not found!", Forminator::DOMAIN );
	}

	/**
	 * Return form wrappers & fields
	 *
	 * @since 1.0
	 * @return array|mixed
	 */
	public function get_wrappers() {
		if ( is_object( $this->model ) ) {
			return $this->model->getFieldsGrouped();
		} else {
			return $this->message_not_found();
		}
	}

	/**
	 * Return form wrappers & fields
	 *
	 * @since 1.0
	 * @return array|mixed
	 */
	public function get_fields() {
		$fields   = array();
		$wrappers = $this->get_wrappers();

		// Fallback
		if ( empty( $wrappers ) ) {
			return $fields;
		}

		foreach ( $wrappers as $key => $wrapper ) {

			if ( ! isset( $wrapper['fields'] ) ) {
				return array();
			}

			foreach ( $wrapper['fields'] as $k => $field ) {
				$fields[] = $field;
			}
		}

		return $fields;
	}

	/**
	 * Return before wrapper markup
	 *
	 * @since 1.0
	 *
	 * @param $wrapper
	 *
	 * @return mixed
	 */
	public function render_wrapper_before( $wrapper ) {
		$html = '<div class="forminator-row">';

		return apply_filters( 'forminator_before_wrapper_markup', $html, $wrapper );
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
		$html = '</div>';

		return apply_filters( 'forminator_after_wrapper_markup', $html, $wrapper );
	}

	/**
	 * Extra form classes for ajax
	 *
	 * @since 1.0
	 */
	public function form_extra_classes() {
		$ajax_form = $this->is_ajax_submit();

		return $ajax_form ? 'forminator_ajax' : '';
	}

	/**
	 * Return fields markup
	 *
	 * @since 1.0
	 *
	 * @param bool $render
	 *
	 * @return string|void
	 */
	public function render_fields( $render = true ) {
		$html = '';
		$step = 1;

		$wrappers = $this->get_wrappers();

		$html .= $this->do_before_render_form_fields_for_addons();

		// Check if we have pagination field
		if ( $this->has_pagination() ) {
			$html .= $this->pagination_start();
			$html .= $this->pagination_header();
			$html .= $this->pagination_content_start();
		}

		if ( ! empty( $wrappers ) ) {
			foreach ( $wrappers as $key => $wrapper ) {

				//a wrapper with no fields, continue to next wrapper
				if ( ! isset( $wrapper['fields'] ) ) {
					continue;
				}

				$has_pagination = false;

				// Skip row markup if pagination field
				if ( ! $this->is_pagination_row( $wrapper ) ) {
					// Render before wrapper markup
					$html .= $this->render_wrapper_before( $wrapper );
				}

				foreach ( $wrapper['fields'] as $k => $field ) {
					if ( $this->is_pagination( $field ) ) {
						$has_pagination = true;
					}

					// Skip row markup if pagination field
					if ( ! $this->is_pagination_row( $wrapper ) ) {
						$html .= $this->get_field( $field );
					}
				}

				// Skip row markup if pagination field
				if ( ! $this->is_pagination_row( $wrapper ) ) {
					// Render after wrapper markup
					$html .= $this->render_wrapper_after( $wrapper );
				}

				if ( $has_pagination ) {
					$html .= $this->pagination_content_end();
					if ( isset( $field ) ) {
						$html .= $this->pagination_step( $step, $field );
					}
					$html .= $this->pagination_header();
					$html .= $this->pagination_content_start();
					$step ++;
				}
			}
		}

		// Check if we have pagination field
		if ( $this->has_pagination() ) {
			$html .= $this->pagination_content_end();
			$html .= $this->pagination_submit_button();
			$html .= $this->pagination_end();
		}

		$html .= $this->do_after_render_form_fields_for_addons();

		if ( $render ) {
			echo $html;// wpcs XSS ok. unescaped html output expected
		} else {
			/** @noinspection PhpInconsistentReturnPointsInspection */
			return apply_filters( 'forminator_render_fields_markup', $html, $wrappers );
		}
	}

	/**
	 * Return if the row is pagination
	 *
	 * @since 1.0
	 *
	 * @param $wrapper
	 *
	 * @return bool
	 */
	public function is_pagination_row( $wrapper ) {
		$is_single = $this->is_single_field( $wrapper );

		if ( $is_single && isset( $wrapper['fields'][0]['type'] ) && "pagination" === $wrapper['fields'][0]['type'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Return if only single field in the wrapper
	 *
	 * @since 1.0
	 *
	 * @param $wrapper
	 *
	 * @return bool
	 */
	public function is_single_field( $wrapper ) {
		if ( isset( $wrapper['fields'] ) && ( count( $wrapper['fields'] ) === 1 ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Return pagination header
	 *
	 * @since 1.0
	 * @return string
	 */
	public function pagination_header() {

		$type           = $this->get_pagination_type();
		$has_pagination = $this->has_pagination_header();

		if ( ! $has_pagination ) {
			return '';
		}

		if ( 'bar' === $type ) {

			$html = '<div class="forminator-pagination--bar"></div>';

		} else {

			$html = '<ol class="forminator-pagination--nav"></ol>';

		}

		return apply_filters( 'forminator_pagination_header_markup', $html );

	}

	/**
	 * Return pagination start markup
	 *
	 * @since 1.0
	 * @return string
	 */
	public function pagination_start() {

		$form_settings = $this->get_form_settings();
		$label         = __( "Finish", Forminator::DOMAIN );

		if ( isset( $form_settings['pagination-step-label'] ) ) {
			$label = $form_settings['pagination-step-label'];
		}

		$html = sprintf( '<div class="forminator-pagination forminator-pagination-start" data-step="0" data-label="%s">', $label );

		return apply_filters( 'forminator_pagination_start_markup', $html, $label );

	}


	/**
	 * Get Pagination Properties as array
	 *
	 * @since 1.1
	 *
	 *
	 * @return array
	 */
	public function get_pagination_properties() {

		$form_settings = $this->get_form_settings();

		$properties = array(
			'has-pagination'                => $this->has_pagination(),
			'pagination-header-design'      => 'no-pagination',
			'pagination-step-label'         => __( "Finish", Forminator::DOMAIN ),
			'pagination-footer-button'      => false,
			'pagination-footer-button-text' => __( "Back", Forminator::DOMAIN ),
			'pagination-right-button'       => false,
			'pagination-right-button-text'  => __( "Next", Forminator::DOMAIN ),
		);

		foreach ( $properties as $property => $value ) {
			if ( isset( $form_settings[ $property ] ) ) {
				$new_value = $form_settings[ $property ];
				if ( is_bool( $value ) ) {
					// return boolean
					$new_value = filter_var( $new_value, FILTER_VALIDATE_BOOLEAN );
				} elseif ( is_string( $new_value ) ) {
					// if empty string fallback to default
					if ( empty( $new_value ) ) {
						$new_value = $value;
					}
				}
				$properties[ $property ] = $new_value;
			}
		}

		$form_id = $this->model->id;

		/**
		 * Filter pagination properties
		 *
		 * @since 1.1
		 *
		 * @param array $properties
		 * @param int   $form_id Current Form ID
		 */
		$properties = apply_filters( 'forminator_pagination_properties', $properties, $form_id );

		return $properties;

	}

	/**
	 * Return pagination content start markup
	 *
	 * @since 1.0
	 * @return string
	 */
	public function pagination_content_start() {
		$html = '<div class="forminator-pagination--content">';

		return apply_filters( 'forminator_pagination_content_start_markup', $html );
	}

	/**
	 * Return pagination content end markup
	 *
	 * @since 1.0
	 * @return string
	 */
	public function pagination_content_end() {
		$html = '</div>';

		return apply_filters( 'forminator_pagination_content_end_markup', $html );
	}

	/**
	 * Return pagination submit button markup
	 *
	 * @since 1.0
	 * @return string
	 */
	public function pagination_submit_button() {
		$button = $this->get_submit_button_text();
		//hide submit button on markup, use it later

		if ( $this->get_form_design() !== 'material' ) {

			$html = sprintf( '<button class="forminator-button forminator-pagination-submit" style="display: none;" disabled>%s</button>', $button );
		} else {
			$html
				= sprintf( '<button class="forminator-button forminator-pagination-submit" style="display: none;" disabled><span class="forminator-button--mask" aria-label="hidden"></span><span class="forminator-button--text">%s</span></button>',
				           $button );
		}

		return apply_filters( 'forminator_pagination_submit_markup', $html );
	}

	/**
	 * Return pagination end markup
	 *
	 * @since 1.0
	 * @return string
	 */
	public function pagination_end() {
		$html = '</div>';

		return apply_filters( 'forminator_pagination_end_markup', $html );
	}

	/**
	 * Return pagination start markup
	 *
	 * @since 1.0
	 *
	 * @param $step
	 * @param $field
	 *
	 * @return string
	 */
	public function pagination_step( $step, $field ) {
		$label = sprintf( '%s %s', __( "Step", Forminator::DOMAIN ), $step );
		if ( isset( $field['pagination-label'] ) ) {
			$label = $field['pagination-label'];
		}

		$html = sprintf( '</div><div class="forminator-pagination" data-step="%s" data-label="%s">', $step, $label );

		return apply_filters( 'forminator_pagination_step_markup', $html, $step, $label );
	}

	/**
	 * Return field markup
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function get_field( $field ) {
		$html = '';

		do_action( 'forminator_before_field_render', $field );

		// Get field object
		/** @var Forminator_Field $field_object */
		$field_object = forminator_get_field( $this->get_field_type( $field ) );

		if ( $field_object->is_available( $field ) ) {
			if ( ! $this->is_hidden( $field ) ) {
				// Render before field markup
				$html .= $this->render_field_before( $field );
			}

			// Render field
			$html .= $this->render_field( $field );

			if ( ! $this->is_hidden( $field ) ) {
				// Render after field markup
				$html .= $this->render_field_after( $field );
			}
		}

		do_action( 'forminator_after_field_render', $field );

		return $html;
	}

	/**
	 * Return field markup
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function render_field( $field ) {
		$html            = '';
		$type            = $this->get_field_type( $field );
		$field_label     = $this->get_field_label( $field );
		$placeholder     = $this->get_placeholder( $field );
		$is_required     = $this->is_required( $field );
		$has_placeholder = $placeholder ? true : false;

		if ( ! $this->is_hidden( $field ) && ! $this->has_label( $field ) ) {

			if ( ! $this->is_multi_name( $field ) ) {
				$html .= $this->get_field_label_markup( $field_label, $is_required, $has_placeholder, $field );
			}

			// If field labels are empty
			if ( ! $field_label ) {
				if ( $is_required ) {
					$html .= $this->get_field_label_markup( '', true, true, $field );
				}
			}
		}

		// Get field object
		/** @var Forminator_Field $field_object */
		$field_object = forminator_get_field( $type );

		// Print field markup
		$html .= $field_object->markup( $field, $this->model->settings );

		$this->inline_rules    .= $field_object->get_validation_rules();
		$this->inline_messages .= $field_object->get_validation_messages();

		// Print field description
		$html .= $this->get_description( $field );

		return apply_filters( 'forminator_field_markup', $html, $field, $this );
	}

	/**
	 * Return field ID
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return string
	 */
	public function get_id( $field ) {
		if ( ! isset( $field['element_id'] ) ) {
			return '';
		}

		return $field['element_id'];
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
	 * Return if field is required
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return bool
	 */
	public function is_required( $field ) {

	$required = Forminator_Field::get_property( 'required', $field, false );
			$required = filter_var( $required , FILTER_VALIDATE_BOOLEAN );

			return $required;
	}

	/**
	 * Return field type
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function get_field_type( $field ) {
		if ( ! isset( $field['type'] ) ) {
			return false;
		}

		return $field['type'];
	}

	/**
	 * Return placeholder
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function get_placeholder( $field ) {
		if ( ! isset( $field['placeholder'] ) ) {
			return '';
		}

		return $this->sanitize_output( $field['placeholder'] );
	}

	/**
	 * Return field label
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function get_field_label( $field ) {
		if ( ! isset( $field['field_label'] ) ) {
			return '';
		}

		return $this->sanitize_output( $field['field_label'] );
	}

	/**
	 * Return field label markup
	 *
	 * @since 1.0
	 *
	 * @param $label
	 * @param $required
	 * @param $placeholder
	 * @param $field
	 *
	 * @return mixed
	 */
	public function get_field_label_markup( $label, $required, $placeholder, $field ) {
		// Skip markup if label missing
		if ( empty( $label ) ) {
			return '';
		}

		$container_class = 'forminator-field--label';
		$type            = $this->get_field_type( $field );
		/** @var Forminator_Field $field_object */
		$field_object = forminator_get_field( $type );
		$design       = $this->get_form_design();

		if ( $required ) {
			$asterisk = ' ' . forminator_get_required_icon();
		} else {
			$asterisk = '';
		}

		$html = sprintf( '<div class="%s">', $container_class );
		$html .= sprintf( '<label class="forminator-label" id="%s">%s%s</label>', 'forminator-label-' . $field['element_id'], $label, $asterisk );
		$html .= sprintf( '</div>' );

		return apply_filters( 'forminator_field_get_field_label', $html, $label );
	}

	/**
	 * Return description markup
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function get_description( $field ) {
		$type = $this->get_field_type( $field );
		/** @var Forminator_Field $field_object */
		$field_object              = forminator_get_field( $type );
		$has_phone_character_limit = ( ( isset( $field['phone_validation'] ) && $field['phone_validation'] )
		                               && ( isset( $field['phone_validation_type'] )
		                                    && 'character_limit' === $field['phone_validation_type'] ) );

		if ( ( isset( $field['description'] ) && ! empty( $field['description'] ) ) || isset( $field['text_limit'] ) || $has_phone_character_limit ) {

			$html = sprintf( '<div class="forminator-field--helper">' );

			if ( isset( $field['description'] ) && ! empty( $field['description'] ) ) {
				$description = $this->sanitize_output( $field['description'] );
				if ( "false" === $description ) {
					$description = '';
				}
				$html .= sprintf( '<label class="forminator-label--helper">%s</label>', $description );
			}

			if ( ( isset( $field['text_limit'] ) || isset( $field['phone_limit'] ) ) && isset( $field['limit'] ) && $field_object->has_counter || $has_phone_character_limit ) {
				if ( ( isset( $field['text_limit'] ) && $field['text_limit'] ) || ( isset( $field['phone_limit'] ) && $field['phone_limit'] ) || $has_phone_character_limit ) {
					$limit      = isset( $field['limit'] ) ? $field['limit'] : '';
					$limit_type = isset( $field['limit_type'] ) ? $field['limit_type'] : '';
					$html       .= sprintf( '<label class="forminator-label--limit" data-limit="%s" data-type="%s">0 / %s</label>', $limit, $limit_type, $limit );
				}
			}

			$html .= sprintf( '</div>' );
		} else {
			$html = '';
		}

		return apply_filters( 'forminator_field_get_description', $html, $field );
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
		$class = $this->get_classes( $field );
		$cols  = $this->get_cols( $field );
		$id    = $this->get_id( $field );

		$html = sprintf( '<div id="%s" class="forminator-col forminator-col-%s"><div class="%s">', $id, $cols, $class );

		return apply_filters( 'forminator_before_field_markup', $html, $class );
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
		$html = sprintf( '</div></div>' );

		return apply_filters( 'forminator_after_field_markup', $html, $field );
	}

	/**
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_form_type() {
		return 'custom-form';
	}

	/**
	 * Return Form Settins
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function get_form_settings() {
		// If not using the new "submission-behaviour" setting, set it according to the previous settings
		if ( ! isset( $this->model->settings['submission-behaviour'] ) ) {
			$redirect = ( isset( $this->model->settings['redirect'] ) && 'true' === $this->model->settings['redirect'] );
			$thankyou = ( isset( $this->model->settings['thankyou'] ) && 'true' === $this->model->settings['thankyou'] );

			if( $thankyou || ( ! $thankyou && ! $redirect ) ){
				$this->model->settings['submission-behaviour'] = 'behaviour-thankyou';
			} elseif( $redirect ){
				$this->model->settings['submission-behaviour'] = 'behaviour-redirect';
			}
		}
		return $this->model->settings;
	}

	/**
	 * Return if hidden field
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function is_hidden( $field ) {
		// Array of hidden fields
		$hidden = apply_filters( 'forminator_cform_hidden_fields', array( 'hidden' ) );

		if ( in_array( $field['type'], $hidden, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Return if name field
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function is_multi_name( $field ) {
		// Array of hidden fields
		$hidden = apply_filters( 'forminator_cform_hidden_fields', array( 'name' ) );

		if ( ( in_array( $field['type'], $hidden, true ) ) && ( isset( $field['multiple_name'] ) && $field['multiple_name'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Return if field has label
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function has_label( $field ) {
		// Array of hidden fields
		$without_label = apply_filters( 'forminator_cform_fields_without_label', array( '' ) );

		if ( in_array( $field['type'], $without_label, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Return Form Design
	 *
	 * @since 1.0
	 * @return mixed|string
	 */
	public function get_form_design() {
		$form_settings = $this->get_form_settings();

		if ( ! isset( $form_settings['form-style'] ) ) {
			return 'default';
		}

		return $form_settings['form-style'];
	}

	/**
	 * Return fields style
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function get_fields_style() {
		$form_settings = $this->get_form_settings();

		if ( ! isset( $form_settings['fields-style'] ) ) {
			return 'open';
		}

		return $form_settings['fields-style'];
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
	 * Check if honeypot protection is enabled
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function is_honeypot_enabled() {
		$form_settings = $this->get_form_settings();

		if ( ! isset( $form_settings['honeypot'] ) ) {
			return false;
		}

		return filter_var( $form_settings['honeypot'], FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Check if form has a captcha field
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function has_captcha() {
		$fields = $this->get_fields();

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				if ( "captcha" === $field["type"] ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if form has a date field
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function has_date() {
		$fields = $this->get_fields();

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				if ( "date" === $field["type"] ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if form has a date field
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function has_upload() {
		$fields = $this->get_fields();

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				if ( "upload" === $field["type"] || "postdata" === $field["type"] ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if form has a pagination field
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function has_pagination() {
		$fields = $this->get_fields();

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				if ( "pagination" === $field["type"] ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Return if field is pagination
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return bool
	 */
	public function is_pagination( $field ) {
		if ( isset( $field["type"] ) && "pagination" === $field["type"] ) {
			return true;
		}

		return false;
	}

	/**
	 * Return field classes
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return string
	 */
	public function get_classes( $field ) {

		$class = 'forminator-field';

		if ( isset( $field['custom-class'] ) && ! empty( $field['custom-class'] ) ) {
			$class .= ' ' . $field['custom-class'];
		}

		return $class;
	}

	/**
	 * Return fields conditions for JS
	 *
	 * @since 1.0
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	public function get_relations( $id ) {
		$relations = array();
		$fields    = $this->get_fields();

		// Fallback
		if ( empty( $fields ) ) {
			return $relations;
		}

		foreach ( $fields as $field ) {
			if ( $this->is_conditional( $field ) ) {
				$field_conditions = isset( $field['conditions'] ) ? $field['conditions'] : array();

				foreach ( $field_conditions as $condition ) {
					if ( $id === $condition['element_id'] ) {
						$relations[] = $this->get_field_id( $field );
					}
				}
			}
		}

		return $relations;
	}

	/**
	 * Return fields conditions for JS
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function get_conditions() {
		$conditions = array();
		$relations  = array();
		$fields     = $this->get_fields();

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				$id               = $this->get_field_id( $field );
				$relations[ $id ] = $this->get_relations( $id );

				// Check if conditions are enabled
				if ( $this->is_conditional( $field ) ) {
					$field_data       = array();
					$condition_action = isset( $field['condition_action'] ) ? $field['condition_action'] : 'show';
					$condition_rule   = isset( $field['condition_rule'] ) ? $field['condition_rule'] : 'any';
					$field_conditions = isset( $field['conditions'] ) ? $field['conditions'] : array();

					foreach ( $field_conditions as $condition ) {
						$new_condition = array(
							'field'    => $condition['element_id'],
							'operator' => $condition['rule'],
							'value'    => $condition['value'],
						);

						$field_data[] = $new_condition;
					}

					$conditions[ $id ] = array(
						"action"     => $condition_action,
						"rule"       => $condition_rule,
						"conditions" => $field_data,
					);
				}
			}
		}

		return array(
			'fields'    => $conditions,
			'relations' => $relations,
		);
	}

	/**
	 * Check field is conditional
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return bool
	 */
	public function is_conditional( $field ) {
		if ( isset( $field['use_conditions'] ) && $field['use_conditions'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Set the form encryption type if there is an upload
	 *
	 * @since 1.0
	 * @return string
	 */
	public function form_enctype() {
		if ( $this->has_upload() ) {
			return 'enctype="multipart/form-data"';
		} else {
			return '';
		}
	}

	/**
	 * @since 1.0
	 * @return bool
	 */
	public function has_paypal() {
		$is_enabled = forminator_has_paypal_settings();
		$selling    = 0;
		$fields     = $this->get_fields();

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				if ( "product" === $field["type"] && ( ! isset( $field["product_free"] ) ) ) {
					$selling ++;
				}
			}
		}

		return ( $is_enabled && $selling > 0 ) ? true : false;
	}

	/**
	 * PayPal button markup
	 *
	 * @since 1.0
	 *
	 * @param $form_id
	 *
	 * @return mixed
	 */
	public function get_paypal_button_markup( $form_id ) {
		$html = '<div class="forminator-row">';
		$html .= '<div class="forminator-col forminator-col-12">';
		$html .= '<div class="forminator-field" id="paypal-button-container-' . $form_id . '">';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';

		return apply_filters( 'forminator_render_button_markup', $html );
	}

	/**
	 * Return form submit button markup
	 *
	 * @since 1.0
	 *
	 * @param        $form_id
	 * @param bool   $render
	 *
	 * @return mixed|void
	 */
	public function get_submit( $form_id, $render = true ) {
		$html       = '';
		$nonce      = wp_nonce_field( 'forminator_submit_form', 'forminator_nonce', true, false );
		$post_id    = $this->get_post_id();
		$has_paypal = $this->has_paypal();

		if ( $has_paypal ) {
			if ( ! ( self::$paypal instanceof Forminator_Paypal_Express ) ) {
				self::$paypal = new Forminator_Paypal_Express();
			}
			self::$paypal_forms[] = $form_id;
		}

		// If we have pagination skip button markup
		if ( ! $this->has_pagination() ) {
			if ( ! $has_paypal ) {
				$html .= $this->get_button_markup();
			} else {
				$html .= '<input type="hidden" name="payment_gateway_total" value="" />';
				$html .= $this->get_paypal_button_markup( $form_id );
			}
		}

		$html .= $nonce;
		$html .= sprintf( '<input type="hidden" name="form_id" value="%s">', $form_id );
		$html .= sprintf( '<input type="hidden" name="page_id" value="%s">', $post_id );
		$html .= sprintf( '<input type="hidden" name="current_url" value="%s">', forminator_get_current_url() );
		if ( isset( self::$render_ids[ $form_id ] ) ) {
			$html .= sprintf( '<input type="hidden" name="render_id" value="%s">', self::$render_ids[ $form_id ] );
		}
		$html .= sprintf( '<input type="hidden" name="action" value="%s">', "forminator_submit_form_custom-forms" );

		$html .= $this->do_after_render_form_for_addons();

		if ( $render ) {
			echo apply_filters( 'forminator_render_form_submit_markup', $html, $form_id, $post_id, $nonce ); // wpcs XSS ok. unescaped html output expected
		} else {
			/** @noinspection PhpInconsistentReturnPointsInspection */
			return apply_filters( 'forminator_render_form_submit_markup', $html, $form_id, $post_id, $nonce );
		}
	}

	/**
	 * Submit button text
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_submit_button_text() {
		if ( $this->has_custom_submit_text() ) {
			return $this->get_custom_submit_text();
		} else {
			return __( "Submit", Forminator::DOMAIN );
		}
	}

	/**
	 * Return custom submit button text
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_custom_submit_text() {
		$settings = $this->get_form_settings();

		return $this->sanitize_output( $settings['custom-submit-text'] );
	}

	/**
	 * Return if custom submit button text
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function has_custom_submit_text() {
		$settings = $this->get_form_settings();

		if ( isset( $settings['use-custom-submit'] ) && isset( $settings['custom-submit-text'] ) && ! empty( $settings['use-custom-submit'] ) && ! empty( $settings['custom-submit-text'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Render honeypot field
	 *
	 * @since 1.0
	 *
	 * @param string $html    - the button html
	 * @param int    $form_id - the current form id
	 * @param int    $post_id - the current post id
	 * @param string $nonce   - the nonce field
	 *
	 * @return string $html
	 */
	public function render_honeypot_field(
		$html,
		$form_id,
		/** @noinspection PhpUnusedParameterInspection */
		$post_id,
		/** @noinspection PhpUnusedParameterInspection */
		$nonce
	) {
		if ( $form_id === $this->model->id && $this->is_honeypot_enabled() ) {
			$fields       = $this->get_fields();
			$total_fields = count( $fields ) + 1;
			//Most bots wont bother with hidden fields, so set to text and hide it
			$html .= sprintf( '<input type="text" style="display:none !important; visibility:hidden !important;" name="%s" value="">', "input_$total_fields" );
		}

		return $html;
	}

	/**
	 * Return styles template path
	 *
	 * @since 1.0
	 * @return bool|string
	 */
	public function styles_template_path() {
		return realpath( forminator_plugin_dir() . '/assets/js/front/templates/custom-form-styles.html' );
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
	 * Return font specific front-end styles
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
				if ( ! isset( $properties['font-family'] ) ) {
					$properties['font-family'] = 'custom';
				}

				if ( isset( $properties['custom_css'] ) && isset( $properties['formID'] ) ) {
					$properties['custom_css'] = forminator_prepare_css( $properties['custom_css'], '.forminator-custom-form-' . $properties['formID'] . '', false, true, 'forminator-custom-form' );
				}

				/** @noinspection PhpIncludeInspection */
				include $this->styles_template_path();
				$styles         = ob_get_clean();
				$trimmed_styles = trim( $styles );

				if ( isset( $properties['formID'] ) && strlen( $trimmed_styles ) > 0 ) {
					?>
                    <style type="text/css" id="forminator-custom-form-styles-<?php echo esc_attr( $properties['formID'] ); ?>">
	                    <?php echo esc_html( $trimmed_styles ); ?>
                    </style>

					<?php
				}
			}
		}

	}

	/**
	 * Return if form pagination has header
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function has_pagination_header() {
		if ( "nav" === $this->get_pagination_type() || "bar" === $this->get_pagination_type() ) {
			return true;
		}

		return false;
	}

	/**
	 * Get pagination type
	 *
	 * @since 1.1
	 * @return string
	 */
	public function get_pagination_type() {
		$settings = $this->get_form_settings();

		if ( ! isset( $settings['pagination-header-design'] ) ) return 'no-pagination';

		return $settings['pagination-header-design'];
	}

	/**
	 * Prints Javascript required for each form with PayPal
	 *
	 * @since 1.0
	 */
	public function print_paypal_scripts() {
		foreach ( self::$paypal_forms as $paypal_form_id ) {
			/** @noinspection PhpUndefinedMethodInspection */
			self::$paypal->render_buttons_script( $paypal_form_id );
		}
	}

	/**
	 * Defines translatable strings to pass to datepicker
	 * Add other strings if required
	 *
	 * @since 1.0.5
	 */
	public function get_strings_for_calendar() {
		$days = array(
			esc_html__( 'Su', Forminator::DOMAIN ),
			esc_html__( 'Mo', Forminator::DOMAIN ),
			esc_html__( 'Tu', Forminator::DOMAIN ),
			esc_html__( 'We', Forminator::DOMAIN ),
			esc_html__( 'Th', Forminator::DOMAIN ),
			esc_html__( 'Fr', Forminator::DOMAIN ),
			esc_html__( 'Sa', Forminator::DOMAIN ),
		);

		return '"' . implode( '","', $days ) . '"';
	}

	/**
	 * Return if form use google font
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function has_google_font() {
		$settings = $this->get_form_settings();

		// Check if custom font enabled
		if ( ! isset( $settings['use-fonts-settings'] ) || empty( $settings['use-fonts-settings'] ) ) {
			return false;
		}

		// Check if custom font
		if ( ! isset( $settings['font-family'] ) || empty( $settings['font-family'] ) || "custom" === $settings['font-family'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Return google font
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_google_font() {
		$settings = $this->get_form_settings();

		return $settings['font-family'];
	}

	/**
	 * Return if form use inline validation
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function has_inline_validation() {
		$settings = $this->get_form_settings();

		if ( ! isset( $settings['validation-inline'] ) || ! $settings['validation-inline'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Render Front Script
	 *
	 * @since 1.0
	 * @since 1.1 add pagination properties on `window`
	 */
	public function forminator_render_front_scripts() {
		?>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				window.Forminator_Cform_Paginations = [];
				<?php
				if ( ! empty( self::$forms_properties ) ) {
				foreach ( self::$forms_properties as $form_properties ) {
				?>
				window.Forminator_Cform_Paginations[<?php echo esc_attr( $form_properties['id'] ); ?>] =
				<?php echo wp_json_encode( $form_properties['pagination'] ); ?>;
				jQuery('#forminator-module-<?php echo esc_attr( $form_properties['id'] ); ?>[data-forminator-render="<?php echo esc_attr( $form_properties['render_id'] ); ?>"]').forminatorFront({
					form_type        : '<?php echo $this->get_form_type(); // wpcs XSS ok. unescaped html output expected ?>',
					inline_validation: <?php echo $form_properties['inline_validation']; // wpcs XSS ok. unescaped html output expected ?>,
					rules            : {<?php echo $form_properties['validation_rules']; // wpcs XSS ok. unescaped html output expected ?>},
					messages         : {<?php echo $form_properties['validation_messages']; // wpcs XSS ok. unescaped html output expected ?>},
					conditions       : <?php echo wp_json_encode( $form_properties['conditions'] ); // wpcs XSS ok. unescaped html output expected ?>,
					calendar         : [ <?php echo $this->get_strings_for_calendar(); // wpcs XSS ok. unescaped html output expected ?> ],
				});
				<?php
				}
				}
				?>
				if (typeof ForminatorValidationErrors !== 'undefined') {
					var selector              = ForminatorValidationErrors.selector,
					    errors                = ForminatorValidationErrors.errors,
					    forminatorFrontSubmit = jQuery(selector).data('forminatorFrontSubmit');
					if (typeof forminatorFrontSubmit !== 'undefined') {
						forminatorFrontSubmit.show_messages(errors);
					}
				}
			});
		</script>
		<?php

	}

	/**
	 * Get Output of addons after_render_form
	 * @see Forminator_Addon_Zapier_Form_Hooks::on_after_render_form()
	 *
	 * @since 1.1
	 * @return string
	 */
	public function do_after_render_form_for_addons() {
		//find is_form_connected
		$connected_addons = forminator_get_addons_instance_connected_with_form( $this->model->id );

		ob_start();
		foreach ( $connected_addons as $connected_addon ) {
			try {
				$form_hooks = $connected_addon->get_addon_form_hooks( $this->model->id );
				if ( $form_hooks instanceof Forminator_Addon_Form_Hooks_Abstract ) {
					$form_hooks->on_after_render_form();
				}
			} catch ( Exception $e ) {
				forminator_addon_maybe_log( $connected_addon->get_slug(), 'failed to on_after_render_form', $e->getMessage() );
			}

		}
		$output = ob_get_clean();

		return $output;
	}

	/**
	 * Get Output of addons before render form fields
	 * @see Forminator_Addon_Zapier_Form_Hooks::on_before_render_form_fields()
	 *
	 * @since 1.1
	 * @return string
	 */
	public function do_before_render_form_fields_for_addons() {
		//find is_form_connected
		$connected_addons = forminator_get_addons_instance_connected_with_form( $this->model->id );

		ob_start();
		foreach ( $connected_addons as $connected_addon ) {
			try {
				$form_hooks = $connected_addon->get_addon_form_hooks( $this->model->id );
				if ( $form_hooks instanceof Forminator_Addon_Form_Hooks_Abstract ) {
					$form_hooks->on_before_render_form_fields();
				}
			} catch ( Exception $e ) {
				forminator_addon_maybe_log( $connected_addon->get_slug(), 'failed to on_before_render_form_fields', $e->getMessage() );
			}

		}
		$output = ob_get_clean();

		return $output;

	}

	/**
	 * Get Output of addons after render form fields
	 * @see Forminator_Addon_Zapier_Form_Hooks::on_after_render_form_fields()
	 *
	 * @since 1.1
	 * @return string
	 */
	public function do_after_render_form_fields_for_addons() {
		//find is_form_connected
		$connected_addons = forminator_get_addons_instance_connected_with_form( $this->model->id );

		ob_start();
		foreach ( $connected_addons as $connected_addon ) {
			try {
				$form_hooks = $connected_addon->get_addon_form_hooks( $this->model->id );
				if ( $form_hooks instanceof Forminator_Addon_Form_Hooks_Abstract ) {
					$form_hooks->on_after_render_form_fields();
				}
			} catch ( Exception $e ) {
				forminator_addon_maybe_log( $connected_addon->get_slug(), 'failed to on_after_render_form_fields', $e->getMessage() );
			}

		}
		$output = ob_get_clean();

		return $output;
	}

}