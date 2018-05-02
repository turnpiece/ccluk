<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Field
 *
 * @since 1.0
 * Abstract class for fields
 *
 * @since 1.0.5
 * @property array form_settings
 * @property array field
 *
 */
abstract class Forminator_Field {

	/**
	 * @var
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = '';

	/**
	 * @var string
	 */
	public $category = '';

	/**
	 * @var array
	 */
	public $settings = array();

	/**
	 * @var array
	 */
	public $defaults = array();

	/**
	 * @var bool
	 */
	public $hide_advanced = false;

	/**
	 * @var int
	 */
	public $position = 99;

	/**
	 * @var bool
	 */
	public $is_input = false;

	/**
	 * @var bool
	 */
	public $has_counter = false;

	/**
	 * Check if the input data for field is valid
	 *
	 * @var bool
	 */
	public $is_valid = true;

	/**
	 * Validation message
	 *
	 * @var array
	 */
	public $validation_message = array();

	/**
	 * Activated Autofill Providers for this field based @see autofill_settings
	 *
	 * @since 1.0.5
	 * @var Forminator_Autofill_Provider_Abstract[]
	 *
	 */
	protected $activated_autofill_providers = array();

	public function __construct() {
		if( is_admin() ) {
			$this->settings = apply_filters( "forminator_field_{{ $this->slug }}_general_settings", $this->load_settings() );
			$this->autofill_settings = apply_filters( "forminator_field_{{ $this->slug }}_autofill_settings", $this->autofill_settings() );
			$this->advanced_settings = apply_filters( "forminator_field_{{ $this->slug }}_advanced_settings", $this->load_advanced_settings() );
			$this->markup   = apply_filters( "forminator_field_{{ $this->slug }}_admin_markup", $this->admin_html() );
			$this->defaults = apply_filters( "forminator_field_{{ $this->slug }}_defaults", $this->defaults() );
			$this->position = apply_filters( "forminator_field_{{ $this->slug }}_position", $this->position );
 		}
	}

	/**
	 * Return field name
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Return field slug
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * @return string
	 */
	public function get_category() {
		return $this->category;
	}

	/**
	 * Return field settings
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * @since 1.0
	 * @param array $settings
	 * @return array
	 */
	public function load_settings( $settings = array() ) {
		return $settings;
	}

	/**
	 * @since 1.0
	 * @param array $settings
	 * @return array
	 */
	public function load_advanced_settings( $settings = array() ) {
		return $settings;
	}

	/**
	 * Return field property
	 *
	 * @since 1.0
	 * @param string $property
	 * @param array $field
	 * @param string $fallback
	 *
	 * @return mixed
	 */
	public static function get_property( $property, $field, $fallback = '' ) {
		if( ! isset( $field[ $property ] ) ) return $fallback;

		return $field[ $property ];
	}

	/**
	 * @since 1.0
	 * @return string
	 */
	public function admin_html() {
		return '';
	}

	/**
	 * @since 1.0
	 * @return mixed
	 */
	public function markup( $field, $settings = array() ) {
		return '';
	}

	/**
	 * @since 1.0
	 * @return array
	 */
	public function defaults() {
		return array();
	}

	/**
	 * Return description
	 *
	 * @since 1.0
	 * @param string
	 *
	 * @return string
	 */
	 public static function get_description( $description ) {
		 if( empty( $description ) ) return '';

		 $html = '<div class="forminator-field--helper">';
		 $html .= sprintf( '<label class="forminator-label--helper">%s</label>', $description );
		 $html .= '</div>';

		 return $html;
	 }

	/**
	 * Return new input field
	 *
	 * @since 1.0
	 * @param array $attr
	 *
	 * @return mixed
	 */
	public static function create_input( $attr = array(), $label = '', $description = '', $required = false, $design = '' ) {
		$html   = '';
		$markup = self::implode_attr( $attr );

		if ( $label ) {

			if ( $required ) {

				$html .= '<div class="forminator-field--label">';
				$html .= sprintf( '<label id="forminator-label-%s" class="forminator-label">%s %s</label>', $attr['id'], $label, forminator_get_required_icon() );
				$html .= '</div>';

			} else {

				$html .= '<div class="forminator-field--label">';
				$html .= sprintf( '<label id="forminator-label-%s" class="forminator-label">%s</label>', $attr['id'], $label );
				$html .= '</div>';

			}

		}

		if ( $design === 'material' ) {
			$html .= '<div class="forminator-input--wrap">';
		}

			$html .= sprintf( '<input %s />', $markup );

		if ( $design === 'material' ) {
			$html .= '</div>';
		}

		if( ! empty( $description ) || $description !== '' ) {
			$html .= self::get_description( $description );
		}

		return apply_filters( 'forminator_field_create_input', $html, $attr, $label, $description );
	}


