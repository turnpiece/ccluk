<?php
/**
 * Class WP_Hummingbird_Settings manages common settings for modules.
 *
 * @package Hummingbird
 * @since 1.8
 */

class WP_Hummingbird_Settings {

	/**
	 * Plugin instance.
	 *
	 * @var WP_Hummingbird_Settings
	 */
	private static $instance;

	/**
	 * List of available modules.
	 *
	 * @since 1.8
	 *
	 * @var array
	 */
	private static $available_modules = array( 'minify', 'page_cache', 'performance', 'uptime', 'gravatar', 'caching', 'cloudflare', 'advanced', 'rss' );

	/**
	 * List of network modules that have settings for each sub-site.
	 *
	 * @since 1.8
	 *
	 * @var array
	 */
	private static $network_modules = array( 'minify', 'page_cache', 'performance', 'advanced' );

	/**
	 * Return the plugin instance.
	 *
	 * @return WP_Hummingbird_Settings
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new WP_Hummingbird_Settings();
		}

		return self::$instance;
	}

	/**
	 * WP_Hummingbird_Settings constructor.
	 */
	private function __construct() {}

	/**
	 * Return the plugin default settings.
	 *
	 * @return array  Default Hummingbird settings.
	 */
	public static function get_default_settings() {
		$defaults = array(
			'minify' => array(
				'enabled'     => false,
				'use_cdn'     => true,
				'log'         => false,
				'file_path'   => '',
				// Only for multisites. Toggles minification in a subsite
				// By default is true as if 'minify'-'enabled' is set to false, this option has no meaning.
				'minify_blog' => true,
				'view'        => 'basic', // Accepts: 'basic' or 'advanced'.
				// Only for multisite.
				'block'       => array(
					'scripts' => array(),
					'styles'  => array(),
				),
				'dont_minify' => array(
					'scripts' => array(),
					'styles'  => array(),
				),
				'combine'     => array(
					'scripts' => array(),
					'styles'  => array(),
				),
				'position'    => array(
					'scripts' => array(),
					'styles'  => array(),
				),
				'defer'       => array(
					'scripts' => array(),
					'styles'  => array(),
				),
				'inline'      => array(
					'scripts' => array(),
					'styles'  => array(),
				),
			),
			'uptime'      => array(
				'enabled' => false,
			),
			'gravatar' => array(
				'enabled'    => false,
			),
			'page_cache'  => array(
				'enabled'    => false,
				// Only for multisites. Toggles page caching in a subsite
				// By default is true as if 'page_cache'-'enabled' is set to false, this option has no meaning.
				'cache_blog'   => true,
				'control'      => false,
				// Accepts: 'manual', 'auto' and 'none'.
				'detection'    => 'manual',
				'pages_cached' => 0,
			),
			'caching' => array(
				// Always enabled, so no 'enabled' option.
				'expiry_css'        => '8d/A691200',
				'expiry_javascript' => '8d/A691200',
				'expiry_media'      => '8d/A691200',
				'expiry_images'     => '8d/A691200',
			),
			'cloudflare' => array(
				'enabled'      => false,
				'connected'    => false,
				'email'        => '',
				'api_key'      => '',
				'zone'         => '',
				'zone_name'    => '',
				'plan'         => false,
				'page_rules'   => array(),
				'cache_expiry' => 691200,
			),
			'performance' => array(
				'reports'       => false,
				'subsite_tests' => false,
				'dismissed'     => false,
				'last_score'    => 0,
			),
			'advanced' => array(
				'query_string' => false,
				'emoji'        => false,
				'prefetch'     => array(),
				'db_cleanups'  => false,
			),
			'rss' => array(
				'enabled'  => true,
				'duration' => 3600,
			),
		);

		/**
		 * Filter the default settings.
		 * Useful when adding new settings to the plugin
		 */
		return apply_filters( 'wp_hummingbird_default_options', $defaults );
	}

	/**
	 * Array of settings per sub-site.
	 *
	 * @access private
	 *
	 * @param string $module  Module for to get sub site setting fields for.
	 *
	 * @return array
	 */
	private static function get_blog_option_names( $module ) {
		if ( ! in_array( $module, self::$network_modules, true ) ) {
			return array();
		}

		$options = array(
			'minify'      => array( 'minify_blog', 'view', 'block', 'dont_minify', 'combine', 'position', 'defer', 'inline' ),
			'page_cache'  => array( 'cache_blog' ),
			'performance' => array( 'dismissed', 'last_score' ),
			'advanced'    => array( 'query_string', 'emoji', 'prefetch' ),
		);

		return $options[ $module ];
	}

	/**
	 * Filter out sub site options from network options on multisite.
	 *
	 * @access private
	 *
	 * @param array $options  Options array.
	 *
	 * @return array
	 */
	private static function filter_multisite_options( $options ) {
		$network_options = $blog_options = array();

		foreach ( $options as $module => $setting ) {
			/*
			 * Skip if module is not registered.
			 * Only needed in case an update to 1.8 manually by replacing the files.
			 */
			if ( ! in_array( $module, self::$available_modules, true ) ) {
				continue;
			}

			$data = array_fill_keys( self::get_blog_option_names( $module ), self::get_blog_option_names( $module ) );
			$network_options[ $module ] = array_diff_key( $setting, $data );
			$blog_options[ $module ] = array_intersect_key( $setting, $data );
		}

		// array_filter will remove all empty values.
		return array(
			'network' => $network_options,
			'blog'    => array_filter( $blog_options ),
		);
	}

