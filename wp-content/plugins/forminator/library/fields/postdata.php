<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Postdata
 *
 * @since 1.0
 */
class Forminator_Postdata extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'postdata';

	/**
	 * @var string
	 */
	public $type = 'postdata';

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * @var string
	 */
	public $category = 'posts';

	/**
	 * Forminator_Postdata constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'Post data', Forminator::DOMAIN );
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
				'id'         => 'separator-1',
				'type'       => 'Separator',
				'hide_label' => true,
			),

			array(
				'id'           => 'data-status',
				'type'         => 'Select',
				'name'         => 'data_status',
				'className'    => 'select-field',
				'label'        => __( 'Submitted data status', Forminator::DOMAIN ),
				'label_hidden' => false,
				'values'       => array(
					array(
						'value' => "draft",
						'label' => __( 'Draft', Forminator::DOMAIN ),
					),
					array(
						'value' => "pending",
						'label' => __( 'Pending Review', Forminator::DOMAIN ),
					),
					array(
						'value' => "publish",
						'label' => __( 'Published', Forminator::DOMAIN ),
					),
				),
			),

			array(
				'id'          => 'default-author',
				'type'        => 'ToggleContainer',
				'name'        => 'default_author',
				'className'   => 'toggle-container',
				'hide_label'  => true,
				'has_content' => true,
				'values'      => array(
					array(
						'value'      => "true",
						'label'      => __( 'Set default author', Forminator::DOMAIN ),
						'labelSmall' => "true",
					),
				),
				'fields'      => array(
					array(
						'id'           => 'select-author',
						'type'         => 'Select',
						'name'         => 'select_author',
						'className'    => 'number-field',
						'label_hidden' => false,
						'label'        => __( 'Pick user to be default author', Forminator::DOMAIN ),
						'values'       => $this->list_users(),
					),
				),
			),

			array(
				'id'         => 'separator-2',
				'type'       => 'Separator',
				'hide_label' => true,
			),

			array(
				'id'         => 'post-title',
				'type'       => 'MultiName',
				'name'       => 'post_title',
				'className'  => 'multiname',
				'hide_label' => true,
				'values'     => array(
					array(
						'value' => "false",
						'label' => __( 'Post title', Forminator::DOMAIN ),
					),
				),
				'fields'     => array(
					array(
						'id'        => 'post-title-label',
						'type'      => 'Text',
						'name'      => 'post_title_label',
						'className' => 'text-field',
						'label'     => __( 'Label', Forminator::DOMAIN ),
					),
					array(
						'id'        => 'post-title-placeholder',
						'type'      => 'Text',
						'name'      => 'post_title_placeholder',
						'className' => 'text-field',
						'label'     => __( 'Placeholder', Forminator::DOMAIN ),
					),
					array(
						'id'        => 'post-title-description',
						'type'      => 'Text',
						'name'      => 'post_title_description',
						'className' => 'text-field',
						'label'     => __( 'Description (below field)', Forminator::DOMAIN ),
					),
				),
			), // POST title

			array(
				'id'         => 'post-content',
				'type'       => 'MultiName',
				'name'       => 'post_content',
				'className'  => 'multiname',
				'hide_label' => true,
				'values'     => array(
					array(
						'value' => "false",
						'label' => __( 'Post content', Forminator::DOMAIN ),
					),
				),
				'fields'     => array(
					array(
						'id'        => 'post-content-label',
						'type'      => 'Text',
						'name'      => 'post_content_label',
						'className' => 'text-field',
						'label'     => __( 'Label', Forminator::DOMAIN ),
					),
					array(
						'id'        => 'post-content-placeholder',
						'type'      => 'Text',
						'name'      => 'post_content_placeholder',
						'className' => 'text-field',
						'label'     => __( 'Placeholder', Forminator::DOMAIN ),
					),
					array(
						'id'        => 'post-content-description',
						'type'      => 'Text',
						'name'      => 'post_content_description',
						'className' => 'text-field',
						'label'     => __( 'Description (below field)', Forminator::DOMAIN ),
					),
				),
			), // POST content

			array(
				'id'         => 'post-excerpt',
				'type'       => 'MultiName',
				'name'       => 'post_excerpt',
				'className'  => 'multiname',
				'hide_label' => true,
				'values'     => array(
					array(
						'value' => "false",
						'label' => __( 'Post excerpt', Forminator::DOMAIN ),
					),
				),
				'fields'     => array(
					array(
						'id'        => 'post-excerpt-label',
						'type'      => 'Text',
						'name'      => 'post_excerpt_label',
						'className' => 'text-field',
						'label'     => __( 'Label', Forminator::DOMAIN ),
					),
					array(
						'id'        => 'post-excerpt-placeholder',
						'type'      => 'Text',
						'name'      => 'post_excerpt_placeholder',
						'className' => 'text-field',
						'label'     => __( 'Placeholder', Forminator::DOMAIN ),
					),
					array(
						'id'        => 'post-excerpt-description',
						'type'      => 'Text',
						'name'      => 'post_excerpt_description',
						'className' => 'text-field',
						'label'     => __( 'Description (below field)', Forminator::DOMAIN ),
					),
				),
			), // POST excerpt

			array(
				'id'         => 'post-image',
				'type'       => 'MultiName',
				'name'       => 'post_image',
				'className'  => 'multiname',
				'hide_label' => true,
				'values'     => array(
					array(
						'value' => "false",
						'label' => __( 'Featured image', Forminator::DOMAIN ),
					),
				),
				'fields'     => array(
					array(
						'id'        => 'post-image-label',
						'type'      => 'Text',
						'name'      => 'post_image_label',
						'className' => 'text-field',
						'label'     => __( 'Label', Forminator::DOMAIN ),
					),
					array(
						'id'        => 'post-image-description',
						'type'      => 'Text',
						'name'      => 'post_image_description',
						'className' => 'text-field',
						'label'     => __( 'Description (below field)', Forminator::DOMAIN ),
					),
				),
			), // POST feat image

			array(
				'id'         => 'post-category',
				'type'       => 'MultiName',
				'name'       => 'post_category',
				'className'  => 'multiname',
				'hide_label' => true,
				'values'     => array(
					array(
						'value' => "false",
						'label' => __( 'Category', Forminator::DOMAIN ),
					),
				),
				'fields'     => array(
					array(
						'id'        => 'post-category-label',
						'type'      => 'Text',
						'name'      => 'post_category_label',
						'className' => 'text-field',
						'label'     => __( 'Label', Forminator::DOMAIN ),
					),
					array(
						'id'        => 'post-category-description',
						'type'      => 'Text',
						'name'      => 'post_category_description',
						'className' => 'text-field',
						'label'     => __( 'Description (below field)', Forminator::DOMAIN ),
					),
				),
			), // POST category

			array(
				'id'         => 'post-tags',
				'type'       => 'MultiName',
				'name'       => 'post_tags',
				'className'  => 'multiname',
				'hide_label' => true,
				'values'     => array(
					array(
						'value'      => "false",
						'label'      => __( 'Tags', Forminator::DOMAIN ),
						'labelSmall' => "true",
					),
				),
				'fields'     => array(
					array(
						'id'        => 'post-tags-label',
						'type'      => 'Text',
						'name'      => 'post_tags_label',
						'className' => 'text-field',
						'label'     => __( 'Label', Forminator::DOMAIN ),
					),
					array(
						'id'        => 'post-tags-description',
						'type'      => 'Text',
						'name'      => 'post_tags_description',
						'className' => 'text-field',
						'label'     => __( 'Description (below field)', Forminator::DOMAIN ),
					),
				),
			), // POST tags

			//custom fields
			array(
				'id'          => 'post-custom',
				'type'        => 'ToggleContainer',
				'name'        => 'post_custom',
				'className'   => 'toggle-container',
				'hide_label'  => true,
				'has_content' => true,
				'values'      => array(
					array(
						'value'      => 'true',
						'label'      => __( 'This post has custom fields', Forminator::DOMAIN ),
						'labelSmall' => "true",
					),
				),
				'fields'      => array(
					array(
						'id'         => 'post-vars',
						'type'       => 'MultiValue',
						'name'       => 'custom_vars',
						'className'  => 'post-var-field',
						'hide_label' => true,
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
		return apply_filters( 'forminator_post_data_defaults_settings', array(
			'data_status'         => 'pending',
			'post_title_label'    => 'Post Title',
			'post_content_label'  => 'Post Content',
			'post_excerpt_label'  => 'Post Excerpt',
			'post_image_label'    => 'Featured Image',
			'post_category_label' => 'Category',
			'post_tags_label'     => 'Tags',
		) );
	}

	/**
	 * Return users list
	 *
	 * @since 1.0
	 * @return array
	 */
	public function list_users() {
		$users_list = array();
		$users      = get_users( array( 'role__in' => array( 'administrator', 'editor', 'author' ), 'fields' => array( 'ID', 'display_name' ) ) );
		foreach ( $users as $user ) {
			$users_list[] = array(
				'value' => $user->ID,
				'label' => ucfirst( $user->display_name ),
			);
		}

		return apply_filters( 'forminator_postdata_users_list', $users_list );
	}

	/**
	 * Field admin markup
	 *
	 * @since 1.0
	 * @return string
	 */
	public function admin_html() {
		return '{[ if( field.post_title ) { ]}
		<div class="wpmudev-form-field--group">
			{[ if( field.post_title_label !== "" ) { ]}
				<label class="wpmudev-group--label">{{ encodeHtmlEntity( field.post_title_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
			{[ } ]}
			<input type="text" class="wpmudev-input" placeholder="{{ encodeHtmlEntity( field.post_title_placeholder ) }}" />
			{[ if( field.post_title_description !== "" ) { ]}
			<div class="wpmudev-group--info">
				<span class="wpmudev-info--text">{{ encodeHtmlEntity( field.post_title_description ) }}</span>
			</div>
			{[ } ]}
		</div>
		{[ } ]}
		{[ if( field.post_content ) { ]}
        <div class="wpmudev-form-field--group">
            {[ if( field.post_content_label !== "" ) { ]}
				<label class="wpmudev-group--label">{{ encodeHtmlEntity( field.post_content_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
			{[ } ]}
			<textarea class="wpmudev-textarea" placeholder="{{ encodeHtmlEntity( field.post_content_placeholder ) }}"></textarea>
			{[ if( field.post_content_description !== "" ) { ]}
			<div class="wpmudev-group--info">
				<span class="wpmudev-info--text">{{ encodeHtmlEntity( field.post_content_description ) }}</span>
			</div>
			{[ } ]}
		</div>
		{[ } ]}
		{[ if( field.post_excerpt ) { ]}
        <div class="wpmudev-form-field--group">
			{[ if( field.post_excerpt_label !== "" ) { ]}
				<label class="wpmudev-group--label">{{ encodeHtmlEntity( field.post_excerpt_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
			{[ } ]}
			<textarea class="wpmudev-textarea" placeholder="{{ encodeHtmlEntity( field.post_excerpt_placeholder ) }}"></textarea>
			{[ if( field.post_excerpt_description !== "" ) { ]}
			<div class="wpmudev-group--info">
				<span class="wpmudev-info--text">{{ encodeHtmlEntity( field.post_excerpt_description ) }}</span>
			</div>
			{[ } ]}
		</div>
		{[ } ]}
		{[ if( field.post_image ) { ]}
        <div class="wpmudev-form-field--group">
			{[ if( field.post_image_label !== "" ) { ]}
				<label class="wpmudev-group--label">{{ encodeHtmlEntity( field.post_image_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
			{[ } ]}
            <div class="wpmudev-form-field--upload">
                <button class="wpmudev-upload-button">Choose file</button>
				<label class="wpmudev-upload-file">No file chosen</label>
				{[ if( field.post_image_description !== "" ) { ]}
				<small class="wpmudev-upload-info">{{ encodeHtmlEntity( field.post_image_description ) }}</small>
				{[ } ]}
            </div>
		</div>
		{[ } ]}
		{[ if( field.post_category ) { ]}
		<div class="wpmudev-form-field--group">
			{[ if( field.post_category_label !== "" ) { ]}
				<label class="wpmudev-group--label">{{ encodeHtmlEntity( field.post_category_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
			{[ } ]}
			<select class="wpmudev-select">
				<option>Uncategorized</option>
			</select>
			{[ if( field.post_category_description !== "" ) { ]}
			<div class="wpmudev-group--info">
				<span class="wpmudev-info--text">{{ encodeHtmlEntity( field.post_category_description ) }}</span>
			</div>
			{[ } ]}
		</div>
		{[ } ]}
		{[ if( field.post_tags ) { ]}
		<div class="wpmudev-form-field--group">
			{[ if( field.post_tags_label !== "" ) { ]}
				<label class="wpmudev-group--label">{{ encodeHtmlEntity( field.post_tags_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
			{[ } ]}
			<select class="wpmudev-select">
				<option>Uncategorized</option>
			</select>
			{[ if( field.post_tags_description !== "" ) { ]}
			<div class="wpmudev-group--info">
				<span class="wpmudev-info--text">{{ encodeHtmlEntity( field.post_tags_description ) }}</span>
			</div>
			{[ } ]}
		</div>
		{[ } ]}';
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
		$required    = self::get_property( 'required', $field, false );
		$id          = $name = self::get_property( 'element_id', $field );

		$html = $this->get_post_title( $id, $name, $field, $required );
		$html .= $this->get_post_content( $id, $name, $field, $required );
		$html .= $this->get_post_excerpt( $id, $name, $field, $required );
		$html .= $this->get_post_image( $id, $name, $field, $required );
		$html .= $this->get_post_category( $id, $name, $field, $required );
		$html .= $this->get_post_tag( $id, $name, $field, $required );
		$html .= $this->_render_custom_fields( $id, $name, $field, $required );

		return apply_filters( 'forminator_field_postdata_markup', $html, $field, $required, $id, $this );
	}

	/**
	 * Return post title
	 *
	 * @since 1.0
	 *
	 * @param $id
	 * @param $name
	 * @param $field
	 * @param $required
	 *
	 * @return string
	 */
	public function get_post_title( $id, $name, $field, $required ) {
		return apply_filters( 'forminator_field_postdata_post_title', $this->_get_post_field( $id, $name, $field, $required, 'post_title', 'text', 'forminator-input', 'post-title' ) );
	}

	/**
	 * Return post content
	 *
	 * @since 1.0
	 *
	 * @param $id
	 * @param $name
	 * @param $field
	 * @param $required
	 *
	 * @return string
	 */
	public function get_post_content( $id, $name, $field, $required ) {
		return apply_filters( 'forminator_field_postdata_post_content', $this->_get_post_field( $id, $name, $field, $required, 'post_content', 'wp_editor', 'forminator-textarea', 'post-content' ) );
	}

	/**
	 * Return post excerpt
	 *
	 * @since 1.0
	 *
	 * @param $id
	 * @param $name
	 * @param $field
	 * @param $required
	 *
	 * @return string
	 */
	public function get_post_excerpt( $id, $name, $field, $required ) {
		return apply_filters( 'forminator_field_postdata_post_excerpt', $this->_get_post_field( $id, $name, $field, $required, 'post_excerpt', 'textarea', 'forminator-textarea', 'post-excerpt' ) );
	}

	/**
	 * Return post featured image
	 *
	 * @since 1.0
	 *
	 * @param $id
	 * @param $name
	 * @param $field
	 * @param $required
	 *
	 * @return string
	 */
	public function get_post_image( $id, $name, $field, $required ) {
		return apply_filters( 'forminator_field_postdata_post_image', $this->_get_post_field( $id, $name, $field, $required, 'post_image', 'file', 'forminator-upload', 'post-image' ) );
	}

	/**
	 * Return categories
	 *
	 * @since 1.0
	 *
	 * @param $id
	 * @param $name
	 * @param $field
	 * @param $required
	 *
	 * @return string
	 */
	public function get_post_category( $id, $name, $field, $required ) {
		$options    = array();
		$categories = get_categories(
			array(
				'orderby'    => 'name',
				'order'      => 'ASC',
				'hide_empty' => false,
			)
		);

		foreach ( $categories as $category ) {
			$options[] = array(
				'value' => $category->term_id,
				'label' => $category->name,
			);
		}

		return apply_filters( 'forminator_field_postdata_post_category', $this->_get_post_field( $id, $name, $field, $required, 'post_category', 'select', 'forminator-select', 'post-category', $options ) );
	}

	/**
	 * Return tags
	 *
	 * @since 1.0
	 *
	 * @param $id
	 * @param $name
	 * @param $field
	 * @param $required
	 *
	 * @return string
	 */
	public function get_post_tag( $id, $name, $field, $required ) {
		$options = array();
		$tags    = get_tags(
			array(
				'hide_empty' => false,
			)
		);

		foreach ( $tags as $tag ) {
			$options[] = array(
				'value' => $tag->term_id,
				'label' => $tag->name,
			);
		}

		return apply_filters( 'forminator_field_postdata_post_tag', $this->_get_post_field( $id, $name, $field, $required, 'post_tags', 'select', 'forminator-select', 'post-tags', $options ) );
	}

	/**
	 * Return post field
	 *
	 * @since 1.0
	 *
	 * @param        $id
	 * @param        $name
	 * @param        $field
	 * @param        $required
	 * @param        $field_name
	 * @param        $type
	 * @param        $class
	 * @param        $input_suffix
	 * @param array  $options
	 * @param string $value
	 *
	 * @return string
	 */
	private function _get_post_field( $id, $name, $field, $required, $field_name, $type, $class, $input_suffix, $options = array(), $value = '' ) {
		$html          = '';
		$field_enabled = self::get_property( $field_name, $field, '' );

		if ( ! empty( $field_enabled ) ) {
			$cols         = 12;
			$placeholder  = self::get_property( $field_name . '_placeholder', $field );
			$label        = self::get_property( $field_name . '_label', $field );
			$description  = self::get_property( $field_name . '_description', $field );
			$field_markup = array(
				'type'        => $type,
				'class'       => $class,
				'name'        => $id . '-' . $input_suffix,
				'id'          => $id . '-' . $input_suffix,
				'placeholder' => $placeholder,
			);

			if ( $required ) {
				$field_markup['required'] = $required;
			}

			$html .= sprintf( '<div class="forminator-row forminator-row--inner"><div class="forminator-col forminator-col-%s">', $cols );
			$html .= '<div class="forminator-field forminator-field--inner">';

			if ( $type == 'wp_editor' ) {
				// multiple wp_editor support
				$field_markup['id'] = $field_markup['id'] . '-' . uniqid();
				$html               .= self::create_wp_editor( $field_markup, $label, $description, $required );
			} elseif ( $type == 'textarea' ) {
				$html .= self::create_textarea( $field_markup, $label, $description, $required );
			} elseif ( $type == 'select' ) {
				if ( empty( $options ) ) {
					unset( $field_markup['required'] );
				}
				$html .= self::create_select( $field_markup, $label, $options, $value, $description, $required );
			} elseif ( $type == 'file' ) {
				if ( $required ) {
					$html .= '<div class="forminator-field--label">';
					$html .= sprintf( '<label class="forminator-label">%s%s</label>', $label, forminator_get_required_icon() );
					$html .= '</div>';
				} else {
					$html .= sprintf( '<div class="forminator-field--label"><label class="forminator-label">%s</label></div>', $label );
				}
				$html .= self::create_file_upload( $id . '-' . $input_suffix, $name . '-' . $input_suffix, $required );
			} else {
				$html .= self::create_input( $field_markup, $label, $description, $required );
			}

			$html .= '</div>';
			$html .= '</div></div>';
		}

		return $html;
	}

	/**
	 * Render custom fields
	 *
	 * @since 1.0
	 *
	 * @param $id
	 * @param $name
	 * @param $field
	 * @param $required
	 *
	 * @return string
	 */
	private function _render_custom_fields( $id, $name, $field, $required ) {
		$html              = '';
		$cols              = 12;
		$has_custom_fields = self::get_property( 'post_custom', $field, false );

		if ( $has_custom_fields ) {
			$custom_vars = self::get_property( 'custom_vars', $field );

			if ( ! empty( $custom_vars ) ) {
				$html .= '<div class="forminator-row forminator-row--inner">';

				foreach ( $custom_vars as $variable ) {
					$html         .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );
					$value        = ! empty( $variable['value'] ) ? $variable['value'] : sanitize_title( $variable['label'] );
					$input_id     = $id . '-post_meta-' . $value;
					$label        = $variable['label'];
					$field_markup = array(
						'type'        => 'text',
						'class'       => 'forminator-input',
						'name'        => $input_id,
						'id'          => $input_id,
						'placeholder' => $label,
					);
					$html         .= self::create_input( $field_markup, $label, '' );
					$html         .= '</div>';
				}
			}

			$html .= '</div>';
		}

		return $html;
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
		$id = self::get_property( 'element_id', $field );

		$post_title   = self::get_property( 'post_title', $field, '' );
		$post_content = self::get_property( 'post_content', $field, '' );
		$post_excerpt = self::get_property( 'post_excerpt', $field, '' );

		$title    = isset( $data['post-title'] ) ? $data['post-title'] : '';
		$content  = isset( $data['post-content'] ) ? $data['post-content'] : '';
		$excerpt  = isset( $data['post-excerpt'] ) ? $data['post-excerpt'] : '';
		$image    = isset( $data['post-image'] ) ? $data['post-image'] : '';
		$category = isset( $data['post-category'] ) ? $data['post-category'] : '';
		$tags     = isset( $data['post-tags'] ) ? $data['post-tags'] : '';

		if ( $this->is_required( $field ) ) {
			if ( empty( $data ) ) {
				$postdata_validation_message = apply_filters(
					'forminator_postdata_field_validation_message',
					__( 'This field is required. Please fill in post data', Forminator::DOMAIN ),
					$id
				);
				$this->validation_message[ $id ] = $postdata_validation_message;
			} elseif ( is_array( $data ) ) {
				$post_image    = self::get_property( 'post_image', $field, '' );
				$post_category = self::get_property( 'post_category', $field, '' );
				$post_tags     = self::get_property( 'post_tags', $field, '' );

				if ( ! empty( $post_title ) && empty( $title ) ) {
					$postdata_post_title_validation_message = apply_filters(
						'forminator_postdata_field_post_title_validation_message',
						__( 'This field is required. Please enter the post title', Forminator::DOMAIN ),
						$id
					);
					$this->validation_message[ $id . '-post-title' ] = $postdata_post_title_validation_message;
				}
				if ( ! empty( $post_content ) && empty( $content ) ) {
					$postdata_post_content_validation_message = apply_filters(
						'forminator_postdata_field_post_content_validation_message',
						__( 'This field is required. Please enter the post content', Forminator::DOMAIN ),
						$id
					);
					$this->validation_message[ $id . '-post-content' ] = $postdata_post_content_validation_message;
				}
				if ( ! empty( $post_excerpt ) && empty( $excerpt ) ) {
					$postdata_post_excerpt_validation_message = apply_filters(
						'forminator_postdata_field_post_excerpt_validation_message',
						__( 'This field is required. Please enter the post excerpt', Forminator::DOMAIN ),
						$id
					);
					$this->validation_message[ $id . '-post-excerpt' ] = $postdata_post_excerpt_validation_message;
				}
				if ( ! empty( $post_image ) && empty( $image ) ) {
					$postdata_post_image_validation_message = apply_filters(
						'forminator_postdata_field_post_image_validation_message',
						__( 'This field is required. Please upload a post image', Forminator::DOMAIN ),
						$id
					);
					$this->validation_message[ $id . '-post-image' ] = $postdata_post_image_validation_message;
				}
				if ( ! empty( $post_category ) && empty( $category ) ) {
					$postdata_post_category_validation_message = apply_filters(
						'forminator_postdata_field_post_category_validation_message',
						__( 'This field is required. Please select a post category', Forminator::DOMAIN ),
						$id
					);
					$this->validation_message[ $id . '-post-category' ] = $postdata_post_category_validation_message;
				}
				if ( ! empty( $post_tags ) && empty( $tags ) ) {
					$postdata_post_tag_validation_message = apply_filters(
						'forminator_postdata_field_post_tag_validation_message',
						__( 'This field is required. Please select a post tag', Forminator::DOMAIN ),
						$id
					);
					$this->validation_message[ $id . '-post-tags' ] = $postdata_post_tag_validation_message;
				}
			}
		} else {
			// validation for postdata when its not required.
			// `wp_insert_post` required at least ONE OF THESE to be available title / content / excerpt.
			// check only when user send some data
			if ( ! empty( $data ) && is_array( $data ) ) {
				if ( ! $title && ! $content && ! $excerpt ) {
					// check if there is any field with content
					$is_content_available = false;
					foreach ( $data as $datum ) {
						if ( ! empty( $datum ) ) {
							$is_content_available = true;
							break;
						}
					}

					// when $is_content_available false means, field not required, and user didnt put any content on form
					if ( $is_content_available ) {
						//check if on postdata these sub field is avail available
						if ( ! empty( $post_title ) ) {
							$this->validation_message[ $id . '-post-title' ] = apply_filters(
								// nr = not required
								'forminator_postdata_field_post_title_nr_validation_message',
								__( 'At least one of these fields is required: Post Title, Post Excerpt or Post Content', Forminator::DOMAIN ),
								$id
							);
						}
						if ( ! empty( $post_content ) ) {
							$this->validation_message[ $id . '-post-content' ] = apply_filters(
								// nr = not required
								'forminator_postdata_field_post_content_nr_validation_message',
								__( 'At least one of these fields is required: Post Title, Post Excerpt or Post Content', Forminator::DOMAIN ),
								$id
							);
						}
						if ( ! empty( $post_excerpt ) ) {
							$this->validation_message[ $id . '-post-excerpt' ] = apply_filters(
								// nr = not required
								'forminator_postdata_field_post_excerpt_nr_validation_message',
								__( 'At least one of these fields is required: Post Title, Post Excerpt or Post Content', Forminator::DOMAIN ),
								$id
							);
						}
					}
				}
			}
		}
	}

	/**
	 * Upload post image
	 *
	 * @since 1.0
	 *
	 * @param array  $field      - the field
	 * @param string $field_name - the field name
	 *
	 * @return array|bool - if success, return an array
	 */
	public function upload_post_image( $field, $field_name ) {
		$post_image = self::get_property( 'post_image', $field, '' );

		if ( ! empty( $post_image ) ) {
			if ( isset( $_FILES[ $field_name ] ) ) {
				if ( isset( $_FILES[ $field_name ]['name'] ) && ! empty( $_FILES[ $field_name ]['name'] ) ) {
					$file_name        = $_FILES[ $field_name ]['name'];
					$file_data        = file_get_contents( $_FILES[ $field_name ]['tmp_name'] );
					$upload_dir       = wp_upload_dir(); // Set upload folder
					$unique_file_name = wp_unique_filename( $upload_dir['path'], $file_name );
					$filename         = basename( $unique_file_name ); // Create base file name

					if ( wp_mkdir_p( $upload_dir['path'] ) ) {
						$file = $upload_dir['path'] . '/' . $filename;
					} else {
						$file = $upload_dir['basedir'] . '/' . $filename;
					}

					// Create the  file on the server
					file_put_contents( $file, $file_data );

					// Check image file type
					$wp_filetype = wp_check_filetype( $filename, null );
					$image_exts  = apply_filters( 'forminator_field_postdata_image_file_types', array( 'jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp' ) );
					if ( in_array( $wp_filetype['ext'], $image_exts ) ) {
						// Set attachment data
						$attachment = array(
							'post_mime_type' => $wp_filetype['type'],
							'post_title'     => sanitize_file_name( $filename ),
							'post_content'   => '',
							'post_status'    => 'inherit',
						);

						// Create the attachment
						$attachment_id = wp_insert_attachment( $attachment, $file );

						// Include image.php
						require_once( ABSPATH . 'wp-admin/includes/image.php' );

						// Define attachment metadata
						$attach_data = wp_generate_attachment_metadata( $attachment_id, $file );

						// Assign metadata to attachment
						wp_update_attachment_metadata( $attachment_id, $attach_data );
						$uploaded_file = wp_get_attachment_image_src( $attachment_id, 'large', false );
						if ( $uploaded_file && is_array( $uploaded_file ) ) {
							return array(
								'attachment_id' => $attachment_id,
								'uploaded_file' => $uploaded_file,
							);
						}
					}
				}
			}

			return array(
				'attachment_id' => 0,
				'uploaded_file' => 0,
			);
		}

		return true;
	}

	/**
	 * Save post
	 *
	 * @since 1.0
	 *
	 * @param array $field - field array
	 * @param array $data  - post data
	 *
	 * @return bool|int - success is post id
	 */
	public function save_post( $field, $data ) {
		$default_author = self::get_property( 'default_author', $field, false );
		$default_author = filter_var( $default_author, FILTER_VALIDATE_BOOLEAN );
		$post_author    = 1;
		if ( $default_author ) {
			$post_author = self::get_property( 'select_author', $field, 1 );
		}
		$post_status = self::get_property( 'data_status', $field, 'draft' );
		$title       = isset( $data['post-title'] ) ? $data['post-title'] : '';
		$content     = isset( $data['post-content'] ) ? $data['post-content'] : '';
		$excerpt     = isset( $data['post-excerpt'] ) ? $data['post-excerpt'] : '';
		$image       = isset( $data['post-image'] ) ? $data['post-image'] : '';
		$category    = isset( $data['post-category'] ) ? $data['post-category'] : '';
		$tags        = isset( $data['post-tags'] ) ? $data['post-tags'] : '';
		$post_meta   = isset( $data['post-custom'] ) ? $data['post-custom'] : '';

		$post = array(
			'post_author'  => $post_author,
			'post_content' => wp_kses_post( $content ),
			'post_excerpt' => $excerpt,
			'post_name'    => sanitize_text_field( $title ),
			'post_status'  => $post_status,
			'post_title'   => $title,
		);

		if ( ! empty( $category ) ) {
			$post['post_category'] = array( intval( $category ) );
		}

		if ( ! empty( $tags ) ) {
			$post['tags_input'] = array( intval( $tags ) );
		}

		//trigger wp_error for is_wp_error to be correctly identified
		$post_id = wp_insert_post( $post, true );
		if ( ! is_wp_error( $post_id ) ) {
			$post_image = self::get_property( 'post_image', $field, '' );
			if ( ! empty( $post_image ) && ! empty( $image ) && is_array( $image ) ) {
				set_post_thumbnail( $post_id, $image['attachment_id'] );
			}

			if ( ! empty( $post_meta ) ) {
				foreach ( $post_meta as $meta ) {
					add_post_meta( $post_id, $meta['key'], $meta );
				}
				add_post_meta( $post_id, '_has_forminator_meta', true );
			}

			do_action( 'forminator_post_data_field_post_saved', $post_id, $field, $data, $this );

			return $post_id;
		}

		return false;
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
		$image = $content = '';

		// Do not sanitize image URL
		if( isset( $data['post-image'] ) ) {
			$image = $data['post-image'];
		}

		// Do not sanitize post content
		if( isset( $data['post-content'] ) ) {
			$content = $data['post-content'];
		}

		// Sanitize
		$data = forminator_sanitize_field( $data );

		// Return image url original value
		if( isset( $data['post-image'] ) ) {
			$data['post-image'] = $image;
		}

		// Return post content original value
		if( isset( $data['post-content'] ) ) {
			$data['post-content'] = $content;
		}

		return apply_filters( 'forminator_field_postdata_sanitize', $data, $field );
	}
}