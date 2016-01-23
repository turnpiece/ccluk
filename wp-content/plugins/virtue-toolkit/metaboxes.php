<?php 
// Build Metaboxs for gallery
function kadtool_gallery_field( $field, $meta ) {
    echo '<div class="kad-gallery kad_widget_image_gallery">';
    echo '<div class="gallery_images">';
    $attachments = array_filter( explode( ',', $meta ) );
             if ( $attachments )
            foreach ( $attachments as $attachment_id ) {
                $img = wp_get_attachment_image_src($attachment_id, 'thumbnail');
                $imgfull = wp_get_attachment_image_src($attachment_id, 'full');
                    echo '<a class="of-uploaded-image" target="_blank" rel="external" href="' . esc_url($imgfull[0]) . '">';
                    echo '<img class="gallery-widget-image" id="gallery_widget_image_'.esc_attr($attachment_id). '" src="' . esc_url($img[0]) . '" />';
                    echo '</a>';
                }
    echo '</div>';
    echo ' <input type="hidden" id="' . esc_attr($field['id']) . '" name="' . esc_attr($field['id']) . '" class="gallery_values" value="' . esc_attr($meta) . '" />';
    echo '<a href="#" onclick="return false;" id="edit-gallery" class="gallery-attachments button button-primary">' . __('Add/Edit Gallery', 'virtue-toolkit') . '</a>';
    echo '<a href="#" onclick="return false;" id="clear-gallery" class="gallery-attachments button">' . __('Clear Gallery', 'virtue-toolkit') . '</a>';
    echo '</div>';

    if ( ! empty( $field['desc'] ) ) echo '<p class="cmb_metabox_description">' . $field['desc'] . '</p>';
}
add_filter( 'cmb_render_kad_gallery', 'kadtool_gallery_field', 10, 2 );

function kadtool_gallery_field_sanitise( $field, $meta ) {
    if ( empty( $meta ) ) {
        $meta = '';
    } else {
        $meta = $meta;
    }
    return $meta;
}
$the_theme = wp_get_theme();
if( ($the_theme->get( 'Name' ) == 'Pinnacle' && $the_theme->get( 'Version') >= '1.0.6' ) || ($the_theme->get( 'Template') == 'pinnacle') ) {
add_filter( 'cmb_meta_boxes', 'kadence_pinnacletoolkit_metaboxes', 100 );
}

function kadence_pinnacletoolkit_metaboxes( array $meta_boxes ) {

	// Start with an underscore to hide fields from custom fields list
	$prefix = '_kad_';
$meta_boxes[] = array(
				'id'         => 'subtitle_metabox',
				'title'      => __( "Page Title and Subtitle", 'virtue-toolkit' ),
				'pages'      => array( 'page' ), // Post type
				'context'    => 'normal',
				'priority'   => 'default',
				'show_names' => true, // Show field names on the left
				'fields' => array(
					array(
						'name' => __( "Subtitle", 'virtue-toolkit' ),
						'desc' => __( "Subtitle will go below page title", 'virtue-toolkit' ),
						'id'   => $prefix . 'subtitle',
						'type' => 'textarea_code',
					),
					array(
						'name'    => __("Hide Page Title", 'virtue-toolkit' ),
						'desc'    => '',
						'id'      => $prefix . 'pagetitle_hide',
						'type'    => 'select',
						'options' => array(
							array( 'name' => __("Default", 'virtue-toolkit' ), 'value' => 'default', ),
							array( 'name' => __("Show", 'virtue-toolkit' ), 'value' => 'show', ),
							array( 'name' => __("Hide", 'virtue-toolkit' ), 'value' => 'hide', ),
						),
					),
					array(
						'name'    => __("Page Title background behind Header", 'virtue-toolkit' ),
						'desc'    => '',
						'id'      => $prefix . 'pagetitle_behind_head',
						'type'    => 'select',
						'options' => array(
							array( 'name' => __("Default", 'virtue-toolkit' ), 'value' => 'default', ),
							array( 'name' => __("Place behind Header", 'virtue-toolkit' ), 'value' => 'true', ),
							array( 'name' => __("Don't place behind Header", 'virtue-toolkit' ), 'value' => 'false', ),
						),
					),
				)
			);
$meta_boxes[] = array(
				'id'         => 'subtitle_metabox',
				'title'      => __( "Post Title and Subtitle", 'virtue-toolkit' ),
				'pages'      => array( 'product', 'post', 'portfolio'), // Post type
				'context'    => 'normal',
				'priority'   => 'default',
				'show_names' => true, // Show field names on the left
				'fields' => array(
					array(
						'name' => __( "Post Header Title", 'virtue-toolkit' ),
						'desc' => __( "Post Header Title", 'virtue-toolkit' ),
						'id'   => $prefix . 'post_header_title',
						'type' => 'textarea_code',
					),
					array(
						'name' => __( "Subtitle", 'virtue-toolkit' ),
						'desc' => __( "Subtitle will go below post title", 'virtue-toolkit' ),
						'id'   => $prefix . 'subtitle',
						'type' => 'textarea_code',
					),
					array(
						'name'    => __("Hide Page Title", 'virtue-toolkit' ),
						'desc'    => '',
						'id'      => $prefix . 'pagetitle_hide',
						'type'    => 'select',
						'options' => array(
							array( 'name' => __("Default", 'virtue-toolkit' ), 'value' => 'default', ),
							array( 'name' => __("Show", 'virtue-toolkit' ), 'value' => 'show', ),
							array( 'name' => __("Hide", 'virtue-toolkit' ), 'value' => 'hide', ),
						),
					),
					array(
						'name'    => __("Page Title background behind Header", 'virtue-toolkit' ),
						'desc'    => '',
						'id'      => $prefix . 'pagetitle_behind_head',
						'type'    => 'select',
						'options' => array(
							array( 'name' => __("Default", 'virtue-toolkit' ), 'value' => 'default', ),
							array( 'name' => __("Place behind Header", 'virtue-toolkit' ), 'value' => 'true', ),
							array( 'name' => __("Don't place behind Header", 'virtue-toolkit' ), 'value' => 'false', ),
						),
					),
				)
			);
$meta_boxes[] = array(
				'id'         => 'gallery_post_metabox',
				'title'      => __("Gallery Post Options", 'virtue-toolkit'),
				'pages'      => array( 'post',), // Post type
				//'show_on' => array( 'key' => 'format', 'value' => 'standard'),
				'context'    => 'normal',
				'priority'   => 'high',
				'show_names' => true, // Show field names on the left
				'fields' => array(
			
			array(
				'name'    => __("Post Head Content", 'virtue-toolkit' ),
				'desc'    => '',
				'id'      => $prefix . 'gallery_blog_head',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __("Gallery Post Default", 'virtue-toolkit' ), 'value' => 'default', ),
					array( 'name' => __("Image Slider - (Flex Slider)", 'virtue-toolkit' ), 'value' => 'flex', ),
					array( 'name' => __("Carousel Slider - (Caroufedsel Slider)", 'virtue-toolkit' ), 'value' => 'carouselslider', ),
					array( 'name' => __("None", 'virtue-toolkit' ), 'value' => 'none', ),
				),
			),
			array(
				'name' => __("Post Slider Gallery", 'virtue-toolkit' ),
				'desc' => __("Add images for gallery here", 'virtue-toolkit' ),
				'id'   => $prefix . 'image_gallery',
				'type' => 'kad_gallery',
			),
			array(
				'name' => __("Max Slider Height", 'virtue-toolkit' ),
				'desc' => __("Default is: 400 (Note: just input number, example: 350)", 'virtue-toolkit' ),
				'id'   => $prefix . 'gallery_posthead_height',
				'type' => 'text_small',
			),
			array(
				'name' => __("Max Slider Width", 'virtue-toolkit' ),
				'desc' => __("Default is: 848 or 1140 on fullwidth posts (Note: just input number, example: 650, only applys to Image Slider)", 'virtue-toolkit' ),
				'id'   => $prefix . 'gallery_posthead_width',
				'type' => 'text_small',
			),
			array(
				'name'    => __("Post Summary", 'virtue-toolkit' ),
				'desc'    => '',
				'id'      => $prefix . 'gallery_post_summery',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Gallery Post Default', 'virtue-toolkit' ), 'value' => 'default', ),
					array( 'name' => __('Portrait Image (feature image)', 'virtue-toolkit'), 'value' => 'img_portrait', ),
					array( 'name' => __('Landscape Image (feature image)', 'virtue-toolkit'), 'value' => 'img_landscape', ),
					array( 'name' => __('Portrait Image Slider', 'virtue-toolkit'), 'value' => 'slider_portrait', ),
					array( 'name' => __('Landscape Image Slider', 'virtue-toolkit'), 'value' => 'slider_landscape', ),
				),
			),
		),
	);
