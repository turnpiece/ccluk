<?php
/*
Plugin Name: Additional Behaviors
Description: Exposes additional map default behaviors as global map options:<br>- Click on item in marker-list opens the detail popup<br>- Click on directions link scrolls to the directions form
Plugin URI:  http://premium.wpmudev.org/project/wordpress-google-maps-plugin
Version:     1.0
Author:      Ve Bailovity (Incsub)
*/

class Agm_Mab_AdditionalBehaviors {

	private function __construct() {}

	public static function serve() {
		$me = new Agm_Mab_AdditionalBehaviors();
		$me->_add_hooks();
	}

	private function _add_hooks() {
		add_action(
			'agm-user-scripts',
			array( $this, 'load_scripts' )
		);
		add_filter(
			'agm_google_maps-javascript-data_object-user',
			array( $this, 'add_behaviors_data' )
		);

		add_action(
			'agm_google_maps-options-plugins_options',
			array( $this, 'register_settings' )
		);
	}

	public function load_scripts() {
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/user/additional-behaviors.min.js', 'front' );
	}

	public function add_behaviors_data( $data ) {
		$data['additional_behaviors'] = $this->_get_options();
		return $data;
	}

	public function register_settings() {
		add_settings_section(
			'agm_google_maps_mab',
			__( 'Additional Behaviors', AGM_LANG ),
			array( $this, 'create_section' ),
			'agm_google_maps_options_page'
		);
		add_settings_field(
			'agm_google_maps_list_kmls',
			__( 'Behaviors', AGM_LANG ),
			array( $this, 'create_behaviors_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps_mab'
		);
	}

	public function create_section() {
		?>
		<p>
			<em>
			<?php _e( 'This is where you can define additional global settings for your maps.', AGM_LANG ); ?>
			</em>
		</p>
		<?php
	}

	public function create_behaviors_box() {
		$opts = $this->_get_options();
		?>
		<label for="agm_google_maps-mab-marker_click_popup">
			<input type="hidden"
				value=""
				name="agm_google_maps[mab][marker_click_popup]" />
			<input type="checkbox"
				value="1"
				id="agm_google_maps-mab-marker_click_popup"
				name="agm_google_maps[mab][marker_click_popup]"
				<?php checked( $opts['marker_click_popup'], true ); ?> />
			&nbsp
			<?php _e( 'Click on item in marker-list opens the detail popup', AGM_LANG ); ?>
		</label>
		<br />

		<label for="agm_google_maps-mab-directions_click_scroll">
			<input type="hidden"
				value=""
				name="agm_google_maps[mab][directions_click_scroll]" />
			<input type="checkbox"
				value="1"
				id="agm_google_maps-mab-directions_click_scroll"
				name="agm_google_maps[mab][directions_click_scroll]"
				<?php checked( $opts['directions_click_scroll'], true ); ?> />
			&nbsp
			<?php _e( 'Click on directions link scrolls to the directions form', AGM_LANG ); ?>
		</label>
		<br />
		<?php
	}

	private function _get_options() {
		$opts = apply_filters( 'agm_google_maps-options', get_option( 'agm_google_maps' ) );
		$opts = isset( $opts['mab'] ) && $opts['mab'] ? $opts['mab'] : array();
		return wp_parse_args(
			$opts,
			array(
				'directions_click_scroll' => false,
				'marker_click_popup' => false,
			)
		);
	}

};

Agm_Mab_AdditionalBehaviors::serve();