<?php
/**
 * @category Virtue Theme
 * @package  Metaboxes
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     https://github.com/jaredatch/Custom-Metaboxes-and-Fields-for-WordPress
 */

add_filter( 'cmb_meta_boxes', 'virtue_metaboxes' );
/**
 * Define the metabox and field configurations.
 *
 * @param  array $meta_boxes
 * @return array
 */
//add_filter('cmb_icomoon', 'cmb_icomoon');
add_filter( 'cmb_render_imag_select_taxonomy', 'imag_render_imag_select_taxonomy', 10, 2 );
function imag_render_imag_select_taxonomy( $field, $meta ) {

    wp_dropdown_categories(array(
            'show_option_none' => __( "All", 'virtue' ),
            'hierarchical' => 1,
            'taxonomy' => $field['taxonomy'],
            'orderby' => 'name', 
            'hide_empty' => 0, 
            'name' => $field['id'],
            'selected' => $meta  

        ));
    if ( !empty( $field['desc'] ) ) echo '<p class="cmb_metabox_description">' . $field['desc'] . '</p>';
}
add_filter( 'cmb_render_imag_select_category', 'imag_render_imag_select_category', 10, 2 );
function imag_render_imag_select_category( $field, $meta ) {

    wp_dropdown_categories(array(
            'show_option_none' => __( "All Blog Posts", 'virtue' ),
            'hierarchical' => 1,
            'taxonomy' => 'category',
            'orderby' => 'name', 
            'hide_empty' => 0, 
            'name' => $field['id'],
            'selected' => $meta  

        ));
    if ( !empty( $field['desc'] ) ) echo '<p class="cmb_metabox_description">' . $field['desc'] . '</p>';

}
add_filter( 'cmb_render_imag_select_sidebars', 'imag_render_imag_select_sidebars', 10, 2 );
function imag_render_imag_select_sidebars( $field, $meta ) {
	global $vir_sidebars;	
	
	 echo '<select name="', $field['id'], '" id="', $field['id'], '">';
  foreach ($vir_sidebars as $side) {
    echo '<option value="', $side['id'], '"', $meta == $side['id'] ? ' selected="selected"' : '', '>', $side['name'], '</option>';
  }
  echo '</select>';
	
    if ( !empty( $field['desc'] ) ) echo '<p class="cmb_metabox_description">' . $field['desc'] . '</p>';

}
function virtue_metaboxes( array $meta_boxes ) {

	// Start with an underscore to hide fields from custom fields list
	$prefix = '_kad_';

	$meta_boxes[] = array(
				'id'         => 'subtitle_metabox',
				'title'      => __( "Page Title and Subtitle", 'virtue' ),
				'pages'      => array( 'page', ), // Post type
				'context'    => 'normal',
				'priority'   => 'high',
				'show_names' => true, // Show field names on the left
				'fields' => array(
					array(
						'name' => __( "Subtitle", 'virtue' ),
						'desc' => __( "Subtitle will go below page title", 'virtue' ),
						'id'   => $prefix . 'subtitle',
						'type' => 'textarea_small',
					),
				)
			);

	$meta_boxes[] = array(
				'id'         => 'post_metabox',
				'title'      => __("Post Options", 'virtue'),
				'pages'      => array( 'post',), // Post type
				'context'    => 'normal',
				'priority'   => 'high',
				'show_names' => true, // Show field names on the left
				'fields' => array(
			
			array(
				'name'    => __("Head Content", 'virtue' ),
				'desc'    => '',
				'id'      => $prefix . 'blog_head',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Default', 'virtue' ), 'value' => 'default', ),
					array( 'name' => __("None", 'virtue' ), 'value' => 'none', ),
					array( 'name' => __("Image Slider", 'virtue' ), 'value' => 'flex', ),
					array( 'name' => __("Video", 'virtue' ), 'value' => 'video', ),
					array( 'name' => __("Image", 'virtue' ), 'value' => 'image', ),
				),
			),
			array(
				'name' => __("Max Image/Slider Height", 'virtue' ),
				'desc' => __("Default is: 400 <b>(Note: just input number, example: 350)</b>", 'virtue' ),
				'id'   => $prefix . 'posthead_height',
				'type' => 'text_small',
			),
			array(
				'name' => __("Max Image/Slider Width", 'virtue' ),
				'desc' => __("Default is: 770 or 1140 on fullwidth posts <b>(Note: just input number, example: 650, does not apply to carousel slider)</b>", 'virtue' ),
				'id'   => $prefix . 'posthead_width',
				'type' => 'text_small',
			),
			array(
				'name'    => __("Post Summary", 'virtue' ),
				'desc'    => '',
				'id'      => $prefix . 'post_summery',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Default', 'virtue' ), 'value' => 'default', ),
					array( 'name' => __('Text', 'virtue' ), 'value' => 'text', ),
					array( 'name' => __('Portrait Image', 'virtue'), 'value' => 'img_portrait', ),
					array( 'name' => __('Landscape Image', 'virtue'), 'value' => 'img_landscape', ),
					array( 'name' => __('Portrait Image Slider', 'virtue'), 'value' => 'slider_portrait', ),
					array( 'name' => __('Landscape Image Slider', 'virtue'), 'value' => 'slider_landscape', ),
					array( 'name' => __('Video', 'virtue'), 'value' => 'video', ),
				),
			),
			array(
				'name' => __('Display Sidebar?', 'virtue'),
				'desc' => __('Choose if layout is fullwidth or sidebar', 'virtue'),
				'id'   => $prefix . 'post_sidebar',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Yes', 'virtue'), 'value' => 'yes', ),
					array( 'name' => __('No', 'virtue'), 'value' => 'no', ),
				),
			),
			array(
				'name'    => __('Choose Sidebar', 'virtue'),
				'desc'    => '',
				'id'      => $prefix . 'sidebar_choice',
				'type'    => 'imag_select_sidebars',
			),
			array(
				'name' => __('Author Info', 'virtue'),
				'desc' => __('Display an author info box?', 'virtue'),
				'id'   => $prefix . 'blog_author',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Default', 'virtue'), 'value' => 'default', ),
					array( 'name' => __('No', 'virtue'), 'value' => 'no', ),
					array( 'name' => __('Yes', 'virtue'), 'value' => 'yes', ),
				),
			),	
			array(
				'name' => __('Posts Carousel', 'virtue'),
				'desc' => __('Display a carousel with similar or recent posts?', 'virtue'),
				'id'   => $prefix . 'blog_carousel_similar',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Default', 'virtue'), 'value' => 'default', ),
					array( 'name' => __('No', 'virtue'), 'value' => 'no', ),
					array( 'name' => __('Yes - Display Recent Posts', 'virtue'), 'value' => 'recent', ),
					array( 'name' => __('Yes - Display Similar Posts', 'virtue'), 'value' => 'similar', )
				),
				
			),
			array(
				'name' => __('Carousel Title', 'virtue'),
				'desc' => __('ex. Similar Posts', 'virtue'),
				'id'   => $prefix . 'blog_carousel_title',
				'type' => 'text_medium',
			),
		),
	);
			
			
	$meta_boxes[] = array(
				'id'         => 'bloglist_metabox',
				'title'      => __('Blog List Options', 'virtue'),
				'pages'      => array( 'page' ), // Post type
				'show_on' => array('key' => 'page-template', 'value' => array( 'page-blog.php')),
				'context'    => 'normal',
				'priority'   => 'high',
				'show_names' => true, // Show field names on the left
				'fields' => array(
			
			array(
                'name' => __('Blog Category', 'virtue'),
                'desc' => __('Select all blog posts or a specific category to show', 'virtue'),
                'id' => $prefix .'blog_cat',
                'type' => 'imag_select_category',
                'taxonomy' => 'category',
            ),
			array(
				'name'    => __('How Many Posts Per Page', 'virtue'),
				'desc'    => '',
				'id'      => $prefix . 'blog_items',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('All', 'virtue'), 'value' => 'all', ),
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
				),
			),
			array(
				'name'    => __('Display Post Content as:', 'virtue'),
				'desc'    => '',
				'id'      => $prefix . 'blog_summery',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Summary', 'virtue'), 'value' => 'summery', ),
					array( 'name' => __('Full', 'virtue'), 'value' => 'full', ),
				),
			),
			array(
				'name' => __('Display Sidebar?', 'virtue'),
				'desc' => __('Choose if layout is fullwidth or sidebar', 'virtue'),
				'id'   => $prefix . 'page_sidebar',
				'type'    => 'select',
				'options' => array(
					array( 'name' => __('Yes', 'virtue'), 'value' => 'yes', ),
					array( 'name' => __('No', 'virtue'), 'value' => 'no', ),
				),
			),
			array(
				'name'    => __('Choose Sidebar', 'virtue'),
				'desc'    => '',
				'id'      => $prefix . 'sidebar_choice',
				'type'    => 'imag_select_sidebars',
				),
				
			));
			
			$meta_boxes[] = array(
				'id'         => 'page_sidebar',
				'title'      => __('Sidebar Options', 'virtue'),
				'pages'      => array( 'page' ), // Post type
				'show_on' => array( 'key' => 'page-template', 'value' => array('page-sidebar.php','page-feature-sidebar.php')),
				'context'    => 'normal',
				'priority'   => 'high',
				'show_names' => true, // Show field names on the left
				'fields' => array(
			
			array(
				'name'    => __('Choose Sidebar', 'virtue'),
				'desc'    => '',
				'id'      => $prefix . 'sidebar_choice',
				'type'    => 'imag_select_sidebars',
				),
				
			));
	
	// Add other metaboxes as needed

	return $meta_boxes;
}

add_action( 'init', 'initialize_showcase_meta_boxes', 9999 );
/**
 * Initialize the metabox class.
 */
function initialize_showcase_meta_boxes() {

	if ( ! class_exists( 'cmb_Meta_Box' ) )
		require_once 'cmb/init.php';

}