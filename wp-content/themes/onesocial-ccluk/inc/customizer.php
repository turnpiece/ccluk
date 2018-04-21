<?php
/**
 * CCLUK Theme Customizer.
 *
 * @package CCLUK
 */

class CCLUK_Customizer {

	const SLUG = 'ccluk';

	private $customize;

	function __construct() {

		/**
		 * Selective refresh
		 */
		require get_stylesheet_directory() . '/inc/customizer-selective-refresh.php';

		add_action( 'customize_preview_init', array( $this, 'preview_js' ), 65 );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'js_settings' ) );
		add_action( 'customize_register', array( $this, 'register' ) );
	}

	/**
	 * Add postMessage support for site title and description for the Theme Customizer.
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	function register( $wp_customize ) {

		// store customize object
		$this->customize = $wp_customize;

		// Load custom controls.
		require get_stylesheet_directory() . '/inc/customizer-controls.php';

		/**
		 * Hook to add other customize
		 */
		do_action( self::SLUG.'_customize_before_register', $wp_customize );

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
	    /*  Homepage: Join
	    /*------------------------------------------------------------------------*/

		    $wp_customize->add_panel( self::SLUG.'_homepage_join' ,
				array(
					'priority'        => 160,
					'title'           => esc_html__( 'Homepage: Join', 'onesocial' ),
					'description'     => esc_html__( 'The join CCL UK section on the homepage', 'onesocial' ),
					'active_callback' => array( $this, 'showon_frontpage' )
				)
			);

		    $this->standard_settings( self::SLUG.'_homepage_join', 'join' );

		    /*
			$wp_customize->add_section( self::SLUG.'_homepage_join_settings' ,
				array(
					'priority'    => 3,
					'title'       => esc_html__( 'Section Settings', 'onesocial' ),
					'description' => '',
					'panel'       => self::SLUG.'_homepage_join',
				)
			);

			// Show Content
			$wp_customize->add_setting( self::SLUG.'_homepage_join_disable',
				array(
					'sanitize_callback' => self::SLUG.'_sanitize_checkbox',
					'default'           => '',
				)
			);

			$wp_customize->add_control( self::SLUG.'_homepage_join_disable',
				array(
					'type'        => 'checkbox',
					'label'       => esc_html__('Hide this section?', 'onesocial'),
					'section'     => self::SLUG.'_homepage_join_settings',
					'description' => esc_html__('Check this box to hide this section.', 'onesocial'),
				)
			);

			// Section ID
			$wp_customize->add_setting( self::SLUG.'_homepage_join_id',
				array(
					'sanitize_callback' => self::SLUG.'_sanitize_text',
					'default'           => esc_html__('about', 'onesocial'),
				)
			);

			$wp_customize->add_control( self::SLUG.'_homepage_join_id',
				array(
					'label' 		=> esc_html__('Section ID:', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_join_settings',
					'description'   => esc_html__( 'The section id, we will use this for link anchor.', 'onesocial' )
				)
			);
			*/
			// Title
			$wp_customize->add_setting( self::SLUG.'_homepage_join_title',
				array(
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => sprintf( __('Join %s', 'onesocial'), get_bloginfo('name') ),
				)
			);

			$wp_customize->add_control( self::SLUG.'_homepage_join_title',
				array(
					'label' 		=> esc_html__('Section Title', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_join_settings',
					'description'   => '',
				)
			);

			// Source page settings
			$wp_customize->add_setting( self::SLUG.'_homepage_join_source_page',
				array(
					'sanitize_callback' => array( $this, 'sanitize_number' ),
					'default'           => '',
				)
			);
			$wp_customize->add_control( self::SLUG.'_homepage_join_source_page',
				array(
					'label'     	=> esc_html__('Title Link', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_join_settings',
					'type'          => 'select',
					'priority'      => 10,
					'choices'       => $option_pages,
					'description'   => esc_html__('Select a page the title will link to.', 'onesocial'),
				)
			);

			$wp_customize->add_section( self::SLUG.'_homepage_join_content' ,
				array(
					'priority'    => 6,
					'title'       => esc_html__( 'Section Content', 'onesocial' ),
					'description' => '',
					'panel'       => self::SLUG.'_homepage_join',
				)
			);

			$wp_customize->add_setting( self::SLUG.'_homepage_join_intro',
				array(
					'sanitize_callback' => self::SLUG.'_sanitize_text',
					'default'           => '',
				)
			);

			$wp_customize->add_control( new CCLUK_Editor_Custom_Control(
				$wp_customize,
				self::SLUG.'_homepage_join_intro',
				array(
					'label' 		=> sprintf( esc_html__('Introduction', 'onesocial'), $box ),
					'section' 		=> self::SLUG.'_homepage_join_content',
					'description'   => '',
				)
			));


		/*------------------------------------------------------------------------*/
	    /*  Homepage: About
	    /*------------------------------------------------------------------------*/

		    $wp_customize->add_panel( self::SLUG.'_homepage_about' ,
				array(
					'priority'        => 160,
					'title'           => esc_html__( 'Homepage: About', 'onesocial' ),
					'description'     => '',
					'active_callback' => array( $this, 'showon_frontpage' )
				)
			);

		    $this->standard_settings( self::SLUG.'_homepage_about', 'about' );
		    /*
			$wp_customize->add_section( self::SLUG.'_homepage_about_settings' ,
				array(
					'priority'    => 3,
					'title'       => esc_html__( 'Section Settings', 'onesocial' ),
					'description' => '',
					'panel'       => self::SLUG.'_homepage_about',
				)
			);

			// Show Content
			$wp_customize->add_setting( self::SLUG.'_homepage_about_disable',
				array(
					'sanitize_callback' => self::SLUG.'_sanitize_checkbox',
					'default'           => '',
				)
			);

			$wp_customize->add_control( self::SLUG.'_homepage_about_disable',
				array(
					'type'        => 'checkbox',
					'label'       => esc_html__('Hide this section?', 'onesocial'),
					'section'     => self::SLUG.'_homepage_about_settings',
					'description' => esc_html__('Check this box to hide this section.', 'onesocial'),
				)
			);

			// Section ID
			$wp_customize->add_setting( self::SLUG.'_homepage_about_id',
				array(
					'sanitize_callback' => self::SLUG.'_sanitize_text',
					'default'           => esc_html__('about', 'onesocial'),
				)
			);

			$wp_customize->add_control( self::SLUG.'_homepage_about_id',
				array(
					'label' 		=> esc_html__('Section ID:', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_about_settings',
					'description'   => esc_html__( 'The section id, we will use this for link anchor.', 'onesocial' )
				)
			);
			*/
			// Title
			$wp_customize->add_setting( self::SLUG.'_homepage_about_title',
				array(
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => esc_html__('About Us', 'onesocial'),
				)
			);

			$wp_customize->add_control( self::SLUG.'_homepage_about_title',
				array(
					'label' 		=> esc_html__('Section Title', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_about_settings',
					'description'   => '',
				)
			);

			// Source page settings
			$wp_customize->add_setting( self::SLUG.'_homepage_about_source_page',
				array(
					'sanitize_callback' => array( $this, 'sanitize_number' ),
					'default'           => '',
				)
			);
			$wp_customize->add_control( self::SLUG.'_homepage_about_source_page',
				array(
					'label'     	=> esc_html__('Title Link', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_about_settings',
					'type'          => 'select',
					'priority'      => 10,
					'choices'       => $option_pages,
					'description'   => esc_html__('Select a page the title will link to.', 'onesocial'),
				)
			);

			$wp_customize->add_section( self::SLUG.'_homepage_about_content' ,
				array(
					'priority'    => 6,
					'title'       => esc_html__( 'Section Content', 'onesocial' ),
					'description' => '',
					'panel'       => self::SLUG.'_homepage_about',
				)
			);

			$wp_customize->add_setting( self::SLUG.'_homepage_about_intro',
				array(
					'sanitize_callback' => self::SLUG.'_sanitize_text',
					'default'           => '',
				)
			);

			$wp_customize->add_control( new CCLUK_Editor_Custom_Control(
				$wp_customize,
				self::SLUG.'_homepage_about_intro',
				array(
					'label' 		=> sprintf( esc_html__('Introduction', 'onesocial'), $box ),
					'section' 		=> self::SLUG.'_homepage_about_content',
					'description'   => '',
				)
			));

			// Boxes
			for ( $box = 1; $box <= 2; $box++ ) :

			$wp_customize->add_setting( self::SLUG.'_homepage_about_box_'.$box,
				array(
					'sanitize_callback' => self::SLUG.'_sanitize_text',
					'default'           => '',
				)
			);

			$wp_customize->add_control( new CCLUK_Editor_Custom_Control(
				$wp_customize,
				self::SLUG.'_homepage_about_box_'.$box,
				array(
					'label' 		=> sprintf( esc_html__('Box %d content', 'onesocial'), $box ),
					'section' 		=> self::SLUG.'_homepage_about_content',
					'description'   => '',
				)
			));

			endfor;

			$wp_customize->add_setting( self::SLUG.'_homepage_about_footer',
				array(
					'sanitize_callback' => self::SLUG.'_sanitize_text',
					'default'           => '',
				)
			);

			$wp_customize->add_control( new CCLUK_Editor_Custom_Control(
				$wp_customize,
				self::SLUG.'_homepage_about_footer',
				array(
					'label' 		=> sprintf( esc_html__('Footer', 'onesocial'), $box ),
					'section' 		=> self::SLUG.'_homepage_about_content',
					'description'   => __( 'Appears below the two boxes', 'onesocial' )
				)
			));


		/*------------------------------------------------------------------------*/
	    /*  Home page: Contact
	    /*------------------------------------------------------------------------*/


		    $wp_customize->add_panel( self::SLUG.'_homepage_contact' ,
				array(
					'priority'        => 270,
					'title'           => esc_html__( 'Home page: Contact', 'onesocial' ),
					'description'     => '',
					'active_callback' => array( $this, 'showon_frontpage' )
				)
			);

			$this->standard_settings( self::SLUG.'_homepage_contact', 'contact' );
			/*
			$wp_customize->add_section( self::SLUG.'_homepage_contact_settings' ,
				array(
					'priority'    => 3,
					'title'       => esc_html__( 'Section Settings', 'onesocial' ),
					'description' => '',
					'panel'       => self::SLUG.'_homepage_contact',
				)
			);
			/*
			// Show Content
			$wp_customize->add_setting( self::SLUG.'_homepage_contact_disable',
				array(
					'sanitize_callback' => self::SLUG.'_sanitize_checkbox',
					'default'           => '',
				)
			);

			$wp_customize->add_control( self::SLUG.'_homepage_contact_disable',
				array(
					'type'        => 'checkbox',
					'label'       => esc_html__('Hide this section?', 'onesocial'),
					'section'     => self::SLUG.'_homepage_contact_settings',
					'description' => esc_html__('Check this box to hide this section.', 'onesocial'),
				)
			);

			// Section ID
			$wp_customize->add_setting( self::SLUG.'_homepage_contact_id',
				array(
					'sanitize_callback' => self::SLUG.'_sanitize_text',
					'default'           => esc_html__('contact', 'onesocial'),
				)
			);

			$wp_customize->add_control( self::SLUG.'_homepage_contact_id',
				array(
					'label'     => esc_html__('Section ID:', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_contact_settings',
					'description'   => esc_html__( 'The section id, we will use this for link anchor.', 'onesocial' )
				)
			);
			*/
			// Title
			$wp_customize->add_setting( self::SLUG.'_homepage_contact_title',
				array(
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => esc_html__('Get in touch', 'onesocial'),
				)
			);

			$wp_customize->add_control( self::SLUG.'_homepage_contact_title',
				array(
					'label'     => esc_html__('Section Title', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_contact_settings',
					'description'   => '',
				)
			);

			// Sub Title
			$wp_customize->add_setting( self::SLUG.'_homepage_contact_subtitle',
				array(
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => esc_html__('Section subtitle', 'onesocial'),
				)
			);

			$wp_customize->add_control( self::SLUG.'_homepage_contact_subtitle',
				array(
					'label'     => esc_html__('Section Subtitle', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_contact_settings',
					'description'   => '',
				)
			);

	        // Description
	        $wp_customize->add_setting( self::SLUG.'_homepage_contact_desc',
	            array(
	                'sanitize_callback' => self::SLUG.'_sanitize_text',
	                'default'           => '',
	            )
	        );

	        $wp_customize->add_control( new CCLUK_Editor_Custom_Control(
	            $wp_customize,
	            self::SLUG.'_homepage_contact_desc',
	            array(
	                'label' 		=> esc_html__('Section Description', 'onesocial'),
	                'section' 		=> self::SLUG.'_homepage_contact_settings',
	                'description'   => '',
	            )
	        ));

			$wp_customize->add_section( self::SLUG.'_homepage_contact_content' ,
				array(
					'priority'    => 6,
					'title'       => esc_html__( 'Section Content', 'onesocial' ),
					'description' => '',
					'panel'       => self::SLUG.'_homepage_contact',
				)
			);

			// Contact form 7 guide.
			$wp_customize->add_setting( self::SLUG.'_homepage_contact_cf7_guide',
				array(
					'sanitize_callback' => self::SLUG.'_sanitize_text'
				)
			);

			$wp_customize->add_control( new CCLUK_Misc_Control( $wp_customize, self::SLUG.'_homepage_contact_cf7_guide',
				array(
					'section'     => self::SLUG.'_homepage_contact_content',
					'type'        => 'custom_message',
					'description' => wp_kses_post( 'In order to display contact form please install <a target="_blank" href="https://wordpress.org/plugins/contact-form-7/">Contact Form 7</a> plugin and then copy the contact form shortcode and paste it here, the shortcode will be like this <code>[contact-form-7 id="xxxx" title="Example Contact Form"]</code>', 'onesocial' )
				)
			));

			// Contact Form 7 Shortcode
			$wp_customize->add_setting( self::SLUG.'_homepage_contact_cf7',
				array(
					'sanitize_callback' => self::SLUG.'_sanitize_text',
					'default'           => '',
				)
			);

			$wp_customize->add_control( self::SLUG.'_homepage_contact_cf7',
				array(
					'label'     	=> esc_html__('Contact Form 7 Shortcode.', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_contact_content',
					'description'   => '',
				)
			);

			// Show CF7
			$wp_customize->add_setting( self::SLUG.'_homepage_contact_cf7_disable',
				array(
					'sanitize_callback' => self::SLUG.'_sanitize_checkbox',
					'default'           => '',
				)
			);

			$wp_customize->add_control( self::SLUG.'_homepage_contact_cf7_disable',
				array(
					'type'        => 'checkbox',
					'label'       => esc_html__('Hide contact form completely.', 'onesocial'),
					'section'     => self::SLUG.'_homepage_contact_content',
					'description' => esc_html__('Check this box to hide contact form.', 'onesocial'),
				)
			);

			// Contact Text
			$wp_customize->add_setting( self::SLUG.'_homepage_contact_text',
				array(
					'sanitize_callback' => self::SLUG.'_sanitize_text',
					'default'           => '',
				)
			);

			$wp_customize->add_control( new CCLUK_Editor_Custom_Control(
				$wp_customize,
				self::SLUG.'_homepage_contact_text',
				array(
					'label'     	=> esc_html__('Contact Text', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_contact_content',
					'description'   => '',
				)
			));

			// hr
			$wp_customize->add_setting( self::SLUG.'_homepage_contact_text_hr', array( 'sanitize_callback' => self::SLUG.'_sanitize_text' ) );
			$wp_customize->add_control( new CCLUK_Misc_Control( $wp_customize, self::SLUG.'_homepage_contact_text_hr',
				array(
					'section'     => self::SLUG.'_homepage_contact_content',
					'type'        => 'hr'
				)
			));

			// Address Box
			$wp_customize->add_setting( self::SLUG.'_homepage_contact_address_title',
				array(
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => '',
				)
			);

			$wp_customize->add_control( self::SLUG.'_homepage_contact_address_title',
				array(
					'label'     	=> esc_html__('Contact Box Title', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_contact_content',
					'description'   => '',
				)
			);

			// Contact Text
			$wp_customize->add_setting( self::SLUG.'_homepage_contact_address',
				array(
					'sanitize_callback' => self::SLUG.'_sanitize_text',
					'default'           => '',
				)
			);

			$wp_customize->add_control( self::SLUG.'_homepage_contact_address',
				array(
					'label'     => esc_html__('Address', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_contact_content',
					'description'   => '',
				)
			);

			// Contact Phone
			$wp_customize->add_setting( self::SLUG.'_homepage_contact_phone',
				array(
					'sanitize_callback' => self::SLUG.'_sanitize_text',
					'default'           => '',
				)
			);

			$wp_customize->add_control( self::SLUG.'_homepage_contact_phone',
				array(
					'label'     	=> esc_html__('Phone', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_contact_content',
					'description'   => '',
				)
			);

			// Contact Email
			$wp_customize->add_setting( self::SLUG.'_homepage_contact_email',
				array(
					'sanitize_callback' => 'sanitize_email',
					'default'           => '',
				)
			);

			$wp_customize->add_control( self::SLUG.'_homepage_contact_email',
				array(
					'label'     	=> esc_html__('Email', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_contact_content',
					'description'   => '',
				)
			);

			// Contact Fax
			$wp_customize->add_setting( self::SLUG.'_homepage_contact_fax',
				array(
					'sanitize_callback' => self::SLUG.'_sanitize_text',
					'default'           => '',
				)
			);

			$wp_customize->add_control( self::SLUG.'_homepage_contact_fax',
				array(
					'label'     	=> esc_html__('Fax', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_contact_content',
					'description'   => '',
				)
			);


	    endif;

		/*------------------------------------------------------------------------*/
	    /*  Join CCL
	    /*------------------------------------------------------------------------*/

			$wp_customize->add_section( self::SLUG.'_join' ,
				array(
					'priority'    => 3,
					'title'       => esc_html__( 'Join CCL', 'onesocial' ),
					'description' => '',
				)
			);

			$wp_customize->add_setting( self::SLUG.'_join_intro',
				array(
					'sanitize_callback' => self::SLUG.'_sanitize_text',
					'default'           => sprintf( __( 'Joining %s is easy. Just fill in the fields below, and we\'ll get a new account set up for you in no time.', 'onesocial' ), get_bloginfo('name') )
				)
			);

			$wp_customize->add_control( new CCLUK_Editor_Custom_Control(
				$wp_customize,
				self::SLUG.'_join_intro',
				array(
					'label'     	=> esc_html__('Introduction', 'onesocial'),
					'section' 		=> self::SLUG.'_join',
					'description'   => '',
				)
			));




			/**
			 * Hook to add other customize
			 */
			do_action( self::SLUG.'_customize_after_register', $wp_customize );

	}


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
		if ( $control->manager->get_setting(self::SLUG.'_hero_fullscreen')->value() == '' ) {
	        return true;
	    } else {
	        return false;
	    }
	}

	function sanitize_number( $input ) {
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

	function showon_frontpage() {
		return true;
		//return is_page_template( 'template-frontpage.php' );
	}

	function ccluk_gallery_source_validate( $validity, $value ){
		if ( ! class_exists( self::SLUG.'_PLus' ) ) {
			if ( $value != 'page' ) {
				$validity->add('notice', esc_html__('Upgrade to CCLUK Plus to unlock this feature.', 'onesocial' ) );
			}
		}
		return $validity;
	}

	/**
	 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
	 */
	function preview_js() {
	    wp_enqueue_script( self::SLUG.'_customizer_liveview', get_stylesheet_directory_uri() . '/assets/js/customizer-liveview.js', array( 'customize-preview', 'customize-selective-refresh' ), false, true );
	}

	/**
	 *
	 * customize standard settings
	 *
	 * @param object $wp_customize
	 * @param string $name
	 * @param string $id
	 *
	 */
	function standard_settings( $name, $id ) {

		$this->customize->add_section( $name.'_settings' ,
			array(
				'priority'    => 3,
				'title'       => esc_html__( 'Section Settings', 'onesocial' ),
				'description' => '',
				'panel'       => $name,
			)
		);

		// Show Content
		$this->customize->add_setting( $name.'_disable',
			array(
				'sanitize_callback' => 'ccluk_sanitize_checkbox',
				'default'           => '',
			)
		);

		$this->customize->add_control( $name.'_disable',
			array(
				'type'        => 'checkbox',
				'label'       => esc_html__('Hide this section?', 'onesocial'),
				'section'     => $name.'_settings',
				'description' => esc_html__('Check this box to hide this section.', 'onesocial'),
			)
		);

		// Section ID
		$this->customize->add_setting( $name.'_id',
			array(
				'sanitize_callback' => 'ccluk_sanitize_text',
				'default'           => $id,
			)
		);

		$this->customize->add_control( $name.'_id',
			array(
				'label' 		=> esc_html__('Section ID:', 'onesocial'),
				'section' 		=> $name.'_settings',
				'description'   => esc_html__( 'The section id, we will use this for link anchor.', 'onesocial' )
			)
		);

	}

	function js_settings(){
	    if ( ! function_exists( self::SLUG.'_get_actions_required' ) ) {
	        return;
	    }
	    $actions = ccluk_get_actions_required();
	    $n = array_count_values( $actions );
	    $number_action =  0;
	    if ( $n && isset( $n['active'] ) ) {
	        $number_action = $n['active'];
	    }

	    wp_localize_script( 'customize-controls', self::SLUG.'_customizer_settings', array(
	        'number_action' => $number_action,
	        'is_plus_activated' => class_exists( self::SLUG.'_PLus' ) ? 'y' : 'n',
	        'action_url' => admin_url( 'themes.php?page=ft_onepress&tab=actions_required' ),
	    ) );
	}
}
new CCLUK_Customizer;