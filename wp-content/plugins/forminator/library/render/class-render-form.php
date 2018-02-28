<?php

/**
 * Class Forminator_Render_Form
 *
 * @since 1.0
 */
abstract class Forminator_Render_Form {

	/**
	 * Model data
	 *
	 * @var Forminator_Base_Form_Model
	 */
	public $model = null;

	/**
	 * Checks if is admin
	 *
	 * @var bool
	 */
	protected $is_admin = false;

	/**
	 * Track Views
	 *
	 * @var bool
	 */
	protected $track_views = true;

	/**
	 * Mapper form with its instance, handling multiple same form rendered
	 *
	 * @var array
	 */
	protected static $render_ids = array();

	/**
	 * Forminator_Render_Form constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->is_admin = is_admin();
		$this->init();
	}

	/**
	 * Init method
	 *
	 * @since 1.0
	 */
	public function init() {
	}

	/**
	 * Display form method
	 * Must be implemented by class that extend it
	 *
	 * @since 1.0
	 *
	 * @param      $id
	 * @param bool $ajax
	 *
	 * @return mixed
	 */
	abstract function display( $id, $ajax = false );

	/**
	 * Generate render_id for current form
	 * represented as integer, start from 0
	 *
	 * @param $id
	 */
	public function generate_render_id( $id ) {
		// set render_id for mapping Front End with its form.
		if ( ! isset( self::$render_ids[ $id ] ) ) {
			self::$render_ids[ $id ] = 0;
		} else {
			self::$render_ids[ $id ] ++;
		}
	}

	/**
	 * Render form markup
	 *
	 * @since 1.0
	 *
	 * @param $id
	 */
	public function render( $id ) {
		$form_type     = $this->get_form_type();
		$form_fields   = $this->get_fields();
		$form_settings = $this->get_form_settings();
		$post_id       = $this->get_post_id();

		do_action( 'forminator_before_form_render', $id, $form_type, $post_id, $form_fields, $form_settings );

		$this->get_form( $id, true );

		do_action( 'forminator_after_form_render', $id, $form_type, $post_id, $form_fields, $form_settings );
	}

	/**
	 * Return form markup
	 *
	 * @since 1.0
	 *
	 * @param      $id
	 * @param bool $render
	 *
	 * @return mixed|void
	 */
	public function get_form( $id, $render = true ) {
		$html          = '';
		$form_type     = $this->get_form_type();
		$form_fields   = $this->get_fields();
		$form_settings = $this->get_form_settings();
		$form_design   = $this->get_form_design();
		$form_enctype  = $this->form_enctype();
		$extra_classes = $this->form_extra_classes();
		$track_views   = $this->can_track_views();
		//if rendered on Preview, the array is empty and sometimes PHP notices show up
		if( $this->is_admin && empty( self::$render_ids ) ){
			self::$render_ids[$id] = 0;
		}

		$render_id = self::$render_ids[ $id ];

		if ( $form_type === 'custom-form' ) {

			$fields_style = $this->get_fields_style();

			if ( $fields_style === 'enclosed' ) {
				$fields_type = 'forminator-enclosed';
			} else {
				$fields_type = '';
			}

		} else {
			$fields_type = '';
		}


		if ( ! $this->is_admin ) {
			$html .= sprintf( '<form id="forminator-module-%s" class="forminator-%s forminator-%s-%s forminator-design--%s %s %s" action="" method="post" data-forminator-render="%s" %s>',
			                  $id,
			                  $form_type,
			                  $form_type,
			                  $id,
			                  $form_design,
			                  $fields_type,
			                  $extra_classes,
			                  $render_id,
			                  $form_enctype );
		} else {
			$html .= sprintf( '<div id="forminator-module-%s" class="forminator-%s forminator-%s-%s forminator-design--%s %s %s" %s>',
			                  $id,
			                  $form_type,
			                  $form_type,
			                  $id,
			                  $form_design,
			                  $fields_type,
			                  $extra_classes,
			                  $form_enctype );
		}

		$html .= $this->render_form_header();
		$html .= $this->render_fields( false );
		$html .= $this->get_submit( $id, false );

		if ( ! $this->is_admin ) {
			$html .= sprintf( '</form>' );
		} else {
			$html .= sprintf( '</div>' );
		}

		if ( $track_views ) {
			$form_view = Forminator_Form_Views_Model::get_instance();
			$post_id   = $this->get_post_id();
			$form_view->save_view( $id, $post_id, Forminator_Geo::get_user_ip() );
		}

		if ( $render ) {
			echo apply_filters( 'forminator_render_form_markup', $html, $form_fields, $form_type, $form_settings, $form_design, $render_id );
		} else {
			/** @noinspection PhpInconsistentReturnPointsInspection */
			return apply_filters( 'forminator_render_form_markup', $html, $form_fields, $form_type, $form_settings, $form_design, $render_id );
		}
	}