	/**
	 * Reset database to default settings. Will overwrite all current settings.
	 * This can be moved out to update_settings, because it's almost identical.
	 */
	public static function reset_to_defaults() {
		$defaults = self::get_default_settings();

		if ( ! is_multisite() ) {
			update_option( 'wphb_settings', $defaults );
		} else {
			$options = self::filter_multisite_options( $defaults );
			update_site_option( 'wphb_settings', $options['network'] );
			update_option( 'wphb_settings', $options['blog'] );
		}
	}

	/**
	 * Return the plugin settings.
	 *
	 * @param bool|string $for_module  Module to fetch options for.
	 *
	 * @return array  Hummingbird settings.
	 */
	public static function get_settings( $for_module = false ) {
		if ( ! is_multisite() ) {
			$options = get_option( 'wphb_settings', array() );
		} else {
			$blog_options = get_option( 'wphb_settings', array() );
			$network_options = get_site_option( 'wphb_settings', array() );
			$options = array_merge_recursive( $blog_options, $network_options );
		}

		$defaults = self::get_default_settings();

		// We need to parse each module individually.
		foreach ( $defaults as $module => $option ) {
			// If there is nothing set in the current option, we use the default set.
			if ( ! isset( $options[ $module ] ) ) {
				$options[ $module ] = $option;
				continue;
			}
			// Else we combine defaults with current options.
			$options[ $module ] = wp_parse_args( $options[ $module ], $option );
		}

		return ( $for_module ) ? $options[ $for_module ] : $options;
	}

	/**
	 * Update the plugin settings.
	 *
	 * @param array       $new_settings  New settings.
	 * @param bool|string $for_module    Module to update settings for.
	 */
	public static function update_settings( $new_settings, $for_module = false ) {
		if ( $for_module ) {
			$options = self::get_settings();
			$options[ $for_module ] = $new_settings;
			$new_settings = $options;
		}

		if ( ! is_multisite() ) {
			update_option( 'wphb_settings', $new_settings );
		} else {
			$options = self::filter_multisite_options( $new_settings );
			update_site_option( 'wphb_settings', $options['network'] );
			update_option( 'wphb_settings', $options['blog'] );
		}
	}

	/**
	 * Get setting.
	 *
	 * @param string      $option_name  Return a single WP Hummingbird setting.
	 * @param bool|string $for_module   Module to fetch options for.
	 *
	 * @return mixed
	 */
	public static function get_setting( $option_name, $for_module = false ) {
		$options = self::get_settings( $for_module );

		if ( ! isset( $options[ $option_name ] ) ) {
			return '';
		}

		/**
		 * Failsafe for when options are stored incorrectly.
		 */
		$defaults = self::get_default_settings();
		if ( $for_module ) {
			$defaults = $defaults[ $for_module ];
		}

		if ( self::is_exception( $for_module, $options, $option_name ) ) {
			return $options[ $option_name ];
		}

		if ( gettype( $defaults[ $option_name ] ) !== gettype( $options[ $option_name ] ) ) {
			self::update_setting( $option_name, $defaults[ $option_name ], $for_module );
			return $defaults[ $option_name ];
		}

		return $options[ $option_name ];
	}

	/**
	 * Check if setting has an exception.
	 *
	 * In get_settings we compare the values to defaults (including value type).
	 * Two options can be bool/string: minify -> enabled and page_cache -> enabled.
	 *
	 * @since 1.8.1
	 *
	 * @param string $module       Module.
	 * @param array  $options      Options.
	 * @param string $option_name  Option name.
	 *
	 * @return bool
	 */
	private static function is_exception( $module, $options, $option_name ) {
		$exceptions = array(
			'minify'      => 'super-admins',
			'page_cache'  => 'blog-admins',
		);

		if ( isset( $exceptions[ $module ] ) && $exceptions[ $module ] === $options[ $option_name ] ) {
			return true;
		}

		return false;
	}

	/**
	 * Update selected plugin setting.
	 *
	 * @param string      $option_name  Setting name.
	 * @param mixed       $value        Setting value.
	 * @param bool|string $for_module   Module to update settings for.
	 */
	public static function update_setting( $option_name, $value, $for_module = false ) {
		$options = self::get_settings( $for_module );
		$options[ $option_name ] = $value;
		self::update_settings( $options, $for_module );
	}

	/**
	 * Return a single WP Hummingbird option.
	 *
	 * @param string $option  Option.
	 *
	 * @return mixed
	 */
	public static function get( $option ) {
		if ( ! is_main_site() ) {
			$value = get_option( $option );
		} else {
			$value = get_site_option( $option );
		}

		return $value;
	}

	/**
	 * Delete a single WP Hummingbird option.
	 *
	 * @param string $option  Option.
	 */
	public static function delete( $option ) {
		if ( ! is_main_site() ) {
			delete_option( $option );
		} else {
			delete_site_option( $option );
		}
	}

	/**
	 * Update option.
	 *
	 * @param string $option  WP Hummingbird option name.
	 * @param mixed  $value   WP Hummingbird option value.
	 */
	public static function update( $option, $value ) {
		if ( ! is_main_site() ) {
			update_option( $option, $value );
		} else {
			update_site_option( $option, $value );
		}
	}

}