	/**
	 * Return new textarea field
	 *
	 * @since 1.0
	 * @param array $attr
	 *
	 * @return mixed
	 */
	public static function create_textarea( $attr = array(), $label = '', $description = '', $required = false, $design = '' ) {
		$html   = '';

		$content = '';
		if ( isset( $attr['content'] ) && $attr['content'] ) {
			$content = $attr['content'];
			unset( $attr['content'] );
		}

		$markup = self::implode_attr( $attr );

		if ( $label ) {

			if ( $required ) {

				$html .= '<div class="forminator-field--label">';
				$html .= sprintf( '<label id="forminator-label-%s" class="forminator-label">%s %s</label>', $attr['id'], $label, forminator_get_required_icon() );
				$html .= '</div>';

			} else {

				$html .= '<div class="forminator-field--label">';
				$html .= sprintf( '<label id="forminator-label-%s" class="forminator-label">%s</label>', $attr['id'], $label );
				$html .= '</div>';

			}

		}

		if ( $design === 'material' ) {
			$html .= '<div class="forminator-textarea--wrap">';
		}

			$html .= sprintf( '<textarea %s >%s</textarea>', $markup, $content );

		if ( $design === 'material' ) {
			$html .= '</div>';
		}

		if( ! empty( $description ) ) {
			$html .= self::get_description( $description );
		}

		return apply_filters( 'forminator_field_create_textarea', $html, $attr, $label, $description );
	}

	/**
	 * Return wp_editor_field
	 *
	 * @since 1.0.2
	 *
	 * @param array $attr
	 *
	 * @return mixed
	 */
	public static function create_wp_editor( $attr = array(), $label = '', $description = '', $required = false ) {
		$html = '';

		if ( $label ) {

			if ( $required ) {

				$html .= '<div class="forminator-field--label">';
				$html .= sprintf( '<label id="forminator-label-%s" class="forminator-label">%s %s</label>', $attr['id'], $label, forminator_get_required_icon() );
				$html .= '</div>';

			} else {

				$html .= '<div class="forminator-field--label">';
				$html .= sprintf( '<label id="forminator-label-%s" class="forminator-label">%s</label>', $attr['id'], $label );
				$html .= '</div>';

			}

		}

		$wp_editor_class = isset( $attr['class'] ) ? $attr['class'] : '';
		if ( $required ) {
			add_action( 'the_editor', array( __CLASS__, 'add_required_wp_editor' ) );
			$wp_editor_class .= ' do-validate forminator-wp-editor-required';
		}
		ob_start();
		wp_editor( '',
		           isset( $attr['id'] ) ? $attr['id'] : '',
		           array(
			           'textarea_name' => isset( $attr['name'] ) ? $attr['name'] : '',
			           'media_buttons' => false,
			           'editor_class'  => $wp_editor_class,
		           ) );
		$html .= ob_get_clean();

		if ( ! empty( $description ) ) {
			$html .= self::get_description( $description );
		}

		return apply_filters( 'forminator_field_create_wp_editor', $html, $attr, $label, $description );
	}

	/**
	 * Add Required attribute to wp_editor
	 *
	 * @since 1.0.2
	 *
	 * @param $editor_markup
	 *
	 * @return mixed
	 */
	public static function add_required_wp_editor( $editor_markup ) {
		if ( stripos( $editor_markup, 'forminator-wp-editor-required' ) !== false ) {
			// mark required
			$editor_markup = str_replace( '<textarea', '<textarea required="true"', $editor_markup );
		}

		return $editor_markup;
	}

