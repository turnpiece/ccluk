<?php
/**
 * Shipper tasks: import, setup postprocessing task.
 *
 * Fires post-import, used to flush caches etc.
 *
 * @package shipper
 */

/**
 * Shipper import remote scrub class
 */
class Shipper_Task_Import_Postprocess extends Shipper_Task_Import {

	/**
	 * Gets import task label
	 *
	 * @return string
	 */
	public function get_work_description() {
		return __( 'Postprocess the new install', 'shipper' );
	}

	/**
	 * Task runner method
	 *
	 * Returns (bool)true on completion.
	 *
	 * @param array $args Not used.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$this->postprocess_hummingbird();
		$this->postprocess_elementor();
		$this->postprocess_theme_and_plugins();
		$this->postprocess_set_package_size();
		$this->postprocess_object_cache();
		$this->postprocess_maybe_move_sitemeta_to_options_table();

		return true; // One and done.
	}

	/**
	 * Flushes all object caches
	 *
	 * @return bool
	 */
	public function postprocess_object_cache() {
		Shipper_Helper_Log::write( 'Flushing caches' );
		shipper_flush_cache();

		return true;
	}

	/**
	 * Postprocesses Hummingbird caches
	 *
	 * @return bool
	 */
	public function postprocess_hummingbird() {
		if ( ! class_exists( 'WP_Hummingbird' ) ) {
			return false;
		}

		Shipper_Helper_Log::write( __( 'Detected Hummingbird, flushing caches', 'shipper' ) );

		$hummingbird = WP_Hummingbird::get_instance();
		foreach ( $hummingbird->core->modules as $module ) {
			if ( ! $module->is_active() ) {
				continue;
			}
			Shipper_Helper_Log::write(
				sprintf(
					/* translators: module name. */
					__( 'Flushing caches for %s', 'shipper' ),
					get_class( $module )
				)
			);
			$module->clear_cache();
		}

		return true;
	}

	/**
	 * Postprocesses Elementor caches
	 *
	 * @return bool
	 */
	public function postprocess_elementor() {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return false;
		}

		try {
			\Elementor\Plugin::$instance->settings->update_css_print_method();
		} catch ( Exception $e ) {
			Shipper_Helper_Log::write(
				sprintf(
					/* Translators: %s: get error message. */
					__( 'Detected unknown Elementor version, flushing caches not possible %s', 'shipper' ),
					$e->getMessage()
				)
			);

			return false;
		}
		Shipper_Helper_Log::write( __( 'Detected Elementor, flushing caches', 'shipper' ) );