$meta_boxes[] = array(
				'id'         => 'video_post_metabox',
				'title'      => __("Video Post Options", 'virtue-toolkit'),
				'pages'      => array( 'post',), // Post type
				//'show_on' => array( 'key' => 'format', 'value' => 'standard'),
				'context'    => 'normal',
				'priority'   => 'high',
				'show_names' => true, // Show field names on the left
				'fields' => array(
			
			array(
				'name'    => __("Post Head Content", 'virtue-toolkit' ),
				'desc'    => '',
				'id'      => $prefix . 'video_blog_head',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __("Video Post Default", 'virtue-toolkit' ), 'value' => 'default', ),
					array( 'name' => __("Video", 'virtue-toolkit' ), 'value' => 'video', ),
					array( 'name' => __("None", 'virtue-toolkit' ), 'value' => 'none', ),
				),
			),
			array(
				'name' => __('Video Post embed code', 'virtue-toolkit'),
				'desc' => __('Place Embed Code Here, works with youtube, vimeo. (Use the featured image for screen shot)', 'virtue-toolkit'),
				'id'   => $prefix . 'post_video',
				'type' => 'textarea_code',
			),
			array(
				'name' => __("Max Video Width", 'virtue-toolkit' ),
				'desc' => __("Default is: 848 or 1140 on fullwidth posts (Note: just input number, example: 650, does not apply to carousel slider)", 'virtue-toolkit' ),
				'id'   => $prefix . 'video_posthead_width',
				'type' => 'text_small',
			),
			array(
				'name'    => __("Post Summary", 'virtue-toolkit' ),
				'desc'    => '',
				'id'      => $prefix . 'video_post_summery',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Video Post Default', 'virtue-toolkit' ), 'value' => 'default', ),
					array( 'name' => __('Video - (when possible)', 'virtue-toolkit'), 'value' => 'video', ),
					array( 'name' => __('Portrait Image (feature image)', 'virtue-toolkit'), 'value' => 'img_portrait', ),
					array( 'name' => __('Landscape Image (feature image)', 'virtue-toolkit'), 'value' => 'img_landscape', ),
				),
			),
		),
	);
