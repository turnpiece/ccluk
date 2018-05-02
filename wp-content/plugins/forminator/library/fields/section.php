<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Section
 *
 * @since 1.0
 */
class Forminator_Section extends Forminator_Field {

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $slug = 'section';

	/**
	 * @var string
	 */
    public $type = 'section';

	/**
	 * @var int
	 */
    public $position = 17;

	/**
	 * @var string
	 */
    public $options = array();

    /**
     * @var string
     */
    public $category = 'standard';

	/**
	 * Forminator_Section constructor.
	 *
	 * @since 1.0
	 */
    public function __construct() {
        parent::__construct();

        $this->name = __( 'Section', Forminator::DOMAIN );
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
				'id' => 'section-title',
				'type' => 'Text',
				'name' => 'section_title',
				'hide_label' => false,
				'label'	=> __( 'Section title', Forminator::DOMAIN )
            ),

            array(
				'id' => 'section-subtitle',
				'type' => 'Text',
				'name' => 'section_subtitle',
				'hide_label' => false,
				'label'	=> __( 'Section subtitle', Forminator::DOMAIN )
            ),

            array(
				'id' => 'section-border',
				'type' => 'Toggle',
				'name' => 'section_border',
				'hide_label' => true,
				//'default_value' => "false",
				'values' => array(
					array(
						'value' => "true",
						'label' => __( 'Section with border', Forminator::DOMAIN ),
						'labelSmall' => "true"
					)
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
            'section_title'     => __( 'Form Section', Forminator::DOMAIN ),
            'section_border'    => "false"
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
		//Unsupported Autofill
		$autofill_settings = array();

		return $autofill_settings;
	}

	/**
	 * Field admin markup
	 *
	 * @since 1.0
	 * @return string
	 */
	public function admin_html() {
        return '{[ if( field.section_title !== "" || field.section_subtitle !== "" ) { ]}
            <div class="wpmudev-form-field--group">
                <label class="wpmudev-group--section_title">{{ encodeHtmlEntity( field.section_title ) }}</label>
                <label class="wpmudev-group--section_subtitle">{{ encodeHtmlEntity( field.section_subtitle ) }}</label>
                {[ if( field.section_border == "true" ) { ]}
                <hr />
                {[ } ]}
            </div>
        {[ } ]}';
    }

	/**
	 * Field front-end markup
	 *
	 * @since 1.0
	 * @param $field
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function markup( $field, $settings = array() ) {
        $this->field = $field;
		$id = $name  = self::get_property( 'element_id', $field );
		$id          = $id . '-field';
        $required    = self::get_property( 'required', $field, false );
        $title       = self::get_property( 'section_title', $field );
        $subtitle    = self::get_property( 'section_subtitle', $field );
        $type        = self::get_property( 'section_type', $field );
        $border	     = self::get_property( 'section_border', $field, false );
		$border 	 = filter_var( $border, FILTER_VALIDATE_BOOLEAN );

        $html = '<div class="forminator-break">';

		if ( ! empty( $title ) ) {
			$title = $this->sanitize_output( $title );
			$html .= sprintf( '<h2 class="forminator-title">%s</h2>', $title );
		} else {
			$html .= '';
        }

        if ( ! empty( $subtitle ) ) {
			$subtitle = $this->sanitize_output( $subtitle );
			$html .= sprintf( '<h3 class="forminator-subtitle">%s</h3>', $subtitle );
		} else {
			$html .= '';
		}

		if ( $border ) {
			$html .= '<hr class="forminator-border" />';
		}

		$html .= '</div>';

		return apply_filters( 'forminator_field_section_markup', $html, $field );
	}

	/**
	 * Return sanitized form data
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function sanitize_output( $content ) {
		return htmlentities( $content, ENT_QUOTES );
	}
}