	/**
	 * Return new select field
	 *
	 * @since 1.0
	 * @param array $attr
	 * @param array $options
	 * @param string $value
	 *
	 * @return mixed
	 */
	public static function create_select( $attr = array(), $label = '', $options = array(), $value = '', $description = '', $required = false ) {
		$html   = '';
		$markup = self::implode_attr( $attr );

		if( $label ) {
			if ( $required ) {
				$html .= sprintf( '<div class="forminator-field--label"><label class="forminator-label">%s %s</label></div>', $label, forminator_get_required_icon() );
			} else {
				$html .= sprintf( '<div class="forminator-field--label"><label class="forminator-label">%s</label></div>', $label );
			}
		}

		$html .= sprintf( '<select %s>', $markup );

		foreach( $options as $option ) {
			$selected = '';

			if( $option[ 'value' ] == $value ) {
				$selected = 'selected="selected"';
			}
			$html .= sprintf( '<option value="%s" %s>%s</option>', $option[ 'value' ], $selected, $option[ 'label' ] );
		}

		$html .= '</select>';

		if( ! empty( $description ) ) {
			$html .= self::get_description( $description );
		}

		return apply_filters( 'forminator_field_create_select', $html, $attr, $label, $options, $value, $description );
	}

	/**
	 * Return new simple select field
	 *
	 * @since 1.0
	 * @param array $attr
	 * @param array $options
	 * @param string $value
	 *
	 * @return mixed
	 */
	public static function create_simple_select( $attr = array(), $options = array(), $value = '', $description = '' ) {
		$html   = '';
		$markup = self::implode_attr( $attr );

		$html .= sprintf( '<select %s>', $markup );

		foreach( $options as $option ) {
			$selected = '';

			if( $option[ 'value' ] == $value ) {
				$selected = 'selected="selected"';
			}
			$html .= sprintf( '<option value="%s" %s>%s</option>', $option[ 'value' ], $selected, $option[ 'label' ] );
		}

		$html .= '</select>';

		if( ! empty( $description ) ) {
			$html .= self::get_description( $description );
		}

		return apply_filters( 'forminator_field_create_simple_select', $html, $attr, $options, $value, $description );
	}

	/**
	 * Create file upload
	 *
	 * @since 1.0
	 *
	 * @param string $id
	 * @param string $name
	 * @param bool   $required
	 *
	 * @return string $html
	 */
	public static function create_file_upload( $id, $name, $required = false, $design ) {
		$id    = $id . '-field';
		$class = 'forminator-input-file';
		if ( $required ) {
			$class .= '-required do-validate';
		}
		$html = '<div class="forminator-upload">';
		if ( $design !== 'material') {
			$html .= sprintf( '<button type="button" class="forminator-button forminator-upload-button" data-id="%s" id="%s">%s</button>',
							  $id,
							  $id,
							  __( 'Choose File', Forminator::DOMAIN ) );
		} else {
			$html .= sprintf( '<button type="button" class="forminator-button forminator-upload-button" data-id="%s" id="%s"><span class="forminator-button--mask" aria-label="hidden"></span><span class="forminator-button--text">%s</span></button>',
							  $id,
							  $id,
							  __( 'Choose File', Forminator::DOMAIN ) );
		}
		$html .= sprintf( '<label class="forminator-label" id="%s">%s</label>', $id, __( 'No file chosen', Forminator::DOMAIN ) );
		$html .= '<button class="forminator-upload--remove" style="display: none;"><span class="wpdui-icon wpdui-icon-close"></span></button>';
		$html .= sprintf( '<input class="forminator-input %s" type="file" name="%s" id="%s" style="display:none" %s/>', $class, $name, $id, ( $required ? 'required="true"' : '' ) );
		$html .= '</div>';

		return apply_filters( 'forminator_field_create_file_upload', $html, $id, $name, $required );
	}

	/**
	 * Return string from array
	 *
	 * @since 1.0
	 * @param array $args
	 *
	 * @return string
	 */
	public static function implode_attr( $args ) {
		$data = array();

		foreach( $args as $key => $value ) {
			$data[] = $key . '="' . $value . '"';
		}

		return implode( " ", $data );
	}

	/**
	 * Validate data
	 *
	 * @since 1.0
	 * @param array $field
	 * @param array|string $data - the data to be validated
	 */
	public function validate( $field, $data ) { }

