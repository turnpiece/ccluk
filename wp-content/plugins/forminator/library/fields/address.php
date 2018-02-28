<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Address
 *
 * @since 1.0
 */
class Forminator_Address extends Forminator_Field {

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $slug = 'address';

	/**
	 * @var int
	 */
	public $position = 5;

	 /**
     * @var string
     */
    public $type = 'address';

	 /**
     * @var array
     */
    public $options = array();

    /**
     * @var string
     */
    public $category = 'standard';

	/**
	 * Forminator_Address constructor.
	 *
	 * @since 1.0
	 */
    public function __construct() {
        parent::__construct();
        $this->name = __( 'Address', Forminator::DOMAIN );
    }

    /**
	 * @param array $settings
	 *
     * @since 1.0
	 * @return array
	 */
    public function load_settings( $settings = array() ) {
        return array(
            array(
				'id' => 'required',
				'type' => 'Toggle',
				'name' => 'required',
				'hide_label' => true,
				'values' => array(
					array(
						'value' => "true",
						'label' => __( 'Required', Forminator::DOMAIN ),
						'labelSmall' => "true"
					)
				)
            ),

            array(
				'id' => 'separator',
				'type' => 'Separator',
				'hide_label' => true,
			),

            array(
				'id' => 'street-address',
				'type' => 'MultiName',
				'name' => 'street_address',
				'hide_label' => true,
				'values' => array(
					array(
						'value' => "true",
						'label' => __( 'Street address', Forminator::DOMAIN )
					)
				),
				'fields' => array(
					array(
						'id' => 'street-address-label',
						'type' => 'Text',
						'name' => 'street_address_label',
						'label' => __( 'Label', Forminator::DOMAIN )
					),
					array(
						'id' => 'street-address-placeholder',
						'type' => 'Text',
						'name' => 'street_address_placeholder',
						'label' => __( 'Placeholder', Forminator::DOMAIN )
					),
				)
			),

			array(
				'id' => 'address-line',
				'type' => 'MultiName',
				'name' => 'address_line',
				'hide_label' => true,
				'values' => array(
					array(
						'value' => "true",
						'label' => __( 'Address Line 2', Forminator::DOMAIN )
					)
				),
				'fields' => array(
					array(
						'id' => 'address-line-label',
						'type' => 'Text',
						'name' => 'address_line_label',
						'label' => __( 'Label', Forminator::DOMAIN )
					),
					array(
						'id' => 'address-line-placeholder',
						'type' => 'Text',
						'name' => 'address_line_placeholder',
						'label' => __( 'Placeholder', Forminator::DOMAIN )
					),
				)
			),

			array(
				'id' => 'address-city',
				'type' => 'MultiName',
				'name' => 'address_city',
				'hide_label' => true,
				'values' => array(
					array(
						'value' => "true",
						'label' => __( 'City', Forminator::DOMAIN )
					)
				),
				'fields' => array(
					array(
						'id' => 'address-city-label',
						'type' => 'Text',
						'name' => 'address_city_label',
						'label' => __( 'Label', Forminator::DOMAIN )
					),
					array(
						'id' => 'address-city-placeholder',
						'type' => 'Text',
						'name' => 'address_city_placeholder',
						'label' => __( 'Placeholder', Forminator::DOMAIN )
					),
				)
			),

			array(
				'id' => 'address-state',
				'type' => 'MultiName',
				'name' => 'address_state',
				'hide_label' => true,
				'values' => array(
					array(
						'value' => "true",
						'label' => __( 'State / Province', Forminator::DOMAIN )
					)
				),
				'fields' => array(
					array(
						'id' => 'address-state-label',
						'type' => 'Text',
						'name' => 'address_state_label',
						'className' => 'text-field',
						'label' => __( 'Label', Forminator::DOMAIN )
					),
					array(
						'id' => 'address-state-placeholder',
						'type' => 'Text',
						'name' => 'address_state_placeholder',
						'className' => 'text-field',
						'label' => __( 'Placeholder', Forminator::DOMAIN )
					),
				)
			),

			array(
				'id' => 'address-zip',
				'type' => 'MultiName',
				'name' => 'address_zip',
				'hide_label' => true,
				'values' => array(
					array(
						'value' => "true",
						'label' => __( 'ZIP / Postal Code', Forminator::DOMAIN )
					)
				),
				'fields' => array(
					array(
						'id' => 'address-zip-label',
						'type' => 'Text',
						'name' => 'address_zip_label',
						'label' => __( 'Label', Forminator::DOMAIN )
					),
					array(
						'id' => 'address-zip-placeholder',
						'type' => 'Text',
						'name' => 'address_zip_placeholder',
						'label' => __( 'Placeholder', Forminator::DOMAIN )
					),
				)
			),

			array(
				'id' => 'address-country',
				'type' => 'MultiName',
				'name' => 'address_country',
				'hide_label' => true,
				'values' => array(
					array(
						'value' => "true",
						'label' => __( 'Country', Forminator::DOMAIN )
					)
				),
				'fields' => array(
					array(
						'id' => 'address-country-label',
						'type' => 'Text',
						'name' => 'address_country_label',
						'label' => __( 'Label', Forminator::DOMAIN )
					),
				)
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
			'street_address'             => "true",
			'address_city'               => "true",
			'address_state'              => "true",
			'address_zip'                => "true",
			'address_country'            => "true",
			'street_address_label'       => __( 'Street address', Forminator::DOMAIN ),
			'street_address_placeholder' => __( 'E.g. 42 Wallaby Way', Forminator::DOMAIN ),
			'address_city_label'         => __( 'City', Forminator::DOMAIN ),
			'address_city_placeholder'   => __( 'E.g. Sydney', Forminator::DOMAIN ),
			'address_state_label'        => __( 'State/Province', Forminator::DOMAIN ),
			'address_state_placeholder'  => __( 'E.g. New South Wales', Forminator::DOMAIN ),
			'address_zip_label'          => __( 'ZIP / Postal Code', Forminator::DOMAIN ),
			'address_zip_placeholder'    => __( 'E.g. 2000', Forminator::DOMAIN ),
			'address_country_label'      => __( 'Country', Forminator::DOMAIN ),
		);
	}

	/**
	 * Field admin markup
	 *
	 * @since 1.0
	 * @return string
	 */
	public function admin_html() {
		return '{[ if( field.street_address == "true" ) { ]}
			<div class="wpmudev-form-field--grouped">
				<div class="wpmudev-form-field--group">
					{[ if( field.street_address_label !== "" ) { ]}
						<label class="wpmudev-group--label">{{ encodeHtmlEntity( field.street_address_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
					{[ } ]}
					<input type="text" class="wpmudev-input" placeholder="{{ encodeHtmlEntity( field.street_address_placeholder ) }}" {{ field.required ? "required" : "" }}>
				</div>
			</div>
		{[ } ]}
		{[ if( field.address_line == "true" ) { ]}
			<div class="wpmudev-form-field--grouped">
				<div class="wpmudev-form-field--group">
					{[ if( field.address_line_label !== "" ) { ]}
						<label class="wpmudev-group--label">{{ encodeHtmlEntity( field.address_line_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
					{[ } ]}
					<input type="text" class="wpmudev-input" placeholder="{{ encodeHtmlEntity( field.address_line_placeholder ) }}" {{ field.required ? "required" : "" }}>
				</div>
			</div>
		{[ } ]}
		{[ if( field.address_city == "true" || field.address_state == "true" ) { ]}
			<div class="wpmudev-form-field--grouped">
				{[ if( field.address_city == "true" ) { ]}
					<div class="wpmudev-form-field--group">
						{[ if( field.address_city_label !== "" ) { ]}
							<label class="wpmudev-group--label">{{ encodeHtmlEntity( field.address_city_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
						{[ } ]}
						<input type="text" class="wpmudev-input" placeholder="{{ encodeHtmlEntity( field.address_city_placeholder ) }}" {{ field.required ? "required" : "" }}>
					</div>
				{[ } ]}
				{[ if( field.address_state == "true" ) { ]}
					<div class="wpmudev-form-field--group">
						{[ if( field.address_state_label !== "" ) { ]}
							<label class="wpmudev-group--label">{{ encodeHtmlEntity( field.address_state_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
						{[ } ]}
						<input type="text" class="wpmudev-input" placeholder="{{ encodeHtmlEntity( field.address_state_placeholder ) }}" {{ field.required ? "required" : "" }}>
					</div>
				{[ } ]}
			</div>
		{[ } ]}
		{[ if( field.address_zip == "true" || field.address_country == "true" ) { ]}
			<div class="wpmudev-form-field--grouped">
				{[ if( field.address_zip == "true" ) { ]}
					<div class="wpmudev-form-field--group">
						{[ if( field.address_zip_label !== "" ) { ]}
							<label class="wpmudev-group--label">{{ encodeHtmlEntity( field.address_zip_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
						{[ } ]}
						<input type="number" class="wpmudev-input" placeholder="{{ encodeHtmlEntity( field.address_zip_placeholder ) }}" {{ field.required ? "required" : "" }}>
					</div>
				{[ } ]}
				{[ if( field.address_country == "true" ) { ]}
					<div class="wpmudev-form-field--group">
						{[ if( field.address_country_label !== "" ) { ]}
							<label class="wpmudev-group--label">{{ encodeHtmlEntity( field.address_country_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
						{[ } ]}
						<select class="wpmudev-select" style="width: 100%;" {{ field.required ? "required" : "" }}>
							{[ _.each( field.options, function( value, key ){ ]}
								<option>{{ value.label }}</option>
							{[ }) ]}
						</select>
					</div>
				{[ } ]}
			</div>
		{[ } ]}';
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

		// Address
		$html = $this->get_address( $field, 'street_address' );

		// Second Address
		$html .= $this->get_address( $field, 'address_line' );

		// City & State fields
		$html .= $this->get_city_state( $field );

		// ZIP & Country fields
		$html .= $this->get_zip_country( $field );

		return apply_filters( 'forminator_field_address_markup', $html, $field );
	}

	/**
	 * Return address input markup
	 *
	 * @since 1.0
	 * @param $field
	 * @param $slug
	 *
	 * @return string
	 */
	public function get_address( $field, $slug ) {
		$cols 		  	= 12;
		$html        	= '';
		$id = $name 	= self::get_property( 'element_id', $field );
		$required		= self::get_property( 'required', $field, false );
		$enabled		= self::get_property( $slug, $field );

		if( ! $enabled ) return '';

		 /**
		 * Create address field
		 */
		$address = array(
			 'type' 		=> 'text',
			 'class' 		=> 'forminator-input',
			 'name' 		=> $name . '-' . $slug,
			 'id'			=> $name  . '-' . $slug,
			 'placeholder' 	=> self::get_property( $slug . '_placeholder', $field ),
		);

		// Address field markup
		$html .= '<div class="forminator-row forminator-row--inner">';
		$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );
		$html .= '<div class="forminator-field forminator-field--inner">';

		if ( $required ) {
			$label = self::get_property( $slug .  '_label', $field );
			$asterisk = '<i class="wpdui-icon wpdui-icon-asterisk" aria-hidden="true"></i>';
			if ( ! empty( $label ) ) {
				$html .= '<div class="forminator-field--label">';
				$html .= '<label class="forminator-label">' . $label . ' ' . $asterisk . '</label>';
				$html .= '</div>';
			}
			$html .= self::create_simple_input( $address );
		} else {
			$html .= self::create_input( $address, self::get_property( $slug .  '_label', $field ) );
		}

		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Return City and State fields markup
	 *
	 * @since 1.0
	 * @param $field
	 *
	 * @return string
	 */
	public function get_city_state( $field ) {
		$cols        = 12;
		$html        = '';
		$id = $name  = self::get_property( 'element_id', $field );
		$required	 = self::get_property( 'required', $field, false );
		$city 	     = self::get_property( 'address_city', $field, false );
		$state 	  	 = self::get_property( 'address_state', $field, false );

		 // If both prefix & first name are disabled, return
		if( ! $city && ! $state ) return '';

		 // If both prefix & first name are enabled, change cols
		if( $city && $state ) {
			$cols = 6;
		}

		if( $city ) {
			/**
			* Create city field
			*/
			$city_data = array(
				'type' 			=> 'text',
				'class' 		=> 'forminator-input',
				'name' 			=> $id . '-city',
				'id' 			=> $id . '-city',
				'placeholder' 	=> self::get_property( 'address_city_placeholder', $field ),
			);

			// City markup
			$html .= '<div class="forminator-row forminator-row--inner">';
			$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );
			$html .= '<div class="forminator-field forminator-field--inner">';

			if ( $required ) {
				$label = self::get_property( 'address_city_label', $field );
				$asterisk = '<i class="wpdui-icon wpdui-icon-asterisk" aria-hidden="true"></i>';
				if ( ! empty( $label ) ) {
					$html .= '<div class="forminator-field--label">';
					$html .= '<label class="forminator-label">' . $label . ' ' . $asterisk . '</label>';
					$html .= '</div>';
				}
				$html .= self::create_simple_input( $city_data );
			} else {
				$html .= self::create_input( $city_data, self::get_property( 'address_city_label', $field ) );
			}

			$html .= '</div>';
			$html .= '</div>';

			if ( ! $state ) {
				$html .= '</div>';
			}
		}

		if( $state ) {
			/**
			* Create state field
			*/
			$state_data = array(
				'type' 			=> 'text',
				'class' 		=> 'forminator-input',
				'name' 			=> $id . '-state',
				'id' 			=> $id . '-state',
				'placeholder' 	=> self::get_property( 'address_state_placeholder', $field ),
			);

			if ( ! $city ) {
				$html .= '<div class="forminator-row forminator-row--inner">';
			}

			// State markup
			$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );
			$html .= '<div class="forminator-field forminator-field--inner">';

			if ( $required ) {
				$label = self::get_property( 'address_state_label', $field );
				$asterisk = '<i class="wpdui-icon wpdui-icon-asterisk" aria-hidden="true"></i>';
				if ( ! empty( $label ) ) {
					$html .= '<div class="forminator-field--label">';
					$html .= '<label class="forminator-label">' . $label . ' ' . $asterisk . '</label>';
					$html .= '</div>';
				}
				$html .= self::create_simple_input( $state_data );
			} else {
				$html .= self::create_input( $state_data, self::get_property( 'address_state_label', $field ) );
			}

			$html .= '</div>';
			$html .= '</div>';
			$html .= '</div>';
		}

		return $html;
	}

	/**
	 * Return Zip and County inputs
	 *
	 * @since 1.0
	 * @param $field
	 *
	 * @return string
	 */
	public function get_zip_country( $field ) {
		$cols           	= 12;
		$html           	= '';
		$id = $name     	= self::get_property( 'element_id', $field );
		$required	 		= self::get_property( 'required', $field, false );
		$address_zip 	  	= self::get_property( 'address_zip', $field, false );
		$address_country 	= self::get_property( 'address_country', $field, false );

		 // If both prefix & first name are disabled, return
		if( ! $address_zip && ! $address_country ) return '';

		 // If both prefix & first name are enabled, change cols
		if( $address_zip && $address_country ) {
			$cols = 6;
		}

		if( $address_zip ) {
			/**
			* Create first name field
			*/
			$zip_data = array(
				'type' 			=> 'text',
				'class' 		=> 'forminator-input',
				'name' 			=> $id . '-zip',
				'id' 			=> $id . '-zip',
				'placeholder' 	=> self::get_property( 'address_zip_placeholder', $field ),
			);

			$html .= '<div class="forminator-row forminator-row--inner">';
			$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );
			$html .= '<div class="forminator-field forminator-field--inner">';

			if ( $required ) {
				$label = self::get_property( 'address_zip_label', $field );
				$asterisk = '<i class="wpdui-icon wpdui-icon-asterisk" aria-hidden="true"></i>';
				if ( ! empty( $label ) ) {
					$html .= '<div class="forminator-field--label">';
					$html .= '<label class="forminator-label">' . $label . ' ' . $asterisk . '</label>';
					$html .= '</div>';
				}
				$html .= self::create_simple_input( $zip_data );
			} else {
				$html .= self::create_input( $zip_data, self::get_property( 'address_zip_label', $field ) );
			}

			$html .= '</div>';
			$html .= '</div>';

			if ( ! $address_country ) {
				$html .= '</div>';
			}
		}

		if( $address_country ) {
			 /**
			 * Create prefix field
			 */
			$country_data = array(
				 'class'	=> 'forminator-select',
				 'name' 	=> $id . '-country',
				 'id' 		=> $id . '-country',
			);

			if ( ! $address_zip ) {
				$html .= '<div class="forminator-row">';
			}

			$options = forminator_to_field_array( forminator_get_countries_list() );
			$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );
			$html .= '<div class="forminator-field forminator-field--inner">';

			if ( $required ) {
				$label = self::get_property( 'address_country_label', $field );
				$asterisk = '<i class="wpdui-icon wpdui-icon-asterisk" aria-hidden="true"></i>';
				if ( ! empty( $label ) ) {
					$html .= '<div class="forminator-field--label">';
					$html .= '<label class="forminator-label">' . $label . ' ' . $asterisk . '</label>';
					$html .= '</div>';
				}
				$html .= self::create_simple_select( $country_data, $options, self::get_property( 'address_country_placeholder', $field ) );
			} else {
				$html .= self::create_select( $country_data, self::get_property( 'address_country_label', $field ), $options, self::get_property( 'address_country_placeholder', $field ) );
			}

			$html .= '</div>';
			$html .= '</div>';
			$html .= '</div>';
		}

		return $html;
	}

	/**
	 * Return field inline validation rules
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_rules() {
		$field    = $this->field;
		$multiple = self::get_property( 'multiple_name', $field, false );
		$rules = '';

		if( $this->is_required( $field ) ) {
			$rules .= '"' . $this->get_id( $field ) . '-street_address": "required",';
			$rules .= '"' . $this->get_id( $field ) . '-city": "required",';
			$rules .= '"' . $this->get_id( $field ) . '-state": "required",';
			$rules .= '"' . $this->get_id( $field ) . '-zip": "required",';
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
			$messages .= '"' . $this->get_id( $field ) . '-street_address": "' . __( 'This field is required. Please input a value', Forminator::DOMAIN ) . '",' . "\n";
			$messages .= '"' . $this->get_id( $field ) . '-city": "' . __( 'This field is required. Please input a value', Forminator::DOMAIN ) . '",' . "\n";
			$messages .= '"' . $this->get_id( $field ) . '-state": "' . __( 'This field is required. Please input a value', Forminator::DOMAIN ) . '",' . "\n";
			$messages .= '"' . $this->get_id( $field ) . '-zip": "' . __( 'This field is required. Please input a value', Forminator::DOMAIN ) . '",' . "\n";
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
			$id = self::get_property( 'element_id', $field );
			if ( empty( $data ) ) {
				$this->validation_message[ $id ] = __( 'This field is required. Please enter the address', Forminator::DOMAIN );
			} else  {
				if ( is_array( $data ) ) {
					//add street address
					$address_street 	= self::get_property( 'street_address', $field, false );
					$address_zip 	  	= self::get_property( 'address_zip', $field, false );
					$address_country 	= self::get_property( 'address_country', $field, false );
					$address_city 	    = self::get_property( 'address_city', $field, false );
					$address_state 	  	= self::get_property( 'address_state', $field, false );
					$street			 	= isset( $data['street_address'] ) ? $data['street_address'] : '';
					$zip 				= isset( $data['zip'] ) ? $data['zip'] : '';
					$country 			= isset( $data['country'] ) ? $data['country'] : '';
					$city 				= isset( $data['city'] ) ? $data['city'] : '';
					$state 				= isset( $data['state'] ) ? $data['state'] : '';
					if ( $address_street && empty( $street ) ) {
						$this->validation_message[ $id . '-street_address' ] = __( 'This field is required. Please enter the street address',  Forminator::DOMAIN );
					}
					if ( $address_zip && empty( $zip ) ) {
						$this->validation_message[ $id . '-zip' ] = __( 'This field is required. Please enter the zip code',  Forminator::DOMAIN );
					}
					if ( $address_country && empty( $country ) && $country !== '0' ) {
						$this->validation_message[ $id . '-country' ] = __( 'This field is required. Please select the country',  Forminator::DOMAIN );
					}
					if ( $address_city && empty( $city ) ) {
						$this->validation_message[ $id . '-city' ] = __( 'This field is required. Please enter the city',  Forminator::DOMAIN );
					}
					if ( $address_state && empty( $state ) ) {
						$this->validation_message[ $id . '-state' ] = __( 'This field is required. Please enter the state',  Forminator::DOMAIN );
					}
				}
			}
		}
	}
}