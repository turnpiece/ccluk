<?php
/*
Plugin Name: Traffic overlay
Description: Gives you the possibility to show the traffic overlay on your map. You can either enable it "Map Options", or with the shortcode attribute like so: <code>show_traffic="true"</code>.<br />E.g. <code>[map id="1" show_traffic="true"]</code>
Plugin URI:  http://premium.wpmudev.org/project/wordpress-google-maps-plugin
Version:     1.0
Author:      Philipp Stracker (Incsub)
*/

class Agm_Traffic_AdminPages {

	private function __construct() {}

	/**
	 * Initialize the Admin interface.
	 *
	 * @since 1.0
	 */
	public static function serve() {
		$me = new Agm_Traffic_AdminPages();
		$me->_add_hooks();
	}

	/**
	 * Attach the hooks for the admin-page
	 *
	 * @since 1.0
	 */
	private function _add_hooks() {
		// Load the javascript that provides new map options.
		add_action(
			'agm-admin-scripts',
			array( $this, 'load_scripts' )
		);

		// Needed to save and load new setting in map-options.
		add_filter(
			'agm-save-options',
			array( $this, 'sanitize_map_options' ),
			10, 2
		);

		add_filter(
			'agm-load-options',
			array( $this, 'sanitize_map_options' ),
			10, 2
		);
	}

	/**
	 * Load the javascript file that provides new map options.
	 *
	 * @since 1.0
	 */
	public function load_scripts() {
		$data = array(
			'lang' => array(
				'show_traffic' => __( 'Show traffic', AGM_LANG ),
			),
		);

		lib3()->ui->data( '_agmTraffic', $data );
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/admin/traffic.min.js' );
	}

	/**
	 * Sanitize the map options before writing to database or after reading from
	 * database. "$raw" contains either the $_POST values or the deserialized
	 * Array from database.
	 *
	 * @since 1.0
	 */
	function sanitize_map_options( $options, $raw ) {
		if ( isset( $raw['show_traffic'] ) ) {
			$options['show_traffic'] = agm_positive_values( $raw['show_traffic'] );
		}
		return $options;
	}
}



class Agm_Traffic_UserPages {

	private function __construct() {}

	/**
	 * Initialize the traffic overlay on frontend of the website.
	 *
	 * @since 1.0
	 */
	public static function serve() {
		$me = new Agm_Traffic_UserPages();
		$me->_add_hooks();
	}

	/**
	 * Setup all the WordPress hooks to get the overlay working.
	 *
	 * @since 1.0
	 */
	private function _add_hooks() {
		// Load the javascript that hooks the overlay to the map.
		add_action(
			'agm-user-scripts',
			array( $this, 'load_scripts' )
		);

		// Sanitize the map options before passing them to javascript.
		add_filter(
			'agm-load-options',
			array( $this, 'prepare_for_load' ),
			10, 2
		);

		// Handling shortcode attributes.
		add_filter(
			'agm-shortcode-defaults',
			array( $this, 'attributes_defaults' )
		);
		add_filter(
			'agm-shortcode-overrides',
			array( $this, 'overrides_process' ),
			10, 2
		);
	}

	/**
	 * Load the javascript that hooks the overlay to the map.
	 *
	 * @since 1.0
	 */
	public function load_scripts() {
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/user/traffic.min.js', 'front' );
	}

	/**
	 * Sanitize the map options before passing them to javascript.
	 * This makes sure the traffic-options are correct, even when not explitily
	 * defined by the user.
	 *
	 * @since 1.0
	 */
	public function prepare_for_load( $options, $raw ) {
		if ( isset( $raw['show_traffic'] ) ) {
			$options['show_traffic'] = agm_positive_values( $raw['show_traffic'] );
		}
		return $options;
	}

	/**
	 * Handling shortcode attributes: Define the default settings.
	 *
	 * @since 1.0
	 */
	public function attributes_defaults( $defaults ) {
		$defaults['show_traffic'] = false;
		return $defaults;
	}

	/**
	 * Handling shortcode attributes: See if user did specify attribute in the
	 * map shortcode.
	 *
	 * @since 1.0
	 */
	public function overrides_process( $overrides, $atts ) {
		if ( @$atts['show_traffic'] ) {
			$overrides['show_traffic'] = agm_positive_values( $atts['show_traffic'] );
		}
		return $overrides;
	}

}


if ( is_admin() ) {
	Agm_Traffic_AdminPages::serve();
} else {
	Agm_Traffic_UserPages::serve();
}