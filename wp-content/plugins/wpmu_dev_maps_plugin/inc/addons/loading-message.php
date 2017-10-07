<?php
/*
Plugin Name: Map loading message
Description: Gives your maps a customizable loading message via shortcode attribute "loading_message".
Example:     [map id="1" loading_message="Please wait while map is loading"]
Plugin URI:  http://premium.wpmudev.org/project/wordpress-google-maps-plugin
Version:     1.0
Author:      Ve Bailovity (Incsub)
*/

class Agm_Mlm_Pages {

	private $_data;

	private function __construct() {
		$this->_data = $this->_get_options();
	}

	public static function serve() {
		$me = new Agm_Mlm_Pages();
		$me->_add_hooks();
	}

	private function _add_hooks() {
		add_action(
			'agm-shortcode-defaults',
			array( $this, 'attribute_defaults' )
		);
		add_filter(
			'agm_google_maps-autogen_map-shortcode_attributes',
			array( $this, 'autogen_attributes' )
		);
		add_filter(
			'agm-shortcode-overrides',
			array( $this, 'process_overrides' ),
			10, 2
		);
		add_filter(
			'agm_google_maps-bp_profile_map-all_users_overrides',
			array( $this, 'bp_profiles_attributes' )
		);

		add_filter(
			'agm_google_maps-shortcode-tag_content',
			array( $this, 'apply_loading_message' ),
			10, 2
		);

		add_action(
			'agm_google_maps-options-plugins_options',
			array( $this, 'register_settings' )
		);

		if ( $this->_data['cached_map'] ) {
			add_action(
				'agm-user-scripts',
				array( $this, 'load_scripts' )
			);
			add_action(
				'wp_ajax_agm-mlm-store-cache',
				array( $this, 'store_cache' )
			);
			add_action(
				'wp_ajax_nopriv_agm-mlm-store-cache',
				array( $this, 'store_cache' )
			);
		}
	}

	public function store_cache() {
		$data = stripslashes_deep( $_POST );
		$map_id = ! empty( $data['map_id'] ) ? (int) $data['map_id'] : false;
		$cache = ! empty( $data['cache'] ) ? $data['cache'] : false;
		$sent_key = ! empty( $data['key'] ) ? $data['key'] : false;

		if ( empty( $cache ) || empty( $sent_key ) ) {
			die(); // Invalid request
		}

		$local_key = $this->_get_map_sig( $map_id );
		if ( $sent_key != $local_key ) {
			die(); // Key mismatch
		}

		$this->_store_cache( $map_id, $cache );
		die();
	}

	public function load_scripts() {
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/user/message.min.js', 'front' );
	}

	public function apply_loading_message( $msg, $map ) {
		if ( isset( $map['loading_message'] ) ) {
			return $this->_create_loading_message( $map['loading_message'], $map );
		}
		return $msg;
	}

	public function attribute_defaults( $args ) {
		$args['loading_message'] = false;
		return $args;
	}

	public function bp_profiles_attributes( $args ) {
		$args['loading_message'] = $this->_data['bp_profile'];
		return $args;
	}

	public function process_overrides( $overrides, $args ) {
		if ( isset( $args['loading_message'] ) && $args['loading_message'] ) {
			$overrides['loading_message'] = $args['loading_message'];
		} else if ( $this->_data['all'] ) {
			$overrides['loading_message'] = $this->_data['all'];
		}
		return $overrides;
	}

	public function autogen_attributes( $args ) {
		$args['loading_message'] = $this->_data['autogen'];
		return $args;
	}

