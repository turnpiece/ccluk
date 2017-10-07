<?php
/*
Plugin Name: Hide auto-created Markers
Description: Cleans up your maps by hiding the map markers for maps auto-generated from custom fields.
Example:     [map id="1" hide_map_markers="true"]
Plugin URI:  http://premium.wpmudev.org/project/wordpress-google-maps-plugin
Version:     1.0
Author:      Ve Bailovity (Incsub)
*/

class Agm_Hacm_UserPages {

	private function __construct() {
	}

	public static function serve() {
		$me = new Agm_Hacm_UserPages();
		$me->_add_hooks();
	}

	private function _add_hooks() {
		add_filter(
			'agm_google_maps-autogen_map-shortcode_attributes',
			array($this, 'autogen_hide')
		);
		add_filter(
			'agm-shortcode-defaults',
			array($this, 'attributes_defaults')
		);
		add_filter(
			'agm-shortcode-overrides',
			array($this, 'overrides_process'),
			10, 2
		);
		add_action(
			'agm-user-scripts',
			array($this, 'load_scripts')
		);
	}

	public function load_scripts() {
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/user/hide-markers.min.js', 'front' );
	}

	public function autogen_hide( $args ) {
		$args['hide_map_markers'] = 'true';
		return $args;
	}

	public function attributes_defaults( $defaults ) {
		$defaults['hide_map_markers'] = false;
		return $defaults;
	}

	public function overrides_process( $overrides, $atts ) {
		if ( agm_positive_values( @$atts['hide_map_markers'] ) ) {
			$overrides['hide_map_markers'] = true;
		}
		return $overrides;
	}
}

if ( ! is_admin() ) {
	Agm_Hacm_UserPages::serve();
}