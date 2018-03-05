<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Name
 *
 * @since 1.0
 */
class Forminator_Name extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'name';

	/**
	 * @var string
	 */
	public $type = 'name';

	/**
	 * @var int
	 */
	public $position = 1;

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * @var string
	 */
	public $category = 'standard';

	/**
	 * Forminator_Name constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();
		$this->name = __( 'Name', Forminator::DOMAIN );
	}

	/**
	 * @since 1.0
	 * @param array $settings
	 *
	 * @return array
	 */
	public function load_settings( $settings = array() ) {
		return array(
			array(
				'id' => 'required',
				'type' => 'Toggle',
				'name' => 'required',
				'size' => 12,
				'className' => 'required-field',
				'hide_label' => true,
				'values' => array(
					array(
						'value' => "true",
						'label' => __( 'Required', Forminator::DOMAIN )
					)
				)
			),

			array(
				'id' => 'separator-1',
				'type' => 'Separator',
				'name' => 'separator',
				'hide_label' => true,
				'size' => 12,
				'className' => 'separator-field',
			),

			array(
				'id' => 'multiple-name',
				'type' => 'ToggleContainer',
				'name' => 'multiple_name',
				'size' => 12,
				'className' => 'toggle-container',
				'hide_label' => true,
				'hasOpposite' => '#single-name-field-label, #single-name-placeholder, #single-name-description',
				'values' => array(
					array(
						'value' => "true",
						'label' => __( 'Use multiple name fields', Forminator::DOMAIN )
					)
				),
				'fields' => array(

					array(
						'id' => 'prefix',
						'type' => 'MultiName',
						'name' => 'prefix',
						'size' => 12,
						'className' => 'multiname',
						'hide_label' => true,
						'values' => array(
							array(
								'value' => "true",
								'label' => __( 'Prefix', Forminator::DOMAIN )
							)
						),
						'fields' => array(
							array(
								'id' => 'prefix-label',
								'type' => 'Text',
								'name' => 'prefix_label',
								'className' => 'text-field',
								'label' => __( 'Label', Forminator::DOMAIN )
							),
							array(
								'id' => 'prefix-description',
								'type' => 'Text',
								'name' => 'prefix_description',
								'className' => 'text-field',
								'label' => __( 'Description (below field)', Forminator::DOMAIN )
							),
						)
					), // END prefix

					array(
						'id' => 'fname',
						'type' => 'MultiName',
						'name' => 'fname',
						'size' => 12,
						'className' => 'multiname',
						'hide_label' => true,
						'values' => array(
							array(
								'value' => "true",
								'label' => __( 'First Name', Forminator::DOMAIN )
							)
						),
						'fields' => array(
							array(
								'id' => 'fname-label',
								'type' => 'Text',
								'name' => 'fname_label',
								'className' => 'text-field',
								'label' => __( 'Label', Forminator::DOMAIN )
							),
							array(
								'id' => 'fname-placeholder',
								'type' => 'Text',
								'name' => 'fname_placeholder',
								'className' => 'text-field',
								'label' => __( 'Placeholder', Forminator::DOMAIN )
							),
							array(
								'id' => 'fname-description',
								'type' => 'Text',
								'name' => 'fname_description',
								'className' => 'text-field',
								'label' => __( 'Description (below field)', Forminator::DOMAIN )
							),
						)
					), // END first name

					array(
						'id' => 'mname',
						'type' => 'MultiName',
						'name' => 'mname',
						'size' => 12,
						'className' => 'multiname',
						'hide_label' => true,
						'values' => array(
							array(
								'value' => "true",
								'label' => __( 'Middle Name', Forminator::DOMAIN )
							)
						),
						'fields' => array(
							array(
								'id' => 'mname-label',
								'type' => 'Text',
								'name' => 'mname_label',
								'className' => 'text-field',
								'label' => __( 'Label', Forminator::DOMAIN )
							),
							array(
								'id' => 'mname-placeholder',
								'type' => 'Text',
								'name' => 'mname_placeholder',
								'className' => 'text-field',
								'label' => __( 'Placeholder', Forminator::DOMAIN )
							),
							array(
								'id' => 'mname-description',
								'type' => 'Text',
								'name' => 'mname_description',
								'className' => 'text-field',
								'label' => __( 'Description (below field)', Forminator::DOMAIN )
							),
						)
					), // END middle name

					array(
						'id' => 'lname',
						'type' => 'MultiName',
						'name' => 'lname',
						'size' => 12,
						'className' => 'multiname',
						'hide_label' => true,
						'values' => array(
							array(
								'value' => "true",
								'label' => __( 'Last Name', Forminator::DOMAIN )
							)
						),
						'fields' => array(
							array(
								'id' => 'lname-label',
								'type' => 'Text',
								'name' => 'lname_label',
								'className' => 'text-field',
								'label' => __( 'Label', Forminator::DOMAIN )
							),
							array(
								'id' => 'lname-placeholder',
								'type' => 'Text',
								'name' => 'lname_placeholder',
								'className' => 'text-field',
								'label' => __( 'Placeholder', Forminator::DOMAIN )
							),
							array(
								'id' => 'lname-description',
								'type' => 'Text',
								'name' => 'lname_description',
								'className' => 'text-field',
								'label' => __( 'Description (below field)', Forminator::DOMAIN )
							),
						)
					), // END last name
				)
			),

			array(
				'id' => 'separator-3',
				'type' => 'Separator',
				'name' => 'separator',
				'hide_label' => true,
				'size' => 12,
				'className' => 'separator-field',
			),

			array(
				'id' => 'single-name-field-label',
				'type' => 'Text',
				'name' => 'field_label',
				'hide_label' => false,
				'label'	=> __( 'Field label', Forminator::DOMAIN ),
				'size' => 12,
				'className' => 'text-field',
			),

			array(
				'id' => 'single-name-placeholder',
				'type' => 'Text',
				'name' => 'placeholder',
				'hide_label' => false,
				'label'	=> __( 'Field placeholder (optional)', Forminator::DOMAIN ),
				'size' => 12,
				'className' => 'text-field',
			),

			array(
				'id' => 'single-name-description',
				'type' => 'Text',
				'name' => 'description',
				'hide_label' => false,
				'label'	=> __( 'Description (below field)', Forminator::DOMAIN ),
				'size' => 12,
				'className' => 'text-field',
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
			'field_label'		=> __( 'Name', Forminator::DOMAIN ),
			'placeholder'		=> __( 'E.g. John Doe', Forminator::DOMAIN ),
			'prefix_label'		=> __( 'Prefix', Forminator::DOMAIN ),
			'fname_label'		=> __( 'First Name', Forminator::DOMAIN ),
			'fname_placeholder'	=> __( 'E.g. John', Forminator::DOMAIN ),
			'mname_label'		=> __( 'Middle Name', Forminator::DOMAIN ),
			'mname_placeholder'	=> __( 'E.g. Smith', Forminator::DOMAIN ),
			'lname_label'		=> __( 'Last Name', Forminator::DOMAIN ),
			'lname_placeholder'	=> __( 'E.g. Doe', Forminator::DOMAIN ),
			'prefix'            => "true",
			'fname'             => "true",
			'mname'             => "true",
			'lname'             => "true"
		);
	}

	/**
	 * Field admin markup
	 *
	 * @since 1.0
	 * @return string
	 */
	public function admin_html() {
		return '{[ if( field.multiple_name === "true" || field.multiple_name === true ) { ]}
			{[ if( field.prefix || field.fname ) { ]}
				<div class="wpmudev-form-field--grouped">
					{[ if( field.prefix ) { ]}
						<div class="wpmudev-form-field--group">
							{[ if( field.prefix_label !== "" ) { ]}
								<label class="wpmudev-group--label">{{ encodeHtmlEntity( field.prefix_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
							{[ } ]}
							<select class="wpmudev-select" style="width: 100%;" {{ field.required ? "required" : "" }}>
								<option>Mr</option>
								<option>Mrs</option>
								<option>Ms</option>
								<option>Miss</option>
							</select>
							{[ if( field.prefix_description !== "" ) { ]}
							<div class="wpmudev-group--info">
								<span class="wpmudev-info--text">{{ encodeHtmlEntity( field.prefix_description ) }}</span>
							</div>
							{[ } ]}
						</div>
					{[ } ]}
					{[ if( field.fname ) { ]}
						<div class="wpmudev-form-field--group">
							{[ if( field.fname_label !== "" ) { ]}
								<label class="wpmudev-group--label">{{ encodeHtmlEntity( field.fname_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
							{[ } ]}
							<input type="text" class="wpmudev-input" placeholder="{{ encodeHtmlEntity( field.fname_placeholder ) }}" {{ field.required ? "required" : "" }}>
							{[ if( field.fname_description !== "" ) { ]}
							<div class="wpmudev-group--info">
								<span class="wpmudev-info--text">{{ encodeHtmlEntity( field.fname_description ) }}</span>
							</div>
							{[ } ]}
						</div>
					{[ } ]}
				</div>
			{[ } ]}
			{[ if( field.mname || field.lname ) { ]}
				<div class="wpmudev-form-field--grouped">
					{[ if( field.mname ) { ]}
						<div class="wpmudev-form-field--group">
							{[ if( field.mname_label !== "" ) { ]}
								<label class="wpmudev-group--label">{{ encodeHtmlEntity( field.mname_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
							{[ } ]}
							<input type="text" class="wpmudev-input" placeholder="{{ encodeHtmlEntity( field.mname_placeholder ) }}" {{ field.required ? "required" : "" }}>
							{[ if( field.mname_description !== "" ) { ]}
							<div class="wpmudev-group--info">
								<span class="wpmudev-info--text">{{ encodeHtmlEntity( field.mname_description ) }}</span>
							</div>
							{[ } ]}
						</div>
					{[ } ]}
					{[ if( field.lname ) { ]}
						<div class="wpmudev-form-field--group">
							{[ if( field.lname_label !== "" ) { ]}
								<label class="wpmudev-group--label">{{ encodeHtmlEntity( field.lname_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
							{[ } ]}
							<input type="text" class="wpmudev-input" placeholder="{{ encodeHtmlEntity( field.lname_placeholder ) }}" {{ field.required ? "required" : "" }}>
							{[ if( field.lname_description !== "" ) { ]}
							<div class="wpmudev-group--info">
								<span class="wpmudev-info--text">{{ encodeHtmlEntity( field.lname_description ) }}</span>
							</div>
							{[ } ]}
						</div>
					{[ } ]}
				</div>
			{[ } ]}
		{[ } else { ]}
			<div class="wpmudev-form-field--group">
				{[ if( field.field_label !== "" ) { ]}
					<label class="wpmudev-group--label">{{ encodeHtmlEntity( field.field_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
				{[ } ]}
				<input type="text" class="wpmudev-input" placeholder="{{ encodeHtmlEntity( field.placeholder ) }}" {{ field.required ? "required" : "" }}>
				{[ if( field.description !== "" ) { ]}
				<div class="wpmudev-group--info">
					<span class="wpmudev-info--text">{{ encodeHtmlEntity( field.description ) }}</span>
				</div>
				{[ } ]}
			</div>
		{[ } ]}';
	}

	/**
	 * Return simple field markup
	 *
	 * @since 1.0
	 * @param $field
	 *
	 * @return string
	 */
	public function get_simple( $field ) {
		$id = $name = self::get_property( 'element_id', $field );
		$required = self::get_property( 'required', $field, false );
		$placeholder = $this->sanitize_value( self::get_property( 'placeholder', $field ) );

		return sprintf( '<input class="forminator-name--field forminator-input" type="text" data-required="%s" name="%s" placeholder="%s" />', $required, $name, $placeholder );
	}

	/**
	 * Return multi field first row markup
	 *
	 * @since 1.0
	 * @param $field
	 *
	 * @return string
	 */
	public function get_multi_first_row( $field ) {
		$cols        	= 12;
		$html        	= '';
		$id = $name  	= self::get_property( 'element_id', $field );
		$required		= self::get_property( 'required', $field, false );
		$prefix 		= self::get_property( 'prefix', $field, false );
		$fname 	  		= self::get_property( 'fname', $field, false );

		// If both prefix & first name are disabled, return
		if( ! $prefix && ! $fname ) return '';

		// If both prefix & first name are enabled, change cols
		if( $prefix && $fname ) {
			$cols = 6;
		}

		if( $prefix ) {
			/**
			 * Create prefix field
			 */
			$prefix_data = array(
				'class' => 'forminator-select',
				'name' => $id . '-prefix',
				'id' => $id . '-prefix',
			);

			$options = array();
			$prefix_options = forminator_get_name_prefixes();

			foreach( $prefix_options as $key => $pfx ) {
				$options[] = array(
					'value' => $key,
					'label' => $pfx
				);
			}

			$html .= '<div class="forminator-row forminator-row--inner">';
			$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );
			$html .= '<div class="forminator-field forminator-field--inner">';

			if ( $required ) {
				$label = self::get_property( 'prefix_label', $field );
				$asterisk = '<i class="wpdui-icon wpdui-icon-asterisk" aria-hidden="true"></i>';
				if ( ! empty( $label ) ) {
					$html .= '<div class="forminator-field--label">';
					$html .= '<label class="forminator-label">' . $label . ' ' . $asterisk . '</label>';
					$html .= '</div>';
				}
				$html .= self::create_simple_select( $prefix_data, $options, self::get_property( 'prefix_placeholder', $field ), self::get_property( 'prefix_description', $field ) );
			} else {
				$html .= self::create_select( $prefix_data, self::get_property( 'prefix_label', $field ), $options, self::get_property( 'prefix_placeholder', $field ), self::get_property( 'prefix_description', $field ) );
			}

			$html .= '</div>';
			$html .= '</div>';

			if ( ! $fname ) {
				$html .= '</div>';
			}
		}

		if( $fname ) {
			/**
			 * Create first name field
			 */
			$first_name = array(
				'type' => 'text',
				'class' => 'forminator-input',
				'name' => $id . '-first-name',
				'id' => $id . '-first-name',
				'placeholder' => $this->sanitize_value( self::get_property( 'fname_placeholder', $field ) ),
			);

			if ( ! $prefix ) {
				$html .= '<div class="forminator-row forminator-row--inner">';
			}

			$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );
			$html .= '<div class="forminator-field forminator-field--inner">';

			if ( $required ) {
				$label = self::get_property( 'fname_label', $field );
				$asterisk = '<i class="wpdui-icon wpdui-icon-asterisk" aria-hidden="true"></i>';
				if ( ! empty( $label ) ) {
					$html .= '<div class="forminator-field--label">';
					$html .= '<label class="forminator-label">' . $label . ' ' . $asterisk . '</label>';
					$html .= '</div>';
				}
				$html .= self::create_simple_input( $first_name, self::get_property( 'fname_description', $field ) );
			} else {
				$html .= self::create_input( $first_name, self::get_property( 'fname_label', $field ), self::get_property( 'fname_description', $field ) );
			}

			$html .= '</div>';
			$html .= '</div>';
			$html .= '</div>';
		}

		return $html;
	}

	/**
	 * Return multi field second row markup
	 *
	 * @since 1.0
	 * @param $field
	 *
	 * @return string
	 */
	public function get_multi_second_row( $field ) {
		$cols        = 12;
		$html        = '';
		$id = $name  = self::get_property( 'element_id', $field );
		$required	  = self::get_property( 'required', $field, false );
		$mname 	  = self::get_property( 'mname', $field, false );
		$lname 	  = self::get_property( 'lname', $field, false );

		// If both prefix & first name are disabled, return
		if( ! $mname && ! $lname ) return '';

		// If both prefix & first name are enabled, change cols
		if( $mname && $lname ) {
			$cols = 6;
		}

		if( $mname ) {
			/**
			 * Create middle name field
			 */
			$middle_name = array(
				'type' => 'text',
				'class' => 'forminator-input',
				'name' => $id . '-middle-name',
				'id' => $id . '-middle-name',
				'placeholder' => $this->sanitize_value( self::get_property( 'mname_placeholder', $field ) ),
			);

			$html .= '<div class="forminator-row forminator-row--inner">';
			$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );
			$html .= '<div class="forminator-field forminator-field--inner">';

			if ( $required ) {
				$label = self::get_property( 'mname_label', $field );
				$asterisk = '<i class="wpdui-icon wpdui-icon-asterisk" aria-hidden="true"></i>';
				if ( ! empty( $label ) ) {
					$html .= '<div class="forminator-field--label">';
					$html .= '<label class="forminator-label">' . $label . ' ' . $asterisk . '</label>';
					$html .= '</div>';
				}
				$html .= self::create_simple_input( $middle_name, self::get_property( 'mname_description', $field ) );
			} else {
				$html .= self::create_input( $middle_name, self::get_property( 'mname_label', $field ), self::get_property( 'mname_description', $field ) );
			}

			$html .= '</div>';
			$html .= '</div>';

			if ( ! $lname ) {
				$html .= '</div>';
			}
		}

		if( $lname ) {
			/**
			 * Create last name field
			 */
			$last_name = array(
				'type' => 'text',
				'class' => 'forminator-input',
				'name' => $id . '-last-name',
				'id' => $id . '-last-name',
				'placeholder' => $this->sanitize_value( self::get_property( 'lname_placeholder', $field ) ),
			);

			if ( ! $mname ) {
				$html .= '<div class="forminator-row forminator-row--inner">';
			}

			$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );
			$html .= '<div class="forminator-field forminator-field--inner">';

			if ( $required ) {
				$label = self::get_property( 'lname_label', $field );
				$asterisk = '<i class="wpdui-icon wpdui-icon-asterisk" aria-hidden="true"></i>';
				if ( ! empty( $label ) ) {
					$html .= '<div class="forminator-field--label">';
					$html .= '<label class="forminator-label">' . $label . ' ' . $asterisk . '</label>';
					$html .= '</div>';
				}
				$html .= self::create_simple_input( $last_name, self::get_property( 'lname_description', $field ) );
			} else {
				$html .= self::create_input( $last_name, self::get_property( 'lname_label', $field ), self::get_property( 'lname_description', $field ) );
			}

			$html .= '</div>';
			$html .= '</div>';
			$html .= '</div>';
		}

		return $html;
	}

	/**
	 * Field front-end markup
	 *
	 * @since 1.0
	 * @param $field
	 *
	 * @return mixed
	 */
	public function markup( $field ) {
		$this->field = $field;
		$multiple 	 = self::get_property( 'multiple_name', $field, false );

		// Check we use multi fields
		if( ! $multiple ) {
			// Only one field
			$html = $this->get_simple( $field );
		} else {
			// Multiple fields
			$html = $this->get_multi_first_row( $field );
			$html .= $this->get_multi_second_row( $field );
		}

		return apply_filters( 'forminator_field_name_markup', $html, $field );
	}

	/**
	 * Return field inline validation rules
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_rules() {
		$rules    = '';
		$field    = $this->field;
		$multiple = self::get_property( 'multiple_name', $field, false );

		if( $this->is_required( $field ) ) {
			// Check we use multi fields
			if( ! $multiple ) {
				$rules = '"' . $this->get_id( $field ) . '": "required",';
			} else {
				$rules = '"' . $this->get_id( $field ) . '-first-name": "required",';
				$rules .= '"' . $this->get_id( $field ) . '-middle-name": "required",';
				$rules .= '"' . $this->get_id( $field ) . '-last-name": "required",';
			}
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
		$multiple = self::get_property( 'multiple_name', $field, false );
		$messages = '';

		if( $this->is_required( $field ) ) {
			if( ! $multiple ) {
				$messages = '"' . $this->get_id( $field ) . '": "' . __( 'This field is required. Please input a value', Forminator::DOMAIN ) . '",' . "\n";
			} else {
				$messages = '"' . $this->get_id( $field ) . '-first-name": "' . __( 'This field is required. Please input a value', Forminator::DOMAIN ) . '",' . "\n";
				$messages .= '"' . $this->get_id( $field ) . '-middle-name": "' . __( 'This field is required. Please input a value', Forminator::DOMAIN ) . '",' . "\n";
				$messages .= '"' . $this->get_id( $field ) . '-last-name": "' . __( 'This field is required. Please input a value', Forminator::DOMAIN ) . '",' . "\n";
			}
		}

		return $messages;
	}

	/**
	 * Field back-end validation
	 *
	 * @since 1.0
	 * @param array $field
	 * @param array|string $data
	 */
	public function validate( $field, $data ) {
		if ( $this->is_required( $field ) ) {
			$id 		= self::get_property( 'element_id', $field );
			$multiple 	= self::get_property( 'multiple_name', $field, false );
			if ( empty( $data ) ) {
				$this->validation_message[ $id ] = __( 'This field is required. Please input your name', Forminator::DOMAIN );
			} else {
				if ( $multiple && is_array( $data ) ) {
					$fname 	  	= self::get_property( 'fname', $field, false );
					$mname 	  	= self::get_property( 'mname', $field, false );
					$lname 	  	= self::get_property( 'lname', $field, false );
					$firstname 	= isset( $data['first-name'] ) ? $data['first-name'] : '';
					$middlename = isset( $data['middle-name'] ) ? $data['middle-name'] : '';
					$lastname 	= isset( $data['last-name'] ) ? $data['last-name'] : '';

					if ( $fname && empty( $firstname ) ) {
						$this->validation_message[ $id . '-first-name' ] = __( 'This field is required. Please input your first name', Forminator::DOMAIN );
					}
					if ( $mname && empty( $middlename ) ) {
						$this->validation_message[ $id . '-middle-name' ] = __( 'This field is required. Please input your middle name', Forminator::DOMAIN );
					}
					if ( $lname && empty( $lastname ) ) {
						$this->validation_message[ $id . '-last-name' ] = __( 'This field is required. Please input your last name', Forminator::DOMAIN );
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

		return apply_filters( 'forminator_field_name_sanitize', $data, $field );
	}
}