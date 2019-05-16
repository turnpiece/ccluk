<?php
/**
 * CCLUK Theme Customizer.
 *
 * @package CCLUK
 */

class CCLUK_Customizer {

	const SLUG = 'ccluk';

	private $customize;

	private $option_pages = array();

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
		$this->option_pages[0] = esc_html__( 'Select page', 'onesocial' );
		foreach( $pages as $p ){
			$this->option_pages[ $p->ID ] = $p->post_title;
		}

		$static_front_page = get_option( 'show_on_front' ) === 'page';

		if ($static_front_page) {

		/*------------------------------------------------------------------------*/
	    /*  Homepage: Banner
	    /*------------------------------------------------------------------------*/

	    	$this->homepage_banner( 140 );

		/*------------------------------------------------------------------------*/
	    /*  Homepage: Join
	    /*------------------------------------------------------------------------*/

	    	$this->homepage_join( 150 );

		/*------------------------------------------------------------------------*/
	    /*  Homepage: Newsletter signup
	    /*------------------------------------------------------------------------*/

	    	$this->homepage_newsletter( 160 );

		/*------------------------------------------------------------------------*/
	    /*  Homepage: HTML embed
	    /*------------------------------------------------------------------------*/

	    	$this->homepage_embed( 170 );

		/*------------------------------------------------------------------------*/
	    /*  Homepage: About
	    /*------------------------------------------------------------------------*/

	    	$this->homepage_about( 180 );

		/*------------------------------------------------------------------------*/
	    /*  Home page: Contact
	    /*------------------------------------------------------------------------*/

	    	$this->homepage_contact( 190 );

	    }

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

			$this->add_setting(
				'join_intro',
				array( $this, 'sanitize_text' ),
				sprintf( __( 'Join %s and become part of a global movement lobbying for effective action on climate change.', 'onesocial' ), get_bloginfo('name') )
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




		/*------------------------------------------------------------------------*/
	    /*  Newsletter
	    /*------------------------------------------------------------------------*/

	    	$section = 'newsletter';

			$this->customize->add_section( self::SLUG.'_'.$section ,
				array(
					'priority'    => 9,
					'title'       => esc_html__( 'Newsletter', 'onesocial' ),
					'description' => '',
				)
			);

			$this->add_setting( $section.'_signup_form' );

			$this->customize->add_control( new CCLUK_Editor_Custom_Control(
				$this->customize,
				self::SLUG.'_'.$section.'_signup_form',
				array(
					'label' 		=> esc_html__('HTML code', 'onesocial'),
					'section' 		=> self::SLUG.'_'.$section,
					'description'   => __( 'Paste in the HTML code for whatever you want to embed', 'onesocial' )
				)
			));

			// Privacy settings
			$this->add_setting( $section.'_privacy_page', 'sanitize_number' );

			$this->customize->add_control( self::SLUG.'_'.$section.'_privacy_page',
				array(
					'label'     	=> esc_html__('Privacy Policy', 'onesocial'),
					'section' 		=> self::SLUG.'_'.$section,
					'type'          => 'select',
					'priority'      => 10,
					'choices'       => $this->option_pages,
					'description'   => esc_html__('Select the privacy policy page.', 'onesocial'),
				)
			);

	}

