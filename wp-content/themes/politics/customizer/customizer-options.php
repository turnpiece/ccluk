<?php

/**
 * Remove Default Options
 */
function politics_customize_register( $wp_customize ) {
	// Remove the default colors section
	$wp_customize->remove_section('colors');
	// Remove the default background image section
	$wp_customize->remove_section('background_image');
}
add_action( 'customize_register', 'politics_customize_register' );

/**
 * Defines customizer options
 */
function politics_customizer_library_options() {

	// Theme defaults
	$primary_color = '#5bc08c';
	$secondary_color = '#666';
	$white = '#fff';
	$primary_color_drkblue = '#465a63';

	$site_link = "https://rescuethemes.com";
	$upgrade_link = "https://rescuethemes.com/wordpress-themes/politics-plus/";
	$upgrade_text = __( 'Upgrade to Politics Plus', 'politics');

	// Image path default
	$imagepath =  get_template_directory_uri() . '/img/';

	// Get Categories
	$options_cats = array();
	$options_cats_obj = get_categories( 'type=post');
	$options_cats[''] = __( 'Select a Category', 'politics' );
	foreach ($options_cats_obj as $cat) {
		$options_cats[$cat->term_id] = $cat->cat_name;
	}

	// Stores all the controls that will be added
	$options = array();

	// Stores all the sections to be added
	$sections = array();

	// Stores all the panels to be added
	$panels = array();

	// Adds the sections to the $options array
	$options['sections'] = $sections;


/*--------------------------------------------------------------
>>> TABLE OF CONTENTS:
----------------------------------------------------------------
1.0 Header
2.0 Home
	2.1 Home posts
	2.2 Secondary Content
	2.3 Paralax
	2.4 Slider
3.0 Footer
4.0 Blog
5.0 Colors Sitewide
6.0 Plus Upgrade Options
--------------------------------------------------------------*/

/*--------------------------------------------------------------
1.0 Header
--------------------------------------------------------------*/

	// Header Panel
	$panel = 'header-panel';

	$panels[] = array(
		'id' => $panel,
		'title' => __( 'Header', 'politics' ),
		'priority' => '6'
	);

	$section = 'general';

	$sections[] = array(
		'id' => $section,
		'title' => __( 'General', 'politics' ),
		'priority' => '5',
		'description' => __( 'General header settings', 'politics' ),
		'panel' => $panel
	);

	$options['politics-sticky-header'] = array(
		'id' => 'politics-sticky-header',
		'label'   => __( 'Sticky Header', 'politics' ),
		'section' => $section,
		'type'    => 'checkbox',
		'default' => 0,
	);

	$section = 'logo';

	$sections[] = array(
		'id' => $section,
		'title' => __( 'Logo', 'politics' ),
		'priority' => '10',
		'description' => __( 'Upload the logo image', 'politics' ),
		'panel' => $panel
	);

	$options['politics-logo'] = array(
		'id' => 'politics-logo',
		'label'   => __( 'Logo', 'politics' ),
		'section' => $section,
		'type'    => 'image',
		'default' => $imagepath . 'logo.png'
	);

	$options['politics-logo-height'] = array(
		'id' => 'politics-logo-height',
		'label'   => __( 'Logo Height (px or em)', 'politics' ),
		'section' => $section,
		'type'    => 'text'
	);

	$options['politics-logo-width'] = array(
		'id' => 'politics-logo-width',
		'label'   => __( 'Logo Width (px or em)', 'politics' ),
		'section' => $section,
		'type'    => 'text',
		'default' => '225px'
	);

	$section = 'contact';

	$sections[] = array(
		'id' => $section,
		'title' => __( 'Contact Details', 'politics' ),
		'priority' => '10',
		'description' => __( 'Contact details displayed in the top header', 'politics' ),
		'panel' => $panel
	);

	$options['politics-header-phone'] = array(
		'id' => 'politics-header-phone',
		'label'   => __( 'Phone Number', 'politics' ),
		'section' => $section,
		'type'    => 'textarea',
		'default' => __( '800 555 1234', 'politics'),
		'transport'	=> 'postMessage'
	);

	$options['politics-header-address'] = array(
		'id' => 'politics-header-address',
		'label'   => __( 'Location', 'politics' ),
		'section' => $section,
		'type'    => 'textarea',
		'default' => __( '5046 S Greenwood, Chicago USA', 'politics'),
		'transport'	=> 'postMessage'
	);

/*--------------------------------------------------------------
2.0 Home
--------------------------------------------------------------*/
	// Home Panel
	$panel = 'home-panel';

	$panels[] = array(
		'id' => $panel,
		'title' => __( 'Home', 'politics' ),
		'priority' => '10'
	);


/*--------------------------------------------------------------
2.1 Home Posts
--------------------------------------------------------------*/
	$section = 'home-featured-posts';

	$sections[] = array(
		'id' => $section,
		'title' => __( 'Featured Posts', 'politics' ),
		'priority' => '15',
		'description' => __( 'Settings for the featured posts section', 'politics' ),
		'panel' => $panel
	);

	$options['home_posts_section_title'] = array(
		'id' 						=> 'home_posts_section_title',
		'label'   			=> __( 'Section Title', 'politics' ),
		'section' 			=> $section,
		'type'    			=> 'textarea',
		'default' 			=> __( 'Latest News', 'politics' ),
		'transport'			=> 'postMessage'
	);

	$options['home_posts_section_subtitle'] = array(
		'id' => 'home_posts_section_subtitle',
		'label'   		=> __( 'Section Subtitle', 'politics' ),
		'section' 		=> $section,
		'type'    		=> 'textarea',
		'default' 		=> __( 'Change this text in the theme customizer', 'politics' ),
		'transport'		=> 'postMessage'
	);

	$options['home_posts_cat'] = array(
		'id' 				=> 'home_posts_cat',
		'label'   			=> __( 'Home Posts Category', 'politics' ),
		'section' 			=> $section,
		'type'    			=> 'select',
		'choices' 			=> $options_cats
	);

/*--------------------------------------------------------------
2.2 Secondary Content
--------------------------------------------------------------*/
	$section = 'home-secondary-content';

	$sections[] = array(
		'id' => $section,
		'title' => __( 'Secondary Content', 'politics' ),
		'priority' => '25',
		'description' => __( 'Settings for the secondary content section on the home page', 'politics' ),
		'panel' => $panel
	);

	$options['home_secondary_content_page'] = array(
		'id' => 'home_secondary_content_page',
		'label' => __( 'Secondary Content Page', 'politics' ),
		'section' => $section,
		'type' => 'dropdown-pages',
		'default' => ''
	);

	$options['home_secondary_content_title'] = array(
		'id' 						=> 'home_secondary_content_title',
		'label'   			=> __( 'Section Title', 'politics' ),
		'section' 			=> $section,
		'type'    			=> 'textarea',
		'default' 			=> __( 'Community', 'politics' ),
		'transport'			=> 'postMessage'
	);

	$options['home_secondary_content_subtitle'] = array(
		'id' => 'home_secondary_content_subtitle',
		'label'   		=> __( 'Section Subtitle', 'politics' ),
		'section' 		=> $section,
		'type'    		=> 'textarea',
		'default' 		=> __( 'Change this text in the theme customizer', 'politics' ),
		'transport'		=> 'postMessage'
	);


/*--------------------------------------------------------------
2.3 Parallax Content
--------------------------------------------------------------*/
	$section = 'home-paralax-content';

	$sections[] = array(
		'id' => $section,
		'title' => __( 'Paralax Content', 'politics' ),
		'priority' => '20',
		'description' => __( 'Settings for the paralax content section on the home page', 'politics' ),
		'panel' => $panel
	);

	$options['home_paralax_content_page'] = array(
		'id' => 'home_paralax_content_page',
		'label' => __( 'Paralax Content Page', 'politics' ),
		'section' => $section,
		'type' => 'dropdown-pages',
		'default' => ''
	);

	$options['home_paralax_bg'] = array(
		'id' 				=> 'home_paralax_bg',
		'label'   			=> __( 'Background Image', 'politics' ),
		'section' 			=> $section,
		'type'    			=> 'image',
		'default' 			=> $imagepath . 'home-paralax.jpg'
	);

	$options['home_paralax_bg_color'] = array(
		'id' 				=> 'home_paralax_bg_color',
		'label'   			=> __( 'Background color if no image is being used', 'politics' ),
		'section' 			=> $section,
		'type'    			=> 'color',
		'default' 			=> $primary_color_drkblue
	);

/*--------------------------------------------------------------
2.4 Slider
--------------------------------------------------------------*/
/**
 * Section named 'header_image' so that
 * add_theme_support( 'custom-header' ) will display in
 * this section.
 *
 * @see politics_backstretch_setup()
 */
	$section = 'header_image';

	$sections[] = array(
		'id' 			=> $section,
		'title' 		=> __( 'Hero Slider', 'politics' ),
		'priority' 		=> '5',
		'description' 	=> __( 'Settings for the home slider.', 'politics' ),
		'panel' => $panel
	);

	$options['home-slider-content-help'] = array(
		'id' => 'home-slider-content-help',
        'section' => $section,
        'label'    => 'Content',
        'type'     => 'content',
		'description' 	=> __( 'The content area is widgetized. To add content (text, buttons, etc.), add a widget at Widgets > Home Hero', 'politics' )
	);

	$options['home_duration'] = array(
	    'id' => 'home_duration',
	    'label'   => __( 'Images Duration', 'politics' ),
	    'section' => $section,
	    'type'    => 'range',
	    'description' => __( 'Duration time for each background image.', 'politics' ),
	    'default' => 4000,
	    'input_attrs' => array(
	        'min'   => 100,
	        'max'   => 10000,
	        'step'  => 100,
	    )
	);

	$options['home_fade'] = array(
	    'id' => 'home_fade',
	    'label'   => __( 'Image Fade In', 'politics' ),
	    'section' => $section,
	    'type'    => 'range',
	    'description' => __( 'Fade in duration for each background image.', 'politics' ),
	    'default' => 1000,
	    'input_attrs' => array(
	        'min'   => 100,
	        'max'   => 3000,
	        'step'  => 100,
	    )
	);

	$options['home_overlay_color'] = array(
		'id' => 'home_overlay_color',
		'label'   => __( 'Overlay Color', 'politics' ),
		'description' => __( 'Color overlayed on the home page images.', 'politics' ),
		'section' => $section,
		'type'    => 'color',
		'default' => $primary_color_drkblue,
	);

	$options['home_opacity'] = array(
		'id' => 'home_opacity',
		'label'   => __( 'Opacity level', 'politics' ),
		'section' => $section,
		'type'    => 'range',
		'description' => __( 'Adjust the opacity for the overlay color.', 'politics' ),
		'default' => '0.6',
	    'input_attrs' => array(
	        'min'   => 0,
	        'max'   => 1,
	        'step'  => 0.1
	    ),
	);

/*--------------------------------------------------------------
3.0 Footer
--------------------------------------------------------------*/
	$section = 'footer-section';

	$sections[] = array(
		'id' => $section,
		'title' => __( 'Copyright', 'politics' ),
		'priority' => '30',
		'description' => __( 'Settings for the copyright area', 'politics' ),
	);

	$options['footer_copyright'] = array(
		'id' => 'footer_copyright',
		'label'   		=> __( 'Copyright Text', 'politics' ),
		'section' 		=> $section,
		'type'    		=> 'textarea',
		'default' 		=> sprintf( __( 'Copyright 2015 <a href="%1s">Rescue Themes</a>. All Rights Reserved. Paid for by the campaign.', 'politics' ), esc_url( $site_link ) ),
		'transport'		=> 'postMessage'
	);

/*--------------------------------------------------------------
4.0 Blog
--------------------------------------------------------------*/
	// Blog Panel
	$panel = 'blog-panel';

	$panels[] = array(
		'id' => $panel,
		'title' => __( 'Blog', 'politics' ),
		'priority' => '20'
	);

	$section = 'blog-layout-section';

	$sections[] = array(
		'id' => $section,
		'title' => __( 'Layout', 'politics' ),
		'priority' => '30',
		'description' => __( 'Settings for the blog page layout', 'politics' ),
		'panel' => $panel
	);

	$choices = array(
		'masonry' => 'Masonry',
		'traditional' => 'Traditional'
	);

	$options['politics_blog_style'] = array(
		'id' => 'politics_blog_style',
		'label'   => __( 'Blog Page Style', 'politics' ),
		'section' => $section,
		'type'    => 'select',
		'choices' => $choices,
		'default' => 'masonry'
	);

	$section = 'blog-title-section';

	$sections[] = array(
		'id' => $section,
		'title' => __( 'Header', 'politics' ),
		'priority' => '30',
		'description' => __( 'Settings for the blog page header', 'politics' ),
		'panel' => $panel
	);

	$options['politics_blog_title'] = array(
		'id' => 'politics_blog_title',
		'label'   		=> __( 'Page Title', 'politics' ),
		'section' 		=> $section,
		'type'    		=> 'textarea',
		'default' 		=> __( 'Latest Posts', 'politics' ),
		'transport'		=> 'postMessage'
	);

	$options['politics_blog_subtitle'] = array(
		'id' => 'politics_blog_subtitle',
		'label'   		=> __( 'Page Subtitle', 'politics' ),
		'section' 		=> $section,
		'type'    		=> 'textarea',
		'default' 		=> __( 'Change this text in the customizer', 'politics' ),
		'transport'		=> 'postMessage'
	);

/*--------------------------------------------------------------
5.0 Colors Sitewide
--------------------------------------------------------------*/

	$body_font_color		= '#78909c';
	$header_font_color 	= '#465a63';
	$accent_color				= '#ef5350';
	$accent_color_dark  = '#38474d';
	$white							= '#FFFFFF';

	$section = 'basic-colors';

	$sections[] = array(
		'id' => $section,
		'title' => __( 'Basic Colors', 'politics' ),
		'priority' => '5',
		'description'	=> __('These are the default sitewide colors.','politics')
	);

	$options['color-headers'] = array(
		'id'				=> 'color-headers',
		'label'			=> __( 'Text Headers', 'politics' ),
		'section'		=> $section,
		'type'			=> 'color',
		'default'		=> $header_font_color,
		'transport' => 'postMessage'
	);

	$options['color-content'] = array(
		'id'				=> 'color-content',
		'label'			=> __( 'Standard Text', 'politics' ),
		'section'		=> $section,
		'type'			=> 'color',
		'default'		=> $body_font_color,
		'transport' => 'postMessage'
	);

	$options['color-content-link'] = array(
		'id'				=> 'color-content-link',
		'label'			=> __( 'Links', 'politics' ),
		'section'		=> $section,
		'type'			=> 'color',
		'default'		=> $header_font_color,
		'transport' => 'postMessage'
	);

	$options['color-content-link-hover'] = array(
		'id'				=> 'color-content-link-hover',
		'label'			=> __( 'Link Hover', 'politics' ),
		'section'		=> $section,
		'type'			=> 'color',
		'default'		=> $accent_color
	);

	$options['color-content-top-bar'] = array(
		'id'				=> 'color-content-top-bar',
		'label'			=> __( 'Top Bar Background', 'politics' ),
		'section'		=> $section,
		'type'			=> 'color',
		'default'		=> $accent_color_dark,
		'transport' => 'postMessage'
	);

	$options['color-content-body-bg'] = array(
		'id'				=> 'color-content-body-bg',
		'label'			=> __( 'Sitewide Background', 'politics' ),
		'section'		=> $section,
		'type'			=> 'color',
		'default'		=> $white,
		'transport' => 'postMessage'
	);

	$options['advanced-colors'] = array(
    'id' 					=> 'advanced-colors',
    'label' 			=> __( 'Advanced Colors', 'politics' ),
    'section' 		=> $section,
    'type' 				=> 'content',
    'content' 		=> __( '<p>Fine tune your site design with an unlimited number of color options for each section of the site:</p><ul><li>Backgrounds</li><li>Navigation</li><li>Links and Fonts</li><li>Footer</li><li>and more!</li></ul>', 'politics' ),
    'description' => sprintf( '<a class="upgrade-link" target="_blank" href="%1s">%2s &rarr;</a>', esc_url( $upgrade_link ), esc_attr( $upgrade_text ) )
	);

/*--------------------------------------------------------------
6.0 Plus Upgrade Options
--------------------------------------------------------------*/

	// Plus: Home Control
	$section = 'plus-home-control';

	$sections[] = array(
		'id' 						=> $section,
		'title' 				=> __( 'Upgrade Available', 'politics' ),
		'priority'	 		=> '50',
		'description' 	=> ''
	);

	$options['home-controls-plus'] = array(
    'id' 				=> 'home-controls-plus',
    'label' 		=> __( 'Home Sections', 'politics' ),
    'section' 	=> $section,
    'type' 			=> 'content',
    'content' 	=> sprintf( __( '<p>Take control of your home page content sections with the abilitiy to re-order and toggle sections on/off.</p><img src="%s">', 'politics' ),  $imagepath .'home-controls.png' )
	);

	$options['typography-plus'] = array(
    'id' 					=> 'typography-plus',
    'label' 			=> __( 'Advanced Typography Settings', 'politics' ),
    'section' 		=> $section,
    'type' 				=> 'content',
    'content' 		=> __( '<p>You\'ll have access to 500+ Google Fonts along with font size, weight, style, transform, line height, and spacing settings for every part of your site!</p><ul><li>Headers and Content</li><li>Navigation</li><li>Sidebar</li><li>Footer</li><li>Copyright</li><li>and more!</li></ul>', 'politics' )
	);

	$options['colors-plus'] = array(
    'id' 					=> 'colors-plus',
    'label' 			=> __( 'Advanced Color Settings', 'politics' ),
    'section' 		=> $section,
    'type' 				=> 'content',
    'content' 		=> __( '<p>Fine tune your site design with an unlimited number of color options for each section of the site:</p><ul><li>Backgrounds</li><li>Navigation</li><li>Links and Fonts</li><li>Footer</li><li>and more!</li></ul>', 'politics' )
	);

	$options['plus-sidebar-notice'] = array(
    'id' 					=> 'plus-sidebar-notice',
    'label' 			=> __( 'Sidebar Layout', 'politics' ),
    'section' 		=> $section,
    'type' 				=> 'content',
    'content' 		=> sprintf( __( '<p>Adjust the inner sidebar position to display on the right or left side of your content.</p>', 'politics' ) )
	);

	$options['plus-woocommerce-notice'] = array(
    'id' 					=> 'plus-woocommerce-notice',
    'label' 			=> __( 'WooCommerce', 'politics' ),
    'section' 		=> $section,
    'type' 				=> 'content',
    'content' 		=> __( '<p>Politics Plus includes compatibility with the world\'s most popular ecommerce system, WooCommerce. Sell physical products, music files, or even your time when you upgrade!</p>', 'politics' ),
    'description' => sprintf( '<a class="upgrade-link" target="_blank" href="%1s">%2s &rarr;</a>', esc_url( $upgrade_link ), esc_attr( $upgrade_text ) )
	);

	// Adds the sections to the $options array
	$options['sections'] = $sections;

	// Adds the panels to the $options array
	$options['panels'] = $panels;

	$customizer_library = Customizer_Library::Instance();
	$customizer_library->add_options( $options );

	// To delete custom mods use: customizer_library_remove_theme_mods();

}
add_action( 'init', 'politics_customizer_library_options' );
