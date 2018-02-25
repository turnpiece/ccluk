<?php
/**
 * CCLUK Theme Customizer.
 *
 * @package CCLUK
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function ccluk_customize_register( $wp_customize ) {

	// Load custom controls.
	require get_stylesheet_directory() . '/inc/customizer-controls.php';

	/**
	 * Hook to add other customize
	 */
	do_action( 'ccluk_customize_before_register', $wp_customize );

	$pages = get_pages();
	$option_pages = array();
	$option_pages[0] = esc_html__( 'Select page', 'onesocial' );
	foreach( $pages as $p ){
		$option_pages[ $p->ID ] = $p->post_title;
	}
/*
	$users = get_users( array(
		'orderby'      => 'display_name',
		'order'        => 'ASC',
		'number'       => '',
	) );

	$option_users[0] = esc_html__( 'Select member', 'onesocial' );
	foreach( $users as $user ){
		$option_users[ $user->ID ] = $user->display_name;
	}
*/

	$static_front_page = get_option( 'show_on_front' ) === 'page';

	if ($static_front_page) :

	/*------------------------------------------------------------------------*/
    /*  Homepage: About
    /*------------------------------------------------------------------------*/

	    $wp_customize->add_panel( 'ccluk_about' ,
			array(
				'priority'        => 160,
				'title'           => esc_html__( 'Homepage: About', 'onesocial' ),
				'description'     => '',
				'active_callback' => 'ccluk_showon_frontpage'
			)
		);

		$wp_customize->add_section( 'ccluk_about_settings' ,
			array(
				'priority'    => 3,
				'title'       => esc_html__( 'Section Settings', 'onesocial' ),
				'description' => '',
				'panel'       => 'ccluk_about',
			)
		);

		// Show Content
		$wp_customize->add_setting( 'ccluk_about_disable',
			array(
				'sanitize_callback' => 'ccluk_sanitize_checkbox',
				'default'           => '',
			)
		);

		$wp_customize->add_control( 'ccluk_about_disable',
			array(
				'type'        => 'checkbox',
				'label'       => esc_html__('Hide this section?', 'onesocial'),
				'section'     => 'ccluk_about_settings',
				'description' => esc_html__('Check this box to hide this section.', 'onesocial'),
			)
		);

		// Section ID
		$wp_customize->add_setting( 'ccluk_about_id',
			array(
				'sanitize_callback' => 'ccluk_sanitize_text',
				'default'           => esc_html__('about', 'onesocial'),
			)
		);

		$wp_customize->add_control( 'ccluk_about_id',
			array(
				'label' 		=> esc_html__('Section ID:', 'onesocial'),
				'section' 		=> 'ccluk_about_settings',
				'description'   => esc_html__( 'The section id, we will use this for link anchor.', 'onesocial' )
			)
		);

		// Title
		$wp_customize->add_setting( 'ccluk_about_title',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => esc_html__('About Us', 'onesocial'),
			)
		);

		$wp_customize->add_control( 'ccluk_about_title',
			array(
				'label' 		=> esc_html__('Section Title', 'onesocial'),
				'section' 		=> 'ccluk_about_settings',
				'description'   => '',
			)
		);

		// Source page settings
		$wp_customize->add_setting( 'ccluk_about_source_page',
			array(
				'sanitize_callback' => 'ccluk_sanitize_number',
				'default'           => '',
			)
		);
		$wp_customize->add_control( 'ccluk_about_source_page',
			array(
				'label'     	=> esc_html__('Title Link', 'onesocial'),
				'section' 		=> 'ccluk_about_settings',
				'type'          => 'select',
				'priority'      => 10,
				'choices'       => $option_pages,
				'description'   => esc_html__('Select a page the title will link to.', 'onesocial'),
			)
		);

		$wp_customize->add_section( 'ccluk_about_content' ,
			array(
				'priority'    => 6,
				'title'       => esc_html__( 'Section Content', 'onesocial' ),
				'description' => '',
				'panel'       => 'ccluk_about',
			)
		);

		// Boxes
		for ( $box = 1; $box <= 2; $box++ ) :

		$wp_customize->add_setting( 'ccluk_about_box_'.$box,
			array(
				'sanitize_callback' => 'ccluk_sanitize_text',
				'default'           => '',
			)
		);

		$wp_customize->add_control( new CCLUK_Editor_Custom_Control(
			$wp_customize,
			'ccluk_about_box_'.$box,
			array(
				'label' 		=> sprintf( esc_html__('Box %d content', 'onesocial'), $box ),
				'section' 		=> 'ccluk_about_content',
				'description'   => '',
			)
		));

		endfor;


	/*------------------------------------------------------------------------*/
    /*  Home page: Contact
    /*------------------------------------------------------------------------*/
	    $wp_customize->add_panel( 'ccluk_contact' ,
			array(
				'priority'        => 270,
				'title'           => esc_html__( 'Home page: Contact', 'onesocial' ),
				'description'     => '',
				'active_callback' => 'ccluk_showon_frontpage'
			)
		);

		$wp_customize->add_section( 'ccluk_contact_settings' ,
			array(
				'priority'    => 3,
				'title'       => esc_html__( 'Section Settings', 'onesocial' ),
				'description' => '',
				'panel'       => 'ccluk_contact',
			)
		);

		// Show Content
		$wp_customize->add_setting( 'ccluk_contact_disable',
			array(
				'sanitize_callback' => 'ccluk_sanitize_checkbox',
				'default'           => '',
			)
		);

		$wp_customize->add_control( 'ccluk_contact_disable',
			array(
				'type'        => 'checkbox',
				'label'       => esc_html__('Hide this section?', 'onesocial'),
				'section'     => 'ccluk_contact_settings',
				'description' => esc_html__('Check this box to hide this section.', 'onesocial'),
			)
		);

		// Section ID
		$wp_customize->add_setting( 'ccluk_contact_id',
			array(
				'sanitize_callback' => 'ccluk_sanitize_text',
				'default'           => esc_html__('contact', 'onesocial'),
			)
		);

		$wp_customize->add_control( 'ccluk_contact_id',
			array(
				'label'     => esc_html__('Section ID:', 'onesocial'),
				'section' 		=> 'ccluk_contact_settings',
				'description'   => esc_html__( 'The section id, we will use this for link anchor.', 'onesocial' )
			)
		);

		// Title
		$wp_customize->add_setting( 'ccluk_contact_title',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => esc_html__('Get in touch', 'onesocial'),
			)
		);

		$wp_customize->add_control( 'ccluk_contact_title',
			array(
				'label'     => esc_html__('Section Title', 'onesocial'),
				'section' 		=> 'ccluk_contact_settings',
				'description'   => '',
			)
		);

		// Sub Title
		$wp_customize->add_setting( 'ccluk_contact_subtitle',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => esc_html__('Section subtitle', 'onesocial'),
			)
		);

		$wp_customize->add_control( 'ccluk_contact_subtitle',
			array(
				'label'     => esc_html__('Section Subtitle', 'onesocial'),
				'section' 		=> 'ccluk_contact_settings',
				'description'   => '',
			)
		);

        // Description
        $wp_customize->add_setting( 'ccluk_contact_desc',
            array(
                'sanitize_callback' => 'ccluk_sanitize_text',
                'default'           => '',
            )
        );

        $wp_customize->add_control( new CCLUK_Editor_Custom_Control(
            $wp_customize,
            'ccluk_contact_desc',
            array(
                'label' 		=> esc_html__('Section Description', 'onesocial'),
                'section' 		=> 'ccluk_contact_settings',
                'description'   => '',
            )
        ));

		$wp_customize->add_section( 'ccluk_contact_content' ,
			array(
				'priority'    => 6,
				'title'       => esc_html__( 'Section Content', 'onesocial' ),
				'description' => '',
				'panel'       => 'ccluk_contact',
			)
		);

		// Contact form 7 guide.
		$wp_customize->add_setting( 'ccluk_contact_cf7_guide',
			array(
				'sanitize_callback' => 'ccluk_sanitize_text'
			)
		);

		$wp_customize->add_control( new CCLUK_Misc_Control( $wp_customize, 'ccluk_contact_cf7_guide',
			array(
				'section'     => 'ccluk_contact_content',
				'type'        => 'custom_message',
				'description' => wp_kses_post( 'In order to display contact form please install <a target="_blank" href="https://wordpress.org/plugins/contact-form-7/">Contact Form 7</a> plugin and then copy the contact form shortcode and paste it here, the shortcode will be like this <code>[contact-form-7 id="xxxx" title="Example Contact Form"]</code>', 'onesocial' )
			)
		));

		// Contact Form 7 Shortcode
		$wp_customize->add_setting( 'ccluk_contact_cf7',
			array(
				'sanitize_callback' => 'ccluk_sanitize_text',
				'default'           => '',
			)
		);

		$wp_customize->add_control( 'ccluk_contact_cf7',
			array(
				'label'     	=> esc_html__('Contact Form 7 Shortcode.', 'onesocial'),
				'section' 		=> 'ccluk_contact_content',
				'description'   => '',
			)
		);

		// Show CF7
		$wp_customize->add_setting( 'ccluk_contact_cf7_disable',
			array(
				'sanitize_callback' => 'ccluk_sanitize_checkbox',
				'default'           => '',
			)
		);

		$wp_customize->add_control( 'ccluk_contact_cf7_disable',
			array(
				'type'        => 'checkbox',
				'label'       => esc_html__('Hide contact form completely.', 'onesocial'),
				'section'     => 'ccluk_contact_content',
				'description' => esc_html__('Check this box to hide contact form.', 'onesocial'),
			)
		);

		// Contact Text
		$wp_customize->add_setting( 'ccluk_contact_text',
			array(
				'sanitize_callback' => 'ccluk_sanitize_text',
				'default'           => '',
			)
		);

		$wp_customize->add_control( new CCLUK_Editor_Custom_Control(
			$wp_customize,
			'ccluk_contact_text',
			array(
				'label'     	=> esc_html__('Contact Text', 'onesocial'),
				'section' 		=> 'ccluk_contact_content',
				'description'   => '',
			)
		));

		// hr
		$wp_customize->add_setting( 'ccluk_contact_text_hr', array( 'sanitize_callback' => 'ccluk_sanitize_text' ) );
		$wp_customize->add_control( new CCLUK_Misc_Control( $wp_customize, 'ccluk_contact_text_hr',
			array(
				'section'     => 'ccluk_contact_content',
				'type'        => 'hr'
			)
		));

		// Address Box
		$wp_customize->add_setting( 'ccluk_contact_address_title',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		$wp_customize->add_control( 'ccluk_contact_address_title',
			array(
				'label'     	=> esc_html__('Contact Box Title', 'onesocial'),
				'section' 		=> 'ccluk_contact_content',
				'description'   => '',
			)
		);

		// Contact Text
		$wp_customize->add_setting( 'ccluk_contact_address',
			array(
				'sanitize_callback' => 'ccluk_sanitize_text',
				'default'           => '',
			)
		);

		$wp_customize->add_control( 'ccluk_contact_address',
			array(
				'label'     => esc_html__('Address', 'onesocial'),
				'section' 		=> 'ccluk_contact_content',
				'description'   => '',
			)
		);

		// Contact Phone
		$wp_customize->add_setting( 'ccluk_contact_phone',
			array(
				'sanitize_callback' => 'ccluk_sanitize_text',
				'default'           => '',
			)
		);

		$wp_customize->add_control( 'ccluk_contact_phone',
			array(
				'label'     	=> esc_html__('Phone', 'onesocial'),
				'section' 		=> 'ccluk_contact_content',
				'description'   => '',
			)
		);

		// Contact Email
		$wp_customize->add_setting( 'ccluk_contact_email',
			array(
				'sanitize_callback' => 'sanitize_email',
				'default'           => '',
			)
		);

		$wp_customize->add_control( 'ccluk_contact_email',
			array(
				'label'     	=> esc_html__('Email', 'onesocial'),
				'section' 		=> 'ccluk_contact_content',
				'description'   => '',
			)
		);

		// Contact Fax
		$wp_customize->add_setting( 'ccluk_contact_fax',
			array(
				'sanitize_callback' => 'ccluk_sanitize_text',
				'default'           => '',
			)
		);

		$wp_customize->add_control( 'ccluk_contact_fax',
			array(
				'label'     	=> esc_html__('Fax', 'onesocial'),
				'section' 		=> 'ccluk_contact_content',
				'description'   => '',
			)
		);


    endif;

		/**
		 * Hook to add other customize
		 */
		do_action( 'ccluk_customize_after_register', $wp_customize );

}
add_action( 'customize_register', 'ccluk_customize_register' );
/**
 * Selective refresh
 */
