<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Time
 *
 * @property  array field
 * @since 1.0
 */
class Forminator_Time extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'time';

	/**
	 * @var string
	 */
	public $type = 'time';

	/**
	 * @var int
	 */
	public $position = 13;

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * @var string
	 */
	public $category = 'standard';

	/**
	 * Forminator_Time constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'Time', Forminator::DOMAIN );
	}

	/**
	 * @since 1.0
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function load_settings( $settings = array() ) {
		return array(
			array(
				'id'         => 'required',
				'type'       => 'Toggle',
				'name'       => 'required',
				'className'  => 'required-field',
				'hide_label' => true,
				'values'     => array(
					array(
						'value'      => "true",
						'label'      => __( 'Required', Forminator::DOMAIN ),
						'labelSmall' => "true",
					),
				),
			),

			array(
				'id'         => 'separator',
				'type'       => 'Separator',
				'name'       => 'separator',
				'hide_label' => true,
				'className'  => 'separator-field',
			),

			array(
				'id'         => 'field-label',
				'type'       => 'Text',
				'name'       => 'field_label',
				'hide_label' => false,
				'label'      => __( 'Field label', Forminator::DOMAIN ),
				'className'  => 'text-field',
			),

			array(
				'id'            => 'time-type',
				'type'          => 'Radio',
				'name'          => 'time_type',
				'label'         => __( "Time format", Forminator::DOMAIN ),
				'className'     => 'radio-field',
				'default_value' => 'twelve',
				'values'        => array(
					array(
						'value' => "twelve",
						'label' => __( '12 hour', Forminator::DOMAIN ),
					),
					array(
						'value' => "twentyfour",
						'label' => __( '24 hour', Forminator::DOMAIN ),
					),
				),
			),

			array(
				'id'        => 'hh-config',
				'type'      => 'ColDouble',
				'name'      => 'hh_config',
				'label'     => __( 'Hours field', Forminator::DOMAIN ),
				'className' => 'text-field',
				'fields'    => array(
					array(
						'id'        => 'hh-label',
						'type'      => 'Text',
						'name'      => 'hh_label',
						'label'     => __( 'Label', Forminator::DOMAIN ),
						'className' => 'text-field',
					),
					array(
						'id'        => 'hh-placeholder',
						'type'      => 'Text',
						'name'      => 'hh_placeholder',
						'label'     => __( 'Placeholder', Forminator::DOMAIN ),
						'className' => 'text-field',
					),
				),
			),

			array(
				'id'        => 'mm-config',
				'type'      => 'ColDouble',
				'name'      => 'mm_config',
				'label'     => __( 'Minutes field', Forminator::DOMAIN ),
				'className' => 'text-field',
				'fields'    => array(
					array(
						'id'        => 'mm-label',
						'type'      => 'Text',
						'name'      => 'mm_label',
						'label'     => __( 'Label', Forminator::DOMAIN ),
						'className' => 'text-field',
					),
					array(
						'id'        => 'mm-placeholder',
						'type'      => 'Text',
						'name'      => 'mm_placeholder',
						'label'     => __( 'Placeholder', Forminator::DOMAIN ),
						'className' => 'text-field',
					),
				),
			),

			array(
				'id'        => 'type',
				'type'      => 'Radio',
				'name'      => 'field_type',
				'label'     => __( "Field type", Forminator::DOMAIN ),
				'size'      => 12,
				'className' => 'type-field',
				'values'    => array(
					array(
						'value' => "input",
						'label' => __( 'Number inputs', Forminator::DOMAIN ),
					),
					array(
						'value' => "select",
						'label' => __( 'Drop downs', Forminator::DOMAIN ),
					),
				),
			),

		);
	}

	/**
	 * Field defaults
	 *
	 * @since 1.0
	 * @return array
	 */
	public function defaults() {
		return array(
			'field_type'  	 => 'input',
			'time_type'      => 'twelve',
			'field_label'    => '',
			'hh_label'       => __( 'Hours', Forminator::DOMAIN ),
			'hh_placeholder' => __( 'E.g. 08', Forminator::DOMAIN ),
			'mm_label'       => __( 'Minutes', Forminator::DOMAIN ),
			'mm_placeholder' => __( 'E.g. 00', Forminator::DOMAIN ),
		);
	}

	/**
	 * Autofill Setting
	 *
	 * @since 1.0.5
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function autofill_settings( $settings = array() ) {
		$hours_providers   = apply_filters( 'forminator_field_' . $this->slug . '_hours_autofill', array(), $this->slug . '_hours' );
		$minutes_providers = apply_filters( 'forminator_field_' . $this->slug . '_minutes_autofill', array(), $this->slug . '_minutes' );
		$ampm_providers    = apply_filters( 'forminator_field_' . $this->slug . '_ampm_autofill', array(), $this->slug . '_ampm' );

		$autofill_settings = array(
			'time-hours'   => array(
				'values' => forminator_build_autofill_providers( $hours_providers ),
			),
			'time-minutes' => array(
				'values' => forminator_build_autofill_providers( $minutes_providers ),
			),
			'time-ampm'    => array(
				'values' => forminator_build_autofill_providers( $ampm_providers ),
			),
		);

		return $autofill_settings;
	}

	/**
	 * Field admin markup
	 *
	 * @since 1.0
	 * @return string
	 */
	public function admin_html() {
		return '{[ if( field.field_label !== "" ) { ]}
			<label class="sui-label">{{ encodeHtmlEntity( field.field_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
		{[ } ]}
		<div class="sui-row">
			{[ if( _.isUndefined(field.field_type) || field.field_type === "input" ) { ]}
				{[ if( field.time_type === "twelve" ) { ]}
					<div class="sui-col-md-4">
				{[ } else { ]}
					<div class="sui-col-md-6">
				{[ } ]}
					{[ if( field.hh_label !== "" ) { ]}
						<label class="sui-label">{{ encodeHtmlEntity( field.hh_label ) }}{[ if( field.field_label === "" && field.required == "true" ) { ]} *{[ } ]}</label>
					{[ } ]}
					<input class="sui-form-control" placeholder="{{ encodeHtmlEntity( field.hh_placeholder ) }}" />
				</div>
				{[ if( field.time_type === "twelve" ) { ]}
					<div class="sui-col-md-4">
				{[ } else { ]}
					<div class="sui-col-md-6">
				{[ } ]}
					{[ if( field.mm_label !== "" ) { ]}
						<label class="sui-label">{{ encodeHtmlEntity( field.mm_label ) }}{[ if( field.field_label === "" && field.required == "true" ) { ]} *{[ } ]}</label>
					{[ } ]}
					<input class="sui-form-control" placeholder="{{ encodeHtmlEntity( field.mm_placeholder ) }}" />
				</div>
			{[ } ]}
			{[ if( field.field_type === "select" ) { ]}
				{[ if( field.time_type === "twelve" ) { ]}
					<div class="sui-col-md-4">
				{[ } else { ]}
					<div class="sui-col-md-6">
				{[ } ]}
					{[ if( field.hh_label !== "" ) { ]}
						<label class="sui-label">{{ encodeHtmlEntity( field.hh_label ) }}{[ if( field.field_label === "" && field.required == "true" ) { ]} *{[ } ]}</label>
					{[ } ]}
					<select>
						<option>1</option>
					</select>
				</div>
				{[ if( field.time_type === "twelve" ) { ]}
					<div class="sui-col-md-4">
				{[ } else { ]}
					<div class="sui-col-md-6">
				{[ } ]}
					{[ if( field.mm_label !== "" ) { ]}
						<label class="sui-label">{{ encodeHtmlEntity( field.mm_label ) }}{[ if( field.field_label === "" && field.required == "true" ) { ]} *{[ } ]}</label>
					{[ } ]}
					<select>
						<option>0</option>
					</select>
				</div>
			{[ } ]}
			{[ if( field.time_type === "twelve" ) { ]}
				<div class="sui-col-md-4 fui-flex-end">
					<select>
						<option>AM</option>
						<option>PM</option>
					</select>
				</div>
			{[ } ]}
		</div>';
	}

	/**
	 * Field front-end markup
	 *
	 * @since 1.0
	 *
	 * @param $field
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function markup( $field, $settings = array() ) {
		$this->field = $field;
		$html        = '<div class="forminator-row forminator-row--time forminator-row--inner">';
		$id          = self::get_property( 'element_id', $field );
		$name        = $id;
		$required    = self::get_property( 'required', $field, false );
		$design      = $this->get_form_style( $settings );
		// backward compatibilty when time doesnt have field_type
		$field_type  = trim( self::get_property( 'field_type', $field, 'input' ) );
		$type        = trim( self::get_property( 'time_type', $field ) );
		$field_label = self::get_property( 'field_label', $field );

		//mark hours and minutes required markup as false
		if ( ! empty( $field_label ) ) {
			$required = false;
		}

		// Determinate field cols
		$cols = ( "twelve" === $type ) ? 4 : 6;

		/**
		 * Create hours field
		 */
		$hours = array(
			'type'        => 'number',
			'class'       => 'forminator-input forminator-input-time',
			'name'        => $id . '-hours',
			'id'          => $id . '-hours',
			'placeholder' => $this->sanitize_value( self::get_property( 'hh_placeholder', $field ) ),
			'min'         => ( "twelve" === $type ) ? '1' : '0',
			'max'         => ( "twelve" === $type ) ? '12' : '23',
		);

		$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );
		$html .= sprintf( '<div class="forminator-field forminator-field--inner">', $cols );

		if ( $required ) {
			$label = self::get_property( 'hh_label', $field );
			if ( ! empty( $label ) ) {
				$html .= '<div class="forminator-field--label">';
				$html .= '<label class="forminator-label">' . $label . ' <span class="wpdui-icon wpdui-icon-asterisk"></span></label>';
				$html .= '</div>';
			}
			if ( 'input' === $field_type ) {
				$html .= self::create_input( $hours, false, '', $required, $design );
			}
		} else {
			if ( 'input' === $field_type ) {
				$html .= self::create_input( $hours, self::get_property( 'hh_label', $field ), '', $required, $design );
			}
		}
		if ( 'select' === $field_type ) {
			$hours_data = array(
				'class' => 'forminator-time',
				'name'  => $id . '-hours',
				'id'    => $id . '-hours',
			);

			$html .= self::create_select( $hours_data, '', $this->get_hours( $type ), '', '', $required );
		}

		$html .= '</div>';
		$html .= '</div>';

		/**
		 * Create mintues field
		 */
		$minutes = array(
			'type'        => 'number',
			'class'       => 'forminator-input forminator-input-time',
			'name'        => $id . '-minutes',
			'id'          => $id . '-minutes',
			'placeholder' => $this->sanitize_value( self::get_property( 'mm_placeholder', $field ) ),
			'min'         => 0,
			'max'         => 59,
		);

		$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );
		$html .= sprintf( '<div class="forminator-field forminator-field--inner">', $cols );

		if ( $required ) {
			$label = self::get_property( 'mm_label', $field );
			if ( ! empty( $label ) ) {
				$html .= '<div class="forminator-field--label">';
				$html .= '<label class="forminator-label">' . $label . ' <span class="wpdui-icon wpdui-icon-asterisk"></span></label>';
				$html .= '</div>';
			}

			if ( 'input' === $field_type ) {
				$html .= self::create_input( $minutes, false, '', $required, $design );
			}
		} else {
			if ( 'input' === $field_type ) {
				$html .= self::create_input( $minutes, self::get_property( 'mm_label', $field ), '', $required, $design );
			}
		}
		if ( 'select' === $field_type ) {
			$minutes_data = array(
				'class' => 'forminator-time',
				'name'  => $id . '-minutes',
				'id'    => $id . '-minutes',
			);

			$html .= self::create_select( $minutes_data, '', $this->get_minutes(), '', '', $required );
		}

		$html .= '</div>';
		$html .= '</div>';

		if ( "twelve" === $type ) {
			/**
			 * Create AM/PM field
			 */
			$ampm = array(
				'class' => 'forminator-time',
				'name'  => $id . '-ampm',
				'id'    => $id . '-ampm',
			);

			$options = array(
				array(
					'value' => 'am',
					'label' => __( 'AM', Forminator::DOMAIN ),
				),
				array(
					'value' => 'pm',
					'label' => __( 'PM', Forminator::DOMAIN ),
				),
			);

			$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );
			$html .= sprintf( '<div class="forminator-field forminator-field--inner">', $cols );
			$html .= self::create_select( $ampm, '', $options );
			$html .= '</div>';
			$html .= '</div>';
		}

		// Close row div
		$html .= '</div>';

		return apply_filters( 'forminator_field_time_markup', $html, $field );
	}

	/**
	 * Return hours
	 *
	 * @since 1.0.5
	 *
	 * @param $type
	 *
	 * @return array
	 */
	public function get_hours( $type ) {
		$array = array();
		if ( 'twelve' === $type ) {
			$min = 1;
			$max = 12;
		} else {
			$min = 0;
			$max = 23;
		}

		for ( $i = $min; $i <= $max; $i ++ ) {
			$array[] = array(
				'label' => $i,
				'value' => $i
			);
		}

		return apply_filters( 'forminator_field_time_get_hours', $array, $this );
	}

	/**
	 * Return minutes
	 *
	 * @since 1.0.5
	 * @return array
	 */
	public function get_minutes() {
		$array = array();

		for ( $i = 0; $i < 60; $i ++ ) {
			$array[] = array(
				'label' => $i,
				'value' => $i
			);
		}

		return apply_filters( 'forminator_field_time_get_minutes', $array, $this );
	}

	/**
	 * Return field inline validation rules
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_rules() {
		$field = $this->field;
		$rules = '';

		if ( $this->is_required( $field ) ) {
			$rules .= '"' . $this->get_id( $field ) . '-hours": { required: true },' . "\n";
			$rules .= '"' . $this->get_id( $field ) . '-minutes": { required: true },' . "\n";
		}

		return $rules;
	}

	/**
	 * Return field inline validation errors
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_messages() {
		$field    = $this->field;
		$id       = self::get_property( 'element_id', $field );
		$messages = '';

		if ( $this->is_required( $field ) ) {
			// Hours validation
			$hours_message = apply_filters(
				'forminator_time_field_hours_required_validation_message',
				__( 'This field is required. Please input a valid hour', Forminator::DOMAIN ),
				$id,
				$field
			);
			$messages = '"' . $this->get_id( $field ) . '-hours": { required: "' . $hours_message . '" },' . "\n";

			// Minutes validation
			$minutes_message = apply_filters(
				'forminator_time_field_minutes_required_validation_message',
				__( 'This field is required. Please input a valid minute', Forminator::DOMAIN ),
				$id,
				$field
			);
			$messages .= '"' . $this->get_id( $field ) . '-minutes": { required: "' . $minutes_message . '" },' . "\n";
		}

		return $messages;
	}

	/**
	 * Field back-end validation
	 *
	 * @since 1.0
	 *
	 * @param array        $field
	 * @param array|string $data
	 */
	public function validate( $field, $data ) {
		if ( $this->is_required( $field ) ) {
			$id = self::get_property( 'element_id', $field );

			if ( empty( $data ) ) {
				$this->validation_message[ $id . '-hours' ] = apply_filters(
					'forminator_time_field_hours_required_validation_message',
					__( 'This field is required. Please input a valid hour', Forminator::DOMAIN ),
					$id,
					$field
				);
				$this->validation_message[ $id . '-minutes' ] = apply_filters(
					'forminator_time_field_minutes_required_validation_message',
					__( 'This field is required. Please input a valid minute', Forminator::DOMAIN ),
					$id,
					$field
				);
			} else {
				$hour   = isset( $data['hours'] ) ? $data['hours'] : '';
				$minute = isset( $data['minutes'] ) ? $data['minutes'] : '';
				$type   = self::get_property( 'time_type', $field );
				$hours_error_message = apply_filters(
					'forminator_time_field_minutes_validation_message',
					__( 'Please enter a valid hour', Forminator::DOMAIN ),
					$id,
					$field
				);
				$minutes_error_message = apply_filters(
					'forminator_time_field_minutes_validation_message',
					__( 'Please enter a valid minute', Forminator::DOMAIN ),
					$id,
					$field
				);
				if ( ! is_numeric( $hour ) || ! is_numeric( $minute ) ) {
					if ( ! is_numeric( $hour ) ) {
						$this->validation_message[ $id . '-hours' ] = $hours_error_message;
					}
					if ( ! is_numeric( $minute ) ) {
						$this->validation_message[ $id . '-minutes' ] = $minutes_error_message;
					}
				} else {
					// possible hour is string, because its sent from form data
					$hour       = (int) $hour;
					$min_hour   = 'twelve' === $type ? 1 : 0;
					$max_hour   = 'twelve' === $type ? 12 : 23;
					$max_minute = $hour >= 23 ? 0 : 59;

					if ( 0 === $hour ) {
						$max_minute = 0;
					}
					if ( $hour < $min_hour || $hour > $max_hour ) {
						$this->validation_message[ $id . '-hours' ] = $hours_error_message;
					}
					if ( $minute > $max_minute ) {
						$this->validation_message[ $id . '-minutes' ] = $minutes_error_message;
					}
				}
			}
		}
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
		// Sanitize
		$data = forminator_sanitize_field( $data );

		return apply_filters( 'forminator_field_time_sanitize', $data, $field );
	}
}