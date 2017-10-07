<?php
/*
Plugin Name: Disable scroll
Description: Allows you to disable scroll on a map.<br/>You can either disable the scrolling (A) in "Map Options", or (B) via a shortcode attribute: <code>disable_scroll="true"</code>
Example:     [map id="1" disable_scroll="true"]
Plugin URI:  http://premium.wpmudev.org/project/wordpress-google-maps-plugin
Version:     1.0.1
Author:      Ve Bailovity (Incsub)
*/

class Agm_DZ_AdminPages {

	private function __construct() {}

	public static function serve() {
		$me = new Agm_DZ_AdminPages();
		$me->_add_hooks();
	}

	private function _add_hooks() {
		// Basic KML overlay funcitonality
		add_action(
			'agm-admin-scripts',
			array( $this, 'load_scripts' )
		);

		add_filter(
			'agm-save-options',
			array( $this, 'sanitize_options' ), 10, 2
		);

		add_filter(
			'agm-load-options',
			array( $this, 'sanitize_options' ), 10, 2
		);
	}

	public function load_scripts() {

		$data = array(
			'lang' => array(
				'disable_scroll' => __( 'Disable scroll', AGM_LANG ),
			),
		);

		lib3()->ui->data( '_agmDS', $data );
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/admin/disable-scroll.min.js' );
	}

	public function sanitize_options( $options, $raw ) {
		if ( isset( $raw['disable_scroll'] ) ) {
			$options['disable_scroll'] = agm_positive_values( $raw['disable_scroll'] );
		}
		return $options;
	}
};


class Agm_DZ_UserPages {

	private function __construct() {}

	public static function serve() {
		$me = new Agm_DZ_UserPages();
		$me->_add_hooks();
	}

	private function _add_hooks() {
		// Basic funcitonality
		add_action(
			'agm-user-scripts',
			array( $this, 'load_scripts' )
		);
		add_filter(
			'agm-load-options',
			array( $this, 'prepare_for_load' ),
			10, 2
		);

		// Disabling in shortcode attribute
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

	public function load_scripts() {
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/user/disable-scroll.min.js', 'front' );
	}

	public function prepare_for_load( $options, $raw ) {
		if ( isset( $raw['disable_scroll'] ) ) {
			$options['disable_scroll'] = agm_positive_values( $raw['disable_scroll'] );
		}
		return $options;
	}

	public function attributes_defaults( $defaults ) {
		$defaults['disable_scroll'] = null;
		return $defaults;
	}

	public function overrides_process( $overrides, $atts ) {
		if ( isset( $atts['disable_scroll'] ) && null !== $atts['disable_scroll'] ) {
			$overrides['disable_scroll'] = agm_positive_values( $atts['disable_scroll'] );
		}
		return $overrides;
	}

};


if ( is_admin() ) {
	Agm_DZ_AdminPages::serve();
} else {
	Agm_DZ_UserPages::serve();
}