<?php

if( !function_exists ('rescue_shortcodes_scripts') ) :

	function rescue_shortcodes_scripts() {

		wp_enqueue_script( 'jquery' );

		/**
		 * Scripts
		 */
		wp_register_script('rescue_wow', plugin_dir_url( __FILE__ ) . 'js/wow.min.js', array ( 'jquery'), '1.1.2', true );
		wp_register_script('rescue_wow_init', plugin_dir_url( __FILE__ ) . 'js/rescue_wow.js', array ( 'jquery' ), '1.0', true );
		wp_register_script('rescue_tabs', plugin_dir_url( __FILE__ ) . 'js/rescue_tabs.js', array ( 'jquery', 'jquery-ui-tabs'), '1.0', true );
		wp_register_script('rescue_donation_tabs', plugin_dir_url( __FILE__ ) . 'js/rescue_donation_tabs.js', array ( 'jquery', 'jquery-ui-tabs'), '1.0', true );
		wp_register_script('rescue_toggle', plugin_dir_url( __FILE__ ) . 'js/rescue_toggle.js', 'jquery', '1.0', true );
		wp_register_script('rescue_accordion', plugin_dir_url( __FILE__ ) . 'js/rescue_accordion.js', array ( 'jquery', 'jquery-ui-accordion'), '1.0', true );
		wp_register_script('rescue_googlemap',  plugin_dir_url( __FILE__ ) . 'js/rescue_googlemap.js', array('jquery'), '1.0', true );
		wp_register_script('rescue_progressbar', plugin_dir_url( __FILE__ ) . 'js/rescue_progressbar.js', array ( 'jquery' ), '1.0', true );
		wp_register_script('rescue_waypoints', plugin_dir_url( __FILE__ ) . 'js/waypoints.min.js', array ( 'jquery' ), '1.0', true );

		/**
		 * Stylesheets
		 */
		wp_register_style('rescue_animate', plugin_dir_url( __FILE__ ) . 'css/animate.min.css', array(), '3.5.1', 'all' );
		wp_enqueue_style( 'font-awesome', plugin_dir_url( __FILE__ ) . 'fonts/font-awesome.min.css', array(), '4.7', 'all' );
		wp_enqueue_style('rescue_shortcode_styles', plugin_dir_url( __FILE__ ) . 'css/rescue_shortcodes_styles.css' );

	}
	add_action('wp_enqueue_scripts', 'rescue_shortcodes_scripts');

endif;

if( !function_exists ('rescue_shortcodes_admin_scripts') ) :

	add_action( 'admin_enqueue_scripts', 'rescue_shortcodes_admin_scripts' );
	/**
	 * Register the shortcode button script for the modal window on posts
	 */
	function rescue_shortcodes_admin_scripts() {
		wp_register_script('rescue_shortcode_buttons', plugin_dir_url( __FILE__ ) . 'js/shortcode-buttons.js', array ( 'jquery' ), '1.0', true );
	}

endif;
