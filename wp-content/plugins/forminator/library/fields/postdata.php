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
				'className' => 'required-field',
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
				'id' => 'separator-1',
				'type' => 'Separator',
				'hide_label' => true,
            ),

			array(
				'id' => 'data-status',
				'type' => 'Select',
				'name' => 'data_status',
				'className' => 'select-field',
				'label'	=> __( 'Submitted data status', Forminator::DOMAIN ),
				'label_hidden' => false,
				'values' => array(
					array(
						'value' => "draft",
						'label' => __( 'Draft', Forminator::DOMAIN )
					),
					array(
						'value' => "pending",
						'label' => __( 'Pending Review', Forminator::DOMAIN )
					),
					array(
						'value' => "publish",
						'label' => __( 'Published', Forminator::DOMAIN )
					)
				)
			),

			array(
				'id' => 'default-author',
				'type' => 'ToggleContainer',
				'name' => 'default_author',
				'className' => 'toggle-container',
				'hide_label' => true,
				'has_content' => true,
				'values' => array(
					array(
						'value' => "true",
						'label' => __( 'Set default author', Forminator::DOMAIN ),
						'labelSmall' => "true"
					)
				),
				'fields' => array(
					array(
						'id' => 'select-author',
						'type' => 'Select',
						'name' => 'select_author',
						'className' => 'number-field',
						'label_hidden' => false,
						'label' => __( 'Pick user to be default author', Forminator::DOMAIN ),
						'values' => $this->list_users()
					),
				)
			),

			array(
				'id' => 'separator-2',
				'type' => 'Separator',
				'hide_label' => true,
			),

			array(
				'id' => 'post-title',
				'type' => 'MultiName',
				'name' => 'post_title',
				'className' => 'multiname',
				'hide_label' => true,
				'values' => array(
					array(
						'value' => "false",
						'label' => __( 'Post title', Forminator::DOMAIN )
					)
				),
				'fields' => array(
					array(
						'id' => 'post-title-label',
						'type' => 'Text',
						'name' => 'post_title_label',
						'className' => 'text-field',
						'label' => __( 'Label', Forminator::DOMAIN )
					),
					array(
						'id' => 'post-title-placeholder',
						'type' => 'Text',
						'name' => 'post_title_placeholder',
						'className' => 'text-field',
						'label' => __( 'Placeholder', Forminator::DOMAIN )
					),
					array(
						'id' => 'post-title-description',
						'type' => 'Text',
						'name' => 'post_title_description',
						'className' => 'text-field',
						'label' => __( 'Description (below field)', Forminator::DOMAIN )
					),
				)
			), // POST title

			array(
				'id' => 'post-content',
				'type' => 'MultiName',
				'name' => 'post_content',
				'className' => 'multiname',
				'hide_label' => true,
				'values' => array(
					array(
						'value' => "false",
						'label' => __( 'Post content', Forminator::DOMAIN )
					)
				),
				'fields' => array(
					array(
						'id' => 'post-content-label',
						'type' => 'Text',
						'name' => 'post_content_label',
						'className' => 'text-field',
						'label' => __( 'Label', Forminator::DOMAIN )
					),
					array(
						'id' => 'post-content-placeholder',
						'type' => 'Text',
						'name' => 'post_content_placeholder',
						'className' => 'text-field',
						'label' => __( 'Placeholder', Forminator::DOMAIN )
					),
					array(
						'id' => 'post-content-description',
						'type' => 'Text',
						'name' => 'post_content_description',
						'className' => 'text-field',
						'label' => __( 'Description (below field)', Forminator::DOMAIN )
					),
				)
			), // POST content

			array(
				'id' => 'post-excerpt',
				'type' => 'MultiName',
				'name' => 'post_excerpt',
				'className' => 'multiname',
				'hide_label' => true,
				'values' => array(
					array(
						'value' => "false",
						'label' => __( 'Post excerpt', Forminator::DOMAIN )
					)
				),
				'fields' => array(
					array(
						'id' => 'post-excerpt-label',
						'type' => 'Text',
						'name' => 'post_excerpt_label',
						'className' => 'text-field',
						'label' => __( 'Label', Forminator::DOMAIN )
					),
					array(
						'id' => 'post-excerpt-placeholder',
						'type' => 'Text',
						'name' => 'post_excerpt_placeholder',
						'className' => 'text-field',
						'label' => __( 'Placeholder', Forminator::DOMAIN )
					),
					array(
						'id' => 'post-excerpt-description',
						'type' => 'Text',
						'name' => 'post_excerpt_description',
						'className' => 'text-field',
						'label' => __( 'Description (below field)', Forminator::DOMAIN )
					),
				)
			), // POST excerpt

			array(
				'id' => 'post-image',
				'type' => 'MultiName',
				'name' => 'post_image',
				'className' => 'multiname',
				'hide_label' => true,
				'values' => array(
					array(
						'value' => "false",
						'label' => __( 'Featured image', Forminator::DOMAIN )
					)
				),
				'fields' => array(
					array(
						'id' => 'post-image-label',
						'type' => 'Text',
						'name' => 'post_image_label',
						'className' => 'text-field',
						'label' => __( 'Label', Forminator::DOMAIN )
					),
					array(
						'id' => 'post-image-description',
						'type' => 'Text',
						'name' => 'post_image_description',
						'className' => 'text-field',
						'label' => __( 'Description (below field)', Forminator::DOMAIN )
					),
				)
			), // POST feat image

			array(
				'id' => 'post-category',
				'type' => 'MultiName',
				'name' => 'post_category',
				'className' => 'multiname',
				'hide_label' => true,
				'values' => array(
					array(
						'value' => "false",
						'label' => __( 'Category', Forminator::DOMAIN )
					)
				),
				'fields' => array(
					array(
						'id' => 'post-category-label',
						'type' => 'Text',
						'name' => 'post_category_label',
						'className' => 'text-field',
						'label' => __( 'Label', Forminator::DOMAIN )
					),
					array(
						'id' => 'post-category-description',
						'type' => 'Text',
						'name' => 'post_category_description',
						'className' => 'text-field',
						'label' => __( 'Description (below field)', Forminator::DOMAIN )
					),
				)
			), // POST category

			array(
				'id' => 'post-tags',
				'type' => 'MultiName',
				'name' => 'post_tags',
				'className' => 'multiname',
				'hide_label' => true,
				'values' => array(
					array(
						'value' => "false",
						'label' => __( 'Tags', Forminator::DOMAIN ),
						'labelSmall' => "true"
					)
				),
				'fields' => array(
					array(
						'id' => 'post-tags-label',
						'type' => 'Text',
						'name' => 'post_tags_label',
						'className' => 'text-field',
						'label' => __( 'Label', Forminator::DOMAIN )
					),
					array(
						'id' => 'post-tags-description',
						'type' => 'Text',
						'name' => 'post_tags_description',
						'className' => 'text-field',
						'label' => __( 'Description (below field)', Forminator::DOMAIN )
					),
				)
			), // POST tags

			//custom fields
			array(
				'id' => 'post-custom',
				'type' => 'ToggleContainer',
				'name' => 'post_custom',
				'className' => 'toggle-container',
				'hide_label' => true,
				'has_content' => true,
				'hide_label' => true,
				'values' => array(
					array(
						'value' => 'true',
						'label' => __( 'This post has custom fields', Forminator::DOMAIN ),
						'labelSmall' => "true"
					)
				),
				'fields' => array(
					array(
						'id' => 'post-vars',
						'type' => 'MultiValue',
						'name' => 'custom_vars',
						'className' => 'post-var-field',
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
		return array(
			'data_status'			=> 'pending',
			'post_title_label'		=> 'Post Title',
			'post_content_label'	=> 'Post Content',
			'post_excerpt_label'	=> 'Post Excerpt',
			'post_image_label'		=> 'Featured Image',
			'post_category_label'	=> 'Category',
			'post_tags_label'		=> 'Tags'
		);
	}

	/**
	 * Return users list
	 *
	 * @since 1.0
	 * @return array
	 */
	public function list_users() {
		$users_list = array();
		$users 		= get_users( array( 'role__in' => array( 'administrator', 'editor', 'author' ) , 'fields' => array( 'ID', 'display_name' ) ) );
		foreach ( $users as $user ) {
			$users_list[] = array(
				'value' => $user->ID,
				'label' => ucfirst( $user->display_name )
			);
		}
		return $users_list;
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
	 * @param $field
	 *
	 * @return mixed
	 */
	public function markup( $field ) {
		$required    = self::get_property( 'required', $field, false );
		$id = $name  = self::get_property( 'element_id', $field );

		$html  = $this->get_post_title( $id, $name, $field, $required );
		$html .= $this->get_post_content( $id, $name, $field, $required );
		$html .= $this->get_post_excerpt( $id, $name, $field, $required );
		$html .= $this->get_post_image( $id, $name, $field, $required );
		$html .= $this->get_post_category( $id, $name, $field, $required );
		$html .= $this->get_post_tag( $id, $name, $field, $required );
		$html .= $this->_render_custom_fields( $id, $name, $field, $required );

		return apply_filters( 'forminator_field_postdata_markup', $html, $field );
	}

	/**
	 * Return post title
	 *
	 * @since 1.0
	 * @param $id
	 * @param $name
	 * @param $field
	 * @param $required
	 *
	 * @return string
	 */
	public function get_post_title( $id, $name, $field, $required ) {
		return $this->_get_post_field( $id, $name, $field, $required, 'post_title', 'text', 'forminator-input', 'post-title' );
	}

	/**
	 * Return post content
	 *
	 * @since 1.0
	 * @param $id
	 * @param $name
	 * @param $field
	 * @param $required
	 *
	 * @return string
	 */
	public function get_post_content( $id, $name, $field, $required ) {
		return $this->_get_post_field( $id, $name, $field, $required, 'post_content', 'textarea', 'forminator-textarea', 'post-content' );
	}

	/**
	 * Return post excerpt
	 *
	 * @since 1.0
	 * @param $id
	 * @param $name
	 * @param $field
	 * @param $required
	 *
	 * @return string
	 */
	public function get_post_excerpt( $id, $name, $field, $required ) {
		return $this->_get_post_field( $id, $name, $field, $required, 'post_excerpt', 'textarea', 'forminator-textarea', 'post-excerpt' );
	}

	/**
	 * Return post featured image
	 *
	 * @since 1.0
	 * @param $id
	 * @param $name
	 * @param $field
	 * @param $required
	 *
	 * @return string
	 */
	public function get_post_image( $id, $name, $field, $required ) {
		return $this->_get_post_field( $id, $name, $field, $required, 'post_image', 'file', 'forminator-upload', 'post-image' );
	}

	/**
	 * Return categories
	 *
	 * @since 1.0
	 * @param $id
	 * @param $name
	 * @param $field
	 * @param $required
	 *
	 * @return string
	 */
	public function get_post_category( $id, $name, $field, $required ) {
		$options = array();
		$categories = get_categories( array(
			'orderby'    => 'name',
			'order'      => 'ASC',
			'hide_empty' => false
		) );

		foreach( $categories as $category ) {
			$options[] = array(
				'value' => $category->term_id,
				'label' => $category->name
			);
		}

		return $this->_get_post_field( $id, $name, $field, $required, 'post_category', 'select', 'forminator-select', 'post-category', $options );
	}

	/**
	 * Return tags
	 *
	 * @since 1.0
	 * @param $id
	 * @param $name
	 * @param $field
	 * @param $required
	 *
	 * @return string
	 */
	public function get_post_tag( $id, $name, $field, $required ) {
		$options = array();
		$tags = get_tags( array(
			'hide_empty' => false
		) );

		foreach( $tags as $tag ) {
			$options[] = array(
				'value' => $tag->term_id,
				'label' => $tag->name
			);
		}

		return $this->_get_post_field( $id, $name, $field, $required, 'post_tags', 'select', 'forminator-select', 'post-tags', $options );
	}

	/**
	 * Return post field
	 *
	 * @since 1.0
	 * @param $id
	 * @param $name
	 * @param $field
	 * @param $required
	 * @param $field_name
	 * @param $type
	 * @param $class
	 * @param $input_suffix
	 * @param array $options
	 * @param string $value
	 *
	 * @return string
	 */
	private function _get_post_field( $id, $name, $field, $required, $field_name, $type, $class, $input_suffix, $options = array(), $value= '' ) {
		$html = '';
		$field_enabled  = self::get_property( $field_name, $field, '' );

		if ( !empty( $field_enabled ) ) {
			$cols        	= 12;
			$placeholder 	= self::get_property( $field_name . '_placeholder', $field );
			$label 		 	= self::get_property( $field_name . '_label', $field );
			$description 	= self::get_property( $field_name . '_description', $field );
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

			if ( $type == 'textarea' ) {
				$html .= self::create_textarea( $field_markup, $label, $description, $required );
			} else if ( $type == 'select' ) {
				if ( empty( $options ) ) {
					unset( $field_markup['required'] );
				}
				$html .= self::create_select( $field_markup, $label, $options, $value, $description, $required );
			} else if ( $type == 'file' ){
				if ( $required ) {
					$html .= '<div class="forminator-field--label">';
					$html .= sprintf( '<label class="forminator-label">%s%s</label>', $label, forminator_get_required_icon() );
					$html .= '</div>';
				} else {
					$html .= sprintf( '<div class="forminator-field--label"><label class="forminator-label">%s</label></div>', $label );
				}
				$html .= self::create_file_upload( $id . '-' . $input_suffix, $name . '-' . $input_suffix );
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
	 * @param $id
	 * @param $name
	 * @param $field
	 * @param $required
	 *
	 * @return string
	 */
	private function _render_custom_fields( $id, $name, $field, $required ) {
		$html = '';
		$cols = 12;
		$has_custom_fields 	= self::get_property( 'post_custom', $field, false );

		if ( $has_custom_fields ) {
			$custom_vars = self::get_property( 'custom_vars', $field );

			if ( !empty( $custom_vars ) ) {
				$html .= '<div class="forminator-row forminator-row--inner">';

				foreach ( $custom_vars as $variable ) {
					$html .= sprintf( '<div class="forminator-col forminator-col-%s">', $cols );
					$value 			= !empty( $variable['value'] ) ? $variable['value'] : sanitize_title( $variable['label'] );
					$input_id 		= $id . '-post_meta-' . $value;
					$label 			= $variable['label'];
					$field_markup 	= array(
						'type' 			=> 'text',
						'class' 		=> 'forminator-input',
						'name' 			=> $input_id,
						'id' 			=> $input_id,
						'placeholder' 	=> $label
					);
					$html .= self::create_input( $field_markup, $label, '' );
					$html .= '</div>';
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
	 * @param array $field
	 * @param array|string $data
	 */
	public function validate( $field, $data ) {
		if ( $this->is_required( $field ) ) {
			$id = self::get_property( 'element_id', $field );

			if ( empty( $data ) ) {
				$this->validation_message[ $id ] = __( 'This field is required. Please fill in post data', Forminator::DOMAIN );
			} else  if ( is_array( $data ) ) {
				$post_title		= self::get_property( 'post_title', $field, '' );
				$post_content 	= self::get_property( 'post_content', $field, '' );
				$post_excerpt 	= self::get_property( 'post_excerpt', $field, '' );
				$post_image 	= self::get_property( 'post_image', $field, '' );
				$post_category 	= self::get_property( 'post_category', $field, '' );
				$post_tags 		= self::get_property( 'post_tags', $field, '' );

				$title 			= isset( $data['post-title'] ) ? $data['post-title'] : '';
				$content 		= isset( $data['post-content'] ) ? $data['post-content'] : '';
				$excerpt 		= isset( $data['post-excerpt'] ) ? $data['post-excerpt'] : '';
				$image 			= isset( $data['post-image'] ) ? $data['post-image'] : '';
				$category 		= isset( $data['post-category'] ) ? $data['post-category'] : '';
				$tags 			= isset( $data['post-tags'] ) ? $data['post-tags'] : '';
				if ( !empty( $post_title ) && empty( $title ) ) {
					$this->validation_message[ $id . '-post-title' ] = __( 'This field is required. Please enter the post title',  Forminator::DOMAIN );
				}
				if ( !empty( $post_content ) && empty( $content ) ) {
					$this->validation_message[ $id . '-post-content' ] = __( 'This field is required. Please enter the post content',  Forminator::DOMAIN );
				}
				if ( !empty( $post_excerpt ) && empty( $excerpt ) ) {
					$this->validation_message[ $id . '-post-excerpt' ] = __( 'This field is required. Please enter the post excerpt',  Forminator::DOMAIN );
				}
				if ( !empty( $post_image ) && empty( $image ) ) {
					$this->validation_message[ $id . '-post-image' ] = __( 'This field is required. Please upload a post image',  Forminator::DOMAIN );
				}
				if ( !empty( $post_category ) && empty( $category ) ) {
					$this->validation_message[ $id . '-post-category' ] = __( 'This field is required. Please select a post category',  Forminator::DOMAIN );
				}
				if ( !empty( $post_tags ) && empty( $tags ) ) {
					$this->validation_message[ $id . '-post-tags' ] = __( 'This field is required. Please select a post tag',  Forminator::DOMAIN );
				}
			}
		}
	}

	/**
	 * Upload post image
	 *
	 * @since 1.0
	 * @param array $field - the field
	 * @param string $field_name - the field name
	 *
	 * @return array|bool - if success, return an array
	 */
	public function upload_post_image( $field, $field_name ) {
		$post_image 	= self::get_property( 'post_image', $field, '' );

		if ( !empty( $post_image ) ) {
			if ( isset( $_FILES[$field_name] ) ) {
				if ( isset( $_FILES[$field_name]['name'] ) && !empty( $_FILES[$field_name]['name'] ) ) {
					$file_name 			= $_FILES[$field_name]['name'];
					$file_data 			= file_get_contents( $_FILES[$field_name]['tmp_name'] );
					$upload_dir       	= wp_upload_dir(); // Set upload folder
					$unique_file_name 	= wp_unique_filename( $upload_dir['path'], $file_name );
					$filename         	= basename( $unique_file_name ); // Create base file name

					if ( wp_mkdir_p( $upload_dir['path'] ) ) {
						$file 	= $upload_dir['path'] . '/' . $filename;
					} else {
						$file 	= $upload_dir['basedir'] . '/' . $filename;
					}

					// Create the  file on the server
					file_put_contents( $file, $file_data );

					// Check image file type
					$wp_filetype 	= wp_check_filetype( $filename, null );
					$image_exts 	= array( 'jpg', 'jpeg', 'jpe', 'gif', 'png' );
					if ( in_array( $wp_filetype['ext'], $image_exts ) ) {
						// Set attachment data
						$attachment = array(
							'post_mime_type' => $wp_filetype['type'],
							'post_title'     => sanitize_file_name( $filename ),
							'post_content'   => '',
							'post_status'    => 'inherit'
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
								'uploaded_file' => $uploaded_file
							);
						}
					}
				}
			}
			return array(
				'attachment_id' => 0,
				'uploaded_file' => 0
			);
		}

		return true;
	}

	/**
	 * Save post
	 *
	 * @since 1.0
	 * @param array $field - field array
	 * @param array $data - post data
	 *
	 * @return bool|int - success is post id
	 */
	public function save_post( $field, $data ) {
		$post_author	= self::get_property( 'select_author', $field, 1 );
		$post_status	= self::get_property( 'data_status', $field, 'draft' );
		$title 			= isset( $data['post-title'] ) ? $data['post-title'] : '';
		$content 		= isset( $data['post-content'] ) ? $data['post-content'] : '';
		$excerpt 		= isset( $data['post-excerpt'] ) ? $data['post-excerpt'] : '';
		$image 			= isset( $data['post-image'] ) ? $data['post-image'] : '';
		$category 		= isset( $data['post-category'] ) ? $data['post-category'] : '';
		$tags 			= isset( $data['post-tags'] ) ? $data['post-tags'] : '';
		$post_meta 		= isset( $data['post-custom'] ) ? $data['post-custom'] : '';

		$post 			= array(
			'post_author' 		=> $post_author,
			'post_content' 		=> $content,
			'post_excerpt' 		=> $excerpt,
			'post_name' 		=> sanitize_text_field( $title ),
			'post_status' 		=> $post_status,
			'post_title' 		=> $title,
		);

		if ( !empty( $category ) ) {
			$post['post_category'] = array( intval( $category ) );
		}

		if ( !empty( $tags ) ) {
			$post['tags_input'] = array( intval( $tags ) );
		}

		//trigger wp_error for is_wp_error to be correctly identified
		$post_id = wp_insert_post( $post, true );
		if ( !is_wp_error( $post_id ) ) {
			$post_image 	= self::get_property( 'post_image', $field, '' );
			if ( !empty( $post_image ) && !empty( $image ) && is_array( $image ) ) {
				set_post_thumbnail( $post_id, $image['attachment_id'] );
			}

			if ( !empty( $post_meta ) ) {
				foreach ( $post_meta as $meta ) {
					add_post_meta( $post_id, $meta['key'], $meta );
				}
				add_post_meta( $post_id, '_has_forminator_meta', true );
			}

			return $post_id;
		}

		return false;
	}
}