		return true;
	}

	/**
	 * Postprocess theme and plugins
	 *
	 * @since 1.1.4
	 *
	 * @return void
	 */
	public function postprocess_theme_and_plugins() {
		$manifest      = $this->get_manifest();
		$site_info     = $manifest->get( 'site_info' );
		$is_multi_site = 'whole_network' === $manifest->get( 'network_type' );

		$plugins     = ! empty( $site_info['plugins'] ) ? maybe_unserialize( $site_info['plugins'] ) : array();
		$theme       = ! empty( $site_info['template'] ) ? $site_info['template'] : '';
		$child_theme = ! empty( $site_info['stylesheet'] ) ? $site_info['stylesheet'] : '';

		Shipper_Helper_Log::write( __( 'Finally updating active theme and plugins', 'shipper' ) );

		$this->setup_theme( $theme, $child_theme );
		$this->setup_plugins( $plugins, $is_multi_site );
	}

	/**
	 * Setup active theme
	 *
	 * @since 1.1.4
	 *
	 * @param string $theme Theme name.
	 * @param string $child_theme Child Theme name.
	 */
	public function setup_theme( $theme, $child_theme ) {
		$existing_themes = wp_get_themes();
		$existing_themes = is_array( $existing_themes ) ? array_keys( $existing_themes ) : array();

		/* Translators: %s: theme name. */
		Shipper_Helper_Log::write( sprintf( __( 'Found themes from manifest.json: %s', 'shipper' ), $child_theme ) );

		/* Translators: %s: theme name. */
		Shipper_Helper_Log::write( sprintf( __( 'Available themes in wp-content/themes: %s', 'shipper' ), $this->convert_array_to_string( $existing_themes ) ) );

		if ( in_array( $child_theme, $existing_themes, true ) ) {
			/* Translators: %s: theme name. */
			Shipper_Helper_Log::write( sprintf( __( 'Setting this theme as active: %s', 'shipper' ), $theme ) );
			update_option( 'template', $theme );
			update_option( 'stylesheet', $child_theme );
		} else {
			$theme = is_array( $existing_themes ) ? array_rand( array_flip( $existing_themes ) ) : '';
			/* Translators: %s: theme name. */
			Shipper_Helper_Log::write( sprintf( __( 'The active theme from manifest.json is not found in wp-content/themes. So trying to activate a random theme: %s', 'shipper' ), $theme ) );
			update_option( 'template', $theme );
			update_option( 'stylesheet', $theme );
		}
	}

	/**
	 * Setup active plugins
	 *
	 * @since 1.1.4
	 *
	 * @param array $plugins List of plugins.
	 * @param bool  $is_multi_site Is it multisite.
	 */
	public function setup_plugins( $plugins, $is_multi_site = false ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$existing_plugins = get_plugins();
		$existing_plugins = is_array( $existing_plugins ) ? array_keys( $existing_plugins ) : array();

		/* Translators: %s: plugin name. */
		Shipper_Helper_Log::write( sprintf( __( 'Found plugins from manifest.json: %s', 'shipper' ), $this->convert_array_to_string( $plugins ) ) );

		/* Translators: %s: plugin name. */
		Shipper_Helper_Log::write( sprintf( __( 'Available plugins in wp-content/plugins: %s', 'shipper' ), $this->convert_array_to_string( $existing_plugins ) ) );

		$active_plugins = array_intersect( $plugins, $existing_plugins );

		/* Translators: %s: plugin name. */
		Shipper_Helper_Log::write( sprintf( __( 'Setting these plugins as active: %s', 'shipper' ), $this->convert_array_to_string( $active_plugins ) ) );

		if ( ! empty( $active_plugins ) ) {
			update_option( 'active_plugins', $active_plugins );
		}

		if ( $is_multi_site ) {
			$flattened_plugins = array();
			array_map(
				function( $plugin ) use ( &$flattened_plugins ) {
					$flattened_plugins = array_merge( $flattened_plugins, array( $plugin => time() ) );
				},
				$active_plugins
			);

			/* Translators: %s: plugin name. */
			Shipper_Helper_Log::write( sprintf( __( 'Setting these plugins as active for the network: %s', 'shipper' ), $this->convert_array_to_string( $active_plugins ) ) );
			update_site_option( 'active_sitewide_plugins', $flattened_plugins );
		}
	}

	/**
	 * Set package size. On API export migration, set package size on destination site.
	 * So that we can show the migration states on the dashboard.
	 *
	 * @since 1.2
	 *
	 * @see https://incsub.atlassian.net/browse/SHI-134
	 *
	 * @return void
	 */
	public function postprocess_set_package_size() {
		$migration = new Shipper_Model_Stored_Migration();

		// Only set data if it's a destination site.
		if ( ! $migration->is_from_hub() ) {
			return;
		}

		$manifest     = $this->get_manifest();
		$package_size = $manifest->get( 'package_size' );

		/* Translators: %s: package size. */
		Shipper_Helper_Log::debug( sprintf( __( 'Setting up package size: %s', 'shipper' ), $package_size ) );

		$migration->set_size( $package_size );
		$migration->save();
	}


	/**
	 * Convert a list of plugins/themes to a string.
	 *
	 * @since 1.2.4
	 *
	 * @param array $items An array of items.
	 *
	 * @return string|void
	 */
	private function convert_array_to_string( $items ) {
		if ( ! is_array( $items ) || empty( $items ) ) {
			return;
		}

		$items = array_map(
			function( $item ) {
				$info = pathinfo( $item );
				return '.' !== $info['dirname'] ? $info['dirname'] : $info['filename'];
			},
			$items
		);

		return apply_filters( 'shipper_convert_array_to_string', implode( ', ', $items ) );
	}

	/**
	 * Maybe move sitemeta to the options table.
	 *
	 * Network plugin settings don't get carried over when subsite to single migration is performed.
	 * So we're moving those sitemeta to options table.
	 *
	 * @see https://incsub.atlassian.net/browse/SHI-248
	 * @since 1.2.8
	 *
	 * @return void
	 */
	private function postprocess_maybe_move_sitemeta_to_options_table() {
		$manifest = $this->get_manifest();

		if ( 'subsite' !== $manifest->get( 'network_type' ) ) {
			return;
		}

		global $wpdb;

		$sitemeta_table = $wpdb->prefix . 'sitemeta';
		$has_sitemeta   = $wpdb->get_results( $wpdb->prepare( 'show tables like %s', $sitemeta_table ) );

		if ( empty( $has_sitemeta ) ) {
			Shipper_Helper_Log::debug( __( 'Sitemeta table is not found.', 'shipper' ) );
			return;
		}

		$sitemeta = $wpdb->get_results( "select * from {$wpdb->prefix}sitemeta", ARRAY_A );

		if ( empty( $sitemeta ) || ! is_array( $sitemeta ) ) {
			Shipper_Helper_Log::debug( __( 'No meta found to be moved to options table', 'shipper' ) );
			return;
		}

		Shipper_Helper_Log::write( __( 'Moving sitemeta to the options tables.', 'shipper' ) );

		foreach ( $sitemeta as $meta ) {
			if ( in_array( $meta['meta_key'], $this->meta_to_ignore(), true ) ) {
				continue;
			}

			$meta_value = is_serialized( $meta['meta_value'] )
				? unserialize( $meta['meta_value'] ) // phpcs:ignore
				: $meta['meta_value'];

			update_option( $meta['meta_key'], $meta_value );
		}

		$wpdb->query( "DROP TABLE {$wpdb->prefix}sitemeta" ); // phpcs:ignore
	}

	/**
	 * Ignored meta list
	 *
	 * @since 1.2.8
	 *
	 * @return string[]
	 */
	public function meta_to_ignore() {
		return apply_filters(
			'shipper_postprocess_ignored_meta_list',
			array(
				'site_name',
				'admin_email',
				'admin_user_id',
				'site_admins',
				'siteurl',
			)
		);
	}
}