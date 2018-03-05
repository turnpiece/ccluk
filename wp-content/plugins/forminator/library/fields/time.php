<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Time
 *
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
			'time_type'      => 'twelve',
			'field_label'    => '',
			'hh_label'       => __( 'Hours', Forminator::DOMAIN ),
			'hh_placeholder' => __( 'E.g. 08', Forminator::DOMAIN ),
			'mm_label'       => __( 'Minutes', Forminator::DOMAIN ),
			'mm_placeholder' => __( 'E.g. 00', Forminator::DOMAIN ),
		);
	}

	/**
	 * Field admin markup
	 *
	 * @since 1.0
	 * @return string
	 */
	public function admin_html() {
		return '{[ if( field.field_label !== "" ) { ]}
			<div class="wpmudev-form-field--group">
				<label class="wpmudev-group--label">{{ encodeHtmlEntity( field.field_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
			</div>
		{[ } ]}
		<div class="wpmudev-form-field--grouped">
			<div class="wpmudev-form-field--group">
				{[ if( field.hh_label !== "" ) { ]}
					<label class="wpmudev-group--label">{{ encodeHtmlEntity( field.hh_label ) }}{[ if( field.field_label === "" && field.required == "true" ) { ]} *{[ } ]}</label>
				{[ } ]}
				<input class="wpmudev-input" placeholder="{{ encodeHtmlEntity( field.hh_placeholder ) }}" />
			</div>
			<div class="wpmudev-form-field--group">
				{[ if( field.mm_label !== "" ) { ]}
					<label class="wpmudev-group--label">{{ encodeHtmlEntity( field.mm_label ) }}{[ if( field.field_label === "" && field.required == "true" ) { ]} *{[ } ]}</label>
				{[ } ]}
				<input class="wpmudev-input" placeholder="{{ encodeHtmlEntity( field.mm_placeholder ) }}" />
			</div>
			{[ if( field.time_type === "twelve" ) { ]}
				<div class="wpmudev-form-field--group">
					<label class="wpmudev-group--label"></label>
					<select class="wpmudev-select">
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
	 *
	 * @return mixed
	 */
	public function markup( $field ) {
		$this->field = $field;
		$html        = '<div class="forminator-row forminator-row--inner forminator-align_btm">';
		$id          = $name = self::get_property( 'element_id', $field );
		$required    = self::get_property( 'required', $field, false );
		$placeholder = $this->sanitize_value( self::get_property( 'placeholder', $field ) );
		$type        = self::get_property( 'time_type', $field );
		$field_label = self::get_property( 'field_label', $field );

		//mark hours and minutes required markup as false
		if ( ! empty( $field_label ) ) {
			$required = false;
		}

		// Determinate field cols
		$cols = ( $type == "twelve" ) ? 4 : 6;

		/**
		 * Create hours field
		 */
		$hours = array(
			'type'        => 'number',
			'class'       => 'forminator-input forminator-input-time',
			'name'        => $id . '-hours',
			'id'          => $id . '-hours',
			'placeholder' => $this->sanitize_value( self::get_property( 'hh_placeholder', $field ) ),
			'min'         => ( $type == "twelve" ) ? '1' : '0',
			'max'         => ( $type == "twelve" ) ? '12' : '23',
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
			$html .= self::create_simple_input( $hours, false, $field );
		} else {
			$html .= self::create_input( $hours, self::get_property( 'hh_label', $field ) );
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
			$html .= self::create_simple_input( $minutes, false, $field );
		} else {
			$html .= self::create_input( $minutes, self::get_property( 'mm_label', $field ) );
		}

		$html .= '</div>';
		$html .= '</div>';

		if ( $type == "twelve" ) {
			/**
			 * Create AM/PM field
			 */
			$ampm = array(
				'class' => 'forminator-select',
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
		$messages = '';

		if ( $this->is_required( $field ) ) {
			$messages = '"' . $this->get_id( $field ) . '-hours": { required: "' . __( 'This field is required. Please input a valid hour', Forminator::DOMAIN ) . '" },' . "\n";
			$messages .= '"' . $this->get_id( $field ) . '-minutes": { required: "' . __( 'This field is required. Please input a valid minute', Forminator::DOMAIN ) . '" },' . "\n";
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
				$this->validation_message[ $id . '-hours' ]   = __( 'This field is required. Please enter a valid hour', Forminator::DOMAIN );
				$this->validation_message[ $id . '-minutes' ] = __( 'This field is required. Please enter a valid minute', Forminator::DOMAIN );
			} else {
				$hour   = isset( $data['hours'] ) ? $data['hours'] : '';
				$minute = isset( $data['minutes'] ) ? $data['minutes'] : '';
				$type   = self::get_property( 'time_type', $field );

				if ( ! is_numeric( $hour ) || ! is_numeric( $minute ) ) {
					if ( ! is_numeric( $hour ) ) {
						$this->validation_message[ $id . '-hours' ] = __( 'Please enter the hour', Forminator::DOMAIN );
					}
					if ( ! is_numeric( $minute ) ) {
						$this->validation_message[ $id . '-minutes' ] = __( 'Please enter the minute', Forminator::DOMAIN );
					}
				} else {
					$min_hour   = $type == 'twelve' ? 1 : 0;
					$max_hour   = $type == 'twelve' ? 12 : 23;
					$max_minute = $hour >= 23 ? 0 : 59;

					if ( $hour == 0 ) {
						$max_minute = 0;
					}
					if ( $hour < $min_hour || $hour > $max_hour ) {
						$this->validation_message[ $id . '-hours' ] = __( 'Please enter a valid hour', Forminator::DOMAIN );
					}
					if ( $minute > $max_minute ) {
						$this->validation_message[ $id . '-minutes' ] = __( 'Please enter a valid minute', Forminator::DOMAIN );
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