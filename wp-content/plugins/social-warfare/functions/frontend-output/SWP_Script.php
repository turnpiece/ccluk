<?php

/**
 * Register and enqueue plugin scripts and styles.
 *
 * @package   SocialWarfare\Functions
 * @copyright Copyright (c) 2018, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0
 */
class SWP_Script {

	/**
	 * The magic method used to instatiate our class and queue up
	 * all of the add_action and add_filter functions.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  none
	 * @return none
	 */
    public function __construct() {

        // Queue up our footer hook function
        add_filter( 'swp_footer_scripts' , array($this, 'nonce') );
        add_filter( 'swp_footer_scripts' , array($this, 'frame_buster' ) );

        // Queue up our footer hook function
        add_filter( 'swp_footer_scripts' , array($this, 'click_tracking' ) );

		// Queue up the Social Warfare scripts and styles
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts') );
        add_action( 'admin_enqueue_scripts', array($this, 'enqueue_admin_scripts') );

        // Queue up our hook function
        add_action( 'wp_footer' , array($this, 'footer_functions') , 99 );

        if ( method_exists( 'LiteSpeed_Cache_API', 'esi_enabled' ) && LiteSpeed_Cache_API::esi_enabled() ) :
        	LiteSpeed_Cache_API::hook_tpl_esi('swp_esi', array($this, 'hook_esi') );
        endif;
    }


    /**
     * Helper function for getting the script/style `.min` suffix for minified files.
     *
     * @since  1.0.0
     * @access public
     * @return string
     *
     */
    public static function get_suffix() {
    	$debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;

    	if ( _swp_is_debug( 'script' ) ) {
    		$debug = true;
    	}

    	$enabled = (bool) apply_filters( 'swp_enable_suffix', ! $debug );

    	return $enabled ? '.min' : '';
    }


    /**
     * Load front end scripts and styles.
     *
     * @since  1.0.0
     * @access public
     * @global $swp_user_options
     * @return void
     */
    public function enqueue_scripts() {
    	global $swp_user_options;

    	$suffix = SWP_Script::get_suffix();

    	wp_enqueue_style(
    		'social_warfare',
    		SWP_PLUGIN_URL . "/css/style{$suffix}.css",
    		array(),
    		SWP_VERSION
    	);

    	wp_enqueue_script(
    		'social_warfare_script',
    		SWP_PLUGIN_URL . "/js/script{$suffix}.js",
    		array( 'jquery' ),
    		SWP_VERSION,
    		true
    	);

    }


    /**
     * Load admin scripts and styles.
     *
     * @since  1.0.0
     * @access public
     * @param  string $screen The ID of the current admin screen.
     * @return void
     *
     */
    public function enqueue_admin_scripts( $screen ) {

        $this->enqueue_scripts();

    	$suffix = SWP_Script::get_suffix();

    	wp_enqueue_style(
    		'social_warfare_admin',
    		SWP_PLUGIN_URL . "/css/admin{$suffix}.css",
    		array(),
    		SWP_VERSION
    	);

    	wp_enqueue_script(
    		'social_warfare_admin_script',
    		SWP_PLUGIN_URL . "/js/admin{$suffix}.js",
    		array( 'jquery' ),
    		SWP_VERSION
    	);

    	wp_localize_script( 'social_warfare_admin_script', 'swp_localize_admin',
    		array(
    			'swp_characters_remaining' => __( 'Characters Remaining', 'social-warfare' ),
    		)
    	);
    }


    /**
     * Queue up our javscript for options and whatnot
     * @since 1.4.0
     * @param Void
     * @return Void. Echo results directly to the screen.
     *
     */
    public function footer_functions() {
    	global $swp_user_options;

    	// Fetch a few variables.
    	$info['postID']           = get_the_ID();
    	$info['swp_user_options'] = $swp_user_options;
    	$info['footer_output']    = '';

    	// Pass the array through our custom filters.
    	$info = apply_filters( 'swp_footer_scripts' , $info );

    	// If we have output, output it.
    	if ( $info['footer_output'] ) {
    		echo '<script type="text/javascript">';
    		echo $info['footer_output'];
    		echo '</script>';
    	}
    }


    /**
     * Enable click tracking in Google Analytics.
     *
     * @since  1.4
     * @access public
     * @param  array $info An array of footer script information.
     * @return array $info A modified array of footer script information.
     *
     */
    public function click_tracking( $info ) {
    	if ( $info['swp_user_options']['swp_click_tracking'] ) {
    		$info['footer_output'] .= 'var swpClickTracking = true;';
    	} else {
    		$info['footer_output'] .= 'var swpClickTracking = false;';
    	}

    	return $info;
    }


    /**
     * Create a nonce for added security
     *
     * @since  2.1.4
     * @access public
     * @param  array $info An array of footer script information.
     * @return array $info A modified array of footer script information.
     *
     */
    public function nonce( $info ) {

    	// To make sure LSCWP ESI is on
    	if( method_exists( 'LiteSpeed_Cache_API', 'esi_enabled' ) && LiteSpeed_Cache_API::esi_enabled() ) {
    		// To make sure is using the compatible API version
    		if ( method_exists( 'LiteSpeed_Cache_API', 'v' ) && LiteSpeed_Cache_API::v( '1.3' ) ) {
    			// Let's turn this block to ESI and return
    			$info['footer_output'] .= LiteSpeed_Cache_API::esi_url( 'swp_esi', 'Social Warfare', array(), 'default', true );
    			return $info;
    		}
    	}

    	// Create a nonce
    	$info['footer_output'] .= ' var swp_nonce = "'.wp_create_nonce().'";';
    	return $info;
    }


	/**
	 * Add LiteSpeed ESI hook for nonce cache
	 *
	 * @access public
	 * @param  None
	 * @return None
	 *
	 */
	public function hook_esi() {
		echo ' var swp_nonce = "'.wp_create_nonce().'";';
		exit;
	}


    /**
     * The Frame Buster Option
     *
     * @since  1.4.0
     * @access public
     * @param  array $info An array of footer script information.
     * @return array $info A modified array of footer script information.
     *
     */
    public function frame_buster( $info ) {

    	global $swp_user_options;

    	if ( true === $swp_user_options['frame_buster'] ) :
    		$info['footer_output'] .= PHP_EOL . 'function parentIsEvil() { var html = null; try { var doc = top.location.pathname; } catch(err){ }; if(typeof doc === "undefined") { return true } else { return false }; }; if (parentIsEvil()) { top.location = self.location.href; };var url = "' . get_permalink() . '";if(url.indexOf("stfi.re") != -1) { var canonical = ""; var links = document.getElementsByTagName("link"); for (var i = 0; i < links.length; i ++) { if (links[i].getAttribute("rel") === "canonical") { canonical = links[i].getAttribute("href")}}; canonical = canonical.replace("?sfr=1", "");top.location = canonical; console.log(canonical);};';
    	endif;

    	return $info;
    }
}