$meta_boxes[] = array(
				'id'         => 'portfolio_post_metabox',
				'title'      => __('Portfolio Post Options', 'virtue-toolkit'),
				'pages'      => array( 'portfolio' ), // Post type
				'context'    => 'normal',
				'priority'   => 'high',
				'show_names' => true, // Show field names on the left
				'fields' => array(
			
			array(
				'name'    => __('Project Layout', 'virtue-toolkit'),
				'desc'    => '<a href="http://docs.kadencethemes.com/pinnacle/#portfolio_posts" target="_blank" >Whats the difference?</a>',
				'id'      => $prefix . 'ppost_layout',
				'type'    => 'radio_inline',
				'options' => array(
					array( 'name' => __('Beside 40%', 'virtue-toolkit'), 'value' => 'beside', ),
					array( 'name' => __('Beside 33%', 'virtue-toolkit'), 'value' => 'besidesmall', ),
					array( 'name' => __('Above', 'virtue-toolkit'), 'value' => 'above', ),
					array( 'name' => __('Three Rows', 'virtue-toolkit'), 'value' => 'three', ), 
				),
			),
			array(
				'name'    => __('Project Options', 'virtue-toolkit'),
				'desc'    => '',
				'id'      => $prefix . 'ppost_type',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Image', 'virtue-toolkit'), 'value' => 'image', ),
					array( 'name' => __('Image Slider (Flex Slider)', 'virtue-toolkit'), 'value' => 'flex', ),
					array( 'name' => __('Carousel Slider', 'virtue-toolkit'), 'value' => 'carousel', ),
					array( 'name' => __('Video', 'virtue-toolkit'), 'value' => 'video', ),
					array( 'name' => __('Image Grid', 'virtue-toolkit'), 'value' => 'imagegrid', ),
					array( 'name' => __('None', 'virtue-toolkit'), 'value' => 'none', ),
				),
			),
			array(
				'name'    => __('Columns (Only for Image Grid option)', 'virtue-toolkit'),
				'desc'    => '',
				'id'      => $prefix . 'portfolio_img_grid_columns',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Four Column', 'virtue-toolkit'), 'value' => '4', ),
					array( 'name' => __('Three Column', 'virtue-toolkit'), 'value' => '3', ),
					array( 'name' => __('Two Column', 'virtue-toolkit'), 'value' => '2', ),
					array( 'name' => __('Five Column', 'virtue-toolkit'), 'value' => '5', ),
					array( 'name' => __('Six Column', 'virtue-toolkit'), 'value' => '6', ),
				),
			),
			array(
				'name' => __("Portfolio Slider/Images", 'virtue-toolkit' ),
				'desc' => __("Add images for post here", 'virtue-toolkit' ),
				'id'   => $prefix . 'image_gallery',
				'type' => 'kad_gallery',
			),
			array(
				'name' => __("Max Image/Slider Height", 'virtue-toolkit' ),
				'desc' => __("Default is: 450 (Note: just input number, example: 350)", 'virtue-toolkit' ),
				'id'   => $prefix . 'posthead_height',
				'type' => 'text_small',
			),
			array(
				'name' => __("Max Image/Slider Width", 'virtue-toolkit' ),
				'desc' => __("Default is: 670 or 1140 on above or three row layouts (Note: just input number, example: 650)", 'virtue-toolkit' ),
				'id'   => $prefix . 'posthead_width',
				'type' => 'text_small',
			),
			array(
				'name' => __('Value 01 Title', 'virtue-toolkit'),
				'desc' => __('ex. Project Type:', 'virtue-toolkit'),
				'id'   => $prefix . 'project_val01_title',
				'type' => 'text_medium',
			),
			array(
				'name' => __('Value 01 Description', 'virtue-toolkit'),
				'desc' => __('ex. Character Illustration', 'virtue-toolkit'),
				'id'   => $prefix . 'project_val01_description',
				'type' => 'text_medium',
			),
			array(
				'name' => __('Value 02 Title', 'virtue-toolkit'),
				'desc' => __('ex. Skills Needed:', 'virtue-toolkit'),
				'id'   => $prefix . 'project_val02_title',
				'type' => 'text_medium',
			),
			array(
				'name' => __('Value 02 Description', 'virtue-toolkit'),
				'desc' => __('ex. Photoshop, Illustrator', 'virtue-toolkit'),
				'id'   => $prefix . 'project_val02_description',
				'type' => 'text_medium',
			),
			array(
				'name' => __('Value 03 Title', 'virtue-toolkit'),
				'desc' => __('ex. Customer:', 'virtue-toolkit'),
				'id'   => $prefix . 'project_val03_title',
				'type' => 'text_medium',
			),
			array(
				'name' => __('Value 03 Description', 'virtue-toolkit'),
				'desc' => __('ex. Example Inc', 'virtue-toolkit'),
				'id'   => $prefix . 'project_val03_description',
				'type' => 'text_medium',
			),
			array(
				'name' => __('Value 04 Title', 'virtue-toolkit'),
				'desc' => __('ex. Project Year:', 'virtue-toolkit'),
				'id'   => $prefix . 'project_val04_title',
				'type' => 'text_medium',
			),
			array(
				'name' => __('Value 04 Description', 'virtue-toolkit'),
				'desc' => __('ex. 2013', 'virtue-toolkit'),
				'id'   => $prefix . 'project_val04_description',
				'type' => 'text_medium',
			),
			array(
				'name' => __('External Website', 'virtue-toolkit'),
				'desc' => __('ex. Website:', 'virtue-toolkit'),
				'id'   => $prefix . 'project_val05_title',
				'type' => 'text_medium',
			),
			array(
				'name' => __('Website Address', 'virtue-toolkit'),
				'desc' => __('ex. http://www.example.com', 'virtue-toolkit'),
				'id'   => $prefix . 'project_val05_description',
				'type' => 'text_medium',
			),
			array(
						'name' => __('If Video Project', 'virtue-toolkit'),
						'desc' => __('Place Embed Code Here, works with youtube, vimeo...', 'virtue-toolkit'),
						'id'   => $prefix . 'post_video',
						'type' => 'textarea_code',
					),
				
		),
	);
	$meta_boxes[] = array(
				'id'         => 'portfolio_post_carousel_metabox',
				'title'      => __('Bottom Carousel Options', 'virtue-toolkit'),
				'pages'      => array( 'portfolio' ), // Post type
				'context'    => 'normal',
				'priority'   => 'high',
				'show_names' => true, // Show field names on the left
				'fields' => array(
			array(
				'name' => __('Carousel Title', 'virtue-toolkit'),
				'desc' => __('ex. Similar Projects', 'virtue-toolkit'),
				'id'   => $prefix . 'portfolio_carousel_title',
				'type' => 'text_medium',
			),
			array(
				'name' => __('Bottom Portfolio Carousel', 'virtue-toolkit'),
				'desc' => __('Display a carousel with portfolio items below project?', 'virtue-toolkit'),
				'id'   => $prefix . 'portfolio_carousel_recent',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Default', 'virtue-toolkit'), 'value' => 'defualt', ),
					array( 'name' => __('No', 'virtue-toolkit'), 'value' => 'no', ),
					array( 'name' => __('Yes', 'virtue-toolkit'), 'value' => 'yes', ),
				),
			),
			array(
				'name' => __('Carousel Items', 'virtue-toolkit'),
				'desc' => '',
				'id'   => $prefix . 'portfolio_carousel_group',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Default', 'virtue-toolkit'), 'value' => 'defualt', ),
					array( 'name' => __('All Portfolio Posts', 'virtue-toolkit'), 'value' => 'all', ),
					array( 'name' => __('Only of same Portfolio Type', 'virtue-toolkit'), 'value' => 'cat', ),
				),
			),
			array(
				'name' => __('Carousel Order', 'virtue-toolkit'),
				'desc' => '',
				'id'   => $prefix . 'portfolio_carousel_order',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Menu Order', 'virtue-toolkit'), 'value' => 'menu_order', ),
					array( 'name' => __('Title', 'virtue-toolkit'), 'value' => 'title', ),
					array( 'name' => __('Date', 'virtue-toolkit'), 'value' => 'date', ),
					array( 'name' => __('Random', 'virtue-toolkit'), 'value' => 'rand', ),
				),
			),
				
		),
	);
			$meta_boxes[] = array(
				'id'         => 'portfolio_metabox',
				'title'      => __('Portfolio Page Options', 'virtue-toolkit'),
				'pages'      => array( 'page' ), // Post type
				'show_on' => array('key' => 'page-template', 'value' => array( 'template-portfolio-grid.php')),
				'context'    => 'normal',
				'priority'   => 'high',
				'show_names' => true, // Show field names on the left
				'fields' => array(
			array(
				'name'    => __('Style', 'virtue-toolkit'),
				'desc'    => '',
				'id'      => $prefix . 'portfolio_style',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Default', 'virtue-toolkit'), 'value' => 'default', ),
					array( 'name' => __('Post Boxes', 'virtue-toolkit'), 'value' => 'padded_style', ),
					array( 'name' => __('Flat with Margin', 'virtue-toolkit'), 'value' => 'flat-w-margin', ),
				),
			),
			array(
				'name'    => __('Hover Style', 'virtue-toolkit'),
				'desc'    => '',
				'id'      => $prefix . 'portfolio_hover_style',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Default', 'virtue-toolkit'), 'value' => 'default', ),
					array( 'name' => __('Light', 'virtue-toolkit'), 'value' => 'p_lightstyle', ),
					array( 'name' => __('Dark', 'virtue-toolkit'), 'value' => 'p_darkstyle', ),
					array( 'name' => __('Primary Color', 'virtue-toolkit'), 'value' => 'p_primarystyle', ),
				),
			),
			array(
				'name'    => __('Columns', 'virtue-toolkit'),
				'desc'    => '',
				'id'      => $prefix . 'portfolio_columns',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Four Column', 'virtue-toolkit'), 'value' => '4', ),
					array( 'name' => __('Three Column', 'virtue-toolkit'), 'value' => '3', ),
					array( 'name' => __('Two Column', 'virtue-toolkit'), 'value' => '2', ),
					array( 'name' => __('Five Column', 'virtue-toolkit'), 'value' => '5', ),
				),
			),
			array(
                'name' => __('Portfolio Work Types', 'virtue-toolkit'),
                'id' => $prefix .'portfolio_type',
                'type' => 'imag_select_taxonomy',
                'taxonomy' => 'portfolio-type',
            ),
            array(
				'name'    => __('Order Items By', 'virtue-toolkit'),
				'desc'    => '',
				'id'      => $prefix . 'portfolio_order',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Menu Order', 'virtue-toolkit'), 'value' => 'menu_order', ),
					array( 'name' => __('Title', 'virtue-toolkit'), 'value' => 'title', ),
					array( 'name' => __('Date', 'virtue-toolkit'), 'value' => 'date', ),
					array( 'name' => __('Random', 'virtue-toolkit'), 'value' => 'rand', ),
				),
			),
			array(
				'name'    => __('Items per Page', 'virtue-toolkit'),
				'desc'    => __('How many portfolio items per page', 'virtue-toolkit'),
				'id'      => $prefix . 'portfolio_items',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('All', 'virtue-toolkit'), 'value' => 'all', ),
					array( 'name' => '3', 'value' => '3', ),
					array( 'name' => '4', 'value' => '4', ),
					array( 'name' => '5', 'value' => '5', ),
					array( 'name' => '6', 'value' => '6', ),
					array( 'name' => '7', 'value' => '7', ),
					array( 'name' => '8', 'value' => '8', ),
					array( 'name' => '9', 'value' => '9', ),
					array( 'name' => '10', 'value' => '10', ),
					array( 'name' => '11', 'value' => '11', ),
					array( 'name' => '12', 'value' => '12', ),
					array( 'name' => '13', 'value' => '13', ),
					array( 'name' => '14', 'value' => '14', ),
					array( 'name' => '15', 'value' => '15', ),
					array( 'name' => '16', 'value' => '16', ),
				),
			),
			array(
				'name'    => __('Image Ratio?', 'virtue-toolkit'),
				'desc'    => '',
				'id'      => $prefix . 'portfolio_img_ratio',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Default', 'virtue-toolkit'), 'value' => 'default', ),
					array( 'name' => __('Square 1:1', 'virtue-toolkit'), 'value' => 'square', ),
					array( 'name' => __('Portrait 3:4', 'virtue-toolkit'), 'value' => 'portrait', ),
					array( 'name' => __('Landscape 4:3', 'virtue-toolkit'), 'value' => 'landscape', ),
					array( 'name' => __('Wide Landscape 4:2', 'virtue-toolkit'), 'value' => 'widelandscape', ),
				),
			),
			array(
				'name' => __('Display Item Work Types', 'virtue-toolkit'),
				'desc' => '',
				'id'   => $prefix . 'portfolio_item_types',
				'type' => 'checkbox',
			),
			array(
				'name' => __('Display Item Excerpt', 'virtue-toolkit'),
				'desc' => '',
				'id'   => $prefix . 'portfolio_item_excerpt',
				'type' => 'checkbox',
			),
			array(
				'name' => __('Add Lightbox link in each item', 'virtue-toolkit'),
				'desc' => '',
				'id'   => $prefix . 'portfolio_lightbox',
				'type' => 'checkbox',
			),
				
			));
			$meta_boxes[] = array(
				'id'         => 'pagefeature_metabox',
				'title'      => __('Feature Page Options', 'virtue-toolkit'),
				'pages'      => array( 'page' ), // Post type
				'show_on' => array('key' => 'page-template', 'value' => array( 'template-feature.php')),
				'context'    => 'normal',
				'priority'   => 'high',
				'show_names' => true, // Show field names on the left
				'fields' => array(
			
			array(
				'name'    => __('Header Options', 'virtue-toolkit'),
				'desc'    => __('If image slider make sure images uploaded are at-least 1170px wide.', 'virtue-toolkit'),
				'id'      => $prefix . 'page_head',
				'type'    => 'select',
				'defualt' => 'pagetitle',
				'options' => array(
					array( 'name' => __('Page Title', 'virtue-toolkit'), 'value' => 'pagetitle', ),
					array( 'name' => __('Image Slider (Flex Slider)', 'virtue-toolkit'), 'value' => 'flex', ),
					array( 'name' => __('Carousel Slider', 'virtue-toolkit'), 'value' => 'carousel', ),
					array( 'name' => __('Video', 'virtue-toolkit'), 'value' => 'video', ),
				),
			),
			array(
				'name' => __("Slider Images", 'virtue-toolkit' ),
				'desc' => __("Add for flex, carousel, and image carousel.", 'virtue-toolkit' ),
				'id'   => $prefix . 'image_gallery',
				'type' => 'kad_gallery',
			),
			array(
				'name' => __('If Cyclone Slider', 'virtue-toolkit'),
				'desc' => __('Paste Cyclone slider shortcode here (example: [cycloneslider id="slider1"])', 'virtue-toolkit'),
				'id'   => $prefix . 'shortcode_slider',
				'type' => 'textarea_code',
			),
			array(
				'name' => __('Max Image/Slider Height', 'virtue-toolkit'),
				'desc' => __('Default is: 400 (Note: just input number, example: 350)', 'virtue-toolkit'),
				'id'   => $prefix . 'posthead_height',
				'type' => 'text_small',
			),
			array(
				'name' => __("Max Image/Slider Width", 'virtue-toolkit' ),
				'desc' => __("Default is: 1140 on fullwidth posts (Note: just input number, example: 650, does not apply to Carousel slider)", 'virtue-toolkit' ),
				'id'   => $prefix . 'posthead_width',
				'type' => 'text_small',
			),
			array(
				'name' => __('If Video Post', 'virtue-toolkit'),
				'desc' => __('Place Embed Code Here, works with youtube, vimeo...', 'virtue-toolkit'),
				'id'   => $prefix . 'post_video',
				'type' => 'textarea_code',
			),
								
			));
			$meta_boxes[] = array(
				'id'         => 'contact_metabox',
				'title'      => __('Contact Page Options', 'virtue-toolkit'),
				'pages'      => array( 'page' ), // Post type
				'show_on' => array('key' => 'page-template', 'value' => array( 'template-contact.php')),
				'context'    => 'normal',
				'priority'   => 'high',
				'show_names' => true, // Show field names on the left
				'fields' => array(
			
			array(
                'name' => __('Use Contact Form', 'virtue-toolkit'),
                'desc' => '',
                'id' => $prefix .'contact_form',
                'type'    => 'select',
				'options' => array(
					array( 'name' => __('Yes', 'virtue-toolkit'), 'value' => 'yes', ),
					array( 'name' => __('No', 'virtue-toolkit'), 'value' => 'no', ),
				),
			),
			array(
				'name' => __('Contact Form Title', 'virtue-toolkit'),
				'desc' => __('ex. Send us an Email', 'virtue-toolkit'),
				'id'   => $prefix . 'contact_form_title',
				'type' => 'text',
			),
			array(
				'name' => __('Contact Form Email Recipient', 'virtue-toolkit'),
				'desc' => __('ex. joe@gmail.com', 'virtue-toolkit'),
				'id'   => $prefix . 'contact_form_email',
				'type' => 'text',
			),
			array(
                'name' => __('Use Simple Math Question', 'virtue-toolkit'),
                'desc' => 'Adds a simple math question to form.',
                'id' => $prefix .'contact_form_math',
                'type'    => 'select',
				'options' => array(
					array( 'name' => __('Yes', 'virtue-toolkit'), 'value' => 'yes', ),
					array( 'name' => __('No', 'virtue-toolkit'), 'value' => 'no', ),
				),
			),
			array(
                'name' => __('Use Map', 'virtue-toolkit'),
                'desc' => '',
                'id' => $prefix .'contact_map',
                'type'    => 'select',
				'options' => array(
					array( 'name' => __('No', 'virtue-toolkit'), 'value' => 'no', ),
					array( 'name' => __('Yes', 'virtue-toolkit'), 'value' => 'yes', ),
				),
			),
			array(
				'name' => __('Address', 'virtue-toolkit'),
				'desc' => __('Enter your Location', 'virtue-toolkit'),
				'id'   => $prefix . 'contact_address',
				'type' => 'text',
			),
			array(
				'name'    => __('Map Type', 'virtue-toolkit'),
				'desc'    => '',
				'id'      => $prefix . 'contact_maptype',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('ROADMAP', 'virtue-toolkit'), 'value' => 'ROADMAP', ),
					array( 'name' => __('HYBRID', 'virtue-toolkit'), 'value' => 'HYBRID', ),
					array( 'name' => __('TERRAIN', 'virtue-toolkit'), 'value' => 'TERRAIN', ),
					array( 'name' => __('SATELLITE', 'virtue-toolkit'), 'value' => 'SATELLITE', ),
				),
			),
			array(
				'name' => __('Map Zoom Level', 'virtue-toolkit'),
				'desc' => __('A good place to start is 15', 'virtue-toolkit'),
				'id'   => $prefix . 'contact_zoom',
				'std'  => '15',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('1 (World View)', 'virtue-toolkit'), 'value' => '1', ),
					array( 'name' => '2', 'value' => '2', ),
					array( 'name' => '3', 'value' => '3', ),
					array( 'name' => '4', 'value' => '4', ),
					array( 'name' => '5', 'value' => '5', ),
					array( 'name' => '6', 'value' => '6', ),
					array( 'name' => '7', 'value' => '7', ),
					array( 'name' => '8', 'value' => '8', ),
					array( 'name' => '9', 'value' => '9', ),
					array( 'name' => '10', 'value' => '10', ),
					array( 'name' => '11', 'value' => '11', ),
					array( 'name' => '12', 'value' => '12', ),
					array( 'name' => '13', 'value' => '13', ),
					array( 'name' => '14', 'value' => '14', ),
					array( 'name' => '15', 'value' => '15', ),
					array( 'name' => '16', 'value' => '16', ),
					array( 'name' => '17', 'value' => '17', ),
					array( 'name' => '18', 'value' => '18', ),
					array( 'name' => '19', 'value' => '19', ),
					array( 'name' => '20', 'value' => '20', ),
					array( 'name' => __('21 (Street View)', 'virtue-toolkit'), 'value' => '21', ),
					),
			),
			array(
				'name' => __('Map Height', 'virtue-toolkit'),
				'desc' => __('Default is 300', 'virtue-toolkit'),
				'id'   => $prefix . 'contact_mapheight',
				'type' => 'text_small',
			),
			));

	return $meta_boxes;
}


