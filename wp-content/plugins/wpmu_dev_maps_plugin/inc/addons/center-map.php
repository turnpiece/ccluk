<?php
/*
Plugin Name: Center map on location
Description: Adds a "Center" button to the Map Editor to manually set the center position.<br />Optionally the map center can be defined with a shortcode attribute that takes a comma-separated latitude/longitude pair.
Example:     [map id="12" center="45.359,20.412"]
Plugin URI:  http://premium.wpmudev.org/project/wordpress-google-maps-plugin
Version:     1.1
Author:      Ve Bailovity (Incsub)
*/


class Agm_Cm_AdminPages {

	private function __construct() {}

	public static function serve() {
		$me = new Agm_Cm_AdminPages();
		$me->_add_hooks();
	}

	private function _add_hooks() {
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
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/admin/center-map.min.js' );
	}

	public function sanitize_options( $options, $raw ) {
		if ( isset( $raw['map_center'] ) ) {
			$options['map_center'] = $raw['map_center'];
		}
		return $options;
	}
}


class Agm_Cm_PublicPages {

	private function __construct() {}

	public static function serve() {
		$me = new Agm_Cm_PublicPages();
		$me->_add_hooks();
	}

	private function _add_hooks() {
		add_action(
			'agm-user-scripts',
			array( $this, 'load_scripts' )
		);

		add_filter(
			'agm-load-options',
			array( $this, 'prepare_for_load' ),
			10, 2
		);

		add_filter(
			'agm-shortcode-defaults',
			array( $this, 'set_attribute_defaults' )
		);

		add_filter(
			'agm-shortcode-overrides',
			array( $this, 'process_overrides' ),
			10, 2
		);
	}

	public function load_scripts() {
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/user/center-map.min.js', 'front' );
	}

	public function prepare_for_load( $options, $raw ) {
		if ( isset( $raw['map_center'] ) ) {
			$options['map_center'] = $raw['map_center'];
		}
		return $options;
	}

	public function set_attribute_defaults( $atts ) {
		$atts['center'] = false;
		return $atts;
	}

	public function process_overrides( $overrides, $atts ) {
		if ( @$atts['center'] ) {
			$overrides['center'] = $this->_convert_to_point( $atts['center'] );
		}
		return $overrides;
	}

	protected function _convert_to_point( $src ) {
		if ( empty( $src ) ) {
			return false;
		}

		$coords = explode( ',', $src );

		// Validate pair
		if ( count( $coords ) != 2 ) {
			return false;
		}

		return array(
			'latitude' => trim( $coords[0] ),
			'longitude' => trim( $coords[1] ),
		);
	}
}


if ( is_admin() ) {
	Agm_Cm_AdminPages::serve();
} else {
	Agm_Cm_PublicPages::serve();
}