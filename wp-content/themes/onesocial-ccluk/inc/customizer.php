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

	// Remove default sections.
	$wp_customize->remove_section( 'colors' );
	$wp_customize->remove_section( 'background_image' );

	// Custom WP default control & settings.
	$wp_customize->get_section( 'title_tagline' )->title = esc_html__('Site Title, Tagline & Logo', 'onesocial');
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	/**
	 * Hook to add other customize
	 */
	do_action( 'ccluk_customize_before_register', $wp_customize );


	$pages  =  get_pages();
	$option_pages = array();
	$option_pages[0] = esc_html__( 'Select page', 'onesocial' );
	foreach( $pages as $p ){
		$option_pages[ $p->ID ] = $p->post_title;
	}

	$users = get_users( array(
		'orderby'      => 'display_name',
		'order'        => 'ASC',
		'number'       => '',
	) );

	$option_users[0] = esc_html__( 'Select member', 'onesocial' );
	foreach( $users as $user ){
		$option_users[ $user->ID ] = $user->display_name;
	}

	/*------------------------------------------------------------------------*/
    /*  Site Identity.
    /*------------------------------------------------------------------------*/
        /*
         * @deprecated 1.2.0
         */
        /*
    	$wp_customize->add_setting( 'ccluk_site_image_logo',
			array(
				'sanitize_callback' => 'ccluk_sanitize_file_url',
				'default'           => ''
			)
		);
    	$wp_customize->add_control( new WP_Customize_Image_Control(
            $wp_customize,
            'ccluk_site_image_logo',
				array(
					'label' 		=> esc_html__('Site Image Logo', 'onesocial'),
					'section' 		=> 'title_tagline',
					'description'   => esc_html__('Your site image logo', 'onesocial'),
				)
			)
		);
        */
        $is_old_logo = get_theme_mod( 'ccluk_site_image_logo' );

        $wp_customize->add_setting( 'ccluk_hide_sitetitle',
            array(
                'sanitize_callback' => 'ccluk_sanitize_checkbox',
                'default'           => $is_old_logo ? 1: 0,
            )
        );
        $wp_customize->add_control(
            'ccluk_hide_sitetitle',
            array(
                'label' 		=> esc_html__('Hide site title', 'onesocial'),
                'section' 		=> 'title_tagline',
                'type'          => 'checkbox',
            )
        );

        $wp_customize->add_setting( 'ccluk_hide_tagline',
            array(
                'sanitize_callback' => 'ccluk_sanitize_checkbox',
                'default'           => $is_old_logo ? 1: 0,
            )
        );
        $wp_customize->add_control(
            'ccluk_hide_tagline',
            array(
                'label' 		=> esc_html__('Hide site tagline', 'onesocial'),
                'section' 		=> 'title_tagline',
                'type'          => 'checkbox',

            )
        );

	/*------------------------------------------------------------------------*/
    /*  Site Options
    /*------------------------------------------------------------------------*/
		$wp_customize->add_panel( 'ccluk_options',
			array(
				'priority'       => 22,
			    'capability'     => 'edit_theme_options',
			    'theme_supports' => '',
			    'title'          => esc_html__( 'Theme Options', 'onesocial' ),
			    'description'    => '',
			)
		);

		/* Global Settings
		----------------------------------------------------------------------*/
		$wp_customize->add_section( 'ccluk_global_settings' ,
			array(
				'priority'    => 3,
				'title'       => esc_html__( 'Global', 'onesocial' ),
				'description' => '',
				'panel'       => 'ccluk_options',
			)
		);

			// Disable Sticky Header
			$wp_customize->add_setting( 'ccluk_sticky_header_disable',
				array(
					'sanitize_callback' => 'ccluk_sanitize_checkbox',
					'default'           => '',
				)
			);
			$wp_customize->add_control( 'ccluk_sticky_header_disable',
				array(
					'type'        => 'checkbox',
					'label'       => esc_html__('Disable Sticky Header?', 'onesocial'),
					'section'     => 'ccluk_global_settings',
					'description' => esc_html__('Check this box to disable sticky header when scroll.', 'onesocial')
				)
			);

			// Disable Animation
			$wp_customize->add_setting( 'ccluk_animation_disable',
				array(
					'sanitize_callback' => 'ccluk_sanitize_checkbox',
					'default'           => '',
				)
			);
			$wp_customize->add_control( 'ccluk_animation_disable',
				array(
					'type'        => 'checkbox',
					'label'       => esc_html__('Disable animation effect?', 'onesocial'),
					'section'     => 'ccluk_global_settings',
					'description' => esc_html__('Check this box to disable all element animation when scroll.', 'onesocial')
				)
			);

			// Disable Animation
			$wp_customize->add_setting( 'ccluk_btt_disable',
				array(
					'sanitize_callback' => 'ccluk_sanitize_checkbox',
					'default'           => '',
					'transport'			=> 'postMessage'
				)
			);
			$wp_customize->add_control( 'ccluk_btt_disable',
				array(
					'type'        => 'checkbox',
					'label'       => esc_html__('Hide footer back to top?', 'onesocial'),
					'section'     => 'ccluk_global_settings',
					'description' => esc_html__('Check this box to hide footer back to top button.', 'onesocial')
				)
			);

		/* Colors
		----------------------------------------------------------------------*/
		$wp_customize->add_section( 'ccluk_colors_settings' ,
			array(
				'priority'    => 4,
				'title'       => esc_html__( 'Site Colors', 'onesocial' ),
				'description' => '',
				'panel'       => 'ccluk_options',
			)
		);
			// Primary Color
			$wp_customize->add_setting( 'ccluk_primary_color', array('sanitize_callback' => 'sanitize_hex_color_no_hash', 'sanitize_js_callback' => 'maybe_hash_hex_color', 'default' => '#03c4eb' ) );
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ccluk_primary_color',
				array(
					'label'       => esc_html__( 'Primary Color', 'onesocial' ),
					'section'     => 'ccluk_colors_settings',
					'description' => '',
					'priority'    => 1
				)
			));

            // Footer BG Color
            $wp_customize->add_setting( 'ccluk_footer_bg', array(
                'sanitize_callback' => 'sanitize_hex_color_no_hash',
                'sanitize_js_callback' => 'maybe_hash_hex_color',
                'default' => '',
                'transport' => 'postMessage'
            ) );
            $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ccluk_footer_bg',
                array(
                    'label'       => esc_html__( 'Footer Background', 'onesocial' ),
                    'section'     => 'ccluk_colors_settings',
                    'description' => '',
                )
            ));

            // Footer Widgets Color
            $wp_customize->add_setting( 'ccluk_footer_info_bg', array(
                'sanitize_callback' => 'sanitize_hex_color_no_hash',
                'sanitize_js_callback' => 'maybe_hash_hex_color',
                'default' => '',
                'transport' => 'postMessage'
            ) );
            $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ccluk_footer_info_bg',
                array(
                    'label'       => esc_html__( 'Footer Info Background', 'onesocial' ),
                    'section'     => 'ccluk_colors_settings',
                    'description' => '',
                )
            ));




		/* Header
		----------------------------------------------------------------------*/
		$wp_customize->add_section( 'ccluk_header_settings' ,
			array(
				'priority'    => 5,
				'title'       => esc_html__( 'Header', 'onesocial' ),
				'description' => '',
				'panel'       => 'ccluk_options',
			)
		);

		// Header BG Color
		$wp_customize->add_setting( 'ccluk_header_bg_color',
			array(
				'sanitize_callback' => 'sanitize_hex_color_no_hash',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
				'default' => ''
			) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ccluk_header_bg_color',
			array(
				'label'       => esc_html__( 'Background Color', 'onesocial' ),
				'section'     => 'ccluk_header_settings',
				'description' => '',
			)
		));


		// Site Title Color
		$wp_customize->add_setting( 'ccluk_logo_text_color',
			array(
				'sanitize_callback' => 'sanitize_hex_color_no_hash',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
				'default' => ''
			) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ccluk_logo_text_color',
			array(
				'label'       => esc_html__( 'Site Title Color', 'onesocial' ),
				'section'     => 'ccluk_header_settings',
				'description' => esc_html__( 'Only set if you don\'t use an image logo.', 'onesocial' ),
			)
		));

		// Header Menu Color
		$wp_customize->add_setting( 'ccluk_menu_color',
			array(
				'sanitize_callback' => 'sanitize_hex_color_no_hash',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
				'default' => ''
			) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ccluk_menu_color',
			array(
				'label'       => esc_html__( 'Menu Link Color', 'onesocial' ),
				'section'     => 'ccluk_header_settings',
				'description' => '',
			)
		));

		// Header Menu Hover Color
		$wp_customize->add_setting( 'ccluk_menu_hover_color',
			array(
				'sanitize_callback' => 'sanitize_hex_color_no_hash',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
				'default' => ''
			) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ccluk_menu_hover_color',
			array(
				'label'       => esc_html__( 'Menu Link Hover/Active Color', 'onesocial' ),
				'section'     => 'ccluk_header_settings',
				'description' => '',

			)
		));

		// Header Menu Hover BG Color
		$wp_customize->add_setting( 'ccluk_menu_hover_bg_color',
			array(
				'sanitize_callback' => 'sanitize_hex_color_no_hash',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
				'default' => ''
			) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ccluk_menu_hover_bg_color',
			array(
				'label'       => esc_html__( 'Menu Link Hover/Active BG Color', 'onesocial' ),
				'section'     => 'ccluk_header_settings',
				'description' => '',
			)
		));

		// Reponsive Mobie button color
		$wp_customize->add_setting( 'ccluk_menu_toggle_button_color',
			array(
				'sanitize_callback' => 'sanitize_hex_color_no_hash',
				'sanitize_js_callback' => 'maybe_hash_hex_color',
				'default' => ''
			) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ccluk_menu_toggle_button_color',
			array(
				'label'       => esc_html__( 'Responsive Menu Button Color', 'onesocial' ),
				'section'     => 'ccluk_header_settings',
				'description' => '',
			)
		));

		// Vertical align menu
		$wp_customize->add_setting( 'ccluk_vertical_align_menu',
			array(
				'sanitize_callback' => 'ccluk_sanitize_checkbox',
				'default'           => '',
			)
		);
		$wp_customize->add_control( 'ccluk_vertical_align_menu',
			array(
				'type'        => 'checkbox',
				'label'       => esc_html__('Center vertical align for menu', 'onesocial'),
				'section'     => 'ccluk_header_settings',
				'description' => esc_html__('If you use logo and your logo is too tall, check this box to auto vertical align menu.', 'onesocial')
			)
		);

		// Header Transparent
        $wp_customize->add_setting( 'ccluk_header_transparent',
            array(
                'sanitize_callback' => 'ccluk_sanitize_checkbox',
                'default'           => '',
                'active_callback'   => 'ccluk_showon_frontpage'
            )
        );
        $wp_customize->add_control( 'ccluk_header_transparent',
            array(
                'type'        => 'checkbox',
                'label'       => esc_html__('Header Transparent', 'onesocial'),
                'section'     => 'ccluk_header_settings',
                'description' => esc_html__('Apply for front page template only.', 'onesocial')
            )
        );

        $wp_customize->add_setting( 'ccluk_header_scroll_logo',
            array(
                'sanitize_callback' => 'ccluk_sanitize_checkbox',
                'default'           => 0,
                'active_callback'   => ''
            )
        );
        $wp_customize->add_control( 'ccluk_header_scroll_logo',
            array(
                'type'        => 'checkbox',
                'label'       => esc_html__('Scroll to top when click to the site logo or site title, only apply on front page.', 'onesocial'),
                'section'     => 'ccluk_header_settings',
            )
        );

		/* Social Settings
		----------------------------------------------------------------------*/
		$wp_customize->add_section( 'ccluk_social' ,
			array(
				'priority'    => 6,
				'title'       => esc_html__( 'Social Profiles', 'onesocial' ),
				'description' => '',
				'panel'       => 'ccluk_options',
			)
		);

			// Disable Social
			$wp_customize->add_setting( 'ccluk_social_disable',
				array(
					'sanitize_callback' => 'ccluk_sanitize_checkbox',
					'default'           => '1',
				)
			);
			$wp_customize->add_control( 'ccluk_social_disable',
				array(
					'type'        => 'checkbox',
					'label'       => esc_html__('Hide Footer Social?', 'onesocial'),
					'section'     => 'ccluk_social',
					'description' => esc_html__('Check this box to hide footer social section.', 'onesocial')
				)
			);

			$wp_customize->add_setting( 'ccluk_social_footer_guide',
				array(
					'sanitize_callback' => 'ccluk_sanitize_text'
				)
			);
			$wp_customize->add_control( new CCLUK_Misc_Control( $wp_customize, 'ccluk_social_footer_guide',
				array(
					'section'     => 'ccluk_social',
					'type'        => 'custom_message',
					'description' => esc_html__( 'These social profiles setting below will display at the footer of your site.', 'onesocial' )
				)
			));

			// Footer Social Title
			$wp_customize->add_setting( 'ccluk_social_footer_title',
				array(
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => esc_html__( 'Keep Updated', 'onesocial' ),
					'transport'			=> 'postMessage',
				)
			);
			$wp_customize->add_control( 'ccluk_social_footer_title',
				array(
					'label'       => esc_html__('Social Footer Title', 'onesocial'),
					'section'     => 'ccluk_social',
					'description' => ''
				)
			);

           // Socials
            $wp_customize->add_setting(
                'ccluk_social_profiles',
                array(
                    //'default' => '',
                    'sanitize_callback' => 'ccluk_sanitize_repeatable_data_field',
                    'transport' => 'postMessage', // refresh or postMessage
            ) );

            $wp_customize->add_control(
                new Onepress_Customize_Repeatable_Control(
                    $wp_customize,
                    'ccluk_social_profiles',
                    array(
                        'label' 		=> esc_html__('Socials', 'onesocial'),
                        'description'   => '',
                        'section'       => 'ccluk_social',
                        'live_title_id' => 'network', // apply for unput text and textarea only
                        'title_format'  => esc_html__('[live_title]', 'onesocial'), // [live_title]
                        'max_item'      => 5, // Maximum item can add
                        'limited_msg' 	=> wp_kses_post( 'Upgrade to <a target="_blank" href="https://www.famethemes.com/themes/onepress/?utm_source=theme_customizer&utm_medium=text_link&utm_campaign=ccluk_customizer#get-started">CCLUK Plus</a> to be able to add more items and unlock other premium features!', 'onesocial' ),
                        'fields'    => array(
                            'network'  => array(
                                'title' => esc_html__('Social network', 'onesocial'),
                                'type'  =>'text',
                            ),
                            'icon'  => array(
                                'title' => esc_html__('Icon', 'onesocial'),
                                'desc' => __('Paste your <a target="_blank" href="http://fortawesome.github.io/Font-Awesome/icons/">Font Awesome</a> icon class name here.', 'onesocial'),
                                'type'  =>'text',
                            ),
                            'link'  => array(
                                'title' => esc_html__('URL', 'onesocial'),
                                'type'  =>'text',
                            ),
                        ),

                    )
                )
            );

		/* Newsletter Settings
		----------------------------------------------------------------------*/
		$wp_customize->add_section( 'ccluk_newsletter' ,
			array(
				'priority'    => 9,
				'title'       => esc_html__( 'Newsletter', 'onesocial' ),
				'description' => '',
				'panel'       => 'ccluk_options',
			)
		);
			// Disable Newsletter
			$wp_customize->add_setting( 'ccluk_newsletter_disable',
				array(
					'sanitize_callback' => 'ccluk_sanitize_checkbox',
					'default'           => '1',
				)
			);
			$wp_customize->add_control( 'ccluk_newsletter_disable',
				array(
					'type'        => 'checkbox',
					'label'       => esc_html__('Hide Footer Newsletter?', 'onesocial'),
					'section'     => 'ccluk_newsletter',
					'description' => esc_html__('Check this box to hide footer newsletter form.', 'onesocial')
				)
			);

			// Mailchimp Form Title
			$wp_customize->add_setting( 'ccluk_newsletter_title',
				array(
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => esc_html__( 'Join our Newsletter', 'onesocial' ),
                    'transport'         => 'postMessage', // refresh or postMessage
				)
			);
			$wp_customize->add_control( 'ccluk_newsletter_title',
				array(
					'label'       => esc_html__('Newsletter Form Title', 'onesocial'),
					'section'     => 'ccluk_newsletter',
					'description' => ''
				)
			);

			// Mailchimp action url
			$wp_customize->add_setting( 'ccluk_newsletter_mailchimp',
				array(
					'sanitize_callback' => 'esc_url',
					'default'           => '',
                    'transport'         => 'postMessage', // refresh or postMessage
				)
			);
			$wp_customize->add_control( 'ccluk_newsletter_mailchimp',
				array(
					'label'       => esc_html__('MailChimp Action URL', 'onesocial'),
					'section'     => 'ccluk_newsletter',
					'description' => __( 'The newsletter form use MailChimp, please follow <a target="_blank" href="http://goo.gl/uRVIst">this guide</a> to know how to get MailChimp Action URL. Example <i>//famethemes.us8.list-manage.com/subscribe/post?u=521c400d049a59a4b9c0550c2&amp;id=83187e0006</i>', 'onesocial' )
				)
			);

			/* Hero options
			----------------------------------------------------------------------*/
			$wp_customize->add_section(
				'ccluk_hero_options',
				array(
					'title'       => __( 'Hero Options', 'onesocial' ),
					'panel'       => 'ccluk_options',
				)
			);


			$wp_customize->add_setting(
				'ccluk_hero_option_animation',
				array(
					'default'              => 'flipInX',
					'sanitize_callback'    => 'sanitize_text_field',
				)
			);

			/**
			 * @see https://github.com/daneden/animate.css
			 */

			$animations_css = 'bounce flash pulse rubberBand shake headShake swing tada wobble jello bounceIn bounceInDown bounceInLeft bounceInRight bounceInUp bounceOut bounceOutDown bounceOutLeft bounceOutRight bounceOutUp fadeIn fadeInDown fadeInDownBig fadeInLeft fadeInLeftBig fadeInRight fadeInRightBig fadeInUp fadeInUpBig fadeOut fadeOutDown fadeOutDownBig fadeOutLeft fadeOutLeftBig fadeOutRight fadeOutRightBig fadeOutUp fadeOutUpBig flipInX flipInY flipOutX flipOutY lightSpeedIn lightSpeedOut rotateIn rotateInDownLeft rotateInDownRight rotateInUpLeft rotateInUpRight rotateOut rotateOutDownLeft rotateOutDownRight rotateOutUpLeft rotateOutUpRight hinge rollIn rollOut zoomIn zoomInDown zoomInLeft zoomInRight zoomInUp zoomOut zoomOutDown zoomOutLeft zoomOutRight zoomOutUp slideInDown slideInLeft slideInRight slideInUp slideOutDown slideOutLeft slideOutRight slideOutUp';

			$animations_css = explode( ' ', $animations_css );
			$animations = array();
			foreach ( $animations_css as $v ) {
				$v =  trim( $v );
				if ( $v ){
					$animations[ $v ]= $v;
				}

			}

			$wp_customize->add_control(
				'ccluk_hero_option_animation',
				array(
					'label'    => __( 'Text animation', 'onesocial' ),
					'section'  => 'ccluk_hero_options',
					'type'     => 'select',
					'choices' => $animations,
				)
			);


			$wp_customize->add_setting(
				'ccluk_hero_option_speed',
				array(
					'default'              => '5000',
					'sanitize_callback'    => 'sanitize_text_field',
				)
			);

			$wp_customize->add_control(
				'ccluk_hero_option_speed',
				array(
					'label'    => __( 'Speed', 'onesocial' ),
					'description' => esc_html__( 'The delay between the changing of each phrase in milliseconds.', 'onesocial' ),
					'section'  => 'ccluk_hero_options',
				)
			);


			/* Custom CSS Settings
			----------------------------------------------------------------------*/
			$wp_customize->add_section(
				'ccluk_custom_code',
				array(
					'title'       => __( 'Custom CSS', 'onesocial' ),
					'panel'       => 'ccluk_options',
				)
			);


			$wp_customize->add_setting(
				'ccluk_custom_css',
				array(
					'default'              => '',
					'sanitize_callback'    => 'ccluk_sanitize_css',
					'type' 				   => 'option',
				)
			);

			$wp_customize->add_control(
				'ccluk_custom_css',
				array(
					'label'    => __( 'Custom CSS', 'onesocial' ),
					'section'  => 'ccluk_custom_code',
					'type'     => 'textarea'
				)
			);


	/*------------------------------------------------------------------------*/
    /*  Section: Order & Styling
    /*------------------------------------------------------------------------*/
	$wp_customize->add_section( 'ccluk_order_styling' ,
		array(
			'priority'        => 129,
			'title'           => esc_html__( 'Section Order & Styling', 'onesocial' ),
			'description'     => '',
			'active_callback' => 'ccluk_showon_frontpage'
		)
	);
		// Plus message
		$wp_customize->add_setting( 'ccluk_order_styling_message',
			array(
				'sanitize_callback' => 'ccluk_sanitize_text',
			)
		);
		$wp_customize->add_control( new CCLUK_Misc_Control( $wp_customize, 'ccluk_order_styling_message',
			array(
				'section'     => 'ccluk_news_settings',
				'type'        => 'custom_message',
				'section'     => 'ccluk_order_styling',
				'description' => wp_kses_post( 'Check out <a target="_blank" href="https://www.famethemes.com/themes/onepress/?utm_source=theme_customizer&utm_medium=text_link&utm_campaign=ccluk_customizer#get-started">CCLUK Plus version</a> for full control over <strong>section order</strong> and <strong>section styling</strong>! ', 'onesocial' )
			)
		));


	/*------------------------------------------------------------------------*/
    /*  Section: Hero
    /*------------------------------------------------------------------------*/

	$wp_customize->add_panel( 'ccluk_hero_panel' ,
		array(
			'priority'        => 130,
			'title'           => esc_html__( 'Section: Hero', 'onesocial' ),
			'description'     => '',
			'active_callback' => 'ccluk_showon_frontpage'
		)
	);

		// Hero settings
		$wp_customize->add_section( 'ccluk_hero_settings' ,
			array(
				'priority'    => 3,
				'title'       => esc_html__( 'Hero Settings', 'onesocial' ),
				'description' => '',
				'panel'       => 'ccluk_hero_panel',
			)
		);

			// Show section
			$wp_customize->add_setting( 'ccluk_hero_disable',
				array(
					'sanitize_callback' => 'ccluk_sanitize_checkbox',
					'default'           => '',
				)
			);
			$wp_customize->add_control( 'ccluk_hero_disable',
				array(
					'type'        => 'checkbox',
					'label'       => esc_html__('Hide this section?', 'onesocial'),
					'section'     => 'ccluk_hero_settings',
					'description' => esc_html__('Check this box to hide this section.', 'onesocial'),
				)
			);
			// Section ID
			$wp_customize->add_setting( 'ccluk_hero_id',
				array(
					'sanitize_callback' => 'ccluk_sanitize_text',
					'default'           => esc_html__('hero', 'onesocial'),
				)
			);
			$wp_customize->add_control( 'ccluk_hero_id',
				array(
					'label' 		=> esc_html__('Section ID:', 'onesocial'),
					'section' 		=> 'ccluk_hero_settings',
					'description'   => esc_html__( 'The section id, we will use this for link anchor.', 'onesocial' )
				)
			);

			// Show hero full screen
			$wp_customize->add_setting( 'ccluk_hero_fullscreen',
				array(
					'sanitize_callback' => 'ccluk_sanitize_checkbox',
					'default'           => '',
				)
			);
			$wp_customize->add_control( 'ccluk_hero_fullscreen',
				array(
					'type'        => 'checkbox',
					'label'       => esc_html__('Make hero section full screen', 'onesocial'),
					'section'     => 'ccluk_hero_settings',
					'description' => esc_html__('Check this box to make hero section full screen.', 'onesocial'),
				)
			);

			// Hero content padding top
			$wp_customize->add_setting( 'ccluk_hero_pdtop',
				array(
					'sanitize_callback' => 'ccluk_sanitize_text',
					'default'           => esc_html__('10', 'onesocial'),
				)
			);
			$wp_customize->add_control( 'ccluk_hero_pdtop',
				array(
					'label'           => esc_html__('Padding Top:', 'onesocial'),
					'section'         => 'ccluk_hero_settings',
					'description'     => esc_html__( 'The hero content padding top in percent (%).', 'onesocial' ),
					'active_callback' => 'ccluk_hero_fullscreen_callback'
				)
			);

			// Hero content padding bottom
			$wp_customize->add_setting( 'ccluk_hero_pdbotom',
				array(
					'sanitize_callback' => 'ccluk_sanitize_text',
					'default'           => esc_html__('10', 'onesocial'),
				)
			);
			$wp_customize->add_control( 'ccluk_hero_pdbotom',
				array(
					'label'           => esc_html__('Padding Bottom:', 'onesocial'),
					'section'         => 'ccluk_hero_settings',
					'description'     => esc_html__( 'The hero content padding bottom in percent (%).', 'onesocial' ),
					'active_callback' => 'ccluk_hero_fullscreen_callback'
				)
			);

		$wp_customize->add_section( 'ccluk_hero_images' ,
			array(
				'priority'    => 6,
				'title'       => esc_html__( 'Hero Background Media', 'onesocial' ),
				'description' => '',
				'panel'       => 'ccluk_hero_panel',
			)
		);

			$wp_customize->add_setting(
				'ccluk_hero_images',
				array(
					'sanitize_callback' => 'ccluk_sanitize_repeatable_data_field',
					'transport' => 'refresh', // refresh or postMessage
					'default' => json_encode( array(
						array(
							'image'=> array(
								'url' => get_template_directory_uri().'/assets/images/hero5.jpg',
								'id' => ''
							)
						)
					) )
				) );

			$wp_customize->add_control(
				new Onepress_Customize_Repeatable_Control(
					$wp_customize,
					'ccluk_hero_images',
					array(
						'label'     => esc_html__('Background Images', 'onesocial'),
						'description'   => '',
						'priority'     => 40,
						'section'       => 'ccluk_hero_images',
						'title_format'  => esc_html__( 'Background', 'onesocial'), // [live_title]
						'max_item'      => 2, // Maximum item can add

						'fields'    => array(
							'image' => array(
								'title' => esc_html__('Background Image', 'onesocial'),
								'type'  =>'media',
								'default' => array(
									'url' => get_template_directory_uri().'/assets/images/hero5.jpg',
									'id' => ''
								)
							),

						),

					)
				)
			);

			// Overlay color
			$wp_customize->add_setting( 'ccluk_hero_overlay_color',
				array(
					'sanitize_callback' => 'ccluk_sanitize_color_alpha',
					'default'           => 'rgba(0,0,0,.3)',
					'transport' => 'refresh', // refresh or postMessage
				)
			);
			$wp_customize->add_control( new CCLUK_Alpha_Color_Control(
					$wp_customize,
					'ccluk_hero_overlay_color',
					array(
						'label' 		=> esc_html__('Background Overlay Color', 'onesocial'),
						'section' 		=> 'ccluk_hero_images',
						'priority'      => 130,
					)
				)
			);


            // Parallax
            $wp_customize->add_setting( 'ccluk_hero_parallax',
                array(
                    'sanitize_callback' => 'ccluk_sanitize_checkbox',
                    'default'           => 0,
                    'transport' => 'refresh', // refresh or postMessage
                )
            );
            $wp_customize->add_control(
                'ccluk_hero_parallax',
                array(
                    'label' 		=> esc_html__('Enable parallax effect (apply for first BG image only)', 'onesocial'),
                    'section' 		=> 'ccluk_hero_images',
                    'type' 		   => 'checkbox',
                    'priority'      => 50,
                    'description' => '',
                )
            );

			// Background Video
			$wp_customize->add_setting( 'ccluk_hero_videobackground_upsell',
				array(
					'sanitize_callback' => 'ccluk_sanitize_text',
				)
			);
			$wp_customize->add_control( new CCLUK_Misc_Control( $wp_customize, 'ccluk_hero_videobackground_upsell',
				array(
					'section'     => 'ccluk_hero_images',
					'type'        => 'custom_message',
					'description' => wp_kses_post( 'Want to add <strong>background video</strong> for hero section? Upgrade to <a target="_blank" href="https://www.famethemes.com/themes/onepress/?utm_source=theme_customizer&utm_medium=text_link&utm_campaign=ccluk_customizer#get-started">CCLUK Plus</a> version.', 'onesocial' ),
					'priority'    => 131,
				)
			));



		$wp_customize->add_section( 'ccluk_hero_content_layout1' ,
			array(
				'priority'    => 9,
				'title'       => esc_html__( 'Hero Content Layout', 'onesocial' ),
				'description' => '',
				'panel'       => 'ccluk_hero_panel',

			)
		);

			// Hero Layout
			$wp_customize->add_setting( 'ccluk_hero_layout',
				array(
					'sanitize_callback' => 'ccluk_sanitize_text',
					'default'           => '1',
				)
			);
			$wp_customize->add_control( 'ccluk_hero_layout',
				array(
					'label' 		=> esc_html__('Display Layout', 'onesocial'),
					'section' 		=> 'ccluk_hero_content_layout1',
					'description'   => '',
					'type'          => 'select',
					'choices'       => array(
						'1' => esc_html__('Layout 1', 'onesocial' ),
						'2' => esc_html__('Layout 2', 'onesocial' ),
					),
				)
			);
			// For Hero layout ------------------------

				// Large Text
				$wp_customize->add_setting( 'ccluk_hcl1_largetext',
					array(
						'sanitize_callback' => 'ccluk_sanitize_text',
						'mod' 				=> 'html',
						'default'           => wp_kses_post('We are <span class="js-rotating">CCLUK | One Page | Responsive | Perfection</span>', 'onesocial'),
					)
				);
				$wp_customize->add_control( new CCLUK_Editor_Custom_Control(
					$wp_customize,
					'ccluk_hcl1_largetext',
					array(
						'label' 		=> esc_html__('Large Text', 'onesocial'),
						'section' 		=> 'ccluk_hero_content_layout1',
						'description'   => esc_html__('Text Rotating Guide: Put your rotate texts separate by "|" into <span class="js-rotating">...</span>, go to Customizer->Site Option->Animate to control rotate animation.', 'onesocial'),
					)
				));

				// Small Text
				$wp_customize->add_setting( 'ccluk_hcl1_smalltext',
					array(
						'sanitize_callback' => 'ccluk_sanitize_text',
						'default'			=> wp_kses_post('Morbi tempus porta nunc <strong>pharetra quisque</strong> ligula imperdiet posuere<br> vitae felis proin sagittis leo ac tellus blandit sollicitudin quisque vitae placerat.', 'onesocial'),
					)
				);
				$wp_customize->add_control( new CCLUK_Editor_Custom_Control(
					$wp_customize,
					'ccluk_hcl1_smalltext',
					array(
						'label' 		=> esc_html__('Small Text', 'onesocial'),
						'section' 		=> 'ccluk_hero_content_layout1',
						'mod' 				=> 'html',
						'description'   => esc_html__('You can use text rotate slider in this textarea too.', 'onesocial'),
					)
				));

				// Button #1 Text
				$wp_customize->add_setting( 'ccluk_hcl1_btn1_text',
					array(
						'sanitize_callback' => 'ccluk_sanitize_text',
						'default'           => esc_html__('About Us', 'onesocial'),
					)
				);
				$wp_customize->add_control( 'ccluk_hcl1_btn1_text',
					array(
						'label' 		=> esc_html__('Button #1 Text', 'onesocial'),
						'section' 		=> 'ccluk_hero_content_layout1'
					)
				);

				// Button #1 Link
				$wp_customize->add_setting( 'ccluk_hcl1_btn1_link',
					array(
						'sanitize_callback' => 'esc_url',
						'default'           => esc_url( home_url( '/' )).esc_html__('#about', 'onesocial'),
					)
				);
				$wp_customize->add_control( 'ccluk_hcl1_btn1_link',
					array(
						'label' 		=> esc_html__('Button #1 Link', 'onesocial'),
						'section' 		=> 'ccluk_hero_content_layout1'
					)
				);
                // Button #1 Style
				$wp_customize->add_setting( 'ccluk_hcl1_btn1_style',
					array(
						'sanitize_callback' => 'ccluk_sanitize_text',
						'default'           => 'btn-theme-primary',
					)
				);
				$wp_customize->add_control( 'ccluk_hcl1_btn1_style',
					array(
						'label' 		=> esc_html__('Button #1 style', 'onesocial'),
						'section' 		=> 'ccluk_hero_content_layout1',
                        'type'          => 'select',
                        'choices' => array(
                                'btn-theme-primary' => esc_html__('Button Primary', 'onesocial'),
                                'btn-secondary-outline' => esc_html__('Button Secondary', 'onesocial'),
                                'btn-default' => esc_html__('Button', 'onesocial'),
                                'btn-primary' => esc_html__('Primary', 'onesocial'),
                                'btn-success' => esc_html__('Success', 'onesocial'),
                                'btn-info' => esc_html__('Info', 'onesocial'),
                                'btn-warning' => esc_html__('Warning', 'onesocial'),
                                'btn-danger' => esc_html__('Danger', 'onesocial'),
                        )
					)
				);

				// Button #2 Text
				$wp_customize->add_setting( 'ccluk_hcl1_btn2_text',
					array(
						'sanitize_callback' => 'ccluk_sanitize_text',
						'default'           => esc_html__('Get Started', 'onesocial'),
					)
				);
				$wp_customize->add_control( 'ccluk_hcl1_btn2_text',
					array(
						'label' 		=> esc_html__('Button #2 Text', 'onesocial'),
						'section' 		=> 'ccluk_hero_content_layout1'
					)
				);

				// Button #2 Link
				$wp_customize->add_setting( 'ccluk_hcl1_btn2_link',
					array(
						'sanitize_callback' => 'esc_url',
						'default'           => esc_url( home_url( '/' )).esc_html__('#contact', 'onesocial'),
					)
				);
				$wp_customize->add_control( 'ccluk_hcl1_btn2_link',
					array(
						'label' 		=> esc_html__('Button #2 Link', 'onesocial'),
						'section' 		=> 'ccluk_hero_content_layout1'
					)
				);

                // Button #1 Style
                $wp_customize->add_setting( 'ccluk_hcl1_btn2_style',
                    array(
                        'sanitize_callback' => 'ccluk_sanitize_text',
                        'default'           => 'btn-secondary-outline',
                    )
                );
                $wp_customize->add_control( 'ccluk_hcl1_btn2_style',
                    array(
                        'label' 		=> esc_html__('Button #2 style', 'onesocial'),
                        'section' 		=> 'ccluk_hero_content_layout1',
                        'type'          => 'select',
                        'choices' => array(
                            'btn-theme-primary' => esc_html__('Button Primary', 'onesocial'),
                            'btn-secondary-outline' => esc_html__('Button Secondary', 'onesocial'),
                            'btn-default' => esc_html__('Button', 'onesocial'),
                            'btn-primary' => esc_html__('Primary', 'onesocial'),
                            'btn-success' => esc_html__('Success', 'onesocial'),
                            'btn-info' => esc_html__('Info', 'onesocial'),
                            'btn-warning' => esc_html__('Warning', 'onesocial'),
                            'btn-danger' => esc_html__('Danger', 'onesocial'),
                        )
                    )
                );


				/* Layout 2 ---- */

				// Layout 22 content text
				$wp_customize->add_setting( 'ccluk_hcl2_content',
					array(
						'sanitize_callback' => 'ccluk_sanitize_text',
						'mod' 				=> 'html',
						'default'           =>  wp_kses_post( '<h1>Business Website'."\n".'Made Simple.</h1>'."\n".'We provide creative solutions to clients around the world,'."\n".'creating things that get attention and meaningful.'."\n\n".'<a class="btn btn-secondary-outline btn-lg" href="#">Get Started</a>' ),
					)
				);
				$wp_customize->add_control( new CCLUK_Editor_Custom_Control(
					$wp_customize,
					'ccluk_hcl2_content',
					array(
						'label' 		=> esc_html__('Content Text', 'onesocial'),
						'section' 		=> 'ccluk_hero_content_layout1',
						'description'   => '',
					)
				));

				// Layout 2 image
				$wp_customize->add_setting( 'ccluk_hcl2_image',
					array(
						'sanitize_callback' => 'ccluk_sanitize_text',
						'mod' 				=> 'html',
						'default'           =>  get_template_directory_uri().'/assets/images/ccluk_responsive.png',
					)
				);
				$wp_customize->add_control( new WP_Customize_Image_Control(
					$wp_customize,
					'ccluk_hcl2_image',
					array(
						'label' 		=> esc_html__('Image', 'onesocial'),
						'section' 		=> 'ccluk_hero_content_layout1',
						'description'   => '',
					)
				));


			// END For Hero layout ------------------------

	/*------------------------------------------------------------------------*/
	/*  Section: Video Popup
	/*------------------------------------------------------------------------*/
	$wp_customize->add_panel( 'ccluk_videolightbox' ,
		array(
			'priority'        => 180,
			'title'           => esc_html__( 'Section: Video Lightbox', 'onesocial' ),
			'description'     => '',
			'active_callback' => 'ccluk_showon_frontpage'
		)
	);

    $wp_customize->add_section( 'ccluk_videolightbox_settings' ,
        array(
            'priority'    => 3,
            'title'       => esc_html__( 'Section Settings', 'onesocial' ),
            'description' => '',
            'panel'       => 'ccluk_videolightbox',
        )
    );

    // Show Content
    $wp_customize->add_setting( 'ccluk_videolightbox_disable',
        array(
            'sanitize_callback' => 'ccluk_sanitize_checkbox',
            'default'           => '',
        )
    );
    $wp_customize->add_control( 'ccluk_videolightbox_disable',
        array(
            'type'        => 'checkbox',
            'label'       => esc_html__('Hide this section?', 'onesocial'),
            'section'     => 'ccluk_videolightbox_settings',
            'description' => esc_html__('Check this box to hide this section.', 'onesocial'),
        )
    );

    // Section ID
    $wp_customize->add_setting( 'ccluk_videolightbox_id',
        array(
            'sanitize_callback' => 'ccluk_sanitize_text',
            'default'           => 'videolightbox',
        )
    );
    $wp_customize->add_control( 'ccluk_videolightbox_id',
        array(
            'label' 		=> esc_html__('Section ID:', 'onesocial'),
            'section' 		=> 'ccluk_videolightbox_settings',
            'description'   => esc_html__('The section id, we will use this for link anchor.', 'onesocial' )
        )
    );

    // Title
    $wp_customize->add_setting( 'ccluk_videolightbox_title',
        array(
            'sanitize_callback' => 'ccluk_sanitize_text',
            'default'           => '',
        )
    );

    $wp_customize->add_control( new CCLUK_Editor_Custom_Control(
        $wp_customize,
        'ccluk_videolightbox_title',
        array(
            'label'     	=>  esc_html__('Section heading', 'onesocial'),
            'section' 		=> 'ccluk_videolightbox_settings',
            'description'   => '',
        )
    ));

    // Video URL
    $wp_customize->add_setting( 'ccluk_videolightbox_url',
        array(
            'sanitize_callback' => 'esc_url_raw',
            'default'           => '',
        )
    );
    $wp_customize->add_control( 'ccluk_videolightbox_url',
        array(
            'label' 		=> esc_html__('Video url', 'onesocial'),
            'section' 		=> 'ccluk_videolightbox_settings',
            'description'   =>  esc_html__('Paste Youtube or Vimeo url here', 'onesocial'),
        )
    );

    // Parallax image
    $wp_customize->add_setting( 'ccluk_videolightbox_image',
        array(
            'sanitize_callback' => 'esc_url_raw',
            'default'           => '',
        )
    );
    $wp_customize->add_control( new WP_Customize_Image_Control(
        $wp_customize,
        'ccluk_videolightbox_image',
        array(
            'label' 		=> esc_html__('Background image', 'onesocial'),
            'section' 		=> 'ccluk_videolightbox_settings',
        )
    ));


	/*------------------------------------------------------------------------*/
	/*  Section: Gallery
    /*------------------------------------------------------------------------*/
	$wp_customize->add_panel( 'ccluk_gallery' ,
		array(
			'priority'        => 190,
			'title'           => esc_html__( 'Section: Gallery', 'onesocial' ),
			'description'     => '',
			'active_callback' => 'ccluk_showon_frontpage'
		)
	);

	$wp_customize->add_section( 'ccluk_gallery_settings' ,
		array(
			'priority'    => 3,
			'title'       => esc_html__( 'Section Settings', 'onesocial' ),
			'description' => '',
			'panel'       => 'ccluk_gallery',
		)
	);

	// Show Content
	$wp_customize->add_setting( 'ccluk_gallery_disable',
		array(
			'sanitize_callback' => 'ccluk_sanitize_checkbox',
			'default'           => 1,
		)
	);
	$wp_customize->add_control( 'ccluk_gallery_disable',
		array(
			'type'        => 'checkbox',
			'label'       => esc_html__('Hide this section?', 'onesocial'),
			'section'     => 'ccluk_gallery_settings',
			'description' => esc_html__('Check this box to hide this section.', 'onesocial'),
		)
	);

	// Section ID
	$wp_customize->add_setting( 'ccluk_gallery_id',
		array(
			'sanitize_callback' => 'ccluk_sanitize_text',
			'default'           => esc_html__('gallery', 'onesocial'),
		)
	);
	$wp_customize->add_control( 'ccluk_gallery_id',
		array(
			'label'     => esc_html__('Section ID:', 'onesocial'),
			'section' 		=> 'ccluk_gallery_settings',
			'description'   => esc_html__( 'The section id, we will use this for link anchor.', 'onesocial' )
		)
	);

	// Title
	$wp_customize->add_setting( 'ccluk_gallery_title',
		array(
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => esc_html__('Gallery', 'onesocial'),
		)
	);
	$wp_customize->add_control( 'ccluk_gallery_title',
		array(
			'label'     => esc_html__('Section Title', 'onesocial'),
			'section' 		=> 'ccluk_gallery_settings',
			'description'   => '',
		)
	);

	// Sub Title
	$wp_customize->add_setting( 'ccluk_gallery_subtitle',
		array(
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => esc_html__('Section subtitle', 'onesocial'),
		)
	);
	$wp_customize->add_control( 'ccluk_gallery_subtitle',
		array(
			'label'     => esc_html__('Section Subtitle', 'onesocial'),
			'section' 		=> 'ccluk_gallery_settings',
			'description'   => '',
		)
	);

	// Description
	$wp_customize->add_setting( 'ccluk_gallery_desc',
		array(
			'sanitize_callback' => 'ccluk_sanitize_text',
			'default'           => '',
		)
	);
	$wp_customize->add_control( new CCLUK_Editor_Custom_Control(
		$wp_customize,
		'ccluk_gallery_desc',
		array(
			'label' 		=> esc_html__('Section Description', 'onesocial'),
			'section' 		=> 'ccluk_gallery_settings',
			'description'   => '',
		)
	));

	$wp_customize->add_section( 'ccluk_gallery_content' ,
		array(
			'priority'    => 6,
			'title'       => esc_html__( 'Section Content', 'onesocial' ),
			'description' => '',
			'panel'       => 'ccluk_gallery',
		)
	);
	// Gallery Source
	$wp_customize->add_setting( 'ccluk_gallery_source',
		array(
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'ccluk_gallery_source_validate',
			'default'           => 'page',
		)
	);
	$wp_customize->add_control( 'ccluk_gallery_source',
		array(
			'label'     	=> esc_html__('Select Gallery Source', 'onesocial'),
			'section' 		=> 'ccluk_gallery_content',
			'type'          => 'select',
			'priority'      => 5,
			'choices'       => array(
				'page'      => esc_html__('Page', 'onesocial'),
				'facebook'  => 'Facebook',
				'instagram' => 'Instagram',
				'flickr'    => 'Flickr',
			)
		)
	);

	// Source page settings
	$wp_customize->add_setting( 'ccluk_gallery_source_page',
		array(
			'sanitize_callback' => 'ccluk_sanitize_number',
			'default'           => '',
		)
	);
	$wp_customize->add_control( 'ccluk_gallery_source_page',
		array(
			'label'     	=> esc_html__('Select Gallery Page', 'onesocial'),
			'section' 		=> 'ccluk_gallery_content',
			'type'          => 'select',
			'priority'      => 10,
			'choices'       => $option_pages,
			'description'   => esc_html__('Select a page which have content contain [gallery] shortcode.', 'onesocial'),
		)
	);


	// Gallery Layout
	$wp_customize->add_setting( 'ccluk_gallery_layout',
		array(
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 'default',
		)
	);
	$wp_customize->add_control( 'ccluk_gallery_layout',
		array(
			'label'     	=> esc_html__('Layout', 'onesocial'),
			'section' 		=> 'ccluk_gallery_content',
			'type'          => 'select',
			'priority'      => 40,
			'choices'       => array(
				'default'      => esc_html__('Default, inside container', 'onesocial'),
				'full-width'  => esc_html__('Full Width', 'onesocial'),
			)
		)
	);

	// Gallery Display
	$wp_customize->add_setting( 'ccluk_gallery_display',
		array(
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 'default',
		)
	);
	$wp_customize->add_control( 'ccluk_gallery_display',
		array(
			'label'     	=> esc_html__('Display', 'onesocial'),
			'section' 		=> 'ccluk_gallery_content',
			'type'          => 'select',
			'priority'      => 50,
			'choices'       => array(
				'grid'      => esc_html__('Grid', 'onesocial'),
				'carousel'    => esc_html__('Carousel', 'onesocial'),
				'slider'      => esc_html__('Slider', 'onesocial'),
				'justified'   => esc_html__('Justified', 'onesocial'),
				'masonry'     => esc_html__('Masonry', 'onesocial'),
			)
		)
	);

	// Gallery grid spacing
	$wp_customize->add_setting( 'ccluk_g_spacing',
		array(
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 20,
		)
	);
	$wp_customize->add_control( 'ccluk_g_spacing',
		array(
			'label'     	=> esc_html__('Item Spacing', 'onesocial'),
			'section' 		=> 'ccluk_gallery_content',
			'priority'      => 55,

		)
	);

	// Gallery grid spacing
	$wp_customize->add_setting( 'ccluk_g_row_height',
		array(
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 120,
		)
	);
	$wp_customize->add_control( 'ccluk_g_row_height',
		array(
			'label'     	=> esc_html__('Row Height', 'onesocial'),
			'section' 		=> 'ccluk_gallery_content',
			'priority'      => 57,

		)
	);

	// Gallery grid gird col
	$wp_customize->add_setting( 'ccluk_g_col',
		array(
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '4',
		)
	);
	$wp_customize->add_control( 'ccluk_g_col',
		array(
			'label'     	=> esc_html__('Layout columns', 'onesocial'),
			'section' 		=> 'ccluk_gallery_content',
			'priority'      => 60,
			'type'          => 'select',
			'choices'       => array(
				'1'      => 1,
				'2'      => 2,
				'3'      => 3,
				'4'      => 4,
				'5'      => 5,
				'6'      => 6,
			)

		)
	);

	// Gallery max number
	$wp_customize->add_setting( 'ccluk_g_number',
		array(
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 10,
		)
	);
	$wp_customize->add_control( 'ccluk_g_number',
		array(
			'label'     	=> esc_html__('Number items', 'onesocial'),
			'section' 		=> 'ccluk_gallery_content',
			'priority'      => 65,
		)
	);
	// Gallery grid spacing
	$wp_customize->add_setting( 'ccluk_g_lightbox',
		array(
			'sanitize_callback' => 'ccluk_sanitize_checkbox',
			'default'           => 1,
		)
	);
	$wp_customize->add_control( 'ccluk_g_lightbox',
		array(
			'label'     	=> esc_html__('Enable Lightbox', 'onesocial'),
			'section' 		=> 'ccluk_gallery_content',
			'priority'      => 70,
			'type'          => 'checkbox',
		)
	);


	/*------------------------------------------------------------------------*/
    /*  Section: About
    /*------------------------------------------------------------------------*/
    $wp_customize->add_panel( 'ccluk_about' ,
		array(
			'priority'        => 160,
			'title'           => esc_html__( 'Section: About', 'onesocial' ),
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

		// Sub Title
		$wp_customize->add_setting( 'ccluk_about_subtitle',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => esc_html__('Section subtitle', 'onesocial'),
			)
		);
		$wp_customize->add_control( 'ccluk_about_subtitle',
			array(
				'label' 		=> esc_html__('Section Subtitle', 'onesocial'),
				'section' 		=> 'ccluk_about_settings',
				'description'   => '',
			)
		);

		// Description
		$wp_customize->add_setting( 'ccluk_about_desc',
			array(
				'sanitize_callback' => 'ccluk_sanitize_text',
				'default'           => '',
			)
		);
		$wp_customize->add_control( new CCLUK_Editor_Custom_Control(
			$wp_customize,
			'ccluk_about_desc',
			array(
				'label' 		=> esc_html__('Section Description', 'onesocial'),
				'section' 		=> 'ccluk_about_settings',
				'description'   => '',
			)
		));


	$wp_customize->add_section( 'ccluk_about_content' ,
		array(
			'priority'    => 6,
			'title'       => esc_html__( 'Section Content', 'onesocial' ),
			'description' => '',
			'panel'       => 'ccluk_about',
		)
	);

		// Order & Stlying
		$wp_customize->add_setting(
			'ccluk_about_boxes',
			array(
				//'default' => '',
				'sanitize_callback' => 'ccluk_sanitize_repeatable_data_field',
				'transport' => 'refresh', // refresh or postMessage
			) );


			$wp_customize->add_control(
				new Onepress_Customize_Repeatable_Control(
					$wp_customize,
					'ccluk_about_boxes',
					array(
						'label' 		=> esc_html__('About content page', 'onesocial'),
						'description'   => '',
						'section'       => 'ccluk_about_content',
						'live_title_id' => 'content_page', // apply for unput text and textarea only
						'title_format'  => esc_html__('[live_title]', 'onesocial'), // [live_title]
						'max_item'      => 3, // Maximum item can add
                        'limited_msg' 	=> wp_kses_post( 'Upgrade to <a target="_blank" href="https://www.famethemes.com/themes/onepress/?utm_source=theme_customizer&utm_medium=text_link&utm_campaign=ccluk_customizer#get-started">CCLUK Plus</a> to be able to add more items and unlock other premium features!', 'onesocial' ),
						//'allow_unlimited' => false, // Maximum item can add

						'fields'    => array(
							'content_page'  => array(
								'title' => esc_html__('Select a page', 'onesocial'),
								'type'  =>'select',
								'options' => $option_pages
							),
							'hide_title'  => array(
								'title' => esc_html__('Hide item title', 'onesocial'),
								'type'  =>'checkbox',
							),
							'enable_link'  => array(
								'title' => esc_html__('Link to single page', 'onesocial'),
								'type'  =>'checkbox',
							),
						),

					)
				)
			);

            // About content source
            $wp_customize->add_setting( 'ccluk_about_content_source',
                array(
                    'sanitize_callback' => 'sanitize_text_field',
                    'default'           => 'content',
                )
            );

            $wp_customize->add_control( 'ccluk_about_content_source',
                array(
                    'label' 		=> esc_html__('Item content source', 'onesocial'),
                    'section' 		=> 'ccluk_about_content',
                    'description'   => '',
                    'type'          => 'select',
                    'choices'       => array(
                        'content' => esc_html__( 'Full Page Content', 'onesocial' ),
                        'excerpt' => esc_html__( 'Page Excerpt', 'onesocial' ),
                    ),
                )
            );


    /*------------------------------------------------------------------------*/
    /*  Section: Features
    /*------------------------------------------------------------------------*/
    $wp_customize->add_panel( 'ccluk_features' ,
        array(
            'priority'        => 150,
            'title'           => esc_html__( 'Section: Features', 'onesocial' ),
            'description'     => '',
            'active_callback' => 'ccluk_showon_frontpage'
        )
    );

    $wp_customize->add_section( 'ccluk_features_settings' ,
        array(
            'priority'    => 3,
            'title'       => esc_html__( 'Section Settings', 'onesocial' ),
            'description' => '',
            'panel'       => 'ccluk_features',
        )
    );

    // Show Content
    $wp_customize->add_setting( 'ccluk_features_disable',
        array(
            'sanitize_callback' => 'ccluk_sanitize_checkbox',
            'default'           => '',
        )
    );
    $wp_customize->add_control( 'ccluk_features_disable',
        array(
            'type'        => 'checkbox',
            'label'       => esc_html__('Hide this section?', 'onesocial'),
            'section'     => 'ccluk_features_settings',
            'description' => esc_html__('Check this box to hide this section.', 'onesocial'),
        )
    );

    // Section ID
    $wp_customize->add_setting( 'ccluk_features_id',
        array(
            'sanitize_callback' => 'ccluk_sanitize_text',
            'default'           => esc_html__('features', 'onesocial'),
        )
    );
    $wp_customize->add_control( 'ccluk_features_id',
        array(
            'label' 		=> esc_html__('Section ID:', 'onesocial'),
            'section' 		=> 'ccluk_features_settings',
            'description'   => esc_html__( 'The section id, we will use this for link anchor.', 'onesocial' )
        )
    );

    // Title
    $wp_customize->add_setting( 'ccluk_features_title',
        array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => esc_html__('Features', 'onesocial'),
        )
    );
    $wp_customize->add_control( 'ccluk_features_title',
        array(
            'label' 		=> esc_html__('Section Title', 'onesocial'),
            'section' 		=> 'ccluk_features_settings',
            'description'   => '',
        )
    );

    // Sub Title
    $wp_customize->add_setting( 'ccluk_features_subtitle',
        array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => esc_html__('Section subtitle', 'onesocial'),
        )
    );
    $wp_customize->add_control( 'ccluk_features_subtitle',
        array(
            'label' 		=> esc_html__('Section Subtitle', 'onesocial'),
            'section' 		=> 'ccluk_features_settings',
            'description'   => '',
        )
    );

    // Description
    $wp_customize->add_setting( 'ccluk_features_desc',
        array(
            'sanitize_callback' => 'ccluk_sanitize_text',
            'default'           => '',
        )
    );
    $wp_customize->add_control( new CCLUK_Editor_Custom_Control(
        $wp_customize,
        'ccluk_features_desc',
        array(
            'label' 		=> esc_html__('Section Description', 'onesocial'),
            'section' 		=> 'ccluk_features_settings',
            'description'   => '',
        )
    ));

    // Features layout
    $wp_customize->add_setting( 'ccluk_features_layout',
        array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '3',
        )
    );

    $wp_customize->add_control( 'ccluk_features_layout',
        array(
            'label' 		=> esc_html__('Features Layout Setting', 'onesocial'),
            'section' 		=> 'ccluk_features_settings',
            'description'   => '',
            'type'          => 'select',
            'choices'       => array(
                '3' => esc_html__( '4 Columns', 'onesocial' ),
                '4' => esc_html__( '3 Columns', 'onesocial' ),
                '6' => esc_html__( '2 Columns', 'onesocial' ),
            ),
        )
    );


    $wp_customize->add_section( 'ccluk_features_content' ,
        array(
            'priority'    => 6,
            'title'       => esc_html__( 'Section Content', 'onesocial' ),
            'description' => '',
            'panel'       => 'ccluk_features',
        )
    );

    // Order & Styling
    $wp_customize->add_setting(
        'ccluk_features_boxes',
        array(
            //'default' => '',
            'sanitize_callback' => 'ccluk_sanitize_repeatable_data_field',
            'transport' => 'refresh', // refresh or postMessage
        ) );

    $wp_customize->add_control(
        new Onepress_Customize_Repeatable_Control(
            $wp_customize,
            'ccluk_features_boxes',
            array(
                'label' 		=> esc_html__('Features content', 'onesocial'),
                'description'   => '',
                'section'       => 'ccluk_features_content',
                'live_title_id' => 'title', // apply for unput text and textarea only
                'title_format'  => esc_html__('[live_title]', 'onesocial'), // [live_title]
                'max_item'      => 4, // Maximum item can add
                'limited_msg' 	=> wp_kses_post( 'Upgrade to <a target="_blank" href="https://www.famethemes.com/themes/onepress/?utm_source=theme_customizer&utm_medium=text_link&utm_campaign=ccluk_customizer#get-started">CCLUK Plus</a> to be able to add more items and unlock other premium features!', 'onesocial' ),
                'fields'    => array(
                    'title'  => array(
                        'title' => esc_html__('Title', 'onesocial'),
                        'type'  =>'text',
                    ),
					'icon_type'  => array(
						'title' => esc_html__('Custom icon', 'onesocial'),
						'desc' => __('Paste your <a target="_blank" href="http://fortawesome.github.io/Font-Awesome/icons/">Font Awesome</a> icon class name here.', 'onesocial'),
						'type'  =>'select',
						'options' => array(
							'icon' => esc_html__('Icon', 'onesocial'),
							'image' => esc_html__('image', 'onesocial'),
						),
					),
                    'icon'  => array(
                        'title' => esc_html__('Icon', 'onesocial'),
                        'desc' => __('Paste your <a target="_blank" href="http://fortawesome.github.io/Font-Awesome/icons/">Font Awesome</a> icon class name here.', 'onesocial'),
                        'type'  =>'text',
						'required' => array( 'icon_type', '=', 'icon' ),
                    ),
					'image'  => array(
						'title' => esc_html__('Image', 'onesocial'),
						'type'  =>'media',
						'required' => array( 'icon_type', '=', 'image' ),
					),
                    'desc'  => array(
                        'title' => esc_html__('Description', 'onesocial'),
                        'type'  =>'editor',
                    ),
                    'link'  => array(
                        'title' => esc_html__('Custom Link', 'onesocial'),
                        'type'  =>'text',
                    ),
                ),

            )
        )
    );

    // About content source
    $wp_customize->add_setting( 'ccluk_about_content_source',
        array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'content',
        )
    );

    $wp_customize->add_control( 'ccluk_about_content_source',
        array(
            'label' 		=> esc_html__('Item content source', 'onesocial'),
            'section' 		=> 'ccluk_about_content',
            'description'   => '',
            'type'          => 'select',
            'choices'       => array(
                'content' => esc_html__( 'Full Page Content', 'onesocial' ),
                'excerpt' => esc_html__( 'Page Excerpt', 'onesocial' ),
            ),
        )
    );


    /*------------------------------------------------------------------------*/
    /*  Section: Services
    /*------------------------------------------------------------------------*/
    $wp_customize->add_panel( 'ccluk_services' ,
		array(
			'priority'        => 170,
			'title'           => esc_html__( 'Section: Services', 'onesocial' ),
			'description'     => '',
			'active_callback' => 'ccluk_showon_frontpage'
		)
	);

	$wp_customize->add_section( 'ccluk_service_settings' ,
		array(
			'priority'    => 3,
			'title'       => esc_html__( 'Section Settings', 'onesocial' ),
			'description' => '',
			'panel'       => 'ccluk_services',
		)
	);

		// Show Content
		$wp_customize->add_setting( 'ccluk_services_disable',
			array(
				'sanitize_callback' => 'ccluk_sanitize_checkbox',
				'default'           => '',
			)
		);
		$wp_customize->add_control( 'ccluk_services_disable',
			array(
				'type'        => 'checkbox',
				'label'       => esc_html__('Hide this section?', 'onesocial'),
				'section'     => 'ccluk_service_settings',
				'description' => esc_html__('Check this box to hide this section.', 'onesocial'),
			)
		);

		// Section ID
		$wp_customize->add_setting( 'ccluk_services_id',
			array(
				'sanitize_callback' => 'ccluk_sanitize_text',
				'default'           => esc_html__('services', 'onesocial'),
			)
		);
		$wp_customize->add_control( 'ccluk_services_id',
			array(
				'label'     => esc_html__('Section ID:', 'onesocial'),
				'section' 		=> 'ccluk_service_settings',
				'description'   => 'The section id, we will use this for link anchor.'
			)
		);

		// Title
		$wp_customize->add_setting( 'ccluk_services_title',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => esc_html__('Our Services', 'onesocial'),
			)
		);
		$wp_customize->add_control( 'ccluk_services_title',
			array(
				'label'     => esc_html__('Section Title', 'onesocial'),
				'section' 		=> 'ccluk_service_settings',
				'description'   => '',
			)
		);

		// Sub Title
		$wp_customize->add_setting( 'ccluk_services_subtitle',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => esc_html__('Section subtitle', 'onesocial'),
			)
		);
		$wp_customize->add_control( 'ccluk_services_subtitle',
			array(
				'label'     => esc_html__('Section Subtitle', 'onesocial'),
				'section' 		=> 'ccluk_service_settings',
				'description'   => '',
			)
		);

        // Description
        $wp_customize->add_setting( 'ccluk_services_desc',
            array(
                'sanitize_callback' => 'ccluk_sanitize_text',
                'default'           => '',
            )
        );
        $wp_customize->add_control( new CCLUK_Editor_Custom_Control(
            $wp_customize,
            'ccluk_services_desc',
            array(
                'label' 		=> esc_html__('Section Description', 'onesocial'),
                'section' 		=> 'ccluk_service_settings',
                'description'   => '',
            )
        ));


        // Services layout
        $wp_customize->add_setting( 'ccluk_service_layout',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '6',
            )
        );

        $wp_customize->add_control( 'ccluk_service_layout',
            array(
                'label' 		=> esc_html__('Services Layout Setting', 'onesocial'),
                'section' 		=> 'ccluk_service_settings',
                'description'   => '',
                'type'          => 'select',
                'choices'       => array(
                    '3' => esc_html__( '4 Columns', 'onesocial' ),
                    '4' => esc_html__( '3 Columns', 'onesocial' ),
                    '6' => esc_html__( '2 Columns', 'onesocial' ),
                    '12' => esc_html__( '1 Column', 'onesocial' ),
                ),
            )
        );


	$wp_customize->add_section( 'ccluk_service_content' ,
		array(
			'priority'    => 6,
			'title'       => esc_html__( 'Section Content', 'onesocial' ),
			'description' => '',
			'panel'       => 'ccluk_services',
		)
	);

		// Section service content.
		$wp_customize->add_setting(
			'ccluk_services',
			array(
				'sanitize_callback' => 'ccluk_sanitize_repeatable_data_field',
				'transport' => 'refresh', // refresh or postMessage
			) );


		$wp_customize->add_control(
			new Onepress_Customize_Repeatable_Control(
				$wp_customize,
				'ccluk_services',
				array(
					'label'     	=> esc_html__('Service content', 'onesocial'),
					'description'   => '',
					'section'       => 'ccluk_service_content',
					'live_title_id' => 'content_page', // apply for unput text and textarea only
					'title_format'  => esc_html__('[live_title]', 'onesocial'), // [live_title]
					'max_item'      => 4, // Maximum item can add
                    'limited_msg' 	=> wp_kses_post( 'Upgrade to <a target="_blank" href="https://www.famethemes.com/themes/onepress/?utm_source=theme_customizer&utm_medium=text_link&utm_campaign=ccluk_customizer#get-started">CCLUK Plus</a> to be able to add more items and unlock other premium features!', 'onesocial' ),

					'fields'    => array(
						'icon_type'  => array(
							'title' => esc_html__('Custom icon', 'onesocial'),
							'desc' => __('Paste your <a target="_blank" href="http://fortawesome.github.io/Font-Awesome/icons/">Font Awesome</a> icon class name here.', 'onesocial'),
							'type'  =>'select',
							'options' => array(
								'icon' => esc_html__('Icon', 'onesocial'),
								'image' => esc_html__('image', 'onesocial'),
							),
						),
						'icon'  => array(
							'title' => esc_html__('Icon', 'onesocial'),
							'desc' => __('Paste your <a target="_blank" href="http://fortawesome.github.io/Font-Awesome/icons/">Font Awesome</a> icon class name here.', 'onesocial'),
							'type'  =>'text',
							'required' => array( 'icon_type', '=', 'icon' ),
						),
						'image'  => array(
							'title' => esc_html__('Image', 'onesocial'),
							'type'  =>'media',
							'required' => array( 'icon_type', '=', 'image' ),
						),

						'content_page'  => array(
							'title' => esc_html__('Select a page', 'onesocial'),
							'type'  =>'select',
							'options' => $option_pages
						),
						'enable_link'  => array(
							'title' => esc_html__('Link to single page', 'onesocial'),
							'type'  =>'checkbox',
						),
					),

				)
			)
		);

	/*------------------------------------------------------------------------*/
    /*  Section: Counter
    /*------------------------------------------------------------------------*/
	$wp_customize->add_panel( 'ccluk_counter' ,
		array(
			'priority'        => 210,
			'title'           => esc_html__( 'Section: Counter', 'onesocial' ),
			'description'     => '',
			'active_callback' => 'ccluk_showon_frontpage'
		)
	);

	$wp_customize->add_section( 'ccluk_counter_settings' ,
		array(
			'priority'    => 3,
			'title'       => esc_html__( 'Section Settings', 'onesocial' ),
			'description' => '',
			'panel'       => 'ccluk_counter',
		)
	);
		// Show Content
		$wp_customize->add_setting( 'ccluk_counter_disable',
			array(
				'sanitize_callback' => 'ccluk_sanitize_checkbox',
				'default'           => '',
			)
		);
		$wp_customize->add_control( 'ccluk_counter_disable',
			array(
				'type'        => 'checkbox',
				'label'       => esc_html__('Hide this section?', 'onesocial'),
				'section'     => 'ccluk_counter_settings',
				'description' => esc_html__('Check this box to hide this section.', 'onesocial'),
			)
		);

		// Section ID
		$wp_customize->add_setting( 'ccluk_counter_id',
			array(
				'sanitize_callback' => 'ccluk_sanitize_text',
				'default'           => esc_html__('counter', 'onesocial'),
			)
		);
		$wp_customize->add_control( 'ccluk_counter_id',
			array(
				'label'     	=> esc_html__('Section ID:', 'onesocial'),
				'section' 		=> 'ccluk_counter_settings',
				'description'   => esc_html__( 'The section id, we will use this for link anchor.', 'onesocial' )
			)
		);

		// Title
		$wp_customize->add_setting( 'ccluk_counter_title',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => esc_html__('Our Numbers', 'onesocial'),
			)
		);
		$wp_customize->add_control( 'ccluk_counter_title',
			array(
				'label'     	=> esc_html__('Section Title', 'onesocial'),
				'section' 		=> 'ccluk_counter_settings',
				'description'   => '',
			)
		);

		// Sub Title
		$wp_customize->add_setting( 'ccluk_counter_subtitle',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => esc_html__('Section subtitle', 'onesocial'),
			)
		);
		$wp_customize->add_control( 'ccluk_counter_subtitle',
			array(
				'label'     	=> esc_html__('Section Subtitle', 'onesocial'),
				'section' 		=> 'ccluk_counter_settings',
				'description'   => '',
			)
		);

        // Description
        $wp_customize->add_setting( 'ccluk_counter_desc',
            array(
                'sanitize_callback' => 'ccluk_sanitize_text',
                'default'           => '',
            )
        );
        $wp_customize->add_control( new CCLUK_Editor_Custom_Control(
            $wp_customize,
            'ccluk_counter_desc',
            array(
                'label' 		=> esc_html__('Section Description', 'onesocial'),
                'section' 		=> 'ccluk_counter_settings',
                'description'   => '',
            )
        ));

	$wp_customize->add_section( 'ccluk_counter_content' ,
		array(
			'priority'    => 6,
			'title'       => esc_html__( 'Section Content', 'onesocial' ),
			'description' => '',
			'panel'       => 'ccluk_counter',
		)
	);

	// Order & Styling
	$wp_customize->add_setting(
		'ccluk_counter_boxes',
		array(
			'sanitize_callback' => 'ccluk_sanitize_repeatable_data_field',
			'transport' => 'refresh', // refresh or postMessage
		) );


		$wp_customize->add_control(
			new Onepress_Customize_Repeatable_Control(
				$wp_customize,
				'ccluk_counter_boxes',
				array(
					'label'     	=> esc_html__('Counter content', 'onesocial'),
					'description'   => '',
					'section'       => 'ccluk_counter_content',
					'live_title_id' => 'title', // apply for unput text and textarea only
					'title_format'  => esc_html__('[live_title]', 'onesocial'), // [live_title]
					'max_item'      => 4, // Maximum item can add
                    'limited_msg' 	=> wp_kses_post( 'Upgrade to <a target="_blank" href="https://www.famethemes.com/themes/onepress/?utm_source=theme_customizer&utm_medium=text_link&utm_campaign=ccluk_customizer#get-started">CCLUK Plus</a> to be able to add more items and unlock other premium features!', 'onesocial' ),
                    'fields'    => array(
						'title' => array(
							'title' => esc_html__('Title', 'onesocial'),
							'type'  =>'text',
							'desc'  => '',
							'default' => esc_html__( 'Your counter label', 'onesocial' ),
						),
						'number' => array(
							'title' => esc_html__('Number', 'onesocial'),
							'type'  =>'text',
							'default' => 99,
						),
						'unit_before'  => array(
							'title' => esc_html__('Before number', 'onesocial'),
							'type'  =>'text',
							'default' => '',
						),
						'unit_after'  => array(
							'title' => esc_html__('After number', 'onesocial'),
							'type'  =>'text',
							'default' => '',
						),
					),

				)
			)
		);

	/*------------------------------------------------------------------------*/
    /*  Section: Team
    /*------------------------------------------------------------------------*/
    $wp_customize->add_panel( 'ccluk_team' ,
		array(
			'priority'        => 250,
			'title'           => esc_html__( 'Section: Team', 'onesocial' ),
			'description'     => '',
			'active_callback' => 'ccluk_showon_frontpage'
		)
	);

	$wp_customize->add_section( 'ccluk_team_settings' ,
		array(
			'priority'    => 3,
			'title'       => esc_html__( 'Section Settings', 'onesocial' ),
			'description' => '',
			'panel'       => 'ccluk_team',
		)
	);

		// Show Content
		$wp_customize->add_setting( 'ccluk_team_disable',
			array(
				'sanitize_callback' => 'ccluk_sanitize_checkbox',
				'default'           => '',
			)
		);
		$wp_customize->add_control( 'ccluk_team_disable',
			array(
				'type'        => 'checkbox',
				'label'       => esc_html__('Hide this section?', 'onesocial'),
				'section'     => 'ccluk_team_settings',
				'description' => esc_html__('Check this box to hide this section.', 'onesocial'),
			)
		);
		// Section ID
		$wp_customize->add_setting( 'ccluk_team_id',
			array(
				'sanitize_callback' => 'ccluk_sanitize_text',
				'default'           => esc_html__('team', 'onesocial'),
			)
		);
		$wp_customize->add_control( 'ccluk_team_id',
			array(
				'label'     	=> esc_html__('Section ID:', 'onesocial'),
				'section' 		=> 'ccluk_team_settings',
				'description'   => 'The section id, we will use this for link anchor.'
			)
		);

		// Title
		$wp_customize->add_setting( 'ccluk_team_title',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => esc_html__('Our Team', 'onesocial'),
			)
		);
		$wp_customize->add_control( 'ccluk_team_title',
			array(
				'label'    		=> esc_html__('Section Title', 'onesocial'),
				'section' 		=> 'ccluk_team_settings',
				'description'   => '',
			)
		);

		// Sub Title
		$wp_customize->add_setting( 'ccluk_team_subtitle',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => esc_html__('Section subtitle', 'onesocial'),
			)
		);
		$wp_customize->add_control( 'ccluk_team_subtitle',
			array(
				'label'     => esc_html__('Section Subtitle', 'onesocial'),
				'section' 		=> 'ccluk_team_settings',
				'description'   => '',
			)
		);

        // Description
        $wp_customize->add_setting( 'ccluk_team_desc',
            array(
                'sanitize_callback' => 'ccluk_sanitize_text',
                'default'           => '',
            )
        );
        $wp_customize->add_control( new CCLUK_Editor_Custom_Control(
            $wp_customize,
            'ccluk_team_desc',
            array(
                'label' 		=> esc_html__('Section Description', 'onesocial'),
                'section' 		=> 'ccluk_team_settings',
                'description'   => '',
            )
        ));

        // Team layout
        $wp_customize->add_setting( 'ccluk_team_layout',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '3',
            )
        );

        $wp_customize->add_control( 'ccluk_team_layout',
            array(
                'label' 		=> esc_html__('Team Layout Setting', 'onesocial'),
                'section' 		=> 'ccluk_team_settings',
                'description'   => '',
                'type'          => 'select',
                'choices'       => array(
					'3' => esc_html__( '4 Columns', 'onesocial' ),
					'4' => esc_html__( '3 Columns', 'onesocial' ),
					'6' => esc_html__( '2 Columns', 'onesocial' ),
                ),
            )
        );

	$wp_customize->add_section( 'ccluk_team_content' ,
		array(
			'priority'    => 6,
			'title'       => esc_html__( 'Section Content', 'onesocial' ),
			'description' => '',
			'panel'       => 'ccluk_team',
		)
	);

		// Team member settings
		$wp_customize->add_setting(
			'ccluk_team_members',
			array(
				'sanitize_callback' => 'ccluk_sanitize_repeatable_data_field',
				'transport' => 'refresh', // refresh or postMessage
			) );


		$wp_customize->add_control(
			new Onepress_Customize_Repeatable_Control(
				$wp_customize,
				'ccluk_team_members',
				array(
					'label'     => esc_html__('Team members', 'onesocial'),
					'description'   => '',
					'section'       => 'ccluk_team_content',
					//'live_title_id' => 'user_id', // apply for unput text and textarea only
					'title_format'  => esc_html__( '[live_title]', 'onesocial'), // [live_title]
					'max_item'      => 4, // Maximum item can add
                    'limited_msg' 	=> wp_kses_post( 'Upgrade to <a target="_blank" href="https://www.famethemes.com/themes/onepress/?utm_source=theme_customizer&utm_medium=text_link&utm_campaign=ccluk_customizer#get-started">CCLUK Plus</a> to be able to add more items and unlock other premium features!', 'onesocial' ),
                    'fields'    => array(
						'user_id' => array(
							'title' => esc_html__('User media', 'onesocial'),
							'type'  =>'media',
							'desc'  => '',
						),
                        'link' => array(
                            'title' => esc_html__('Custom Link', 'onesocial'),
                            'type'  =>'text',
                            'desc'  => '',
                        ),
					),

				)
			)
		);



	/*------------------------------------------------------------------------*/
    /*  Section: News
    /*------------------------------------------------------------------------*/
    $wp_customize->add_panel( 'ccluk_news' ,
		array(
			'priority'        => 260,
			'title'           => esc_html__( 'Section: News', 'onesocial' ),
			'description'     => '',
			'active_callback' => 'ccluk_showon_frontpage'
		)
	);

	$wp_customize->add_section( 'ccluk_news_settings' ,
		array(
			'priority'    => 3,
			'title'       => esc_html__( 'Section Settings', 'onesocial' ),
			'description' => '',
			'panel'       => 'ccluk_news',
		)
	);

		// Show Content
		$wp_customize->add_setting( 'ccluk_news_disable',
			array(
				'sanitize_callback' => 'ccluk_sanitize_checkbox',
				'default'           => '',
			)
		);
		$wp_customize->add_control( 'ccluk_news_disable',
			array(
				'type'        => 'checkbox',
				'label'       => esc_html__('Hide this section?', 'onesocial'),
				'section'     => 'ccluk_news_settings',
				'description' => esc_html__('Check this box to hide this section.', 'onesocial'),
			)
		);

		// Section ID
		$wp_customize->add_setting( 'ccluk_news_id',
			array(
				'sanitize_callback' => 'ccluk_sanitize_text',
				'default'           => esc_html__('news', 'onesocial'),
			)
		);
		$wp_customize->add_control( 'ccluk_news_id',
			array(
				'label'     => esc_html__('Section ID:', 'onesocial'),
				'section' 		=> 'ccluk_news_settings',
				'description'   => esc_html__( 'The section id, we will use this for link anchor.', 'onesocial' )
			)
		);

		// Title
		$wp_customize->add_setting( 'ccluk_news_title',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => esc_html__('Latest News', 'onesocial'),
			)
		);
		$wp_customize->add_control( 'ccluk_news_title',
			array(
				'label'     => esc_html__('Section Title', 'onesocial'),
				'section' 		=> 'ccluk_news_settings',
				'description'   => '',
			)
		);

		// Sub Title
		$wp_customize->add_setting( 'ccluk_news_subtitle',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => esc_html__('Section subtitle', 'onesocial'),
			)
		);
		$wp_customize->add_control( 'ccluk_news_subtitle',
			array(
				'label'     => esc_html__('Section Subtitle', 'onesocial'),
				'section' 		=> 'ccluk_news_settings',
				'description'   => '',
			)
		);

        // Description
        $wp_customize->add_setting( 'ccluk_news_desc',
            array(
                'sanitize_callback' => 'ccluk_sanitize_text',
                'default'           => '',
            )
        );
        $wp_customize->add_control( new CCLUK_Editor_Custom_Control(
            $wp_customize,
            'ccluk_news_desc',
            array(
                'label' 		=> esc_html__('Section Description', 'onesocial'),
                'section' 		=> 'ccluk_news_settings',
                'description'   => '',
            )
        ));

		// hr
		$wp_customize->add_setting( 'ccluk_news_settings_hr',
			array(
				'sanitize_callback' => 'ccluk_sanitize_text',
			)
		);
		$wp_customize->add_control( new CCLUK_Misc_Control( $wp_customize, 'ccluk_news_settings_hr',
			array(
				'section'     => 'ccluk_news_settings',
				'type'        => 'hr'
			)
		));

		// Number of post to show.
		$wp_customize->add_setting( 'ccluk_news_number',
			array(
				'sanitize_callback' => 'ccluk_sanitize_number',
				'default'           => '3',
			)
		);
		$wp_customize->add_control( 'ccluk_news_number',
			array(
				'label'     	=> esc_html__('Number of post to show', 'onesocial'),
				'section' 		=> 'ccluk_news_settings',
				'description'   => '',
			)
		);

		// Blog Button
		$wp_customize->add_setting( 'ccluk_news_more_link',
			array(
				'sanitize_callback' => 'esc_url',
				'default'           => '#',
			)
		);
		$wp_customize->add_control( 'ccluk_news_more_link',
			array(
				'label'       => esc_html__('More News button link', 'onesocial'),
				'section'     => 'ccluk_news_settings',
				'description' => esc_html__(  'It should be your blog page link.', 'onesocial' )
			)
		);
		$wp_customize->add_setting( 'ccluk_news_more_text',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => esc_html__('Read Our Blog', 'onesocial'),
			)
		);
		$wp_customize->add_control( 'ccluk_news_more_text',
			array(
				'label'     	=> esc_html__('More News Button Text', 'onesocial'),
				'section' 		=> 'ccluk_news_settings',
				'description'   => '',
			)
		);

	/*------------------------------------------------------------------------*/
    /*  Section: Contact
    /*------------------------------------------------------------------------*/
    $wp_customize->add_panel( 'ccluk_contact' ,
		array(
			'priority'        => 270,
			'title'           => esc_html__( 'Section: Contact', 'onesocial' ),
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
	return is_page_template( 'template-frontpage.php' );
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
    wp_enqueue_script( 'ccluk_customizer_liveview', get_template_directory_uri() . '/assets/js/customizer-liveview.js', array( 'customize-preview', 'customize-selective-refresh' ), false, true );
}
add_action( 'customize_preview_init', 'ccluk_customize_preview_js', 65 );



add_action( 'customize_controls_enqueue_scripts', 'opneress_customize_js_settings' );
function opneress_customize_js_settings(){
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