$the_theme = wp_get_theme();
if( ($the_theme->get( 'Name' ) == 'Virtue' && $the_theme->get( 'Version') >= '2.3.5') || ($the_theme->get( 'Template') == 'virtue') ) {
add_filter( 'cmb_meta_boxes', 'kadence_virtuetoolkit_metaboxes', 100 );
}
function kadence_virtuetoolkit_metaboxes( array $meta_boxes ) {

	// Start with an underscore to hide fields from custom fields list
	$prefix = '_kad_';
	$meta_boxes[] = array(
				'id'         => 'post_video_metabox',
				'title'      => __('Post Video Box', 'virtue-toolkit'),
				'pages'      => array( 'post',), // Post type
				'context'    => 'normal',
				'priority'   => 'high',
				'show_names' => true, 
				'fields' => array(
			
					array(
						'name' => __('If Video Post', 'virtue-toolkit'),
						'desc' => __('Place Embed Code Here, works with youtube, vimeo...', 'virtue-toolkit'),
						'id'   => $prefix . 'post_video',
						'type' => 'textarea_code',
					),
				),
	);
		$meta_boxes[] = array(
				'id'         => 'portfolio_post_metabox',
				'title'      => __('Portfolio Post Options', 'virtue-toolkit'),
				'pages'      => array( 'portfolio' ), // Post type
				'context'    => 'normal',
				'priority'   => 'high',
				'show_names' => true, // Show field names on the left
				'fields' => array(
			
			array(
				'name'    => __('Project Layout', 'virtue-toolkit'),
				'desc'    => '<a href="http://docs.kadencethemes.com/virtue/#portfolio_posts" target="_new" >Whats the difference?</a>',
				'id'      => $prefix . 'ppost_layout',
				'type'    => 'radio_inline',
				'options' => array(
					array( 'name' => __('Beside', 'virtue-toolkit'), 'value' => 'beside', ),
					array( 'name' => __('Above', 'virtue-toolkit'), 'value' => 'above', ),
					array( 'name' => __('Three Rows', 'virtue-toolkit'), 'value' => 'three', ), 
				),
			),
			array(
				'name'    => __('Project Options', 'virtue-toolkit'),
				'desc'    => '',
				'id'      => $prefix . 'ppost_type',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Image', 'virtue-toolkit'), 'value' => 'image', ),
					array( 'name' => __('Image Slider', 'virtue-toolkit'), 'value' => 'flex', ),
					array( 'name' => __('Carousel Slider', 'virtue-toolkit'), 'value' => 'carousel', ),
					array( 'name' => __('Image Grid', 'virtue-toolkit'), 'value' => 'imagegrid', ),
					array( 'name' => __('Video', 'virtue-toolkit'), 'value' => 'video', ),
					array( 'name' => __('None', 'virtue-toolkit'), 'value' => 'none', ),
				),
			),
			array(
				'name'    => __('Columns (Only for Image Grid option)', 'virtue-toolkit'),
				'desc'    => '',
				'id'      => $prefix . 'portfolio_img_grid_columns',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Four Column', 'virtue-toolkit'), 'value' => '4', ),
					array( 'name' => __('Three Column', 'virtue-toolkit'), 'value' => '3', ),
					array( 'name' => __('Two Column', 'virtue-toolkit'), 'value' => '2', ),
					array( 'name' => __('Five Column', 'virtue-toolkit'), 'value' => '5', ),
					array( 'name' => __('Six Column', 'virtue-toolkit'), 'value' => '6', ),
				),
			),
			array(
				'name' => __("Max Image/Slider Height", 'virtue-toolkit' ),
				'desc' => __("Default is: 450 <b>(Note: just input number, example: 350)</b>", 'virtue-toolkit' ),
				'id'   => $prefix . 'posthead_height',
				'type' => 'text_small',
			),
			array(
				'name' => __("Max Image/Slider Width", 'virtue-toolkit' ),
				'desc' => __("Default is: 670 or 1140 on <b>above</b> or <b>three row</b> layouts (Note: just input number, example: 650)</b>", 'virtue-toolkit' ),
				'id'   => $prefix . 'posthead_width',
				'type' => 'text_small',
			),
			array(
				'name' => __('Auto Play Slider?', 'virtue-toolkit'),
				'desc' => '',
				'id'   => $prefix . 'portfolio_autoplay',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Yes', 'virtue-toolkit'), 'value' => 'Yes', ),
					array( 'name' => __('No', 'virtue-toolkit'), 'value' => 'no', ),
				),
			),
			array(
				'name' => __('Value 01 Title', 'virtue-toolkit'),
				'desc' => __('ex. Project Type:', 'virtue-toolkit'),
				'id'   => $prefix . 'project_val01_title',
				'type' => 'text_medium',
			),
			array(
				'name' => __('Value 01 Description', 'virtue-toolkit'),
				'desc' => __('ex. Character Illustration', 'virtue-toolkit'),
				'id'   => $prefix . 'project_val01_description',
				'type' => 'text_medium',
			),
			array(
				'name' => __('Value 02 Title', 'virtue-toolkit'),
				'desc' => __('ex. Skills Needed:', 'virtue-toolkit'),
				'id'   => $prefix . 'project_val02_title',
				'type' => 'text_medium',
			),
			array(
				'name' => __('Value 02 Description', 'virtue-toolkit'),
				'desc' => __('ex. Photoshop, Illustrator', 'virtue-toolkit'),
				'id'   => $prefix . 'project_val02_description',
				'type' => 'text_medium',
			),
			array(
				'name' => __('Value 03 Title', 'virtue-toolkit'),
				'desc' => __('ex. Customer:', 'virtue-toolkit'),
				'id'   => $prefix . 'project_val03_title',
				'type' => 'text_medium',
			),
			array(
				'name' => __('Value 03 Description', 'virtue-toolkit'),
				'desc' => __('ex. Example Inc', 'virtue-toolkit'),
				'id'   => $prefix . 'project_val03_description',
				'type' => 'text_medium',
			),
			array(
				'name' => __('Value 04 Title', 'virtue-toolkit'),
				'desc' => __('ex. Project Year:', 'virtue-toolkit'),
				'id'   => $prefix . 'project_val04_title',
				'type' => 'text_medium',
			),
			array(
				'name' => __('Value 04 Description', 'virtue-toolkit'),
				'desc' => __('ex. 2013', 'virtue-toolkit'),
				'id'   => $prefix . 'project_val04_description',
				'type' => 'text_medium',
			),
			array(
				'name' => __('External Website', 'virtue-toolkit'),
				'desc' => __('ex. Website:', 'virtue-toolkit'),
				'id'   => $prefix . 'project_val05_title',
				'type' => 'text_medium',
			),
			array(
				'name' => __('Website Address', 'virtue-toolkit'),
				'desc' => __('ex. http://www.example.com', 'virtue-toolkit'),
				'id'   => $prefix . 'project_val05_description',
				'type' => 'text_medium',
			),
			array(
						'name' => __('If Video Project', 'virtue-toolkit'),
						'desc' => __('Place Embed Code Here, works with youtube, vimeo...', 'virtue-toolkit'),
						'id'   => $prefix . 'post_video',
						'type' => 'textarea_code',
					),
			array(
				'name' => __('Similar Portfolio Item Carousel', 'virtue-toolkit'),
				'desc' => __('Display a carousel with similar portfolio items below project?', 'virtue-toolkit'),
				'id'   => $prefix . 'portfolio_carousel_recent',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('No', 'virtue-toolkit'), 'value' => 'no', ),
					array( 'name' => __('Yes - Display Recent Projects', 'virtue-toolkit'), 'value' => 'recent', ),
				),
			),
			array(
				'name' => __('Carousel Title', 'virtue-toolkit'),
				'desc' => __('ex. Similar Projects', 'virtue-toolkit'),
				'id'   => $prefix . 'portfolio_carousel_title',
				'type' => 'text_medium',
			),
				
		),
	);