	private function add_setting( $id, $callback = null, $default = '' ) {

		$args = array();

		if (!is_null($callback)) {
			if (function_exists($callback))
				$args['sanitize_callback'] = $callback;
			elseif (method_exists( $this, $callback ) )
				$args['sanitize_callback'] = array( $this, $callback );
		}

		if ($default)
			$args['default'] = $default;

		$this->customize->add_setting( $id, $args );
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
				'panel'       => self::SLUG.'_'.$name,
			)
		);

		$this->customize->add_setting( $name.'_audience', array(
		  	'capability' => 'edit_theme_options',
		  	'default' => 'all',
		  	'sanitize_callback' => array( $this, 'sanitize_text' ),
		) );

		$this->customize->add_control( $name.'_audience', array(
		  	'type' => 'radio',
		  	'section' => $name.'_settings',
		  	'label' => __( 'Audience', 'onesocial' ),
		  	'description' => __( 'Who do you want to see this section?' ),
		  	'choices' => array(
		    	'all' => __( 'Everyone', 'onesocial' ),
		    	'logged_in' => __( 'Only logged in users', 'onesocial' ),
		    	'logged_out' => __( 'Only logged out users', 'onesocial' ),
		    	'none' => __( 'No one', 'onesocial' )
		  	),
		) );

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

	/**
	 *
	 * add content section
	 *
	 * @param string $section
	 * @param string $panel
	 *
	 */
	private function add_content_section( $section, $panel = null ) {
		$this->customize->add_section( $section.'_content' ,
			array(
				'priority'    => 6,
				'title'       => esc_html__( 'Section Content', 'onesocial' ),
				'description' => '',
				'panel'       => empty($panel) ? self::SLUG.'_'.$section : $panel,
			)
		);
	}

	private function homepage_banner( $priority = 150 ) {

    	$section = 'homepage_banner';

		$this->add_homepage_panel( 
			$section, 
			esc_html__( 'Homepage: Banner', 'onesocial' ),
			esc_html__( 'Set the banner image and content', 'onesocial' ),
			$priority
		);

	    $this->standard_settings( $section, 'banner' );

	    $this->customize->add_setting( $section.'_layout', array(
		  	'capability' => 'edit_theme_options',
		  	'default' => 'background',
		  	'sanitize_callback' => array( $this, 'sanitize_text' ),
		) );

		$this->customize->add_control( $section.'_layout', array(
		  	'type' => 'radio',
		  	'section' => $section.'_settings',
		  	'label' => __( 'Layout', 'onesocial' ),
		  	'choices' => array(
		    	'text-left' => __( 'Text on left, image on right', 'onesocial' ),
		    	'text-right' => __( 'Text on right, image on left', 'onesocial' ),
		    	'background' => __( 'Text over background image', 'onesocial' )
		  	),
		) );

		$this->add_content_section( $section );

		$this->add_setting( 
			$section.'_heading', 
			'sanitize_text'
		);

		$this->customize->add_control( new CCLUK_Editor_Custom_Control(
			$this->customize,
			$section.'_heading',
			array(
				'label' 		=> esc_html__('Heading', 'onesocial'),
				'section' 		=> $section.'_content'
			)
		));

		$this->add_setting( 
			$section.'_text', 
			'sanitize_text'
		);

		$this->customize->add_control( new CCLUK_Editor_Custom_Control(
			$this->customize,
			$section.'_text',
			array(
				'label' 		=> esc_html__('Text', 'onesocial'),
				'section' 		=> $section.'_content',
				'description'   => __( 'Text that will go over the image', 'onesocial' )
			)
		));

		// Link settings
		$this->add_setting( $section.'_page', 'sanitize_number' );

		$this->customize->add_control( $section.'_page',
			array(
				'label'     	=> esc_html__('Page', 'onesocial'),
				'section' 		=> $section.'_content',
				'type'          => 'select',
				'priority'      => 10,
				'choices'       => $this->option_pages,
				'description'   => esc_html__('Select the page you want to link to.', 'onesocial'),
			)
		);

		// Image
		$this->add_setting( $section.'_image', 'sanitize_number' );	

		$this->customize->add_control( new WP_Customize_Image_Control( 
			$this->customize, 
			$section.'_image', 
			array(
	        'label'             => __('Image', 'onesocial'),
	        'section'           => $section.'_content',
	        'settings'          => $section.'_image',    
	    )));

		// Buttons
		for( $i = 1; $i <= 2; $i++ ) {
			$this->add_setting( $section.'_button_'.$i.'_page', 'sanitize_number' );

			$this->customize->add_control( $section.'_button_'.$i.'_page',
				array(
					'label'     	=> esc_html__('Button '.$i.' Page', 'onesocial'),
					'section' 		=> $section.'_content',
					'type'          => 'select',
					'priority'      => 10,
					'choices'       => $this->option_pages,
					'description'   => esc_html__('Select the page you want the button to link to.', 'onesocial'),
				)
			);	

			$this->add_setting( 
				$section.'_button_'.$i.'_text', 
				'sanitize_text'
			);

			$this->customize->add_control( new CCLUK_Editor_Custom_Control(
				$this->customize,
				$section.'_button_'.$i.'_text',
				array(
					'label' 		=> esc_html__('Button '.$i.' Text', 'onesocial'),
					'section' 		=> $section.'_content'
				)
			));
		}
	}

	private function homepage_join( $priority = 160 ) {

		$section = 'homepage_join';

	    $this->add_homepage_panel( 
	    	$section, 
	    	esc_html__( 'Homepage: Join', 'onesocial' ), 
	    	esc_html__( 'The join CCL UK section on the homepage', 'onesocial' ), 
	    	$priority 
	    );

	    $this->standard_settings( $section, 'join' );

		// Title
		$this->add_setting( 
			$section.'_title',
			'sanitize_text_field',
			sprintf( __('Join %s', 'onesocial'), get_bloginfo('name') )
		);

		$this->customize->add_control( $section.'_title',
			array(
				'label' 		=> esc_html__('Section Title', 'onesocial'),
				'section' 		=> $section.'_settings',
				'description'   => '',
			)
		);

		// Source page settings
		$this->add_setting( 
			$section.'_source_page',
			'sanitize_number'
		);

		$this->customize->add_control( $section.'_source_page',
			array(
				'label'     	=> esc_html__('Button Link', 'onesocial'),
				'section' 		=> $section.'_settings',
				'type'          => 'select',
				'priority'      => 10,
				'choices'       => $this->option_pages,
				'description'   => esc_html__('Select a page to link to.', 'onesocial'),
			)
		);

		$this->add_content_section( $section );

		$this->add_setting(
			$section.'_text',
			'sanitize_text',
			__( 'If you want to be part of a movement lobbying for effective action on climate change, click the button and join us.', 'onesocial' )
		);

		$this->customize->add_control( new CCLUK_Editor_Custom_Control(
			$this->customize,
			$section.'_text',
			array(
				'label' 		=> esc_html__('Text', 'onesocial'),
				'section' 		=> $section.'_content',
				'description'   => __( 'Text that will go alongside the join button', 'onesocial' )
			)
		));
	}

	private function homepage_newsletter( $priority = 160 ) {

    	$section = 'homepage_newsletter';

		$this->add_homepage_panel( 
			$section, 
			esc_html__( 'Homepage: Newsletter', 'onesocial' ),
			esc_html__( 'The newsletter section on the homepage', 'onesocial' ),
			$priority
		);

	    $this->standard_settings( $section, 'newsletter' );

		$this->add_content_section( $section );

		$this->add_setting( 
			$section.'_text', 
			'sanitize_text', 
			__( 'If you want to know what we\'re up to, signup for our newsletter.', 'onesocial' )
		);

		$this->customize->add_control( new CCLUK_Editor_Custom_Control(
			$this->customize,
			$section.'_text',
			array(
				'label' 		=> esc_html__('Text', 'onesocial'),
				'section' 		=> $section.'_content',
				'description'   => __( 'Text that will go alongside the form', 'onesocial' )
			)
		));

		$this->add_setting( 
			$section.'_privacy_text', 
			'sanitize_text', 
			__( 'We respect your privacy.', 'onesocial' ) 
		);

		$this->customize->add_control( new CCLUK_Editor_Custom_Control(
			$this->customize,
			$section.'_privacy_text',
			array(
				'label' 		=> esc_html__('Privacy text', 'onesocial'),
				'section' 		=> $section.'_content',
				'description'   => __( 'Text linking to the privacy policy', 'onesocial' ),
			)
		));
	}

	private function homepage_embed( $priority = 170 ) {

    	$section = 'homepage_embed';

		$this->add_homepage_panel( 
			$section, 
			esc_html__( 'Homepage: Embed HTML', 'onesocial' ),
			esc_html__( 'Embed some HTML code such as for a mailing list signup form', 'onesocial' ),
			$priority
		);

	    $this->standard_settings( $section, 'embed' );

		// Title
		$this->add_setting( 
			$section.'_title', 
			'sanitize_text'
		);

		$this->customize->add_control( $section.'_title',
			array(
				'label' 		=> esc_html__('Section Title', 'onesocial'),
				'section' 		=> $section.'_settings',
				'description'   => '',
			)
		);

		$this->add_content_section( $section );

		$this->add_setting( 
			$section.'_text', 
			'sanitize_text'
		);

		$this->customize->add_control( new CCLUK_Editor_Custom_Control(
			$this->customize,
			$section.'_text',
			array(
				'label' 		=> esc_html__('Text', 'onesocial'),
				'section' 		=> $section.'_content',
				'description'   => __( 'Text that will go alongside the embedded HTML', 'onesocial' )
			)
		));

		$this->add_setting( $section.'_embed' );

		$this->customize->add_control( new CCLUK_Editor_Custom_Control(
			$this->customize,
			$section.'_embed',
			array(
				'label' 		=> esc_html__('HTML code', 'onesocial'),
				'section' 		=> $section.'_content',
				'description'   => __( 'Paste in the HTML code for whatever you want to embed', 'onesocial' )
			)
		));

		$this->add_setting( 
			$section.'_link_text', 
			'sanitize_text'
		);

		$this->customize->add_control( new CCLUK_Editor_Custom_Control(
			$this->customize,
			$section.'_link_text',
			array(
				'label' 		=> esc_html__('Privacy text', 'onesocial'),
				'section' 		=> $section.'_content',
				'description'   => __( 'Text linking to the page you select below.', 'onesocial' ),
			)
		));

		// Link settings
		$this->add_setting( $section.'_link_page', 'sanitize_number' );

		$this->customize->add_control( $section.'_link_page',
			array(
				'label'     	=> esc_html__('Page', 'onesocial'),
				'section' 		=> $section.'_content',
				'type'          => 'select',
				'priority'      => 10,
				'choices'       => $this->option_pages,
				'description'   => esc_html__('Select the page you want to link to. If you\'ve pasted in HTML code for a newsletter signup form then you\'ll need to link to the page your privacy policy is on.', 'onesocial'),
			)
		);		
	}

	private function homepage_about( $priority = 180 ) {

    	$section = 'homepage_about';

	    $this->add_homepage_panel( 
	    	$section,
			esc_html__( 'Homepage: About', 'onesocial' ),
			'',
			$priority
		);

	    $this->standard_settings( $section, 'about' );

		// Title
		$this->add_setting( 
			$section.'_title',
			'sanitize_text_field',
			esc_html__('About Us', 'onesocial')
		);

		$this->customize->add_control( $section.'_title',
			array(
				'label' 		=> esc_html__('Section Title', 'onesocial'),
				'section' 		=> $section.'_settings',
				'description'   => '',
			)
		);

		// Source page settings
		$this->add_setting( $section.'_source_page', 'sanitize_number' );

		$this->customize->add_control( $section.'_source_page',
			array(
				'label'     	=> esc_html__('Title Link', 'onesocial'),
				'section' 		=> $section.'_settings',
				'type'          => 'select',
				'priority'      => 10,
				'choices'       => $this->option_pages,
				'description'   => esc_html__('Select a page the title will link to.', 'onesocial'),
			)
		);

		$this->add_content_section( $section );

		$this->add_setting( 'homepage_about_intro', array( $this, 'sanitize_text' ) );

		$this->customize->add_control( new CCLUK_Editor_Custom_Control(
			$this->customize,
			$section.'_intro',
			array(
				'label' 		=> sprintf( esc_html__('Introduction', 'onesocial'), $box ),
				'section' 		=> $section.'_content',
				'description'   => '',
			)
		));

		// Boxes
		for ( $box = 1; $box <= 2; $box++ ) :

			$this->add_setting( $section.'_box_'.$box, array( $this, 'sanitize_text' ) );

			$this->customize->add_control( new CCLUK_Editor_Custom_Control(
				$this->customize,
				self::SLUG.'_'.$section.'_box_'.$box,
				array(
					'label' 		=> sprintf( esc_html__('Box %d content', 'onesocial'), $box ),
					'section' 		=> $section.'_content',
					'description'   => '',
				)
			));

		endfor;

		$this->add_setting( 'homepage_about_footer', array( $this, 'sanitize_text' ) );

		$this->customize->add_control( new CCLUK_Editor_Custom_Control(
			$this->customize,
			self::SLUG.'_homepage_about_footer',
			array(
				'label' 		=> sprintf( esc_html__('Footer', 'onesocial'), $box ),
				'section' 		=> self::SLUG.'_homepage_about_content',
				'description'   => __( 'Appears below the two boxes', 'onesocial' )
			)
		));
	}

	private function homepage_contact( $priority = 190 ) {

		$section = 'homepage_contact';

	    $this->add_homepage_panel( 
	    	$section,
	    	esc_html__( 'Homepage: Contact', 'onesocial' ),
	    	'',
	    	$priority
	    );

		$this->standard_settings( $section, 'contact' );

		// Title
		$this->add_setting( 
			$section.'_title',
			array( $this, 'sanitize_text_field' ),
			esc_html__('Get in touch', 'onesocial')
		);

		$this->customize->add_control( $section.'_title',
			array(
				'label'     => esc_html__('Section Title', 'onesocial'),
				'section' 		=> $section.'_settings',
				'description'   => '',
			)
		);

		// Sub Title
		$this->add_setting( $section.'_subtitle', 'sanitize_text_field', esc_html__('Section subtitle', 'onesocial') );

		$this->customize->add_control( self::SLUG.'_'.$section.'_subtitle',
			array(
				'label'     => esc_html__('Section Subtitle', 'onesocial'),
				'section' 		=> self::SLUG.'_'.$section.'_settings',
				'description'   => '',
			)
		);

        // Description
        $this->add_setting( $section.'_desc', array( $this, 'sanitize_text' ) );

        $this->customize->add_control( new CCLUK_Editor_Custom_Control(
            $this->customize,
            self::SLUG.'_'.$section.'_desc',
            array(
                'label' 		=> esc_html__('Section Description', 'onesocial'),
                'section' 		=> self::SLUG.'_'.$section.'_settings',
                'description'   => '',
            )
        ));

        $this->add_content_section( $section );

		// Contact form 7 guide.
		$this->add_setting( $section.'_cf7_guide', array( $this, 'sanitize_text' ) );

		$this->customize->add_control( new CCLUK_Misc_Control( $this->customize, self::SLUG.'_'.$section.'_cf7_guide',
			array(
				'section'     => self::SLUG.'_'.$section.'_content',
				'type'        => 'custom_message',
				'description' => wp_kses_post( 'In order to display contact form please install <a target="_blank" href="https://wordpress.org/plugins/contact-form-7/">Contact Form 7</a> plugin and then copy the contact form shortcode and paste it here, the shortcode will be like this <code>[contact-form-7 id="xxxx" title="Example Contact Form"]</code>', 'onesocial' )
			)
		));

		// Contact Form 7 Shortcode
		$this->add_setting( $section.'_cf7', array( $this, 'sanitize_text' ) );

		$this->customize->add_control( $section.'_cf7',
			array(
				'label'     	=> esc_html__('Contact Form 7 Shortcode.', 'onesocial'),
				'section' 		=> $section.'_content',
				'description'   => '',
			)
		);

		// Show CF7
		$this->add_setting( $section.'_cf7_disable', array( $this, 'sanitize_checkbox' ) );

		$this->customize->add_control( $section.'_cf7_disable',
			array(
				'type'        => 'checkbox',
				'label'       => esc_html__('Hide contact form completely.', 'onesocial'),
				'section'     => $section.'_content',
				'description' => esc_html__('Check this box to hide contact form.', 'onesocial'),
			)
		);

		// Contact Text
		$this->add_setting( $section.'_text', array( $this, 'sanitize_text' ) );

		$this->customize->add_control( new CCLUK_Editor_Custom_Control(
			$this->customize,
			$section.'_text',
			array(
				'label'     	=> esc_html__('Contact Text', 'onesocial'),
				'section' 		=> $section.'_content',
				'description'   => '',
			)
		));

		// hr
		$this->add_setting( $section.'_text_hr', array( $this, 'sanitize_text' ) );
		$this->customize->add_control( new CCLUK_Misc_Control( $this->customize, $section.'_text_hr',
			array(
				'section'     => $section.'_content',
				'type'        => 'hr'
			)
		));

		// Address Box
		$this->add_setting( 
			$section.'_address_title',
			'sanitize_text_field'
		);

		$this->customize->add_control( $section.'_address_title',
			array(
				'label'     	=> esc_html__('Contact Box Title', 'onesocial'),
				'section' 		=> $section.'_content',
				'description'   => '',
			)
		);

		// Contact Text
		$this->add_setting( 
			$section.'_address',
			array( $this, 'sanitize_text' )
		);

		$this->customize->add_control( $section.'_address',
			array(
				'label'     => esc_html__('Address', 'onesocial'),
				'section' 		=> $section.'_content',
				'description'   => '',
			)
		);

		// Contact Phone
		$this->add_setting( 
			$section.'_phone',
			array( $this, 'sanitize_text' )
		);

		$this->customize->add_control( $section.'_phone',
			array(
				'label'     	=> esc_html__('Phone', 'onesocial'),
				'section' 		=> $section.'_content',
				'description'   => '',
			)
		);

		// Contact Email
		$this->add_setting( 
			$section.'_email',
			'sanitize_email'
		);

		$this->customize->add_control( $section.'_email',
			array(
				'label'     	=> esc_html__('Email', 'onesocial'),
				'section' 		=> $section.'_content',
				'description'   => '',
			)
		);

		// Contact Fax
		$this->add_setting( 
			$section.'_fax',
			array( $this, 'sanitize_text' )
		);

		$this->customize->add_control( $section.'_fax',
			array(
				'label'     	=> esc_html__('Fax', 'onesocial'),
				'section' 		=> $section.'_content',
				'description'   => '',
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