require get_stylesheet_directory() . '/inc/customizer-selective-refresh.php';


/*------------------------------------------------------------------------*/
/*  CCLUK Sanitize Functions.
/*------------------------------------------------------------------------*/

function ccluk_sanitize_file_url( $file_url ) {
	$output = '';
	$filetype = wp_check_filetype( $file_url );
	if ( $filetype["ext"] ) {
		$output = esc_url( $file_url );
	}
	return $output;
}


/**
 * Conditional to show more hero settings
 *
 * @param $control
 * @return bool
 */
function ccluk_hero_fullscreen_callback ( $control ) {
	if ( $control->manager->get_setting('ccluk_hero_fullscreen')->value() == '' ) {
        return true;
    } else {
        return false;
    }
}


function ccluk_sanitize_number( $input ) {
    return balanceTags( $input );
}

function ccluk_sanitize_hex_color( $color ) {
	if ( $color === '' ) {
		return '';
	}
	if ( preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
		return $color;
	}
	return null;
}

function ccluk_sanitize_checkbox( $input ) {
    if ( $input == 1 ) {
		return 1;
    } else {
		return 0;
    }
}

function ccluk_sanitize_text( $string ) {
	return wp_kses_post( balanceTags( $string ) );
}

function ccluk_sanitize_html_input( $string ) {
	return wp_kses_allowed_html( $string );
}