$meta_boxes[] = array(
				'id'         => 'portfolio_metabox',
				'title'      => __('Portfolio Page Options', 'virtue-toolkit'),
				'pages'      => array( 'page' ), // Post type
				'show_on' => array('key' => 'page-template', 'value' => array( 'page-portfolio.php' )),
				'context'    => 'normal',
				'priority'   => 'high',
				'show_names' => true, // Show field names on the left
				'fields' => array(
			
			array(
				'name'    => __('Columns', 'virtue-toolkit'),
				'desc'    => '',
				'id'      => $prefix . 'portfolio_columns',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Four Column', 'virtue-toolkit'), 'value' => '4', ),
					array( 'name' => __('Three Column', 'virtue-toolkit'), 'value' => '3', ),
					array( 'name' => __('Two Column', 'virtue-toolkit'), 'value' => '2', ),
				),
			),
			array(
                'name' => __('Portfolio Work Types', 'virtue-toolkit'),
                'desc' => '',
                'id' => $prefix .'portfolio_type',
                'type' => 'imag_select_taxonomy',
                'taxonomy' => 'portfolio-type',
            ),
            array(
				'name'    => __('Order Items By', 'virtue-toolkit'),
				'desc'    => '',
				'id'      => $prefix . 'portfolio_order',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Menu Order', 'virtue-toolkit'), 'value' => 'menu_order', ),
					array( 'name' => __('Title', 'virtue-toolkit'), 'value' => 'title', ),
					array( 'name' => __('Date', 'virtue-toolkit'), 'value' => 'date', ),
					array( 'name' => __('Random', 'virtue-toolkit'), 'value' => 'rand', ),
				),
			),
			array(
				'name'    => __('Items per Page', 'virtue-toolkit'),
				'desc'    => __('How many portfolio items per page', 'virtue-toolkit'),
				'id'      => $prefix . 'portfolio_items',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('All', 'virtue-toolkit'), 'value' => 'all', ),
					array( 'name' => '3', 'value' => '3', ),
					array( 'name' => '4', 'value' => '4', ),
					array( 'name' => '5', 'value' => '5', ),
					array( 'name' => '6', 'value' => '6', ),
					array( 'name' => '7', 'value' => '7', ),
					array( 'name' => '8', 'value' => '8', ),
					array( 'name' => '9', 'value' => '9', ),
					array( 'name' => '10', 'value' => '10', ),
					array( 'name' => '11', 'value' => '11', ),
					array( 'name' => '12', 'value' => '12', ),
					array( 'name' => '13', 'value' => '13', ),
					array( 'name' => '14', 'value' => '14', ),
					array( 'name' => '15', 'value' => '15', ),
					array( 'name' => '16', 'value' => '16', ),
				),
			),
			array(
				'name' => __('Set image height', 'virtue-toolkit'),
				'desc' => __('Default is 1:1 ratio <b>(Note: just input number, example: 350)</b>', 'virtue-toolkit'),
				'id'   => $prefix . 'portfolio_img_crop',
				'type' => 'text_small',
			),
			array(
				'name' => __('Display Item Work Types', 'virtue-toolkit'),
				'desc' => '',
				'id'   => $prefix . 'portfolio_item_types',
				'type' => 'checkbox',
			),
			array(
				'name' => __('Display Item Excerpt', 'virtue-toolkit'),
				'desc' => '',
				'id'   => $prefix . 'portfolio_item_excerpt',
				'type' => 'checkbox',
			),
			array(
				'name'    => __('Add Lightbox link in the top right of each item', 'virtue-toolkit'),
				'desc'    => '',
				'id'      => $prefix . 'portfolio_lightbox',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('No', 'virtue-toolkit'), 'value' => 'no', ),
					array( 'name' => __('Yes', 'virtue-toolkit'), 'value' => 'yes', ),
				),
			),
				
			));