	/**
	 * Check if entry is valid for the field
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function is_valid_entry() {
		$this->is_valid = empty( $this->validation_message );
		if ( !$this->is_valid ) {
			return $this->validation_message;
		}
		return $this->is_valid;
	}

	/**
	 * Check if field has input limit
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function has_limit( $field ) {
		$limit = self::get_property( 'text_limit', $field, false );
		$limit = filter_var( $limit , FILTER_VALIDATE_BOOLEAN );

		return $limit;
	}

	/**
	 * Check if field is required
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function is_required( $field ) {
		$required = self::get_property( 'required', $field, false );
		$required = filter_var( $required , FILTER_VALIDATE_BOOLEAN );

		return $required;
	}

	/**
	 * Check if Field is hidden based on conditions property and POST-ed data
	 *
	 * @since 1.0
	 * @param $field
	 * @param $form_data
	 *
	 * @return bool
	 */
	public function is_hidden( $field, $form_data ) {
		$conditional = self::get_property( 'use_conditions', $field, false );
		$conditions  = self::get_property( 'conditions', $field, array() );

		//not use `use_conditions` or empty conditions
		if ( ! $conditional || empty( $conditions ) ) {
			return false;
		}

		$condition_action = self::get_property( 'condition_action', $field, 'show' );
		$condition_rule   = self::get_property( 'condition_rule', $field, 'any' );

		$condition_fulfilled = 0;
		foreach ( $conditions as $condition ) {
			$element_id = $condition['element_id'];

			if ( ! isset( $form_data[ $element_id ] ) ) {
				$is_condition_fulfilled = false;
			} else {
				$is_condition_fulfilled = self::is_condition_fulfilled( $form_data[ $element_id ], $condition );
			}
			if ( $is_condition_fulfilled ) {
				$condition_fulfilled ++;
			}
		}

		//initialized as hidden
		if ( $condition_action == 'show' ) {
			if ( ( $condition_fulfilled > 0 && $condition_rule == 'any' ) || ( $condition_fulfilled == count( $conditions ) && $condition_rule == 'all' ) ) {
				return false;
			}

			return true;
		} else {
			//initialized as shown
			if ( ( $condition_fulfilled > 0 && $condition_rule == 'any' ) || ( $condition_fulfilled == count( $conditions ) && $condition_rule == 'all' ) ) {
				return true;
			}

			return false;
		}
	}

	/**
	 * Check if Form Field value fullfilled the condition
	 *
	 * @since 1.0
	 * @param $form_field_value
	 * @param $condition
	 *
	 * @return bool
	 */
	public static function is_condition_fulfilled( $form_field_value, $condition ) {
		switch ( $condition['rule'] ) {
			case 'is':
				if ( is_array( $form_field_value ) ) {
					return in_array( $condition['value'], $form_field_value );
				}

				return ( $form_field_value === $condition['value'] );
				break;
			case 'is_not':
				if ( is_array( $form_field_value ) ) {
					return ! in_array( $condition['value'], $form_field_value );
				}

				return ( $form_field_value !== $condition['value'] );
				break;
			case 'is_great':
				if ( ! is_numeric( $condition['value'] ) ) {
					return false;
				}
				if ( ! is_numeric( $form_field_value ) ) {
					return false;
				}

				return $form_field_value > $condition['value'];
				break;
			case 'is_less':
				if ( ! is_numeric( $condition['value'] ) ) {
					return false;
				}
				if ( ! is_numeric( $form_field_value ) ) {
					return false;
				}

				return $form_field_value < $condition['value'];
				break;
			case 'contains':
				return ( stripos( $form_field_value, $condition['value'] ) === false ? false : true );
				break;
			case 'starts':
				return ( stripos( $form_field_value, $condition['value'] ) === 0 ? true : false );
				break;
			case 'ends':
				return ( stripos( $form_field_value, $condition['value'] ) === ( strlen( $form_field_value - 1 ) ) ? true : false );
				break;
			default:
				return false;
				break;
		}
	}

	/**
	 * Return field ID
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_id( $field ) {
		return self::get_property( 'element_id', $field );
	}

	/**
	 * Field validation rules
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_rules() {
		return '';
	}

	/**
	 * Field validation messages
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_messages() {
		return '';
	}


	/**
	 * Sanitize data
	 *
	 * @since 1.0.2
	 *
	 * @param array $field
	 * @param array|string $data - the data to be sanitized
	 *
	 * @return array|string $data - the data after sanitization
	 */
	public function sanitize( $field, $data ) {
		return $data;
	}

	public function sanitize_value( $value ){
		return htmlspecialchars($value, ENT_COMPAT);
	}

	/**
	 * Check if field is available
	 * Override it for field that needs dependencies
	 * Example : `captcha` that needs `captcha_key` to be displayed properly
	 * @see Forminator_Captcha::is_available()
	 *
	 * @since 1.0.3
	 *
	 * @param $field
	 *
	 * @return bool
	 */
	public function is_available( $field ) {
		return true;
	}