function ccluk_showon_frontpage() {
	return true;
	//return is_page_template( 'template-frontpage.php' );
}

function ccluk_gallery_source_validate( $validity, $value ){
	if ( ! class_exists( 'CCLUK_PLus' ) ) {
		if ( $value != 'page' ) {
			$validity->add('notice', esc_html__('Upgrade to CCLUK Plus to unlock this feature.', 'onesocial' ) );
		}
	}
	return $validity;
}
/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function ccluk_customize_preview_js() {
    wp_enqueue_script( 'ccluk_customizer_liveview', get_stylesheet_directory_uri() . '/assets/js/customizer-liveview.js', array( 'customize-preview', 'customize-selective-refresh' ), false, true );
}
add_action( 'customize_preview_init', 'ccluk_customize_preview_js', 65 );



add_action( 'customize_controls_enqueue_scripts', 'ccluk_customize_js_settings' );
function ccluk_customize_js_settings(){
    if ( ! function_exists( 'ccluk_get_actions_required' ) ) {
        return;
    }
    $actions = ccluk_get_actions_required();
    $n = array_count_values( $actions );
    $number_action =  0;
    if ( $n && isset( $n['active'] ) ) {
        $number_action = $n['active'];
    }

    wp_localize_script( 'customize-controls', 'ccluk_customizer_settings', array(
        'number_action' => $number_action,
        'is_plus_activated' => class_exists( 'CCLUK_PLus' ) ? 'y' : 'n',
        'action_url' => admin_url( 'themes.php?page=ft_onepress&tab=actions_required' ),
    ) );
}