$meta_boxes[] = array(
				'id'         => 'pagefeature_metabox',
				'title'      => __('Feature Page Options', 'virtue-toolkit'),
				'pages'      => array( 'page' ), // Post type
				'show_on' => array('key' => 'page-template', 'value' => array( 'page-feature.php', 'page-feature-sidebar.php')),
				'context'    => 'normal',
				'priority'   => 'high',
				'show_names' => true, // Show field names on the left
				'fields' => array(
			
			array(
				'name'    => __('Feature Options', 'virtue-toolkit'),
				'desc'    => __('If image slider make sure images uploaded are at least 1140px wide.', 'virtue-toolkit'),
				'id'      => $prefix . 'page_head',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Image Slider', 'virtue-toolkit'), 'value' => 'flex', ),
					array( 'name' => __('Video', 'virtue-toolkit'), 'value' => 'video', ),
					array( 'name' => __('Image', 'virtue-toolkit'), 'value' => 'image', ),
				),
			),
			array(
				'name' => __("Slider Gallery", 'virtue-toolkit' ),
				'desc' => __("Add images for gallery here", 'virtue-toolkit' ),
				'id'   => $prefix . 'image_gallery',
				'type' => 'kad_gallery',
			),
			array(
				'name' => __('Max Image/Slider Height', 'virtue-toolkit'),
				'desc' => __('Default is: 400 <b>(Note: just input number, example: 350)</b>', 'virtue-toolkit'),
				'id'   => $prefix . 'posthead_height',
				'type' => 'text_small',
			),
			array(
				'name' => __("Max Image/Slider Width", 'virtue-toolkit' ),
				'desc' => __("Default is: 1140 <b>(Note: just input number, example: 650, does not apply to Carousel slider)</b>", 'virtue-toolkit' ),
				'id'   => $prefix . 'posthead_width',
				'type' => 'text_small',
			),
			array(
				'name'    => __('Use Lightbox for Feature Image', 'virtue-toolkit'),
				'desc'    => __("If feature option is set to image, choose to use lightbox link with image.", 'virtue-toolkit' ),
				'id'      => $prefix . 'feature_img_lightbox',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Yes', 'virtue-toolkit'), 'value' => 'yes', ),
					array( 'name' => __('No', 'virtue-toolkit'), 'value' => 'no', ),
				),
			),
			array(
				'name' => __('If Video Post', 'virtue-toolkit'),
				'desc' => __('Place Embed Code Here, works with youtube, vimeo...', 'virtue-toolkit'),
				'id'   => $prefix . 'post_video',
				'type' => 'textarea_code',
			),
				
			));
