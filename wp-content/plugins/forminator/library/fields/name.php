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
				'size'       => 12,
				'className'  => 'required-field',
				'hide_label' => true,
				'values'     => array(
					array(
						'value' => "true",
						'label' => __( 'Required', Forminator::DOMAIN ),
					),
				),
			),

			array(
				'id'			=> 'multiple-name',
				'type'			=> 'ToggleContainer',
				'name'			=> 'multiple_name',
				'clean'			=> true,
				'hide_label'	=> true,
				'hasOpposite'	=> '#single-name-field-label, #single-name-placeholder, #single-name-description',
				'values'		=> array(
					array(
						'value' => "true",
						'label' => __( 'Use multiple name fields', Forminator::DOMAIN ),
					),
				),
				'fields'      => array(

					array(
						'id'         => 'prefix',
						'type'       => 'MultiName',
						'name'       => 'prefix',
						'size'       => 12,
						'className'  => 'multiname',
						'hide_label' => true,
						'values'     => array(
							array(
								'value' => "true",
								'label' => __( 'Prefix', Forminator::DOMAIN ),
							),
						),
						'fields'     => array(
							array(
								'id'        => 'prefix-label',
								'type'      => 'Text',
								'name'      => 'prefix_label',
								'className' => 'text-field',
								'label'     => __( 'Label', Forminator::DOMAIN ),
							),
							array(
								'id'        => 'prefix-description',
								'type'      => 'Text',
								'name'      => 'prefix_description',
								'className' => 'text-field',
								'label'     => __( 'Description (below field)', Forminator::DOMAIN ),
							),
						),
					), // END prefix

					array(
						'id'         => 'fname',
						'type'       => 'MultiName',
						'name'       => 'fname',
						'size'       => 12,
						'className'  => 'multiname',
						'hide_label' => true,
						'values'     => array(
							array(
								'value' => "true",
								'label' => __( 'First Name', Forminator::DOMAIN ),
							),
						),
						'fields'     => array(
							array(
								'id'        => 'fname-label',
								'type'      => 'Text',
								'name'      => 'fname_label',
								'className' => 'text-field',
								'label'     => __( 'Label', Forminator::DOMAIN ),
							),
							array(
								'id'        => 'fname-placeholder',
								'type'      => 'Text',
								'name'      => 'fname_placeholder',
								'className' => 'text-field',
								'label'     => __( 'Placeholder', Forminator::DOMAIN ),
							),
							array(
								'id'        => 'fname-description',
								'type'      => 'Text',
								'name'      => 'fname_description',
								'className' => 'text-field',
								'label'     => __( 'Description (below field)', Forminator::DOMAIN ),
							),
						),
					), // END first name

					array(
						'id'         => 'mname',
						'type'       => 'MultiName',
						'name'       => 'mname',
						'size'       => 12,
						'className'  => 'multiname',
						'hide_label' => true,
						'values'     => array(
							array(
								'value' => "true",
								'label' => __( 'Middle Name', Forminator::DOMAIN ),
							),
						),
						'fields'     => array(
							array(
								'id'        => 'mname-label',
								'type'      => 'Text',
								'name'      => 'mname_label',
								'className' => 'text-field',
								'label'     => __( 'Label', Forminator::DOMAIN ),
							),
							array(
								'id'        => 'mname-placeholder',
								'type'      => 'Text',
								'name'      => 'mname_placeholder',
								'className' => 'text-field',
								'label'     => __( 'Placeholder', Forminator::DOMAIN ),
							),
							array(
								'id'        => 'mname-description',
								'type'      => 'Text',
								'name'      => 'mname_description',
								'className' => 'text-field',
								'label'     => __( 'Description (below field)', Forminator::DOMAIN ),
							),
						),
					), // END middle name

					array(
						'id'         => 'lname',
						'type'       => 'MultiName',
						'name'       => 'lname',
						'size'       => 12,
						'className'  => 'multiname',
						'hide_label' => true,
						'values'     => array(
							array(
								'value' => "true",
								'label' => __( 'Last Name', Forminator::DOMAIN ),
							),
						),
						'fields'     => array(
							array(
								'id'        => 'lname-label',
								'type'      => 'Text',
								'name'      => 'lname_label',
								'className' => 'text-field',
								'label'     => __( 'Label', Forminator::DOMAIN ),
							),
							array(
								'id'        => 'lname-placeholder',
								'type'      => 'Text',
								'name'      => 'lname_placeholder',
								'className' => 'text-field',
								'label'     => __( 'Placeholder', Forminator::DOMAIN ),
							),
							array(
								'id'        => 'lname-description',
								'type'      => 'Text',
								'name'      => 'lname_description',
								'className' => 'text-field',
								'label'     => __( 'Description (below field)', Forminator::DOMAIN ),
							),
						),
					), // END last name
				),
			),

			array(
				'id'         => 'single-name-field-label',
				'type'       => 'Text',
				'name'       => 'field_label',
				'hide_label' => false,
				'label'      => __( 'Field label', Forminator::DOMAIN ),
				'size'       => 12,
				'className'  => 'text-field',
			),

			array(
				'id'         => 'single-name-placeholder',
				'type'       => 'Text',
				'name'       => 'placeholder',
				'hide_label' => false,
				'label'      => __( 'Field placeholder (optional)', Forminator::DOMAIN ),
				'size'       => 12,
				'className'  => 'text-field',
			),

			array(
				'id'         => 'single-name-description',
				'type'       => 'Text',
				'name'       => 'description',
				'hide_label' => false,
				'label'      => __( 'Description (below field)', Forminator::DOMAIN ),
				'size'       => 12,
				'className'  => 'text-field',
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
			'field_label'       => __( 'Name', Forminator::DOMAIN ),
			'placeholder'       => __( 'E.g. John Doe', Forminator::DOMAIN ),
			'prefix_label'      => __( 'Prefix', Forminator::DOMAIN ),
			'fname_label'       => __( 'First Name', Forminator::DOMAIN ),
			'fname_placeholder' => __( 'E.g. John', Forminator::DOMAIN ),
			'mname_label'       => __( 'Middle Name', Forminator::DOMAIN ),
			'mname_placeholder' => __( 'E.g. Smith', Forminator::DOMAIN ),
			'lname_label'       => __( 'Last Name', Forminator::DOMAIN ),
			'lname_placeholder' => __( 'E.g. Doe', Forminator::DOMAIN ),
			'prefix'            => "true",
			'fname'             => "true",
			'mname'             => "true",
			'lname'             => "true",
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

		//single name
		$name_providers = apply_filters( 'forminator_field_' . $this->slug . '_autofill', array(), $this->slug );

		//multi name
		$prefix_providers = apply_filters( 'forminator_field_' . $this->slug . '_prefix_autofill', array(), $this->slug . '_prefix' );
		$fname_providers  = apply_filters( 'forminator_field_' . $this->slug . '_first_name_autofill', array(), $this->slug . '_first_name' );
		$mname_providers  = apply_filters( 'forminator_field_' . $this->slug . '_middle_name_autofill', array(), $this->slug . '_middle_name' );
		$lname_providers  = apply_filters( 'forminator_field_' . $this->slug . '_last_name_autofill', array(), $this->slug . '_last_name' );

		$autofill_settings = array(
			'name'             => array(
				'values' => forminator_build_autofill_providers( $name_providers ),
			),
			'name-prefix'      => array(
				'values' => forminator_build_autofill_providers( $prefix_providers ),
			),
			'name-first-name'  => array(
				'values' => forminator_build_autofill_providers( $fname_providers ),
			),
			'name-middle-name' => array(
				'values' => forminator_build_autofill_providers( $mname_providers ),
			),
			'name-last-name'   => array(
				'values' => forminator_build_autofill_providers( $lname_providers ),
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
		return '{[ if( field.multiple_name === "true" || field.multiple_name === true ) { ]}
			{[ if( field.prefix || field.fname ) { ]}
				<div class="sui-row">
					{[ if( field.prefix ) { ]}
						{[ if( field.fname ) { ]}
						<div class="sui-col-md-6">
						{[ } else { ]}
						<div class="sui-col">
						{[ } ]}
							{[ if( field.prefix_label !== "" ) { ]}
								<label class="sui-label">{{ encodeHtmlEntity( field.prefix_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
							{[ } ]}
							<select>
								<option>Mr</option>
								<option>Mrs</option>
								<option>Ms</option>
								<option>Miss</option>
							</select>
							{[ if( field.prefix_description !== "" ) { ]}
								<span class="sui-description">{{ encodeHtmlEntity( field.prefix_description ) }}</span>
							{[ } ]}
						</div>
					{[ } ]}
					{[ if( field.fname ) { ]}
						{[ if( field.prefix ) { ]}
						<div class="sui-col-md-6">
						{[ } else { ]}
						<div class="sui-col">
						{[ } ]}
							{[ if( field.fname_label !== "" ) { ]}
								<label class="sui-label">{{ encodeHtmlEntity( field.fname_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
							{[ } ]}
							<input type="text" class="sui-form-control" placeholder="{{ encodeHtmlEntity( field.fname_placeholder ) }}" {{ field.required ? "required" : "" }}>
							{[ if( field.fname_description !== "" ) { ]}
								<span class="sui-description">{{ encodeHtmlEntity( field.fname_description ) }}</span>
							{[ } ]}
						</div>
					{[ } ]}
				</div>
			{[ } ]}
			{[ if( field.mname || field.lname ) { ]}
				<div class="sui-row">
					{[ if( field.mname ) { ]}
						{[ if( field.lname ) { ]}
						<div class="sui-col-md-6">
						{[ } else { ]}
						<div class="sui-col">
						{[ } ]}
							{[ if( field.mname_label !== "" ) { ]}
								<label class="sui-label">{{ encodeHtmlEntity( field.mname_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
							{[ } ]}
							<input type="text" class="sui-form-control" placeholder="{{ encodeHtmlEntity( field.mname_placeholder ) }}" {{ field.required ? "required" : "" }}>
							{[ if( field.mname_description !== "" ) { ]}
								<span class="sui-description">{{ encodeHtmlEntity( field.mname_description ) }}</span>
							{[ } ]}
						</div>
					{[ } ]}
					{[ if( field.lname ) { ]}
						{[ if( field.mname ) { ]}
						<div class="sui-col-md-6">
						{[ } else { ]}
						<div class="sui-col">
						{[ } ]}
							{[ if( field.lname_label !== "" ) { ]}
								<label class="sui-label">{{ encodeHtmlEntity( field.lname_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
							{[ } ]}
							<input type="text" class="sui-form-control" placeholder="{{ encodeHtmlEntity( field.lname_placeholder ) }}" {{ field.required ? "required" : "" }}>
							{[ if( field.lname_description !== "" ) { ]}
								<span class="sui-description">{{ encodeHtmlEntity( field.lname_description ) }}</span>
							{[ } ]}
						</div>
					{[ } ]}
				</div>
			{[ } ]}
		{[ } else { ]}
			{[ if( field.field_label !== "" ) { ]}
				<label class="sui-label">{{ encodeHtmlEntity( field.field_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
			{[ } ]}
			<input type="text" class="sui-form-control" placeholder="{{ encodeHtmlEntity( field.placeholder ) }}" {{ field.required ? "required" : "" }}>
			{[ if( field.description !== "" ) { ]}
				<span class="sui-description">{{ encodeHtmlEntity( field.description ) }}</span>
			{[ } ]}
		{[ } ]}';
	}

	/**
	 * Return simple field markup
	 *
	 * @since 1.0
	 *
	 * @param $field
	 *
	 * @return string
	 */
	public function get_simple( $field, $design ) {
		$id          = self::get_property( 'element_id', $field );
		$name        = $id;
		$required    = self::get_property( 'required', $field, false );
		$placeholder = $this->sanitize_value( self::get_property( 'placeholder', $field ) );

		$name_attr = array(
			'type'            => 'text',
			'class'           => 'forminator-name--field forminator-input',
			'name'            => $name,
			'id'              => $id,
			'placeholder'     => $placeholder,
			'aria-labelledby' => 'forminator-label-' . $id,
		);

		$autofill_markup = $this->get_element_autofill_markup_attr( $id, $this->form_settings );

		$name_attr = array_merge( $name_attr, $autofill_markup );

		return self::create_input( $name_attr, '', '', $required, $design );
	}

	/**
	 * Return multi field first row markup
	 *
	 * @since 1.0
	 *
	 * @param $field
	 * @param $design
	 *
	 * @return string
	 */
	public function get_multi_first_row( $field, $design ) {
		$cols     = 12;
		$html     = '';
		$id       = self::get_property( 'element_id', $field );
		$name     = $id;
		$required = self::get_property( 'required', $field, false );
		$prefix   = self::get_property( 'prefix', $field, false );
		$fname    = self::get_property( 'fname', $field, false );

		// If both prefix & first name are disabled, return
		if ( ! $prefix && ! $fname ) {
			return '';
		}

		// If both prefix & first name are enabled, change cols
		if ( $prefix && $fname ) {
			$cols = 6;
		}

		if ( $prefix ) {
			/**
			 * Create prefix field
			 */
			$prefix_data = array(
				'class' => 'forminator-select',
				'name'  => $id . '-prefix',
				'id'    => $id . '-prefix',
			);

			$options        = array();
			$prefix_options = forminator_get_name_prefixes();

			foreach ( $prefix_options as $key => $pfx ) {
				$options[] = array(
					'value' => $key,
					'label' => $pfx,
				);
			}

			$html .= '<div class="forminator-row forminator-row--inner">';
			$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );
			$html .= '<div class="forminator-field forminator-field--inner">';

			if ( $required ) {
				$label    = self::get_property( 'prefix_label', $field );
				$asterisk = '<i class="wpdui-icon wpdui-icon-asterisk" aria-hidden="true"></i>';
				if ( ! empty( $label ) ) {
					$html .= '<div class="forminator-field--label">';
					$html .= '<label class="forminator-label">' . $label . ' ' . $asterisk . '</label>';
					$html .= '</div>';
				}
				$html .= self::create_simple_select( $prefix_data, $options, self::get_property( 'prefix_placeholder', $field ), self::get_property( 'prefix_description', $field ) );
			} else {
				$html .= self::create_select(
					$prefix_data,
					self::get_property( 'prefix_label', $field ),
					$options,
					self::get_property( 'prefix_placeholder', $field ),
					self::get_property( 'prefix_description', $field )
				);
			}

			$html .= '</div>';
			$html .= '</div>';

			if ( ! $fname ) {
				$html .= '</div>';
			}
		}

		if ( $fname ) {
			/**
			 * Create first name field
			 */
			$first_name = array(
				'type'            => 'text',
				'class'           => 'forminator-input',
				'name'            => $id . '-first-name',
				'id'              => $id . '-first-name',
				'placeholder'     => $this->sanitize_value( self::get_property( 'fname_placeholder', $field ) ),
				'aria-labelledby' => 'forminator-label-' . $id . '-first-name',
			);

			$autofill_markup = $this->get_element_autofill_markup_attr( $first_name['id'], $this->form_settings );

			$first_name = array_merge( $first_name, $autofill_markup );

			if ( ! $prefix ) {
				$html .= '<div class="forminator-row forminator-row--inner">';
			}

			$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );
			$html .= '<div class="forminator-field forminator-field--inner">';

			$html .= self::create_input( $first_name, self::get_property( 'fname_label', $field ), self::get_property( 'fname_description', $field ), $required, $design );

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
	 *
	 * @param $field
	 * @param $design @since 1.0.5
	 *
	 * @return string
	 */
	public function get_multi_second_row( $field, $design ) {
		$cols     = 12;
		$html     = '';
		$id       = self::get_property( 'element_id', $field );
		$name     = $id;
		$required = self::get_property( 'required', $field, false );
		$mname    = self::get_property( 'mname', $field, false );
		$lname    = self::get_property( 'lname', $field, false );

		// If both prefix & first name are disabled, return
		if ( ! $mname && ! $lname ) {
			return '';
		}

		// If both prefix & first name are enabled, change cols
		if ( $mname && $lname ) {
			$cols = 6;
		}

		if ( $mname ) {
			/**
			 * Create middle name field
			 */
			$middle_name = array(
				'type'            => 'text',
				'class'           => 'forminator-input',
				'name'            => $id . '-middle-name',
				'id'              => $id . '-middle-name',
				'placeholder'     => $this->sanitize_value( self::get_property( 'mname_placeholder', $field ) ),
				'aria-labelledby' => 'forminator-label-' . $id . '-middle-name',
			);

			$html .= '<div class="forminator-row forminator-row--inner">';
			$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );
			$html .= '<div class="forminator-field forminator-field--inner">';

			$html .= self::create_input( $middle_name, self::get_property( 'mname_label', $field ), self::get_property( 'mname_description', $field ), $required, $design );

			$html .= '</div>';
			$html .= '</div>';

			if ( ! $lname ) {
				$html .= '</div>';
			}
		}

		if ( $lname ) {
			/**
			 * Create last name field
			 */
			$last_name = array(
				'type'            => 'text',
				'class'           => 'forminator-input',
				'name'            => $id . '-last-name',
				'id'              => $id . '-last-name',
				'placeholder'     => $this->sanitize_value( self::get_property( 'lname_placeholder', $field ) ),
				'aria-labelledby' => 'forminator-label-' . $id . '-last-name',
			);

			$autofill_markup = $this->get_element_autofill_markup_attr( $id . '-last-name', $this->form_settings );

			$last_name = array_merge( $last_name, $autofill_markup );

			if ( ! $mname ) {
				$html .= '<div class="forminator-row forminator-row--inner">';
			}

			$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );
			$html .= '<div class="forminator-field forminator-field--inner">';

			$html .= self::create_input( $last_name, self::get_property( 'lname_label', $field ), self::get_property( 'lname_description', $field ), $required, $design );

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
	 *
	 * @param $field
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function markup( $field, $settings = array() ) {
		$this->field = $field;
		$this->form_settings = $settings;

		$this->init_autofill($settings);

		$multiple    = self::get_property( 'multiple_name', $field, false );
		$design      = $this->get_form_style( $settings );

		// Check we use multi fields
		if ( ! $multiple ) {
			// Only one field
			$html = $this->get_simple( $field, $design );
		} else {
			// Multiple fields
			$html = $this->get_multi_first_row( $field, $design );
			$html .= $this->get_multi_second_row( $field, $design );
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

		if ( $this->is_required( $field ) ) {
			// Check we use multi fields
			if ( ! $multiple ) {
				$rules = '"' . $this->get_id( $field ) . '": "required",';
				$rules .= '"' . $this->get_id( $field ) . '": "trim",';
			} else {
				$rules = '"' . $this->get_id( $field ) . '-first-name": "required",';
				$rules .= '"' . $this->get_id( $field ) . '-middle-name": "required",';
				$rules .= '"' . $this->get_id( $field ) . '-last-name": "required",';
				$rules .= '"' . $this->get_id( $field ) . '-first-name": "trim",';
				$rules .= '"' . $this->get_id( $field ) . '-middle-name": "trim",';
				$rules .= '"' . $this->get_id( $field ) . '-last-name": "trim",';
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
		$id       = self::get_property( 'element_id', $field );
		$multiple = self::get_property( 'multiple_name', $field, false );
		$messages = '';

		if ( $this->is_required( $field ) ) {
			if ( ! $multiple ) {
				$error_message = apply_filters(
					'forminator_name_field_required_validation_message',
					__( 'This field is required. Please input a value', Forminator::DOMAIN ),
					$id,
					$field
				);
				$messages      = '"' . $this->get_id( $field ) . '": "' . $error_message . '",' . "\n";
			} else {
				// First name validation
				$first_name_message = apply_filters(
					'forminator_name_field_first_required_validation_message',
					__( 'This field is required. Please input your first name', Forminator::DOMAIN ),
					$id,
					$field
				);
				$messages           = '"' . $this->get_id( $field ) . '-first-name": "' . $first_name_message . '",' . "\n";

				// First name validation
				$middlet_name_message = apply_filters(
					'forminator_name_field_middle_required_validation_message',
					__( 'This field is required. Please input your middle name', Forminator::DOMAIN ),
					$id,
					$field
				);
				$messages             .= '"' . $this->get_id( $field ) . '-middle-name": "' . $middlet_name_message . '",' . "\n";

				// First name validation
				$last_name_message = apply_filters(
					'forminator_name_field_last_required_validation_message',
					__( 'This field is required. Please input your last name', Forminator::DOMAIN ),
					$id,
					$field
				);
				$messages          .= '"' . $this->get_id( $field ) . '-last-name": "' . $last_name_message . '",' . "\n";
			}
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
			$id       = self::get_property( 'element_id', $field );
			$multiple = self::get_property( 'multiple_name', $field, false );
			if ( empty( $data ) ) {
				$this->validation_message[ $id ] = __( 'This field is required. Please input your name', Forminator::DOMAIN );
			} else {
				if ( $multiple && is_array( $data ) ) {
					$fname      = self::get_property( 'fname', $field, false );
					$mname      = self::get_property( 'mname', $field, false );
					$lname      = self::get_property( 'lname', $field, false );
					$firstname  = isset( $data['first-name'] ) ? $data['first-name'] : '';
					$middlename = isset( $data['middle-name'] ) ? $data['middle-name'] : '';
					$lastname   = isset( $data['last-name'] ) ? $data['last-name'] : '';

					if ( $fname && empty( $firstname ) ) {
						$this->validation_message[ $id . '-first-name' ] = apply_filters(
							'forminator_name_field_first_required_validation_message',
							__( 'This field is required. Please input your first name', Forminator::DOMAIN ),
							$id,
							$field
						);
					}
					if ( $mname && empty( $middlename ) ) {
						$this->validation_message[ $id . '-middle-name' ] = apply_filters(
							'forminator_name_field_middle_required_validation_message',
							__( 'This field is required. Please input your middle name', Forminator::DOMAIN ),
							$id,
							$field
						);
					}
					if ( $lname && empty( $lastname ) ) {
						$this->validation_message[ $id . '-last-name' ] = apply_filters(
							'forminator_name_field_last_required_validation_message',
							__( 'This field is required. Please input your last name', Forminator::DOMAIN ),
							$id,
							$field
						);
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
	 * @param array        $field
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