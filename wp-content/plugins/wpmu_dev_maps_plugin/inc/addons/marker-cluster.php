<?php
/*
Plugin Name: Marker cluster
Description: Cleans up your maps by grouping nearby markers into clusters. This will automatically affect all maps when activated.
Plugin URI:  http://premium.wpmudev.org/project/wordpress-google-maps-plugin
Version:     1.0
Author:      Ve Bailovity (Incsub)
*/

class Agm_Mc_UserPages {

	private function __construct() {}

	public static function serve() {
		$me = new Agm_Mc_UserPages();
		$me->_add_hooks();
	}

	private function _add_hooks() {
		add_action(
			'agm-user-scripts',
			array( $this, 'load_scripts' )
		);
	}

	public function load_scripts() {
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/external/markerclusterer_packed.min.js', 'front' );
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/user/marker-cluster.min.js', 'front' );
	}
};

if ( ! is_admin() ) {
	Agm_Mc_UserPages::serve();
}