$meta_boxes[] = array(
				'id'         => 'contact_metabox',
				'title'      => __('Contact Page Options', 'virtue-toolkit'),
				'pages'      => array( 'page' ), // Post type
				'show_on' => array('key' => 'page-template', 'value' => array( 'page-contact.php')),
				'context'    => 'normal',
				'priority'   => 'high',
				'show_names' => true, // Show field names on the left
				'fields' => array(
			
			array(
                'name' => __('Use Contact Form', 'virtue-toolkit'),
                'desc' => '',
                'id' => $prefix .'contact_form',
                'type'    => 'select',
				'options' => array(
					array( 'name' => __('Yes', 'virtue-toolkit'), 'value' => 'yes', ),
					array( 'name' => __('No', 'virtue-toolkit'), 'value' => 'no', ),
				),
			),
			array(
				'name' => __('Contact Form Title', 'virtue-toolkit'),
				'desc' => __('ex. Send us an Email', 'virtue-toolkit'),
				'id'   => $prefix . 'contact_form_title',
				'type' => 'text',
			),
			array(
                'name' => __('Use Simple Math Question', 'virtue-toolkit'),
                'desc' => 'Adds a simple math question to form.',
                'id' => $prefix .'contact_form_math',
                'type'    => 'select',
				'options' => array(
					array( 'name' => __('Yes', 'virtue-toolkit'), 'value' => 'yes', ),
					array( 'name' => __('No', 'virtue-toolkit'), 'value' => 'no', ),
				),
			),
			array(
                'name' => __('Use Map', 'virtue-toolkit'),
                'desc' => '',
                'id' => $prefix .'contact_map',
                'type'    => 'select',
				'options' => array(
					array( 'name' => __('No', 'virtue-toolkit'), 'value' => 'no', ),
					array( 'name' => __('Yes', 'virtue-toolkit'), 'value' => 'yes', ),
				),
			),
			array(
				'name' => __('Address', 'virtue-toolkit'),
				'desc' => __('Enter your Location', 'virtue-toolkit'),
				'id'   => $prefix . 'contact_address',
				'type' => 'text',
			),
			array(
				'name'    => __('Map Type', 'virtue-toolkit'),
				'desc'    => '',
				'id'      => $prefix . 'contact_maptype',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('ROADMAP', 'virtue-toolkit'), 'value' => 'ROADMAP', ),
					array( 'name' => __('HYBRID', 'virtue-toolkit'), 'value' => 'HYBRID', ),
					array( 'name' => __('TERRAIN', 'virtue-toolkit'), 'value' => 'TERRAIN', ),
					array( 'name' => __('SATELLITE', 'virtue-toolkit'), 'value' => 'SATELLITE', ),
				),
			),
			array(
				'name' => __('Map Zoom Level', 'virtue-toolkit'),
				'desc' => __('A good place to start is 15', 'virtue-toolkit'),
				'id'   => $prefix . 'contact_zoom',
				'std'  => '15',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('1 (World View)', 'virtue-toolkit'), 'value' => '1', ),
					array( 'name' => '2', 'value' => '2', ),
					array( 'name' => '3', 'value' => '3', ),
					array( 'name' => '4', 'value' => '4', ),
					array( 'name' => '5', 'value' => '5', ),
					array( 'name' => '6', 'value' => '6', ),
					array( 'name' => '7', 'value' => '7', ),
					array( 'name' => '8', 'value' => '8', ),
					array( 'name' => '9', 'value' => '9', ),
					array( 'name' => '10', 'value' => '10', ),
					array( 'name' => '11', 'value' => '11', ),
					array( 'name' => '12', 'value' => '12', ),
					array( 'name' => '13', 'value' => '13', ),
					array( 'name' => '14', 'value' => '14', ),
					array( 'name' => '15', 'value' => '15', ),
					array( 'name' => '16', 'value' => '16', ),
					array( 'name' => '17', 'value' => '17', ),
					array( 'name' => '18', 'value' => '18', ),
					array( 'name' => '19', 'value' => '19', ),
					array( 'name' => '20', 'value' => '20', ),
					array( 'name' => __('21 (Street View)', 'virtue-toolkit'), 'value' => '21', ),
					),
			),
			array(
				'name' => __('Map Height', 'virtue-toolkit'),
				'desc' => __('Default is 300', 'virtue-toolkit'),
				'id'   => $prefix . 'contact_mapheight',
				'type' => 'text_small',
			),
				
			));
$meta_boxes[] = array(
				'id'         => 'virtue_post_gallery',
				'title'      => __("Slider Images", 'virtue-toolkit'),
				'pages'      => array( 'post', 'portfolio'), // Post type
				'context'    => 'normal',
				'priority'   => 'high',
				'show_names' => true, // Show field names on the left
				'fields' => array(
			array(
				'name' => __("Slider Gallery", 'virtue-toolkit' ),
				'desc' => __("Add images for gallery here", 'virtue-toolkit' ),
				'id'   => $prefix . 'image_gallery',
				'type' => 'kad_gallery',
			),
	));
	return $meta_boxes;
}

add_action( 'init', 'initialize_kadence_toolkit_meta_boxes', 10 );
function initialize_kadence_toolkit_meta_boxes() {

	if ( ! class_exists( 'cmb_Meta_Box' ) ) {
		require_once 'cmb/init.php';
	}

}