	/**
	 * Return form submit button markup
	 *
	 * @since 1.0
	 *
	 * @param      $form_id
	 * @param bool $render
	 *
	 * @return mixed|void
	 */
	public function get_submit( $form_id, $render = true ) {
		$nonce     = wp_nonce_field( 'forminator_submit_form', 'forminator_nonce', true, false );
		$post_id   = $this->get_post_id();
		$html      = $this->get_button_markup();
		$form_type = $this->get_form_type();
		$html      .= $nonce;
		$html      .= sprintf( '<input type="hidden" name="form_id" value="%s">', $form_id );
		$html      .= sprintf( '<input type="hidden" name="page_id" value="%s">', $post_id );
		if ( isset( self::$render_ids[ $form_id ] ) ) {
			$html .= sprintf( '<input type="hidden" name="render_id" value="%s">', self::$render_ids[ $form_id ] );
		}
		$html .= sprintf( '<input type="hidden" name="action" value="%s">', "forminator_submit_form_" . $form_type );
		if ( $render ) {
			echo apply_filters( 'forminator_render_form_submit_markup', $html, $form_id, $post_id, $nonce );
		} else {
			/** @noinspection PhpInconsistentReturnPointsInspection */
			return apply_filters( 'forminator_render_form_submit_markup', $html, $form_id, $post_id, $nonce );
		}
	}

	/**
	 * Return button markup
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function get_button_markup() {
		$button = $this->get_submit_button_text();
		$html   = '<div class="forminator-row">';
		$html   .= '<div class="forminator-col forminator-col-12">';
		$html   .= '<div class="forminator-field">';
		$html   .= sprintf( '<button id="forminator-submit" class="forminator-button"><span class="forminator-button--mask" aria-label="hidden"></span><span class="forminator-button--text">%s</span></button>',
		                    $button );
		$html   .= '</div>';
		$html   .= '</div>';
		$html   .= '</div>';

		return apply_filters( 'forminator_render_button_markup', $html, $button );
	}

	/**
	 * Submit button text
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_submit_button_text() {
		return __( "Submit", Forminator::DOMAIN );
	}

	/**
	 * Return form fields
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_fields() {
		// That function will be overwritten by form class
		return array();
	}


	/**
	 * Return form fields markup
	 *
	 * @since 1.0
	 *
	 * @param bool $render
	 *
	 * @return mixed|void
	 */
	public function render_fields( $render = true ) {
		$html = '';

		$fields = $this->get_fields();
		foreach ( $fields as $key => $field ) {
			do_action( 'forminator_before_field_render', $field );

			// Render before field markup
			$html .= $this->render_field_before( $field );

			// Render field
			$html .= $this->render_field( $field );

			do_action( 'forminator_after_field_render', $field );

			// Render after field markup
			$html .= $this->render_field_after( $field );
		}

		if ( $render ) {
			echo $html;
		} else {
			/** @noinspection PhpInconsistentReturnPointsInspection */
			return apply_filters( 'forminator_render_fields_markup', $html, $fields );
		}
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
	public function get_classes(
		/** @noinspection PhpUnusedParameterInspection */
		$field
	) {
		return 'forminator-field';
	}

	/**
	 * Return markup before field
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function render_field_before( $field ) {
		$class = $this->get_classes( $field );
		$html  = sprintf( '<div class="%s">', $class );

		return apply_filters( 'forminator_before_field_markup', $html, $class );
	}

	/**
	 * Return markup after field
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function render_field_after( $field ) {
		$html = sprintf( '</div>' );

		return apply_filters( 'forminator_after_field_markup', $html, $field );
	}

	/**
	 * Return sanitized form data
	 *
	 * @since 1.0
	 *
	 * @param $content
	 *
	 * @return mixed
	 */
	public function sanitize_output( $content ) {
		return htmlentities( $content, ENT_QUOTES );
	}

	/**
	 * Return field markup
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return mixed|void
	 */
	public function render_field( $field ) {
	}

	/**
	 * Return form settings
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_form_settings() {
		return array();
	}

	/**
	 * Return field ID
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return int
	 */
	public function get_field_id( $field ) {
		return isset( $field['element_id'] ) ? $field['element_id'] : '0';
	}

	/**
	 * Return post ID
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_post_id() {
		return get_post() ? get_the_ID() : '0';
	}

	/**
	 * Return form type
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_form_type() {
		return '';
	}

	/**
	 * Return form design
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_form_design() {
		return '';
	}

	/**
	 * Return fields style
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_fields_style() {
		return '';
	}

	/**
	 * Render form header
	 *
	 * @since 1.0
	 */
	public function render_form_header() {
		return '';
	}

	/**
	 * Form enctype
	 *
	 * @since 1.0
	 * @return string
	 */
	public function form_enctype() {
		return '';
	}

	/**
	 * Form extra classes
	 *
	 * @since 1.0
	 */
	public function form_extra_classes() {
		return '';
	}

	/**
	 * Check if can track views
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function can_track_views() {
		return $this->track_views;
	}
}