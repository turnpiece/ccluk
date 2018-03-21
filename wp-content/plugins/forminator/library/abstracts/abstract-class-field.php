<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Field
 *
 * Abstract class for fields
 *
 * @since 1.0
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

	public function __construct() {
		if( is_admin() ) {
			$this->settings = apply_filters( "forminator_field_{{ $this->slug }}_general_settings", $this->load_settings() );
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
	public static function create_input( $attr = array(), $label = '', $description = '', $required = false ) {
		$html   = '';
		$markup = self::implode_attr( $attr );

		if ( $label ) {
			if ( $required ) {
				$html .= sprintf( '<div class="forminator-field--label"><label class="forminator-label">%s</label>', $label );
				$html .= sprintf( '<div class="forminator-icon" aria-hidden="true">%s</div>', forminator_get_required_icon() );
				$html .= '</div>';
			} else {
				$html .= sprintf( '<div class="forminator-field--label"><label class="forminator-label">%s</label></div>', $label );
			}
		}

		$html .= sprintf( '<input %s />', $markup );

		if( ! empty( $description ) ) {
			$html .= self::get_description( $description );
		}

		return apply_filters( 'forminator_field_create_input', $html, $attr, $label, $description );
	}

	/**
	 * Return new simple input field
	 *
	 * @since 1.0
	 * @param array $attr
	 *
	 * @return mixed
	 */
	public static function create_simple_input( $attr = array(), $description = '' ) {
		$html   = '';
		$markup = self::implode_attr( $attr );

		$html .= sprintf( '<input %s />', $markup );

		if( ! empty( $description ) ) {
			$html .= self::get_description( $description );
		}

		return apply_filters( 'forminator_field_create_simple_input', $html, $attr, $description );
	}


	/**
	 * Return new input field
	 *
	 * @since 1.0
	 * @param array $attr
	 *
	 * @return mixed
	 */
	public static function create_textarea( $attr = array(), $label = '', $description = '', $required = false ) {
		$html   = '';
		$markup = self::implode_attr( $attr );

		if( $label ) {
			if ( $required ) {
				$html .= sprintf( '<div class="forminator-field--label"><label class="forminator-label">%s</label>', $label );
				$html .= sprintf( '<div class="forminator-icon" aria-hidden="true">%s</div>', forminator_get_required_icon() );
				$html .= '</div>';
			} else {
				$html .= sprintf( '<div class="forminator-field--label"><label class="forminator-label">%s</label></div>', $label );
			}
		}

		$html .= sprintf( '<textarea %s ></textarea>', $markup );

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
				$html .= sprintf( '<div class="forminator-field--label"><label class="forminator-label">%s</label>', $label );
				$html .= sprintf( '<div class="forminator-icon" aria-hidden="true">%s</div>', forminator_get_required_icon() );
				$html .= '</div>';
			} else {
				$html .= sprintf( '<div class="forminator-field--label"><label class="forminator-label">%s</label></div>', $label );
			}
		}

		$wp_editor_class = isset( $attr['class'] ) ? $attr['class'] : '';
		if ( $required ) {
			add_action( 'the_editor', array( __CLASS__, 'add_required_wp_editor' ) );
			$wp_editor_class .= ' forminator-wp-editor-required';
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
				$html .= sprintf( '<div class="forminator-field--label"><label class="forminator-label">%s</label>', $label );
				$html .= sprintf( '<div class="forminator-icon" aria-hidden="true">%s</div>', forminator_get_required_icon() );
				$html .= '</div>';
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
	public static function create_file_upload( $id, $name, $required = false ) {
		$id    = $id . '-field';
		$class = 'forminator-input-file';
		if ( $required ) {
			$class .= '-required';
		}
		$html = '<div class="forminator-upload">';
		$html .= sprintf( '<button type="button" class="forminator-button forminator-upload-button" data-id="%s" id="%s"><span class="forminator-button--mask" aria-label="hidden"></span><span class="forminator-button--text">%s</span></button>',
		                  $id,
		                  $id,
		                  __( 'Choose File', Forminator::DOMAIN ) );
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
	 * Check if phone field has input limit
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function has_phone_limit( $field ) {
		$limit = self::get_property( 'phone_limit', $field, false );
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
}