	public function register_settings() {
		add_settings_section(
			'agm_google_maps_mlm',
			__( 'Map loading message', AGM_LANG ),
			array( $this, 'create_section_notice' ),
			'agm_google_maps_options_page'
		);
		add_settings_field(
			'agm_google_maps_mlm_auto_assign',
			__( 'Add loading message to these maps', AGM_LANG ),
			array( $this, 'create_auto_assign_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps_mlm'
		);
	}

	public function create_section_notice() {
		?><em>
			<?php __(
				'You add the loading message in your shortcodes with ' .
				'<code>loading_message="My message"</code> shortcode ' .
				'attribute. You can also specify loading messages for ' .
				'your other maps here.', AGM_LANG
			); ?>
		</em>
		<?php
	}

	public function create_auto_assign_box() {
		echo '' .
			'<label for="agm-mlm-autogen">' . __( 'Auto-generated maps loading message', AGM_LANG ) . '</label>' .
			'<input type="text" class="widefat" id="agm-mlm-autogen" name="agm_google_maps[mlm][autogen]" value="' . esc_attr( $this->_data['autogen'] ) . '" />' .
		'<br />';
		if ( class_exists( 'Agm_Bp_Pm_AdminPages' ) && defined( 'BP_VERSION' ) ) {
			echo '' .
				'<label for="agm-mlm-bp_profile">' . __( 'BuddyPress member directory map', AGM_LANG ) . '</label>' .
				'<input type="text" class="widefat" id="agm-mlm-bp_profile" name="agm_google_maps[mlm][bp_profile]" value="' . esc_attr( $this->_data['bp_profile'] ) . '" />' .
			'<br />';
		}
		echo '' .
			'<label for="agm-mlm-all">' . __( 'All maps loading message', AGM_LANG ) . '</label>' .
			'<input type="text" class="widefat" id="agm-mlm-all" name="agm_google_maps[mlm][all]" value="' . esc_attr( $this->_data['all'] ) . '" />' .
		'<br />';
		echo '' .
			'<input type="hidden" name="agm_google_maps[mlm][cached_map]" value="" />' .
			'<input type="checkbox" id="agm-mlm-cached_map" name="agm_google_maps[mlm][cached_map]" value="1" ' . checked( $this->_data['cached_map'], 1, false ) . '" />' .
			'&nbsp;' .
			'<label for="agm-mlm-cached_map">' . __( 'Use map tiles caching', AGM_LANG ) . '</label>' .
		'<br />';
	}

	private function _get_options() {
		$default_msg = __( 'Map is loading, please hold on', AGM_LANG );
		$opts = apply_filters( 'agm_google_maps-options-mlm', get_option( 'agm_google_maps' ) );
		$opts = isset( $opts['mlm'] ) && $opts['mlm'] ? $opts['mlm'] : array();
		return wp_parse_args(
			$opts,
			array(
				'autogen'    => $default_msg,
				'bp_profile' => $default_msg,
				'all'        => $default_msg,
				'cached_map' => true,
			)
		);
	}

	private function _create_loading_message( $str, $map ) {
		$map_id = ! empty( $map['id'] ) ? $map['id'] : false;

		if ( $this->_data['cached_map'] ) {
			$cache = $map_id
				? $this->_load_cache( $map_id )
				: false
			;
			if ( $cache ) {
				return '<div class="mlm-cached">' . $cache . '</div>';
			}
		}

		if ( ! $str ) {
			return false;
		}
		$key = $this->_data['cached_map'] && $map_id
			? 'data-mlm-cache-key="' . esc_attr( $this->_get_map_sig( $map_id ) ) . '"'
			: ''
		;
		return '<div class="agm_google_maps-loading_message" ' . $key . '">' . $str . '</div>';
	}

	private function _get_map_sig( $map_id ) {
		$model = new AgmMapModel();
		$map = $model->get_map( $map_id );
		return md5( serialize( $map ) . AUTH_SALT . NONCE_KEY );
	}

	private function _get_cache_key( $map_id ) {
		return substr( 'agm-mlm-cache-' . md5( $map_id ), 0, 40 );
	}

	private function _store_cache( $map_id, $cache ) {
		return set_transient( $this->_get_cache_key( $map_id ), $cache, DAY_IN_SECONDS );
	}

	private function _load_cache( $map_id ) {
		return get_transient( $this->_get_cache_key( $map_id ) );
	}
}

Agm_Mlm_Pages::serve();