	/**
	 * Return form style
	 *
	 * @since 1.0.3
	 * @param $settings
	 *
	 * @return string|bool
	 */
	public function get_form_style( $settings ) {
		if( isset( $settings['form-style'] ) ) {
			return $settings['form-style'];
		}

		return false;
	}

	/**
	 * Abstraction of autofill settings
	 *
	 * @since 1.0.5
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function autofill_settings( $settings = array() ) {
		return $settings;
	}


	/**
	 * Init available autofill providers
	 * It will only init provider that ACTIVATED by user form on `fields-autofill` not all of the AVAILABLE
	 * Call it just when it needed
	 *
	 * @since 1.0.5
	 *
	 * @example
	 * @see   Forminator_Field::markup() : when its rendering
	 * @see   Forminator_CForm_Front_Action::handle_form() : when form submitted
	 *
	 * @since 1.0.5
	 *
	 * @param $settings
	 */
	public function init_autofill( $settings ) {

		// Lazy init providers
		if ( self::is_autofill_enabled( $settings )
		     && isset( $settings['fields-autofill'] )
		     && ! empty( $settings['fields-autofill'] ) ) {

			foreach ( $settings['fields-autofill'] as $fields_autofill ) {
				if ( ! isset( $fields_autofill['provider'] ) || empty( $fields_autofill['provider'] ) ) {
					continue;
				}

				$provider       = $fields_autofill['provider'];
				$provider_parts = explode( '.', $provider );
				if ( ! isset( $provider_parts[0] ) || empty( $provider_parts[0] ) ) {
					continue;
				}

				$provider_slug     = $provider_parts[0];
				$provider_instance = forminator_autofill_init_provider( $provider_slug );

				if ( $provider_instance ) {
					$this->activated_autofill_providers[ $provider ] = $provider_instance;
				}
			}
		}
	}

	/**
	 * Get Autofill setting as paired ['element_id' => $setting]
	 *
	 * @since 1.0.5
	 *
	 * @param $settings
	 *
	 * @return array
	 */
	public static function get_autofill_setting( $settings ) {

		// Autofill not enabled
		if ( ! self::is_autofill_enabled( $settings ) ) {
			return array();
		}

		if ( isset( $settings['fields-autofill'] ) && ! empty( $settings['fields-autofill'] ) ) {
			// build to array key
			$fields_autofill      = $settings['fields-autofill'];
			$fields_autofill_pair = array();
			if ( ! is_array( $fields_autofill ) ) {
				return array();
			}

			foreach ( $fields_autofill as $field_autofill ) {
				if ( ! isset( $field_autofill['element_id'] ) || empty( $field_autofill['element_id'] ) ) {
					continue;
				}
				$fields_autofill_pair[ $field_autofill['element_id'] ] = $field_autofill;
			}

			return $fields_autofill_pair;
		}

		return array();
	}

	/**
	 * Check if autofill enabled on this form
	 *
	 * @since 1.0.5
	 *
	 * @param $settings
	 *
	 * @return bool
	 */
	public static function is_autofill_enabled( $settings ) {
		if ( isset( $settings['use-autofill'] ) && $settings['use-autofill'] == "true" ) {
			return true;
		}

		return false;
	}

	/**
	 * Restore value of the POST fields if its not editable, so we ensure its not modified by anykind
	 * Happens before this POST fields getting validated, so autofill-ed value will be getting validated too
	 *
	 * @since 1.0.5
	 *
	 * @param $field_array
	 * @param $field_data
	 * @param $settings
	 *
	 * @return array|mixed|string
	 */
	public function maybe_re_autofill( $field_array, $field_data, $settings ) {
		$autofill_settings = self::get_autofill_setting( $settings );

		if ( empty( $autofill_settings ) ) {
			return $field_data;
		}

		if ( ! self::is_autofill_enabled( $settings ) ) {
			return $field_data;
		}

		$element_id = self::get_property( 'element_id', $field_array );
		if ( is_array( $field_data ) ) {
			foreach ( $field_data as $element_id_suffix => $field_datum ) {
				$element_id                = $element_id . '-' . $element_id_suffix;
				$element_autofill_settings = self::get_element_autofill_settings( $element_id, $autofill_settings );
				if ( ! self::element_autofill_is_editable( $element_autofill_settings ) ) {
					// refill with autofill provider
					$autofill_value = $this->maybe_get_element_autofill_value( $field_datum, $element_autofill_settings );
					// only assign autofill value if autofill_value is not empty
					if ( ! empty( $autofill_value ) ) {
						$field_data[ $element_id_suffix ] = $autofill_value;
					}
				}
			}
		} else {
			$element_autofill_settings = self::get_element_autofill_settings( $element_id, $autofill_settings );

			if ( ! self::element_autofill_is_editable( $element_autofill_settings ) ) {
				// refill with autofill provider
				$autofill_value = $this->maybe_get_element_autofill_value( $field_data, $element_autofill_settings );
				// only assign autofill value if autofill_value is not empty
				if ( ! empty( $autofill_value ) ) {
					$field_data = $autofill_value;
				}
			}

		}

		return $field_data;
	}


