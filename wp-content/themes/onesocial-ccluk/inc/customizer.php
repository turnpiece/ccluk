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
	 * @param WP_Customize_Manager $this->customize Theme Customizer object.
	 */
	function register( $wp_customize ) {

		// store customize object
		$this->customize = $wp_customize;

		// Load custom controls.
		require get_stylesheet_directory() . '/inc/customizer-controls.php';

		/**
		 * Hook to add other customize
		 */
		do_action( self::SLUG.'_customize_before_register', $this->customize );

		$pages = get_pages();
		$option_pages = array();
		$option_pages[0] = esc_html__( 'Select page', 'onesocial' );
		foreach( $pages as $p ){
			$option_pages[ $p->ID ] = $p->post_title;
		}

		$static_front_page = get_option( 'show_on_front' ) === 'page';

		if ($static_front_page) :

		/*------------------------------------------------------------------------*/
	    /*  Homepage: Join
	    /*------------------------------------------------------------------------*/

		    $this->customize->add_panel( self::SLUG.'_homepage_join' ,
				array(
					'priority'        => 160,
					'title'           => esc_html__( 'Homepage: Join', 'onesocial' ),
					'description'     => esc_html__( 'The join CCL UK section on the homepage', 'onesocial' ),
					'active_callback' => array( $this, 'showon_frontpage' )
				)
			);

		    $this->standard_settings( self::SLUG.'_homepage_join', 'join' );

			// Title
			$this->customize->add_setting( self::SLUG.'_homepage_join_title',
				array(
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => sprintf( __('Join %s', 'onesocial'), get_bloginfo('name') ),
				)
			);

			$this->customize->add_control( self::SLUG.'_homepage_join_title',
				array(
					'label' 		=> esc_html__('Section Title', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_join_settings',
					'description'   => '',
				)
			);

			// Source page settings
			$this->customize->add_setting( self::SLUG.'_homepage_join_source_page',
				array(
					'sanitize_callback' => array( $this, 'sanitize_number' ),
					'default'           => '',
				)
			);
			$this->customize->add_control( self::SLUG.'_homepage_join_source_page',
				array(
					'label'     	=> esc_html__('Button Link', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_join_settings',
					'type'          => 'select',
					'priority'      => 10,
					'choices'       => $option_pages,
					'description'   => esc_html__('Select a page to link to.', 'onesocial'),
				)
			);

			$this->customize->add_section( self::SLUG.'_homepage_join_content' ,
				array(
					'priority'    => 6,
					'title'       => esc_html__( 'Section Content', 'onesocial' ),
					'description' => '',
					'panel'       => self::SLUG.'_homepage_join',
				)
			);

			$this->customize->add_setting( self::SLUG.'_homepage_join_text',
				array(
					'sanitize_callback' => array( $this, 'sanitize_text' ),
					'default'           => __( 'If you want to be part of a movement lobbying for effective action on climate change, click the button and join us.', 'onesocial' )
				)
			);

			$this->customize->add_control( new CCLUK_Editor_Custom_Control(
				$this->customize,
				self::SLUG.'_homepage_join_text',
				array(
					'label' 		=> esc_html__('Text', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_join_content',
					'description'   => __( 'Text that will go alongside the join button', 'onesocial' )
				)
			));


		/*------------------------------------------------------------------------*/
	    /*  Homepage: Newsletter signup
	    /*------------------------------------------------------------------------*/

	    	$section = 'homepage_mailchimp';

			$this->add_homepage_panel( 
				$section, 
				esc_html__( 'Homepage: Newsletter', 'onesocial' ),
				esc_html__( 'The newsletter section on the homepage', 'onesocial' ),
				160
			);

		    $this->standard_settings( self::SLUG.'_'.$section, 'mailchimp' );

			// Title
			$this->add_setting( 
				$section.'_title', 
				'sanitize_text',
				__('Signup for our Newsletter', 'onesocial')
			);

			$this->customize->add_control( self::SLUG.'_'.$section.'_title',
				array(
					'label' 		=> esc_html__('Section Title', 'onesocial'),
					'section' 		=> self::SLUG.'_'.$section.'_settings',
					'description'   => '',
				)
			);

			$this->customize->add_section( self::SLUG.'_'.$section.'_content' ,
				array(
					'priority'    => 6,
					'title'       => esc_html__( 'Section Content', 'onesocial' ),
					'description' => '',
					'panel'       => self::SLUG.'_'.$section,
				)
			);

			$this->add_setting( 
				$section.'_text', 
				'sanitize_text', 
				__( 'If you want to know what we\'re up to, signup for our newsletter.', 'onesocial' )
			);

			$this->customize->add_control( new CCLUK_Editor_Custom_Control(
				$this->customize,
				self::SLUG.'_'.$section.'_text',
				array(
					'label' 		=> esc_html__('Text', 'onesocial'),
					'section' 		=> self::SLUG.'_'.$section.'_content',
					'description'   => __( 'Text that will go alongside the form', 'onesocial' )
				)
			));

			$this->add_setting( $section.'_form', 'sanitize_text' );

			$this->customize->add_control( new CCLUK_Editor_Custom_Control(
				$this->customize,
				self::SLUG.'_'.$section.'_form',
				array(
					'label' 		=> esc_html__('Form ID', 'onesocial'),
					'section' 		=> self::SLUG.'_'.$section.'_content',
					'description'   => __( 'Enter a MailChimp form ID', 'onesocial' )
				)
			));

			$this->add_setting( 
				$section.'_privacy_text', 
				'sanitize_text', 
				__( 'We respect your privacy.', 'onesocial' ) 
			);

			$this->customize->add_control( new CCLUK_Editor_Custom_Control(
				$this->customize,
				self::SLUG.'_'.$section.'_privacy_text',
				array(
					'label' 		=> esc_html__('Privacy text', 'onesocial'),
					'section' 		=> self::SLUG.'_'.$section.'_content',
					'description'   => __( 'Text linking to the privacy policy', 'onesocial' ),
				)
			));

			// Privacy settings
			$this->add_setting( $section.'_privacy_page', 'sanitize_number' );

			$this->customize->add_control( self::SLUG.'_'.$section.'_privacy_page',
				array(
					'label'     	=> esc_html__('Privacy Policy', 'onesocial'),
					'section' 		=> self::SLUG.'_'.$section.'_settings',
					'type'          => 'select',
					'priority'      => 10,
					'choices'       => $option_pages,
					'description'   => esc_html__('Select the privacy policy page.', 'onesocial'),
				)
			);


		/*------------------------------------------------------------------------*/
	    /*  Homepage: Shortcode embed
	    /*------------------------------------------------------------------------*/

	    	$section = 'homepage_embed';

			$this->add_homepage_panel( 
				$section, 
				esc_html__( 'Homepage: Custom form', 'onesocial' ),
				esc_html__( 'Embed a custom form via a shortcode on the home page', 'onesocial' ),
				160
			);

		    $this->standard_settings( self::SLUG.'_'.$section, 'embed' );

			// Title
			$this->add_setting( 
				$section.'_title', 
				'sanitize_text',
				__('Signup for our Newsletter', 'onesocial')
			);

			$this->customize->add_control( self::SLUG.'_'.$section.'_title',
				array(
					'label' 		=> esc_html__('Section Title', 'onesocial'),
					'section' 		=> self::SLUG.'_'.$section.'_settings',
					'description'   => '',
				)
			);

			$this->customize->add_section( self::SLUG.'_'.$section.'_content' ,
				array(
					'priority'    => 6,
					'title'       => esc_html__( 'Section Content', 'onesocial' ),
					'description' => '',
					'panel'       => self::SLUG.'_'.$section,
				)
			);

			$this->add_setting( 
				$section.'_text', 
				'sanitize_text', 
				__( 'If you want to know what we\'re up to, signup for our newsletter.', 'onesocial' )
			);

			$this->customize->add_control( new CCLUK_Editor_Custom_Control(
				$this->customize,
				self::SLUG.'_'.$section.'_text',
				array(
					'label' 		=> esc_html__('Text', 'onesocial'),
					'section' 		=> self::SLUG.'_'.$section.'_content',
					'description'   => __( 'Text that will go alongside the signup form', 'onesocial' )
				)
			));

			$this->add_setting( $section.'_form', 'sanitize_text' );

			$this->customize->add_control( new CCLUK_Editor_Custom_Control(
				$this->customize,
				self::SLUG.'_'.$section.'_form',
				array(
					'label' 		=> esc_html__('Form shortcode', 'onesocial'),
					'section' 		=> self::SLUG.'_'.$section.'_content',
					'description'   => __( 'Enter a shortcode, including the square brackets', 'onesocial' )
				)
			));

			$this->add_setting( 
				$section.'_privacy_text', 
				'sanitize_text', 
				__( 'We respect your privacy.', 'onesocial' ) 
			);

			$this->customize->add_control( new CCLUK_Editor_Custom_Control(
				$this->customize,
				self::SLUG.'_'.$section.'_privacy_text',
				array(
					'label' 		=> esc_html__('Privacy text', 'onesocial'),
					'section' 		=> self::SLUG.'_'.$section.'_content',
					'description'   => __( 'Text linking to the privacy policy', 'onesocial' ),
				)
			));

			// Privacy settings
			$this->add_setting( $section.'_privacy_page', 'sanitize_number' );

			$this->customize->add_control( self::SLUG.'_'.$section.'_privacy_page',
				array(
					'label'     	=> esc_html__('Privacy Policy', 'onesocial'),
					'section' 		=> self::SLUG.'_'.$section.'_settings',
					'type'          => 'select',
					'priority'      => 10,
					'choices'       => $option_pages,
					'description'   => esc_html__('Select the privacy policy page.', 'onesocial'),
				)
			);

		/*------------------------------------------------------------------------*/
	    /*  Homepage: About
	    /*------------------------------------------------------------------------*/

		    $this->customize->add_panel( self::SLUG.'_homepage_about' ,
				array(
					'priority'        => 160,
					'title'           => esc_html__( 'Homepage: About', 'onesocial' ),
					'description'     => '',
					'active_callback' => array( $this, 'showon_frontpage' )
				)
			);

		    $this->standard_settings( self::SLUG.'_homepage_about', 'about' );

			// Title
			$this->customize->add_setting( self::SLUG.'_homepage_about_title',
				array(
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => esc_html__('About Us', 'onesocial'),
				)
			);

			$this->customize->add_control( self::SLUG.'_homepage_about_title',
				array(
					'label' 		=> esc_html__('Section Title', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_about_settings',
					'description'   => '',
				)
			);

			// Source page settings
			$this->customize->add_setting( self::SLUG.'_homepage_about_source_page',
				array(
					'sanitize_callback' => array( $this, 'sanitize_number' ),
					'default'           => '',
				)
			);
			$this->customize->add_control( self::SLUG.'_homepage_about_source_page',
				array(
					'label'     	=> esc_html__('Title Link', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_about_settings',
					'type'          => 'select',
					'priority'      => 10,
					'choices'       => $option_pages,
					'description'   => esc_html__('Select a page the title will link to.', 'onesocial'),
				)
			);

			$this->customize->add_section( self::SLUG.'_homepage_about_content' ,
				array(
					'priority'    => 6,
					'title'       => esc_html__( 'Section Content', 'onesocial' ),
					'description' => '',
					'panel'       => self::SLUG.'_homepage_about',
				)
			);

			$this->customize->add_setting( self::SLUG.'_homepage_about_intro',
				array(
					'sanitize_callback' => array( $this, 'sanitize_text' ),
					'default'           => '',
				)
			);

			$this->customize->add_control( new CCLUK_Editor_Custom_Control(
				$this->customize,
				self::SLUG.'_homepage_about_intro',
				array(
					'label' 		=> sprintf( esc_html__('Introduction', 'onesocial'), $box ),
					'section' 		=> self::SLUG.'_homepage_about_content',
					'description'   => '',
				)
			));

			// Boxes
			for ( $box = 1; $box <= 2; $box++ ) :

			$this->customize->add_setting( self::SLUG.'_homepage_about_box_'.$box,
				array(
					'sanitize_callback' => array( $this, 'sanitize_text' ),
					'default'           => '',
				)
			);

			$this->customize->add_control( new CCLUK_Editor_Custom_Control(
				$this->customize,
				self::SLUG.'_homepage_about_box_'.$box,
				array(
					'label' 		=> sprintf( esc_html__('Box %d content', 'onesocial'), $box ),
					'section' 		=> self::SLUG.'_homepage_about_content',
					'description'   => '',
				)
			));

			endfor;

			$this->customize->add_setting( self::SLUG.'_homepage_about_footer',
				array(
					'sanitize_callback' => array( $this, 'sanitize_text' ),
					'default'           => '',
				)
			);

			$this->customize->add_control( new CCLUK_Editor_Custom_Control(
				$this->customize,
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

		    $this->customize->add_panel( self::SLUG.'_homepage_contact' ,
				array(
					'priority'        => 270,
					'title'           => esc_html__( 'Home page: Contact', 'onesocial' ),
					'description'     => '',
					'active_callback' => array( $this, 'showon_frontpage' )
				)
			);

			$this->standard_settings( self::SLUG.'_homepage_contact', 'contact' );

			// Title
			$this->customize->add_setting( self::SLUG.'_homepage_contact_title',
				array(
					'sanitize_callback' => array( $this, 'sanitize_text_field' ),
					'default'           => esc_html__('Get in touch', 'onesocial'),
				)
			);

			$this->customize->add_control( self::SLUG.'_homepage_contact_title',
				array(
					'label'     => esc_html__('Section Title', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_contact_settings',
					'description'   => '',
				)
			);

			// Sub Title
			$this->customize->add_setting( self::SLUG.'_homepage_contact_subtitle',
				array(
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => esc_html__('Section subtitle', 'onesocial'),
				)
			);

			$this->customize->add_control( self::SLUG.'_homepage_contact_subtitle',
				array(
					'label'     => esc_html__('Section Subtitle', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_contact_settings',
					'description'   => '',
				)
			);

	        // Description
	        $this->customize->add_setting( self::SLUG.'_homepage_contact_desc',
	            array(
	                'sanitize_callback' => array( $this, 'sanitize_text' ),
	                'default'           => '',
	            )
	        );

	        $this->customize->add_control( new CCLUK_Editor_Custom_Control(
	            $this->customize,
	            self::SLUG.'_homepage_contact_desc',
	            array(
	                'label' 		=> esc_html__('Section Description', 'onesocial'),
	                'section' 		=> self::SLUG.'_homepage_contact_settings',
	                'description'   => '',
	            )
	        ));

			$this->customize->add_section( self::SLUG.'_homepage_contact_content' ,
				array(
					'priority'    => 6,
					'title'       => esc_html__( 'Section Content', 'onesocial' ),
					'description' => '',
					'panel'       => self::SLUG.'_homepage_contact',
				)
			);

			// Contact form 7 guide.
			$this->customize->add_setting( self::SLUG.'_homepage_contact_cf7_guide',
				array(
					'sanitize_callback' => array( $this, 'sanitize_text' )
				)
			);

			$this->customize->add_control( new CCLUK_Misc_Control( $this->customize, self::SLUG.'_homepage_contact_cf7_guide',
				array(
					'section'     => self::SLUG.'_homepage_contact_content',
					'type'        => 'custom_message',
					'description' => wp_kses_post( 'In order to display contact form please install <a target="_blank" href="https://wordpress.org/plugins/contact-form-7/">Contact Form 7</a> plugin and then copy the contact form shortcode and paste it here, the shortcode will be like this <code>[contact-form-7 id="xxxx" title="Example Contact Form"]</code>', 'onesocial' )
				)
			));

			// Contact Form 7 Shortcode
			$this->customize->add_setting( self::SLUG.'_homepage_contact_cf7',
				array(
					'sanitize_callback' => array( $this, 'sanitize_text' ),
					'default'           => '',
				)
			);

			$this->customize->add_control( self::SLUG.'_homepage_contact_cf7',
				array(
					'label'     	=> esc_html__('Contact Form 7 Shortcode.', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_contact_content',
					'description'   => '',
				)
			);

			// Show CF7
			$this->customize->add_setting( self::SLUG.'_homepage_contact_cf7_disable',
				array(
					'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
					'default'           => '',
				)
			);

			$this->customize->add_control( self::SLUG.'_homepage_contact_cf7_disable',
				array(
					'type'        => 'checkbox',
					'label'       => esc_html__('Hide contact form completely.', 'onesocial'),
					'section'     => self::SLUG.'_homepage_contact_content',
					'description' => esc_html__('Check this box to hide contact form.', 'onesocial'),
				)
			);

			// Contact Text
			$this->customize->add_setting( self::SLUG.'_homepage_contact_text',
				array(
					'sanitize_callback' => array( $this, 'sanitize_text' ),
					'default'           => '',
				)
			);

			$this->customize->add_control( new CCLUK_Editor_Custom_Control(
				$this->customize,
				self::SLUG.'_homepage_contact_text',
				array(
					'label'     	=> esc_html__('Contact Text', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_contact_content',
					'description'   => '',
				)
			));

			// hr
			$this->customize->add_setting( self::SLUG.'_homepage_contact_text_hr', array( 'sanitize_callback' => array( $this, 'sanitize_text' ) ) );
			$this->customize->add_control( new CCLUK_Misc_Control( $this->customize, self::SLUG.'_homepage_contact_text_hr',
				array(
					'section'     => self::SLUG.'_homepage_contact_content',
					'type'        => 'hr'
				)
			));

			// Address Box
			$this->customize->add_setting( self::SLUG.'_homepage_contact_address_title',
				array(
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => '',
				)
			);

			$this->customize->add_control( self::SLUG.'_homepage_contact_address_title',
				array(
					'label'     	=> esc_html__('Contact Box Title', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_contact_content',
					'description'   => '',
				)
			);

			// Contact Text
			$this->customize->add_setting( self::SLUG.'_homepage_contact_address',
				array(
					'sanitize_callback' => array( $this, 'sanitize_text' ),
					'default'           => '',
				)
			);

			$this->customize->add_control( self::SLUG.'_homepage_contact_address',
				array(
					'label'     => esc_html__('Address', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_contact_content',
					'description'   => '',
				)
			);

			// Contact Phone
			$this->customize->add_setting( self::SLUG.'_homepage_contact_phone',
				array(
					'sanitize_callback' => array( $this, 'sanitize_text' ),
					'default'           => '',
				)
			);

			$this->customize->add_control( self::SLUG.'_homepage_contact_phone',
				array(
					'label'     	=> esc_html__('Phone', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_contact_content',
					'description'   => '',
				)
			);

			// Contact Email
			$this->customize->add_setting( self::SLUG.'_homepage_contact_email',
				array(
					'sanitize_callback' => 'sanitize_email',
					'default'           => '',
				)
			);

			$this->customize->add_control( self::SLUG.'_homepage_contact_email',
				array(
					'label'     	=> esc_html__('Email', 'onesocial'),
					'section' 		=> self::SLUG.'_homepage_contact_content',
					'description'   => '',
				)
			);

			// Contact Fax
			$this->customize->add_setting( self::SLUG.'_homepage_contact_fax',
				array(
					'sanitize_callback' => array( $this, 'sanitize_text' ),
					'default'           => '',
				)
			);

			$this->customize->add_control( self::SLUG.'_homepage_contact_fax',
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

			$this->customize->add_section( self::SLUG.'_join' ,
				array(
					'priority'    => 3,
					'title'       => esc_html__( 'Join CCL', 'onesocial' ),
					'description' => '',
				)
			);

			$this->customize->add_setting( self::SLUG.'_join_intro',
				array(
					'sanitize_callback' => array( $this, 'sanitize_text' ),
					'default'           => sprintf( __( 'Joining %s is easy. Just fill in the fields below, and we\'ll get a new account set up for you in no time.', 'onesocial' ), get_bloginfo('name') )
				)
			);

			$this->customize->add_control( new CCLUK_Editor_Custom_Control(
				$this->customize,
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
			do_action( self::SLUG.'_customize_after_register', $this->customize );

	}

	private function add_setting( $id, $callback, $default = '' ) {

		$args = array();

		if (function_exists($callback))
			$args['sanitize_callback'] = $callback;
		elseif (method_exists( array( $this, $callback ) ) )
			$args['sanitize_callback'] = array( $this, $callback );

		if ($default)
			$args['default'] = $default;

		$this->customize->add_setting( self::SLUG.'_' . $id, $args );
	}

	/*------------------------------------------------------------------------*/
	/*  CCLUK Sanitize Functions.
	/*------------------------------------------------------------------------*/

	function sanitize_file_url( $file_url ) {
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
	function hero_fullscreen_callback ( $control ) {
		if ( $control->manager->get_setting(self::SLUG.'_hero_fullscreen')->value() == '' ) {
	        return true;
	    } else {
	        return false;
	    }
	}

	function sanitize_number( $input ) {
	    return balanceTags( $input );
	}

	function sanitize_hex_color( $color ) {
		if ( $color === '' ) {
			return '';
		}
		if ( preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
			return $color;
		}
		return null;
	}

	function sanitize_checkbox( $input ) {
	    if ( $input == 1 ) {
			return 1;
	    } else {
			return 0;
	    }
	}

	function sanitize_text( $string ) {
		return wp_kses_post( balanceTags( $string ) );
	}

	function sanitize_html_input( $string ) {
		return wp_kses_allowed_html( $string );
	}

	function showon_frontpage() {
		return true;
		//return is_page_template( 'template-frontpage.php' );
	}

	/**
	 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
	 */
	public function preview_js() {
	    wp_enqueue_script( self::SLUG.'_customizer_liveview', get_stylesheet_directory_uri() . '/assets/js/customizer-liveview.js', array( 'customize-preview', 'customize-selective-refresh' ), false, true );
	}

	/**
	 *
	 * add homepage panel
	 *
	 */
	private function add_homepage_panel( $section, $title, $description, $priority = 100 ) {
		$this->customize->add_panel( self::SLUG.'_'.$section ,
			array(
				'priority'        => $priority,
				'title'           => $title,
				'description'     => $description,
				'active_callback' => array( $this, 'showon_frontpage' )
			)
		);
	}

	/**
	 *
	 * customize standard settings
	 *
	 * @param object $this->customize
	 * @param string $name
	 * @param string $id
	 *
	 */
	private function standard_settings( $name, $id ) {

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
				'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
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
				'sanitize_callback' => array( $this, 'sanitize_text' ),
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