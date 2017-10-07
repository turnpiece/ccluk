<?php
/**
 * Handles all Google Maps plugins.
 */
class AgmPluginsHandler {

	public static function init() {
		self::load_active_plugins();
	}

	public static function get_active_plugins() {
		$Active = null;

		if ( null === $Active ) {
			$Active = get_option( 'agm_activated_plugins' );
			$Active = $Active ? $Active : array();

			$version = get_option( 'agm_activated_plugins_ver', 1 );

			/**
			 * plugins-ver 2. Changed plugin names.
			 * @since 2.9
			 */
			if ( $version < 2 ) {
				foreach ( $Active as $ind => $name ) {
					$Active[$ind] = str_replace( '_', '-', $Active[$ind] );
					$Active[$ind] = preg_replace( '/^agm-/', '', $Active[$ind] );
				}

				// 'bp-member-map.php' was merged with 'bp-profile-maps-php'
				if ( in_array( 'bp-member-map', $Active ) && ! in_array( 'bp-profile-maps', $Active ) ) {
					$ind = array_search( 'bp-member-map', $Active );
					$Active[$ind] = 'bp-profile-maps';
				}

				// Some add-ons were renamed
				if ( in_array( 'map-additional-behavior', $Active ) && ! in_array( 'additional-behavior', $Active ) ) {
					$ind = array_search( 'map-additional-behavior', $Active );
					$Active[$ind] = 'additional-behavior';
				}
				if ( in_array( 'map-loading-message', $Active ) && ! in_array( 'loading-message', $Active ) ) {
					$ind = array_search( 'map-loading-message', $Active );
					$Active[$ind] = 'loading-message';
				}

				$active = self::validate_active_plugins( $Active );

				update_option( 'agm_activated_plugins', $Active );
				update_option( 'agm_activated_plugins_ver', '2' );
			}

			sort( $Active );
		}

		return $Active;
	}

	public static function load_active_plugins() {
		$active = self::get_active_plugins();

		foreach ( $active as $plugin ) {
			$path = self::plugin_to_path( $plugin );
			if ( ! file_exists( $path ) ) {
				continue;
			} else {
				@include_once $path;
			}
		}
	}

	public static function get_all_plugins() {
		static $All = null;
		if ( null === $All ) {
			$files = glob( AGM_ADDON_DIR . '*.php' );
			$files = $files ? $files : array();
			$All = array();
			foreach ( $files as $path ) {
				$All[] = pathinfo( $path, PATHINFO_FILENAME );
			}
		}
		return $All;
	}

	public static function plugin_to_path( $plugin ) {
		$plugin = str_replace( '/', '_', $plugin );
		return AGM_ADDON_DIR . $plugin . '.php';
	}

	public static function get_plugin_info( $plugin ) {
		$path = self::plugin_to_path( $plugin );
		$default_headers = array(
			'name'     => 'Plugin Name',
			'author'   => 'Author',
			'desc'     => 'Description',
			'url'      => 'Plugin URI',
			'version'  => 'Version',
			'requires' => 'Requires',
			'example'  => 'Example',
		);
		$headers = get_file_data( $path, $default_headers, 'plugin' );

		// @since 2.8.6.1
		if ( 'agm_map' == AgmMapModel::get_config( 'shortcode_map' ) ) {
			$headers['desc'] = str_replace(
				'[map ',
				'[agm_map ',
				$headers['desc']
			);
		}

		return $headers;
	}

	public static function activate_plugin( $plugin ) {
		$active = self::get_active_plugins();
		if ( ! in_array( $plugin, $active ) ) {
			$active[] = $plugin;
		}
		$active = self::validate_active_plugins( $active );
		return update_option( 'agm_activated_plugins', $active );
	}

	public static function deactivate_plugin( $plugin ) {
		$active = self::get_active_plugins();
		if ( in_array( $plugin, $active ) ) {
			$key = array_search( $plugin, $active );
			if ( false !== $key ) {
				unset( $active[$key] );
			}
		}
		$active = self::validate_active_plugins( $active );
		return update_option( 'agm_activated_plugins', $active );
	}

	/**
	 * Removes invalid plugins from the active-list and sorts the list.
	 *
	 * @since  2.9
	 * @param  array $active Active Add-ons.
	 * @return array Active Add-ons.
	 */
	protected static function validate_active_plugins( $active ) {
		$all = self::get_all_plugins();

		for ( $key = count( $active ); $key >= 0; $key -= 1 ) {
			if ( ! isset( $active[$key] ) ) { continue; }
			if ( ! in_array( $active[$key], $all ) ) {
				unset( $active[$key] );
			}
		}
		sort( $active );
		return $active;
	}
}