	/**
	 * Get autofill as markup attributes to used later
	 *
	 * @since   1.0.5
	 *
	 * @example [
	 *  'value' => [] / VALUE
	 *   'readonly' => 'readonly'
	 * ]
	 *
	 * @param $element_id
	 * @param $settings
	 *
	 * @return array;
	 */
	public function get_element_autofill_markup_attr( $element_id, $settings ) {
		if ( ! self::is_autofill_enabled( $settings ) ) {
			return array();
		}

		$autofill_settings = self::get_autofill_setting( $settings );

		if ( empty( $autofill_settings ) ) {
			return array();
		}

		$element_autofill_settings = self::get_element_autofill_settings( $element_id, $autofill_settings );
		$value                     = $this->maybe_get_element_autofill_value( '', $element_autofill_settings );

		// only return value when its autofilled
		if ( ! empty( $value ) ) {
			$markup_attr = array(
				'value' => $value,
			);
			//only disable if value is not empty
			if ( ! self::element_autofill_is_editable( $element_autofill_settings ) ) {
				$markup_attr['readonly'] = 'readonly';
			}

			return $markup_attr;
		}

		return array();
	}

	/**
	 * Get element autofill value if all requirement(s) fulfilled
	 * - Autofill Provider activated
	 *
	 * @param $element_value
	 * @param $element_autofill_settings
	 *
	 * @return mixed|string
	 */
	public function maybe_get_element_autofill_value( $element_value, $element_autofill_settings ) {
		if ( isset( $element_autofill_settings['provider'] ) && ! empty( $element_autofill_settings['provider'] ) ) {
			$attribute_provider = $element_autofill_settings['provider'];
			if ( isset( $this->activated_autofill_providers[ $attribute_provider ] ) ) {
				$attribute_provider_parts = explode( '.', $attribute_provider );
				if ( isset( $attribute_provider_parts[1] ) && ! empty( $attribute_provider_parts[1] ) ) {
					$element_value = $this->activated_autofill_providers[ $attribute_provider ]->fill( $attribute_provider_parts[1] );
				}
			}
		}

		return $element_value;
	}

	/**
	 * Check if element has autofill
	 *
	 * @since 1.0.5
	 *
	 * @param $element_id
	 * @param $autofill_settings
	 *
	 * @return bool
	 */
	public static function element_has_autofill( $element_id, $autofill_settings ) {
		return in_array( $element_id, array_keys( $autofill_settings ) );
	}

	/**
	 * Get individial element autofill setting
	 *
	 * @since 1.0.5
	 *
	 * @param $element_id
	 * @param $autofill_settings
	 *
	 * @return array
	 */
	public static function get_element_autofill_settings( $element_id, $autofill_settings ) {
		$autofill_element_settings = array();
		if ( ! self::element_has_autofill( $element_id, $autofill_settings ) ) {
			return array();
		}

		if ( isset( $autofill_settings[ $element_id ] ) && is_array( $autofill_settings[ $element_id ] ) ) {
			$autofill_element_settings = $autofill_settings[ $element_id ];
		}

		return $autofill_element_settings;
	}

	/**
	 * Check if an element is editable when autofill enabled
	 *
	 * @param $element_autofill_settings
	 *
	 * @return bool
	 */
	public static function element_autofill_is_editable( $element_autofill_settings ) {
		if ( isset( $element_autofill_settings['is_editable'] ) && $element_autofill_settings['is_editable'] == 'yes' ) {
			return true;
		}

		return false;
	}
}